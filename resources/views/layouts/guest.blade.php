<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>SIMBA - Sistem Informasi Barang dan Asset</title>

        <!-- Favicon -->
        <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/icons/simba.png') }}" />

        <!-- Preload gambar penting -->
        <link rel="preload" href="{{ asset('assets/img/backgrounds/Login.png') }}" as="image">
        <link rel="preload" href="{{ asset('assets/img/icons/upelkes.png') }}" as="image">
        <link rel="preload" href="{{ asset('assets/img/icons/simba.png') }}" as="image">

        <!-- Fonts & Icons -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link rel="preconnect" href="https://cdn.jsdelivr.net">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700;900&family=Poppins:wght@400;500&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <!-- Inline CSS untuk menghindari render blocking -->
        <style>
            html, body {
                margin:0;
                padding:0;
                height:100%;
                font-family:'Poppins',sans-serif;
            }
            .simba-title {
                font-family:'Montserrat',sans-serif;
                font-weight:900;
                color:#fff;
                text-transform:uppercase;
                letter-spacing:1.5px;
                text-shadow:0 2px 4px rgba(0,0,0,0.25),0 0 8px rgba(255,255,255,0.25);
            }
            @keyframes floatSmooth {
                0%, 100% { transform: translateY(0); }
                50% { transform: translateY(-10px); }
            }
            .animate-float-smooth {
                animation: floatSmooth 5s ease-in-out infinite;
            }
            @keyframes pulseSlow {
                0%, 100% { opacity: .3; transform: scale(1); }
                50% { opacity: .6; transform: scale(1.05); }
            }
            .animate-pulse-slow {
                animation: pulseSlow 7s ease-in-out infinite;
            }
            @keyframes shake {
                0%, 100% { transform: translateX(0); }
                25% { transform: translateX(-5px); }
                75% { transform: translateX(5px); }
            }
            .shake {
                animation: shake .3s ease;
                border-color: #ef4444 !important;
                box-shadow: 0 0 8px rgba(239,68,68,0.4);
            }
        </style>
    </head>
    <body class="font-sans antialiased">
        {{ $slot }}
    </body>
</html>