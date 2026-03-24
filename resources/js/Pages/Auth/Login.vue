<script setup>
import { useForm, Head, Link } from "@inertiajs/vue3";
import TextInput from "@/Components/TextInput.vue";

const form = useForm({
    email: null,
    password: null,
    remember: false,
});

const submit = () => {
    form.post(route("login.store"), {
        onError: () => form.reset("password"),
    });
};
</script>

<template>
    <Head title="Đăng nhập" />

    <div class="min-h-screen bg-gray-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-md text-center">
            <h1 class="text-3xl font-extrabold text-blue-700 tracking-tight">Procure<span class="text-gray-900">Flow</span></h1>
            <h2 class="mt-4 text-center text-xl font-bold text-gray-800">Đăng nhập hệ thống</h2>
        </div>

        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
            <div class="bg-white py-8 px-4 shadow-xl sm:rounded-xl sm:px-10 border border-gray-100">
                <form @submit.prevent="submit">

                    <TextInput name="Địa chỉ Email" type="email" v-model="form.email" :message="form.errors.email" placeholder="Ví dụ: nhanvien@procureflow.test" />

                    <TextInput name="Mật khẩu" type="password" v-model="form.password" :message="form.errors.password" placeholder="••••••••" />

                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center gap-2">
                            <input type="checkbox" v-model="form.remember" id="remember" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50" />
                            <label for="remember" class="text-sm text-gray-600 font-medium cursor-pointer">Ghi nhớ đăng nhập</label>
                        </div>
                        <a href="#" class="text-sm font-bold text-blue-600 hover:text-blue-500">Quên mật khẩu?</a>
                    </div>

                    <div>
                        <button type="submit" :disabled="form.processing" class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-md shadow-sm text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                            {{ form.processing ? 'Đang xử lý...' : 'ĐĂNG NHẬP' }}
                        </button>
                    </div>
                </form>

                <div class="mt-6 text-center text-sm text-gray-600">
                    Chưa có tài khoản?
                    <Link :href="route('register')" class="font-bold text-blue-600 hover:text-blue-500">Đăng ký ngay</Link>
                </div>
            </div>
        </div>
    </div>
</template>
