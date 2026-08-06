<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Exa E-Approval Login">
    <meta name="author" content="">

    <title>Login - Exa E-Approval</title>

    <!-- FontAwesome & Google Fonts -->
    <link href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Custom styles for SB Admin 2 -->
    <link href="{{ asset('css/sb-admin-2.min.css') }}" rel="stylesheet">

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
            min-height: 100vh;
        }

        .login-card {
            border: none;
            border-radius: 1.25rem;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
        }

        .brand-icon {
            width: 60px;
            height: 60px;
            background: rgba(78, 115, 223, 0.1);
            color: #4e73df;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
            margin-bottom: 1rem;
        }

        .input-group-custom {
            position: relative;
        }

        .input-group-custom .input-icon-left {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: #a0aec0;
            z-index: 10;
            transition: color 0.2s ease;
        }

        .form-control-custom {
            height: 52px;
            padding-left: 48px !important;
            padding-right: 48px !important;
            border-radius: 0.75rem !important;
            border: 1.5px solid #e2e8f0;
            font-size: 0.95rem;
            transition: all 0.2s ease;
        }

        .form-control-custom:focus {
            border-color: #4e73df;
            box-shadow: 0 0 0 4px rgba(78, 115, 223, 0.15);
        }

        .form-control-custom:focus + .input-icon-left {
            color: #4e73df;
        }

        .toggle-password {
            position: absolute;
            right: 18px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #a0aec0;
            z-index: 10;
            transition: color 0.2s ease;
        }

        .toggle-password:hover {
            color: #4e73df;
        }

        .btn-custom {
            height: 50px;
            border-radius: 0.75rem;
            font-weight: 600;
            font-size: 1rem;
            letter-spacing: 0.3px;
            box-shadow: 0 4px 12px rgba(78, 115, 223, 0.3);
            transition: all 0.2s ease;
        }

        .btn-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(78, 115, 223, 0.4);
        }

        .alert-custom {
            border-radius: 0.75rem;
            border: none;
            font-size: 0.9rem;
        }
    </style>
</head>

<body class="d-flex align-items-center justify-content-center min-vh-100 py-5">

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-5 col-lg-6 col-md-8 col-sm-10">

                <div class="card login-card">
                    <div class="card-body p-4 p-sm-5">

                        <!-- Header -->
                        <div class="text-center mb-4">
                            <div class="brand-icon">
                                <i class="fas fa-file-signature"></i>
                            </div>
                            <h2 class="h4 font-weight-bold text-gray-900 mb-1">Welcome!</h2>
                            <p class="text-muted small">Exa E-Approval System</p>
                        </div>

                        <!-- Error Message -->
                        @if ($errors->any())
                            <div class="alert alert-danger alert-custom d-flex align-items-center mb-4" role="alert">
                                <i class="fas fa-exclamation-circle mr-2"></i>
                                <div>{{ $errors->first() }}</div>
                            </div>
                        @endif

                        <!-- Form Login -->
                        <form method="POST" action="{{ route('login.post') }}">
                            @csrf

                            <!-- Username Input -->
                            <div class="form-group mb-3">
                                <label class="small font-weight-bold text-gray-700">Username</label>
                                <div class="input-group-custom">
                                    <i class="fas fa-user input-icon-left"></i>
                                    <input 
                                        type="text" 
                                        name="username" 
                                        class="form-control form-control-custom" 
                                        value="{{ old('username') }}" 
                                        placeholder="Masukkan username" 
                                        required 
                                        autofocus
                                    >
                                </div>
                            </div>

                            <!-- Password Input -->
                            <div class="form-group mb-4">
                                <label class="small font-weight-bold text-gray-700">Password</label>
                                <div class="input-group-custom">
                                    <i class="fas fa-lock input-icon-left"></i>
                                    <input 
                                        type="password" 
                                        name="password" 
                                        id="password" 
                                        class="form-control form-control-custom" 
                                        placeholder="Masukkan password" 
                                        required
                                    >
                                    <span id="togglePassword" class="toggle-password">
                                        <i class="fas fa-eye"></i>
                                    </span>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <button type="submit" class="btn btn-primary btn-block btn-custom">
                                Sign In <i class="fas fa-arrow-right ml-2"></i>
                            </button>
                        </form>

                        <!-- Footer Note -->
                        <div class="text-center mt-4">
                            <small class="text-muted">&copy; {{ date('Y') }} Exa E-Approval. All rights reserved.</small>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>

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
</body>

</html>