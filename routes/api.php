<?php

use App\Http\Controllers\AbsensiController;
use App\Http\Controllers\Api\AuthController as ApiAuthController;
use App\Http\Controllers\MagangController;
use App\Http\Controllers\PembimbingController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PeriodeController;
use App\Http\Controllers\Api\PendaftaranController;

Route::post('/register', [ApiAuthController::class, 'register']);
Route::post('/login', [ApiAuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [ApiAuthController::class, 'logout']);
    Route::get('/me', [ApiAuthController::class, 'me']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::get('/admin/pembimbing', [ApiAuthController::class, 'getPembimbing']);
    Route::post('/admin/pembimbing', [ApiAuthController::class, 'createPembimbing']);

    Route::get('/periode', [PeriodeController::class, 'index']);
    Route::post('/periode', [PeriodeController::class, 'store']);
    Route::put('/periode/{id}', [PeriodeController::class, 'update']);
    Route::delete('/periode/{id}', [PeriodeController::class, 'destroy']);

    Route::get('/pendaftaran', [PendaftaranController::class, 'index']);
    Route::get('/pendaftaran/me', [PendaftaranController::class, 'myRegistration']);
    Route::post('/pendaftaran', [PendaftaranController::class, 'store']);
    Route::post('/pendaftaran/{id}/status', [PendaftaranController::class, 'update']);
    Route::put('/pendaftaran/cancel', [PendaftaranController::class, 'cancelRegistration']);
});

Route::get('/magang', [MagangController::class, 'index']);
Route::post('/magang', [MagangController::class, 'store']);
Route::apiResource('/pembimbing', PembimbingController::class);

Route::get('/absensi', [AbsensiController::class, 'index']);
Route::get('/absensi/hari-ini', [AbsensiController::class, 'today']);
Route::post('/absensi/masuk', [AbsensiController::class, 'checkIn']);
Route::post('/absensi/pulang', [AbsensiController::class, 'checkOut']);
Route::put('/absensi/{id}', [AbsensiController::class, 'update']);
Route::get('/absensi-saya', [AbsensiController::class, 'myAttendance']);
