<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\LaporanMagang;
use App\Models\Pendaftaran;
use App\Models\Penilaian;
use App\Models\SubmisiTugas;
use App\Models\Tugas;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;

class RekapitulasiController extends Controller
{
    public function index(Request $request)
    {
        $query = Pendaftaran::with([
            'user:id,name,email',
            'periode:id,nama_periode,tanggal_mulai,tanggal_selesai',
        ])->where('status', 'Pemagang Aktif')->orderByDesc('id');

        if (strtolower((string) $request->user()->role) === 'pembimbing') {
            $query->where('pembimbing_id', $request->user()->id);
        }

        // Satu baris per akun meski ada lebih dari satu pendaftaran aktif.
        $registrations = $query->get()
            ->filter(fn (Pendaftaran $registration) => $registration->user !== null)
            ->unique('user_id')
            ->values();

        $totalTasks = Tugas::count();
        $today = today();

        $rows = $registrations->map(function (Pendaftaran $registration) use ($totalTasks, $today) {
            $user = $registration->user;
            $period = $registration->periode;
            $start = Carbon::parse($period?->tanggal_mulai ?? $registration->tanggal_daftar)->startOfDay();
            $endValue = $registration->tanggal_selesai ?? $period?->tanggal_selesai;
            $end = $endValue ? Carbon::parse($endValue)->startOfDay() : $today->copy()->startOfDay();
            $end = $end->min($today->copy()->startOfDay());

            $workingDays = 0;
            if ($start->lte($end)) {
                foreach (CarbonPeriod::create($start, $end) as $day) {
                    if ($day->dayOfWeek !== Carbon::SUNDAY) {
                        $workingDays++;
                    }
                }
            }

            $attendance = collect();
            if ($start->lte($end)) {
                $attendance = Absensi::where(function ($query) use ($user) {
                    $query->where('user_id', $user->id)
                        ->orWhere('participant_code', 'USER-' . $user->id);
                })
                    ->whereBetween('tanggal', [$start->toDateString(), $end->toDateString()])
                    ->get(['status']);
            }

            $presentDays = $attendance->where('status', 'Hadir')->count();
            $recordedDays = $attendance->count();
            $attendancePercent = $workingDays > 0
                ? min(100, (int) round(($presentDays / $workingDays) * 100))
                : 0;

            $submissions = SubmisiTugas::with('tugas:id,judul,deskripsi,deadline')
                ->where('user_id', $user->id)
                ->orderBy('tanggal_submit')
                ->get();
            $latestSubmissions = $submissions->groupBy('tugas_id')->map(fn ($items) => $items->last())->values();
            $submittedTaskCount = $latestSubmissions->pluck('tugas_id')->filter()->unique()->count();
            $taskDetails = $latestSubmissions->map(fn (SubmisiTugas $submission) => [
                'submisi_id' => $submission->id,
                'tugas_id' => $submission->tugas_id,
                'judul' => $submission->tugas?->judul ?? 'Tugas telah dihapus',
                'deskripsi' => $submission->tugas?->deskripsi,
                'deadline' => $submission->tugas?->deadline,
                'tanggal_submit' => $submission->tanggal_submit,
                'status' => $submission->status,
                'nilai' => $submission->nilai,
                'feedback' => $submission->feedback,
                'ada_berkas' => filled($submission->file_path),
            ])->values();

            $latestReport = LaporanMagang::where('id_peserta', $user->id)
                ->orderByDesc('id_laporan')
                ->first(['id_laporan', 'nilai_laporan']);
            $assessment = Penilaian::with('laporan:id_laporan,id_peserta,nilai_laporan')
                ->where(function ($query) use ($user, $latestReport) {
                    $query->where('id_peserta', $user->id);
                    if ($latestReport) {
                        $query->orWhere('id_laporan_magang', $latestReport->id_laporan);
                    }
                })
                ->orderByDesc('tanggal_nilai')
                ->orderByDesc('id_penilaian')
                ->first();
            $assessmentScore = $assessment?->nilai_akhir
                ?? $assessment?->laporan?->nilai_laporan
                ?? $latestReport?->nilai_laporan;

            return [
                'user_id' => $user->id,
                'nama_peserta' => $user->name,
                'email' => $user->email,
                'instansi' => $registration->asal_instansi,
                'periode' => $period?->nama_periode,
                'tanggal_mulai' => $period?->tanggal_mulai ?? $registration->tanggal_daftar,
                'tanggal_selesai' => $endValue,
                'detail_tugas' => $taskDetails,
                'nilai_akhir_penilaian' => $assessmentScore,
                'predikat_penilaian' => $assessment?->poin_nilai,
                'hari_kerja' => $workingDays,
                'hari_hadir' => $presentDays,
                'hari_absensi_tercatat' => $recordedDays,
                'persentase_absensi' => $attendancePercent,
                'total_absensi' => $attendancePercent . '%',
                'status_kehadiran' => $recordedDays === 0
                    ? 'Belum ada absensi'
                    : ($workingDays > 0 && $presentDays >= $workingDays
                        ? 'Hadir Lengkap'
                        : ($presentDays > 0 ? 'Kehadiran sebagian' : 'Belum ada hari hadir')),
                'tugas_selesai' => min($submittedTaskCount, $totalTasks),
                'total_tugas' => $totalTasks,
            ];
        });

        return response()->json($rows);
    }
}
