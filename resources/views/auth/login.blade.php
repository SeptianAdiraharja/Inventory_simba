<x-guest-layout>
    <head>
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700;900&family=Poppins:wght@400;500&display=swap" rel="stylesheet">
    </head>

    <div id="mainContainer"
        class="flex flex-col lg:flex-row min-h-screen bg-gray-100 overflow-hidden transition-all duration-700 ease-in-out">

        <!-- LEFT PANEL -->
        <div id="leftPanel"
            class="relative flex flex-col justify-center items-center text-white px-6 sm:px-10 md:px-16 lg:px-24
                   w-full lg:w-[60%] bg-cover bg-center bg-no-repeat shadow-2xl overflow-hidden"
            style="background-image: url('{{ asset('assets/img/backgrounds/Login.png') }}');">
            <div class="relative z-10 text-center max-w-lg flex flex-col justify-center h-full space-y-8 sm:space-y-10 py-10">
                <div class="transition-all duration-700 ease-out">
                    <h1 class="text-[clamp(2.5rem,5vw,4.5rem)] simba-title">SIMBA</h1>
                </div>

                <div class="bg-black/30 backdrop-blur-lg p-6 sm:p-8 rounded-2xl border border-white/70 shadow-2xl space-y-5 text-left hover:scale-[1.02] transition-transform duration-700 ease-out">
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

                <button id="openLoginBtn"
                    class="px-8 py-3 bg-[#ff7a00] hover:bg-[#ff9500] text-white font-semibold text-lg rounded-lg shadow-lg
                           hover:shadow-[0_0_20px_rgba(255,122,0,0.4)] transition-all duration-700 ease-[cubic-bezier(0.4,0,0.2,1)] transform hover:scale-105 focus:outline-none focus:ring-4 focus:ring-[#ff9500]/30">
                    ✨ Masuk Yuk
                </button>
            </div>
        </div>

        <!-- RIGHT PANEL -->
        <div id="rightPanel"
            class="relative flex flex-col justify-center items-center bg-white text-gray-800 w-full lg:w-[40%]
                   transition-all duration-1000 ease-[cubic-bezier(0.4,0,0.2,1)] overflow-visible py-16">

            <div id="upelkesLogo"
                class="flex flex-col justify-center items-center relative w-full py-10 transition-all duration-[1200ms] ease-out">
                <div class="relative flex justify-center items-center">
                    <div class="absolute w-[400px] h-[400px] bg-[#ff9500]/10 blur-3xl rounded-full hidden sm:block animate-pulse-slow"></div>
                    <img src="{{ asset('assets/img/icons/upelkes.png') }}"
                         alt="Logo UPELKES"
                         class="relative w-[clamp(180px,30vw,350px)] h-auto object-contain drop-shadow-[0_0_35px_rgba(255,122,0,0.4)] animate-float-smooth">
                </div>
            </div>

            <div id="loginForm"
                class="max-w-md w-full space-y-8 text-center opacity-0 scale-90 hidden translate-y-10 transition-all duration-[1400ms] ease-[cubic-bezier(0.4,0,0.2,1)] px-6 sm:px-8 pb-10">

                <!-- ALERT BOX -->
                <div id="alertBox"
                    class="hidden relative px-4 py-3 mb-4 rounded-lg text-sm font-medium text-white transition-all duration-[1500ms] ease-in-out opacity-0 transform -translate-y-6 shadow-md">
                </div>

                <div class="flex justify-center">
                    <img src="{{ asset('assets/img/icons/simba.png') }}" alt="SIMBA Logo"
                        class="w-20 h-20 sm:w-24 sm:h-24 rounded-full shadow-[0_0_25px_rgba(255,122,0,0.5)]">
                </div>

                <h2 class="text-3xl font-extrabold text-[#ff7a00] mt-4" style="text-shadow:0 0 6px rgba(255,122,0,0.4);">
                    Selamat Datang di <span class="text-[#ff9500]">SIMBA</span>
                </h2>
                <p class="text-center text-gray-500 -mt-2 mb-4 text-sm">
                    Masuk ke sistem dengan akun UPELKES Anda.
                </p>

                <form id="ajaxLoginForm" class="space-y-6 text-left">
                    @csrf
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input id="email" type="email" name="email" required autofocus
                            placeholder="contoh: user@upelkes.go.id"
                            class="w-full px-4 py-3 rounded-lg bg-gray-50 border border-gray-300 focus:ring-2 focus:ring-[#ff7a00] focus:outline-none text-gray-800 transition-all duration-300 ease-in-out">
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                        <input id="password" type="password" name="password" required minlength="8"
                            placeholder="Minimal 8 karakter"
                            class="w-full px-4 py-3 rounded-lg bg-gray-50 border border-gray-300 focus:ring-2 focus:ring-[#ff7a00] focus:outline-none text-gray-800 transition-all duration-300 ease-in-out">
                    </div>

                    <div class="flex items-center justify-between text-sm">
                        <label class="flex items-center space-x-2">
                            <input type="checkbox" name="remember" class="rounded text-[#ff7a00] focus:ring-[#ff7a00]">
                            <span class="text-gray-700">Ingat saya</span>
                        </label>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="text-[#ff7a00] hover:underline font-medium">
                                Lupa password?
                            </a>
                        @endif
                    </div>

                    <button id="loginSubmitBtn" type="submit"
                        class="w-full bg-[#ff7a00] text-white font-semibold py-3 rounded-lg hover:bg-[#ff9500]
                               shadow-md hover:shadow-[0_0_15px_rgba(255,122,0,0.4)] transition-all duration-500 ease-in-out transform hover:scale-[1.02]">
                        MASUK
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

        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('ajaxLoginForm');
            const alertBox = document.getElementById('alertBox');
            const submitBtn = document.getElementById('loginSubmitBtn');

            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                hideAlert();
                submitBtn.disabled = true;
                submitBtn.textContent = 'Memproses...';

                try {
                    const response = await fetch("{{ route('login') }}", {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            email: form.email.value,
                            password: form.password.value,
                            remember: form.remember.checked,
                        }),
                    });

                    const data = await response.json();

                    if (response.ok && data.success) {
                        showAlert('success', data.message || 'Login berhasil!');
                        // ✅ Langsung redirect ke dashboard tanpa animasi fade
                        setTimeout(() => {
                            window.location.href = data.redirect;
                        }, 800);
                    } else {
                        showAlert('danger', data.message || 'Email atau password salah.');
                        shakeInputs();
                    }
                } catch (err) {
                    showAlert('info', 'Terjadi kesalahan jaringan.');
                } finally {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'MASUK';
                }
            });

            function showAlert(type, message) {
                alertBox.textContent = message;
                alertBox.className = `relative px-4 py-3 mb-4 rounded-lg text-sm font-medium text-white shadow-md opacity-100 translate-y-0 transition-all duration-700`;
                alertBox.classList.add({
                    success: 'bg-green-500',
                    danger: 'bg-red-500',
                    warning: 'bg-yellow-400 text-gray-900',
                    info: 'bg-blue-500'
                }[type] || 'bg-gray-500');
                setTimeout(hideAlert, 5000);
            }

            function hideAlert() {
                alertBox.classList.add('opacity-0', '-translate-y-6');
                setTimeout(() => alertBox.classList.add('hidden'), 1500);
            }

            function shakeInputs() {
                form.querySelectorAll('input').forEach(input => {
                    input.classList.add('shake');
                    setTimeout(() => input.classList.remove('shake'), 500);
                });
            }

            async function refreshCsrfToken() {
                try {
                    const response = await fetch("{{ url('/csrf-token') }}", {
                        method: 'GET',
                        credentials: 'same-origin',
                        headers: { 'Accept': 'application/json' },
                    });
                    if (response.ok) {
                        const data = await response.json();
                        document.querySelector('input[name="_token"]').value = data.token;
                        console.log('🔄 CSRF token diperbarui otomatis');
                    }
                } catch (error) {
                    console.warn('⚠️ Gagal memperbarui token CSRF:', error);
                }
            }
            // Refresh token tiap 25 menit
            setInterval(refreshCsrfToken, 25 * 60 * 1000);
        });
    </script>

    <style>
        html, body { margin:0; padding:0; height:100%; font-family:'Poppins',sans-serif; }
        .simba-title { font-family:'Montserrat',sans-serif; font-weight:900; color:#fff; text-transform:uppercase; letter-spacing:1.5px; text-shadow:0 2px 4px rgba(0,0,0,0.25),0 0 8px rgba(255,255,255,0.25); }
        @keyframes floatSmooth{0%,100%{transform:translateY(0)}50%{transform:translateY(-10px)}}
        .animate-float-smooth{animation:floatSmooth 5s ease-in-out infinite;}
        @keyframes pulseSlow{0%,100%{opacity:.3;transform:scale(1)}50%{opacity:.6;transform:scale(1.05)}}
        .animate-pulse-slow{animation:pulseSlow 7s ease-in-out infinite;}
        @keyframes shake{0%,100%{transform:translateX(0)}25%{transform:translateX(-5px)}75%{transform:translateX(5px)}}
        .shake{animation:shake .3s ease;border-color:#ef4444!important;box-shadow:0 0 8px rgba(239,68,68,0.4);}
    </style>
</x-guest-layout>
