<!DOCTYPE html>
<html>

<head>
    <title>Your New Password</title>
</head>

<body>
    <p>Dear {{ $user->name }},</p>
    <p>Your password has been reset. Here is your new password:</p>
    <p><strong>{{ $newPassword }}</strong></p>
    <p>Please make sure to change this password after logging in.</p>
    <p>Thank you,</p>
    <p>The {{ config('app.name') }} Team</p>
</body>

</html>
