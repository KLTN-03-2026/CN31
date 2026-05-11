<?php

namespace App\Mail;

use App\Models\PhieuYeuCau;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ThongBaoPhieuMail extends Mailable
{
    use Queueable, SerializesModels;

    public $phieu;

    public $tieuDe;

    public $loiNhan;

    // Nhận dữ liệu truyền vào khi gọi gửi Email
    public function __construct(PhieuYeuCau $phieu, $tieuDe, $loiNhan)
    {
        $this->phieu = $phieu;
        $this->tieuDe = $tieuDe;
        $this->loiNhan = $loiNhan;
    }

    // Xây dựng nội dung thư
    public function build()
    {
        return $this->subject($this->tieuDe)
            ->view('emails.thong_bao_phieu'); // Chỏ tới file giao diện Blade
    }
}
