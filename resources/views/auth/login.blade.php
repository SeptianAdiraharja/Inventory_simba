<x-guest-layout>
    <div class="min-h-screen flex bg-gray-100">
        <!-- LEFT PANEL -->
        <div class="hidden lg:flex w-1/2 bg-indigo-600 text-white flex-col justify-center items-start px-20 py-16 rounded-r-3xl shadow-2xl">
            <div class="flex items-center mb-10">
                <img src="{{ asset('assets/img/icons/simba.jpg') }}" alt="SIMBA Logo" class="w-24 h-24 mr-6 rounded-full shadow-lg border-4 border-white">
                <div>
                    <h1 class="text-4xl font-extrabold tracking-wide">SIMBA</h1>
                    <p class="text-sm text-indigo-200 mt-1">Sistem Informasi Barang dan Aset</p>
                </div>
            </div>

            <div class="space-y-6">
                <div>
                    <h2 class="font-semibold text-pink-300 text-lg">🧩 Terorganisir & Terkendali</h2>
                    <p class="text-indigo-100">Kelola inventaris dengan mudah dan efisien.</p>
                </div>
                <div>
                    <h2 class="font-semibold text-pink-300 text-lg">📊 Laporan Instan</h2>
                    <p class="text-indigo-100">Dapatkan statistik dan laporan hanya dengan satu klik.</p>
                </div>
                <div>
                    <h2 class="font-semibold text-pink-300 text-lg">🌍 Akses Dimana Saja</h2>
                    <p class="text-indigo-100">Pantau aset kapanpun dan dimanapun kamu berada.</p>
                </div>
                <div>
                    <h2 class="font-semibold text-pink-300 text-lg">🔒 Aman & Terlindungi</h2>
                    <p class="text-indigo-100">Data terjaga dengan sistem keamanan berlapis.</p>
                </div>
            </div>
        </div>

        <!-- RIGHT PANEL -->
        <div class="flex flex-col justify-center items-center w-full lg:w-1/2 bg-white p-10 text-gray-800">
            <div class="max-w-md w-full space-y-8">
                <h2 class="text-3xl font-extrabold text-center text-indigo-700 mb-8">Selamat Datang Kembali!</h2>

                <!-- Session Status -->
                <x-auth-session-status class="mb-4" :status="session('status')" />
                @if (session('error'))
                    <div class="bg-red-500 text-white px-4 py-2 rounded-md text-center shadow-md">
                        {{ session('error') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="space-y-6">
                    @csrf

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm mb-1 font-medium text-gray-700">Email</label>
                        <input id="email" type="email" name="email" :value="old('email')" required autofocus autocomplete="username"
                            class="w-full px-4 py-3 rounded-lg bg-gray-50 border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none text-gray-800" />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-sm mb-1 font-medium text-gray-700">Password</label>
                        <input id="password" type="password" name="password" required autocomplete="current-password"
                            class="w-full px-4 py-3 rounded-lg bg-gray-50 border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none text-gray-800" />
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <!-- Remember Me -->
                    <div class="flex items-center justify-between text-sm">
                        <label class="flex items-center space-x-2">
                            <input type="checkbox" name="remember" class="rounded text-indigo-600 focus:ring-indigo-500">
                            <span class="text-gray-700">Ingat saya</span>
                        </label>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="text-pink-500 hover:underline font-medium">
                                Lupa password?
                            </a>
                        @endif
                    </div>

                    <!-- Login Button -->
                    <button type="submit"
                        class="w-full bg-indigo-600 text-white font-semibold py-3 rounded-lg hover:bg-indigo-700 transition duration-200 shadow-md">
                        LOGIN
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-guest-layout>
