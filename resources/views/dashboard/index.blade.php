@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
<div class="dashboard-container">
    <h2 class="section-title">SALES ANALYTICS</h2>

    <!-- FILTERS -->
    <div class="filter-buttons">
        @foreach (['lifetime'=>'Lifetime', 'this_month'=>'This Month', 'last_month'=>'Last Month'] as $key => $label)
            <a href="{{ route('dashboard', ['filter'=>$key]) }}" class="filter-btn {{ $filter === $key ? 'active' : '' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>


    <!-- CARDS -->

    <div class="card-grid">

        <!-- total sales card -->
        <div class="stat-card">
            <i class="fa-solid fa-money-check-dollar card-icon"></i>
            <h3>TOTAL SALES</h3>
            <div class="data-row">                
                <p class="stat">${{ number_format($totalSales, 2) }}</p>
                @if (!is_null($salesDelta))
                    <div class="delta-block">
                        <span class="delta {{ $salesDelta >= 0 ? 'up' : 'down' }}">
                            <i class="fas fa-arrow-{{ $salesDelta >= 0 ? 'up' : 'down' }}"></i>
                            {{ abs(round($salesDelta, 1)) }}%
                        </span>
                        <small class="delta-note">vs. last month</small>
                    </div>
                @endif
            </div>
        </div>

        <div class="stat-card">
            <i class="fa-solid fa-list-check card-icon card-icon"></i>
            <h3>TOTAL ORDERS</h3>
            <div class="data-row">
                <p class="stat">{{ number_format($totalOrders, 0) }}</p>
                @if (!is_null($ordersDelta))
                    <div class="delta-block">
                        <span class="delta {{ $ordersDelta >= 0 ? 'up' : 'down' }}">
                            <i class="fas fa-arrow-{{ $ordersDelta >= 0 ? 'up' : 'down' }}"></i>
                            {{ abs(round($ordersDelta, 1)) }}%
                        </span>
                        <small class="delta-note">vs. last month</small>
                    </div>
                @endif
            </div>
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


    <!-- CHARTS -->
    <div class="chart-grid">
        <div class="chart-box">
            <h3>ORDERS BY PRODUCT</h3>
            <canvas id="barChart"></canvas>
        </div>
        <div class="chart-box">
            <h3>Buyer Gender Distribution</h3>
            <canvas id="pieChart"></canvas>
        </div>
        <div class="chart-box">
            <h3>Orders Over Time</h3>
            <canvas id="lineChart"></canvas>
        </div>
        <div class="chart-box">
            <h3>Orders by Location</h3>
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
            layout: {
                padding: {
                    top: 0, right: 0, bottom: 10, left: 0 
                }
            },
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
                borderRadius: 8, 
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            layout: {
                padding: {
                    top: 10,   // existing top padding
                    right: 10,   // if you like symmetric
                    bottom: 30,   // <-- extra bottom space
                    left: 10    // if you like symmetric
                }
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
                        font: { size: 10},
                    },
                },
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
            layout: {
                padding: {
                    top: 0, right: 0, bottom: 20, left: 0 
                }
            },
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
            layout: {
                padding: {
                    top: 0, right: 0, bottom: 10, left: 0 
                }
            },
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
