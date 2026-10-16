<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
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
        $validated = $request->validate([
            'participant_code' => ['required', 'string', 'max:30'],
        ]);

        $attendance = Absensi::where('participant_code', $validated['participant_code'])
            ->whereDate('tanggal', today())
            ->first();

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

        $participantCode = $user->email;

        $attendance = Absensi::where('participant_code', $participantCode)
            ->orderByDesc('tanggal')
            ->get();

        return response()->json($attendance);
    }

    public function checkIn(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'participant_code' => ['required', 'string', 'max:30'],
            'participant_name' => ['required', 'string', 'max:100'],
            'institution' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', 'in:Hadir,Izin,Sakit,Alpa'],
            'keterangan' => ['nullable', 'string', 'max:255'],
            'foto' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $status = $validated['status'] ?? 'Hadir';

        $photoPath = $request->file('foto')->store('absensi', 'public');

        $attendance = Absensi::firstOrCreate(
            [
                'participant_code' => $validated['participant_code'],
                'tanggal' => today(),
            ],
            [
                'participant_name' => $validated['participant_name'],
                'institution' => $validated['institution'] ?? null,
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
        $validated = $request->validate([
            'participant_code' => ['required', 'string', 'max:30'],
        ]);

        $attendance = Absensi::where('participant_code', $validated['participant_code'])
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