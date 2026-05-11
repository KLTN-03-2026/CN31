<?php

namespace App\Notifications;

use App\Mail\ThongBaoPhieuMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class PhieuYeuCauNotification extends Notification implements ShouldBroadcast, ShouldQueue
{
    use Queueable;

    public $phieu;

    public $thongDiep;

    public $loai;

    public function __construct($phieu, $thongDiep, $loai = 'info')
    {
        $this->phieu = $phieu;
        $this->thongDiep = $thongDiep;
        $this->loai = $loai;
    }

    // 1. Khai báo thêm 'mail' vào kênh gửi thông báo
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast', 'mail'];
    }

    // 2. hàm toMail để khởi tạo bức thư và gửi đi
    public function toMail(object $notifiable)
    {
        $tieuDe = 'Thông báo từ hệ thống: '.$this->phieu->ma_phieu;

        return (new ThongBaoPhieuMail($this->phieu, $tieuDe, $this->thongDiep))
            ->to($notifiable->email);
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'phieu_id' => $this->phieu->id,
            'ma_phieu' => $this->phieu->ma_phieu,
            'thong_diep' => $this->thongDiep,
            'loai' => $this->loai,
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'phieu_id' => $this->phieu->id,
            'ma_phieu' => $this->phieu->ma_phieu,
            'thong_diep' => $this->thongDiep,
            'loai' => $this->loai,
            'created_at' => now()->toIso8601String(),
            'created_at_label' => 'Vừa xong',
        ]);
    }
}
