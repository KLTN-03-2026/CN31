<?php

namespace App\Notifications;

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

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
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
