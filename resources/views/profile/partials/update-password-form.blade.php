<section>
    <form method="post" action="{{ route('password.update') }}">
        @csrf
        @method('put')

        <div class="row g-3">
            <div class="col-12">
                <label class="form-label">Current Password</label>
                <input id="update_password_current_password" type="password" name="current_password"
                       class="form-control" autocomplete="current-password">
                @if ($errors->updatePassword->has('current_password'))
                    <small class="text-danger">{{ $errors->updatePassword->first('current_password') }}</small>
                @endif
            </div>

            <div class="col-12">
                <label class="form-label">New Password</label>
                <input id="update_password_password" type="password" name="password"
                       class="form-control" autocomplete="new-password">
                @if ($errors->updatePassword->has('password'))
                    <small class="text-danger">{{ $errors->updatePassword->first('password') }}</small>
                @endif
            </div>

            <div class="col-12">
                <label class="form-label">Confirm New Password</label>
                <input id="update_password_password_confirmation" type="password" name="password_confirmation"
                       class="form-control" autocomplete="new-password">
                @if ($errors->updatePassword->has('password_confirmation'))
                    <small class="text-danger">{{ $errors->updatePassword->first('password_confirmation') }}</small>
                @endif
            </div>
        </div>

        <div class="mt-4 d-flex align-items-center gap-3">
            <button type="submit">
                <i class="fa-solid fa-shield-halved me-1"></i> Update Password
            </button>
            @if (session('status') === 'password-updated')
                <span class="text-success"><i class="fa-solid fa-circle-check me-1"></i>Password updated.</span>
            @endif
        </div>
    </form>
</section>
