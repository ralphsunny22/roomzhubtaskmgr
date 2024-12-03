<div class="app-menu">

    <!-- Brand Logo -->
    <div class="logo-box">
        <!-- Brand Logo Light -->
        <a href="{{route('adminDashboard')}}" class="logo-light">
            <img src="{{asset('/assets/backend/images/roomzhub-logo.png')}}" alt="logo" class="logo-lg">
            <img src="{{asset('/assets/backend/images/roomzhub-logo.png')}}" alt="small logo" class="logo-sm">
            <h4>Property Maintenance</h4>
        </a>

        <!-- Brand Logo Dark -->
        <a href="{{route('adminDashboard')}}" class="logo-dark">
            <img src="{{asset('/assets/backend/images/roomzhub-logo.png')}}" alt="dark logo" class="logo-lg">
            <img src="{{asset('/assets/backend/images/roomzhub-logo.png')}}" alt="small logo" class="logo-sm">
            <h4>Property Maintenance</h4>
        </a>
    </div>

    <!-- menu-left -->
    <div class="scrollbar">

        <!-- User box -->
        <div class="user-box text-center">
            <img src="{{asset('/assets/backend/images/users/user-1.jpg')}}" alt="user-img" title="Mat Helme" class="rounded-circle avatar-md">
            <div class="dropdown">
                <a href="javascript: void(0);" class="dropdown-toggle h5 mb-1 d-block" data-bs-toggle="dropdown">Geneva Kennedy</a>
                <div class="dropdown-menu user-pro-dropdown">

                    <!-- item-->
                    <a href="javascript:void(0);" class="dropdown-item notify-item">
                        <i class="fe-user me-1"></i>
                        <span>My Account</span>
                    </a>

                    <!-- item-->
                    <a href="javascript:void(0);" class="dropdown-item notify-item">
                        <i class="fe-settings me-1"></i>
                        <span>Settings</span>
                    </a>

                    <!-- item-->
                    <a href="javascript:void(0);" class="dropdown-item notify-item">
                        <i class="fe-lock me-1"></i>
                        <span>Lock Screen</span>
                    </a>

                    <!-- item-->
                    <a href="javascript:void(0);" class="dropdown-item notify-item">
                        <i class="fe-log-out me-1"></i>
                        <span>Logout</span>
                    </a>

                </div>
            </div>
            <p class="text-muted mb-0">Admin Head</p>
        </div>

        <!--- Menu -->
        <ul class="menu">

            <li class="menu-title">Navigation</li>

            <li class="menu-item">
                <a href="{{route('adminDashboard')}}" class="menu-link">
                    <span class="menu-icon"><i data-feather="airplay"></i></span>
                    <span class="menu-text"> Dashboard</span>
                </a>
            </li>

            <li class="menu-title">Users</li>

            <li class="menu-item">
                <a href="#userListings" data-bs-toggle="collapse" class="menu-link">
                    <span class="menu-icon"><i class="fa fa-home"></i></span>
                    <span class="menu-text"> Users Lists </span>
                    <span class="menu-arrow ms-auto"><i class="fa fa-angle-right"></i></span>
                </a>
                <div class="collapse" id="userListings">
                    <ul class="sub-menu">
                        <li class="menu-item">
                            <a href="{{route('allUser')}}" class="menu-link">
                                <span class="menu-text">All Users</span>
                            </a>
                        </li>
                        <li class="menu-item">
                            <a href="{{route('allUser', 'pending')}}" class="menu-link">
                                <span class="menu-text">Pending Users</span>
                            </a>
                        </li>
                        <li class="menu-item">
                            <a href="{{route('allUser', 'approved')}}" class="menu-link">
                                <span class="menu-text">Approved Users</span>
                            </a>
                        </li>
                        <li class="menu-item">
                            <a href="{{route('allUser', 'suspended')}}" class="menu-link">
                                <span class="menu-text">Suspended Users</span>
                            </a>
                        </li>

                    </ul>
                </div>
            </li>

            <li class="menu-title">Clients</li>

            <li class="menu-item">
                <a href="#clientListings" data-bs-toggle="collapse" class="menu-link">
                    <span class="menu-icon"><i class="fa fa-home"></i></span>
                    <span class="menu-text"> Client Lists </span>
                    <span class="menu-arrow ms-auto"><i class="fa fa-angle-right"></i></span>
                </a>
                <div class="collapse" id="clientListings">
                    <ul class="sub-menu">
                        <li class="menu-item">
                            <a href="{{route('allClient')}}" class="menu-link">
                                <span class="menu-text">All Clients</span>
                            </a>
                        </li>
                        <li class="menu-item">
                            <a href="{{route('allClient', 'pending')}}" class="menu-link">
                                <span class="menu-text">Pending Clients</span>
                            </a>
                        </li>
                        <li class="menu-item">
                            <a href="{{route('allClient', 'approved')}}" class="menu-link">
                                <span class="menu-text">Approved Clients</span>
                            </a>
                        </li>
                        <li class="menu-item">
                            <a href="{{route('allClient', 'suspended')}}" class="menu-link">
                                <span class="menu-text">Suspended Clients</span>
                            </a>
                        </li>

                    </ul>
                </div>
            </li>

            <li class="menu-title">Freelancers</li>

            <li class="menu-item">
                <a href="#freelancerListings" data-bs-toggle="collapse" class="menu-link">
                    <span class="menu-icon"><i class="fa fa-home"></i></span>
                    <span class="menu-text"> Freelancer Lists </span>
                    <span class="menu-arrow ms-auto"><i class="fa fa-angle-right"></i></span>
                </a>
                <div class="collapse" id="freelancerListings">
                    <ul class="sub-menu">
                        <li class="menu-item">
                            <a href="{{route('allFreelancer')}}" class="menu-link">
                                <span class="menu-text">All Freelancers</span>
                            </a>
                        </li>
                        <li class="menu-item">
                            <a href="{{route('allFreelancer', 'pending')}}" class="menu-link">
                                <span class="menu-text">Pending Freelancers</span>
                            </a>
                        </li>
                        <li class="menu-item">
                            <a href="{{route('allFreelancer', 'approved')}}" class="menu-link">
                                <span class="menu-text">Approved Freelancers</span>
                            </a>
                        </li>
                        <li class="menu-item">
                            <a href="{{route('allFreelancer', 'suspended')}}" class="menu-link">
                                <span class="menu-text">Suspended Freelancers</span>
                            </a>
                        </li>

                    </ul>
                </div>
            </li>

            <li class="menu-title">Tasks</li>

            <li class="menu-item">
                <a href="#taskListings" data-bs-toggle="collapse" class="menu-link">
                    <span class="menu-icon"><i class="fa fa-home"></i></span>
                    <span class="menu-text"> Task Lists </span>
                    <span class="menu-arrow ms-auto"><i class="fa fa-angle-right"></i></span>
                </a>
                <div class="collapse" id="taskListings">
                    <ul class="sub-menu">
                        <li class="menu-item">
                            <a href="{{route('allTask')}}" class="menu-link">
                                <span class="menu-text">All Tasks</span>
                            </a>
                        </li>
                        <li class="menu-item">
                            <a href="{{route('allTask', 'pending')}}" class="menu-link">
                                <span class="menu-text">Pending Tasks</span>
                            </a>
                        </li>
                        <li class="menu-item">
                            <a href="{{route('allTask', 'started')}}" class="menu-link">
                                <span class="menu-text">Started Tasks</span>
                            </a>
                        </li>
                        <li class="menu-item">
                            <a href="{{route('allTask', 'completed')}}" class="menu-link">
                                <span class="menu-text">Completed Tasks</span>
                            </a>
                        </li>
                        <li class="menu-item">
                            <a href="{{route('allTask', 'cancelled')}}" class="menu-link">
                                <span class="menu-text">Cancelled Tasks</span>
                            </a>
                        </li>
                        <li class="menu-item">
                            <a href="{{route('allTask', 'abandoned')}}" class="menu-link">
                                <span class="menu-text">Abandoned Tasks</span>
                            </a>
                        </li>

                    </ul>
                </div>
            </li>

            <li class="menu-title">Transactions</li>

            <li class="menu-item">
                <a href="#transactionListings" data-bs-toggle="collapse" class="menu-link">
                    <span class="menu-icon"><i class="fa fa-home"></i></span>
                    <span class="menu-text"> Transaction Lists </span>
                    <span class="menu-arrow ms-auto"><i class="fa fa-angle-right"></i></span>
                </a>
                <div class="collapse" id="transactionListings">
                    <ul class="sub-menu">
                        <li class="menu-item">
                            <a href="{{route('allTransaction')}}" class="menu-link">
                                <span class="menu-text">All Transactions</span>
                            </a>
                        </li>
                        <li class="menu-item">
                            <a href="{{route('allEarning')}}" class="menu-link">
                                <span class="menu-text">Earnings Transactions</span>
                            </a>
                        </li>
                        <li class="menu-item">
                            <a href="{{route('allPayout')}}" class="menu-link">
                                <span class="menu-text">Payouts Transactions</span>
                            </a>
                        </li>

                    </ul>
                </div>
            </li>

            <li class="menu-title d-none">Payments</li>

            <li class="menu-item d-none">
                <a href="#allPayment" data-bs-toggle="collapse" class="menu-link">
                    <span class="menu-icon"><i class="fa fa-money"></i></span>
                    <span class="menu-text">All Payments </span>
                    <span class="menu-arrow ms-auto"><i class="fa fa-angle-right"></i></span>
                </a>
                <div class="collapse" id="allPayment">
                    <ul class="sub-menu">
                        <li class="menu-item">
                            <a href="/" class="menu-link">
                                <span class="menu-text">All Payments</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

        </ul>
        <!--- End Menu -->
        <div class="clearfix"></div>
    </div>
</div>
