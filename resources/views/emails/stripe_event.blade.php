<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Payment Method Notification</title>
</head>

<body>
    <h2>Hello,</h2>

    <p>{{ $messageBody }}</p>

    <p>If you made this change, no action is needed.</p>
    <p>If you didn’t, please contact our support team right away.</p>

    <p>Thank you,<br>
        The {{ config('app.name') }} Team</p>
</body>

</html>
