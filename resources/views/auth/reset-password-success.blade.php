@extends('layouts.auth')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow border-0 rounded-lg">
                <div class="card-body p-5 text-center">
                    <div class="success-icon mb-4">
                        <!-- Primary icon with Bootstrap Icons -->
                        <div class="circle-icon bg-success d-flex align-items-center justify-content-center mx-auto">
                            <i class="bi bi-check-circle-fill text-white" style="font-size: 2.5rem;"></i>
                            <!-- Fallback icon (will be hidden if Bootstrap Icons works) -->
                            <span class="fallback-icon text-white d-none">✓</span>
                        </div>
                    </div>
                    
                    <h3 class="text-success fw-bold mb-3">Password Reset Successful!</h3>
                    
                    <p class="text-muted mb-4">Your password has been reset successfully. You can now log in using your email and new password.</p>
                    
                    <div class="email-box p-3 mb-4 bg-light rounded">
                        <p class="mb-0"><strong>Email:</strong> <span class="text-primary">{{ $email }}</span></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .circle-icon {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        position: relative;
    }
    
    .circle-icon i {
        font-size: 2.5rem;
    }
    
    .fallback-icon {
        font-size: 2.5rem;
        font-weight: bold;
    }
    
    .card {
        transition: all 0.3s ease;
    }
    
    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 30px rgba(0,0,0,0.1) !important;
    }
    
    .email-box {
        border-left: 4px solid #0d6efd;
    }
</style>

<script>
    // Check if Bootstrap Icons is loaded correctly
    document.addEventListener('DOMContentLoaded', function() {
        // Get computed style of the icon
        var iconElement = document.querySelector('.bi-check-circle-fill');
        var computedStyle = window.getComputedStyle(iconElement, ':before');
        
        // If the icon's content is empty, Bootstrap Icons is not working
        if (!computedStyle.content || computedStyle.content === 'none' || computedStyle.content === '') {
            // Hide the Bootstrap icon
            iconElement.style.display = 'none';
            
            // Show the fallback icon
            document.querySelector('.fallback-icon').classList.remove('d-none');
        }
    });
</script>
@endsection 