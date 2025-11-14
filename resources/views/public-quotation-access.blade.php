<!doctype html>
<html lang="en" class="layout-wide customizer-hide" dir="ltr" data-skin="default" data-assets-path="../../assets/"
    data-template="vertical-menu-template-no-customizer" data-bs-theme="light">
<head>
    <meta charset="utf-8" />
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <title>Access Quotation - Quotation System</title>
    <meta name="description" content="" />
    <link rel="icon" type="image/x-icon" href="../../assets/img/favicon/favicon.ico" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&ampdisplay=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <style>
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

        .access-container {
            width: 100%;
            max-width: 350px;
        }

        .access-card {
            background: rgba(255, 255, 255, 0.98);
            border-radius: 8px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 40px 30px;
            backdrop-filter: blur(10px);
        }

        .logo-section {
            text-align: center;
            margin-bottom: 25px;
        }

        .logo-section img {
            height: 70px;
            margin-bottom: 15px;
        }

        .access-card h2 {
            font-size: 1.5em;
            color: #07ad90;
            margin-bottom: 10px;
            text-align: center;
        }

        .access-card small {
            font-size: 0.85em;
            color: #999;
            display: block;
            text-align: center;
            margin-bottom: 30px;
        }

        .field-wrapper {
            margin-bottom: 22px;
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

        .field-wrapper input[type="text"] {
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

        .field-wrapper input[type="text"]:focus {
            outline: none;
            border-bottom-color: #42509e;
        }

        .field-wrapper input[type="text"]:focus + label,
        .field-wrapper input:not(:placeholder-shown) + label {
            top: -30%;
            color: #42509e;
            font-weight: 600;
        }

        .field-wrapper input[type="submit"] {
            cursor: pointer;
            width: 100%;
            background: linear-gradient(135deg, #07ad90, #05a882);
            line-height: 2.8em;
            color: white;
            border: none;
            border-radius: 4px;
            padding: 12px;
            font-weight: 600;
            font-size: 1em;
            transition: all 0.3s ease;
            margin-top: 20px;
        }

        .field-wrapper input[type="submit"]:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(7, 173, 144, 0.3);
        }

        .field-wrapper input[type="submit"]:active {
            transform: scale(0.98);
        }

        .alert {
            background-color: #ffebee;
            color: #c62828;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            font-size: 0.9em;
            border-left: 4px solid #c62828;
            animation: slideDown 0.3s ease;
        }

        .alert ul {
            margin: 0;
            padding-left: 20px;
        }

        .alert li {
            margin-top: 5px;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .info-text {
            text-align: center;
            font-size: 0.8em;
            color: #999;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }

        .info-text a {
            color: #07ad90;
            text-decoration: none;
            font-weight: 600;
        }

        .info-text a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <div class="access-container">
        <div class="access-card">
            <div class="logo-section">
                <img src="{{ asset('Image/LOGO.png') }}" alt="Logo">
            </div>
            <h2>Access Your Quotation</h2>
            <small>Enter your details to view the quotation</small>

            @if ($errors->any())
                <div class="alert">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('quotation.public.validate', ['token' => $token]) }}">
                @csrf
                <div class="field-wrapper">
                    <input type="text" name="first_name" placeholder="firstname" required autofocus>
                </div>
                <div class="field-wrapper">
                    <input type="text" name="last_name" placeholder="lastname" required>
                </div>
                <div class="field-wrapper">
                    <input type="text" name="phone_number" placeholder="phone" required>
                </div>
                <div class="field-wrapper">
                    <input type="submit" value="Access Quotation">
                </div>
            </form>

        </div>
    </div>
</body>
</html>
