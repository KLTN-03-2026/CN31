<?php

namespace App\Http\Controllers\Ai;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Smalot\PdfParser\Parser;
use App\Models\NhaCungCap;

class AiOcrController extends Controller
{
    public function extractBaoGia(Request $request)
    {
        // 1. Validate file đầu vào
        $request->validate([
            'file_bao_gia' => 'required|file|mimes:pdf,jpeg,png,jpg|max:5120', // Tối đa 5MB
        ]);

        $file = $request->file('file_bao_gia');
        $extension = strtolower($file->getClientOriginalExtension());
        $apiKey = env('GROQ_API_KEY');

        // Lấy danh sách Nhà cung cấp để AI tự map ID
        $danhSachNcc = NhaCungCap::select('id', 'ten_nha_cung_cap')->get()->toArray();
        $nccJson = json_encode($danhSachNcc, JSON_UNESCAPED_UNICODE);

        // 2. Xây dựng Prompt (Lệnh) cực kỳ khắt khe cho AI
        $systemPrompt = "Bạn là một hệ thống trích xuất dữ liệu hóa đơn/báo giá (OCR).
        Dưới đây là danh sách các nhà cung cấp hợp lệ trong hệ thống: {$nccJson}.
        Nhiệm vụ của bạn:
        1. Tìm Tên nhà cung cấp trong báo giá và đối chiếu với danh sách trên. Trả về 'nha_cung_cap_id' của nhà cung cấp khớp nhất (nếu không thấy, trả về null).
        2. Tìm danh sách sản phẩm và đơn giá (chỉ lấy số, ví dụ 45000000).
        BẮT BUỘC trả về ĐÚNG MỘT đối tượng JSON, KHÔNG có markdown ```json, KHÔNG có text giải thích.
        Định dạng chuẩn:
        {
            \"nha_cung_cap_id\": 1,
            \"san_pham\": [
                {\"ten_san_pham\": \"Laptop Dell\", \"don_gia\": 15000000\"thanh_tien\": 15000000},
            ]
        }";

        $messages = [];

        try {
            // LUỒNG 1: XỬ LÝ FILE PDF
            if ($extension === 'pdf') {
                $parser = new Parser();
                $pdf = $parser->parseFile($file->getPathname());
                $textContent = $pdf->getText();

                $textContent = mb_convert_encoding($textContent, 'UTF-8', 'UTF-8');

                // Dọn dẹp dấu khoảng trắng thừa
                $textContent = preg_replace('/\s+/', ' ', $textContent);

                $messages = [
                    ["role" => "system", "content" => $systemPrompt],
                    ["role" => "user", "content" => "Trích xuất dữ liệu từ nội dung báo giá sau:\n\n" . $textContent]
                ];

                $model = "llama-3.3-70b-versatile";
            }
            // LUỒNG 2: XỬ LÝ FILE ẢNH (VISION)
            else {
                // Mã hóa ảnh sang Base64
                $imageData = base64_encode(file_get_contents($file->getPathname()));
                $mimeType = $file->getMimeType();
                $base64Image = "data:{$mimeType};base64,{$imageData}";

                $messages = [
                    ["role" => "system", "content" => $systemPrompt],
                    [
                        "role" => "user",
                        "content" => [
                            ["type" => "text", "text" => "Hãy trích xuất thông tin báo giá từ hình ảnh này."],
                            ["type" => "image_url", "image_url" => ["url" => $base64Image]]
                        ]
                    ]
                ];

                // Dùng model Vision chuyên phân tích hình ảnh
                $model = "llama-3.2-11b-vision-preview";
            }

            // 3. GỌI API LÊN GROQ
            $apiUrl = 'https://api.groq.com/openai/v1/chat/completions';
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json'
            ])->timeout(60)->post($apiUrl, [
                "model" => $model,
                "messages" => $messages,
                "temperature" => 0.1, // Nhiệt độ thấp để AI trả về JSON chính xác, không sáng tạo
                "response_format" => ["type" => "json_object"] // Ép AI nhả JSON chuẩn
            ]);

            if ($response->failed()) {
                throw new \Exception("Lỗi kết nối Groq: " . $response->body());
            }

            // 4. LẤY JSON VÀ DỌN DẸP
            $botReply = $response->json('choices.0.message.content');

            // Xóa các chuỗi markdown rác nếu AI lỡ tay sinh ra
            $botReply = str_replace(['```json', '```'], '', $botReply);
            $parsedData = json_decode(trim($botReply), true);

            if (!$parsedData) {
                throw new \Exception("Dữ liệu AI trả về không phải là JSON hợp lệ.");
            }

            return response()->json([
                'success' => true,
                'data' => $parsedData
            ]);

        } catch (\Exception $e) {
            Log::error('Lỗi OCR Báo giá: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Không thể trích xuất dữ liệu lúc này.'
            ], 500);
        }
    }
}
