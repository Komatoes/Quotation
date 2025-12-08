<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 30px 20px;
            text-align: center;
            color: white;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 600;
        }
        .content {
            padding: 40px 30px;
            text-align: center;
        }
        .content p {
            color: #333;
            line-height: 1.6;
            margin: 0 0 20px 0;
            font-size: 14px;
        }
        .otp-box {
            background-color: #f8f9fa;
            border: 2px solid #667eea;
            border-radius: 8px;
            padding: 30px;
            margin: 30px 0;
        }
        .otp-code {
            font-size: 48px;
            font-weight: 700;
            color: #667eea;
            letter-spacing: 10px;
            margin: 0;
            font-family: 'Courier New', monospace;
        }
        .otp-note {
            color: #666;
            font-size: 12px;
            margin-top: 15px;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            color: #666;
            font-size: 12px;
            border-top: 1px solid #e3e6f0;
        }
        .security-info {
            background-color: #e7f3ff;
            border-left: 4px solid #0d6efd;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
            text-align: left;
            font-size: 13px;
            color: #004085;
        }
        .security-info strong {
            display: block;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>Password Reset Request</h1>
            <p style="margin: 10px 0 0 0; font-size: 14px; opacity: 0.9;">Quotation Management System</p>
        </div>

        <!-- Content -->
        <div class="content">
            <p style="margin-top: 0;">Hello,</p>
            
            <p>We received a request to reset your password. Use the OTP below to proceed with your password reset:</p>

            <!-- OTP Box -->
            <div class="otp-box">
                <p class="otp-code">{{ $otp }}</p>
                <p class="otp-note">This OTP will expire in 15 minutes</p>
            </div>

            <!-- Security Info -->
            <div class="security-info">
                <strong>Security Information:</strong>
                <ul style="margin: 5px 0; padding-left: 20px;">
                    <li>Never share your OTP with anyone</li>
                    <li>Our team will never ask for your OTP via email or phone</li>
                    <li>If you didn't request a password reset, please ignore this email</li>
                </ul>
            </div>

            <p style="color: #999; font-size: 12px; margin-top: 20px;">
                If you have any questions or need assistance, please contact our support team.
            </p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p style="margin: 0 0 10px 0;">Quotation Management System</p>
            <p style="margin: 0; color: #999;">This is an automated email. Please do not reply directly to this message.</p>
        </div>
    </div>
</body>
</html>
