<div class="app-menu">

    <!-- Brand Logo -->
    <div class="logo-box">
        <!-- Brand Logo Light -->
        <a href="{{route('adminDashboard')}}" class="logo-light">
            {{-- <img src="{{asset('/assets/images/logo-light.png')}}" alt="logo" class="logo-lg">
            <img src="assets/images/logo-sm.png" alt="small logo" class="logo-sm"> --}}
            <h3>MARKETPLACE</h3>
        </a>

        <!-- Brand Logo Dark -->
        <a href="{{route('adminDashboard')}}" class="logo-dark">
            {{-- <img src="{{asset('/assets/images/logo-dark.png')}}" alt="dark logo" class="logo-lg">
            <img src="assets/images/logo-sm.png" alt="small logo" class="logo-sm"> --}}
            <h3>MARKETPLACE</h3>
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

            <li class="menu-title">Products</li>

            <li class="menu-item">
                <a href="#productListings" data-bs-toggle="collapse" class="menu-link">
                    <span class="menu-icon"><i class="fa fa-home"></i></span>
                    <span class="menu-text"> Product Lists </span>
                    <span class="menu-arrow ms-auto"><i class="fa fa-angle-right"></i></span>
                </a>
                <div class="collapse" id="productListings">
                    <ul class="sub-menu">
                        <li class="menu-item">
                            <a href="{{route('allProduct')}}" class="menu-link">
                                <span class="menu-text">All Products</span>
                            </a>
                        </li>
                        <li class="menu-item">
                            <a href="{{route('allProduct', 'pending')}}" class="menu-link">
                                <span class="menu-text">Pending Products</span>
                            </a>
                        </li>
                        <li class="menu-item">
                            <a href="{{route('allProduct', 'featured')}}" class="menu-link">
                                <span class="menu-text">Featured Products</span>
                            </a>
                        </li>

                    </ul>
                </div>
            </li>

            <li class="menu-title">Order Management</li>

            <li class="menu-item">
                <a href="#orderListings" data-bs-toggle="collapse" class="menu-link">
                    <span class="menu-icon"><i class="fa fa-home"></i></span>
                    <span class="menu-text"> Order Lists </span>
                    <span class="menu-arrow ms-auto"><i class="fa fa-angle-right"></i></span>
                </a>
                <div class="collapse" id="orderListings">
                    <ul class="sub-menu">
                        <li class="menu-item">
                            <a href="{{route('allOrder')}}" class="menu-link">
                                <span class="menu-text">All Orders</span>
                            </a>
                        </li>

                        <li class="menu-item">
                            <a href="{{route('allOrder', 'pending')}}" class="menu-link">
                                <span class="menu-text">Pending Orders</span>
                            </a>
                        </li>

                        <li class="menu-item">
                            <a href="{{route('allOrder', 'delivered')}}" class="menu-link">
                                <span class="menu-text">Delivered Orders</span>
                            </a>
                        </li>

                        <li class="menu-item">
                            <a href="{{route('allOrder', 'cancelled')}}" class="menu-link">
                                <span class="menu-text">Cancelled Orders</span>
                            </a>
                        </li>

                    </ul>
                </div>
            </li>

            <li class="menu-title">Customer MANAGEMENT</li>

            <li class="menu-item">
                <a href="#userMgt" data-bs-toggle="collapse" class="menu-link">
                    <span class="menu-icon"><i class="fa fa-users"></i></span>
                    <span class="menu-text">Users </span>
                    <span class="menu-arrow ms-auto"><i class="fa fa-angle-right"></i></span>
                </a>
                <div class="collapse" id="userMgt">
                    <ul class="sub-menu">
                        <li class="menu-item">
                            <a href="{{ route('allCustomer') }}" class="menu-link">
                                <span class="menu-text">All Customers</span>
                            </a>
                        </li>

                    </ul>
                </div>
            </li>

            <li class="menu-title">Vendor MANAGEMENT</li>

            <li class="menu-item">
                <a href="#vendorMgt" data-bs-toggle="collapse" class="menu-link">
                    <span class="menu-icon"><i class="fa fa-users"></i></span>
                    <span class="menu-text">Vendors </span>
                    <span class="menu-arrow ms-auto"><i class="fa fa-angle-right"></i></span>
                </a>
                <div class="collapse" id="vendorMgt">
                    <ul class="sub-menu">
                        <li class="menu-item">
                            <a href="{{ route('allVendor') }}" class="menu-link">
                                <span class="menu-text">All Vendors</span>
                            </a>
                        </li>
                        <li class="menu-item">
                            <a href="{{ route('allVendor', 'confirmed') }}" class="menu-link">
                                <span class="menu-text">Approved Vendors</span>
                            </a>
                        </li>
                        <li class="menu-item">
                            <a href="{{ route('allVendor', 'suspended') }}" class="menu-link">
                                <span class="menu-text">Suspended Vendors</span>
                            </a>
                        </li>

                    </ul>
                </div>
            </li>

            <li class="menu-title">Payments</li>

            <li class="menu-item">
                <a href="#allPayment" data-bs-toggle="collapse" class="menu-link">
                    <span class="menu-icon"><i class="fa fa-money"></i></span>
                    <span class="menu-text">All Payments </span>
                    <span class="menu-arrow ms-auto"><i class="fa fa-angle-right"></i></span>
                </a>
                <div class="collapse" id="allPayment">
                    <ul class="sub-menu">
                        <li class="menu-item">
                            <a href="{{ route('allPayment') }}" class="menu-link">
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
