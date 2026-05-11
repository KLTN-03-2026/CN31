<script setup>
import { useForm, Head } from "@inertiajs/vue3";
import TextInput from "@/Components/Form/TextInput.vue";

defineOptions({ layout: null });

const form = useForm({
    email: null,
    password: null,
    remember: false,
});

const submit = () => {
    form.post(route("login.store"), {
        onFinish: () => form.reset("password"),
    });
};
</script>

<template>
    <Head title="Đăng nhập" />

    <div class="min-h-screen w-full bg-[#f0f2f5] flex items-center justify-center p-4 sm:p-8 font-sans selection:bg-blue-600/10 selection:text-blue-900 overflow-hidden">

        <div class="max-w-6xl w-full grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-20 items-center relative z-10">

            <div class="flex flex-col text-center lg:text-left order-2 lg:order-1 px-4 lg:px-0 mt-8 lg:mt-0">

                <h1 class="text-5xl sm:text-6xl font-black tracking-tighter text-blue-600 mb-6 drop-shadow-sm">
                    MrGiot<span class="text-slate-900">Tech</span>
                </h1>

                <h2 class="text-2xl sm:text-3xl font-semibold text-slate-800 leading-snug mb-12 max-w-lg mx-auto lg:mx-0">
                    Nền tảng quản trị mua sắm và tối ưu hóa quy trình doanh nghiệp.
                </h2>

                <div class="relative w-full max-w-[420px] mx-auto lg:mx-0">

                    <img
                        src="https://illustrations.popsy.co/blue/freelancer.svg"
                        alt="ProcureFlow Technology"
                        class="w-full h-auto object-contain drop-shadow-xl animate-floating"
                    />

                    <div class="absolute -bottom-2 left-1/2 -translate-x-1/2 h-3 bg-slate-400/30 blur-md rounded-[100%] animate-shadow-pulse"></div>
                </div>
            </div>

            <div class="flex justify-center lg:justify-end order-1 lg:order-2 w-full">

                <div class="bg-white p-8 sm:p-10 shadow-[0_20px_40px_-15px_rgba(0,0,0,0.05)] rounded-2xl w-full max-w-[420px] border border-slate-100 relative">

                    <div class="mb-8 text-center">
                        <h3 class="text-2xl font-black text-slate-900 tracking-tight">Đăng nhập hệ thống</h3>
                    </div>

                    <form @submit.prevent="submit" class="space-y-5">

                        <TextInput
                            label="Địa chỉ Email"
                            type="email"
                            v-model="form.email"
                            :message="form.errors.email"
                            placeholder="Email"
                            autofocus
                        />

                        <TextInput
                            label="Mật khẩu"
                            type="password"
                            v-model="form.password"
                            :message="form.errors.password"
                            placeholder="••••••••"
                        />

                        <!-- Phân cấp thị giác: Nút CTA khổng lồ -->
                        <div class="pt-2">
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="group relative w-full flex justify-center items-center py-3.5 px-4 border-2 border-blue-600 rounded-xl text-[15px] font-bold text-blue-600 overflow-hidden transition-all focus:outline-none focus:ring-4 focus:ring-blue-600/20 disabled:opacity-50 disabled:cursor-not-allowed bg-white"
                            >
                                <!-- Hiệu ứng Sweep nền xanh khi Hover -->
                                <div class="absolute inset-0 w-0 bg-blue-600 transition-all duration-300 ease-out group-hover:w-full group-disabled:w-full group-disabled:bg-blue-600 group-disabled:duration-0"></div>

                                <span class="relative z-10 flex items-center gap-2 group-hover:text-white transition-colors duration-300 group-disabled:text-white">
                                    <svg v-if="form.processing" class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                    {{ form.processing ? 'ĐANG XỬ LÝ...' : 'Đăng nhập' }}
                                </span>
                            </button>
                        </div>

                        <!-- Forgot Password -->
                        <div class="flex items-center justify-center mt-4">
                            <a href="#" class="text-sm font-bold text-slate-500 hover:text-blue-600 transition-colors">Quên mật khẩu?</a>
                        </div>

                        <!-- Divider -->
                        <div class="flex items-center gap-4 my-6">
                            <div class="flex-1 h-px bg-slate-100"></div>
                            <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">Tùy chọn</span>
                            <div class="flex-1 h-px bg-slate-100"></div>
                        </div>

                        <!-- Remember Me -->
                        <div class="flex items-center justify-center">
                            <label class="flex items-center gap-2 cursor-pointer group">
                                <input
                                    type="checkbox"
                                    v-model="form.remember"
                                    class="w-4 h-4 rounded border-slate-300 text-blue-600 shadow-sm focus:border-blue-600 focus:ring focus:ring-blue-600/20 transition-colors cursor-pointer"
                                />
                                <span class="text-sm text-slate-600 font-bold select-none group-hover:text-slate-900 transition-colors">Lưu phiên đăng nhập</span>
                            </label>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>

@keyframes floating {
    0%, 100% {
        transform: translateY(0) rotate(0deg) scale(1);
    }
    50% {
        /* Bay lên 12px, nghiêng nhẹ 1 độ tạo cảm giác tự nhiên */
        transform: translateY(-12px) rotate(-1deg) scale(1.02);
    }
}

@keyframes shadow-pulse {
    0%, 100% {
        width: 80%;
        opacity: 0.5;
    }
    50% {
        width: 40%;
        opacity: 0.15;
    }
}

.animate-floating {
    animation: floating 2s ease-in-out infinite;
}

.animate-shadow-pulse {
    animation: shadow-pulse 4s ease-in-out infinite;
}
</style>
