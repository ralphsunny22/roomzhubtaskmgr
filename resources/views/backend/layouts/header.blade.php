<nav class="topnav">
    <div class="d-flex align-items-center justify-content-between w-100">
        <button id="toggle-sidebar" class="btn btn-outline-secondary">
            <i class="fas fa-bars"></i>
        </button>
        <div class="d-flex align-items-center">
            <!-- Notifications -->
            <div class="dropdown me-3">
                <a href="#" class="nav-link" id="notificationDropdown" data-bs-toggle="dropdown">
                    <i class="fas fa-bell"></i>
                    <span class="badge bg-danger rounded-pill">3</span>
                </a>
                <div class="dropdown-menu notification-dropdown" aria-labelledby="notificationDropdown">
                    <h6 class="dropdown-header">Notifications</h6>
                    <a class="dropdown-item" href="#">New property listed</a>
                    <a class="dropdown-item" href="#">Payment received</a>
                    <a class="dropdown-item" href="#">User registered</a>
                </div>
            </div>
            <!-- Profile -->
            <div class="dropdown">
                <a href="#" class="nav-link d-flex align-items-center" id="profileDropdown" data-bs-toggle="dropdown">
                    <span class="me-2">John Doe</span>
                    <img src="https://via.placeholder.com/40" alt="Profile" class="profile-img">
                </a>
                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown">
                    <a class="dropdown-item" href="#">Profile</a>
                    <a class="dropdown-item" href="#" id="logout">Logout</a>
                </div>
            </div>
        </div>
    </div>
</nav>
