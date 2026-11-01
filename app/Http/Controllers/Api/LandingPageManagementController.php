<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AktivitasMagang;
use App\Models\AlumniMagang;
use App\Models\CompanyProfile;
use App\Models\KomentarAplikasi;
use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class LandingPageManagementController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Data pengelolaan landing page berhasil dimuat',
            'data' => [
                'profil_kantor' => CompanyProfile::first(),
                'alumni_magang' => AlumniMagang::latest()->get(),
                'aktivitas_magang' => AktivitasMagang::with('user:id,name,email')
                    ->withCount('komentar')
                    ->latest('tanggal')
                    ->get(),
                'komentar_aplikasi' => KomentarAplikasi::with('user:id,name,email')
                    ->latest()
                    ->get(),
            ],
        ]);
    }

    public function updateProfil(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'nama_perusahaan' => 'required|string|max:255',
            'deskripsi_singkat' => 'required|string',
            'tentang_kantor' => 'required|string',
            'bidang_usaha' => 'required|string|max:255',
            'visi' => 'nullable|string',
            'misi' => 'nullable|string',
            'alamat' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'telepon' => 'nullable|string|max:255',
            'website' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator, 'Validasi profil kantor gagal');
        }

        $profil = CompanyProfile::first();
        if ($profil) {
            $profil->update($validator->validated());
        } else {
            $profil = CompanyProfile::create($validator->validated());
        }

        return $this->success('Profil kantor berhasil diperbarui', $profil);
    }

    public function storeAlumni(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), $this->alumniRules());
        if ($validator->fails()) {
            return $this->validationError($validator, 'Validasi data alumni gagal');
        }

        $alumni = AlumniMagang::create($validator->validated());

        return $this->success('Data alumni berhasil ditambahkan', $alumni, 201);
    }

    public function updateAlumni(Request $request, $id): JsonResponse
    {
        $alumni = AlumniMagang::findOrFail($id);
        $validator = Validator::make($request->all(), $this->alumniRules(true));
        if ($validator->fails()) {
            return $this->validationError($validator, 'Validasi data alumni gagal');
        }

        $alumni->update($validator->validated());

        return $this->success('Data alumni berhasil diperbarui', $alumni->refresh());
    }

    public function destroyAlumni($id): JsonResponse
    {
        AlumniMagang::findOrFail($id)->delete();

        return $this->success('Data alumni berhasil dihapus');
    }

    public function storeAktivitas(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), $this->aktivitasRules());
        if ($validator->fails()) {
            return $this->validationError($validator, 'Validasi aktivitas gagal');
        }

        $data = $validator->validated();
        $data['user_id'] = $request->user()->id;
        $data['status'] = $data['status'] ?? 'active';
        if ($request->hasFile('foto')) {
            $data['foto'] = $this->storeFoto($request);
        }

        $aktivitas = AktivitasMagang::create($data);
        $aktivitas->load('user:id,name,email');

        return $this->success('Aktivitas magang berhasil ditambahkan', $aktivitas, 201);
    }

    public function updateAktivitas(Request $request, $id): JsonResponse
    {
        $aktivitas = AktivitasMagang::findOrFail($id);
        $validator = Validator::make($request->all(), $this->aktivitasRules(true));
        if ($validator->fails()) {
            return $this->validationError($validator, 'Validasi aktivitas gagal');
        }

        $data = $validator->validated();
        $oldFoto = $aktivitas->foto;
        $replaceFoto = $request->hasFile('foto');
        $removeFoto = array_key_exists('foto', $data) && $data['foto'] === null;

        if ($replaceFoto) {
            $data['foto'] = $this->storeFoto($request);
        }

        $aktivitas->update($data);
        if ($replaceFoto || $removeFoto) {
            $this->deleteManagedFoto($oldFoto);
        }

        $aktivitas->load('user:id,name,email');

        return $this->success('Aktivitas magang berhasil diperbarui', $aktivitas->refresh());
    }

    public function destroyAktivitas($id): JsonResponse
    {
        $aktivitas = AktivitasMagang::findOrFail($id);
        $foto = $aktivitas->foto;
        $aktivitas->delete();
        $this->deleteManagedFoto($foto);

        return $this->success('Aktivitas magang berhasil dihapus');
    }

    public function updateKomentarStatus(Request $request, $id): JsonResponse
    {
        $komentar = KomentarAplikasi::findOrFail($id);
        $validator = Validator::make($request->all(), [
            'status' => ['required', 'string', Rule::in(['published', 'pending'])],
        ]);
        if ($validator->fails()) {
            return $this->validationError($validator, 'Validasi status komentar gagal');
        }

        $komentar->update($validator->validated());

        return $this->success('Status komentar berhasil diperbarui', $komentar->load('user:id,name,email'));
    }

    public function destroyKomentar($id): JsonResponse
    {
        KomentarAplikasi::findOrFail($id)->delete();

        return $this->success('Komentar berhasil dihapus');
    }

    private function alumniRules(bool $partial = false): array
    {
        $required = $partial ? 'sometimes|required' : 'required';

        return [
            'nama' => "$required|string|max:255",
            'asal_instansi' => "$required|string|max:255",
            'jurusan' => "$required|string|max:255",
            'periode_magang' => "$required|string|max:255",
            'kesan_pesan' => "$required|string",
        ];
    }

    private function aktivitasRules(bool $partial = false): array
    {
        $required = $partial ? 'sometimes|required' : 'required';

        return [
            'judul' => "$required|string|max:255",
            'deskripsi' => "$required|string",
            'tanggal' => "$required|date",
            'kategori' => 'sometimes|nullable|string|max:100',
            'lokasi' => 'sometimes|nullable|string|max:255',
            'foto' => 'sometimes|nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5048',
            'status' => ['sometimes', 'required', 'string', Rule::in(['active', 'draft', 'archived'])],
        ];
    }

    private function storeFoto(Request $request): string
    {
        $path = $request->file('foto')->store('uploads/aktivitas', 'public');
        if (! $path) {
            throw new \RuntimeException('Foto aktivitas gagal disimpan.');
        }

        return 'storage/'.$path;
    }

    private function deleteManagedFoto(?string $foto): void
    {
        $prefix = 'storage/uploads/aktivitas/';
        if ($foto && str_starts_with($foto, $prefix)) {
            Storage::disk('public')->delete(substr($foto, strlen('storage/')));
        }
    }

    private function validationError(ValidatorContract $validator, string $message): JsonResponse
    {
        return response()->json([
            'status' => 'error',
            'message' => $message,
            'errors' => $validator->errors(),
        ], 422);
    }

    private function success(string $message, mixed $data = null, int $status = 200): JsonResponse
    {
        $response = [
            'status' => 'success',
            'message' => $message,
        ];
        if ($data !== null) {
            $response['data'] = $data;
        }

        return response()->json($response, $status);
    }
}
