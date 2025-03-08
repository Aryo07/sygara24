<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $userRole): Response
    {
        // Cek apakah user yang sedang login memiliki role yang sesuai
        if (Auth::user()->role == $userRole) {
            // Jika role sesuai maka akan melanjutkan ke proses berikutnya
            return $next($request);
        }

        // Jika role tidak sesuai maka akan mengembalikan response berikut ini
        return response()->json(['Kamu tidak memiliki akses untuk melakukan tindakan ini.'], 400);
    }
}
