<?php

use App\Http\Controllers\AbsensiController;
use App\Http\Controllers\Api\AuthController as ApiAuthController;
use App\Http\Controllers\MagangController;
use App\Http\Controllers\PembimbingController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PeriodeController;
use App\Http\Controllers\Api\PendaftaranController;
use App\Http\Controllers\Api\TugasController;
use App\Http\Controllers\Api\LaporanMagangController;
use App\Http\Controllers\Api\PenilaianController;

Route::post('/register', [ApiAuthController::class, 'register']);
Route::post('/login', [ApiAuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [ApiAuthController::class, 'logout']);
    Route::get('/me', [ApiAuthController::class, 'me']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::middleware('role:admin')->group(function () {
        Route::get('/admin/pembimbing', [ApiAuthController::class, 'getPembimbing']);
        Route::post('/admin/pembimbing', [ApiAuthController::class, 'createPembimbing']);
    });

    Route::get('/periode', [PeriodeController::class, 'index']);
    Route::middleware('role:admin')->group(function () {
        Route::post('/periode', [PeriodeController::class, 'store']);
        Route::put('/periode/{id}', [PeriodeController::class, 'update']);
        Route::delete('/periode/{id}', [PeriodeController::class, 'destroy']);
    });

    Route::middleware('role:admin')->group(function () {
        Route::get('/pendaftaran', [PendaftaranController::class, 'index']);
        Route::post('/pendaftaran/{id}/status', [PendaftaranController::class, 'update']);
    });
    Route::middleware('role:peserta')->group(function () {
        Route::get('/pendaftaran/me', [PendaftaranController::class, 'myRegistration']);
        Route::post('/pendaftaran', [PendaftaranController::class, 'store']);
        Route::put('/pendaftaran/cancel', [PendaftaranController::class, 'cancelRegistration']);
    });

    // The legacy attendance UI uses participant codes. Read and edit operations
    // are restricted to staff; participant attendance routes require an active
    // internship account. The current schema still needs an explicit account-to-
    // participant-code link to enforce ownership of that code.
    Route::middleware('role:admin,pembimbing')->group(function () {
        Route::get('/absensi', [AbsensiController::class, 'index']);
        Route::put('/absensi/{id}', [AbsensiController::class, 'update']);
    });
    Route::middleware(['role:admin,pembimbing,peserta', 'active.internship'])->group(function () {
        Route::get('/magang', [MagangController::class, 'index']);
        Route::get('/absensi/hari-ini', [AbsensiController::class, 'today']);
    });
    Route::middleware(['role:peserta', 'active.internship'])->group(function () {
        Route::post('/absensi/masuk', [AbsensiController::class, 'checkIn']);
        Route::post('/absensi/pulang', [AbsensiController::class, 'checkOut']);
        Route::get('/absensi-saya', [AbsensiController::class, 'myAttendance']);
    });

    Route::middleware('role:admin')->group(function () {
        Route::post('/magang', [MagangController::class, 'store']);
    });

    Route::middleware('role:admin')->group(function () {
        Route::apiResource('/pembimbing', PembimbingController::class);
    });
});

Route::get('/test', function () {
    return response()->json(['message' => 'Halo dari Laravel API!']);
});

// Endpoint laporan dan penilaian yang dipakai oleh fitur Laporan Magang React.
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    Route::middleware('role:admin,pembimbing,peserta')->group(function () {
        Route::get('/laporan-magang', [LaporanMagangController::class, 'index']);
        Route::get('/laporan-magang/{id}', [LaporanMagangController::class, 'show']);
        Route::get('/laporan-magang/{id}/download', [LaporanMagangController::class, 'download']);
    });

    Route::middleware('role:peserta')->group(function () {
        Route::post('/laporan-magang', [LaporanMagangController::class, 'store']);
        Route::post('/laporan-magang/{id}/revisi', [LaporanMagangController::class, 'uploadRevisi']);
    });

    Route::middleware('role:admin,pembimbing')->group(function () {
        Route::post('/laporan-magang/{id}/review', [LaporanMagangController::class, 'review']);
        Route::delete('/laporan-magang/{id}', [LaporanMagangController::class, 'destroy']);
        Route::get('/penilaian', [PenilaianController::class, 'index']);
        Route::post('/penilaian', [PenilaianController::class, 'store']);
    });
});

// Tugas & submisi hanya dapat diakses dengan sesi login yang valid.
Route::middleware('auth:sanctum')->group(function () {
    // Semua akun dengan role ini boleh melihat tugas. Status Pemagang Aktif
    // tetap diwajibkan untuk mengumpulkan tugas di bawah.
    Route::middleware('role:admin,pembimbing,peserta')->group(function () {
        Route::get('/tugas', [TugasController::class, 'index']);
        Route::get('/tugas/{id}', [TugasController::class, 'show']);
    });

    Route::middleware('role:admin,pembimbing')->group(function () {
        Route::post('/tugas', [TugasController::class, 'store']);
        Route::put('/tugas/{id}', [TugasController::class, 'update']);
        Route::delete('/tugas/{id}', [TugasController::class, 'destroy']);
        Route::get('/tugas/{id}/submisi', [TugasController::class, 'daftarSubmisi']);
        Route::get('/submisi', [TugasController::class, 'semuaSubmisi']);
        Route::get('/submisi/{submisiId}/download', [TugasController::class, 'download']);
        Route::put('/submisi/{submisiId}/nilai', [TugasController::class, 'beriNilai']);
        Route::post('/submisi/{submisiId}/nilai', [TugasController::class, 'beriNilai']);
        Route::post('/submisi/{submisiId}/penilaian', [TugasController::class, 'beriNilai']);
    });

    Route::middleware(['role:peserta', 'active.internship'])->group(function () {
        Route::post('/tugas/{id}/submit', [TugasController::class, 'submit']);
        Route::post('/tugas/{id}/submisi', [TugasController::class, 'submit']);
        Route::get('/submisi-saya', [TugasController::class, 'submisiSaya']);
    });
});
