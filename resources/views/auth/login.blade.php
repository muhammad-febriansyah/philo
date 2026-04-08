<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <title>Login | {{ config('app.name') }}</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <!-- App favicon -->
        <link rel="shortcut icon" href="{{ asset('assets/images/favicon.ico') }}">
        <!-- Bootstrap Css -->
        <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
        <!-- Icons Css -->
        <link href="{{ asset('assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
        <!-- App Css-->
        <link href="{{ asset('assets/css/app.min.css') }}" rel="stylesheet" type="text/css" />
    </head>

    <body class="bg-primary bg-pattern">
        <div class="home-btn d-none d-sm-block">
            <a href="{{ route('home') }}"><i class="mdi mdi-home-variant h2 text-white"></i></a>
        </div>

        <div class="account-pages my-5 pt-sm-5">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="text-center mb-5">
                            <a href="{{ route('home') }}" class="logo">
                                <img src="{{ asset('assets/images/logo-light.png') }}" height="24" alt="logo">
                            </a>
                            <h5 class="font-size-16 text-white-50 mb-4">{{ config('app.name') }}</h5>
                        </div>
                    </div>
                </div>

                <div class="row justify-content-center">
                    <div class="col-xl-5 col-sm-8">
                        <div class="card">
                            <div class="card-body p-4">
                                <div class="p-2">
                                    <h5 class="mb-5 text-center">Sign in to continue to {{ config('app.name') }}.</h5>

                                    @if ($errors->any())
                                        <div class="alert alert-danger mb-4">
                                            @foreach ($errors->all() as $error)
                                                <div>{{ $error }}</div>
                                            @endforeach
                                        </div>
                                    @endif

                                    @if (session('status'))
                                        <div class="alert alert-success mb-4">
                                            {{ session('status') }}
                                        </div>
                                    @endif

                                    <form class="form-horizontal" method="POST" action="{{ route('login') }}">
                                        @csrf

                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group form-group-custom mb-4">
                                                    <input type="email" class="form-control @error('email') is-invalid @enderror"
                                                        id="email" name="email" value="{{ old('email') }}" required autofocus>
                                                    <label for="email">Email</label>
                                                </div>

                                                <div class="form-group form-group-custom mb-4">
                                                    <input type="password" class="form-control @error('password') is-invalid @enderror"
                                                        id="password" name="password" required>
                                                    <label for="password">Password</label>
                                                </div>

                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="custom-control custom-checkbox">
                                                            <input type="checkbox" class="custom-control-input" id="remember" name="remember">
                                                            <label class="custom-control-label" for="remember">Remember me</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="text-md-right mt-3 mt-md-0">
                                                            @if (Route::has('password.request'))
                                                                <a href="{{ route('password.request') }}" class="text-muted">
                                                                    <i class="mdi mdi-lock"></i> Forgot your password?
                                                                </a>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="mt-4">
                                                    <button class="btn btn-success d-block w-100 waves-effect waves-light" type="submit">
                                                        Log In
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- JAVASCRIPT -->
        <script src="{{ asset('assets/libs/jquery/jquery.min.js') }}"></script>
        <script src="{{ asset('assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
        <script src="{{ asset('assets/libs/metismenu/metisMenu.min.js') }}"></script>
        <script src="{{ asset('assets/libs/simplebar/simplebar.min.js') }}"></script>
        <script src="{{ asset('assets/libs/node-waves/waves.min.js') }}"></script>
        <script src="{{ asset('assets/js/app.js') }}"></script>
    </body>
</html>
