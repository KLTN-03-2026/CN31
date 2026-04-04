<?php

use Illuminate\Support\Facades\Route;

// Khai báo tập trung toàn bộ Controller ở trên cùng cho code sạch đẹp
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PhieuYeuCauController;
use App\Http\Controllers\MuaSamController;
use App\Http\Controllers\NghiPhepController;
use App\Http\Controllers\VnpayController;
use App\Http\Controllers\ThanhToanController;
use App\Http\Controllers\DanhMucController;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// ==========================================
// ROUTE CÔNG KHAI (Không cần đăng nhập)
// ==========================================
Route::inertia('/', 'Home')->name('home');
Route::inertia('/Page-Test', 'Page-Test')->name('home-test');

Route::middleware('guest')->group(function () {
    Route::inertia('/register', 'Auth/Register')->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');

    Route::inertia('/login', 'Auth/Login')->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
});

// ==========================================
// ROUTE HỆ THỐNG (Bắt buộc đăng nhập)
// ==========================================
Route::middleware('auth')->group(function () {

    // 1. Dashboard & Đăng xuất
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    // API đánh dấu đã đọc thông báo
    Route::post('/notifications/{id}/read', [PhieuYeuCauController::class, 'markNotificationAsRead'])->name('notifications.read');

    // ==========================================
    // MODULE: MUA SẮM (Tài sản / Thiết bị)
    // ==========================================
    Route::prefix('phieu-yeu-cau')->group(function () {
        Route::get('/', [MuaSamController::class, 'create'])->name('phieu.create');
        Route::post('/', [MuaSamController::class, 'store'])->name('phieu.store');
        Route::post('/{id}/bao-gia', [MuaSamController::class, 'capNhatBaoGia'])->name('phieu.bao_gia');
        Route::post('/{id}/nhan-hang', [MuaSamController::class, 'xacNhanNhanHang'])->name('phieu.nhan_hang');
    });

    // ==========================================
    // MODULE: NGHỈ PHÉP (E-Leave)
    // ==========================================
    Route::prefix('nghi-phep')->group(function () {
        Route::get('/tao-moi', [NghiPhepController::class, 'create'])->name('nghiphep.create');
        Route::post('/', [NghiPhepController::class, 'store'])->name('nghiphep.store');
    });

    // ==========================================
    // MODULE: TỔNG TRẠM ĐIỀU PHỐI (Xử lý chung)
    // ==========================================
    Route::prefix('phieu-yeu-cau')->group(function () {
        Route::get('/{id}', [PhieuYeuCauController::class, 'show'])->name('phieu.show');
        Route::post('/{id}/duyet', [PhieuYeuCauController::class, 'approve'])->name('phieu.duyet');
        Route::post('/{id}/huy', [PhieuYeuCauController::class, 'cancel'])->name('phieu.cancel');
        Route::get('/{id}/in', [PhieuYeuCauController::class, 'print'])->name('phieu.print');
    });

    // ==========================================
    // MODULE: THANH TOÁN (Kế toán)
    // ==========================================
    // VNPAY
    Route::post('/phieu-yeu-cau/{id}/vnpay', [VnpayController::class, 'createPayment'])->name('vnpay.create');
    Route::get('/vnpay-return', [VnpayController::class, 'vnpayReturn'])->name('vnpay.return');

    // Thủ công (VietQR)
    Route::get('/phieu-yeu-cau/{id}/thanh-toan', [ThanhToanController::class, 'showQR'])->name('thanhtoan.show');
    Route::post('/phieu-yeu-cau/{id}/xac-nhan-thanh-toan', [ThanhToanController::class, 'xacNhanThanhToan'])->name('thanhtoan.xacnhan');

    // ==========================================
    // MODULE: DANH MỤC & NHÂN SỰ (Admin)
    // ==========================================
    Route::resource('danhmuc', DanhMucController::class)->only(['index', 'store', 'update', 'destroy']);

    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::put('/users/{id}', [UserController::class, 'update'])->name('users.update');

});
