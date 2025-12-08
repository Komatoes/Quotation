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
        .success-box {
            background-color: #d4edda;
            border: 2px solid #28a745;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            color: #155724;
        }
        .config-info {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
            text-align: left;
            font-size: 13px;
            color: #333;
            font-family: 'Courier New', monospace;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            color: #666;
            font-size: 12px;
            border-top: 1px solid #e3e6f0;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>📧 Email Configuration Test</h1>
            <p style="margin: 10px 0 0 0; font-size: 14px; opacity: 0.9;">Quotation Management System</p>
        </div>

        <!-- Content -->
        <div class="content">
            <p style="margin-top: 0;">Hello,</p>
            
            <p>This is a test email to verify your SMTP configuration is working correctly!</p>

            <div class="success-box">
                <strong>✅ Your email system is configured and working!</strong>
                <p style="margin: 10px 0 0 0;">If you're reading this, your SMTP settings are correct and emails are being sent successfully.</p>
            </div>

            <h3 style="margin-top: 30px; color: #333;">Configuration Details:</h3>
            <div class="config-info">
                <strong>Mail Configuration:</strong><br>
                Host: {{ $config['host'] }}<br>
                Port: {{ $config['port'] }}<br>
                Encryption: {{ $config['encryption'] }}<br>
                From: {{ $config['from'] }}<br>
                <br>
                <strong>Test Details:</strong><br>
                Test Recipient: {{ $email }}<br>
                Timestamp: {{ $timestamp }}
            </div>

            <p style="margin-top: 30px;">You can now use the password reset and OTP features with confidence!</p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p style="margin: 0; color: #999;">
                This is an automated test email. If you did not request this, you can safely ignore it.
            </p>
        </div>
    </div>
</body>
</html>
