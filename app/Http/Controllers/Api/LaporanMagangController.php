<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LaporanMagang;
use App\Models\Pendaftaran;
use App\Models\RiwayatRevisiLaporan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class LaporanMagangController extends Controller
{
    /**
     * Display a listing of reports with search & filter.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $query = LaporanMagang::with([
            'riwayatRevisi',
            'penilaian',
            'peserta:id,name,email',
            'peserta.pendaftarans' => fn ($registrationQuery) => $registrationQuery->orderByDesc('id'),
        ]);

        if (strtolower((string) $user?->role) === 'pembimbing') {
            $assignedParticipantIds = Pendaftaran::where('pembimbing_id', $user->id)
                ->where('status', 'Pemagang Aktif')
                ->select('user_id');
            $query->whereIn('id_peserta', $assignedParticipantIds);
        } elseif (in_array(strtolower((string) $user?->role), ['peserta', 'pemagang'], true)) {
            $query->where('id_peserta', $user->id);
        }

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('judul_laporan', 'like', "%{$search}%")
                  ->orWhere('abstrak', 'like', "%{$search}%");
            });
        }

        if ($request->has('kategori') && !empty($request->kategori) && $request->kategori !== 'Semua') {
            $query->where('kategori_laporan', $request->kategori);
        }

        if ($request->has('status') && !empty($request->status) && $request->status !== 'Semua') {
            $query->where('status_laporan', $request->status);
        }

        $laporan = $query->orderBy('id_laporan', 'desc')->get()->map(function (LaporanMagang $item) {
            $registration = $item->peserta?->pendaftarans
                ->first(fn (Pendaftaran $pendaftaran) => $pendaftaran->status === 'Pemagang Aktif')
                ?? $item->peserta?->pendaftarans->first();

            $item->setAttribute('nama_peserta', $item->peserta?->name);
            $item->setAttribute('email_peserta', $item->peserta?->email);
            $item->setAttribute('asal_instansi', $registration?->asal_instansi);

            return $item;
        });

        return response()->json([
            'success' => true,
            'message' => 'Daftar laporan magang berhasil diambil',
            'data' => $laporan
        ]);
    }

    /**
     * UC-02: Mengunggah Laporan Magang (Anak Magang)
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'judul_laporan' => 'required|string|max:255',
            'kategori_laporan' => 'required|in:Individu,Kelompok',
            'link_google_doc' => 'required|url|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $registration = Pendaftaran::where('user_id', $request->user()->id)
            ->where('status', 'Pemagang Aktif')
            ->whereNotNull('pembimbing_id')
            ->first();

        if (!$registration) {
            return response()->json([
                'success' => false,
                'message' => 'Laporan hanya dapat diunggah oleh pemagang aktif yang sudah ditugaskan kepada pembimbing.',
            ], 403);
        }

        $laporan = LaporanMagang::create([
            'id_peserta' => $request->user()->id,
            'judul_laporan' => $request->judul_laporan,
            'kategori_laporan' => $request->kategori_laporan,
            'file_laporan_pdf' => null,
            'link_google_doc' => $request->link_google_doc,
            'tgl_submit' => now(),
            'status_laporan' => 'Menunggu Review',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tautan laporan Google Docs berhasil disimpan',
            'data' => $laporan
        ], 201);
    }

    /**
     * Display the specified report.
     */
    public function show(Request $request, $id)
    {
        $laporan = LaporanMagang::with(['riwayatRevisi', 'penilaian'])->find($id);

        if (!$laporan) {
            return response()->json([
                'success' => false,
                'message' => 'Data laporan tidak ditemukan'
            ], 404);
        }

        if (! $this->canAccessReport($request, $laporan)) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses ke laporan ini.',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $laporan
        ]);
    }

    public function download(Request $request, $id)
    {
        $laporan = LaporanMagang::find($id);
        if (!$laporan) {
            return response()->json([
                'success' => false,
                'message' => 'File laporan tidak ditemukan'
            ], 404);
        }

        if (! $this->canAccessReport($request, $laporan)) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses ke laporan ini.',
            ], 403);
        }

        $filePath = $request->boolean('source')
            ? $laporan->file_laporan_pdf
            : ($laporan->file_pdf_final ?: $laporan->file_laporan_pdf);

        if ($filePath === $laporan->file_laporan_pdf && $laporan->link_google_doc && ! $request->boolean('source')) {
            return redirect()->away($laporan->link_google_doc);
        }

        $disk = Storage::disk('public');
        if (! is_string($filePath) || ! $disk->exists($filePath)) {
            return response()->json([
                'success' => false,
                'message' => 'File laporan tidak ditemukan',
            ], 404);
        }

        $fileStream = $disk->readStream($filePath);
        if ($fileStream === false) {
            return response()->json([
                'success' => false,
                'message' => 'File laporan tidak ditemukan'
            ], 404);
        }

        return response()->streamDownload(function () use ($fileStream) {
            fpassthru($fileStream);
            fclose($fileStream);
        }, basename($filePath), [
            'Content-Type' => 'application/pdf',
        ]);
    }

    public function downloadGoogleDocPdf(Request $request, $id)
    {
        $laporan = LaporanMagang::with('riwayatRevisi')->find($id);
        if (! $laporan) {
            return response()->json([
                'success' => false,
                'message' => 'Laporan tidak ditemukan.',
            ], 404);
        }

        if (! $this->canAccessReport($request, $laporan)) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses ke laporan ini.',
            ], 403);
        }

        if ($laporan->status_laporan !== 'Selesai') {
            return response()->json([
                'success' => false,
                'message' => 'PDF dari Google Docs hanya dapat dibuat setelah laporan berstatus Selesai.',
            ], 422);
        }

        $googleDocLink = $laporan->riwayatRevisi->first()?->link_google_doc
            ?: $laporan->link_google_doc;
        $fileId = $this->googleDriveFileId($googleDocLink);
        if (! $fileId) {
            return response()->json([
                'success' => false,
                'message' => 'Tautan Google Docs/Drive yang valid tidak ditemukan pada laporan.',
            ], 422);
        }

        $pdf = $this->fetchGooglePdf($fileId, str_contains((string) $googleDocLink, 'docs.google.com'));
        if (! $pdf) {
            return response()->json([
                'success' => false,
                'message' => 'PDF gagal diambil. Pastikan dokumen dapat diakses melalui tautan dan dibagikan dengan izin yang sesuai.',
            ], 422);
        }

        if (strlen($pdf) > 50 * 1024 * 1024) {
            return response()->json([
                'success' => false,
                'message' => 'Ukuran PDF Google Docs melebihi batas 50 MB.',
            ], 422);
        }

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="laporan-'.$laporan->id_laporan.'.pdf"',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private function googleDriveFileId(?string $link): ?string
    {
        if (! is_string($link) || $link === '') {
            return null;
        }

        $parts = parse_url($link);
        if (
            ! is_array($parts)
            || strtolower($parts['scheme'] ?? '') !== 'https'
            || isset($parts['user'])
            || isset($parts['pass'])
            || isset($parts['port'])
            || ! in_array(strtolower($parts['host'] ?? ''), ['docs.google.com', 'drive.google.com'], true)
        ) {
            return null;
        }

        $path = $parts['path'] ?? '';
        $id = null;
        if (preg_match('~/(?:document|file)/d/([A-Za-z0-9_-]+)(?:/|$)~', $path, $matches)) {
            $id = $matches[1];
        } elseif (strtolower($parts['host']) === 'drive.google.com') {
            parse_str($parts['query'] ?? '', $query);
            $id = $query['id'] ?? null;
        }

        return is_string($id) && preg_match('/\A[A-Za-z0-9_-]{10,200}\z/', $id)
            ? $id
            : null;
    }

    private function fetchGooglePdf(string $fileId, bool $isGoogleDoc): ?string
    {
        $urls = $isGoogleDoc
            ? ["https://docs.google.com/document/d/{$fileId}/export?format=pdf"]
            : [
                "https://docs.google.com/document/d/{$fileId}/export?format=pdf",
                "https://drive.google.com/uc?export=download&id={$fileId}",
            ];

        foreach ($urls as $url) {
            for ($redirect = 0; $redirect <= 3; $redirect++) {
                try {
                    $response = Http::timeout(30)
                        ->withOptions(['allow_redirects' => false])
                        ->get($url);
                } catch (\Illuminate\Http\Client\ConnectionException) {
                    break;
                }

                if ($response->successful()) {
                    $body = $response->body();
                    if (str_starts_with(ltrim($body), '%PDF-')) {
                        return $body;
                    }
                    break;
                }

                if (! in_array($response->status(), [301, 302, 303, 307, 308], true)) {
                    break;
                }

                $location = $response->header('Location');
                if (! is_string($location)) {
                    break;
                }

                $redirectHost = strtolower((string) parse_url($location, PHP_URL_HOST));
                $isGoogleHost = in_array($redirectHost, [
                    'docs.google.com',
                    'drive.google.com',
                    'drive.usercontent.google.com',
                    'google.com',
                    'www.google.com',
                ], true) || str_ends_with($redirectHost, '.googleusercontent.com');
                if (
                    strtolower((string) parse_url($location, PHP_URL_SCHEME)) !== 'https'
                    || ! $isGoogleHost
                ) {
                    break;
                }

                $url = $location;
            }
        }

        return null;
    }

    public function saveFinalPdf(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:pdf|max:51200',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $laporan = LaporanMagang::find($id);
        if (! $laporan) {
            return response()->json([
                'success' => false,
                'message' => 'Laporan tidak ditemukan',
            ], 404);
        }

        if (! $this->canAccessReport($request, $laporan)) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak ditugaskan untuk mengelola laporan ini.',
            ], 403);
        }

        if (! in_array(strtolower(trim((string) $request->user()->role)), ['admin', 'pembimbing'], true)) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya pembimbing yang dapat menyimpan PDF final.',
            ], 403);
        }

        if ($laporan->status_laporan !== 'Selesai') {
            return response()->json([
                'success' => false,
                'message' => 'PDF final hanya dapat dibuat setelah laporan berstatus Selesai.',
            ], 422);
        }

        $newPath = $request->file('file')->store("laporan/{$laporan->id_peserta}/final", 'public');
        if (! is_string($newPath) || $newPath === '') {
            return response()->json([
                'success' => false,
                'message' => 'PDF final gagal disimpan.',
            ], 500);
        }

        $oldPath = $laporan->file_pdf_final;
        try {
            $laporan->file_pdf_final = $newPath;
            $laporan->save();
        } catch (\Throwable $exception) {
            Storage::disk('public')->delete($newPath);
            throw $exception;
        }

        if ($oldPath && $oldPath !== $newPath) {
            Storage::disk('public')->delete($oldPath);
        }

        return response()->json([
            'success' => true,
            'message' => 'PDF final berhasil disimpan.',
            'data' => $laporan,
        ]);
    }

    private function canAccessReport(Request $request, LaporanMagang $laporan): bool
    {
        $user = $request->user();
        $role = strtolower(trim((string) $user?->role));

        if ($role === 'admin') {
            return true;
        }

        if (in_array($role, ['peserta', 'pemagang', 'anak_magang'], true)) {
            return (int) $laporan->id_peserta === (int) $user?->id;
        }

        return $role === 'pembimbing'
            && Pendaftaran::where('user_id', $laporan->id_peserta)
                ->where('pembimbing_id', $user->id)
                ->where('status', 'Pemagang Aktif')
                ->exists();
    }

    /**
     * UC-03: Review & Moderasi Laporan (Pembimbing)
     */
    public function review(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status_laporan' => 'required|in:Menunggu Review,Revisi,Disetujui',
            'catatan_revisi' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $laporan = LaporanMagang::find($id);
        if (!$laporan) {
            return response()->json([
                'success' => false,
                'message' => 'Laporan tidak ditemukan'
            ], 404);
        }

        if (! $this->canAccessReport($request, $laporan)) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak ditugaskan untuk meninjau laporan ini.',
            ], 403);
        }

        $oldFinalPdfPath = $laporan->file_pdf_final;
        if ($oldFinalPdfPath) {
            $laporan->file_pdf_final = null;
        }
        $laporan->status_laporan = $request->status_laporan;
        if ($request->has('catatan_revisi')) {
            $laporan->catatan_revisi = $request->catatan_revisi;
        }

        $laporan->save();

        if ($oldFinalPdfPath && ! Storage::disk('public')->delete($oldFinalPdfPath)) {
            throw new \RuntimeException('Review berhasil disimpan, tetapi PDF final lama gagal dihapus.');
        }

        return response()->json([
            'success' => true,
            'message' => 'Review laporan berhasil disimpan',
            'data' => $laporan
        ]);
    }

    /**
     * UC-04: Mengunggah Ulang / Revisi Laporan (Anak Magang)
     */
    public function uploadRevisi(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'link_google_doc' => 'required|url|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $laporan = LaporanMagang::find($id);
        if (!$laporan) {
            return response()->json([
                'success' => false,
                'message' => 'Laporan tidak ditemukan'
            ], 404);
        }

        if (! $this->canAccessReport($request, $laporan)) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses untuk merevisi laporan ini.',
            ], 403);
        }

        $oldFinalPdfPath = $laporan->file_pdf_final;
        $data = DB::transaction(function () use ($request, $laporan) {
            $laporan->file_pdf_final = null;
            $revisi = RiwayatRevisiLaporan::create([
                'id_laporan' => $laporan->id_laporan,
                'link_google_doc' => $request->link_google_doc,
                'tanggal_revisi' => now(),
            ]);

            $laporan->status_laporan = 'Menunggu Review';
            $laporan->save();

            return [
                'laporan' => $laporan->load('riwayatRevisi'),
                'revisi' => $revisi,
            ];
        });

        if ($oldFinalPdfPath && ! Storage::disk('public')->delete($oldFinalPdfPath)) {
            throw new \RuntimeException('Revisi berhasil disimpan, tetapi PDF final lama gagal dihapus.');
        }

        return response()->json([
            'success' => true,
            'message' => 'Revisi laporan berhasil diunggah',
            'data' => $data
        ]);
    }

    /**
     * Destroy a report.
     */
    public function destroy(Request $request, $id)
    {
        $laporan = LaporanMagang::find($id);
        if (!$laporan) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan'
            ], 404);
        }

        $user = $request->user();
        $role = strtolower(trim((string) $user?->role));
        $canDelete = $role === 'admin'
            || (in_array($role, ['peserta', 'pemagang'], true)
                && (int) $laporan->id_peserta === (int) $user->id)
            || ($role === 'pembimbing' && Pendaftaran::where('user_id', $laporan->id_peserta)
                ->where('pembimbing_id', $user->id)
                ->where('status', 'Pemagang Aktif')
                ->exists());

        if (!$canDelete) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses untuk menghapus laporan ini.',
            ], 403);
        }

        $filePaths = [$laporan->file_laporan_pdf, $laporan->file_pdf_final];
        DB::transaction(function () use ($laporan) {
            $laporan->delete();
        });

        foreach ($filePaths as $filePath) {
            if (
                is_string($filePath)
                && $filePath !== ''
                && !filter_var($filePath, FILTER_VALIDATE_URL)
                && !str_contains($filePath, '..')
                && !str_starts_with($filePath, '/')
                && !preg_match('/^[A-Za-z]:/', $filePath)
                && Storage::disk('public')->exists($filePath)
                && !Storage::disk('public')->delete($filePath)
            ) {
                throw new \RuntimeException('Data laporan telah dihapus, tetapi berkas PDF gagal dihapus dari penyimpanan.');
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Data laporan berhasil dihapus'
        ]);
    }
}
