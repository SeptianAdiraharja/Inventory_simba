<x-guest-layout>
    <head>
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700;800&family=Poppins:wght@400;500&display=swap" rel="stylesheet">
    </head>

    <div id="mainContainer"
        class="flex flex-col lg:flex-row min-h-screen bg-gray-100 overflow-hidden transition-all duration-700 ease-in-out">

        <!-- LEFT PANEL -->
        <div id="leftPanel"
            class="relative flex flex-col justify-center items-center text-white px-6 sm:px-10 md:px-16 lg:px-24
                   w-full lg:w-[60%] bg-cover bg-center bg-no-repeat shadow-2xl overflow-hidden"
            style="background-image: url('{{ asset('assets/img/backgrounds/Login.png') }}');">

            <!-- Overlay -->
            <div class="absolute inset-0 bg-gradient-to-br from-[#6a00f4]/70 via-[#7d0dfd]/60 to-[#9b4dff]/50 z-0 transition-all duration-700 ease-in-out"></div>

            <!-- Konten -->
            <div class="relative z-10 text-center max-w-lg flex flex-col justify-center h-full space-y-8 sm:space-y-10 py-10">
                <div class="transition-all duration-700 ease-out">
                    <h1 class="text-[clamp(2.5rem,5vw,4.5rem)] font-extrabold tracking-wide text-white simba-title animate-glow">
                        SIMBA
                    </h1>
                    <p class="text-[clamp(1rem,1.5vw,1.25rem)] text-gray-100 mt-2 font-medium">
                        Sistem Informasi Barang dan Aset
                    </p>
                </div>

                <!-- Fitur -->
                <div class="bg-white/10 backdrop-blur-lg p-6 sm:p-8 rounded-2xl border border-white/20 shadow-2xl space-y-5 text-left hover:scale-[1.02] transition-transform duration-700 ease-out">
                    @php
                        $features = [
                            ['icon' => '🧾', 'title' => 'Pendataan Aset & Barang', 'desc' => 'Mendata seluruh kebutuhan barang dan aset operasional UPELKES secara akurat dan terpusat.'],
                            ['icon' => '📊', 'title' => 'Laporan & Monitoring Cepat', 'desc' => 'Pantau kondisi dan ketersediaan barang dengan laporan real-time yang mudah dipahami.'],
                            ['icon' => '🏢', 'title' => 'Efisiensi Manajemen Unit', 'desc' => 'Mendukung efisiensi pengelolaan kebutuhan pelatihan, fasilitas, dan logistik.'],
                            ['icon' => '🔒', 'title' => 'Keamanan & Integritas Data', 'desc' => 'Menjamin keamanan data inventaris dengan sistem autentikasi transparan.'],
                        ];
                    @endphp

                    @foreach ($features as $feature)
                        <div class="flex items-start space-x-3 transition-all duration-500 ease-in-out hover:translate-x-1">
                            <div class="text-2xl">{{ $feature['icon'] }}</div>
                            <div>
                                <h2 class="font-semibold text-white text-lg">{{ $feature['title'] }}</h2>
                                <p class="text-gray-100 text-sm leading-relaxed">{{ $feature['desc'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Tombol -->
                <button id="openLoginBtn"
                    class="px-8 py-3 bg-[#7d0dfd] hover:bg-[#6f00fc] text-white font-semibold text-lg rounded-lg shadow-lg
                           hover:shadow-[0_0_25px_rgba(125,13,253,0.7)] transition-all duration-700 ease-[cubic-bezier(0.4,0,0.2,1)] transform hover:scale-105 focus:outline-none focus:ring-4 focus:ring-[#7d0dfd]/40">
                    ✨ Buka Halaman Login
                </button>
            </div>
        </div>

        <!-- RIGHT PANEL -->
        <div id="rightPanel"
            class="relative flex flex-col justify-center items-center bg-white text-gray-800 w-full lg:w-[40%]
                   transition-all duration-1000 ease-[cubic-bezier(0.4,0,0.2,1)] overflow-visible py-16">

            <!-- Logo UPELKES -->
            <div id="upelkesLogo"
                class="flex flex-col justify-center items-center relative w-full py-10 transition-all duration-[1200ms] ease-out">
                <div class="relative flex justify-center items-center">
                    <div class="absolute w-[400px] h-[400px] bg-[#7d0dfd]/10 blur-3xl rounded-full hidden sm:block animate-pulse-slow"></div>
                    <img src="{{ asset('assets/img/icons/upelkes.png') }}"
                         alt="Logo UPELKES"
                         class="relative w-[clamp(180px,30vw,350px)] h-auto object-contain drop-shadow-[0_0_35px_rgba(125,13,253,0.4)] animate-float-smooth">
                </div>
            </div>

            <!-- FORM LOGIN -->
            <div id="loginForm"
                class="max-w-md w-full space-y-8 text-center opacity-0 scale-90 hidden translate-y-10 transition-all duration-[1400ms] ease-[cubic-bezier(0.4,0,0.2,1)] px-6 sm:px-8 pb-10">
                <div class="flex justify-center">
                    <img src="{{ asset('assets/img/icons/simba.jpg') }}" alt="SIMBA Logo"
                        class="w-20 h-20 sm:w-24 sm:h-24 rounded-full shadow-[0_0_30px_rgba(125,13,253,0.6)]">
                </div>

                <h2 class="text-3xl font-extrabold text-[#7d0dfd] mt-4"
                    style="text-shadow:0 0 10px rgba(125,13,253,0.6);">
                    Selamat Datang di <span class="text-[#6f42c1]">SIMBA</span>
                </h2>
                <p class="text-center text-gray-500 -mt-4 mb-4 text-sm">
                    Akses sistem pendataan barang dan aset UPELKES
                </p>

                <form method="POST" action="{{ route('login') }}" class="space-y-6 text-left">
                    @csrf
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input id="email" type="email" name="email" required autofocus
                            placeholder="contoh: user@upelkes.go.id"
                            class="w-full px-4 py-3 rounded-lg bg-gray-50 border border-gray-300 focus:ring-2 focus:ring-[#7d0dfd] focus:outline-none text-gray-800 transition-all duration-300 ease-in-out">
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                        <input id="password" type="password" name="password" required minlength="8"
                            placeholder="Minimal 8 karakter"
                            class="w-full px-4 py-3 rounded-lg bg-gray-50 border border-gray-300 focus:ring-2 focus:ring-[#7d0dfd] focus:outline-none text-gray-800 transition-all duration-300 ease-in-out">
                    </div>

                    <div class="flex items-center justify-between text-sm">
                        <label class="flex items-center space-x-2">
                            <input type="checkbox" name="remember" class="rounded text-[#7d0dfd] focus:ring-[#7d0dfd]">
                            <span class="text-gray-700">Ingat saya</span>
                        </label>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="text-[#7d0dfd] hover:underline font-medium">
                                Lupa password?
                            </a>
                        @endif
                    </div>

                    <button type="submit"
                        class="w-full bg-[#7d0dfd] text-white font-semibold py-3 rounded-lg hover:bg-[#6f00fc]
                               shadow-md hover:shadow-[0_0_20px_rgba(125,13,253,0.6)] transition-all duration-500 ease-in-out transform hover:scale-[1.02]">
                        LOGIN
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- SCRIPT -->
    <script>
        const btn = document.getElementById('openLoginBtn');
        const upelkesLogo = document.getElementById('upelkesLogo');
        const loginForm = document.getElementById('loginForm');

        btn.addEventListener('click', () => {
            btn.classList.add('opacity-0', 'scale-90', 'pointer-events-none');
            btn.style.transition = 'all 800ms ease';

            setTimeout(() => {
                upelkesLogo.style.filter = 'blur(12px)';
                upelkesLogo.classList.add('opacity-0', 'scale-90');
            }, 400);

            setTimeout(() => {
                upelkesLogo.classList.add('hidden');
                loginForm.classList.remove('hidden', 'translate-y-10');
                loginForm.classList.add('opacity-100', 'scale-100', 'translate-y-0');
            }, 1200);
        });
    </script>

    <!-- STYLE -->
    <style>
        html, body {
            margin: 0;
            padding: 0;
            height: 100%;
            font-family: 'Poppins', sans-serif;
        }

        .simba-title {
            font-family: 'Montserrat', sans-serif;
            text-shadow: 0 0 12px rgba(125, 13, 253, 0.6),
                         0 0 24px rgba(155, 77, 255, 0.4),
                         0 0 36px rgba(155, 77, 255, 0.3);
        }

        @keyframes glowPulse {
            0%, 100% { text-shadow: 0 0 12px rgba(125,13,253,0.6), 0 0 25px rgba(155,77,255,0.4); }
            50% { text-shadow: 0 0 30px rgba(125,13,253,0.8), 0 0 50px rgba(155,77,255,0.6); }
        }
        .animate-glow { animation: glowPulse 3s ease-in-out infinite; }

        @keyframes floatSmooth {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-12px); }
        }
        .animate-float-smooth { animation: floatSmooth 5s ease-in-out infinite; }

        @keyframes pulseSlow {
            0%, 100% { opacity: 0.3; transform: scale(1); }
            50% { opacity: 0.6; transform: scale(1.05); }
        }
        .animate-pulse-slow { animation: pulseSlow 6s ease-in-out infinite; }

        /* Responsif */
        @media (max-width: 1024px) {
            #mainContainer { flex-direction: column; overflow-y: auto; }
            #leftPanel, #rightPanel { width: 100%; height: auto; padding: 3rem 1.5rem; }
            #upelkesLogo { padding: 2rem 0; }
            #upelkesLogo img { width: 180px; }
        }

        @media (max-width: 640px) {
            .simba-title { font-size: 2.2rem; }
            #upelkesLogo img { width: 150px; }
        }
    </style>
</x-guest-layout>
