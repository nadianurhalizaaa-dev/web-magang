<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AlumniMagang;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AlumniController extends Controller
{
    /**
     * Display listing of alumni magang with search & filter.
     */
    public function index(Request $request)
    {
        $search = $request->query('search');
        $instansi = $request->query('instansi');

        $query = AlumniMagang::query();

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('asal_instansi', 'like', "%{$search}%")
                  ->orWhere('jurusan', 'like', "%{$search}%")
                  ->orWhere('periode_magang', 'like', "%{$search}%");
            });
        }

        if (!empty($instansi)) {
            $query->where('asal_instansi', $instansi);
        }

        $alumniList = $query->latest()->paginate(10)->withQueryString();
        $instansiList = AlumniMagang::distinct()->pluck('asal_instansi');

        return view('alumni.index', compact('alumniList', 'instansiList', 'search', 'instansi'));
    }

    /**
     * Store new Alumni Magang record.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'asal_instansi' => 'required|string|max:255',
            'jurusan' => 'required|string|max:255',
            'periode_magang' => 'required|string|max:255',
            'kesan_pesan' => 'required|string',
        ]);

        AlumniMagang::create($validated);

        return redirect()->route('alumni.index')->with('success', 'Data alumni magang berhasil ditambahkan!');
    }

    /**
     * Update Alumni Magang record.
     */
    public function update(Request $request, $id): RedirectResponse
    {
        $alumni = AlumniMagang::findOrFail($id);

        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'asal_instansi' => 'required|string|max:255',
            'jurusan' => 'required|string|max:255',
            'periode_magang' => 'required|string|max:255',
            'kesan_pesan' => 'required|string',
        ]);

        $alumni->update($validated);

        return redirect()->route('alumni.index')->with('success', 'Data alumni magang berhasil diperbarui!');
    }

    /**
     * Delete Alumni Magang record.
     */
    public function destroy($id): RedirectResponse
    {
        $alumni = AlumniMagang::findOrFail($id);
        $alumni->delete();

        return redirect()->route('alumni.index')->with('success', 'Data alumni magang berhasil dihapus!');
    }
}
