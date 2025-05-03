@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
<div class="dashboard-container">
    <h2 class="section-title">SALES ANALYTICS</h2>

    <div class="card-grid">
        <div class="stat-card">
            <i class="fa-solid fa-money-check-dollar card-icon"></i>
            <h3>TOTAL SALES</h3>
            <p class="stat">${{ number_format($totalSales, 2) }}</p>
        </div>
        <div class="stat-card">
            <i class="fa-solid fa-list-check card-icon card-icon"></i>
            <h3>TOTAL ORDERS</h3>
            <p class="stat">{{ $totalOrders }}</p>
        </div>
        <div class="stat-card">
            <i class="fa-solid fa-boxes-packing card-icon"></i>
            <h3>TOTAL QUANTITY</h3>
            <p class="stat">{{ number_format($totalQuantity, 0) }}</p>
        </div>
        <div class="stat-card">
            <i class="fa-solid fa-boxes-packing card-icon"></i>
            <h3>SHIPPING CHARGES</h3>
            <p class="stat">${{ number_format($totalShippingCharges, 2) }}</p>
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
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
<script>

    // BAR CHART
    const barChart = new Chart(document.getElementById('barChart'), {
        type: 'bar',
        data: {
            labels: {!! json_encode($ordersByProduct->keys()) !!},
            datasets: [{
                label: 'Orders',
                data: {!! json_encode($ordersByProduct->values()) !!},
                backgroundColor: '#ddd30d',
                borderRadius: 8,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false, 
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
        },
        layout: {
            padding: 20
        },
    });

    // PIE CHART
    const pieChart = new Chart(document.getElementById('pieChart'), {
        type: 'pie',
        data: {
            labels: {!! json_encode($genderDistribution->keys()) !!},
            datasets: [{
                data: {!! json_encode($genderDistribution->values()) !!},
                backgroundColor: ['#ddd30d', '#e9e369'],
                borderColor: '#252737',
                borderWidth: 2,
                borderRadius: 4, 
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            aspectRatio: 1,
            layout: {
                padding: 0
            },
            plugins: {
                legend: {
                    position: 'right',
                    align: 'center',
                    labels: {
                        boxWidth: 12,
                        boxHeight: 12,
                        padding: 5,
                        color: '#bebebe',
                        font: { size: 12},
                    },
                },
                // datalabels: {
                //     formatter: (value, ctx) => {
                //         const data = ctx.chart.data.datasets[0].data;
                //         const sum = data.reduce((a, b) => a + b, 0);
                //         const pct = (value / sum * 100).toFixed(1) + '%';
                //         return pct;
                //     },
                //     color: '#fff',
                //     font: {
                //         weight: 'bold',
                //         size: 12
                //     }
                // },
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
                tension: 0.3,
                pointStyle: 'circle',
                pointRadius: 6,
                pointBackgroundColor: '#ddd30d',
                pointBorderColor: '#111',
                pointBorderWidth: 2,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false, 
            plugins: {
                legend: { 
                    display: true,
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,       // use circle instead of box
                        pointStyle: 'circle',
                        boxWidth: 8,               // width of the legend marker
                        padding: 10,
                        color: '#bebebe',
                        font: { size: 10 }
                    }
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
                backgroundColor: '#ddd30d',
                borderRadius: 8,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false, 
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
