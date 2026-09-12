<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Workspace - Exa E-Approval</title>
    <link href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/sb-admin-2.min.css') }}" rel="stylesheet">
</head>
<body class="bg-gradient-primary">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-xl-8 col-lg-10">
                <div class="card shadow-lg border-0">
                    <div class="card-body p-4 p-md-5">
                        <div class="text-center mb-4">
                            <h1 class="h4 text-gray-800">Select Your Workspace</h1>
                            <p class="text-muted mb-0">Choose the organization and division you want to work in.</p>
                        </div>

                        <div class="row">
                            @foreach($accesses as $access)
                                <div class="col-md-6 mb-3">
                                    <form method="POST" action="{{ route('context.select') }}">
                                        @csrf
                                        <input type="hidden" name="access_id" value="{{ $access->id }}">
                                        <button type="submit" class="btn btn-light border w-100 text-left p-3 h-100">
                                            <div class="font-weight-bold text-primary">
                                                <i class="fas fa-building mr-2"></i>{{ $access->organization?->organization_name ?? '-' }}
                                            </div>
                                            <div class="small text-muted mt-2">
                                                <i class="fas fa-sitemap mr-2"></i>{{ $access->division?->division_name ?? '-' }}
                                            </div>
                                            <div class="small text-muted">
                                                <i class="fas fa-user-tag mr-2"></i>{{ $access->role?->role_name ?? '-' }}
                                            </div>
                                        </button>
                                    </form>
                                </div>
                            @endforeach
                        </div>

                        <form method="POST" action="{{ route('logout') }}" class="text-center mt-3">
                            @csrf
                            <button type="submit" class="btn btn-link text-secondary">Sign out</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
