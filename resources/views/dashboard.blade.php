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
                    <div style="height: 180px; position: relative;">
                        <canvas id="registeredStudentsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Claims Card -->
        <div class="col-md-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2 text-muted">Pending Claims</h6>
                    <div style="height: 180px; position: relative;">
                        <canvas id="pendingClaimsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Claimed Card -->
        <div class="col-md-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2 text-muted">Claimed</h6>
                    <div style="height: 180px; position: relative;">
                        <canvas id="claimedChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- recovery rate = successful claim/ total claims (successful + pending + rejected) -->
        <!-- Recovery Rate Card -->
        <div class="col-md-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2 text-muted">Recovery Rate</h6>
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
    document.addEventListener('DOMContentLoaded', function() {
        // Data passed from the Laravel backend - accessed through Blade
        const dashboardData = {!! json_encode($dashboardData ?? []) !!};
        
        // Extract values from the dashboardData or use defaults if data is not available
        const registeredStudents = Math.round(dashboardData.registeredStudents ?? 0);
        const pendingClaims = Math.round(dashboardData.pendingClaims ?? 0);
        const claimedItems = Math.round(dashboardData.claimedItems ?? 0);
        const recoveryRate = Math.round(dashboardData.recoveryRate ?? 0);
        
        // Monthly claimed data for trend chart - ensure all values are integers
        const monthlyClaimedData = (dashboardData.monthlyClaimedData ?? [0, 0, 0]).map(val => Math.round(val));
        const monthLabels = dashboardData.monthLabels ?? ['Jan', 'Feb', 'Mar'];
        
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
        new Chart(document.getElementById('registeredStudentsChart'), {
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
                cutout: '70%'
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
                    label: 'Claimed',
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
                                return `Claimed: ${integerFormatter(context.parsed.y)}`;
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
                labels: ['Recovery Rate', 'Remaining'],
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
    });
</script>
@endpush

