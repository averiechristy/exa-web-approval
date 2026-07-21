<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Login</title>

    <!-- Custom fonts for this template-->
    <link href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet" type="text/css">    
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="{{ asset('css/sb-admin-2.min.css') }}" rel="stylesheet">

</head>

<body class="bg-gradient-primary">

    <div class="container vh-100 d-flex align-items-center justify-content-center">

        <div class="col-xl-6 col-lg-7 col-md-8">

            <div class="card border-0 shadow-lg">
                <div class="card-body p-5">

                    <div class="text-center mb-4">
                        <h1 class="h3 text-gray-900">Login</h1>
                    </div>
                                            {{-- ERROR MESSAGE --}}
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                {{ $errors->first() }}
                            </div>
                        @endif

                    <form method="POST" action="{{ route('login.post') }}">
                        @csrf

                        <div class="form-group mb-4">
                            <input
                                type="text"
                                name="username"
                                class="form-control form-control-user"
                                value="{{ old('username') }}"
                                placeholder="Username"
                                required
                            >
                        </div>

                        <div class="form-group mb-4 position-relative">
                            <input
                                type="password"
                                name="password"
                                id="password"
                                class="form-control form-control-user pr-5"
                                placeholder="Password"
                                required
                            >

                            <span
                                id="togglePassword"
                                style="position:absolute; right:18px; top:50%; transform:translateY(-50%); cursor:pointer; color:#6c757d;"
                            >
                                <i class="fas fa-eye"></i>
                            </span>
                        </div>

                        <button type="submit" class="btn btn-primary btn-user btn-block">
                            Login
                        </button>
                    </form>

                </div>
            </div>

        </div>

    </div>

</body>

<script>
    const password = document.getElementById('password');
    const togglePassword = document.getElementById('togglePassword');

    togglePassword.addEventListener('click', function () {
        const icon = this.querySelector('i');

        if (password.type === 'password') {
            password.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            password.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    });
</script>

</html>