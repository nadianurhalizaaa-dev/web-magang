<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AktivitasMagang;
use App\Models\KomentarAktivitas;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AktivitasController extends Controller
{
    /**
     * Display listing of internship activities managed by Admin.
     */
    public function index(Request $request)
    {
        $search = $request->query('search');
        $kategori = $request->query('kategori');

        $query = AktivitasMagang::with(['user', 'komentar.user']);

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('judul', 'like', "%{$search}%")
                  ->orWhere('deskripsi', 'like', "%{$search}%")
                  ->orWhere('lokasi', 'like', "%{$search}%")
                  ->orWhere('kategori', 'like', "%{$search}%");
            });
        }

        if (!empty($kategori)) {
            $query->where('kategori', $kategori);
        }

        $aktivitasList = $query->latest('tanggal')->paginate(10)->withQueryString();
        $kategoriList = AktivitasMagang::whereNotNull('kategori')->distinct()->pluck('kategori');

        return view('aktivitas.index', compact('aktivitasList', 'kategoriList', 'search', 'kategori'));
    }

    /**
     * Store new Aktivitas Magang input by Admin.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'judul' => 'required|string|max:255',
            'deskripsi' => 'required|string',
            'tanggal' => 'required|date',
            'kategori' => 'nullable|string|max:100',
            'lokasi' => 'nullable|string|max:255',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:5048',
            'status' => 'nullable|string|in:active,archived,draft',
        ]);

        if ($request->hasFile('foto')) {
            $file = $request->file('foto');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/aktivitas'), $filename);
            $validated['foto'] = 'uploads/aktivitas/' . $filename;
        }

        $validated['user_id'] = Auth::id();
        $validated['status'] = $validated['status'] ?? 'active';

        AktivitasMagang::create($validated);

        return redirect()->route('aktivitas.index')->with('success', 'Aktivitas magang baru berhasil ditambahkan!');
    }

    /**
     * Update existing Aktivitas Magang.
     */
    public function update(Request $request, $id): RedirectResponse
    {
        $aktivitas = AktivitasMagang::findOrFail($id);

        $validated = $request->validate([
            'judul' => 'required|string|max:255',
            'deskripsi' => 'required|string',
            'tanggal' => 'required|date',
            'kategori' => 'nullable|string|max:100',
            'lokasi' => 'nullable|string|max:255',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:5048',
            'status' => 'nullable|string|in:active,archived,draft',
        ]);

        if ($request->hasFile('foto')) {
            // Delete previous image if exists
            if ($aktivitas->foto && file_exists(public_path($aktivitas->foto))) {
                unlink(public_path($aktivitas->foto));
            }

            $file = $request->file('foto');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/aktivitas'), $filename);
            $validated['foto'] = 'uploads/aktivitas/' . $filename;
        }

        $aktivitas->update($validated);

        return redirect()->route('aktivitas.index')->with('success', 'Data aktivitas magang berhasil diperbarui!');
    }

    /**
     * Delete Aktivitas Magang.
     */
    public function destroy($id): RedirectResponse
    {
        $aktivitas = AktivitasMagang::findOrFail($id);

        if ($aktivitas->foto && file_exists(public_path($aktivitas->foto))) {
            unlink(public_path($aktivitas->foto));
        }

        $aktivitas->delete();

        return redirect()->route('aktivitas.index')->with('success', 'Aktivitas magang berhasil dihapus!');
    }

    /**
     * Delete a specific user comment from the activity detail.
     */
    public function destroyKomentar($id): RedirectResponse
    {
        $komentar = KomentarAktivitas::findOrFail($id);
        $komentar->delete();

        return redirect()->route('aktivitas.index')->with('success', 'Komentar pengguna berhasil dihapus!');
    }
}
