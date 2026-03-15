<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Phiếu Yêu Cầu {{ $phieu->ma_phieu }}</title>
    <style>
        /* Font chữ DejaVu Sans hỗ trợ tiếng Việt trong DOMPDF */
        body { font-family: DejaVu Sans, sans-serif; font-size: 13px; line-height: 1.5; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #444; padding-bottom: 10px; }
        .header h1 { margin: 0; color: #2563eb; text-transform: uppercase; }
        .meta-info { margin-bottom: 20px; }
        .meta-info p { margin: 5px 0; }

        /* Bảng hàng hóa */
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th { background-color: #f3f4f6; border: 1px solid #9ca3af; padding: 10px; text-align: center; font-weight: bold; }
        td { border: 1px solid #9ca3af; padding: 8px; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .bold { font-weight: bold; }

        /* Phần chữ ký */
        .signatures { margin-top: 50px; display: table; width: 100%; }
        .sign-box { display: table-cell; text-align: center; width: 33%; vertical-align: top; }
        .sign-box h4 { margin-bottom: 5px; }
        .sign-box i { color: #555; font-size: 11px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>PHIẾU YÊU CẦU MUA SẮM</h1>
        <p>Mã phiếu: <strong>{{ $phieu->ma_phieu }}</strong></p>
    </div>

    <div class="meta-info">
        <p><strong>Người đề xuất:</strong> {{ $phieu->nguoiTao->name }}</p>
        <p><strong>Phòng ban:</strong> {{ $phieu->phongBan->ten_phong ?? 'Chưa cập nhật' }}</p>
        <p><strong>Ngày tạo:</strong> {{ $phieu->created_at->format('d/m/Y H:i') }}</p>
        <p><strong>Trạng thái hiện tại:</strong> {{ $phieu->trang_thai->label() }}</p>
        <p><strong>Lý do mua sắm:</strong> <em>{{ $phieu->ly_do }}</em></p>
    </div>

    <table>
        <thead>
            <tr>
                <th width="5%">STT</th>
                <th>Tên Sản Phẩm</th>
                <th width="10%">SL</th>
                <th width="15%">Đơn Giá</th>
                <th width="20%">Thành Tiền</th>
            </tr>
        </thead>
        <tbody>
            @foreach($phieu->chiTiet as $index => $item)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $item->ten_san_pham }}</td>
                <td class="text-center">{{ $item->so_luong }}</td>
                <td class="text-right">{{ number_format($item->don_gia, 0, ',', '.') }}</td>
                <td class="text-right bold">{{ number_format($item->thanh_tien, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" class="text-right bold" style="background-color: #f9fafb;">TỔNG CỘNG:</td>
                <td class="text-right bold" style="color: #dc2626; font-size: 14px;">{{ number_format($phieu->tong_tien, 0, ',', '.') }} VND</td>
            </tr>
        </tfoot>
    </table>

    <div class="signatures">
        <div class="sign-box">
            <h4>Người Lập Phiếu</h4>
            <i>(Ký và ghi rõ họ tên)</i>
            <br><br><br><br>
            <strong>{{ $phieu->nguoiTao->name }}</strong>
        </div>
        <div class="sign-box">
            <h4>Trưởng Phòng</h4>
            <i>(Duyệt và ký tên)</i>
        </div>
        <div class="sign-box">
            <h4>Giám Đốc / Kế Toán</h4>
            <i>(Xác nhận cuối cùng)</i>
        </div>
    </div>
</body>
</html>
