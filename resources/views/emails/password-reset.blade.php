<!DOCTYPE html>
<html>
<head>
    <title>Your New Password</title>
</head>
<body>
    <p>Hi {{ $user->name }},</p>
    <p>Your password has been reset. Here is your new password:</p>
    <p><strong>{{ $password }}</strong></p>
    <p>Please use this password to log in to your account. We recommend changing it immediately after logging in.</p>
    <p>Thank you!</p>
</body>
</html>