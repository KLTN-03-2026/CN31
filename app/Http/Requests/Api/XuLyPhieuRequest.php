<?php

namespace App\Http\Requests\Api;

use App\Enums\HanhDong;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rules\Enum;

class XuLyPhieuRequest extends FormRequest
{
    /**
     * CHỨC NĂNG 1: XÁC THỰC THẨM QUYỀN
     */
    public function authorize(): bool
    {
        $user = $this->user();

        return $user->isTruongPhong() || $user->isGiamDoc();
    }

    protected function failedAuthorization()
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Bạn không có thẩm quyền thực hiện hành động này.',
        ], 403));
    }

    /**
     * CHỨC NĂNG 2: KIỂM TRA DỮ LIỆU ĐẦU VÀO
     */
    public function rules(): array
    {
        return [
            // 1. Ép buộc dữ liệu gửi lên PHẢI NẰM TRONG file HanhDong Enum
            'hanh_dong' => ['required', new Enum(HanhDong::class)],

            // 2. Chỗ này cũng dùng Enum luôn cho xịn, không gõ raw string 'tu_choi' nữa
            'ly_do' => ['required_if:hanh_dong,'.HanhDong::TU_CHOI->value, 'nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'hanh_dong.required' => 'Vui lòng cung cấp hành động xử lý.',
            'hanh_dong.in' => 'Hành động không hợp lệ.',
            'ly_do.required_if' => 'Vui lòng nhập lý do khi từ chối phiếu.',
        ];
    }

    // Tùy chỉnh lỗi Validation trả về JSON cho Mobile
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => $validator->errors()->first(),
        ], 422));
    }
}
