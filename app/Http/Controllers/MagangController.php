<?php

namespace App\Http\Controllers;

use App\Models\Magang;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class MagangController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(
            Magang::orderBy('participant_name')->get()
        );
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'participant_code' => ['required', 'string', 'max:30', 'unique:magangs,participant_code'],
            'participant_name' => ['required', 'string', 'max:100'],
            'institution' => ['nullable', 'string', 'max:120'],
        ]);

        $magang = Magang::create($validated);

        return response()->json([
            'message' => 'Data magang berhasil ditambahkan.',
            'data' => $magang,
        ], 201);
    }
}
