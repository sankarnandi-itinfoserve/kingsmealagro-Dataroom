<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><title>Welcome to {{ config('app.name') }}</title></head>
<body style="font-family:Arial,sans-serif;background:#f4f6f8;margin:0;padding:0;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f6f8;padding:30px 0;">
  <tr><td align="center">
    <table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:8px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.08);">
      <tr><td style="background:#ffffff;padding:20px 32px;border-bottom:3px solid #1e3a5f;text-align:center;">
        <img src="{{ asset('admin/images/logo.png') }}" alt="{{ config('app.name') }}" width="197" height="56" style="max-height:56px;width:auto;display:inline-block;">
      </td></tr>
      <tr><td style="background:#1e3a5f;padding:16px 32px;">
        <h2 style="color:#fff;margin:0;font-size:18px;letter-spacing:.3px;">{{ config('app.name') }}</h2>
      </td></tr>
      <tr><td style="padding:32px;">
        <h3 style="color:#1e3a5f;margin:0 0 16px;">Welcome Aboard!</h3>
        <p style="color:#374151;margin:0 0 20px;">
          Hello, {{ $user->full_name }},<br><br>
          We're excited to welcome you to {{ config('app.name') }}. Your account has been successfully created and
          you can now securely manage, share, and collaborate on your data.
        </p>
        <table cellpadding="0" cellspacing="0" style="background:#f9fafb;border-radius:6px;padding:16px;width:100%;margin:0 0 20px;">
          <tr><td style="color:#6b7280;font-size:13px;padding:4px 0;">Name</td><td style="color:#111827;font-weight:600;font-size:14px;padding:4px 0;">{{ $user->full_name }}</td></tr>
          <tr><td style="color:#6b7280;font-size:13px;padding:4px 0;">Email</td><td style="color:#111827;font-weight:600;font-size:14px;padding:4px 0;">{{ $user->email }}</td></tr>
          @if ($user->username)
            <tr><td style="color:#6b7280;font-size:13px;padding:4px 0;">Username</td><td style="color:#111827;font-weight:600;font-size:14px;padding:4px 0;">{{ $user->username }}</td></tr>
          @endif
          @if ($password)
            <tr><td style="color:#6b7280;font-size:13px;padding:4px 0;">Password</td><td style="color:#111827;font-weight:600;font-size:14px;padding:4px 0;">{{ $password }}</td></tr>
          @endif
        </table>
        @if ($password)
          <p style="color:#6b7280;font-size:13px;margin:0 0 20px;">For your security, please sign in and change your password as soon as possible.</p>
        @endif
        <a href="{{ url('/login') }}" style="display:inline-block;background:#1e3a5f;color:#fff;text-decoration:none;padding:10px 24px;border-radius:5px;font-size:14px;margin:0 0 20px;">Sign In to Your Account</a>
        <p style="color:#6b7280;font-size:13px;margin:0;">If you didn't create this account, please contact our support team immediately.</p>
      </td></tr>
      <tr><td style="background:#f9fafb;padding:16px 32px;color:#9ca3af;font-size:12px;text-align:center;">
        Need help? <a href="mailto:support@weavers-web.com" style="color:#1e3a5f;text-decoration:none;font-weight:600;">Contact Support</a><br>
        &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
      </td></tr>
    </table>
  </td></tr>
</table>
</body>
</html>
