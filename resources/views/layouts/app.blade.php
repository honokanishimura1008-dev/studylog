<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'MuscleLog') }}</title>

        <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css" rel="stylesheet">
        <link href="{{ asset('css/musslelog.css') }}" rel="stylesheet">

        @stack('styles')
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body>
        @include('layouts.navigation')

        @isset($header)
            <header class="border-bottom" style="border-color: var(--line) !important;">
                <div class="container py-3">
                    {{ $header }}
                </div>
            </header>
        @endisset

        @yield('content')
        {{ $slot ?? '' }}

        <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.bundle.min.js"></script>
        @stack('scripts')
    </body>
</html>
