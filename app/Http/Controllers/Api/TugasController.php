<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tugas;
use App\Models\SubmisiTugas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TugasController extends Controller
{
    // Semua role: daftar tugas
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Tugas::latest();

        if ($user->role === 'peserta') {
            $query->with(['submisi' => function ($q) use ($user) {
                $q->where('user_id', $user->id);
            }]);
        } else {
            $query->with('submisi.peserta:id,name');
        }

        return response()->json($query->get());
    }

    // Semua role: detail 1 tugas
    public function show(Request $request, $id)
    {
        $user = $request->user();
        $query = Tugas::query();

        if ($user->role === 'peserta') {
            $query->with(['submisi' => function ($q) use ($user) {
                $q->where('user_id', $user->id);
            }]);
        } else {
            $query->with('submisi.peserta:id,name');
        }

        $tugas = $query->find($id);

        if (!$tugas) {
            return response()->json(['message' => 'Tugas tidak ditemukan'], 404);
        }

        return response()->json($tugas);
    }

    // Admin: membuat tugas baru
    public function store(Request $request)
    {
        $data = $request->validate([
            'judul'     => 'required|string|max:255',
            'deskripsi' => 'required|string',
            'deadline'  => 'required|date',
        ]);

        $data['created_by'] = $request->user()->id;

        $tugas = Tugas::create($data);

        return response()->json($tugas, 201);
    }

    // Admin: mengubah tugas
    public function update(Request $request, $id)
    {
        $tugas = Tugas::find($id);

        if (!$tugas) {
            return response()->json(['message' => 'Tugas tidak ditemukan'], 404);
        }

        $data = $request->validate([
            'judul'     => 'required|string|max:255',
            'deskripsi' => 'required|string',
            'deadline'  => 'required|date',
        ]);

        $tugas->update($data);

        return response()->json($tugas);
    }

    // Admin: menghapus tugas (file submisinya ikut dihapus)
    public function destroy($id)
    {
        $tugas = Tugas::with('submisi')->find($id);

        if (!$tugas) {
            return response()->json(['message' => 'Tugas tidak ditemukan'], 404);
        }

        foreach ($tugas->submisi as $submisi) {
            Storage::disk('public')->delete($submisi->file_path);
        }

        $tugas->delete();

        return response()->json(['message' => 'Tugas berhasil dihapus']);
    }

    // Peserta: mengumpulkan tugas (upload file)
    public function submit(Request $request, $id)
    {
        $request->validate([
            'file'            => 'required|file|mimes:pdf,doc,docx,zip,rar,jpg,jpeg,png|max:5120',
            'catatan_peserta' => 'nullable|string',
        ]);

        $tugas = Tugas::find($id);

        if (!$tugas) {
            return response()->json(['message' => 'Tugas tidak ditemukan'], 404);
        }

        // Deadline check removed so late submissions are allowed

        $userId = $request->user()->id;

        $submisi = SubmisiTugas::where('tugas_id', $id)
            ->where('user_id', $userId)
            ->first();

        if ($submisi && $submisi->status === 'sudah_dinilai') {
            return response()->json(['message' => 'Tugas sudah dinilai, tidak bisa dikumpulkan ulang'], 422);
        }

        if ($submisi) {
            Storage::disk('public')->delete($submisi->file_path);
        }

        // Menyimpan file ke storage/app/public/submisi_tugas
        $path = $request->file('file')->store('submisi_tugas', 'public');

        $submisi = SubmisiTugas::updateOrCreate(
            ['tugas_id' => $id, 'user_id' => $userId],
            [
                'file_path'       => $path,
                'catatan_peserta' => $request->catatan_peserta,
                'tanggal_submit'  => now(),
                'status'          => 'belum_dinilai',
            ]
        );

        return response()->json($submisi, 201);
    }

    // Peserta: melihat submisi milik sendiri
    public function submisiSaya(Request $request)
    {
        $submisi = SubmisiTugas::with('tugas:id,judul,deadline')
            ->where('user_id', $request->user()->id)
            ->latest('tanggal_submit')
            ->get();

        return response()->json($submisi);
    }

    // Admin & Pembimbing: daftar submisi untuk 1 tugas
    public function daftarSubmisi($id)
    {
        $tugas = Tugas::find($id);

        if (!$tugas) {
            return response()->json(['message' => 'Tugas tidak ditemukan'], 404);
        }

        $submisi = $tugas->submisi()
            ->with('peserta:id,name')
            ->latest('tanggal_submit')
            ->get();

        return response()->json($submisi);
    }

    // Pembimbing: semua submisi, bisa difilter ?status=belum_dinilai
    public function semuaSubmisi(Request $request)
    {
        $query = SubmisiTugas::with(['tugas:id,judul', 'peserta:id,name', 'penilai:id,name'])
            ->latest('tanggal_submit');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return response()->json($query->get());
    }

    // Pembimbing: mengunduh file submisi
    public function download($submisiId)
    {
        $submisi = SubmisiTugas::find($submisiId);

        if (!$submisi) {
            return response()->json(['message' => 'Submisi tidak ditemukan'], 404);
        }

        if (!Storage::disk('public')->exists($submisi->file_path)) {
            return response()->json(['message' => 'File tidak ditemukan'], 404);
        }

        $ext  = pathinfo($submisi->file_path, PATHINFO_EXTENSION);
        $nama = 'tugas_' . $submisi->tugas_id . '_peserta_' . $submisi->user_id . '.' . $ext;

        return Storage::disk('public')->download($submisi->file_path, $nama);
    }

    // Pembimbing: memberi nilai & feedback
    public function beriNilai(Request $request, $submisiId)
    {
        $request->validate([
            'nilai'    => 'required|integer|min:0|max:100',
            'feedback' => 'nullable|string',
        ]);

        $submisi = SubmisiTugas::find($submisiId);

        if (!$submisi) {
            return response()->json(['message' => 'Submisi tidak ditemukan'], 404);
        }

        $submisi->update([
            'nilai'           => $request->nilai,
            'feedback'        => $request->feedback,
            'status'          => 'sudah_dinilai',
            'dinilai_oleh'    => $request->user()->id,
            'tanggal_dinilai' => now(),
        ]);

        return response()->json($submisi->load(['peserta:id,name', 'penilai:id,name']));
    }
}