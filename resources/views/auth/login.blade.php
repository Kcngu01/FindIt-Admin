<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>登入</title>
    <!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"> -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .login-container {
            max-width: 800px;
            margin: 50px auto;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }

        .login-row {
            display: flex;
            min-height: 500px;
        }

        .login-image-col {
            background-color: #f0f1f7;
            display: flex;
            align-items: center;
            justify-content: center;
            /* padding: 30px; */
        }

        .login-image {
            max-width: 100%;
            height: 100%;
        }

        .login-form-col {
            padding: 40px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .logo-container {
            text-align: center;
            /* margin-bottom: 30px; */
        }

        .welcome-text {
            margin-bottom: 30px;
            color: #666;
            text-align: center;
        }

        .form-group {
            margin-bottom: 20px;
            width: 100%;
        }

        .login-btn {
            background-color: #6c757d;
            border: none;
            width: 100px;
        }

        .login-btn:hover {
            background-color: #5a6268;
        }

        /* Add this to your CSS to remove the browser's default eye icon */
        input[type="password"]::-ms-reveal,
        input[type="password"]::-ms-clear,
        input[type="password"]::-webkit-contacts-auto-fill-button,
        input[type="password"]::-webkit-credentials-auto-fill-button {
            display: none !important;
        }
        
        </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <div class="login-row">
                <div class="col-md-5 login-image-col">
                    <img src="{{ asset('images/background.jpg') }}" alt="Login" class="login-image">
                </div>
                <div class="col-md-7 login-form-col">
                    <div class="logo-container">
                        <div class="logo">
                            <img src="{{asset('images/logo.png')}}" alt="logo" class= "logo-image">
                        </div>
                    </div>
                    
                    <p class="welcome-text">Welcome back! Log in to your account.</p>
                    
                    <form method="POST" action="{{ route('login') }}" class="w-100">
                        @csrf
                        
                        <div class="form-group">
                            <label for="email">Email address</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                id="email" name="email" value="{{ old('email') }}" required autofocus>
                            @error('email')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="password">Password</label>
                            <div class="input-group">
                                <input type="password" autocomplete="off" class="form-control @error('password') is-invalid @enderror" 
                                    id="password" name="password"  required>
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                            </div>
                            @error('password')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        
                        <div class="text-center">
                            <button type="submit" class="btn btn-secondary login-btn">Login</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            // Toggle eye icon
            this.innerHTML = type === 'password' ? '<i class="bi bi-eye"></i>' : '<i class="bi bi-eye-slash"></i>';
        });
    </script>
</body>
</html>