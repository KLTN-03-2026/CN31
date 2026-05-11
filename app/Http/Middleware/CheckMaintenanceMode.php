<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Setting;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class CheckMaintenanceMode
{
    public function handle(Request $request, Closure $next): Response
    {

        if ($request->is('login', 'logout')) {
            return $next($request);
        }

        $isMaintenance = Setting::get('thong_bao_bao_tri', 0);

        // 3. Nếu đang bật bảo trì
        if ($isMaintenance == 1 || $isMaintenance == 'true') {

            // 4. Nếu chưa đăng nhập HOẶC đã đăng nhập nhưng KHÔNG phải là Admin
            if (!Auth::check() || !Auth::user()->isAdmin()) {

                // Trả về trang thông báo bảo trì
                return Inertia::render('Public/Maintenance')->toResponse($request);
            }
        }

        // 5. Nếu không bảo trì, hoặc là Admin -> Cho đi tiếp
        return $next($request);
    }
}
