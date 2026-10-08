<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\CompanyProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CompanyProfileController extends Controller
{
    /**
     * Display Company Profile settings page.
     */
    public function index()
    {
        $profil = CompanyProfile::first();
        if (!$profil) {
            $profil = new CompanyProfile();
        }

        return view('profil.index', compact('profil'));
    }

    /**
     * Update Company Profile information.
     */
    public function update(Request $request): RedirectResponse
    {
        $profil = CompanyProfile::first();
        if (!$profil) {
            $profil = new CompanyProfile();
        }

        $validated = $request->validate([
            'nama_perusahaan' => 'required|string|max:255',
            'bidang_usaha' => 'required|string|max:255',
            'deskripsi_singkat' => 'required|string',
            'tentang_kantor' => 'required|string',
            'visi' => 'nullable|string',
            'misi' => 'nullable|string',
            'alamat' => 'nullable|string',
            'email' => 'nullable|email',
            'telepon' => 'nullable|string',
            'website' => 'nullable|url',
        ]);

        $profil->fill($validated);
        $profil->save();

        return redirect()->route('profil.index')->with('success', 'Profil perusahaan/kantor berhasil diperbarui!');
    }
}
