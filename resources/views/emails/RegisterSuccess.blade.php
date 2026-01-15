@component('mail::message')
<h1 style="margin: 0 auto 10px; width: 145px; font-size: 24px; color: #333;">Registration</h1>
<p style="font-size: 16px; color: #555;">
    Congratulations, {{$message['name']}}! Your account has been created successfully by the Admin. You can now log in with the following credentials:
</p>

<p><strong>Email:</strong> {{ $message['email'] }}</p>
<p><strong>Password:</strong> {{ $message['password'] }}</p>

<div style="width: 160px; margin: auto; margin-top: 20px;">
    <a href="{{url('branch')}}" style="display: block; padding: 10px; color: #fff; background: #12df12; border-radius: 5px; text-align: center; text-decoration: none;">
        Click here to Login
    </a>
</div>
<p style="font-size: 14px; color: #777; margin-top: 20px;">Thanks!<br>Sugar-Papi</p>
@endcomponent
