<?php

use App\Http\Controllers\AbsensiController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MagangController;
use App\Http\Controllers\PembimbingController;
use App\Models\Magang;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);
Route::get('/me', [AuthController::class, 'me']);

Route::get('/magang', [MagangController::class, 'index']);
Route::post('/magang', [MagangController::class, 'store']);

Route::apiResource('/pembimbing', PembimbingController::class);

Route::get('/absensi', [AbsensiController::class, 'index']);
Route::get('/absensi/hari-ini', [AbsensiController::class, 'today']);
Route::post('/absensi/masuk', [AbsensiController::class, 'checkIn']);
Route::post('/absensi/pulang', [AbsensiController::class, 'checkOut']);
Route::put('/absensi/{id}', [AbsensiController::class, 'update']);
Route::get('/absensi-saya', [AbsensiController::class, 'myAttendance']);