<!doctype html>
<html lang="en" class="layout-wide customizer-hide" dir="ltr" data-skin="default" data-assets-path="../../assets/"
    data-template="vertical-menu-template-no-customizer" data-bs-theme="light">
<head>
    <meta charset="utf-8" />
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <title>Login - Quotation System</title>
    <meta name="description" content="" />
    <link rel="icon" type="image/x-icon" href="../../assets/img/favicon/favicon.ico" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&ampdisplay=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <style>
        $prism-height: 400px;
        $prism-length: 300px;
        $prism-depth: $prism-length;
        $spacing: 20px;
        $br: 3px;
        $text-light: #fff;
        $text-dark: #666;
        $blue: #03a9f4;
        $smoke: #f9f9fa;
        $coral: #ff5751;
        $navy-blue: #42509e;
        $green: #07ad90;

        * {
            box-sizing: border-box;
        }

        body {
            font-family: Tahoma, Verdana, Segoe, sans-serif;
            font-size: 14px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 20px;
        }

        .login-container {
            width: 100%;
            max-width: 350px;
            perspective: 600px;
        }

        .rec-prism {
            width: 100%;
            position: relative;
            transform-style: preserve-3d;
            transform: translateZ(-100px);
            transition: transform 0.5s ease-in;
            height: 450px;
        }

        .face {
            position: absolute;
            width: 300px;
            height: 450px;
            padding: 30px;
            background: rgba(255, 255, 255, 0.98);
            border-radius: 8px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            backface-visibility: hidden;
        }

        .face.face-front {
            transform: rotateY(0deg) translateZ(150px);
        }

        .face.face-back {
            transform: rotateY(180deg) translateZ(150px);
        }

        .content {
            color: #666;
            text-align: left;
        }

        .content h2 {
            font-size: 1.5em;
            color: #07ad90;
            margin-bottom: 10px;
            text-align: center;
        }

        .content small {
            font-size: 0.85em;
            color: #999;
            display: block;
            text-align: center;
            margin-bottom: 25px;
        }

        .logo-section {
            text-align: center;
            margin-bottom: 20px;
        }

        .logo-section img {
            height: 60px;
            margin-bottom: 10px;
        }

        .field-wrapper {
            margin-bottom: 20px;
            position: relative;
        }

        .field-wrapper label {
            position: absolute;
            pointer-events: none;
            font-size: 0.85em;
            top: 40%;
            left: 0;
            transform: translateY(-50%);
            transition: all ease-in 0.25s;
            color: #999;
        }

        .field-wrapper input[type="text"],
        .field-wrapper input[type="password"],
        .field-wrapper textarea {
            width: 100%;
            border: none;
            background: transparent;
            line-height: 2em;
            border-bottom: 2px solid #07ad90;
            color: #666;
            font-size: 1em;
            padding: 5px 0;
            transition: border-color 0.3s ease;
        }

        .field-wrapper input[type="text"]:focus,
        .field-wrapper input[type="password"]:focus,
        .field-wrapper textarea:focus {
            outline: none;
            border-bottom-color: #42509e;
        }

        .field-wrapper input[type="text"]:focus + label,
        .field-wrapper input[type="password"]:focus + label,
        .field-wrapper textarea:focus + label,
        .field-wrapper input:not(:placeholder-shown) + label {
            top: -30%;
            color: #42509e;
            font-weight: 600;
        }

        .field-wrapper input[type="submit"] {
            cursor: pointer;
            width: 100%;
            background: linear-gradient(135deg, #07ad90, #05a882);
            line-height: 2.5em;
            color: white;
            border: none;
            border-radius: 4px;
            padding: 10px;
            font-weight: 600;
            font-size: 1em;
            transition: all 0.3s ease;
            margin-top: 15px;
        }

        .field-wrapper input[type="submit"]:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(7, 173, 144, 0.3);
        }

        .field-wrapper input[type="submit"]:active {
            transform: scale(0.98);
        }

        .links {
            margin-top: 20px;
            text-align: center;
        }

        .link-item {
            display: inline-block;
            margin: 0 5px;
            font-size: 0.8em;
            color: #42509e;
            cursor: pointer;
            text-decoration: none;
            transition: color 0.3s ease;
            padding: 5px 10px;
            border-bottom: 1px solid transparent;
        }

        .link-item:hover {
            color: #07ad90;
            border-bottom-color: #07ad90;
        }

        .link-item a {
            color: inherit;
            text-decoration: none;
        }

        .alert {
            background-color: #ffebee;
            color: #c62828;
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 15px;
            font-size: 0.9em;
            border-left: 4px solid #c62828;
        }

        .alert ul {
            margin: 0;
            padding-left: 20px;
        }

        .nav-links {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #eee;
        }

        .nav-link-btn {
            background: none;
            border: none;
            color: #42509e;
            cursor: pointer;
            font-size: 0.9em;
            margin: 0 8px;
            padding: 5px 10px;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .nav-link-btn:hover {
            color: #07ad90;
            font-weight: 600;
        }

        .nav-link-btn.active {
            color: #07ad90;
            font-weight: 700;
            border-bottom: 2px solid #07ad90;
        }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="rec-prism">
            <!-- Login Form -->
            <div class="face face-front">
                <div class="content">
                    <div class="logo-section">
                        <img src="{{ asset('Image/LOGO.png') }}" alt="Logo">
                    </div>
                    <h2>Welcome Back</h2>
                    <small>Sign in to your account</small>

                    @if ($errors->any())
                        <div class="alert">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login.submit') }}">
                        @csrf
                        <div class="field-wrapper">
                            <input type="text" name="username" placeholder="username" required autofocus>
                        </div>
                        <div class="field-wrapper">
                            <input type="password" name="password" placeholder="password" required>
                        </div>
                        <div class="field-wrapper">
                            <input type="submit" value="Sign In">
                        </div>
                    </form>

                    <div class="links">
                        <div style="margin: 15px 0;">
                            <a href="{{ route('password.request') }}" class="link-item">Forgot Password?</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Forgot Password Form -->
            <div class="face face-back">
                <div class="content">
                    <h2>Reset Password</h2>
                    <small>Enter your email to reset your password</small>

                    <form method="POST" action="{{ route('password.email') }}">
                        @csrf
                        <div class="field-wrapper">
                            <input type="text" name="email" placeholder="email" required>
                        </div>
                        <div class="field-wrapper">
                            <input type="submit" value="Send Reset Link">
                        </div>
                    </form>

                    <div class="links" style="margin-top: 30px;">
                        <button class="nav-link-btn" onclick="rotateForm(0)">← Back to Login</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const prism = document.querySelector('.rec-prism');

        function rotateForm(angle) {
            prism.style.transform = `translateZ(-100px) rotateY(${angle}deg)`;
        }

        // Forgot password link
        document.querySelectorAll('.link-item').forEach(link => {
            if (link.textContent.includes('Forgot')) {
                link.onclick = (e) => {
                    e.preventDefault();
                    rotateForm(180);
                };
            }
        });
    </script>
</body>
</html>