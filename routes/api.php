<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;

Route::post('/login', [AuthController::class, 'login'])->name('api.login');

// Tuyến đường test (Bắt buộc phải có Token mới vào được)
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
