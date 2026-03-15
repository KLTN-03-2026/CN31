<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePhieuYeuCauRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tieu_de'                => ['required', 'string', 'max:255'],
            'ly_do'                  => ['required', 'string'],
            'san_pham'               => ['required', 'array', 'min:1'],
            'san_pham.*ten_san_pham' => ['required', 'string'],
            'san_pham.*so_luong'     => ['required', 'integer', 'min:1'],
            'san_pham.*don_gia'      => ['required', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'tieu_de.required' => 'Vui lòng nhập tiêu đề cho phiếu yêu cầu.',
            'san_pham.required' => 'Bạn phải thêm ít nhất một món hàng.',
            'san_pham.*.ten_san_pham.required' => 'Tên sản phẩm không được để trống.',
            'san_pham.*.so_luong.min' => 'Số lượng phải lớn hơn 0.',
        ];
    }
}
