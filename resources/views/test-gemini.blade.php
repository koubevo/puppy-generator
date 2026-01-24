<!DOCTYPE html>
<html lang="cs">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gemini Test</title>
    <style>
        body {
            font-family: system-ui, -apple-system, sans-serif;
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }

        .card {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        h1 {
            margin: 0 0 20px;
            color: #333;
        }

        .message {
            font-size: 1.2em;
            line-height: 1.6;
            color: #555;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 12px;
            margin-bottom: 20px;
        }

        img {
            width: 100%;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
    </style>
</head>

<body>
    <div class="card">
        <h1>🐕 Daily Puppy Test</h1>
        <div class="message">{{ $message }}</div>
        <img src="{{ $image }}" alt="Cute puppy">
    </div>
</body>

</html>