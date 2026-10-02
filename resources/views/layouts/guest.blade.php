<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('partials.head')
</head>
<body class="font-sans antialiased text-gray-900 dark:text-gray-100">
    {{ $slot }}
    @livewireScripts
    @include('partials.flash')
</body>
</html>
