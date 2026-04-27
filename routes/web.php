<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PhieuYeuCauController;
use App\Http\Controllers\MuaSamController;
use App\Http\Controllers\NghiPhepController;
use App\Http\Controllers\VnpayController;
use App\Http\Controllers\ThanhToanController;
use App\Http\Controllers\DanhMucController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ManagerController;
use App\Http\Controllers\DirectorController;
use App\Http\Controllers\PurchasingController;
use App\Http\Controllers\AccountantController;
use App\Http\Controllers\HumanResourceController;
use App\Http\Controllers\BaiVietController;
use App\Http\Controllers\NhaCungCapController;
use App\Http\Controllers\PhongBanController;
use App\Models\BaiViet;
use App\Http\Controllers\ProfileController;
use Inertia\Inertia;

// CÔNG KHAI (Không cần đăng nhập)
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
    ]);
})->name('home');
Route::middleware('guest')->group(function () {
    // Route::inertia('/register', 'Auth/Register')->name('register');
    // Route::post('/register', [AuthController::class, 'register'])->name('register.store');

    Route::inertia('/login', 'Auth/Login')->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
});

//HỆ THỐNG (Bắt buộc đăng nhập)
Route::middleware('auth')->group(function () {

    // 1. Dashboard & Đăng xuất
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // API đánh dấu đã đọc thông báo
    Route::post('/notifications/{id}/read', [PhieuYeuCauController::class, 'markNotificationAsRead'])->name('notifications.read');

    // MODULE: MUA SẮM (Tài sản / Thiết bị)
    Route::prefix('phieu-yeu-cau')->group(function () {
        Route::get('/', [MuaSamController::class, 'create'])->name('phieu.create');
        Route::post('/', [MuaSamController::class, 'store'])->name('phieu.store');
        Route::post('/{id}/bao-gia', [MuaSamController::class, 'capNhatBaoGia'])->name('phieu.bao_gia');
        Route::post('/{id}/nhan-hang', [MuaSamController::class, 'xacNhanNhanHang'])->name('phieu.nhan_hang');
    });

    // MODULE: NGHỈ PHÉP (E-Leave)
    Route::prefix('nghi-phep')->group(function () {
        Route::get('/tao-moi', [NghiPhepController::class, 'create'])->name('nghiphep.create');
        Route::post('/', [NghiPhepController::class, 'store'])->name('nghiphep.store');
    });

    // MODULE: TỔNG TRẠM ĐIỀU PHỐI (Xử lý chung)
    Route::prefix('phieu-yeu-cau')->group(function () {
        Route::get('/{id}', [PhieuYeuCauController::class, 'show'])->name('phieu.show');
        Route::post('/{id}/duyet', [PhieuYeuCauController::class, 'approve'])->name('phieu.duyet');
        Route::post('/{id}/huy', [PhieuYeuCauController::class, 'cancel'])->name('phieu.cancel');
        Route::get('/{id}/in', [PhieuYeuCauController::class, 'print'])->name('phieu.print');
    });

    // MODULE: THANH TOÁN (Kế toán)
    // VNPAY
    Route::post('/phieu-yeu-cau/{id}/vnpay', [VnpayController::class, 'createPayment'])->name('vnpay.create');
    Route::get('/vnpay-return', [VnpayController::class, 'vnpayReturn'])->name('vnpay.return');

    // Thủ công (VietQR)
    Route::get('/phieu-yeu-cau/{id}/thanh-toan', [ThanhToanController::class, 'showQR'])->name('thanhtoan.show');
    Route::post('/phieu-yeu-cau/{id}/xac-nhan-thanh-toan', [ThanhToanController::class, 'xacNhanThanhToan'])->name('thanhtoan.xacnhan');

  // Admin
    Route::prefix('admin')->name('admin.')->group(function () {
        // Quản lý User
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::put('/users/{id}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');
        // Quản lý Danh mục
        Route::get('/danh-muc', [DanhMucController::class, 'index'])->name('danhmuc.index');
        Route::post('/danh-muc', [DanhMucController::class, 'store'])->name('danhmuc.store');
        Route::put('/danh-muc/{id}', [DanhMucController::class, 'update'])->name('danhmuc.update');
        Route::delete('/danh-muc/{id}', [DanhMucController::class, 'destroy'])->name('danhmuc.destroy');
       // Quản lý Nhà cung cấp
        Route::get('/nha-cung-cap', [NhaCungCapController::class, 'index'])->name('nhacungcap.index');
        Route::post('/nha-cung-cap', [NhaCungCapController::class, 'store'])->name('nhacungcap.store');
        Route::put('/nha-cung-cap/{id}', [NhaCungCapController::class, 'update'])->name('nhacungcap.update');
        Route::delete('/nha-cung-cap/{id}', [NhaCungCapController::class, 'destroy'])->name('nhacungcap.destroy');

        Route::get('/phong-ban', [PhongBanController::class, 'index'])->name('phongban.index');
        Route::post('/phong-ban', [PhongBanController::class, 'store'])->name('phongban.store');
        Route::put('/phong-ban/{id}', [PhongBanController::class, 'update'])->name('phongban.update');
        Route::delete('/phong-ban/{id}', [PhongBanController::class, 'destroy'])->name('phongban.destroy');
    });

    // Trưởng phòng
    Route::get('/manager/approvals', [ManagerController::class, 'index'])->name('manager.approvals');

    // Giám đốc
    Route::get('/director/approvals', [DirectorController::class, 'index'])->name('director.approvals');

    // Nhân sự
    Route::get('/hr/dashboard', [HumanResourceController::class, 'index'])->name('hr.index');

    // Mua sắm
    Route::get('/purchasing', [PurchasingController::class, 'index'])->name('purchasing.index');

    // Kế toán
    Route::get('/accountant', [AccountantController::class, 'index'])->name('accountant.index');

    // Admin

    // Blog (Bài viết)
    Route::get('/blog', [BaiVietController::class, 'index'])->name('blog.index');
    Route::get('/blog/create', [BaiVietController::class, 'create'])->name('blog.create');
    Route::post('/blog', [BaiVietController::class, 'store'])->name('blog.store');
    Route::get('/blog/{slug}', [BaiVietController::class, 'show'])->name('blog.show');

    Route::post('/blog/upload-image', [BaiVietController::class, 'uploadImage'])->name('blog.upload-image');

    // Profile & Đổi mật khẩu
    Route::get('/profile/change-password', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile/change-password', [ProfileController::class, 'updatePassword'])->name('profile.update');


});
