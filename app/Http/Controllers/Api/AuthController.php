<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nama' => 'required_without:name|string|max:255',
            'name' => 'required_without:nama|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8',
            'role' => ['sometimes', Rule::in(['Pemagang', 'pemagang', 'peserta'])],
            'no_hp' => 'nullable|string|max:255',
        ]);

        $user = User::create([
            'name' => $validated['nama'] ?? $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'peserta',
            'no_hp' => $validated['no_hp'] ?? null,
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Registrasi akun berhasil',
            'data' => [
                'user' => $this->presentUser($user),
                'access_token' => $token,
                'token_type' => 'Bearer',
            ],
        ], 201);
    }

    public function createPembimbing(Request $request): JsonResponse
    {
        if (strtolower((string) $request->user()?->role) !== 'admin') {
            return response()->json([
                'message' => 'Hanya Admin yang dapat menambahkan Pembimbing.',
            ], 403);
        }

        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8',
            'no_hp' => 'nullable|string|max:255',
        ]);

        $user = User::create([
            'name' => $validated['nama'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'pembimbing',
            'no_hp' => $validated['no_hp'] ?? null,
        ]);

        return response()->json([
            'data' => $this->presentUser($user),
            'message' => 'Akun Pembimbing berhasil dibuat.',
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Email atau password salah',
            ], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Login berhasil',
            'data' => [
                'user' => $this->presentUser($user),
                'access_token' => $token,
                'token_type' => 'Bearer',
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Berhasil logout',
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => $this->presentUser($request->user()),
        ]);
    }

    public function getPembimbing(Request $request): JsonResponse
    {
        if (strtolower((string) $request->user()?->role) !== 'admin') {
            return response()->json([
                'message' => 'Hanya Admin yang dapat melihat daftar Pembimbing.',
            ], 403);
        }

        $pembimbings = User::where('role', 'pembimbing')->get();

        return response()->json([
            'data' => $pembimbings->map(fn (User $user) => $this->presentUser($user)),
        ]);
    }

    private function presentUser(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name ?? $user->nama,
            'nama' => $user->name ?? $user->nama,
            'email' => $user->email,
            'role' => $user->role,
            'no_hp' => $user->no_hp,
            'foto' => $user->foto,
        ];
    }
}
