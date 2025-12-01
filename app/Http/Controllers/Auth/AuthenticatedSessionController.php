<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\Models\User;

class AuthenticatedSessionController extends Controller
{
    /**
     * Tampilkan halaman login.
     */
    public function create()
    {
        return view('auth.login');
    }

    /**
     * Tangani login (support AJAX tanpa reload).
     */
    public function store(LoginRequest $request)
    {
        // 🧩 Cek apakah akun ada
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'type' => 'warning',
                    'message' => 'Akun tidak dikenali. Silakan periksa kembali email Anda.',
                ], 404);
            }
            return back()->with('error', 'Akun tidak dikenali.');
        }

        try {
            // 🔑 Proses login standar
            $request->authenticate();
            $request->session()->regenerate();

            // 🚫 Cek banned
            if ($user->is_banned) {
                Auth::logout();
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'type' => 'danger',
                        'message' => 'Akun kamu sedang diban. Hubungi admin.',
                    ], 403);
                }
                return redirect()->route('login')->with('error', 'Akun kamu sedang diban. Hubungi admin.');
            }

            // 🧭 Tentukan dashboard berdasarkan role
            $redirect = match ($user->role) {
                'super_admin' => route('super_admin.dashboard'),
                'admin'       => route('admin.dashboard'),
                'pegawai'     => route('pegawai.dashboard'),
                default       => route('dashboard'),
            };

            // ✅ Pastikan session tersimpan
            if (!Auth::check()) {
                return response()->json([
                    'success' => false,
                    'type' => 'warning',
                    'message' => 'Login berhasil, tapi sesi belum tersimpan. Coba ulangi.',
                ], 401);
            }

            // 🚀 Respon AJAX
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'type' => 'success',
                    'message' => 'Login berhasil! Mengalihkan ke dashboard...',
                    'redirect' => $redirect,
                ]);
            }

            // 🌐 Respon normal (non-AJAX)
            return redirect()->intended($redirect);

        } catch (ValidationException $e) {
            // ❌ Password salah
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'type' => 'danger',
                    'message' => 'Kata sandi salah. Silakan coba lagi.',
                ], 422);
            }

            throw $e;
        }
    }

    /**
     * Logout dan hapus session.
     */
    public function destroy(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect('/');
    }
}