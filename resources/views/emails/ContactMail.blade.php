<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Contact Message - Sugar-Papi</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: "Segoe UI", Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 30px;
        }
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            overflow: hidden;
        }
        .header {
            text-align: center;
            padding: 25px 0 10px 0;
            background: #ffffff;
        }
        .header img {
            width: 130px;
            height: auto;
        }
        .content {
            padding: 25px 30px;
        }
        .content h2 {
            color: #222;
            margin-bottom: 15px;
            text-align: center;
        }
        .content p {
            font-size: 15px;
            color: #555;
            line-height: 1.6;
        }
        .info-box {
            background-color: #f8f9fa;
            border-left: 4px solid #007bff;
            padding: 12px 15px;
            border-radius: 6px;
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            padding: 20px;
            background-color: #f4f4f4;
            color: #888;
            font-size: 13px;
        }
    </style>
</head>
<body>

<div class="email-container">

    <!-- Header -->
    <div class="header">
        <img src="{{ asset('public/img/logo.png') }}" alt="Sugar-Papi Logo">
    </div>

    <!-- Content -->
    <div class="content">
        <h2>New Message</h2>

        <!-- Sender Info -->
        <div class="info-box">
            <p><strong>Name:</strong> {{ $data['name'] }}</p>
            <p><strong>Email:</strong> {{ $data['email'] }}</p>
        </div>
        <!-- Subject Box -->
            <p><strong>Subject:</strong> {{ $data['subject'] }}</p>
        <!-- Message -->
        <p>{{ $data['message'] }}</p>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>Thanks,<br><strong>Sugar-Pappi Team</strong></p>
    </div>

</div>

</body>
</html>
