<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\LaporanMagang;
use App\Models\Pendaftaran;
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
            $submittedTaskTitles = $latestSubmissions->pluck('tugas.judul')
                ->filter()
                ->unique()
                ->values();
            $submissionDates = $latestSubmissions->pluck('tanggal_submit')->filter();
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

            $reports = LaporanMagang::with(['penilaian' => fn ($query) => $query
                ->select('id_penilaian', 'id_laporan_magang', 'judul_nilai', 'catatan', 'tanggal_nilai', 'poin_nilai')
                ->orderByDesc('id_penilaian')])
                ->where('id_peserta', $user->id)
                ->orderByDesc('tgl_submit')
                ->get(['id_laporan', 'judul_laporan', 'kategori_laporan', 'status_laporan', 'tgl_submit', 'nilai_laporan']);
            $latestReport = $reports->first();

            return [
                'user_id' => $user->id,
                'nama_peserta' => $user->name,
                'email' => $user->email,
                'instansi' => $registration->asal_instansi,
                'periode' => $period?->nama_periode,
                'tanggal_mulai' => $period?->tanggal_mulai ?? $registration->tanggal_daftar,
                'tanggal_selesai' => $endValue,
                'projek' => $submittedTaskTitles->isNotEmpty() ? $submittedTaskTitles->implode(', ') : 'Belum ada tugas yang dikumpulkan',
                'detail_tugas' => $taskDetails,
                'lampiran_tanggal_mulai' => $submissionDates->first()?->toDateString(),
                'lampiran_tanggal_akhir' => $submissionDates->last()?->toDateString(),
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
                'laporan_terakhir' => $latestReport,
                'daftar_laporan' => $reports,
            ];
        });

        return response()->json($rows);
    }
}
