<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LaporanMagang;
use App\Models\Penilaian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PenilaianController extends Controller
{
    /**
     * UC-05: Penilaian Laporan (Pembimbing)
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_laporan_magang' => 'required|integer|exists:laporan_magang,id_laporan',
            'id_pembimbing' => 'required|integer',
            'judul_nilai' => 'nullable|string',
            'catatan' => 'nullable|string',
            'poin_nilai' => 'required|string',
            'nilai_laporan' => 'required|numeric|between:0,100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $penilaian = DB::transaction(function () use ($request) {
            $penilaian = Penilaian::create([
                'id_laporan_magang' => $request->id_laporan_magang,
                'id_pembimbing' => $request->id_pembimbing,
                'judul_nilai' => $request->judul_nilai ?? 'Penilaian Akhir Laporan Magang',
                'catatan' => $request->catatan,
                'tanggal_nilai' => now(),
                'poin_nilai' => $request->poin_nilai,
            ]);

            $laporan = LaporanMagang::findOrFail($request->id_laporan_magang);
            $laporan->nilai_laporan = $request->nilai_laporan;
            $laporan->tanggal_dinilai = now();
            $laporan->status_laporan = 'Disetujui';
            $laporan->save();

            return $penilaian->load('laporan');
        });

        return response()->json([
            'success' => true,
            'message' => 'Penilaian laporan berhasil disimpan',
            'data' => $penilaian
        ], 201);
    }

    /**
     * Get all evaluation records.
     */
    public function index()
    {
        $penilaian = Penilaian::with('laporan')->orderBy('id_penilaian', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $penilaian
        ]);
    }
}
