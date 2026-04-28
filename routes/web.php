<?php

use App\Http\Controllers\Admin\DanhMucController;
use App\Http\Controllers\Admin\NhaCungCapController;
// Core & Auth Controllers
use App\Http\Controllers\Admin\PhongBanController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AuthController;
// Domain: Nghiệp Vụ (Business Logic)
use App\Http\Controllers\BaiVietController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NghiepVu\MuaSamController;
use App\Http\Controllers\NghiepVu\NghiPhepController; // Lưu ý: đã chỉnh theo bước gom nhóm trước đó nếu có, nếu chưa bạn giữ App\Http\Controllers\BaiVietController
// Domain: Tài Chính (Finance)
use App\Http\Controllers\NghiepVu\PhieuYeuCauController;
use App\Http\Controllers\ProfileController;
// Domain: Admin (Master Data)
use App\Http\Controllers\TaiChinh\ThanhToanController;
use App\Http\Controllers\TaiChinh\VnpayController;
use App\Http\Controllers\Workspaces\GiamDocWorkspaceController;
use App\Http\Controllers\Workspaces\KeToanWorkspaceController;
// Domain: Workspaces (Role-based Dashboards)
use App\Http\Controllers\Workspaces\MuaSamWorkspaceController;
use App\Http\Controllers\Workspaces\NhanSuWorkspaceController;
use App\Http\Controllers\Workspaces\TruongPhongWorkspaceController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| PUBLIC ROUTES
|--------------------------------------------------------------------------
*/
Route::get('/', fn () => Inertia::render('Public/Welcome', ['canLogin' => Route::has('login')]))->name('home');

Route::middleware('guest')->group(function () {
    Route::inertia('/login', 'Auth/Login')->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
});

/*
|--------------------------------------------------------------------------
| AUTHENTICATED ROUTES
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    // --- Core & Profile ---
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::post('/notifications/{id}/read', [PhieuYeuCauController::class, 'markNotificationAsRead'])->name('notifications.read');

    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/change-password', [ProfileController::class, 'edit'])->name('edit');
        Route::put('/change-password', [ProfileController::class, 'updatePassword'])->name('update');
    });

    // --- MODULE: PHIẾU YÊU CẦU & MUA SẮM ---
    Route::prefix('phieu-yeu-cau')->name('phieu.')->group(function () {
        Route::get('/', [MuaSamController::class, 'create'])->name('create');
        Route::post('/', [MuaSamController::class, 'store'])->name('store');
        Route::post('/{id}/bao-gia', [MuaSamController::class, 'capNhatBaoGia'])->name('bao_gia');
        Route::post('/{id}/nhan-hang', [MuaSamController::class, 'xacNhanNhanHang'])->name('nhan_hang');

        Route::get('/{id}', [PhieuYeuCauController::class, 'show'])->name('show');
        Route::post('/{id}/duyet', [PhieuYeuCauController::class, 'approve'])->name('duyet');
        Route::post('/{id}/huy', [PhieuYeuCauController::class, 'cancel'])->name('cancel');
        Route::get('/{id}/in', [PhieuYeuCauController::class, 'print'])->name('print');
    });

    // --- MODULE: NGHỈ PHÉP ---
    Route::prefix('nghi-phep')->name('nghiphep.')->group(function () {
        Route::get('/tao-moi', [NghiPhepController::class, 'create'])->name('create');
        Route::post('/', [NghiPhepController::class, 'store'])->name('store');
    });

    // --- MODULE: TÀI CHÍNH / THANH TOÁN ---
    Route::prefix('phieu-yeu-cau/{id}')->group(function () {
        Route::post('/vnpay', [VnpayController::class, 'createPayment'])->name('vnpay.create');
        Route::get('/thanh-toan', [ThanhToanController::class, 'showQR'])->name('thanhtoan.show');
        Route::post('/xac-nhan-thanh-toan', [ThanhToanController::class, 'xacNhanThanhToan'])->name('thanhtoan.xacnhan');
    });
    Route::get('/vnpay-return', [VnpayController::class, 'vnpayReturn'])->name('vnpay.return');

    // --- MODULE: ADMIN (MASTER DATA) ---
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

        // Quản lý Phòng ban
        Route::get('/phong-ban', [PhongBanController::class, 'index'])->name('phongban.index');
        Route::post('/phong-ban', [PhongBanController::class, 'store'])->name('phongban.store');
        Route::put('/phong-ban/{id}', [PhongBanController::class, 'update'])->name('phongban.update');
        Route::delete('/phong-ban/{id}', [PhongBanController::class, 'destroy'])->name('phongban.destroy');
    });

    // --- MODULE: TRUYỀN THÔNG NỘI BỘ (BLOG) ---
    Route::prefix('blog')->name('blog.')->group(function () {
        Route::get('/', [BaiVietController::class, 'index'])->name('index');
        Route::get('/create', [BaiVietController::class, 'create'])->name('create');
        Route::post('/', [BaiVietController::class, 'store'])->name('store');
        Route::post('/upload-image', [BaiVietController::class, 'uploadImage'])->name('upload-image');
        Route::get('/{slug}', [BaiVietController::class, 'show'])->name('show');
    });

    // --- ROLE WORKSPACES (DASHBOARDS) ---
    Route::get('/manager/approvals', [TruongPhongWorkspaceController::class, 'index'])->name('manager.approvals');
    Route::get('/director/approvals', [GiamDocWorkspaceController::class, 'index'])->name('director.approvals');
    Route::get('/purchasing', [MuaSamWorkspaceController::class, 'index'])->name('purchasing.index');

    Route::get('/accountant', [KeToanWorkspaceController::class, 'index'])->name('accountant.index');
    Route::get('/accountant/export', [KeToanWorkspaceController::class, 'exportExcel'])->name('accountant.export');

    Route::get('/hr/dashboard', [NhanSuWorkspaceController::class, 'index'])->name('hr.index');
});
