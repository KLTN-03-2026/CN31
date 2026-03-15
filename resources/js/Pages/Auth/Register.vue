<script setup>
import { useForm } from "@inertiajs/vue3";
import TextInput from "@/Components/TextInput.vue";

// Khai báo form
const form = useForm({
    name: null,
    email: null,
    password: null,
    password_confirmation: null,
    avatar: null,  // Biến chứa file để gửi lên server
    preview: null, // Biến chứa đường dẫn ảnh ảo để xem trước
});

// Hàm xử lý khi chọn file
const change = (e) => {
    // Lấy file gán vào form
    form.avatar = e.target.files[0];
    // Tạo link ảnh xem trước
    form.preview = URL.createObjectURL(e.target.files[0]);
};

const submit = () => {
    form.post(route("register.store"), {
        onError: () => form.reset("password", "password_confirmation"),
    });
};
</script>

<template>
    <Head title="Register" />

    <h1 class="title">Đăng ký tài khoản</h1>

    <div class="w-2/4 mx-auto">
        <form @submit.prevent="submit">

            <div class="grid place-items-center mb-4">
                <div class="relative w-28 h-28 rounded-full overflow-hidden border border-slate-300">
                    <label for="avatar" class="absolute inset-0 grid content-end cursor-pointer">
                        <span class="bg-white/70 pb-2 text-center">Avatar</span>
                    </label>

                    <input type="file" id="avatar" @input="change" hidden />

                    <img
                        class="object-cover w-full h-full"
                        :src="form.preview ?? 'storage/avatars/default.png'"
                        alt="Avatar Preview"
                    />
                </div>
                <p class="error mt-2">{{ form.errors.avatar }}</p>
            </div>
            <TextInput name="Họ tên" v-model="form.name" :message="form.errors.name" />
            <TextInput name="Email" type="email" v-model="form.email" :message="form.errors.email" />
            <TextInput name="Mật khẩu" type="password" v-model="form.password" :message="form.errors.password" />
            <TextInput name="Nhập lại mật khẩu" type="password" v-model="form.password_confirmation"/>

            <div>
                <button class="primary-btn" :disabled="form.processing">Đăng ký</button>
            </div>
        </form>
    </div>
</template>
