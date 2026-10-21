<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LokasiAbsensi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LokasiAbsensiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $isAdmin = strtolower((string) $user?->role) === 'admin';

        $query = LokasiAbsensi::query();

        if (! $isAdmin) {
            $query->where('is_active', true);
        }

        $locations = $query->orderByDesc('is_active')->latest('id')->get();

        return response()->json([
            'status' => 'success',
            'data' => $locations,
        ]);
    }

    public function show($id): JsonResponse
    {
        $location = LokasiAbsensi::findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => $location,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nama_lokasi' => ['required', 'string', 'max:255'],
            'alamat' => ['nullable', 'string', 'max:500'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'radius_meter' => ['required', 'integer', 'min:5', 'max:50000'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if (! isset($validated['is_active'])) {
            $validated['is_active'] = true;
        }

        $location = LokasiAbsensi::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Lokasi absensi berhasil ditambahkan.',
            'data' => $location,
        ], 201);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $location = LokasiAbsensi::findOrFail($id);

        $validated = $request->validate([
            'nama_lokasi' => ['required', 'string', 'max:255'],
            'alamat' => ['nullable', 'string', 'max:500'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'radius_meter' => ['required', 'integer', 'min:5', 'max:50000'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $location->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Lokasi absensi berhasil diperbarui.',
            'data' => $location,
        ]);
    }

    public function toggleActive($id): JsonResponse
    {
        $location = LokasiAbsensi::findOrFail($id);
        $location->is_active = ! $location->is_active;
        $location->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Status aktif lokasi berhasil diubah.',
            'data' => $location,
        ]);
    }

    public function destroy($id): JsonResponse
    {
        $location = LokasiAbsensi::findOrFail($id);
        $location->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Lokasi absensi berhasil dihapus.',
        ]);
    }
}
