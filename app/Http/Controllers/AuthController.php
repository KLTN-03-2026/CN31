<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AuthController extends Controller
{
    // Đổi tên thành chữ thường: register
    public function register(Request $request): RedirectResponse
    {
        // 1. Validate chặt chẽ
        $fields = $request->validate([
            'avatar'        => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg,avif', 'max:5048'],
            'name'          => ['required', 'string', 'max:255'],
            'email'         => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password'      => ['required', 'confirmed', 'min:8'],
            'phong_ban_id'  => ['required', 'exists:phong_ban,id']
             ]);

        // 2. Upload Avatar (Tối ưu dùng $request->file thay vì $request->avatar)
        if ($request->hasFile('avatar')) {
            $fields['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        // 3. Tạo User (Mật khẩu tự động được mã hóa nhờ Model casts 'hashed')
        $user = User::create($fields);

        // 4. Đăng nhập ngay sau khi tạo
        Auth::login($user);

        // Tối ưu: Dùng to_route() ngắn gọn hơn redirect()->route()
        return to_route('dashboard')->with('success', 'Đăng ký tài khoản thành công!');
    }

    // Đổi tên thành chữ thường: login
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required']
        ]);

        // Tối ưu: Ép kiểu boolean cho nút Remember Me an toàn hơn
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
        // Chỉ cần 1 dòng này là đủ cho guard mặc định
        Auth::logout();

        // Xóa sạch session và token
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return to_route('login')->with('success', 'Bạn đã đăng xuất an toàn.');
    }
}
