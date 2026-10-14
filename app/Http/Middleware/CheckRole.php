<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = $request->user();

        $normalize = static function ($role) {
            $role = strtolower(trim((string) $role));

            return in_array($role, ['peserta', 'pemagang', 'calon pemagang'], true)
                ? 'peserta'
                : $role;
        };

        $allowedRoles = array_map($normalize, $roles);

        if (!$user || !in_array($normalize($user->role), $allowedRoles, true)) {
            return response()->json(['message' => 'Anda tidak memiliki akses.'], 403);
        }

        return $next($request);
    }
}
