<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f7f6; color: #333; padding: 20px; }
        .container { background-color: #ffffff; padding: 30px; border-radius: 8px; max-width: 600px; margin: 0 auto; border-top: 4px solid #2563eb; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        h2 { color: #1e3a8a; }
        .info-box { background-color: #f8fafc; padding: 15px; border-left: 4px solid #3b82f6; margin-bottom: 20px; }
        .btn { display: inline-block; padding: 10px 20px; background-color: #2563eb; color: #ffffff !important; text-decoration: none; border-radius: 5px; font-weight: bold; }
        .footer { margin-top: 30px; font-size: 12px; color: #64748b; text-align: center; border-top: 1px solid #e2e8f0; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Xin chào {{ $phieu->nguoiTao->name }},</h2>

        <p>Hệ thống ProcureFlow xin thông báo: <strong>{{ $loiNhan }}</strong></p>

        <div class="info-box">
            <p><strong>Mã phiếu:</strong> {{ $phieu->ma_phieu }}</p>
            <p><strong>Tiêu đề:</strong> {{ $phieu->tieu_de }}</p>
            <p><strong>Trạng thái hiện tại:</strong> {{ $phieu->trang_thai->label() }}</p>
            <p><strong>Tổng tiền:</strong> {{ number_format($phieu->tong_tien, 0, ',', '.') }} VNĐ</p>
        </div>

        <p>Bạn có thể nhấn vào nút bên dưới để xem chi tiết trên hệ thống:</p>

        <p style="text-align: center; margin: 30px 0;">
            <a href="{{ route('phieu.show', $phieu->id) }}" class="btn">Xem chi tiết phiếu</a>
        </p>

        <div class="footer">
            Đây là email tự động từ hệ thống ProcureFlow. Vui lòng không trả lời email này.<br>
            &copy; {{ date('Y') }} ProcureFlow Inc.
        </div>
    </div>
</body>
</html>
