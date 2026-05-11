<?php

namespace App\Exports;

use App\Enums\TrangThaiPhieu;
use App\Models\PhieuYeuCau;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PhieuThanhToanExport implements FromQuery, ShouldAutoSize, WithColumnFormatting, WithHeadings, WithMapping
{
    public function __construct(
        public ?int $year = null
    ) {}

    public function query()
    {
        return PhieuYeuCau::query()
            ->with('nguoiTao')
            ->whereIn('trang_thai', [
                TrangThaiPhieu::DA_THANH_TOAN,
                TrangThaiPhieu::DA_HOAN_TAT,
            ])
            ->when(
                $this->year,
                fn ($query) => $query->whereYear('created_at', $this->year)
            )
            ->latest('created_at');
    }

    public function headings(): array
    {
        return [
            'Mã phiếu',
            'Tiêu đề',
            'Người tạo',
            'Tổng tiền',
            'Trạng thái',
            'Ngày tạo',
        ];
    }

    public function map($phieu): array
    {
        return [
            $phieu->ma_phieu,
            $phieu->tieu_de,
            $phieu->nguoiTao?->name ?? 'N/A',
            (float) $phieu->tong_tien,
            $phieu->trang_thai?->label() ?? '',
            $phieu->created_at?->format('d/m/Y H:i') ?? '',
        ];
    }

    public function columnFormats(): array
    {
        return [
            'D' => '#,##0',
        ];
    }
}
