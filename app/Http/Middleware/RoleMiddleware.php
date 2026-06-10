<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware Role-Based Access Control
 *
 * Memastikan hanya user dengan role tertentu yang bisa mengakses route.
 *
 * Cara pakai di route:
 *   Route::middleware('role:kepsek')->group(...)
 *   Route::middleware('role:admin')->group(...)
 *   Route::middleware('role:guru')->group(...)
 */
class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        // Map role ke guard Laravel
        $guards = [
            'kepsek' => 'kepsek',
            'admin'  => 'admin',
            'guru'   => 'guru',
        ];

        if (!isset($guards[$role])) {
            abort(500, 'Role tidak dikenali');
        }

        $guard = $guards[$role];

        // Cek apakah user sudah login dengan guard yang sesuai
        if (!Auth::guard($guard)->check()) {
            return redirect()->route('login')
                ->with('error', 'Silakan login terlebih dahulu sebagai ' . ucfirst($role));
        }

        // Cek apakah user masih aktif (is_active = true)
        $user = Auth::guard($guard)->user();
        if (isset($user->is_active) && !$user->is_active) {
            Auth::guard($guard)->logout();
            return redirect()->route('login')
                ->with('error', 'Akun Anda telah dinonaktifkan. Hubungi Kepala Sekolah.');
        }

        // Bind user ke request agar mudah diakses di controller
        $request->attributes->set('current_user', $user);
        $request->attributes->set('current_role', $role);

        return $next($request);
    }
}
