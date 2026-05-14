<?php

namespace App\Http\Controllers\Ai;
use App\Http\Controllers\Controller;
use App\Services\Ai\ProcureBotService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AiController extends Controller
{
    protected $botService;

    public function __construct(ProcureBotService $botService)
    {
        $this->botService = $botService;
    }

    public function chat(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000'
        ]);

        try {
            $reply = $this->botService->chat($request->user(), $request->message);

            return response()->json([
                'success' => true,
                'reply' => $reply
            ], 200);

        } catch (\Exception $e) {
            Log::error('Lỗi ProcureBot: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'reply' => 'Xin lỗi, hệ thống AI đang bận hoặc gặp sự cố mạng. Vui lòng thử lại sau giây lát!'
            ], 500);
        }
    }
}
