<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
<<<<<<< HEAD
use App\Models\Pendaftaran;
=======
use App\Models\Magang;
>>>>>>> origin/main
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AbsensiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        if (! in_array($request->user()?->role, ['admin', 'pembimbing'], true)) {
            return response()->json([
                'message' => 'Anda tidak memiliki izin untuk melihat seluruh data absensi.',
            ], 403);
        }

        $validated = $request->validate([
            'participant_code' => ['nullable', 'string', 'max:30'],
            'tanggal' => ['nullable', 'date'],
        ]);

        $query = Absensi::with(['pembimbing', 'user']);

<<<<<<< HEAD
        if (strtolower((string) $request->user()?->role) === 'pembimbing') {
            $assignedUserIds = Pendaftaran::query()
                ->where('pembimbing_id', $request->user()->id)
                ->where('status', 'Pemagang Aktif')
                ->select('user_id');
            $query->whereIn('user_id', $assignedUserIds);
        }

        if (!empty($validated['participant_code'])) {
=======
        if (! empty($validated['participant_code'])) {
>>>>>>> origin/main
            $query->where('participant_code', $validated['participant_code']);
        }

        if (! empty($validated['tanggal'])) {
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
<<<<<<< HEAD
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
=======
        $profile = $this->participantProfile($request);
        if ($profile instanceof JsonResponse) {
            return $profile;
        }

        $attendance = Absensi::where('participant_code', $profile->participant_code)
            ->whereDate('tanggal', today())
            ->first();
>>>>>>> origin/main

        return response()->json($attendance);
    }

    public function myAttendance(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user || ! in_array($user->role, ['peserta', 'user'], true)) {
            return response()->json([
                'message' => 'Hanya akun peserta yang dapat melihat riwayat pribadinya.',
            ], 403);
        }

<<<<<<< HEAD
        $attendance = Absensi::where('user_id', $user->id)
=======
        $profile = $this->participantProfile($request);
        if ($profile instanceof JsonResponse) {
            return $profile;
        }

        $attendance = Absensi::where('participant_code', $profile->participant_code)
>>>>>>> origin/main
            ->orderByDesc('tanggal')
            ->get();

        return response()->json($attendance);
    }

    public function checkIn(Request $request): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validate([
<<<<<<< HEAD
            'status' => ['nullable', 'in:Hadir,Izin,Sakit,Alpa'],
=======
            'status' => ['nullable', 'in:Hadir,Tidak Hadir,Izin,Sakit,Alpa'],
>>>>>>> origin/main
            'keterangan' => ['nullable', 'string', 'max:255'],
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

<<<<<<< HEAD
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
=======
        $profile = $this->participantProfile($request);
        if ($profile instanceof JsonResponse) {
            return $profile;
        }

        $status = $validated['status'] ?? 'Hadir';
        $isLeaveRequest = $status === 'Izin';
        $isNotPresent = in_array($status, ['Tidak Hadir', 'Izin', 'Sakit', 'Alpa']);

        $user = $request->user();
        $pembimbingId = $profile->pembimbing_id ?? $user->pembimbing_id;

        $photoPath = $request->file('foto')?->store('absensi', 'public');

        $finalStatus = $isNotPresent ? ($status === 'Tidak Hadir' ? 'Tidak Hadir' : ($isLeaveRequest ? 'Tidak Hadir' : $status)) : 'Hadir';

        $attendance = Absensi::firstOrCreate(
            [
                'participant_code' => $profile->participant_code,
>>>>>>> origin/main
                'tanggal' => today(),
            ],
            [
                'user_id' => $user->id,
<<<<<<< HEAD
                'participant_name' => $participantName,
                'institution' => $institution,
                'jam_masuk' => now()->format('H:i:s'),
                'status' => $status,
=======
                'magang_id' => $profile->id,
                'pembimbing_id' => $pembimbingId,
                'participant_name' => $profile->participant_name,
                'institution' => $profile->institution,
                'jam_masuk' => $isNotPresent ? null : now()->format('H:i:s'),
                'status' => $finalStatus,
                'approval_status' => $isLeaveRequest ? 'pending' : null,
>>>>>>> origin/main
                'keterangan' => $validated['keterangan'] ?? null,
                'foto' => $photoPath,
            ]
        );

        if (! $attendance->wasRecentlyCreated) {
            if ($photoPath) {
                Storage::disk('public')->delete($photoPath);
            }

            return response()->json(['message' => 'Kamu sudah melakukan absensi masuk hari ini.', 'data' => $attendance], 422);
        }

        return response()->json([
            'message' => $isLeaveRequest
                ? 'Permohonan izin berhasil dikirim dan menunggu persetujuan pembimbing.'
                : 'Absensi masuk berhasil dicatat.',
            'data' => $attendance,
        ], 201);
    }

    public function reviewLeave(Request $request, string $id): JsonResponse
    {
        if (! in_array($request->user()?->role, ['admin', 'pembimbing'], true)) {
            return response()->json(['message' => 'Anda tidak memiliki izin untuk memproses permohonan izin.'], 403);
        }

        $validated = $request->validate([
            'decision' => ['required', 'in:approved,rejected'],
        ]);

        $attendance = Absensi::findOrFail($id);

        if ($attendance->approval_status !== 'pending') {
            return response()->json([
                'message' => 'Permohonan izin ini sudah diputuskan atau tidak menunggu persetujuan.',
            ], 422);
        }

        $approved = $validated['decision'] === 'approved';
        $attendance->update([
            'status' => $approved ? 'Izin' : 'Alpa',
            'approval_status' => $approved ? 'approved' : 'rejected',
        ]);

        return response()->json([
            'message' => $approved
                ? 'Permohonan izin disetujui.'
                : 'Permohonan izin ditolak.',
            'data' => $attendance,
        ]);
    }

    public function checkOut(Request $request): JsonResponse
    {
<<<<<<< HEAD
        $user = $request->user();
        $attendance = Absensi::where('user_id', $user->id)
=======
        $profile = $this->participantProfile($request);
        if ($profile instanceof JsonResponse) {
            return $profile;
        }

        $attendance = Absensi::where('participant_code', $profile->participant_code)
>>>>>>> origin/main
            ->whereDate('tanggal', today())
            ->first();

        if (! $attendance) {
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
        if (! in_array($request->user()?->role, ['admin', 'pembimbing'], true)) {
            return response()->json(['message' => 'Anda tidak memiliki izin untuk mengubah data absensi.'], 403);
        }

        // Validasi input
        $validated = $request->validate([
            'jam_masuk' => ['nullable', 'string', 'max:20'],
            'jam_pulang' => ['nullable', 'string', 'max:20'],
            'status' => ['required', 'in:Hadir,Tidak Hadir,Izin,Sakit,Alpa,Belum hadir'],
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

        if (! $attendance) {
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
<<<<<<< HEAD
=======

    public function store(Request $request): JsonResponse
    {
        if (! in_array($request->user()?->role, ['admin', 'pembimbing'], true)) {
            return response()->json(['message' => 'Anda tidak memiliki izin untuk menambahkan data absensi.'], 403);
        }

        $validated = $request->validate([
            'participant_code' => ['required', 'string', 'max:30'],
            'participant_name' => ['required', 'string', 'max:100'],
            'tanggal' => ['required', 'date'],
            'jam_masuk' => ['nullable', 'string', 'max:20'],
            'jam_pulang' => ['nullable', 'string', 'max:20'],
            'status' => ['required', 'in:Hadir,Tidak Hadir,Izin,Sakit,Alpa'],
            'keterangan' => ['nullable', 'string', 'max:255'],
            'pembimbing_id' => ['nullable', 'integer'],
        ]);

        $attendance = Absensi::create($validated);

        return response()->json([
            'message' => 'Data absensi berhasil ditambahkan.',
            'data' => $attendance,
        ], 201);
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        if (! in_array($request->user()?->role, ['admin', 'pembimbing'], true)) {
            return response()->json(['message' => 'Anda tidak memiliki izin untuk menghapus data absensi.'], 403);
        }

        $attendance = Absensi::findOrFail($id);
        $attendance->delete();

        return response()->json(['message' => 'Data absensi berhasil dihapus.']);
    }

    private function participantProfile(Request $request): Magang|JsonResponse
    {
        $user = $request->user();

        if (! $user || ! in_array($user->role, ['peserta', 'user'], true)) {
            return response()->json([
                'message' => 'Absensi pribadi hanya tersedia untuk akun peserta.',
            ], 403);
        }

        $profile = $user->magang;
        if (! $profile || $profile->status !== 'Aktif') {
            return response()->json([
                'message' => 'Akun ini belum ditautkan ke peserta magang aktif. Hubungi pengelola untuk menautkan akun dan NPM Anda.',
            ], 403);
        }

        return $profile;
    }
>>>>>>> origin/main
}
