<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Subscription Confirmation</title>
</head>

<body>
    <h2>Hello {{ $user->first_name }},</h2>

    <p>{{ $subscriptionRecord->status_message }}</p>

    <p><strong>Plan:</strong> {{ $subscriptionRecord->plan ?? 'N/A' }}</p>
    <p><strong>Start Date:</strong> {{ \Carbon\Carbon::parse($subscriptionRecord->start_date)->toFormattedDateString() }}</p>
    <p><strong>End Date:</strong> {{ \Carbon\Carbon::parse($subscriptionRecord->end_date)->toFormattedDateString() }}</p>
    <p><strong>Status:</strong> {{ $subscriptionRecord->status }}</p>

    <p>If you have any questions, feel free to reach us.</p>

    <p>Thanks,<br>Swaai</p>
</body>

</html>
