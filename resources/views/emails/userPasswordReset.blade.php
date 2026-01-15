@component('mail::message')
    We have received reset password request, please click below button to reset password.
@component('mail::button', ['url' => $user['url']])
Reset Password
@endcomponent

<p style="font-size: 14px; color: #777; margin-top: 20px;">Thanks!<br>Sugar-Papi</p>
@endcomponent

