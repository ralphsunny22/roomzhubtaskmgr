<div class="sidebar" id="sidebar">
    <!-- Header / Brand -->
    <div class="sidebar-header text-center">
        <a href="{{ route('adminDashboard') }}" class="text-decoration-none d-flex align-items-center gap-2">
            {{-- <img src="{{ asset('/assets/backend/images/roomzhub-logo.png') }}" alt="logo" style="height:32px;width:auto;"> --}}
            <h4 class="mb-0">Fix&Fetch</h4>
        </a>
        <div class="mt-2 small text-muted">
            {{ Auth::guard('web')->user()->name ?? 'Admin' }} · Admin Head
        </div>
    </div>

    <ul class="nav flex-column">
        <!-- Dashboard -->
        <li class="nav-item">
            <a href="{{ route('adminDashboard') }}" class="nav-link">
                <i class="fas fa-tachometer-alt"></i><span>Dashboard</span>
            </a>
        </li>

        <!-- Users -->
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="collapse" href="#usersMenu" role="button" aria-expanded="false" aria-controls="usersMenu">
                <i class="fas fa-users"></i><span>Users</span>
                <i class="fas fa-chevron-down float-end"></i>
            </a>
            <div class="collapse" id="usersMenu">
                <ul class="nav flex-column ms-3">
                    <li class="nav-item">
                        <a href="{{ route('allUser') }}" class="nav-link">
                            <i class="fas fa-user"></i><span>All Users</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('allUser', 'pending') }}" class="nav-link">
                            <i class="fas fa-hourglass-half"></i><span>Pending Users</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('allUser', 'approved') }}" class="nav-link">
                            <i class="fas fa-check-circle"></i><span>Approved Users</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('allUser', 'suspended') }}" class="nav-link">
                            <i class="fas fa-ban"></i><span>Suspended Users</span>
                        </a>
                    </li>
                </ul>
            </div>
        </li>

        <!-- Clients -->
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="collapse" href="#clientsMenu" role="button" aria-expanded="false" aria-controls="clientsMenu">
                <i class="fas fa-user-friends"></i><span>Clients</span>
                <i class="fas fa-chevron-down float-end"></i>
            </a>
            <div class="collapse" id="clientsMenu">
                <ul class="nav flex-column ms-3">
                    <li class="nav-item">
                        <a href="{{ route('allClient') }}" class="nav-link">
                            <i class="fas fa-list"></i><span>All Clients</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('allClient', 'pending') }}" class="nav-link">
                            <i class="fas fa-hourglass-half"></i><span>Pending Clients</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('allClient', 'approved') }}" class="nav-link">
                            <i class="fas fa-check-circle"></i><span>Approved Clients</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('allClient', 'suspended') }}" class="nav-link">
                            <i class="fas fa-ban"></i><span>Suspended Clients</span>
                        </a>
                    </li>
                </ul>
            </div>
        </li>

        <!-- Freelancers -->
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="collapse" href="#freelancersMenu" role="button" aria-expanded="false" aria-controls="freelancersMenu">
                <i class="fas fa-briefcase"></i><span>Freelancers</span>
                <i class="fas fa-chevron-down float-end"></i>
            </a>
            <div class="collapse" id="freelancersMenu">
                <ul class="nav flex-column ms-3">
                    <li class="nav-item">
                        <a href="{{ route('allFreelancer') }}" class="nav-link">
                            <i class="fas fa-list"></i><span>All Freelancers</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('allFreelancer', 'pending') }}" class="nav-link">
                            <i class="fas fa-hourglass-half"></i><span>Pending Freelancers</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('allFreelancer', 'approved') }}" class="nav-link">
                            <i class="fas fa-check-circle"></i><span>Approved Freelancers</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('allFreelancer', 'suspended') }}" class="nav-link">
                            <i class="fas fa-ban"></i><span>Suspended Freelancers</span>
                        </a>
                    </li>
                </ul>
            </div>
        </li>

        <!-- Tasks -->
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="collapse" href="#tasksMenu" role="button" aria-expanded="false" aria-controls="tasksMenu">
                <i class="fas fa-tasks"></i><span>Tasks</span>
                <i class="fas fa-chevron-down float-end"></i>
            </a>
            <div class="collapse" id="tasksMenu">
                <ul class="nav flex-column ms-3">
                    <li class="nav-item">
                        <a href="{{ route('allTask') }}" class="nav-link">
                            <i class="fas fa-list"></i><span>All Tasks</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('allTask', 'pending') }}" class="nav-link">
                            <i class="fas fa-hourglass-half"></i><span>Pending Tasks</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('allTask', 'started') }}" class="nav-link">
                            <i class="fas fa-play-circle"></i><span>Started Tasks</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('allTask', 'completed') }}" class="nav-link">
                            <i class="fas fa-check-circle"></i><span>Completed Tasks</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('allTask', 'cancelled') }}" class="nav-link">
                            <i class="fas fa-times-circle"></i><span>Cancelled Tasks</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('allTask', 'abandoned') }}" class="nav-link">
                            <i class="fas fa-ban"></i><span>Abandoned Tasks</span>
                        </a>
                    </li>
                </ul>
            </div>
        </li>

        <!-- Offers -->
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="collapse" href="#transactionsMenu" role="button" aria-expanded="false" aria-controls="transactionsMenu">
                <i class="fas fa-receipt"></i><span>Offers</span>
                <i class="fas fa-chevron-down float-end"></i>
            </a>
            <div class="collapse" id="transactionsMenu">
                <ul class="nav flex-column ms-3">
                    <li class="nav-item">
                        <a href="{{ route('allOffer') }}" class="nav-link">
                            <i class="fas fa-list"></i><span>All Offers</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('allOffer', 'accepted') }}" class="nav-link">
                            <i class="fas fa-arrow-down"></i><span>Accepted Offers</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('allOffer', 'declined') }}" class="nav-link">
                            <i class="fas fa-arrow-up"></i><span>Declined Offers</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('allOffer', 'cancelled') }}" class="nav-link">
                            <i class="fas fa-arrow-up"></i><span>Cancelled Offers</span>
                        </a>
                    </li>
                </ul>
            </div>
        </li>

        <!-- Transactions -->
        <li class="nav-item d-none">
            <a class="nav-link" data-bs-toggle="collapse" href="#transactionsMenu" role="button" aria-expanded="false" aria-controls="transactionsMenu">
                <i class="fas fa-receipt"></i><span>Transactions</span>
                <i class="fas fa-chevron-down float-end"></i>
            </a>
            <div class="collapse" id="transactionsMenu">
                <ul class="nav flex-column ms-3">
                    <li class="nav-item">
                        <a href="{{ route('allTransaction') }}" class="nav-link">
                            <i class="fas fa-list"></i><span>All Transactions</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('allEarning') }}" class="nav-link">
                            <i class="fas fa-arrow-down"></i><span>Earnings Transactions</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('allPayout') }}" class="nav-link">
                            <i class="fas fa-arrow-up"></i><span>Payouts Transactions</span>
                        </a>
                    </li>
                </ul>
            </div>
        </li>

        <!-- Payments (optional / hidden) -->
        <li class="nav-item d-none">
            <a class="nav-link" data-bs-toggle="collapse" href="#paymentsMenu" role="button" aria-expanded="false" aria-controls="paymentsMenu">
                <i class="fas fa-credit-card"></i><span>Payments</span>
                <i class="fas fa-chevron-down float-end"></i>
            </a>
            <div class="collapse" id="paymentsMenu">
                <ul class="nav flex-column ms-3">
                    <li class="nav-item">
                        <a href="/" class="nav-link">
                            <i class="fas fa-list"></i><span>All Payments</span>
                        </a>
                    </li>
                </ul>
            </div>
        </li>
    </ul>
</div>
