<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): ?bool
    {
        try {
            return true;
        } catch (\Exception $e) {
            Log::error('LoginRequest::authorize failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): ?array
    {
        try {
            return [
                'email' => ['required', 'string', 'email'],
                'password' => ['required', 'string'],
            ];
        } catch (\Exception $e) {
            Log::error('LoginRequest::rules failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        try {
            $this->ensureIsNotRateLimited();

            if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
                RateLimiter::hit($this->throttleKey());

                throw ValidationException::withMessages([
                    'email' => trans('auth.failed'),
                ]);
            }

            RateLimiter::clear($this->throttleKey());
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('LoginRequest::authenticate failed: ' . $e->getMessage());
        }
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        try {
            if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
                return;
            }

            event(new Lockout($this));

            $seconds = RateLimiter::availableIn($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => trans('auth.throttle', [
                    'seconds' => $seconds,
                    'minutes' => ceil($seconds / 60),
                ]),
            ]);
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('LoginRequest::ensureIsNotRateLimited failed: ' . $e->getMessage());
        }
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): ?string
    {
        try {
            return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
        } catch (\Exception $e) {
            Log::error('LoginRequest::throttleKey failed: ' . $e->getMessage());
            return null;
        }
    }
}
