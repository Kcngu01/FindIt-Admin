@extends('layouts.auth')

@section('title', 'Password Reset Success')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-success text-white">{{ __('Password Reset Successful') }}</div>

                <div class="card-body text-center">
                    <div class="mb-4">
                        <i class="bi bi-check-circle-fill text-success" style="font-size: 3rem;"></i>
                    </div>
                    
                    <h4 class="mb-4">Your password has been reset successfully!</h4>
                    
                    <p class="mb-4">You can now log in using your email and new password.</p>
                    
                    <div class="mb-3">
                        <p><strong>Email:</strong> {{ $email }}</p>
                    </div>
                    
                    <div class="d-grid gap-2 col-6 mx-auto">
                        <a href="{{ route('login') }}" class="btn btn-primary">
                            {{ __('Go to Login Page') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 