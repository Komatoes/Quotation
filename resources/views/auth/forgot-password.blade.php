<!doctype html>
<html lang="en" class="layout-wide customizer-hide" dir="ltr" data-skin="default" data-assets-path="../../assets/"
    data-template="vertical-menu-template-no-customizer" data-bs-theme="light">

<head>
    <meta charset="utf-8" />
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <title>Forgot Password - Quotation System</title>

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700&display=swap"
        rel="stylesheet" />

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

    <style>
        /* -----------------------------------------------------
           CORE
        ----------------------------------------------------- */
        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Public Sans', Arial, sans-serif;
            font-size: 14px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 20px;
        }

        /* -----------------------------------------------------
           CONTAINER
        ----------------------------------------------------- */
        .login-container {
            width: 100%;
            max-width: 350px;
        }

        .rec-prism {
            width: 100%;
            height: auto;
            position: relative;
            transform-style: preserve-3d;
            transition: transform 0.6s ease-in-out;
        }

        .face {
            position: relative;
            width: 100%;
            padding: 30px;
            background: rgba(255, 255, 255, 0.98);
            border-radius: 10px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.25);
            -webkit-font-smoothing: antialiased;
        }

        /* -----------------------------------------------------
           CONTENT
        ----------------------------------------------------- */
        .content {
            color: #666;
        }

        .content h2 {
            font-size: 1.55em;
            margin-bottom: 10px;
            text-align: center;
            color: #07ad90;
            font-weight: 700;
        }

        .content small {
            display: block;
            font-size: 0.85em;
            text-align: center;
            margin-bottom: 20px;
            color: #888;
        }

        .logo-section {
            text-align: center;
            margin-bottom: 20px;
        }

        .logo-section img {
            height: 60px;
        }

        /* -----------------------------------------------------
           INPUTS
        ----------------------------------------------------- */
        .field-wrapper {
            margin-bottom: 20px;
        }

        .field-wrapper input[type="text"],
        .field-wrapper input[type="email"],
        .field-wrapper input[type="password"] {
            width: 100%;
            border: none;
            background: transparent;
            border-bottom: 2px solid #07ad90;
            padding: 8px 2px;
            font-size: 1em;
            transition: border-color 0.3s ease;
        }

        .field-wrapper input:focus {
            outline: none;
            border-bottom-color: #42509e;
        }

        .field-wrapper input[type="submit"] {
            cursor: pointer;
            width: 100%;
            background: linear-gradient(135deg, #07ad90, #05a882);
            line-height: 2.5em;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 10px;
            font-weight: 600;
            font-size: 1em;
            transition: 0.3s ease;
            margin-top: 10px;
        }

        .field-wrapper input[type="submit"]:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 18px rgba(7, 173, 144, 0.35);
        }

        /* -----------------------------------------------------
           LINKS
        ----------------------------------------------------- */
        .links {
            text-align: center;
            margin-top: 15px;
        }

        .link-item {
            cursor: pointer;
            color: #42509e;
            font-size: 0.85em;
            text-decoration: none;
            transition: 0.3s;
        }

        .link-item:hover {
            color: #07ad90;
        }

        /* -----------------------------------------------------
           ALERT
        ----------------------------------------------------- */
        .alert {
            background-color: #ffebee;
            color: #c62828;
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 15px;
            border-left: 4px solid #c62828;
            font-size: 0.9em;
        }

        .alert-success {
            background-color: #e8f5e9;
            color: #2e7d32;
            border-left-color: #2e7d32;
        }

        .alert ul {
            margin: 0;
            padding-left: 20px;
        }

        .alert li {
            margin: 5px 0;
        }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="rec-prism" id="prism">

            {{-- ==================== FORGOT PASSWORD FACE ==================== --}}
            <div class="face face-front">
                <div class="content">

                    <div class="logo-section">
                        <img src="{{ asset('Image/LOGO.png') }}" alt="Logo">
                    </div>

                    <h2>Forgot Password</h2>
                    <small>Enter your email to receive an OTP</small>

                    @if ($errors->any())
                        <div class="alert">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if (session('status'))
                        <div class="alert alert-success">
                            {{ session('status') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('send.otp') }}">
                        @csrf

                        <div class="field-wrapper">
                            <input type="email" name="email" placeholder="email" value="{{ old('email') }}" required autofocus>
                        </div>

                        <div class="field-wrapper">
                            <input type="submit" value="Send OTP">
                        </div>
                    </form>

                    <div class="links" style="margin-top: 20px;">
                        <a href="{{ route('login') }}" class="link-item">← Back to Login</a>
                    </div>
                </div>
            </div>

        </div>
    </div>

</body>

</html>