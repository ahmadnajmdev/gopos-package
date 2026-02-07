<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>{{ $title ?? 'Page Title' }}</title>

    <!-- Styles -->
    @vite('resources/css/app.css')
    <!-- Fonts -->
    <style>
        @font-face {
            font-family: 'Rabar';
            font-style: normal;
            src: url({{ asset('css/fonts/Rabar_021.ttf') }}) format('truetype');
        }

        body {
            font-family: 'Rabar', sans-serif;
        }
    </style>

</head>

<body>
    {{ $slot }}
</body>

</html>
