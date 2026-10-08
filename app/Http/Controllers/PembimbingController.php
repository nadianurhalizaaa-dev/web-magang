<?php

namespace App\Http\Controllers;

use App\Models\Pembimbing;
use Illuminate\Http\Request;

class PembimbingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(Pembimbing::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:100',
            'email' => 'required|email|unique:pembimbings,email|max:100',
            'no_hp' => 'nullable|string|max:20',
            'bidang' => 'nullable|string|max:100',
        ]);

        $pembimbing = Pembimbing::create($validated);
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

        $validated = $request->validate([
            'nama' => 'required|string|max:100',
            'email' => 'required|email|max:100|unique:pembimbings,email,' . $id,
            'no_hp' => 'nullable|string|max:20',
            'bidang' => 'nullable|string|max:100',
        ]);

        $pembimbing->update($validated);
        return response()->json($pembimbing);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $pembimbing = Pembimbing::findOrFail($id);
        $pembimbing->delete();
        
        return response()->json(['message' => 'Data pembimbing berhasil dihapus']);
    }
}
