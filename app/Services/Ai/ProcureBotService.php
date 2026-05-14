<?php

namespace App\Services\Ai;

use App\Models\ChatHistory;
use App\Services\Ai\BotStrategies\BotStrategyFactory;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ProcureBotService
{
    protected string $apiKey;
    protected string $apiUrl;

    public function __construct()
    {
        $this->apiKey = env('GROQ_API_KEY');
        $this->apiUrl = "https://api.groq.com/openai/v1/chat/completions";
    }

    public function chat($user, $userMessage)
    {
        $vaiTro = $user->vai_tro ? $user->vai_tro->label() : 'Người dùng nội bộ';

        $systemInstruction = "Bạn là ProcureBot, trợ lý ảo Enterprise của hệ thống Procureflow.
        Người đang chat với bạn là {$user->name}, vai trò: {$vaiTro}.
        QUY TẮC BẮT BUỘC:
        - Trả lời ngắn gọn, lịch sự bằng tiếng Việt. KHÔNG tự bịa số liệu.
        - Nếu [DỮ LIỆU HỆ THỐNG] cung cấp trường 'html_link_chi_tiet' hoặc 'html_link_pdf', BẮT BUỘC bạn phải in Y NGUYÊN chuỗi HTML đó vào câu trả lời của bạn. TUYỆT ĐỐI KHÔNG tự gõ lại thẻ liên kết.
        - Dựa vào lịch sử hội thoại trước đó để hiểu người dùng đang nói về phiếu nào (nếu dùng đại từ thay thế như 'nó', 'phiếu đó').";

        $strategy = BotStrategyFactory::make($user);
        $extraContext = $strategy->analyzeAndGetContext($user, $userMessage);

        if (empty($extraContext)) {
            $extraContext = "\n\n[DỮ LIỆU HỆ THỐNG]: Không có dữ liệu nội bộ nào được yêu cầu tra cứu. Hãy giao tiếp bình thường.";
        }

        $systemInstruction .= $extraContext;

        $history = ChatHistory::where('user_id', $user->id)
            ->latest()
            ->limit(4)
            ->get()
            ->reverse();

        $messages = [];
        $messages[] = ["role" => "system", "content" => $systemInstruction];

        foreach ($history as $msg) {
            $messages[] = ["role" => $msg->role, "content" => $msg->content];
        }

        $messages[] = ["role" => "user", "content" => $userMessage];

        $payload = [
            "model" => "llama-3.3-70b-versatile",
            "messages" => $messages,
            "temperature" => 0.5,
            "max_tokens" => 1024
        ];

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json'
        ])->timeout(60)->post($this->apiUrl, $payload);

        if ($response->failed()) {
            Log::error('Groq API Error: ' . $response->body());
            return "Lỗi kết nối đến máy chủ AI. Vui lòng xem file log.";
        }

        $botReply = $response->json('choices.0.message.content', "Lỗi phản hồi từ AI.");

        ChatHistory::create(['user_id' => $user->id, 'role' => 'user', 'content' => $userMessage]);
        ChatHistory::create(['user_id' => $user->id, 'role' => 'assistant', 'content' => $botReply]);

        return $botReply;
    }
}
