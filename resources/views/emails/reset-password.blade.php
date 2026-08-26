<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><title>Reset Your Password</title></head>
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
        <h3 style="color:#1e3a5f;margin:0 0 16px;">Password Reset Request</h3>
        <p style="color:#374151;margin:0 0 20px;">
          Hello, {{ $user->full_name }},<br><br>
          Someone requested a password reset for your {{ config('app.name') }} account.
          Click the button below to set a new password. This link will expire in <strong>60 minutes</strong>.
        </p>
        <a href="{{ $resetUrl }}" style="display:inline-block;background:#1e3a5f;color:#fff;text-decoration:none;padding:10px 24px;border-radius:5px;font-size:14px;margin:0 0 20px;">Reset My Password</a>
        <table cellpadding="0" cellspacing="0" style="background:#f9fafb;border-radius:6px;padding:16px;width:100%;margin:0 0 20px;">
          <tr><td style="color:#6b7280;font-size:13px;padding:4px 0;">Or copy this link</td></tr>
          <tr><td style="color:#374151;font-size:12px;word-break:break-all;padding:4px 0;">{{ $resetUrl }}</td></tr>
        </table>
        <p style="color:#6b7280;font-size:13px;margin:0;">Didn't request this? You can safely ignore this email — your password will not change unless you click the button above.</p>
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
