<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'MuscleLog') }}</title>

        <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css" rel="stylesheet">
        <link href="{{ asset('css/musslelog.css') }}" rel="stylesheet">
    </head>
    <body class="d-flex flex-column min-vh-100">
        <div class="flex-grow-1 d-flex align-items-center justify-content-center py-5 px-3">
            <div class="w-100" style="max-width: 28rem;">
                <div class="text-center mb-4">
                    <a href="{{ route('login') }}" class="navbar-brand text-decoration-none fs-3">MuscleLog</a>
                </div>

                <div class="panel p-4">
                    @yield('content')
                </div>
            </div>
        </div>

        <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.bundle.min.js"></script>
        @stack('scripts')
    </body>
</html>
