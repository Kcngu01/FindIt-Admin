@extends('layouts.app')

@section('title', 'Dashboard')
@section('content')
<h1 class="mb-4">Dashboard</h1>
    
    <h2 class="mb-3">Overview</h2>
    
    <!-- Registered Students Card (Moved above filter) -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2 text-muted">Registered Students (All Faculty)</h6>
                    <div style="height: 180px; position: relative;">
                        <canvas id="registeredStudentsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Faculty Filter Dropdown -->
    <div class="row mb-4">
        <div class="col-md-4">
            <label for="faculty_id" class="form-label">Filter by Faculty:</label>
            <select class="form-select" id="faculty_id" name="faculty_id" aria-label="Filter by Faculty">
                <option value="">All Faculties</option>
                @foreach($faculties as $faculty)
                    <option value="{{ $faculty->id }}" {{ request('faculty_id') == $faculty->id ? 'selected' : '' }}>
                        {{ $faculty->name }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>
    
    <!-- Charts that can be filtered by faculty -->
    <div class="row g-4 mb-5">
        <!-- Data container (hidden) -->
        <!-- json_encode() function here converts the PHP $dashboardData array into a JSON string. This makes the data available to JavaScript on the client side via the HTML data attribute (data-dashboard). It's necessary because JavaScript can't directly read PHP variables - the data needs to be embedded in the HTML in a format JavaScript can parse. -->
        <div id="dashboard-data" 
             data-dashboard="{{ json_encode($dashboardData ?? []) }}"
             style="display: none;"></div>

        <!-- Pending Claims Card -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2 text-muted">
                        Pending Claims
                        <!-- @if(request('faculty_id'))
                            <span class="badge bg-info">Filtered</span>
                        @endif -->
                    </h6>
                    <div style="height: 180px; position: relative;">
                        <canvas id="pendingClaimsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Claimed Card -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2 text-muted">
                        Approved Claims
                        <!-- @if(request('faculty_id'))
                            <span class="badge bg-info">Filtered</span>
                        @endif -->
                    </h6>
                    <div style="height: 180px; position: relative;">
                        <canvas id="claimedChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recovery Rate Card -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2 text-muted">
                        Recovery Rate
                        <!-- @if(request('faculty_id'))
                            <span class="badge bg-info">Filtered</span>
                        @endif -->
                        <i class="bi bi-info-circle-fill text-muted ms-1" 
                           data-bs-toggle="tooltip" 
                           data-bs-placement="top" 
                           title="Recovery Rate = (Approved Claims ÷ Total Claims) × 100%"></i>
                    </h6>
                    <div style="height: 180px; position: relative;">
                        <canvas id="recoveryRateChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Store the registered students chart instance to prevent re-animation
    let registeredStudentsChart = null;
    
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize all tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
    
        // Get data from HTML data attribute
        const dashboardDataStr = document.getElementById('dashboard-data').dataset.dashboard;
        // JSON.parse() converts the JSON string back into a JavaScript object that can be directly manipulated. The data-dashboard attribute contains a string representation of the data, but JavaScript needs an actual object to work with the properties (like accessing data.pendingClaims or data.recoveryRate). JSON.parse() performs this string-to-object conversion, making the data usable for the chart rendering code.
        const dashboardData = JSON.parse(dashboardDataStr || '{}');
        
        // Extract values and continue with the existing code
        const registeredStudents = Math.round(dashboardData.registeredStudents || 0);
        const pendingClaims = Math.round(dashboardData.pendingClaims || 0);
        const claimedItems = Math.round(dashboardData.claimedItems || 0);
        const recoveryRate = Math.round(dashboardData.recoveryRate || 0);
        
        // Monthly claimed data for trend chart - ensure all values are integers
        const monthlyClaimedData = (dashboardData.monthlyClaimedData || [0, 0, 0]).map(val => Math.round(val));
        const monthLabels = dashboardData.monthLabels || ['Jan', 'Feb', 'Mar'];
        
        // Common chart options
        const commonOptions = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            }
        };
        
        // Integer formatter for all charts
        const integerFormatter = (value) => {
            if (typeof value === 'number') {
                return Math.round(value);
            }
            return value;
        };
        
        // Registered Students Chart (Doughnut Chart)
        // Only create if it doesn't exist yet
        if (!registeredStudentsChart) {
            registeredStudentsChart = new Chart(document.getElementById('registeredStudentsChart'), {
                type: 'doughnut',
                data: {
                    labels: ['Registered'],
                    datasets: [{
                        data: [registeredStudents, 0],
                        backgroundColor: ['#4e73df', '#f8f9fc'],
                        hoverBackgroundColor: ['#2e59d9', '#f8f9fc'],
                        hoverBorderColor: "rgba(234, 236, 244, 1)",
                    }]
                },
                options: {
                    ...commonOptions,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return integerFormatter(registeredStudents);
                                }
                            }
                        }
                    },
                    cutout: '70%',
                    animation: {
                        duration: sessionStorage.getItem('dashboardLoaded') ? 0 : 1000
                    }
                },
                plugins: [{
                    id: 'centerText',
                    afterDraw: function(chart) {
                        const width = chart.width;
                        const height = chart.height;
                        const ctx = chart.ctx;

                        ctx.restore();
                        const fontSize = (height / 114).toFixed(2);
                        ctx.font = fontSize + 'em sans-serif';
                        ctx.textBaseline = 'middle';
                        ctx.fillStyle = '#000';

                        const text = registeredStudents.toString();
                        const textX = Math.round((width - ctx.measureText(text).width) / 2);
                        const textY = height / 2;

                        ctx.fillText(text, textX, textY);
                        ctx.save();
                    }
                }]
            });
            
            // Mark that dashboard has been loaded once
            sessionStorage.setItem('dashboardLoaded', 'true');
        }

        // Pending Claims Chart (Radial Gauge Chart) - better than bar chart for single value
        new Chart(document.getElementById('pendingClaimsChart'), {
            type: 'doughnut',
            data: {
                labels: ['Pending'],
                datasets: [{
                    data: [pendingClaims, pendingClaims > 0 ? 0 : 1], // Show empty chart if 0
                    backgroundColor: ['#f6c23e', '#f8f9fc'],
                    hoverBackgroundColor: ['#e6b226', '#f8f9fc'],
                    hoverBorderColor: "rgba(234, 236, 244, 1)",
                    borderWidth: 0
                }]
            },
            options: {
                ...commonOptions,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return integerFormatter(pendingClaims);
                            }
                        }
                    }
                },
                cutout: '70%'
            },
            plugins: [{
                id: 'pendingClaimsText',
                afterDraw: function(chart) {
                    const width = chart.width;
                    const height = chart.height;
                    const ctx = chart.ctx;

                    ctx.restore();
                    const fontSize = (height / 114).toFixed(2);
                    ctx.font = fontSize + 'em sans-serif';
                    ctx.textBaseline = 'middle';
                    ctx.fillStyle = '#000';

                    const text = pendingClaims.toString();
                    const textX = Math.round((width - ctx.measureText(text).width) / 2);
                    const textY = height / 2;

                    ctx.fillText(text, textX, textY);
                    ctx.save();
                }
            }]
        });

        // Claimed Chart (Line Chart) - with improved axis configuration
        new Chart(document.getElementById('claimedChart'), {
            type: 'line',
            data: {
                labels: monthLabels,
                datasets: [{
                    label: 'Approved',
                    data: monthlyClaimedData,
                    borderColor: '#1cc88a',
                    backgroundColor: 'rgba(28, 200, 138, 0.1)',
                    fill: true,
                    tension: 0.3,
                    pointBackgroundColor: '#1cc88a',
                    pointBorderColor: '#fff',
                    pointHoverBackgroundColor: '#fff',
                    pointHoverBorderColor: '#1cc88a',
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                ...commonOptions,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            font: {
                                size: 10
                            },
                            callback: function(value) {
                                // Only show integers and prevent duplicates
                                if (Number.isInteger(value)) {
                                    return integerFormatter(value);
                                }
                                return null;
                            },
                            stepSize: 1 // Force steps of 1 to avoid decimal values
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    },
                    x: {
                        ticks: {
                            font: {
                                size: 10
                            }
                        },
                        grid: {
                            display: false
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `Approved: ${integerFormatter(context.parsed.y)}`;
                            }
                        }
                    }
                }
            }
        });

        // Recovery Rate Chart (Gauge Chart using Doughnut)
        new Chart(document.getElementById('recoveryRateChart'), {
            type: 'doughnut',
            data: {
                labels: ['Recovery Rate', 'Recovery Rate'],
                datasets: [{
                    data: [recoveryRate, 100 - recoveryRate],
                    backgroundColor: ['#36b9cc', '#eaecf4'],
                    hoverBackgroundColor: ['#2c9faf', '#eaecf4'],
                    hoverBorderColor: "rgba(234, 236, 244, 1)",
                }]
            },
            options: {
                ...commonOptions,
                circumference: 180,
                rotation: -90,
                cutout: '70%',
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return integerFormatter(recoveryRate) + '%';
                            }
                        }
                    }
                }
            },
            plugins: [{
                id: 'gaugeText',
                afterDraw: function(chart) {
                    const width = chart.width;
                    const height = chart.height;
                    const ctx = chart.ctx;

                    ctx.restore();
                    const fontSize = (height / 114).toFixed(2);
                    ctx.font = fontSize + 'em sans-serif';
                    ctx.textBaseline = 'middle';
                    ctx.fillStyle = '#000';

                    const text = recoveryRate + '%';
                    const textX = Math.round((width - ctx.measureText(text).width) / 2);
                    const textY = height / 1.5;

                    ctx.fillText(text, textX, textY);
                    ctx.save();
                }
            }]
        });
        
        // Add event listener to faculty filter dropdown for automatic submission
        document.getElementById('faculty_id').addEventListener('change', function() {
            window.location.href = '{{ route('dashboard') }}' + (this.value ? '?faculty_id=' + this.value : '');
        });
    });
</script>
@endpush

