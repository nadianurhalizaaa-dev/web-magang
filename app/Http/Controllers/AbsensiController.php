<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\Pendaftaran;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AbsensiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'participant_code' => ['nullable', 'string', 'max:30'],
            'tanggal' => ['nullable', 'date'],
        ]);

        $query = Absensi::query();

        if (strtolower((string) $request->user()?->role) === 'pembimbing') {
            $assignedUserIds = Pendaftaran::query()
                ->where('pembimbing_id', $request->user()->id)
                ->where('status', 'Pemagang Aktif')
                ->select('user_id');
            $query->whereIn('user_id', $assignedUserIds);
        }

        if (!empty($validated['participant_code'])) {
            $query->where('participant_code', $validated['participant_code']);
        }

        if (!empty($validated['tanggal'])) {
            $query->whereDate('tanggal', $validated['tanggal']);
        }

        return response()->json(
            $query->latest('tanggal')
                  ->latest('id')
                  ->get()
        );
    }

    public function today(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user?->role === 'peserta') {
            $attendance = Absensi::where('user_id', $user->id)
                ->whereDate('tanggal', today())
                ->first();
        } else {
            $validated = $request->validate([
                'participant_code' => ['required', 'string', 'max:30'],
            ]);
            $attendance = Absensi::where('participant_code', $validated['participant_code'])
                ->whereDate('tanggal', today())
                ->first();
        }

        return response()->json($attendance);
    }

    public function myAttendance(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 401);
        }

        $attendance = Absensi::where('user_id', $user->id)
            ->orderByDesc('tanggal')
            ->get();

        return response()->json($attendance);
    }

    public function checkIn(Request $request): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validate([
            'status' => ['nullable', 'in:Hadir,Izin,Sakit,Alpa'],
            'keterangan' => ['nullable', 'string', 'max:255'],
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $registration = $user->pendaftarans()->where('status', 'Pemagang Aktif')->latest('id')->first();
        if (! $registration) {
            return response()->json(['message' => 'Akun ini belum memiliki pendaftaran magang aktif.'], 403);
        }

        // Identitas absensi peserta diambil dari sesi login dan pendaftaran aktif,
        // bukan dari kode/nama yang dikirim browser.
        $participantCode = 'USER-' . $user->id;
        $participantName = $user->name;
        $institution = $registration->asal_instansi;

        $status = $validated['status'] ?? 'Hadir';

        $photoPath = $request->hasFile('foto')
            ? $request->file('foto')->store('absensi', 'public')
            : null;

        $attendance = Absensi::firstOrCreate(
            [
                'participant_code' => $participantCode,
                'tanggal' => today(),
            ],
            [
                'user_id' => $user->id,
                'participant_name' => $participantName,
                'institution' => $institution,
                'jam_masuk' => now()->format('H:i:s'),
                'status' => $status,
                'keterangan' => $validated['keterangan'] ?? null,
                'foto' => $photoPath,
            ]
        );

        if (!$attendance->wasRecentlyCreated) {
            Storage::disk('public')->delete($photoPath);
            return response()->json(['message' => 'Kamu sudah melakukan absensi masuk hari ini.', 'data' => $attendance], 422);
        }

        return response()->json(['message' => 'Absensi masuk berhasil dicatat.', 'data' => $attendance], 201);
    }

    public function checkOut(Request $request): JsonResponse
    {
        $user = $request->user();
        $attendance = Absensi::where('user_id', $user->id)
            ->whereDate('tanggal', today())
            ->first();

        if (!$attendance) {
            return response()->json(['message' => 'Lakukan absensi masuk terlebih dahulu.'], 422);
        }

        if ($attendance->jam_pulang) {
            return response()->json(['message' => 'Kamu sudah melakukan absensi pulang hari ini.', 'data' => $attendance], 422);
        }

        if (now()->format('H:i') < '17:00') {
            return response()->json(['message' => 'Belum waktunya pulang. Absensi pulang hanya bisa dilakukan mulai jam 17:00.'], 422);
        }

        $attendance->update(['jam_pulang' => now()->format('H:i:s')]);

        return response()->json(['message' => 'Absensi pulang berhasil dicatat.', 'data' => $attendance]);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        // Validasi input
        $validated = $request->validate([
            'jam_masuk' => ['nullable', 'string', 'max:20'],
            'jam_pulang' => ['nullable', 'string', 'max:20'],
            'status' => ['required', 'in:Hadir,Izin,Sakit,Alpa,Belum hadir'],
            'participant_code' => ['required', 'string', 'max:30'],
            'participant_name' => ['required', 'string', 'max:100'],
            'tanggal' => ['required', 'date'],
            'keterangan' => ['nullable', 'string', 'max:255'],
        ]);

        $assignedUserId = null;
        if (strtolower((string) $request->user()?->role) === 'pembimbing') {
            if (! preg_match('/^USER-(\d+)$/', $validated['participant_code'], $matches)) {
                return response()->json(['message' => 'Kode peserta tidak valid untuk akun Pembimbing ini.'], 403);
            }

            $assignedUserId = (int) $matches[1];
            $isAssigned = Pendaftaran::where('user_id', $assignedUserId)
                ->where('pembimbing_id', $request->user()->id)
                ->where('status', 'Pemagang Aktif')
                ->exists();

            if (! $isAssigned) {
                return response()->json(['message' => 'Anda hanya dapat mengubah absensi peserta bimbingan yang aktif.'], 403);
            }
        }

        // Karena ID bisa jadi "Belum hadir" dari frontend yang belum ada di database,
        // kita cari berdasarkan participant_code dan tanggal, atau buat baru.
        $attendance = Absensi::where('participant_code', $validated['participant_code'])
            ->whereDate('tanggal', $validated['tanggal'])
            ->first();

        if (!$attendance) {
            if ($validated['status'] === 'Belum hadir') {
                return response()->json(['message' => 'Tidak ada perubahan.']);
            }
            // Buat record baru jika belum ada
            $attendance = Absensi::create([
                'participant_code' => $validated['participant_code'],
                'user_id' => $assignedUserId,
                'participant_name' => $validated['participant_name'],
                'tanggal' => $validated['tanggal'],
                'jam_masuk' => $validated['jam_masuk'],
                'jam_pulang' => $validated['jam_pulang'],
                'status' => $validated['status'],
                'keterangan' => $validated['keterangan'] ?? null,
            ]);
        } else {
            // Update record yang ada
            $attendance->update([
                'jam_masuk' => $validated['jam_masuk'],
                'jam_pulang' => $validated['jam_pulang'],
                'status' => $validated['status'],
                'keterangan' => $validated['keterangan'] ?? null,
            ]);
        }

        return response()->json(['message' => 'Data absensi berhasil diperbarui.', 'data' => $attendance]);
    }
}
