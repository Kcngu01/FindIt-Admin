<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Forgot Password</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
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
        .forgot-container {
            max-width: 500px;
            margin: 2rem auto;
            padding: 2rem;
            background-color: #ffffff;
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .logo-container {
            text-align: center;
            margin-bottom: 2rem;
        }
        .app-name {
            font-size: 1.5rem;
            margin-top: 1rem;
            font-weight: 600;
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
    </style>
</head>
<body>
    <div class="forgot-container">
        <div class="logo-container">
            <!-- Logo placeholder - replace with your logo -->
            <div class="app-name">{{ config('app.name', 'FindIt') }}</div>
        </div>

        <h2 class="text-center mb-4">Forgot Password</h2>
        <p class="text-center text-muted mb-4">Enter your email address and we'll send you a link to reset your password.</p>

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

        <form method="POST" action="{{ route('password.email') }}">
            @csrf
            <div class="form-group">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" name="email" id="email" class="form-control" value="{{ old('email') }}" required autofocus>
            </div>

            <div class="d-grid mb-3">
                <button type="submit" class="btn btn-primary">Send Password Reset Link</button>
            </div>

            <div class="text-center">
                <a href="{{ route('login') }}" class="text-decoration-none">Back to Login</a>
            </div>
        </form>
    </div>
</body>
</html> 