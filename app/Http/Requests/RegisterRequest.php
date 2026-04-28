<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Cho phép tất cả mọi người truy cập vào request này
    }

    // Định nghĩa các hàm đăng ký và đăng nhập
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8'],
            'avatar' => ['nullable', 'string', 'max:255'], // Avatar có thể để trống, nếu có thì phải là chuỗi và không quá 255 ký tự
            'phong_ban_id' => ['required', 'integer', 'exists:phong_ban,id'], // Kiểm tra phòng ban tồn tại trong bảng phong_ban
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Vui lòng nhập tên.',
            'name.string' => 'Tên phải là một chuỗi.',
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Định dạng email không hợp lệ.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
            'password.min' => 'Mật khẩu phải có ít nhất 8 ký tự.',
            'avatar.string' => 'Avatar phải là một chuỗi.',
            'avatar.max' => 'Avatar không được vượt quá 255 ký tự.',
            'phong_ban_id.required' => 'Vui lòng chọn phòng ban.',
            'phong_ban_id.integer' => 'Phòng ban phải là một số nguyên.',
            'phong_ban_id.exists' => 'Phòng ban không tồn tại.',
        ];
    }
}
