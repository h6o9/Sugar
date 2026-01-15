<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Password Reset Request</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: "Segoe UI", Arial, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
        }
        .email-container {
            max-width: 600px;
            margin: 40px auto;
            background: #ffffff;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            overflow: hidden;
        }
        .header {
            text-align: center;
            padding: 30px 0 10px 0;
            background: #ffffff; /* no background color behind logo */
        }
        .header img {
            width: 150px;
            height: auto;
        }
        .content {
            padding: 30px;
            text-align: center;
        }
        .content h2 {
            color: #222;
            margin-bottom: 15px;
        }
        .content p {
            color: #555;
            font-size: 15px;
            line-height: 1.6;
        }
        .button {
            display: inline-block;
            background-color: #007bff;
            color: white !important;
            text-decoration: none;
            padding: 12px 30px;
            border-radius: 6px;
            margin-top: 25px;
            font-weight: bold;
        }
        .footer {
            text-align: center;
            padding: 20px;
            background-color: #f8f9fa;
            color: #888;
            font-size: 13px;
        }
        .footer p {
            margin: 0;
        }
    </style>
</head>
<body>

<div class="email-container">

    <!-- Logo -->
    <div class="header">
        <img src="{{ url('public/img/logo.png') }}" alt="Sugar-Papi Logo">
    </div>

    <!-- Content -->
    <div class="content">
        <h2>Password Reset Request</h2>
        <p>We have received a request to reset your password. Click the button below to set a new password.</p>

        <a href="{{ $detail['url'] }}" class="button" target="_blank">Reset Password</a>

        <p style="margin-top:25px; font-size:14px;">If you didn’t request this, you can safely ignore this email.</p>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>Thanks,<br><strong>Sugar-Pappi Team</strong></p>
    </div>

</div>

</body>
</html>
