<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><title>You're Invited</title></head>
<body style="font-family:Arial,sans-serif;background:#f4f6f8;margin:0;padding:0;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f6f8;padding:30px 0;">
  <tr><td align="center">
    <table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:8px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.08);">
      <tr><td style="background:#ffffff;padding:20px 32px;border-bottom:3px solid #1e3a5f;text-align:center;">
        <img src="{{ url('admin/images/logo.png') }}" alt="{{ config('app.name') }}" width="160" height="46" style="max-height:46px;width:auto;display:inline-block;">
      </td></tr>
      <tr><td style="background:#1e3a5f;padding:16px 32px;">
        <h2 style="color:#fff;margin:0;font-size:18px;letter-spacing:.3px;">{{ config('app.name') }}</h2>
      </td></tr>
      <tr><td style="padding:32px;">
        <h3 style="color:#1e3a5f;margin:0 0 16px;">You're Invited</h3>
        <p style="color:#374151;margin:0 0 20px;">
          You have been invited to join {{ config('app.name') }}.
          Click the button below to set your password and accept the NDA terms.
        </p>
        <a href="{{ $inviteLink }}" style="display:inline-block;background:#1e3a5f;color:#fff;text-decoration:none;padding:10px 24px;border-radius:5px;font-size:14px;margin:0 0 20px;">Accept Invitation</a>
        <p style="color:#6b7280;font-size:13px;margin:0;">This invitation link expires in <strong>24 hours</strong>.</p>
      </td></tr>
      <tr><td style="background:#f9fafb;padding:16px 32px;color:#9ca3af;font-size:12px;text-align:center;">
        If you did not expect this invitation, you can safely ignore this email — no account will be created without your action.<br>
        &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
      </td></tr>
    </table>
  </td></tr>
</table>
</body>
</html>
