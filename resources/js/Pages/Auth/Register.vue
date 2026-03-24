<script setup>
import { useForm, Head, Link } from "@inertiajs/vue3";
import TextInput from "@/Components/TextInput.vue";

const form = useForm({
    name: null,
    email: null,
    password: null,
    password_confirmation: null,
    phong_ban_id: "",
    avatar: null,
    preview: null,
});

const change = (e) => {
    const file = e.target.files[0];

    // Kiểm tra nếu file lớn hơn 5MB (5 * 1024 * 1024 bytes)
    if (file.size > 5242880) {
        alert('Vui lòng chọn ảnh nhỏ hơn 5MB nhé!');
        e.target.value = ''; // Xóa file vừa chọn
        return;
    }

    form.avatar = file;
    form.preview = URL.createObjectURL(file);
};

const submit = () => {
    form.post(route("register.store"), {
        onError: () => form.reset("password", "password_confirmation"),
    });
};
</script>

<template>
    <Head title="Đăng ký tài khoản" />

    <div class="min-h-screen bg-gray-50 flex flex-col justify-center py-8 sm:px-6 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-xl text-center">
            <h1 class="text-3xl font-extrabold text-blue-700 tracking-tight">Procure<span class="text-gray-900">Flow</span></h1>
            <h2 class="mt-4 text-center text-xl font-bold text-gray-800">Tạo tài khoản mới</h2>
        </div>

        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-xl">
            <div class="bg-white py-8 px-6 shadow-xl sm:rounded-xl sm:px-10 border border-gray-100">
                <form @submit.prevent="submit">

                    <div class="flex flex-col items-center mb-6">
                        <div class="relative w-24 h-24 rounded-full overflow-hidden border-2 border-dashed border-gray-300 hover:border-blue-500 transition-colors group">
                            <img class="object-cover w-full h-full" :src="form.preview ?? '/storage/avatars/default.png'" alt="Avatar" />
                            <label for="avatar" class="absolute inset-0 bg-black bg-opacity-40 flex items-center justify-center opacity-0 group-hover:opacity-100 cursor-pointer transition-opacity">
                                <span class="text-white text-xs font-bold">Tải ảnh lên</span>
                            </label>
                            <input type="file" id="avatar" @input="change" accept="image/*" hidden />
                        </div>
                        <p class="text-red-500 text-xs mt-2 font-medium" v-if="form.errors.avatar">{{ form.errors.avatar }}</p>
                    </div>

                    <TextInput name="Họ và Tên" v-model="form.name" :message="form.errors.name" placeholder="Ví dụ: Nguyễn Văn A" />

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <TextInput name="Email công việc" type="email" v-model="form.email" :message="form.errors.email" placeholder="email@congty.com" />

                        <div class="mb-5">
                            <label class="block font-bold text-sm text-gray-700 mb-1.5">Phòng ban công tác</label>
                            <select v-model="form.phong_ban_id" class="w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm text-sm" :class="{'border-red-500 bg-red-50': form.errors.phong_ban_id}">
                                <option value="" disabled>-- Chọn phòng ban --</option>
                                <option value="1">Phòng Giám Đốc</option>
                                <option value="2">Phòng Kế Toán</option>
                                <option value="3">Phòng IT</option>
                                <option value="4">Phòng Nhân sự</option>
                            </select>
                            <p class="text-red-500 text-xs mt-1.5 font-medium" v-if="form.errors.phong_ban_id">{{ form.errors.phong_ban_id }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <TextInput name="Mật khẩu" type="password" v-model="form.password" :message="form.errors.password" placeholder="Tối thiểu 8 ký tự" />
                        <TextInput name="Xác nhận mật khẩu" type="password" v-model="form.password_confirmation" placeholder="Nhập lại mật khẩu" />
                    </div>

                    <div class="mt-6">
                        <button type="submit" :disabled="form.processing" class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-md shadow-sm text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                            {{ form.processing ? 'Đang tạo tài khoản...' : 'ĐĂNG KÝ' }}
                        </button>
                    </div>
                </form>

                <div class="mt-6 text-center text-sm text-gray-600 border-t pt-4">
                    Đã có tài khoản?
                    <Link :href="route('login')" class="font-bold text-blue-600 hover:text-blue-500">Đăng nhập ngay</Link>
                </div>
            </div>
        </div>
    </div>
</template>
