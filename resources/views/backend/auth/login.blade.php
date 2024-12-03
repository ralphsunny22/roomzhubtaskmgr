<!DOCTYPE html>
<html lang="en" data-topbar-color="dark">

    <head>
        <meta charset="utf-8" />
        <title>TaskManager | ROOMZHUB - Admin Dashboard</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta content="A fully featured admin theme which was built for RoomzHub Management" name="description" />
        <meta content="Coderthemes" name="author" />

        <!-- App favicon -->
        <link rel="shortcut icon" href="{{asset('/assets/backend/images/favicon1.ico')}}">

		<!-- Theme Config Js -->
		<script src="{{asset('/assets/backend/js/head.js')}}"></script>

		<!-- Bootstrap css -->
		<link href="{{asset('/assets/backend/css/bootstrap.min.css')}}" rel="stylesheet" type="text/css" id="app-style" />

		<!-- App css -->
		<link href="{{asset('/assets/backend/css/app.min.css')}}" rel="stylesheet" type="text/css" />

		<!-- Icons css -->
		<link href="{{asset('/assets/backend/css/icons.min.css')}}" rel="stylesheet" type="text/css" />
    </head>

    <body class="authentication-bg authentication-bg-pattern">

        <div class="account-pages mt-5 mb-5">
            <div class="container">
                <div class="row justify-content-center">

                    <div class="col-md-8 col-lg-6 col-xl-4">
                        <div class="card bg-pattern">

                            <div class="card-body p-4">
                                @if(Session::has('error'))
                                    <div class="alert alert-danger mb-3 text-center">
                                        {{ Session::get('error') }}
                                    </div>
                                @endif
                                <div class="text-center w-75 m-auto">
                                    <div class="auth-brand">
                                        <a href="index.html" class="logo logo-dark text-center">
                                            <span class="logo-lg">
                                                <img src="{{asset('/assets/backend/images/roomzhub-logo.png')}}" alt="" height="22">
                                            </span>
                                        </a>

                                        <a href="index.html" class="logo logo-light text-center">
                                            <span class="logo-lg">
                                                <img src="{{asset('/assets/backend/images/logo-light.png')}}" alt="" height="22">
                                            </span>
                                        </a>
                                    </div>
                                    <p class="text-dark mb-4 mt-3">Property Maintenance</p>
                                </div>

                                <form action="{{ route('loginPost') }}" method="POST">@csrf

                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email address</label>
                                        <input class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}" type="email" id="email" name="email" required="" placeholder="Enter your email" value="{{ old('email') }}">
                                        @if ($errors->has('email'))
                                            <div class="invalid-feedback">
                                                {{ $errors->first('email') }}
                                            </div>
                                        @endif
                                    </div>

                                    <div class="mb-3">
                                        <label for="password" class="form-label">Password</label>
                                        <div class="input-group input-group-merge">
                                            <input type="password" id="password" name="password" class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}" placeholder="Enter your password" value="{{old('password')}}">
                                            @if ($errors->has('password'))
                                                <div class="invalid-feedback">
                                                    {{ $errors->first('email') }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="checkbox-signin" checked>
                                            <label class="form-check-label" for="checkbox-signin">Remember me</label>
                                        </div>
                                    </div>

                                    <div class="text-center d-grid">
                                        <button class="btn btn-primary" type="submit"> Log In </button>
                                    </div>

                                </form>



                            </div> <!-- end card-body -->
                        </div>
                        <!-- end card -->

                        <div class="row mt-3 d-none">
                            <div class="col-12 text-center">
                                <p> <a href="auth-recoverpw.html" class="text-white-50 ms-1">Forgot your password?</a></p>
                                <p class="text-white-50">Don't have an account? <a href="auth-register.html" class="text-white ms-1"><b>Sign Up</b></a></p>
                            </div> <!-- end col -->
                        </div>
                        <!-- end row -->

                    </div> <!-- end col -->

                </div>
                <!-- end row -->
            </div>
            <!-- end container -->
        </div>
        <!-- end page -->


        <footer class="footer footer-alt">
            2024 - <script>document.write(new Date().getFullYear())</script> &copy; RoomzHub theme by <a href="#" class="text-white-50">CodeHq</a>
        </footer>

        <!-- Authentication js -->
        {{-- <script src="assets/js/pages/authentication.init.js"></script> --}}

    </body>

</html>
