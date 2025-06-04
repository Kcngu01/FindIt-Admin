<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Your Password - {{ config('app.name') }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f8f9fa;
            color: #212529;
            line-height: 1.5;
            padding: 20px;
        }
        .reset-container {
            max-width: 500px;
            margin: 2rem auto;
            padding: 2rem;
            background-color: #ffffff;
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .login-image {
            max-width: 100%;
            height: 100%;
        }
        .logo-container {
            text-align: center;
            margin-bottom: 2rem;
        }
        .logo {
            max-width: 150px;
            height: auto;
        }
        .app-name {
            font-size: 1.5rem;
            margin-top: 1rem;
            font-weight: 600;
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .success-container {
            text-align: center;
            padding: 2rem;
        }
        .success-icon {
            font-size: 4rem;
            color: #198754;
            margin-bottom: 1rem;
        }
        .btn-app {
            background-color: #0d6efd;
            color: #ffffff;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 0.25rem;
            text-decoration: none;
            font-weight: 500;
            margin-top: 1.5rem;
            display: inline-block;
        }
        .btn-app:hover {
            background-color: #0b5ed7;
            color: #ffffff;
        }
        /* Add this to remove the browser's default eye icon */
        input[type="password"]::-ms-reveal,
        input[type="password"]::-ms-clear,
        input[type="password"]::-webkit-contacts-auto-fill-button,
        input[type="password"]::-webkit-credentials-auto-fill-button {
            display: none !important;
        }
        .password-mismatch {
            color: #dc3545;
            font-size: 0.875rem;
            margin-top: 0.25rem;
            display: none;
        }
    </style>
</head>
<body>
    <div class="reset-container">
        <div class="logo-container">
            <!-- Logo placeholder - replace with your logo -->
            <div class="app-name">{{ config('app.name', 'FindIt') }}</div>
            <img src="{{asset('images/logo.png')}}" alt="logo" class="login-image">
        </div>

        <div id="reset-form">
            <h2 class="text-center mb-4">Reset Your Password</h2>
            <p class="text-center text-muted mb-4">Enter a new password for your account</p>

            @if(session('status'))
                <div class="alert alert-success mb-4">
                    {{ session('status') }}
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger mb-4">
                    {{ session('error') }}
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger mb-4">
                    <ul class="mb-0 list-unstyled">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form id="password-reset-form" method="POST" action="{{ route('password.update') }}" onsubmit="return validateForm()">
                @csrf
                <!-- Hidden inputs for token and email -->
                <input type="hidden" id="id" name="id" value="{{ $id }}">
                <input type="hidden" id="hash" name="hash" value="{{ $hash }}">
                <input type="hidden" id="email" name="email" value="{{ $email }}">
                <input type="hidden" id="token" name="token" value="{{ $token }}">

                <div class="form-group">
                    <label for="password" class="form-label">New Password</label>
                    <div class="input-group">
                        <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="off" minlength="8">
                        <div class="input-group-append">
                            <button type="button" class="btn btn-outline-secondary" id="togglePassword">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div id="password-error" class="invalid-feedback"></div>
                    <div class="form-text">Password must contain at least 8 characters, including uppercase, lowercase, number, and special character.</div>
                </div>

                <div class="form-group">
                    <label for="password_confirmation" class="form-label">Confirm Password</label>
                    <div class="input-group">
                        <input type="password" id="password_confirmation" name="password_confirmation" autocomplete="off" class="form-control" required minlength="8">
                        <div class="input-group-append">
                            <button type="button" class="btn btn-outline-secondary" id="togglePasswordConfirmation">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div id="password-confirmation-error" class="invalid-feedback"></div>
                    <div id="password-mismatch" class="password-mismatch">Passwords do not match</div>
                </div>

                <div class="d-grid">
                    <button type="submit" id="submit-btn" class="btn btn-primary btn-lg">
                        Reset Password
                    </button>
                </div>
            </form>
        </div>

        <div id="success-message" class="success-container" style="display: none;">
            <div class="success-icon">
                <i class="bi bi-check-circle-fill"></i>
            </div>
            <h2 class="mb-3">Password Reset Successfully!</h2>
            <p class="text-muted mb-4">Your password has been reset successfully. You can now log in to the app with your new password.</p>
            <a href="findit://" class="btn-app">Open App</a>
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

        document.getElementById('togglePasswordConfirmation').addEventListener('click', function() {
            const passwordInput = document.getElementById('password_confirmation');
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            // Toggle eye icon
            this.innerHTML = type === 'password' ? '<i class="bi bi-eye"></i>' : '<i class="bi bi-eye-slash"></i>';
        });
        
        // Check if the reset was successful
        if (document.querySelector('.alert-success')) {
            document.getElementById('reset-form').style.display = 'none';
            document.getElementById('success-message').style.display = 'block';
        }

        // Real-time password validation
        document.getElementById('password_confirmation').addEventListener('input', checkPasswordMatch);
        document.getElementById('password').addEventListener('input', function() {
            checkPasswordMatch();
            checkPasswordStrength(this.value);
        });

        function checkPasswordMatch() {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('password_confirmation').value;
            const mismatchElement = document.getElementById('password-mismatch');
            
            if (confirmPassword && password !== confirmPassword) {
                mismatchElement.style.display = 'block';
            } else {
                mismatchElement.style.display = 'none';
            }
        }
        
        // Check password strength
        function checkPasswordStrength(password) {
            const hasUpperCase = /[A-Z]/.test(password);
            const hasLowerCase = /[a-z]/.test(password);
            const hasNumber = /[0-9]/.test(password);
            const hasSpecialChar = /[^a-zA-Z0-9]/.test(password);
            const isLongEnough = password.length >= 8;
            
            const passwordInput = document.getElementById('password');
            
            if (!isLongEnough || !hasUpperCase || !hasLowerCase || !hasNumber || !hasSpecialChar) {
                passwordInput.setCustomValidity('Password must be at least 8 characters long and include uppercase, lowercase, number, and special character.');
            } else {
                passwordInput.setCustomValidity('');
            }
            
            return isLongEnough && hasUpperCase && hasLowerCase && hasNumber && hasSpecialChar;
        }

        function validateForm() {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('password_confirmation').value;
            const isStrong = checkPasswordStrength(password);
            
            if (password !== confirmPassword) {
                document.getElementById('password-mismatch').style.display = 'block';
                return false;
            }
            
            if (!isStrong) {
                alert('Password must be at least 8 characters long and include uppercase, lowercase, number, and special character.');
                return false;
            }
            
            return true;
        }
    </script>
</body>
</html>