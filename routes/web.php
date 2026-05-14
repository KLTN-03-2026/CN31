<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Middleware & Enums
use App\Http\Middleware\CheckRole;
use App\Enums\VaiTro;

// Core & Auth
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Ai\AiController;
use App\Http\Controllers\Ai\AiHistoryController;
use App\Http\Controllers\Ai\AiOcrController;

// Business (Nghiệp vụ)
use App\Http\Controllers\NghiepVu\MuaSamController;
use App\Http\Controllers\NghiepVu\PhieuYeuCauController;
use App\Http\Controllers\NghiepVu\NghiPhepController;
use App\Http\Controllers\BaiVietController;

// Finance (Tài chính)
use App\Http\Controllers\TaiChinh\ThanhToanController;
use App\Http\Controllers\TaiChinh\VnpayController;

// Admin (Master Data)
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\DanhMucController;
use App\Http\Controllers\Admin\NhaCungCapController;
use App\Http\Controllers\Admin\PhongBanController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\PhieuAdminController;

// Workspaces (Dashboards)
use App\Http\Controllers\Workspaces\TruongPhongWorkspaceController;
use App\Http\Controllers\Workspaces\GiamDocWorkspaceController;
use App\Http\Controllers\Workspaces\MuaSamWorkspaceController;
use App\Http\Controllers\Workspaces\KeToanWorkspaceController;
use App\Http\Controllers\Workspaces\NhanSuWorkspaceController;

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

    // --- COMMON & PROFILE ---
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::post('/notifications/{id}/read', [PhieuYeuCauController::class, 'markNotificationAsRead'])->name('notifications.read');

    // Route cho ProcureBot AI (Giao tiếp AJAX từ Vue)
    Route::post('/ai/chat', [AiController::class, 'chat'])->name('ai.chat');
    Route::get('/ai/history', [AiHistoryController::class, 'index'])->name('ai.history');
    Route::post('/api/ai/ocr-bao-gia', [AiOcrController::class, 'extractBaoGia'])->name('ai.ocr_bao_gia');

    Route::controller(ProfileController::class)->prefix('profile')->name('profile.')->group(function () {
        Route::get('/change-password', 'edit')->name('edit');
        Route::put('/change-password', 'updatePassword')->name('update');
    });

    // --- MODULE: MUA SẮM ---
    Route::prefix('phieu-yeu-cau')->name('phieu.')->group(function () {
        Route::controller(MuaSamController::class)->group(function () {
            Route::get('/', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::post('/{id}/bao-gia', 'capNhatBaoGia')->name('bao_gia');
            Route::post('/{id}/nhan-hang', 'xacNhanNhanHang')->name('nhan_hang');
        });

        Route::controller(PhieuYeuCauController::class)->group(function () {
            Route::get('/{id}', 'show')->name('show');
            Route::post('/{id}/duyet', 'approve')->name('duyet');
            Route::post('/{id}/huy', 'cancel')->name('cancel');
            Route::get('/{id}/in', 'print')->name('print');
        });
    });

    // --- MODULE: NGHỈ PHÉP ---
    Route::controller(NghiPhepController::class)->prefix('nghi-phep')->name('nghiphep.')->group(function () {
        Route::get('/tao-moi', 'create')->name('create');
        Route::post('/', 'store')->name('store');
    });

    // --- MODULE: TÀI CHÍNH ---
    Route::prefix('phieu-yeu-cau/{id}')->group(function () {
        Route::post('/vnpay', [VnpayController::class, 'createPayment'])->name('vnpay.create');
        Route::get('/thanh-toan', [ThanhToanController::class, 'showQR'])->name('thanhtoan.show');
        Route::post('/xac-nhan-thanh-toan', [ThanhToanController::class, 'xacNhanThanhToan'])->name('thanhtoan.xacnhan');
    });
    Route::get('/vnpay-return', [VnpayController::class, 'vnpayReturn'])->name('vnpay.return');

    // --- MODULE: BLOG ---
   Route::middleware(['auth'])->group(function () {
    Route::get('/bang-tin', [BaiVietController::class, 'index'])->name('blog.index');
    Route::get('/bang-tin/{slug}', [BaiVietController::class, 'show'])->name('blog.show');
});



    Route::middleware(['auth'])->prefix('admin/bang-tin')->name('admin.blog.')->group(function () {
    Route::controller(BaiVietController::class)->group(function () {

        Route::get('/manage', 'manage')->name('manage');
        Route::get('/create', 'create')->name('create');

        Route::post('/store', 'store')->name('store');
        Route::post('/upload-image', 'uploadImage')->name('uploadImage');
        Route::delete('/{id}', 'destroy')->name('destroy');
        Route::post('/{id}/restore', 'restore')->name('restore');

    });
});

    /*
    |--------------------------------------------------------------------------
    | PROTECTED WORKSPACES (RBAC)
    |--------------------------------------------------------------------------
    */

    // --- 1. ADMIN SYSTEM ---
    Route::middleware(CheckRole::class . ':' . VaiTro::ADMIN->value)->prefix('admin')->name('admin.')->group(function () {

        Route::controller(UserController::class)->prefix('users')->name('users.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/', 'store')->name('store');
            Route::put('/{id}', 'update')->name('update');
            Route::delete('/{id}', 'destroy')->name('destroy');
        });

        Route::controller(DanhMucController::class)->prefix('danh-muc')->name('danhmuc.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/', 'store')->name('store');
            Route::put('/{id}', 'update')->name('update');
            Route::delete('/{id}', 'destroy')->name('destroy');
        });

        Route::controller(NhaCungCapController::class)->prefix('nha-cung-cap')->name('nhacungcap.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/', 'store')->name('store');
            Route::put('/{id}', 'update')->name('update');
            Route::delete('/{id}', 'destroy')->name('destroy');
        });

        Route::controller(PhongBanController::class)->prefix('phong-ban')->name('phongban.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/', 'store')->name('store');
            Route::put('/{id}', 'update')->name('update');
            Route::delete('/{id}', 'destroy')->name('destroy');
        });

        Route::controller(PhieuAdminController::class)->prefix('phieu-yeu-cau')->name('phieu_yeu_cau.')->group(function () {
            Route::get('/', 'index')->name('index');
        });


        Route::controller(SettingController::class)->prefix('settings')->name('settings.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/update', 'update')->name('update');

        Route::controller(PhieuAdminController::class)->prefix('phieu-yeu-cau')->name('phieu_yeu_cau.')->group(function () {
            Route::get('/', 'index')->name('index');
        });

    });
    });

    // --- 2. DEPARTMENT MANAGERS ---
    Route::middleware(CheckRole::class . ':' . VaiTro::TRUONG_PHONG->value)
        ->get('/manager/approvals', [TruongPhongWorkspaceController::class, 'index'])
        ->name('manager.approvals');

    Route::middleware(CheckRole::class . ':' . VaiTro::GIAM_DOC->value)
        ->get('/director/approvals', [GiamDocWorkspaceController::class, 'index'])
        ->name('director.approvals');

    // --- 3. SPECIALIZED STAFF ---
    Route::middleware(CheckRole::class . ':' . VaiTro::NHAN_VIEN_MUA_SAM->value)
        ->get('/purchasing', [MuaSamWorkspaceController::class, 'index'])
        ->name('purchasing.index');

    Route::middleware(CheckRole::class . ':' . VaiTro::KE_TOAN->value)->prefix('accountant')->name('accountant.')->group(function () {
        Route::get('/', [KeToanWorkspaceController::class, 'index'])->name('index');
        Route::get('/export', [KeToanWorkspaceController::class, 'exportExcel'])->name('export');
    });

    Route::middleware(CheckRole::class . ':' . VaiTro::NHAN_SU->value)
        ->get('/hr/dashboard', [NhanSuWorkspaceController::class, 'index'])
        ->name('hr.index');
});
