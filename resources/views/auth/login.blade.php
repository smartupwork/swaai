<!DOCTYPE html>
<html lang="en">

<head>
    
    <meta charset="UTF-8">
    <link rel="shortcut icon" href="{{ url('/public/assets/images/favicon.ico') }}">
    <title>Login Form</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #fff;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .login-container {
            text-align: center;
            width: 100%;
            max-width: 500px;
            padding: 40px;
        }

        .logo {
            width: 120px;
            margin-bottom: 10px;
        }

        .subtitle {
            font-size: 18px;
            color: #333;
            margin-bottom: 30px;
        }

        label {
            display: block;
            text-align: left;
            font-weight: 600;
            margin-bottom: 8px;
            margin-top: 20px;
            font-size: 18px;
        }

        .input-group {
            position: relative;
            width: 100%;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 12px 20px;
            border: 1px solid #ccc;
            border-radius: 9999px;
            font-size: 16px;
            box-sizing: border-box;
            outline: none;
        }

        .toggle-password {
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            width: 24px;
            height: 24px;
        }

        .btn-submit {
            margin-top: 30px;
            padding: 12px 30px;
            font-size: 18px;
            font-weight: bold;
            background-color: #08A5F4;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: 0.3s;
        }

        .btn-submit:hover {
            background-color: #0e2a52;
        }
    </style>
</head>

<body>

    <div class="login-container">
        @if (!empty($settings) && !empty($settings->logo) && file_exists(public_path('media/setting/' . $settings->logo)))
            <img src="{{ asset('public/media/setting/' . $settings->logo) }}" alt="Swaai Logo" class="logo">
        @else
            <img src="{{ asset('public/media/web-hero2.png') }}" alt="Swaai Logo" class="logo">
        @endif
        <div class="subtitle"><strong>Admin Dashboard</strong></div>

        <form method="POST" action="{{ route('login.submit') }}">
            @csrf

            <label for="username">Email</label>
            <input type="text" id="username" name="email" placeholder="Enter your email" required>
            @error('email')
                <span class="text-danger">{{ $message }}</span>
            @enderror

            <label for="password">Password</label>
            <div class="input-group">
                <input type="password" id="password" name="password" placeholder="Enter your password" required>
                @error('password')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
                <svg id="togglePassword" class="toggle-password" xmlns="http://www.w3.org/2000/svg" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
            </div>

            <button type="submit" class="btn-submit">Login</button>
        </form>
    </div>

    <script>
        const togglePassword = document.getElementById("togglePassword");
        const passwordInput = document.getElementById("password");

        const showIcon = `
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
    `;

        const hideIcon = `
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
        d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a10.05 10.05 0 013.128-4.362m2.254-1.354A9.953 9.953 0 0112 5c4.477 0 8.268 2.943 9.542 7a9.967 9.967 0 01-1.357 2.572M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
        d="M3 3l18 18" />
    `;

        let isPasswordVisible = false;

        togglePassword.addEventListener("click", () => {
            isPasswordVisible = !isPasswordVisible;
            passwordInput.type = isPasswordVisible ? "text" : "password";
            togglePassword.innerHTML = isPasswordVisible ? hideIcon : showIcon;
        });
    </script>

</body>

</html>
