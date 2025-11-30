<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'SLSU Scheduler') }}</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="font-sans antialiased" style="background: linear-gradient(135deg, #0C3B2E 0%, #1a5c47 50%, #6D9773 100%); min-height: 100vh;">
    
    <!-- Navigation -->
    @include('layouts.navigation')
    
    <!-- Page Heading -->
    @if (isset($header))
        <header class="shadow" style="background: rgba(255, 255, 255, 0.1); backdrop-filter: blur(10px);">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8" style="color: #fff;">
                {{ $header }}
            </div>
        </header>
    @endif
    
    <!-- Page Content -->
    <main>
        {{ $slot }}
    </main>
    
    @stack('scripts')
</body>
</html>