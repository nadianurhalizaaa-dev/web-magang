<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\KomentarAplikasi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class KomentarController extends Controller
{
    /**
     * Display listing of user comments & feedback.
     */
    public function index(Request $request)
    {
        $search = $request->query('search');
        $status = $request->query('status');

        $query = KomentarAplikasi::with('user');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_pengguna', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('komentar', 'like', "%{$search}%");
            });
        }

        if (!empty($status)) {
            $query->where('status', $status);
        }

        $komentarList = $query->latest()->paginate(10)->withQueryString();

        $statistik = [
            'total_komentar' => KomentarAplikasi::count(),
            'total_published' => KomentarAplikasi::where('status', 'published')->count(),
            'total_pending' => KomentarAplikasi::where('status', 'pending')->count(),
            'avg_rating' => round(KomentarAplikasi::avg('rating') ?? 5.0, 1),
        ];

        return view('komentar.index', compact('komentarList', 'statistik', 'search', 'status'));
    }

    /**
     * Store new comment from admin panel.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama_pengguna' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'rating' => 'required|integer|min:1|max:5',
            'komentar' => 'required|string',
            'status' => 'required|string|in:published,pending',
        ]);

        KomentarAplikasi::create($validated);

        return redirect()->route('komentar.index')->with('success', 'Komentar pengguna berhasil ditambahkan!');
    }

    /**
     * Toggle comment status (published <-> pending).
     */
    public function toggleStatus($id): RedirectResponse
    {
        $komentar = KomentarAplikasi::findOrFail($id);
        $komentar->status = $komentar->status === 'published' ? 'pending' : 'published';
        $komentar->save();

        return redirect()->route('komentar.index')->with('success', 'Status publikasi komentar berhasil diperbarui!');
    }

    /**
     * Delete comment record.
     */
    public function destroy($id): RedirectResponse
    {
        $komentar = KomentarAplikasi::findOrFail($id);
        $komentar->delete();

        return redirect()->route('komentar.index')->with('success', 'Komentar pengguna berhasil dihapus!');
    }
}
