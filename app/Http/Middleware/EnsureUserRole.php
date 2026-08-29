<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!Auth::check()) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sesi login telah berakhir. Silakan login kembali.',
                ], 401);
            }

            return redirect()->route('login');
        }

        $user = Auth::user();
        $userRole = strtoupper($user->jenis_user);

        // Ubah format argumen role ke uppercase untuk perbandingan aman
        $allowedRoles = array_map('strtoupper', $roles);

        if (!empty($allowedRoles) && !in_array($userRole, $allowedRoles)) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses ditolak. Anda tidak memiliki izin untuk tindakan ini.',
                ], 403);
            }

            abort(403, 'Akses ditolak: Peran pengguna (' . $userRole . ') tidak memiliki izin untuk mengakses halaman ini.');
        }

        return $next($request);
    }
}
