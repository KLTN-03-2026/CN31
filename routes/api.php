<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PhieuYeuCauController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route; // Thêm dòng use này

Route::post('/login', [AuthController::class, 'login']);

// KHU VỰC ĐƯỢC BẢO VỆ BỞI SANCTUM (BẮT BUỘC CÓ TOKEN)
Route::middleware('auth:sanctum')->group(function () {

    // Tuyến đường test
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // API Lấy danh sách phiếu chờ duyệt cho Mobile App
    Route::get('/phieu-yeu-cau/cho-duyet', [PhieuYeuCauController::class, 'danhSachChoDuyet']);
    // API Xử lý phiếu (Duyệt/Từ chối) cho Mobile App
    Route::post('/phieu-yeu-cau/{id}/xu-ly', [PhieuYeuCauController::class, 'xuLy']);

    // API Lấy chi tiết phiếu cho Mobile App
    Route::get('/phieu-yeu-cau/{id}', [PhieuYeuCauController::class, 'show']);

});
