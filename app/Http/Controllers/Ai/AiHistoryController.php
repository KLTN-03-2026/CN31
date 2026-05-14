<?php

namespace App\Http\Controllers\Ai;
use App\Http\Controllers\Controller;
use App\Models\ChatHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AiHistoryController extends Controller
{
    /**
     * API: Lấy danh sách lịch sử hội thoại của User đang đăng nhập
     */
    public function index(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn chưa đăng nhập.'
            ], 401);
        }

        try {
            $history = ChatHistory::where('user_id', Auth::id())
                ->latest() // Sắp xếp mới nhất đưa lên đầu
                ->limit(20)
                ->get()
                ->reverse() // Đảo ngược mảng để render lên UI chuẩn (Cũ ở trên, mới ở dưới)
                ->values(); // Reset lại index của mảng sau khi reverse

            return response()->json([
                'success' => true,
                'history' => $history
            ], 200);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Lỗi lấy lịch sử AI: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Không thể tải lịch sử trò chuyện.'
            ], 500);
        }
    }
}
