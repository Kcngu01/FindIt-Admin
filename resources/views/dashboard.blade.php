@extends('layouts.app')

@section('content')
<h1 class="mb-4">Dashboard</h1>
    
    <h2 class="mb-3">Overview</h2>
    
    <div class="row g-4">
        <!-- Registered Students Card -->
        <div class="col-md-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2 text-muted">Registered Students</h6>
                    <h3 class="card-title">Value</h3>
                </div>
            </div>
        </div>

        <!-- Pending Claims Card -->
        <div class="col-md-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2 text-muted">Pending Claims</h6>
                    <h3 class="card-title">Value</h3>
                </div>
            </div>
        </div>

        <!-- Claimed Card -->
        <div class="col-md-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2 text-muted">Claimed</h6>
                    <h3 class="card-title">Value</h3>
                </div>
            </div>
        </div>

        <!-- Recovery Rate Card -->
        <div class="col-md-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2 text-muted">Recovery Rate</h6>
                    <h3 class="card-title">Value</h3>
                </div>
            </div>
        </div>
    </div>
@endsection

