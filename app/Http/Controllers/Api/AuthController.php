<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LoginRequest;
use App\Models\User;
use Illuminate\Http\Request; 
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(LoginRequest $request)
    {
        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Email hoặc mật khẩu không chính xác.',
            ], 401);
        }

        // 4. Thu hồi Token cũ của thiết bị này (Tính tiết kiệm bộ nhớ DB)
        $user->tokens()->where('name', $request->device_name)->delete();

        // 5. Cấp Token mới
        $token = $user->createToken($request->device_name)->plainTextToken;

        // 6. Trả kết quả
        return response()->json([
            'success' => true,
            'message' => 'Đăng nhập thành công',
            'data' => [
                'user' => $user,
                'token' => $token,
            ],
        ], 200);
    }

    /**
     * Đăng xuất khỏi thiết bị hiện tại (Standard Logout)
     */
    public function logout(Request $request)
    {
        // currentAccessToken() chỉ định vị đúng Token đang được gửi lên trong Header
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Đăng xuất thiết bị thành công'
        ], 200);
    }

    /**
     * Dành cho tính năng: "Bảo mật tài khoản -> Đăng xuất khỏi tất cả các thiết bị"
     */
    public function logoutAllDevices(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Đã đăng xuất khỏi tất cả các thiết bị an toàn'
        ], 200);
    }
}
