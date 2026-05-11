<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string ...$roles (Sử dụng tham số variadic để nhận nhiều role cùng lúc)
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        // 1. Kiểm tra nếu chưa đăng nhập (bảo vệ kép)
        if (!$user) {
            return redirect()->route('login');
        }

        // 2. Lấy giá trị chuỗi của Enum
        $userRoleValue = $user->vai_tro->value;

        // 3. Kiểm tra xem Role của user có nằm trong danh sách được phép truy cập không
        if (!in_array($userRoleValue, $roles)) {
            abort(403, 'Bạn không có quyền truy cập vào không gian này.');
        }

        // Nếu hợp lệ, cho phép đi tiếp vào Controller
        return $next($request);
    }
}
