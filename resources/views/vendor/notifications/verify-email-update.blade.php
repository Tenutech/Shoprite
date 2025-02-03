<!DOCTYPE html>
<html>
<head>
    <title>Verify Your Email</title>
</head>
<body>
    <p>Hello {{ $user->firstname }},</p>
    <p>You have requested to update your profile. Please verify your email before proceeding.</p>
    <p>Click the link below to verify:</p>
    <p><a href="{{ $verificationUrl }}">Verify Email</a></p>
    <p><strong>This link will expire in 15 minutes.</strong></p>

    <p>Regards,</p>
    <p><strong>Shoprite Job Opportunities</strong></p>

    <!-- Footer Image (Shoprite Banner) -->
    <p>
        <img src="{{ asset('build/images/shoprite-banner.png') }}" alt="Shoprite - Job Opportunities" style="width: 200px; height: auto;">
    </p>
</body>
</html>