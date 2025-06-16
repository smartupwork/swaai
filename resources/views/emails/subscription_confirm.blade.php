<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Subscription Confirmation</title>
</head>

<body>
    <h2>Hello {{ $user->first_name }},</h2>

    <p>Thank you for subscribing to the <strong>{{ $subscription->plan }}</strong> plan.</p>

    <p><strong>Start Date:</strong> {{ \Carbon\Carbon::parse($subscription->start_date)->toFormattedDateString() }}</p>
    <p><strong>End Date:</strong> {{ \Carbon\Carbon::parse($subscription->end_date)->toFormattedDateString() }}</p>
    <p><strong>Status:</strong> {{ $subscription->status }}</p>

    <p>If you have any questions, feel free to reach out to our support team.</p>

    <p>Thanks,<br>Swaai</p>
</body>

</html>
