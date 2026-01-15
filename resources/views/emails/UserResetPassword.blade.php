@component('mail::message')
    <div style="text-align:center;">
        <img src="https://a-znutritionandsmoothies.com/public/img/logo.png" alt="App Icon"
            style="vertical-align: middle;margin-bottom: -3px;height: 125px;margin-bottom: 35px">
        <h3>Reset Your Password</h3>
    </div>
    <div style="text-align:center;">
        <p>We have received a request to reset your password. To proceed, click the button below:</p>
    </div>

<div style="text-align:center;">
    <p style="width: 160px;margin:auto">
        <a href="{{ $user['url'] }}"
            style="display: inline-block; padding: 5px 10px; color: #fff; background: #dc3838; border-radius: 5px; text-decoration: none;">Reset
            Password</a>
    </p>
    <div style="text-align:center;">
        <p style="font-size: 14px; color: #777; margin-top: 20px;">If you didn't request this change, please ignore this
            email.<br>Thanks,<br>Sugar-Papi Team</p>
    </div>
</div>

@endcomponent
