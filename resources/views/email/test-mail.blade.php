<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Password Reset Request</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 30px;">
    <table width="100%" cellpadding="0" cellspacing="0" style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1);">
        <tr>
            <td style="padding: 30px;">
                <h2 style="color: #333333;">Password Reset Requested</h2>
                <p>Hello {{ $name ?? 'User' }},</p>

                <p>We received a request to reset your password. If you made this request, click the button below to set a new password:</p>

                <p style="text-align: center;">
                    <a href="{{ $resetLink ?? '#' }}" style="display: inline-block; padding: 12px 24px; background-color: #007BFF; color: white; text-decoration: none; border-radius: 5px;">
                        Reset Password
                    </a>
                </p>

                <p>If you didn’t request a password reset, please ignore this email. No changes have been made to your account.</p>

                <p>If you believe this was a mistake or need help, feel free to contact our support team at <a href="mailto:support@yourcompany.com">support@yourcompany.com</a>.</p>

                <p style="margin-top: 40px;">Best regards,<br>Your Company Team</p>
            </td>
        </tr>
    </table>
</body>
</html>
