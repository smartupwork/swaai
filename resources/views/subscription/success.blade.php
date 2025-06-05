<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Subscription Status</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            background: #f8f9fa;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .card {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            width: 90%;
        }

        h2 {
            color: #28a745;
            margin-bottom: 1rem;
        }

        .error {
            color: #dc3545;
        }

        a.button {
            display: inline-block;
            margin-top: 1.5rem;
            padding: 10px 20px;
            background: #007bff;
            color: white;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 500;
        }
    </style>
</head>

<body>
    <div class="card">
        @if (str_contains($return_url, 'success=true'))
            <h2>{{ $message }}</h2>
        @else
            <h2 class="error">{{ $message }}</h2>
        @endif

        <a href="{{ $return_url }}" class="button">Return to App</a>
    </div>
</body>

</html>
