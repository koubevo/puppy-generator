<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#1a1a2e">

    <title>@yield('title', 'Puppy Updates')</title>

    {{-- PWA --}}
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="/icons/icon-192.png">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-bg-primary text-text-primary min-h-screen">
    @yield('content')
</body>

</html>