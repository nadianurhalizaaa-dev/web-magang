<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pendaftaran;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class PendaftaranController extends Controller
{
    // Admin views all
    public function index()
    {
        $pendaftarans = Pendaftaran::with(['user', 'periode', 'pembimbing'])->get();
        return response()->json(['data' => $pendaftarans]);
    }

    // User views own registration
    public function myRegistration()
    {
        $user = Auth::user();
        $pendaftaran = Pendaftaran::with(['periode', 'pembimbing'])->where('user_id', $user->id)->first();
        return response()->json(['data' => $pendaftaran]);
    }

    // Pemagang submit registration
    public function store(Request $request)
    {
        $request->validate([
            'periode_id' => 'required|exists:periode_magangs,id',
            'asal_instansi' => 'required|string|max:150',
            'file_surat_pengantar' => 'required|file|mimes:pdf|max:2048',
        ]);

        $user = Auth::user();

        // Check if already registered
        $existing = Pendaftaran::where('user_id', $user->id)->first();
        if ($existing) {
            return response()->json(['message' => 'Anda sudah mendaftar magang'], 400);
        }

        // Handle file upload
        $file = $request->file('file_surat_pengantar');
        $filename = time() . '_' . $file->getClientOriginalName();
        $path = $file->storeAs('public/surat_pengantar', $filename);

        $pendaftaran = Pendaftaran::create([
            'user_id' => $user->id,
            'periode_id' => $request->periode_id,
            'asal_instansi' => $request->asal_instansi,
            'file_surat_pengantar' => 'storage/surat_pengantar/' . $filename,
            'tanggal_daftar' => now(),
            'status' => 'Calon Pemagang',
        ]);

        return response()->json(['data' => $pendaftaran, 'message' => 'Pendaftaran berhasil dikirim']);
    }

    // Admin updates status
    public function update(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string|in:Calon Pemagang,Pemagang Aktif,Ditolak,Alumni',
            'pembimbing_id' => [
                'nullable',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('role', 'pembimbing')),
            ],
            'tanggal_selesai' => 'nullable|date',
            'file_surat_balasan' => 'nullable|file|mimes:pdf|max:2048',
        ]);

        $pendaftaran = Pendaftaran::findOrFail($id);
        $pendaftaran->status = $request->status;
        $pendaftaran->pembimbing_id = $request->pembimbing_id;
        $pendaftaran->tanggal_selesai = $request->tanggal_selesai;

        if ($request->hasFile('file_surat_balasan')) {
            $file = $request->file('file_surat_balasan');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('public/surat_balasan', $filename);
            $pendaftaran->file_surat_balasan = 'storage/surat_balasan/' . $filename;
        }

        $pendaftaran->save();

        return response()->json(['data' => $pendaftaran, 'message' => 'Status pendaftaran berhasil diupdate']);
    }

    // Pemagang membatalkan pendaftaran
    public function cancelRegistration()
    {
        $user = Auth::user();

        $pendaftaran = Pendaftaran::where('user_id', $user->id)->first();

        if (!$pendaftaran) {
            return response()->json(['message' => 'Data pendaftaran tidak ditemukan'], 404);
        }

        if ($pendaftaran->status != 'Calon Pemagang') {
            return response()->json(['message' => 'Tidak dapat membatalkan pendaftaran pada tahap ini'], 400);
        }

        $pendaftaran->status = 'Dibatalkan';
        $pendaftaran->save();

        return response()->json([
            'data' => $pendaftaran,
            'message' => 'Pengajuan magang berhasil dibatalkan'
        ]);
    }
}
