<?php

use App\Http\Controllers\Web\AktivitasController;
use App\Http\Controllers\Web\AlumniController;
use App\Http\Controllers\Web\ApiDocsController;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\CompanyProfileController;
use App\Http\Controllers\Web\KomentarController;
use App\Http\Controllers\Web\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - Authentication & Enterprise Admin Panel
|--------------------------------------------------------------------------
*/

// Authentication Routes (Guest Only)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// Logout Route (Authenticated Users)
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Protected Admin Panel Routes (Requires Login)
Route::middleware('auth')->group(function () {

    // Default Main Route (Data Alumni Magang)
    Route::get('/', [AlumniController::class, 'index'])->name('admin.dashboard');

    // 1. Data Alumni Magang Management
    Route::get('/alumni', [AlumniController::class, 'index'])->name('alumni.index');
    Route::post('/alumni', [AlumniController::class, 'store'])->name('alumni.store');
    Route::post('/alumni/{id}/update', [AlumniController::class, 'update'])->name('alumni.update');
    Route::post('/alumni/{id}/delete', [AlumniController::class, 'destroy'])->name('alumni.delete');

    // 2. Aktivitas Magang & Komentar Management
    Route::get('/aktivitas', [AktivitasController::class, 'index'])->name('aktivitas.index');
    Route::post('/aktivitas', [AktivitasController::class, 'store'])->name('aktivitas.store');
    Route::post('/aktivitas/{id}/update', [AktivitasController::class, 'update'])->name('aktivitas.update');
    Route::post('/aktivitas/{id}/delete', [AktivitasController::class, 'destroy'])->name('aktivitas.delete');
    Route::post('/aktivitas/komentar/{id}/delete', [AktivitasController::class, 'destroyKomentar'])->name('aktivitas.komentar.delete');

    // 3. Profil Perusahaan Settings
    Route::get('/profil', [CompanyProfileController::class, 'index'])->name('profil.index');
    Route::post('/profil/update', [CompanyProfileController::class, 'update'])->name('profil.update');

    // 4. Komentar & Masukan Pengguna
    Route::get('/komentar', [KomentarController::class, 'index'])->name('komentar.index');
    Route::post('/komentar', [KomentarController::class, 'store'])->name('komentar.store');
    Route::post('/komentar/{id}/toggle', [KomentarController::class, 'toggleStatus'])->name('komentar.toggle');
    Route::post('/komentar/{id}/delete', [KomentarController::class, 'destroy'])->name('komentar.delete');

    // 5. Administrator / User Management
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::post('/users/{id}/update', [UserController::class, 'update'])->name('users.update');
    Route::post('/users/{id}/delete', [UserController::class, 'destroy'])->name('users.delete');

    // 6. REST API Explorer & React Integration Documentation
    Route::get('/api-docs', [ApiDocsController::class, 'index'])->name('api_docs.index');

});
