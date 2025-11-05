<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <h2 class="font-bold text-2xl text-[#f39c12] tracking-tight flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                     stroke-width="1.8" stroke="currentColor" class="w-7 h-7 text-[#f39c12]">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.5 20.25a8.25 8.25 0 1115 0v.75H4.5v-.75z"/>
                </svg>
                Profil Pengguna
            </h2>

            <!-- Tombol Kembali -->
            @php
                $role = Auth::user()->role;
                $dashboardRoutes = [
                    'super_admin' => 'super_admin.dashboard',
                    'admin' => 'admin.dashboard',
                    'pegawai' => 'pegawai.dashboard',
                ];
            @endphp

            @if(isset($dashboardRoutes[$role]))
                <a href="{{ route($dashboardRoutes[$role]) }}"
                   class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-[#f39c12] to-[#e67e22] text-white font-semibold rounded-lg shadow-md hover:shadow-[0_0_20px_rgba(243,156,18,0.4)] transition-all duration-300">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" stroke-width="2"
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Dashboard
                </a>
            @endif
        </div>
    </x-slot>

    <!-- ALERT FLOATING -->
    @if (session('status'))
        <div
            x-data="{ show: true }"
            x-show="show"
            x-transition:enter="transition ease-out duration-500"
            x-transition:enter-start="opacity-0 scale-95 translate-y-[-10px]"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="transition ease-in duration-500"
            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
            x-transition:leave-end="opacity-0 scale-95 translate-y-[-10px]"
            x-init="setTimeout(() => show = false, 3000)"
            class="fixed top-6 right-6 z-50 px-6 py-4 rounded-xl text-white text-sm font-semibold shadow-lg
                   bg-gradient-to-r from-[#f39c12] to-[#e67e22] backdrop-blur-lg border border-white/20">
            <div class="flex items-center space-x-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
                <span>
                    @if (session('status') === 'profile-updated')
                        Profil berhasil diperbarui!
                    @elseif (session('status') === 'password-updated')
                        Kata sandi berhasil diperbarui!
                    @elseif (session('status') === 'verification-link-sent')
                        Tautan verifikasi email telah dikirim!
                    @else
                        {{ session('status') }}
                    @endif
                </span>
            </div>
        </div>
    @endif

    <!-- BODY -->
    <div class="py-12 bg-gradient-to-br from-white via-[#fff7ec] to-[#fff3e0] min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            <!-- Update Profile Card -->
            <div class="relative group bg-white/80 backdrop-blur-lg shadow-lg rounded-2xl border border-[#f39c12]/20 p-6 sm:p-10 transition-all duration-300 hover:shadow-[0_5px_20px_rgba(243,156,18,0.25)]">
                <div class="flex items-center mb-6">
                    <div class="w-12 h-12 bg-[#f39c12]/10 text-[#f39c12] flex justify-center items-center rounded-full mr-4">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                             stroke-width="1.7" stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M5.121 17.804A7.5 7.5 0 1117.803 5.12M15 10.5h.008v.008H15V10.5zm0 0A3 3 0 1118 7.5a3 3 0 01-3 3z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800">Informasi Akun</h3>
                </div>

                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <!-- Update Password Card -->
            <div class="relative group bg-white/80 backdrop-blur-lg shadow-lg rounded-2xl border border-[#e67e22]/20 p-6 sm:p-10 transition-all duration-300 hover:shadow-[0_5px_20px_rgba(243,156,18,0.25)]">
                <div class="flex items-center mb-6">
                    <div class="w-12 h-12 bg-[#f8c471]/20 text-[#e67e22] flex justify-center items-center rounded-full mr-4">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                             stroke-width="1.7" stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M16.5 10.5V7.875A3.375 3.375 0 0013.125 4.5h-2.25A3.375 3.375 0 007.5 7.875V10.5M6 10.5h12a2.25 2.25 0 012.25 2.25v7.125A2.25 2.25 0 0118 22.125H6a2.25 2.25 0 01-2.25-2.25V12.75A2.25 2.25 0 016 10.5z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800">Ubah Password</h3>
                </div>

                <div class="max-w-xl">
                    <form method="post" action="{{ route('password.update') }}" class="space-y-6">
                        @csrf
                        @method('put')

                        <!-- Password Saat Ini -->
                        <div>
                            <x-input-label for="current_password" :value="__('Kata Sandi Saat Ini')" />
                            <x-text-input id="current_password" name="current_password" type="password"
                                class="mt-1 block w-full rounded-lg border-gray-300 focus:border-[#f39c12] focus:ring-[#f39c12]"
                                autocomplete="current-password" placeholder="Masukkan kata sandi saat ini" />
                            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
                        </div>

                        <!-- Password Baru -->
                        <div>
                            <x-input-label for="password" :value="__('Kata Sandi Baru')" />
                            <x-text-input id="password" name="password" type="password"
                                class="mt-1 block w-full rounded-lg border-gray-300 focus:border-[#f39c12] focus:ring-[#f39c12]"
                                autocomplete="new-password" placeholder="Minimal 8 karakter" />
                            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
                        </div>

                        <!-- Konfirmasi Password -->
                        <div>
                            <x-input-label for="password_confirmation" :value="__('Konfirmasi Kata Sandi')" />
                            <x-text-input id="password_confirmation" name="password_confirmation" type="password"
                                class="mt-1 block w-full rounded-lg border-gray-300 focus:border-[#f39c12] focus:ring-[#f39c12]"
                                autocomplete="new-password" placeholder="Ulangi kata sandi baru" />
                            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
                        </div>

                        <div class="flex items-center gap-4">
                            <x-primary-button
                                class="bg-gradient-to-r from-[#f39c12] to-[#e67e22] hover:shadow-[0_0_10px_rgba(243,156,18,0.4)] transition-all duration-300 rounded-lg text-white font-semibold">
                                {{ __('Simpan') }}
                            </x-primary-button>

                            @if (session('status') === 'password-updated')
                                <p x-data="{ show: true }" x-show="show" x-transition
                                   x-init="setTimeout(() => show = false, 2000)"
                                   class="text-sm text-green-600 font-medium">
                                    {{ __('Tersimpan.') }}
                                </p>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <style>
        input::placeholder {
            color: #f5b041;
            opacity: 0.85;
        }

        input, select, textarea {
            transition: all 0.3s ease-in-out;
        }

        input:focus, select:focus, textarea:focus {
            border-color: #f39c12 !important;
            box-shadow: 0 0 0 3px rgba(243, 156, 18, 0.25);
        }

        button {
            transition: all 0.3s ease-in-out;
        }

        button:hover {
            transform: scale(1.03);
        }
    </style>
</x-app-layout>
