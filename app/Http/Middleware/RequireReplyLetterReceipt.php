<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RequireReplyLetterReceipt
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($user && ! in_array(strtolower((string) $user->role), ['peserta', 'pemagang', 'calon pemagang'], true)) {
            return $next($request);
        }

        $hasReceivedLetter = $user
            && $user->pendaftarans()
                ->where('status', 'Pemagang Aktif')
                ->whereNotNull('file_surat_balasan')
                ->whereNotNull('surat_balasan_diterima_at')
                ->exists();

        if (! $hasReceivedLetter) {
            return response()->json([
                'message' => 'Unduh surat balasan penerimaan terlebih dahulu sebelum mengakses absensi.',
            ], 403);
        }

        return $next($request);
    }
}
