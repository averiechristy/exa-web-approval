<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title', 'Dashboard')</title>

    <!-- Fonts -->
    <link href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">

    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.min.js"></script>
    <!-- CSS -->
    <link href="{{ asset('css/sb-admin-2.min.css') }}" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        .sidebar .nav-item .inbox-nav-link {
            display: flex !important;
            align-items: center;
            gap: 6px;
        }

        .sidebar .nav-item .inbox-nav-link > span:not(.inbox-unread-badge) {
            flex: 1 1 auto;
        }

        .sidebar .nav-item .inbox-nav-link .inbox-unread-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 22px !important;
            min-width: 22px !important;
            height: 22px !important;
            margin-left: auto !important;
            padding: 0 !important;
            border-radius: 50% !important;
            background: #e74a3b !important;
            color: #fff !important;
            font-size: 10px !important;
            font-weight: 700 !important;
            line-height: 1 !important;
            flex: 0 0 22px;
        }

        .sidebar .nav-item .inbox-nav-link .inbox-unread-badge.wide {
            width: auto !important;
            min-width: 28px !important;
            padding: 0 5px !important;
            border-radius: 11px !important;
            flex-basis: auto;
        }
    </style>

    @stack('styles')
</head>

<body id="page-top">

<div id="wrapper">

    {{-- SIDEBAR --}}
    @include('layouts.sidebar')

    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content">

            {{-- TOPBAR --}}
            @include('layouts.topbar')

            {{-- MAIN CONTENT --}}
            <div class="container-fluid">
                @yield('content')
            </div>

        </div>
    </div>

</div>

{{-- JS --}}
<script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('js/sb-admin-2.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

 @stack('modals')
@stack('scripts')

</body>
</html>