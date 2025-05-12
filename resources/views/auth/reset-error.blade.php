<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Link Error - {{ config('app.name') }}</title>
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
        .error-container {
            max-width: 500px;
            margin: 2rem auto;
            padding: 2rem;
            background-color: #ffffff;
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        .logo-container {
            text-align: center;
            /* margin-bottom: 1rem; */
        }

        .login-image {
            max-width: 50%;
            height: 50%;
        }

        .app-name {
            font-size: 1.5rem;
            margin-top: 1rem;
            font-weight: 600;
        }
        .error-icon {
            font-size: 4rem;
            color: #dc3545;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="logo-container">
            <!-- Logo placeholder - replace with your logo -->
            <!-- <div class="app-name">{{ config('app.name', 'FindIt') }}</div> -->
            <img src="{{asset('images/logo.png')}}" alt="logo" class="login-image">
        </div>

        <div class="error-icon">
            <i class="bi bi-exclamation-circle"></i>
        </div>
        
        <h2 class="mb-3">Password Reset Link Invalid</h2>
        
        <div class="alert alert-danger">
            {{ $error }}
        </div>
        
        <p class="mb-4">You need a fresh reset link to continue.</p>
        
        <!-- <div class="d-grid mb-3">
            <a href="{{ route('password.request') }}" class="btn btn-primary">Request New Reset Link</a>
        </div> -->
        
    </div>
</body>
</html> 