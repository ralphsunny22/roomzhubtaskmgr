<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fix&Fetch Admin - Wavezio</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f6f9;
            overflow-x: hidden;
        }
        .sidebar {
            width: 250px;
            background: #1e2a44;
            color: #fff;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            transition: width 0.3s;
            overflow-y: auto;
            z-index: 1000;
        }
        .sidebar.collapsed {
            width: 60px;
        }
        .sidebar .nav-link {
            color: #b0b7c3;
            padding: 10px 15px;
            display: flex;
            align-items: center;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            background: #2a3f6d;
            color: #fff;
        }
        .sidebar .nav-link i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        .sidebar.collapsed .nav-link span, .sidebar.collapsed .sidebar-header h4 {
            display: none;
        }
        .sidebar-header {
            padding: 15px;
            background: #17233b;
        }
        .topnav {
            background: #fff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 10px 20px;
            position: fixed;
            top: 0;
            left: 250px;
            right: 0;
            z-index: 999;
            transition: left 0.3s;
        }
        .topnav.collapsed {
            left: 60px;
        }
        .profile-img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }
        .main-content {
            margin-left: 250px;
            margin-top: 60px;
            padding: 20px;
            transition: margin-left 0.3s;
        }
        .main-content.collapsed {
            margin-left: 60px;
        }
        @media (max-width: 768px) {
            .sidebar {
                width: 60px;
            }
            .sidebar.collapsed {
                width: 60px;
            }
            .main-content, .topnav {
                margin-left: 60px;
                left: 60px;
            }
            .sidebar .nav-link span, .sidebar-header h4 {
                display: none;
            }
        }
    </style>
    @yield('extra_css')
</head>
<body>
    <!-- Sidebar -->
    @include('backend.layouts.sidebar')

    <!-- Top Navigation Bar -->
    @include('backend.layouts.header')
    <!-- Main Content -->
    @yield('content')
    <!-- Scripts -->
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#toggle-sidebar').click(function() {
                $('#sidebar').toggleClass('collapsed');
                $('.main-content').toggleClass('collapsed');
                $('.topnav').toggleClass('collapsed');
            });
            // Sub-menu Toggle
            $('[data-toggle="collapse"]').click(function(e) {
                var target = $(this).data('target');
                $(target).toggleClass('show');
                $(this).find('.fa-chevron-down').toggleClass('fa-chevron-up');
            });

            // Page Navigation
            $('.nav-link[data-page]').click(function(e) {
                e.preventDefault();
                var page = $(this).data('page');
                $('.content-page').addClass('d-none');
                $('#' + page).removeClass('d-none');
                $('.nav-link').removeClass('active');
                $(this).addClass('active');
            });
            // $('#seekers-table').DataTable({
            //     responsive: true,
            //     pageLength: 10,
            //     lengthMenu: [10, 20, 50, 100]
            // });
        });
    </script>
    @yield('extra_js')
</body>
</html>
