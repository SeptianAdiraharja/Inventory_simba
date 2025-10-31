<x-guest-layout>
    <div id="mainContainer"
        class="flex flex-col lg:flex-row min-h-screen h-screen bg-gray-100 overflow-hidden transition-all duration-700 ease-in-out">

        <!-- LEFT PANEL -->
        <div id="leftPanel"
            class="relative flex flex-col justify-center items-center w-full text-white
                px-8 sm:px-12 md:px-16 lg:px-20
                shadow-2xl overflow-hidden transition-all duration-700 ease-in-out
                bg-gradient-to-br from-[#6a00f4] via-[#7d0dfd] to-[#9b4dff]"
            style="
                position: relative;
                width: 100%;
                height: 100vh;
                background-image: url('{{ asset('assets/img/backgrounds/Login.png') }}');
                background-size: cover;
                background-position: center;
                background-repeat: no-repeat;
            ">

            <!-- Overlay ungu lembut -->
            <div class="absolute inset-0 bg-[#7d0dfd]/50 z-0"></div>

            <!-- Konten -->
            <div class="relative z-10 text-center max-w-lg px-4 py-8 sm:py-12 h-full flex flex-col justify-center">
                <div class="mb-10 drop-shadow-[0_2px_4px_rgba(0,0,0,0.8)]">
                    <h1 class="text-5xl sm:text-6xl font-extrabold tracking-wide text-white"
                        style="text-shadow:0 0 15px rgba(125,13,253,0.7),0 0 25px rgba(255,255,255,0.4);">
                        SIMBA
                    </h1>
                    <p class="text-base sm:text-lg text-gray-100 mt-2 font-medium">
                        Sistem Informasi Barang dan Aset
                    </p>
                </div>

                <!-- Kartu fitur -->
                <div class="space-y-5 bg-white/10 p-6 rounded-2xl shadow-lg border border-white/20 backdrop-blur-none">
                    <div class="flex items-start space-x-3">
                        <div class="text-2xl">🧾</div>
                        <div>
                            <h2 class="font-semibold text-white text-lg">Pendataan Aset & Barang</h2>
                            <p class="text-gray-100 text-base leading-relaxed">
                                Mendata seluruh kebutuhan barang dan aset operasional UPELKES secara akurat dan terpusat.
                            </p>
                        </div>
                    </div>
                    <div class="flex items-start space-x-3">
                        <div class="text-2xl">📊</div>
                        <div>
                            <h2 class="font-semibold text-white text-lg">Laporan & Monitoring Cepat</h2>
                            <p class="text-gray-100 text-base leading-relaxed">
                                Pantau kondisi dan ketersediaan barang dengan laporan real-time yang mudah dipahami.
                            </p>
                        </div>
                    </div>
                    <div class="flex items-start space-x-3">
                        <div class="text-2xl">🏢</div>
                        <div>
                            <h2 class="font-semibold text-white text-lg">Efisiensi Manajemen Unit</h2>
                            <p class="text-gray-100 text-base leading-relaxed">
                                Mendukung efisiensi pengelolaan kebutuhan pelatihan, fasilitas, dan logistik.
                            </p>
                        </div>
                    </div>
                    <div class="flex items-start space-x-3">
                        <div class="text-2xl">🔒</div>
                        <div>
                            <h2 class="font-semibold text-white text-lg">Keamanan & Integritas Data</h2>
                            <p class="text-gray-100 text-base leading-relaxed">
                                Menjamin keamanan data inventaris dengan sistem autentikasi transparan.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Tombol -->
                <div class="mt-10">
                    <button id="openLoginBtn"
                        class="px-8 py-3 bg-[#7d0dfd] hover:bg-[#750dfd] text-white font-semibold text-lg rounded-lg shadow-lg
                            hover:shadow-[0_0_20px_rgba(125,13,253,0.6)] transition-all duration-300">
                        ✨ Buka Halaman Login
                    </button>
                </div>
            </div>
        </div>

        <!-- RIGHT PANEL -->
        <div id="rightPanel"
            class="flex flex-col justify-center items-center bg-white p-8 text-gray-800 transform origin-right rotate-y-90 opacity-0
                   transition-all duration-[1000ms] ease-in-out w-full lg:w-1/2 h-full">

            <div class="max-w-md w-full space-y-8 text-center opacity-0 scale-95 transition-all duration-700" id="loginForm">
                <div class="flex justify-center">
                    <img src="{{ asset('assets/img/icons/simba.jpg') }}"
                        alt="SIMBA Logo"
                        class="w-20 h-20 sm:w-24 sm:h-24 rounded-full shadow-[0_0_30px_rgba(125,13,253,0.6)]">
                </div>

                <h2 class="text-3xl font-extrabold text-[#7d0dfd] mt-4"
                    style="text-shadow:0 0 10px rgba(125,13,253,0.6);">
                    Selamat Datang di <span class="text-[#6f42c1]">SIMBA</span>
                </h2>
                <p class="text-center text-gray-500 -mt-4 mb-4 text-sm">
                    Akses sistem pendataan barang dan aset UPELKES
                </p>

                <x-auth-session-status class="mb-4" :status="session('status')" />
                @if (session('error'))
                    <div class="bg-red-500 text-white px-4 py-2 rounded-md text-center shadow-md">
                        {{ session('error') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="space-y-6">
                    @csrf

                    <!-- Email -->
                    <div class="text-left">
                        <label for="email" class="block text-sm mb-1 font-medium text-gray-700">Email</label>
                        <input id="email" type="email" name="email" required autofocus
                            placeholder="contoh: user@upelkes.ac.id"
                            class="w-full px-4 py-3 rounded-lg bg-gray-50 border border-gray-300 focus:ring-2
                                   focus:ring-[#7d0dfd] focus:outline-none text-gray-800" />
                    </div>

                    <!-- Password -->
                    <div class="text-left">
                        <label for="password" class="block text-sm mb-1 font-medium text-gray-700">Password</label>
                        <input id="password" type="password" name="password" required minlength="8"
                            placeholder="Minimal 8 karakter"
                            class="w-full px-4 py-3 rounded-lg bg-gray-50 border border-gray-300 focus:ring-2
                                   focus:ring-[#7d0dfd] focus:outline-none text-gray-800" />
                    </div>

                    <div class="flex items-center justify-between text-sm">
                        <label class="flex items-center space-x-2">
                            <input type="checkbox" name="remember"
                                class="rounded text-[#7d0dfd] focus:ring-[#7d0dfd]">
                            <span class="text-gray-700">Ingat saya</span>
                        </label>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}"
                                class="text-[#7d0dfd] hover:underline font-medium">
                                Lupa password?
                            </a>
                        @endif
                    </div>

                    <button type="submit"
                        class="w-full bg-[#7d0dfd] text-white font-semibold py-3 rounded-lg hover:bg-[#750dfd] transition duration-200
                               shadow-md hover:shadow-[0_0_15px_rgba(125,13,253,0.6)]">
                        LOGIN
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- SCRIPT -->
    <script>
        const btn = document.getElementById('openLoginBtn');
        const leftPanel = document.getElementById('leftPanel');
        const rightPanel = document.getElementById('rightPanel');
        const mainContainer = document.getElementById('mainContainer');
        const loginForm = document.getElementById('loginForm');

        btn.addEventListener('click', () => {
            mainContainer.classList.add('lg:flex-row');
            leftPanel.classList.add('lg:w-1/2');
            rightPanel.classList.remove('rotate-y-90', 'opacity-0');
            rightPanel.classList.add('rotate-y-0', 'opacity-100');

            btn.classList.add('scale-0', 'opacity-0');

            setTimeout(() => {
                loginForm.classList.remove('opacity-0', 'scale-95');
                loginForm.classList.add('opacity-100', 'scale-100');
            }, 500);
        });
    </script>

    <style>
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }
        .rotate-y-90 {
            transform: perspective(1000px) rotateY(90deg);
        }
        .rotate-y-0 {
            transform: perspective(1000px) rotateY(0deg);
        }
        #rightPanel {
            transform-style: preserve-3d;
            backface-visibility: hidden;
        }

        html, body {
        margin: 0;
        padding: 0;
        height: 100%;
        overflow-x: hidden;
        }
        #mainContainer {
            height: 100vh;
        }
        #leftPanel {
            min-height: 100vh !important;
        }
    </style>
</x-guest-layout>
