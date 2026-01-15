@component('mail::message')

<div style="text-align:center; margin-bottom:20px;">
    <img src="{{ asset('public/img/logo.png') }}" alt="Logo" style="width:140px;">
</div>

<h2 style="text-align:center; margin-bottom:20px;">Your One Time Password (OTP)</h2>

<p style="font-size:16px; color:#555;">
Hi,
</p>

<p style="font-size:16px; color:#555;">
We received a request to reset your account password. Please use the OTP below to complete your verification:
</p>

<div style="background:#f4f6f8; padding:20px; text-align:center; border-radius:8px; margin:20px 0;">
    <h1 style="font-size:32px; letter-spacing:4px; color:#333; margin:0;">
        {{ $message['otp'] }}
    </h1>
</div>

<p style="font-size:14px; color:#666;">
This OTP is valid for the next 5 minutes. Please use it promptly.  
If you did not request this, please ignore this email.
</p>

<p style="font-size:14px; color:#777; margin-top:30px; text-align:center;">
Thanks & Regards,<br>
<strong>Sugar-Papi</strong>
</p>

@endcomponent
