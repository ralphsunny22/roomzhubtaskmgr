
<!DOCTYPE html>
<html lang="en" data-topbar-color="dark">

    <head>
        <meta charset="utf-8" />
        <title>@yield('title') :: RoomzHub Admin</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta content="A fully featured admin theme which can be used to build CRM, CMS, etc." name="description" />
        <meta content="Coderthemes" name="author" />

        <!-- App favicon -->
        <link rel="shortcut icon" href="{{asset('/assets/backend/images/favicon1.ico')}}">

        <link href="{{asset('/assets/backend/fancybox/jquery.fancybox.css')}}" rel="stylesheet" type="text/css" />

        <!-- third party css -->
        <link href="{{asset('/assets/backend/libs/datatables.net-bs5/css/dataTables.bootstrap5.min.css')}}" rel="stylesheet" type="text/css" />
        <link href="{{asset('/assets/backend/libs/datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css')}}" rel="stylesheet" type="text/css" />
        <link href="{{asset('/assets/backend/libs/datatables.net-buttons-bs5/css/buttons.bootstrap5.min.css')}}" rel="stylesheet" type="text/css" />
        <link href="{{asset('/assets/backend/libs/datatables.net-select-bs5/css/select.bootstrap5.min.css')}}" rel="stylesheet" type="text/css" />
        <!-- third party css end -->

        <!-- Plugins css -->
        <link href="{{asset('/assets/backend/libs/flatpickr/flatpickr.min.css')}}" rel="stylesheet" type="text/css" />
        <link href="{{asset('/assets/backend/libs/selectize/css/selectize.bootstrap3.css')}}" rel="stylesheet" type="text/css" />

        <!-- Theme Config Js -->
        <script src="{{asset('/assets/backend/js/head.js')}}"></script>

        <!-- Bootstrap css -->
        <link href="{{asset('/assets/backend/css/bootstrap.min.css')}}" rel="stylesheet" type="text/css" id="app-style" />

        <!-- App css -->
        <link href="{{asset('/assets/backend/css/app.min.css')}}" rel="stylesheet" type="text/css" />

        <!-- Icons css -->
        {{-- <link href="{{asset('/assets/backend/css/icons.min.css')}}" rel="stylesheet" type="text/css" /> --}}
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

        <style>
            .avatar-title {
                -webkit-box-align: center;
                -ms-flex-align: center;
                align-items: center;
                color: var(--ct-card-bg);
                display: -webkit-box;
                display: -ms-flexbox;
                display: flex;
                height: 100%;
                -webkit-box-pack: center;
                -ms-flex-pack: center;
                justify-content: center;
                width: 100%;
            }
        </style>
        @yield('extra_css')
    </head>

    <body>

        <!-- Begin page -->
        <div id="wrapper">


            <!-- ========== Menu ========== -->
            @include('backend.layouts.sidebar')
            <!-- ========== Left menu End ========== -->

            <!-- ============================================================== -->
            <!-- Start Page Content here -->
            <!-- ============================================================== -->

            <div class="content-page">

                <!-- ========== Topbar Start ========== -->
                @include('backend.layouts.header')
                <!-- ========== Topbar End ========== -->

                 @yield('content')
                 <!-- content -->

                <!-- Footer Start -->
                @include('backend.layouts.footer')
                <!-- end Footer -->

            </div>

            <!-- ============================================================== -->
            <!-- End Page content -->
            <!-- ============================================================== -->

        </div>
        <!-- END wrapper -->

        <!-- Theme Settings -->

        <!-- Vendor js -->
        <script src="{{asset('/assets/backend/js/vendor.min.js')}}"></script>

        <!-- App js -->
        <script src="{{asset('/assets/backend/js/app.min.js')}}"></script>

        <script src="{{asset('/assets/backend/fancybox/jquery.fancybox.min.js')}}"></script>

        <!-- Plugins js-->
        <script src="{{asset('/assets/backend/libs/flatpickr/flatpickr.min.js')}}"></script>
        {{-- <script src="{{asset('/assets/backend/libs/apexcharts/apexcharts.min.js')}}"></script> --}}
        <script src="{{asset('/assets/backend/libs/selectize/js/standalone/selectize.min.js')}}"></script>

        <!-- Dashboar 1 init js-->
        {{-- <script src="{{asset('/assets/backend/js/pages/dashboard-1.init.js')}}"></script> --}}

        <!-- third party js -->
        <script src="{{asset('/assets/backend/libs/datatables.net/js/jquery.dataTables.min.js')}}"></script>
        <script src="{{asset('/assets/backend/libs/datatables.net-bs5/js/dataTables.bootstrap5.min.js')}}"></script>
        <script src="{{asset('/assets/backend/libs/datatables.net-responsive/js/dataTables.responsive.min.js')}}"></script>
        <script src="{{asset('/assets/backend/libs/datatables.net-responsive-bs5/js/responsive.bootstrap5.min.js')}}"></script>

        <script src="{{asset('/assets/backend/libs/datatables.net-buttons/js/dataTables.buttons.min.js')}}"></script>
        <script src="{{asset('/assets/backend/libs/datatables.net-buttons-bs5/js/buttons.bootstrap5.min.js')}}"></script>

        <script src="{{asset('/assets/backend/libs/datatables.net-buttons/js/buttons.html5.min.js')}}"></script>
        <script src="{{asset('/assets/backend/libs/datatables.net-buttons/js/buttons.flash.min.js')}}"></script>
        <script src="{{asset('/assets/backend/libs/datatables.net-buttons/js/buttons.print.min.js')}}"></script>

        <script src="{{asset('/assets/backend/libs/datatables.net-keytable/js/dataTables.keyTable.min.js')}}"></script>
        <script src="{{asset('/assets/backend/libs/datatables.net-select/js/dataTables.select.min.js')}}"></script>

        <script src="{{asset('/assets/backend/libs/pdfmake/build/pdfmake.min.js')}}"></script>
        <script src="{{asset('/assets/backend/libs/pdfmake/build/vfs_fonts.js')}}"></script>

        @yield('extra_js')

    </body>

</html>
