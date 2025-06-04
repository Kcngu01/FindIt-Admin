@extends('layouts.auth')

@section('title', 'Admin Password Reset')
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Reset Admin Password') }}</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('admin.password.update') }}" id="password-reset-form">
                        @csrf

                        <input type="hidden" name="id" value="{{ $id }}">
                        <input type="hidden" name="hash" value="{{ $hash }}">
                        <input type="hidden" name="token" value="{{ $token }}">

                        <div class="mb-3 row">
                            <label for="email" class="col-md-4 col-form-label text-md-end">{{ __('Email Address') }}</label>

                            <div class="col-md-6">
                                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ $email ?? old('email') }}" required autocomplete="email" autofocus readonly>

                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <label for="password" class="col-md-4 col-form-label text-md-end">{{ __('Password') }}</label>

                            <div class="col-md-6">
                                <div class="input-group">
                                    <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="new-password">
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-outline-secondary" id="togglePassword">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                <div id="password-requirements" class="form-text mt-1">
                                    Password must contain at least 8 characters, including uppercase, lowercase, number, and special character.
                                </div>
                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <label for="password-confirm" class="col-md-4 col-form-label text-md-end">{{ __('Confirm Password') }}</label>

                            <div class="col-md-6">
                                <div class="input-group">
                                    <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required autocomplete="new-password">
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-outline-secondary" id="togglePasswordConfirm">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                <div id="password-mismatch" class="text-danger mt-1" style="display: none;">
                                    Passwords do not match.
                                </div>
                            </div>
                        </div>

                        <div class="mb-0 row">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-primary" id="submit-btn">
                                    {{ __('Reset Password') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle password visibility
    document.getElementById('togglePassword').addEventListener('click', function() {
        const passwordInput = document.getElementById('password');
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        
        // Toggle eye icon
        this.innerHTML = type === 'password' ? '<i class="bi bi-eye"></i>' : '<i class="bi bi-eye-slash"></i>';
    });

    document.getElementById('togglePasswordConfirm').addEventListener('click', function() {
        const passwordInput = document.getElementById('password-confirm');
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        
        // Toggle eye icon
        this.innerHTML = type === 'password' ? '<i class="bi bi-eye"></i>' : '<i class="bi bi-eye-slash"></i>';
    });
    
    // Password validation
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('password-confirm');
    const mismatchElement = document.getElementById('password-mismatch');
    const submitBtn = document.getElementById('submit-btn');
    const form = document.getElementById('password-reset-form');
    
    // Check password match
    function checkPasswordMatch() {
        if (confirmPasswordInput.value && passwordInput.value !== confirmPasswordInput.value) {
            mismatchElement.style.display = 'block';
            return false;
        } else {
            mismatchElement.style.display = 'none';
            return true;
        }
    }
    
    // Check password strength
    function checkPasswordStrength(password) {
        const hasUpperCase = /[A-Z]/.test(password);
        const hasLowerCase = /[a-z]/.test(password);
        const hasNumber = /[0-9]/.test(password);
        const hasSpecialChar = /[^a-zA-Z0-9]/.test(password);
        const isLongEnough = password.length >= 8;
        
        return isLongEnough && hasUpperCase && hasLowerCase && hasNumber && hasSpecialChar;
    }
    
    // Update validation on input
    passwordInput.addEventListener('input', function() {
        const password = this.value;
        const isStrong = checkPasswordStrength(password);
        
        if (!isStrong) {
            this.setCustomValidity('Password must be at least 8 characters long and include uppercase, lowercase, number, and special character.');
        } else {
            this.setCustomValidity('');
        }
        
        checkPasswordMatch();
    });
    
    confirmPasswordInput.addEventListener('input', checkPasswordMatch);
    
    // Form submission validation
    form.addEventListener('submit', function(e) {
        const password = passwordInput.value;
        const isStrong = checkPasswordStrength(password);
        const passwordsMatch = checkPasswordMatch();
        
        if (!isStrong || !passwordsMatch) {
            e.preventDefault();
            
            if (!isStrong) {
                alert('Password must be at least 8 characters long and include uppercase, lowercase, number, and special character.');
            } else if (!passwordsMatch) {
                alert('Passwords do not match.');
            }
        }
    });
});
</script>
@endsection 