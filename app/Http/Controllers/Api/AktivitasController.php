<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AktivitasMagang;
use App\Models\KomentarAktivitas;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AktivitasController extends Controller
{
    /**
     * Get list of Aktivitas Magang (Input by Admin).
     * Accessible by public / users.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $query = AktivitasMagang::with(['user:id,name,email'])
            ->withCount('komentar');

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('judul', 'like', "%{$search}%")
                  ->orWhere('deskripsi', 'like', "%{$search}%")
                  ->orWhere('lokasi', 'like', "%{$search}%")
                  ->orWhere('kategori', 'like', "%{$search}%");
            });
        }

        if ($request->has('kategori') && !empty($request->kategori)) {
            $query->where('kategori', $request->kategori);
        }

        $aktivitas = $query->latest('tanggal')->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Daftar aktivitas magang berhasil dimuat',
            'total' => $aktivitas->count(),
            'data' => $aktivitas
        ], 200);
    }

    /**
     * Get detail of a specific Aktivitas Magang including all user comments.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {
        $aktivitas = AktivitasMagang::with([
            'user:id,name,email',
            'komentar.user:id,name,email'
        ])->find($id);

        if (!$aktivitas) {
            return response()->json([
                'status' => 'error',
                'message' => 'Aktivitas magang tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Detail aktivitas magang berhasil diambil',
            'data' => $aktivitas
        ], 200);
    }

    /**
     * User submits a comment on an Aktivitas Magang input by Admin.
     * Requires Sanctum Authentication (auth:sanctum).
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function storeKomentar(Request $request, $id): JsonResponse
    {
        $aktivitas = AktivitasMagang::find($id);

        if (!$aktivitas) {
            return response()->json([
                'status' => 'error',
                'message' => 'Aktivitas magang tidak ditemukan'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'komentar' => 'required|string|min:3',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi komentar gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $komentar = KomentarAktivitas::create([
            'aktivitas_magang_id' => $aktivitas->id,
            'user_id' => $request->user()->id,
            'komentar' => $request->komentar,
        ]);

        $komentar->load('user:id,name,email');

        return response()->json([
            'status' => 'success',
            'message' => 'Komentar Anda pada aktivitas magang berhasil dikirim!',
            'data' => $komentar
        ], 201);
    }

    /**
     * Input new Aktivitas Magang by Admin via API.
     * Requires Sanctum Authentication (auth:sanctum).
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'judul' => 'required|string|max:255',
            'deskripsi' => 'required|string',
            'tanggal' => 'required|date',
            'kategori' => 'nullable|string|max:100',
            'lokasi' => 'nullable|string|max:255',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:5048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi aktivitas gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $file = $request->file('foto');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/aktivitas'), $filename);
            $fotoPath = 'uploads/aktivitas/' . $filename;
        }

        $aktivitas = AktivitasMagang::create([
            'user_id' => $request->user()->id,
            'judul' => $request->judul,
            'deskripsi' => $request->deskripsi,
            'tanggal' => $request->tanggal,
            'kategori' => $request->kategori,
            'lokasi' => $request->lokasi,
            'foto' => $fotoPath,
            'status' => 'active',
        ]);

        $aktivitas->load('user:id,name,email');

        return response()->json([
            'status' => 'success',
            'message' => 'Aktivitas magang berhasil ditambahkan oleh admin',
            'data' => $aktivitas
        ], 201);
    }
}
