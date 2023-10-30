<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Reset Password Email</title>
</head>

<body style="font-family: Arial, Helvetica, sans-serif; font-size: 16px">

    <p>Hello, <b>{{ $formData['user']->name }}</b></p>

    <p>You have requested to change a password.</p>

    <p>Please click a link given below to change password.</p>

    <a href="{{ route('account.resetPassword', $formData['token']) }}">Click Here</a>

    <p>King Rewards</p>

</body>
</html>