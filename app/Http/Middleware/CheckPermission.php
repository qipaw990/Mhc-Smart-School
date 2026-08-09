<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        if (!$user->hasPermission($permission)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Anda tidak memiliki hak akses untuk fitur ini (' . $permission . ').'
                ], 403);
            }
            abort(403, 'Akses Ditolak: Anda tidak memiliki permission (' . $permission . ') untuk mengakses halaman ini.');
        }

        return $next($request);
    }
}
