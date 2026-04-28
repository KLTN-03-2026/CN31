<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // public function register(RegisterRequest $request): RedirectResponse
    // {

    //     $fields = $request->validated();

    //     if ($request->hasFile('avatar')) {
    //         $fields['avatar'] = $request->file('avatar')->store('avatars', 'public');
    //     }

    //     $user = User::create($fields);
    //     Auth::login($user);

    //     // Tối ưu: Dùng to_route() ngắn gọn hơn redirect()->route()
    //     return to_route('dashboard')->with('success', 'Đăng ký tài khoản thành công!');
    // }

    public function login(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->validated();

        if (Auth::attempt($credentials, $request->boolean('remember'))) {

            if (Auth::user()->trang_thai == false) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withErrors([
                    'email' => 'Tài khoản của bạn đã bị vô hiệu hóa (Đã nghỉ việc). Vui lòng liên hệ Admin.',
                ])->onlyInput('email');
            }

            $request->session()->regenerate();

            return redirect()->intended(route('dashboard'))
                ->with('success', 'Chào mừng bạn quay lại hệ thống!');
        }

        return back()->withErrors([
            'password' => 'Email hoặc mật khẩu không chính xác.',
        ])->onlyInput('email');
    }

    public function logout(Request $request): RedirectResponse
    {

        Auth::logout();
        // Xóa sạch session và token
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return to_route('login')->with('success', 'Bạn đã đăng xuất thành công.');
    }
}
