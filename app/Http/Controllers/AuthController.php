<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Tampilkan formulir login.
     */
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    /**
     * Proses autentikasi login pengguna.
     */
    public function login(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ], [
            'username.required' => 'Username wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $username = trim($request->input('username'));
        $password = trim($request->input('password'));

        // Cari pengguna berdasarkan kolom username
        $user = User::where('username', $username)->first();

        /*
         |--------------------------------------------------------------------------
         | CATATAN KEAMANAN & PENGAKTIFAN HASHING (BCRYPT / ARGON2)
         |--------------------------------------------------------------------------
         | Saat ini sistem memvalidasi password plain-text untuk kesesuaian dengan
         | data legacy di Supabase/schema.sql.
         |
         | Untuk mengaktifkan Hashing Bcrypt yang aman di masa mendatang:
         | 1. Simpan password baru menggunakan Hash::make($newPassword).
         | 2. Ganti pengecekan di bawah menjadi:
         |    if (!$user || !Hash::check($password, $user->password)) { ... }
         |--------------------------------------------------------------------------
         */
        $isValid = $user && ($user->password === $password);

        if (!$isValid) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Username atau Password salah!',
                ], 422);
            }

            return back()->withErrors([
                'username' => 'Username atau Password salah!',
            ])->withInput($request->only('username'));
        }

        // Login ke dalam sistem Auth Laravel
        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Login berhasil! Mengalihkan...',
                'user' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'nama_karyawan' => $user->nama_karyawan,
                    'jenis_user' => strtoupper($user->jenis_user),
                ],
                'redirect' => route('dashboard'),
            ]);
        }

        return redirect()->intended(route('dashboard'));
    }

    /**
     * Proses logout pengguna dan pembersihan session.
     */
    public function logout(Request $request): JsonResponse|RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Anda telah berhasil keluar dari sistem.',
                'redirect' => route('login'),
            ]);
        }

        return redirect()->route('login')->with('success', 'Anda telah berhasil logout.');
    }

    /**
     * Dapatkan informasi user yang sedang login saat ini (API/AJAX Helper).
     */
    public function user(): JsonResponse
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['authenticated' => false], 401);
        }

        return response()->json([
            'authenticated' => true,
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'nama_karyawan' => $user->nama_karyawan,
                'jenis_user' => strtoupper($user->jenis_user),
                'is_admin' => $user->isAdmin(),
                'is_external' => $user->isExternal(),
            ],
        ]);
    }
}
