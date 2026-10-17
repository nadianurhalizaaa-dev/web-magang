<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Magang;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ParticipantAccountController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        if ($request->user()?->role !== 'admin') {
            return response()->json([
                'message' => 'Hanya admin yang dapat melihat pengaturan akun peserta.',
            ], 403);
        }

        return response()->json(
            User::query()
                ->whereIn('role', ['peserta', 'user'])
                ->with('magang:id,participant_code,participant_name,status')
                ->orderBy('name')
                ->get(['id', 'name', 'email', 'role', 'magang_id'])
        );
    }

    public function update(Request $request, User $user): JsonResponse
    {
        if ($request->user()?->role !== 'admin') {
            return response()->json([
                'message' => 'Hanya admin yang dapat menghubungkan akun peserta.',
            ], 403);
        }

        if (! in_array($user->role, ['peserta', 'user'], true)) {
            return response()->json([
                'message' => 'Akun ini bukan akun peserta.',
            ], 422);
        }

        $validated = $request->validate([
            'participant_code' => ['required', 'string', 'max:30', 'exists:magangs,participant_code'],
            'replace_existing' => ['sometimes', 'boolean'],
        ]);

        $magang = Magang::where('participant_code', $validated['participant_code'])
            ->where('status', 'Aktif')
            ->first();

        if (! $magang) {
            return response()->json([
                'message' => 'Peserta tidak ditemukan atau sudah tidak aktif.',
            ], 422);
        }

        $currentOwner = User::where('magang_id', $magang->id)
            ->where('id', '!=', $user->id)
            ->first();

        if ($currentOwner && ! ($validated['replace_existing'] ?? false)) {
            return response()->json([
                'message' => 'NPM ini masih terhubung ke akun '.$currentOwner->email.'. Konfirmasi pemindahan tautan untuk memakai email pendaftaran yang benar.',
                'linked_account_email' => $currentOwner->email,
            ], 409);
        }

        DB::transaction(function () use ($currentOwner, $magang, $user) {
            if ($currentOwner) {
                $currentOwner->magang()->dissociate();
                $currentOwner->save();
            }

            $user->magang()->associate($magang);
            $user->save();
        });

        return response()->json([
            'message' => $currentOwner
                ? 'Tautan NPM berhasil dipindahkan ke akun dengan email pendaftaran yang benar.'
                : 'Akun berhasil dihubungkan ke peserta.',
            'data' => $user->load('magang'),
            'previous_account_id' => $currentOwner?->id,
        ]);
    }
}
