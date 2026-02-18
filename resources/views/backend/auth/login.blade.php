<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vacation Hub Admin Login</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #6e8efb, #a777e3);
            height: 100vh;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background: #fff;
            border-radius: 10px;
            padding: 2rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <h1 class="text-center mb-4" style="color:#1C9EA5; font-size: 3rem;"><strong>Fix & Fetch</strong></h1>
        @if(Session::has('error'))
            <div class="alert alert-danger mb-3 text-center">
                {{ Session::get('error') }}
            </div>
        @endif
        <form id="login-form" action="{{ route('loginPost') }}" method="POST">@csrf
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="text" name="email" class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}" id="email" placeholder="Enter your email" value="{{ old('email') }}" required>
                @if ($errors->has('email'))
                    <div class="invalid-feedback">
                        {{ $errors->first('email') }}
                    </div>
                @endif
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" name="password" class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}" id="password" placeholder="Enter your password" value="{{old('password')}}" required>
                @if ($errors->has('password'))
                    <div class="invalid-feedback">
                        {{ $errors->first('password') }}
                    </div>
                @endif
            </div>
            <button type="submit" class="btn btn-primary w-100">Login</button>
        </form>
    </div>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // $('#login-form').submit(function(e) {
        //     e.preventDefault();
        //     // Redirect to dashboard or show success (to be handled by backend)
        //     window.location.href = '../index.html';
        // });
    </script>
</body>
</html>
