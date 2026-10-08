<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AlumniMagang;
use App\Models\CompanyProfile;
use App\Models\KomentarAplikasi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BerandaController extends Controller
{
    /**
     * Get aggregate data for the homepage (Beranda).
     * Designed for React frontend consumption.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $profil = CompanyProfile::first();
        $alumni = AlumniMagang::latest()->get();
        $komentar = KomentarAplikasi::with('user:id,name,email')->where('status', 'published')->latest()->get();

        // Calculate dynamic summary stats
        $totalAlumni = AlumniMagang::count();
        $totalInstansi = AlumniMagang::distinct('asal_instansi')->count('asal_instansi');

        return response()->json([
            'status' => 'success',
            'message' => 'Data beranda berhasil dimuat',
            'data' => [
                'profil_kantor' => $profil ? [
                    'nama_perusahaan' => $profil->nama_perusahaan,
                    'deskripsi_singkat' => $profil->deskripsi_singkat,
                    'tentang_kantor' => $profil->tentang_kantor,
                    'bidang_usaha' => $profil->bidang_usaha,
                    'visi' => $profil->visi,
                    'misi' => $profil->misi,
                    'alamat' => $profil->alamat,
                    'email' => $profil->email,
                    'telepon' => $profil->telepon,
                    'website' => $profil->website,
                ] : null,
                'statistik' => [
                    'total_alumni' => $totalAlumni,
                    'total_instansi_mitra' => $totalInstansi,
                    'avg_rating' => round(KomentarAplikasi::avg('rating') ?? 5.0, 1),
                ],
                'alumni_magang' => $alumni,
                'komentar_aplikasi' => $komentar,
            ]
        ], 200);
    }

    /**
     * Get list of alumni magang with optional search & filter.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getAlumni(Request $request): JsonResponse
    {
        $query = AlumniMagang::query();

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('asal_instansi', 'like', "%{$search}%")
                  ->orWhere('jurusan', 'like', "%{$search}%")
                  ->orWhere('kesan_pesan', 'like', "%{$search}%");
            });
        }

        $alumni = $query->latest()->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Data alumni magang berhasil diambil',
            'total' => $alumni->count(),
            'data' => $alumni
        ], 200);
    }

    /**
     * Get detailed office/company profile information.
     *
     * @return JsonResponse
     */
    public function getProfil(): JsonResponse
    {
        $profil = CompanyProfile::first();

        if (!$profil) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data profil kantor belum dikonfigurasi'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $profil
        ], 200);
    }

    /**
     * Get list of published user application comments/feedback.
     *
     * @return JsonResponse
     */
    public function getKomentar(): JsonResponse
    {
        $komentar = KomentarAplikasi::with('user:id,name,email')->where('status', 'published')->latest()->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Data komentar pengguna berhasil diambil',
            'total' => $komentar->count(),
            'avg_rating' => round(KomentarAplikasi::avg('rating') ?? 5.0, 1),
            'data' => $komentar
        ], 200);
    }

    /**
     * Submit new user application comment / feedback from React.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function storeKomentar(Request $request): JsonResponse
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'nama_pengguna' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'rating' => 'required|integer|min:1|max:5',
            'komentar' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi komentar gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $komentar = KomentarAplikasi::create([
            'user_id' => $user ? $user->id : null,
            'nama_pengguna' => $request->nama_pengguna ?? ($user ? $user->name : 'Pengguna App'),
            'email' => $request->email ?? ($user ? $user->email : null),
            'rating' => $request->rating,
            'komentar' => $request->komentar,
            'status' => 'published',
        ]);

        $komentar->load('user:id,name,email');

        return response()->json([
            'status' => 'success',
            'message' => 'Komentar dan masukan Anda berhasil dikirim! Terima kasih.',
            'data' => $komentar
        ], 201);
    }
}
