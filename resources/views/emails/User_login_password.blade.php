@component('mail::message')
# Introduction

Password: {{ $message['password'] }}

Email: {{ $message['email'] }}


<p style="font-size: 14px; color: #777; margin-top: 20px;">Thanks!<br>Sugar-Papi</p>
@endcomponent

