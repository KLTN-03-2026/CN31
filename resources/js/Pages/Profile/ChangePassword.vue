<script setup>
import { ref } from 'vue';
import { useForm, Head } from '@inertiajs/vue3';

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

// Trạng thái ẩn/hiện mật khẩu
const showCurrent = ref(false);
const showNew = ref(false);
const showConfirm = ref(false);

const submit = () => {
    form.put(route('profile.update'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
};
</script>

<template>
    <Head title="Bảo mật & Mật khẩu" />

    <div class="min-h-[calc(100vh-64px)] bg-[#F8FAFC] py-10 sm:py-12">
        <div class="max-w-xl mx-auto px-4 sm:px-6">

            <div class="mb-6 sm:mb-8">
                <h1 class="text-2xl font-black text-slate-900 tracking-tight">Cài đặt bảo mật</h1>
                <p class="text-sm font-medium text-slate-500 mt-1">Cập nhật mật khẩu định kỳ để bảo vệ tài khoản của bạn.</p>
            </div>

            <form @submit.prevent="submit" class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                <div class="p-6 sm:p-8 space-y-6">

                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-widest mb-2">
                            Mật khẩu hiện tại <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input :type="showCurrent ? 'text' : 'password'" v-model="form.current_password"
                                class="w-full pl-4 pr-11 py-2.5 text-sm font-medium border border-slate-300 rounded-xl focus:ring-1 focus:ring-slate-900 focus:border-slate-900 transition-all shadow-sm placeholder:text-slate-400 placeholder:font-normal"
                                placeholder="Nhập mật khẩu đang sử dụng" required>

                            <button type="button" @click="showCurrent = !showCurrent" tabindex="-1"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-700 transition-colors p-1">
                                <svg v-if="!showCurrent" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                <svg v-else class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.29 3.29m0 0a10.05 10.05 0 015.058-1.21c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0l-3.29-3.29" /></svg>
                            </button>
                        </div>
                        <p v-if="form.errors.current_password" class="text-red-500 text-xs font-bold mt-1.5 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                            {{ form.errors.current_password }}
                        </p>
                    </div>

                    <div class="border-t border-slate-100"></div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-widest mb-2">
                            Mật khẩu mới <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input :type="showNew ? 'text' : 'password'" v-model="form.password"
                                class="w-full pl-4 pr-11 py-2.5 text-sm font-medium border border-slate-300 rounded-xl focus:ring-1 focus:ring-slate-900 focus:border-slate-900 transition-all shadow-sm placeholder:text-slate-400 placeholder:font-normal"
                                placeholder="Tạo mật khẩu mới" required>

                            <button type="button" @click="showNew = !showNew" tabindex="-1"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-700 transition-colors p-1">
                                <svg v-if="!showNew" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                <svg v-else class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.29 3.29m0 0a10.05 10.05 0 015.058-1.21c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0l-3.29-3.29" /></svg>
                            </button>
                        </div>
                        <p class="text-[11px] font-medium text-slate-400 mt-2 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            Mật khẩu phải dài tối thiểu 8 ký tự.
                        </p>
                        <p v-if="form.errors.password" class="text-red-500 text-xs font-bold mt-1.5">{{ form.errors.password }}</p>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-widest mb-2">
                            Xác nhận mật khẩu mới <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input :type="showConfirm ? 'text' : 'password'" v-model="form.password_confirmation"
                                class="w-full pl-4 pr-11 py-2.5 text-sm font-medium border border-slate-300 rounded-xl focus:ring-1 focus:ring-slate-900 focus:border-slate-900 transition-all shadow-sm placeholder:text-slate-400 placeholder:font-normal"
                                placeholder="Nhập lại mật khẩu vừa tạo" required>

                            <button type="button" @click="showConfirm = !showConfirm" tabindex="-1"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-700 transition-colors p-1">
                                <svg v-if="!showConfirm" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                <svg v-else class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.29 3.29m0 0a10.05 10.05 0 015.058-1.21c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0l-3.29-3.29" /></svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-between">

                    <div class="flex-1">
                        <transition enter-active-class="transition ease-out duration-300" enter-from-class="opacity-0 -translate-x-2" enter-to-class="opacity-100 translate-x-0" leave-active-class="transition ease-in duration-200" leave-from-class="opacity-100" leave-to-class="opacity-0">
                            <p v-if="form.recentlySuccessful" class="text-[13px] font-bold text-emerald-600 flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                Đã cập nhật mật khẩu!
                            </p>
                        </transition>
                    </div>

                    <button type="submit" :disabled="form.processing"
                        class="px-6 py-2.5 bg-slate-900 hover:bg-slate-800 text-white text-sm font-bold rounded-lg shadow-sm transition-all disabled:opacity-70 disabled:cursor-not-allowed flex items-center gap-2 shrink-0">
                        <span v-if="form.processing">
                            <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        </span>
                        {{ form.processing ? 'Đang lưu...' : 'Lưu thay đổi' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
