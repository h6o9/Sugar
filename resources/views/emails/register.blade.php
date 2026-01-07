@component('mail::message')
<h1 style="margin: 0 auto 10px; width: 145px; font-size: 24px; color: #333;">Registration</h1>
<p style="font-size: 16px; color: #555;">
    Congratulations, {{$message['name']}}! Your account has been created successfully. Your OTP for verification is:
</p>

<p><strong>OTP:</strong> {{ $message['otp'] }}</p>

<p><strong>Email:</strong> {{ $message['email'] }}</p>
<p><strong>Password:</strong> {{ $message['password'] }}</p>


<p style="font-size: 14px; color: #777; margin-top: 20px;">Thanks!<br>aznutrition-and-smoothie</p>
@endcomponent
