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
    <Head title="Đăng nhập hệ thống" />

    <div class="min-h-screen bg-slate-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8 selection:bg-blue-100 selection:text-blue-900">

        <div class="sm:mx-auto sm:w-full sm:max-w-md text-center mb-8">
            <h1 class="text-4xl font-black text-blue-600 tracking-tighter">Procure<span class="text-slate-900">Flow</span></h1>
            <p class="mt-3 text-center text-sm font-medium text-slate-500 uppercase tracking-widest">Đăng nhập hệ thống</p>
        </div>

        <div class="sm:mx-auto sm:w-full sm:max-w-[420px]">
            <div class="bg-white py-10 px-8 shadow-2xl shadow-slate-200/50 sm:rounded-2xl border border-slate-100">
                <form @submit.prevent="submit" class="space-y-6">

                    <TextInput label="Địa chỉ Email" type="email" v-model="form.email" :message="form.errors.email" placeholder="VD: nhanvien@procureflow.test" autofocus />

                    <TextInput label="Mật khẩu" type="password" v-model="form.password" :message="form.errors.password" placeholder="••••••••" />

                    <div class="flex items-center justify-between mt-2">
                        <div class="flex items-center gap-2">
                            <input type="checkbox" v-model="form.remember" id="remember" class="w-4 h-4 rounded border-slate-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500/20 transition-colors cursor-pointer" />
                            <label for="remember" class="text-sm text-slate-600 font-bold cursor-pointer select-none">Ghi nhớ tôi</label>
                        </div>
                        <a href="#" class="text-sm font-bold text-blue-600 hover:text-blue-800 transition-colors">Quên mật khẩu?</a>
                    </div>

                    <div class="pt-2">
                        <button type="submit" :disabled="form.processing" class="w-full flex justify-center items-center gap-2 py-3.5 px-4 border border-transparent rounded-xl shadow-sm text-sm font-black tracking-wide text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all disabled:opacity-70">
                            <svg v-if="form.processing" class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            {{ form.processing ? 'ĐANG XỬ LÝ...' : 'ĐĂNG NHẬP' }}
                        </button>
                    </div>
                </form>

                <!-- <div class="mt-8 text-center text-sm text-slate-500 font-medium border-t border-slate-100 pt-6">
                    Chưa có tài khoản?
                    <Link :href="route('register')" class="font-bold text-blue-600 hover:text-blue-800 ml-1 transition-colors">Đăng ký ngay</Link>
                </div> -->
            </div>
        </div>
    </div>
</template>
