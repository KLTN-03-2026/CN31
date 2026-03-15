<?php

namespace App\Http\Controllers;

use App\Models\User;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\HttpCache\Store;

class AuthController extends Controller
{
    public function Register(Request $request)
    {
        // 1. Validate
        $fields = $request->validate([
            'avatar'   => ['file', 'nullable', 'max:30000'],
            'name'     => ['required', 'max:255'],
            'email'    => ['required', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', 'min:3'],
        ]);

        // 2. Upload & TRÁO ĐỔI dữ liệu
        if ($request->hasFile('avatar')) {
            //  Dòng này sửa lỗi lưu đường dẫn tạm C:\tmp của bạn lúc nãy
            $fields['avatar'] = Storage::disk('public')->put('avatars', $request->avatar);
        }

        // 3. Tạo User
        $user = User::create($fields); // Lúc này $fields['avatar'] đã là đường dẫn chuẩn

        // 4. Login
        Auth::login($user);
        return redirect()->route('dashboard');
    }

    public function Login(Request $request): RedirectResponse
    {
        $fields = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required']
        ]);
        $user = Auth::attempt($fields, $request->remember);

        if ($user) {
            $request->session()->regenerate();
            return redirect()->intended('dashboard');
            // return redirect()->route('home');
        }
        return back()->withErrors([
            'password' => 'email hoặc mật khẩu không chinh xac',
        ])->onlyInput();
    }

    // Logout
    public function Logout(Request $request)
    {
        Auth::guard('web')->logout();
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        // chuyển trang
        return redirect()->route('login');
    }


}
