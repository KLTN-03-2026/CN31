<script setup>
import { useForm, Head, Link } from "@inertiajs/vue3";
// import TextInput from "@/Components/Form/TextInput.vue";

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
    if (!file) return;

    if (file.size > 5242880) {
        alert('Vui lòng chọn ảnh nhỏ hơn 5MB nhé!');
        e.target.value = '';
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

    <div class="min-h-screen bg-slate-50 flex flex-col justify-center py-10 sm:px-6 lg:px-8 selection:bg-blue-100 selection:text-blue-900">

        <div class="sm:mx-auto sm:w-full sm:max-w-xl text-center mb-8">
            <h1 class="text-4xl font-black text-blue-600 tracking-tighter">Procure<span class="text-slate-900">Flow</span></h1>
            <p class="mt-3 text-center text-sm font-medium text-slate-500 uppercase tracking-widest">Tạo tài khoản mới</p>
        </div>

        <div class="sm:mx-auto sm:w-full sm:max-w-[550px]">
            <div class="bg-white py-10 px-8 shadow-2xl shadow-slate-200/50 sm:rounded-2xl border border-slate-100">
                <form @submit.prevent="submit" class="space-y-2">

                    <div class="flex flex-col items-center mb-8">
                        <div class="relative w-24 h-24 rounded-full overflow-hidden border-2 border-dashed border-slate-300 hover:border-blue-500 transition-colors group cursor-pointer shadow-sm">
                            <img class="object-cover w-full h-full" :src="form.preview ?? '/storage/avatars/default.png'" alt="Avatar" />

                            <label for="avatar" class="absolute inset-0 bg-slate-900/40 flex flex-col items-center justify-center opacity-0 group-hover:opacity-100 cursor-pointer transition-all duration-200 backdrop-blur-[2px]">
                                <svg class="w-6 h-6 text-white mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                <span class="text-white text-[10px] font-bold uppercase tracking-wider">Tải ảnh</span>
                            </label>
                            <input type="file" id="avatar" @input="change" accept="image/*" hidden />
                        </div>
                        <p class="text-red-500 text-xs mt-2 font-bold" v-if="form.errors.avatar">{{ form.errors.avatar }}</p>
                    </div>

                    <TextInput label="Họ và Tên" v-model="form.name" :message="form.errors.name" placeholder="Ví dụ: Nguyễn Văn A" />

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-5">
                        <TextInput label="Email công việc" type="email" v-model="form.email" :message="form.errors.email" placeholder="email@congty.com" />

                        <div class="mb-5">
                            <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-widest mb-2">Phòng ban công tác</label>
                            <select v-model="form.phong_ban_id" class="w-full border-slate-200 focus:border-blue-600 focus:ring focus:ring-blue-600/20 rounded-xl shadow-sm text-sm transition-all bg-slate-50 focus:bg-white py-3 px-4 text-slate-800 font-medium" :class="{'!border-red-500 focus:!ring-red-500/20 !bg-red-50 text-red-900': form.errors.phong_ban_id}">
                                <option value="" disabled>-- Chọn phòng ban --</option>
                                <option value="1">Phòng Giám Đốc</option>
                                <option value="2">Phòng Kế Toán</option>
                                <option value="3">Phòng IT</option>
                                <option value="4">Phòng Nhân sự</option>
                            </select>
                            <p class="text-red-500 text-xs mt-1.5 font-bold" v-if="form.errors.phong_ban_id">{{ form.errors.phong_ban_id }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-5">
                        <TextInput label="Mật khẩu" type="password" v-model="form.password" :message="form.errors.password" placeholder="Tối thiểu 8 ký tự" />
                        <TextInput label="Xác nhận mật khẩu" type="password" v-model="form.password_confirmation" placeholder="Nhập lại mật khẩu" />
                    </div>

                    <div class="pt-4">
                        <button type="submit" :disabled="form.processing" class="w-full flex justify-center items-center gap-2 py-3.5 px-4 border border-transparent rounded-xl shadow-sm text-sm font-black tracking-wide text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all disabled:opacity-70">
                            <svg v-if="form.processing" class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            {{ form.processing ? 'ĐANG TẠO TÀI KHOẢN...' : 'ĐĂNG KÝ' }}
                        </button>
                    </div>
                </form>

                <div class="mt-8 text-center text-sm text-slate-500 font-medium border-t border-slate-100 pt-6">
                    Đã có tài khoản?
                    <Link :href="route('login')" class="font-bold text-blue-600 hover:text-blue-800 ml-1 transition-colors">Đăng nhập ngay</Link>
                </div>
            </div>
        </div>
    </div>
</template>
