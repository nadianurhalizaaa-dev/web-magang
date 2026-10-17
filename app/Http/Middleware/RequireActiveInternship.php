<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RequireActiveInternship
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        // Admin and pembimbing access is governed by the role middleware.
        if ($user && ! in_array(strtolower((string) $user->role), ['peserta', 'pemagang', 'calon pemagang'], true)) {
            return $next($request);
        }

        $isActive = $user && (
            $user->pendaftarans()->where('status', 'Pemagang Aktif')->exists()
            || $user->magang()->where('status', 'Aktif')->exists()
        );

        if (! $isActive) {
            return response()->json([
                'message' => 'Akun ini belum ditautkan ke peserta magang aktif. Hubungi pengelola untuk menautkan akun dan NPM Anda.',
            ], 403);
        }

        return $next($request);
    }
}
