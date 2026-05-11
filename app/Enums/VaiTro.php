<?php

namespace App\Enums;

enum VaiTro: string
{
    case ADMIN = 'admin';
    case NHAN_VIEN = 'nhan_vien';
    case TRUONG_PHONG = 'truong_phong';
    case NHAN_VIEN_MUA_SAM = 'nhan_vien_mua_sam';
    case GIAM_DOC = 'giam_doc';
    case KE_TOAN = 'ke_toan';
    case NHAN_SU = 'nhan_su';

    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Quản trị viên',
            self::NHAN_VIEN => 'Nhân viên',
            self::TRUONG_PHONG => 'Trưởng phòng',
            self::NHAN_VIEN_MUA_SAM => 'Nhân viên Mua sắm',
            self::GIAM_DOC => 'Giám đốc',
            self::KE_TOAN => 'Kế toán',
            self::NHAN_SU => 'Nhân sự (HR)',

        };
    }

    public function color(): string
    {
        return match ($this) {
            self::ADMIN => 'purple',
            self::NHAN_VIEN => 'gray',
            self::TRUONG_PHONG => 'blue',
            self::NHAN_VIEN_MUA_SAM => 'orange',
            self::GIAM_DOC => 'red',
            self::KE_TOAN => 'green',
            self::NHAN_SU => 'pink',
        };
    }

    public static function options(): array
    {
        return array_map(fn ($case) => ['value' => $case->value, 'label' => $case->label(), 'color' => $case->color()], self::cases());
    }
}
