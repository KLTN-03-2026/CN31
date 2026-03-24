<!DOCTYPE html>
<html lang="vi">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Phiếu Yêu Cầu Mua Sắm {{ $phieu->ma_phieu }}</title>
    <style>
        /* BẮT BUỘC dùng DejaVu Sans để không bị lỗi font Tiếng Việt */
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 13px; line-height: 1.5; color: #333; }
        .header { width: 100%; margin-bottom: 30px; }
        .company-info { float: left; width: 40%; text-align: center; font-weight: bold; }
        .quoc-hieu { float: right; width: 60%; text-align: center; font-weight: bold; }
        .clear { clear: both; }
        .line { border-top: 1px solid #000; width: 150px; margin: 5px auto; }

        h1 { text-align: center; font-size: 20px; text-transform: uppercase; margin-top: 20px; margin-bottom: 20px; }

        .info-section { margin-bottom: 20px; }
        .info-section p { margin: 5px 0; }

        table { width: 100%; border-collapse: collapse; margin-top: 10px; margin-bottom: 30px; }
        th, td { border: 1px solid #000; padding: 8px; }
        th { background-color: #f2f2f2; text-align: center; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-bold { font-weight: bold; }

        .footer-signatures { width: 100%; margin-top: 30px; }
        .signature-col { float: left; width: 25%; text-align: center; font-weight: bold; }
        .signature-col small { font-weight: normal; font-style: italic; color: #555; }
        .signature-space { height: 80px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-info">
            CÔNG TY PROCUREFLOW<br>
            Số: {{ $phieu->ma_phieu }}
            <div class="line"></div>
        </div>
        <div class="quoc-hieu">
            CỘNG HÒA XÃ HỘI CHỦ NGHĨA VIỆT NAM<br>
            Độc lập - Tự do - Hạnh phúc
            <div class="line"></div>
        </div>
        <div class="clear"></div>
    </div>

    <h1>TỜ TRÌNH YÊU CẦU MUA SẮM</h1>

    <div class="info-section">
        <p><span class="text-bold">1. Kính gửi:</span> Ban Giám Đốc, Phòng Kế Toán</p>
        <p><span class="text-bold">2. Người đề nghị:</span> {{ $phieu->nguoiTao->name }}</p>
        <p><span class="text-bold">3. Phòng ban:</span> {{ $phieu->phongBan->ten_phong_ban ?? 'Chưa cập nhật' }}</p>
        <p><span class="text-bold">4. Tiêu đề:</span> {{ $phieu->tieu_de }}</p>
        <p><span class="text-bold">5. Lý do mua sắm:</span> {{ $phieu->ly_do ?: 'Không có ghi chú' }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th width="5%">STT</th>
                <th width="40%">Tên hàng hóa / Dịch vụ</th>
                <th width="10%">SL</th>
                <th width="20%">Đơn giá (VNĐ)</th>
                <th width="25%">Thành tiền (VNĐ)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($phieu->chiTiet as $index => $item)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $item->ten_san_pham }}</td>
                <td class="text-center">{{ $item->so_luong }}</td>
                <td class="text-right">{{ number_format($item->don_gia, 0, ',', '.') }}</td>
                <td class="text-right text-bold">{{ number_format($item->thanh_tien, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" class="text-right text-bold uppercase">Tổng thanh toán:</td>
                <td class="text-right text-bold" style="font-size: 15px; color: #d9534f;">
                    {{ number_format($phieu->tong_tien, 0, ',', '.') }}
                </td>
            </tr>
        </tfoot>
    </table>

    <div class="footer-signatures">
        <div class="signature-col">
            Người lập phiếu<br>
            <small>(Ký, ghi rõ họ tên)</small>
            <div class="signature-space"></div>
            {{ $phieu->nguoiTao->name }}
        </div>
        <div class="signature-col">
            Trưởng phòng<br>
            <small>(Ký, ghi rõ họ tên)</small>
        </div>
        <div class="signature-col">
            Kế toán<br>
            <small>(Ký, ghi rõ họ tên)</small>
        </div>
        <div class="signature-col">
            Giám đốc<br>
            <small>(Ký, đóng dấu)</small>
        </div>
        <div class="clear"></div>
    </div>
</body>
</html>
