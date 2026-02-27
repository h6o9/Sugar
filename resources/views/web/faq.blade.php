<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FAQs</title>

    <link href="https://fonts.googleapis.com/css2?family=Heebo:wght@400;500;600&family=Nunito:wght@600;700;800&family=Pacifico&display=swap" rel="stylesheet">

    <style>
        * {
            font-family: "Heebo", sans-serif;
        }

        body {
            margin: 0;
            font-size: 16px;
        }

        .banner {
            background: #a4a0a0;
            padding: 60px 0;
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 20px;
        }

        .banner h1 {
            font-size: 80px;
            color: white;
            text-shadow: 0 2px 6px rgba(0,0,0,.459);
        }

        .container {
            padding: 0 20px 40px;
            max-width: 1000px;
            margin: auto;
        }

        .faq-item {
            margin-bottom: 25px;
        }

        .faq-question {
            font-weight: 600;
            font-size: 18px;
            margin-bottom: 6px;
        }

        .faq-answer {
            color: #555;
            line-height: 1.6;
        }

        @media (max-width: 991.5px) {
            .banner h1 { font-size: 40px; }
            .banner { padding: 32px 0; }
            body { font-size: 14px; }
        }

        @media (max-width: 399px) {
            .banner h1 { font-size: 30px; }
            .banner { padding: 30px 0; }
        }
    </style>
</head>
<body>

    <div class="banner">
        <h1>FAQs</h1>
    </div>

    <div class="container">
		<h1>Frequently Asked Questions</h1>
        @if(isset($data) && $data->count() > 0)

            @foreach($data as $faq)

    <div style="margin-bottom:20px;">

        <div>
            <strong>
                Q: {{ strip_tags($faq->question) }}
            </strong>
        </div>

        <div>
            A: {{ strip_tags($faq->answer) }}
        </div>

    </div>

@endforeach

        @else
            <p>No FAQs available at the moment.</p>
        @endif

    </div>

</body>
</html>