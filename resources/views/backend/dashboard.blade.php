@extends('backend.layouts.design')
@section('title')Dashboard @endsection

@section('extra_css')
<style>
    .dashboard-card {
        background: #fff;
        border: none;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        padding: 20px;
        margin-bottom: 20px;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .dashboard-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 6px 18px rgba(0,0,0,0.12);
    }
    .dashboard-card h6 {
        font-size: 0.9rem;
        margin-bottom: 6px;
        color: #6c757d;
        font-weight: 600;
    }
    .dashboard-card h5 {
        margin: 0;
        font-weight: 700;
    }
    .dashboard-icon {
        font-size: 22px;
        opacity: .9;
    }
    .content-page { padding-top: 4px; }
    .chart-container {
        background: #fff;
        border-radius: 10px;
        padding: 20px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        margin-bottom: 20px;
    }
</style>
@endsection

@section('content')
<!-- Main Content -->
<div class="main-content">
    <div id="dashboard-content" class="content-page">
        <h4 class="mb-3">Dashboard</h4>

        <div class="row">
            <!-- Users -->
            <div class="col-md-3">
                <div class="dashboard-card">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h6>Total Users</h6>
                            <h5>{{ $users }}</h5>
                        </div>
                        <i class="fas fa-users text-info dashboard-icon"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="dashboard-card">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h6>Pending Users</h6>
                            <h5>{{ $userPending }}</h5>
                        </div>
                        <i class="fas fa-hourglass-half text-primary dashboard-icon"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="dashboard-card">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h6>Approved Users</h6>
                            <h5>{{ $userApproved }}</h5>
                        </div>
                        <i class="fas fa-user-check text-success dashboard-icon"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="dashboard-card">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h6>Suspended Users</h6>
                            <h5>{{ $userSuspended }}</h5>
                        </div>
                        <i class="fas fa-ban text-danger dashboard-icon"></i>
                    </div>
                </div>
            </div>

            <!-- Clients / Freelancers -->
            <div class="col-md-3">
                <div class="dashboard-card">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h6>Total Clients</h6>
                            <h5>{{ $clients }}</h5>
                        </div>
                        <i class="fas fa-user-tie text-info dashboard-icon"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="dashboard-card">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h6>Total Freelancers</h6>
                            <h5>{{ $freelancers }}</h5>
                        </div>
                        <i class="fas fa-briefcase text-secondary dashboard-icon"></i>
                    </div>
                </div>
            </div>

            <!-- Tasks: totals -->
            <div class="col-md-3">
                <div class="dashboard-card">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h6>Total Tasks</h6>
                            <h5>{{ $tasks }}</h5>
                        </div>
                        <i class="fas fa-tasks text-info dashboard-icon"></i>
                    </div>
                </div>
            </div>

            <!-- Tasks: by status -->
            <div class="col-md-3">
                <div class="dashboard-card">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h6>Task Pending</h6>
                            <h5>{{ $taskPending }}</h5>
                        </div>
                        <i class="fas fa-hourglass-half text-primary dashboard-icon"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="dashboard-card">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h6>Task Started</h6>
                            <h5>{{ $taskStarted }}</h5>
                        </div>
                        <i class="fas fa-play-circle text-info dashboard-icon"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="dashboard-card">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h6>Task Completed</h6>
                            <h5>{{ $taskCompleted }}</h5>
                        </div>
                        <i class="fas fa-check-circle text-success dashboard-icon"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="dashboard-card">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h6>Task Cancelled</h6>
                            <h5>{{ $taskCancelled }}</h5>
                        </div>
                        <i class="fas fa-times-circle text-danger dashboard-icon"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="dashboard-card">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h6>Task Abandoned</h6>
                            <h5>{{ $taskAbandoned }}</h5>
                        </div>
                        <i class="fas fa-ban text-warning dashboard-icon"></i>
                    </div>
                </div>
            </div>

            <!-- Offers -->
            <div class="col-md-3">
                <div class="dashboard-card">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h6>Task Offer Pending</h6>
                            <h5>{{ $taskOffers }}</h5>
                        </div>
                        <i class="fas fa-gift text-info dashboard-icon"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="dashboard-card">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h6>Task Offer Accepted</h6>
                            <h5>{{ $taskOfferAccepted }}</h5>
                        </div>
                        <i class="fas fa-thumbs-up text-success dashboard-icon"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="dashboard-card">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h6>Task Offer Declined</h6>
                            <h5>{{ $taskOfferDeclined }}</h5>
                        </div>
                        <i class="fas fa-thumbs-down text-danger dashboard-icon"></i>
                    </div>
                </div>
            </div>

            <!-- Wallet / Transactions -->
            <div class="col-md-3">
                <div class="dashboard-card">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h6>Wallet Transactions</h6>
                            <h5>{{ $walletTransactions }}</h5>
                        </div>
                        <i class="fas fa-receipt text-secondary dashboard-icon"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="dashboard-card">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h6>Wallet Earnings</h6>
                            <h5>{{ $walletEarnings }}</h5>
                        </div>
                        <i class="fas fa-arrow-down text-success dashboard-icon"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="dashboard-card">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h6>Wallet Payouts</h6>
                            <h5>{{ $walletPayouts }}</h5>
                        </div>
                        <i class="fas fa-arrow-up text-primary dashboard-icon"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts -->
        <div class="row mt-2">
            <div class="col-md-8">
                <div class="chart-container">
                    {{-- <h5 class="mb-3">Monthly Wallet Flow</h5> --}}
                    <h5 class="mb-3">Monthly Transaction Flow</h5>
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>
            <div class="col-md-4">
                <div class="chart-container">
                    <h5 class="mb-3">Tasks by Status</h5>
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('extra_js')
<script>
/**
 * Assumes Chart.js is already included in your base layout.
 * Replace static data below with server-provided arrays if needed.
 */
document.addEventListener('DOMContentLoaded', function () {
    // Bar: Monthly Wallet Flow (dummy data—swap with real series if available)
    const revenueCtx = document.getElementById('revenueChart');
    if (revenueCtx && window.Chart) {
        new Chart(revenueCtx, {
            type: 'bar',
            data: {
                labels: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],
                datasets: [{
                    label: 'Earnings',
                    data: [1200,1800,1600,1900,2300,2100,2600,2400,2000,2700,3000,3200],
                    backgroundColor: '#6e8efb'
                },{
                    label: 'Payouts',
                    data: [600,900,800,1000,1100,950,1200,1300,900,1400,1500,1600],
                    backgroundColor: '#34c38f'
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: true } },
                scales: { y: { beginAtZero: true } }
            }
        });
    }

    // Doughnut: Tasks by Status (live counts from page)
    const statusCtx = document.getElementById('statusChart');
    if (statusCtx && window.Chart) {
        const pending   = Number(@json($taskPending   ?? 0));
        const started   = Number(@json($taskStarted   ?? 0));
        const completed = Number(@json($taskCompleted ?? 0));
        const cancelled = Number(@json($taskCancelled ?? 0));
        const abandoned = Number(@json($taskAbandoned ?? 0));
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Pending','Started','Completed','Cancelled','Abandoned'],
                datasets: [{
                    data: [pending, started, completed, cancelled, abandoned],
                    backgroundColor: ['#f1b44c', '#6e8efb', '#34c38f', '#f46a6a', '#ffbb33']
                }]
            },
            options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
        });
    }
});
</script>
@endsection
