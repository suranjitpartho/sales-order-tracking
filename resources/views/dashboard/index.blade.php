@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
<div class="dashboard-container">
    <h2 class="section-title">Dashboard</h2>

    <div class="card-grid">
        <div class="stat-card">
            <h3>Total Sales</h3>
            <p class="stat">${{ number_format($totalSales, 2) }}</p>
        </div>
        <div class="stat-card">
            <h3>Total Orders</h3>
            <p class="stat">{{ $totalOrders }}</p>
        </div>
    </div>

    <div class="chart-grid">
        <div class="chart-box">
            <h4>Orders by Product</h4>
            <canvas id="barChart"></canvas>
        </div>
        <div class="chart-box">
            <h4>Buyer Gender Distribution</h4>
            <canvas id="pieChart"></canvas>
        </div>
        <div class="chart-box">
            <h4>Orders Over Time</h4>
            <canvas id="lineChart"></canvas>
        </div>
        <div class="chart-box">
            <h4>Orders by Location</h4>
            <canvas id="locationChart"></canvas>
        </div>
    </div>
</div>
@endsection



<!-- JavaScripts for Dashboard Charts -->

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>

    // BAR CHART
    const barChart = new Chart(document.getElementById('barChart'), {
        type: 'bar',
        data: {
            labels: {!! json_encode($ordersByProduct->keys()) !!},
            datasets: [{
                label: 'Orders',
                data: {!! json_encode($ordersByProduct->values()) !!},
                backgroundColor: '#ddd30d'
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
            },
            scales: {
                x: {
                    ticks: {
                        font: { size: 10, family: 'inherit' },
                        padding: 8,
                    }
                },
                y: {
                    ticks: {
                        font: { size: 9, family: 'inherit' },
                        padding: 8,
                        stepSize: 1
                    }
                }
            }
        }
    });

    // PIE CHART
    const pieChart = new Chart(document.getElementById('pieChart'), {
        type: 'pie',
        data: {
            labels: {!! json_encode($genderDistribution->keys()) !!},
            datasets: [{
                data: {!! json_encode($genderDistribution->values()) !!},
                backgroundColor: ['#ddd30d', '#e9e369'],
                borderColor: '#111',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            aspectRatio: 1,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        color: '#bebebe',
                        font: {
                            size: 9
                        }
                    }
                }
            }
        }
    });

    // LINE CHART
    const lineChart = new Chart(document.getElementById('lineChart'), {
        type: 'line',
        data: {
            labels: {!! json_encode($ordersByDate->keys()) !!},
            datasets: [{
                label: 'Orders',
                data: {!! json_encode($ordersByDate->values()) !!},
                borderColor: '#ddd30d',
                fill: false,
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { 
                    display: true,
                    position: 'bottom'
                },
            },
            scales: {
                x: {
                    ticks: {
                        font: { size: 9, family: 'inherit' },
                        padding: 5,
                    }
                },
                y: {
                    ticks: {
                        font: { size: 9, family: 'inherit' },
                        padding: 8,
                        stepSize: 1
                    }
                }
            }
        }
    });


    // LOCATION CHART
    const locationChart = new Chart(document.getElementById('locationChart'), {
        type: 'bar',
        data: {
            labels: {!! json_encode($ordersByLocation->keys()) !!},
            datasets: [{
                label: 'Orders',
                data: {!! json_encode($ordersByLocation->values()) !!},
                backgroundColor: '#ddd30d'
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { 
                    display: false,
                 },
            },
            scales: {
                x: {
                    ticks: {
                        font: { size: 10, family: 'inherit' },
                        padding: 8,
                    }
                },
                y: {
                    ticks: {
                        font: { size: 9, family: 'inherit' },
                        padding: 8,
                        stepSize: 1
                    }
                }
            }
        }
    });
</script>
@endpush
