<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ in_array(app()->getLocale(), ['ar', 'ckb']) ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? __('GoPOS Installation') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    <style>
        [x-cloak] { display: none !important; }

        @font-face {
            font-family: 'Rabar';
            font-style: normal;
            src: url({{ asset('css/fonts/Rabar_021.ttf') }}) format('truetype');
        }

        :root {
            --primary-50: 250 245 255;
            --primary-100: 243 232 255;
            --primary-200: 233 213 255;
            --primary-300: 216 180 254;
            --primary-400: 192 132 252;
            --primary-500: 168 85 247;
            --primary-600: 147 51 234;
            --primary-700: 126 34 206;
            --primary-800: 107 33 168;
            --primary-900: 88 28 135;
            --primary-950: 59 7 100;
        }

        body {
            font-family: 'Rabar', 'Inter', sans-serif;
        }

        .install-gradient {
            background: linear-gradient(135deg, rgb(var(--primary-100)) 0%, rgb(var(--primary-200)) 50%, rgb(var(--primary-100)) 100%);
        }

        .dark .install-gradient {
            background: linear-gradient(135deg, rgb(17 24 39) 0%, rgb(var(--primary-950)) 50%, rgb(17 24 39) 100%);
        }
    </style>
</head>
<body class="antialiased install-gradient min-h-screen">
    {{ $slot }}

    @livewireScripts
</body>
</html>
