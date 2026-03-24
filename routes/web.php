<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PhieuYeuCauController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VnpayController;
use Inertia\Inertia;

// Route công khai (Ai cũng vào được)
Route::inertia('/', 'Home')->name('home');
Route::inertia('/Page-Test', 'Page-Test')->name('home-test');
//   dành cho KHÁCH (Chưa đăng nhập)
Route::middleware('guest')->group(function () {
    Route::inertia('/register', 'Auth/Register')->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');

    Route::inertia('/login', 'Auth/Login')->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
});

//   dành cho THÀNH VIÊN (Đã đăng nhập)
Route::middleware('auth')->group(function () {
    // Route Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Route tạo phiếu yêu cầu
    // Route::inertia('/phieu-yeu-cau', 'PhieuYeuCau/TaoMoi')->name('phieu.create');
    Route::get('/phieu-yeu-cau', [PhieuYeuCauController::class, 'create'])->name('phieu.create');
    Route::post('/phieu-yeu-cau', [PhieuYeuCauController::class, 'store'])->name('phieu.store');

    // Route xem chi tiết (VD: /phieu-yeu-cau/5)
    Route::get('/phieu-yeu-cau/{id}', [PhieuYeuCauController::class, 'show'])->name('phieu.show');

    // Route Logout (Phải là POST)
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Route xử lý duyệt (Đặt trong middleware auth)
    Route::post('/phieu-yeu-cau/{id}/duyet', [PhieuYeuCauController::class, 'approve'])->name('phieu.duyet');
    Route::get('/phieu-yeu-cau/{id}/in', [PhieuYeuCauController::class, 'print'])->name('phieu.print');

    // Route nhân viên hủy phiếu yêu cầu
    Route::post('/phieu-yeu-cau/{id}/huy', [PhieuYeuCauController::class,'cancel'])->name('phieu.cancel');

    // --- ROUTE THANH TOÁN VNPAY ---
    Route::post('/phieu-yeu-cau/{id}/vnpay', [VnpayController::class, 'createPayment'])->name('vnpay.create');
    Route::get('/vnpay-return', [VnpayController::class, 'vnpayReturn'])->name('vnpay.return');

// --- ROUTE QUẢN LÝ NHÂN SỰ (Chỉ Admin) ---
    Route::get('/users', [\App\Http\Controllers\UserController::class, 'index'])->name('users.index');
    Route::put('/users/{id}', [\App\Http\Controllers\UserController::class, 'update'])->name('users.update');
});



