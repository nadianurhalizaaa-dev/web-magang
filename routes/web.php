<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes (Headless REST API Backend)
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return response()->json([
        'status' => 'online',
        'message' => 'Backend REST API Server is running',
        'api_base_url' => url('/api')
    ]);
});

Route::get('/api-docs', function () {
    return redirect('/api');
});

// Penanganan typo umum seperti kurung tutup / tanda baca tak sengaja
Route::any('/api)', function () {
    return redirect('/api');
});
