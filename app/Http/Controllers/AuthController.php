<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use Inertia\Inertia;


class AuthController extends Controller
{

    public function register(RegisterRequest $request): RedirectResponse
    {

        $fields = $request->validated();

        if ($request->hasFile('avatar')) {
            $fields['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user = User::create($fields);
        Auth::login($user);

        // Tối ưu: Dùng to_route() ngắn gọn hơn redirect()->route()
        return to_route('dashboard')->with('success', 'Đăng ký tài khoản thành công!');
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->validated();

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            // Chống tấn công Session Fixation
            $request->session()->regenerate();

            // intended() giúp chuyển hướng về trang cũ nếu trước đó user bị văng ra
            return redirect()->intended(route('dashboard'))
                             ->with('success', 'Chào mừng bạn quay lại hệ thống!');
        }

        // UX: Trả về lỗi kèm theo email đã nhập để user không phải gõ lại
        return back()->withErrors([
            'password' => 'Email hoặc mật khẩu không chính xác.',
        ])->onlyInput('email');
    }

    // Đổi tên thành chữ thường: logout
    public function logout(Request $request): RedirectResponse
    {

        Auth::logout();
        // Xóa sạch session và token
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return to_route('login')->with('success', 'Bạn đã đăng xuất thành công.');
    }
}
