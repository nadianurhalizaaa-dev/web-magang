<?php

namespace App\Http\Controllers;

use App\Models\Pembimbing;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PembimbingController extends Controller
{
    /**
     * Display a listing of the resource.
     * Mengembalikan seluruh data dari Tabel Pembimbing.
     */
    public function index()
    {
        return response()->json(Pembimbing::orderBy('id', 'asc')->get());
    }

    /**
     * Store a newly created resource in storage.
     * Sesuai ERD: nama (100), email (100), no_hp (20), bidang (100).
     * Otomatis membuat akun user dengan role pembimbing & password default.
     */
    public function store(Request $request)
    {
        $table = (new Pembimbing())->getTable();

        $validated = $request->validate([
            'nama' => 'required|string|max:100',
            'email' => "required|email|max:100|unique:{$table},email",
            'no_hp' => 'nullable|string|max:20',
            'bidang' => 'nullable|string|max:100',
        ]);

        $pembimbing = Pembimbing::create($validated);

        // Sinkronisasi otomatis ke akun User agar pembimbing bisa langsung login
        User::firstOrCreate(
            ['email' => $validated['email']],
            [
                'name' => $validated['nama'],
                'password' => Hash::make('password123'),
                'role' => 'pembimbing',
                'no_hp' => $validated['no_hp'] ?? null,
            ]
        );

        return response()->json($pembimbing, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $pembimbing = Pembimbing::findOrFail($id);
        return response()->json($pembimbing);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $pembimbing = Pembimbing::findOrFail($id);
        $table = $pembimbing->getTable();

        $validated = $request->validate([
            'nama' => 'required|string|max:100',
            'email' => "required|email|max:100|unique:{$table},email," . $id,
            'no_hp' => 'nullable|string|max:20',
            'bidang' => 'nullable|string|max:100',
        ]);

        $oldEmail = $pembimbing->email;
        $pembimbing->update($validated);

        // Perbarui data di tabel user terkait jika ada
        $user = User::where('email', $oldEmail)->first();
        if ($user) {
            $user->update([
                'name' => $validated['nama'],
                'email' => $validated['email'],
                'no_hp' => $validated['no_hp'] ?? null,
                'role' => 'pembimbing',
            ]);
        }

        return response()->json($pembimbing);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $pembimbing = Pembimbing::findOrFail($id);
        $email = $pembimbing->email;
        $pembimbing->delete();

        // Hapus akun user role pembimbing terkait jika diperlukan
        User::where('email', $email)->where('role', 'pembimbing')->delete();
        
        return response()->json(['message' => 'Data pembimbing berhasil dihapus']);
    }
}

