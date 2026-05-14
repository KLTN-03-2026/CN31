<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

defineOptions({
    layout: null
});

const page = usePage();
const user = computed(() => page.props.auth?.user);

const refreshPage = () => {
    window.location.reload();
};
</script>

<template>
    <Head title="Bảo trì hệ thống" />

    <div class="min-h-screen bg-[radial-gradient(ellipse_at_top_right,_var(--tw-gradient-stops))] from-slate-100 via-slate-50 to-blue-50 flex flex-col items-center justify-center p-6 relative overflow-hidden">

        <!-- Background Decor (Trang trí nền) -->
        <div class="absolute top-0 left-0 w-full h-full overflow-hidden pointer-events-none -z-10">
            <div class="absolute -top-[10%] -left-[10%] w-[40%] h-[40%] rounded-full bg-blue-100/50 blur-3xl"></div>
            <div class="absolute -bottom-[10%] -right-[10%] w-[40%] h-[40%] rounded-full bg-slate-200/50 blur-3xl"></div>
        </div>

        <!-- LỐI THOÁT HIỂM (ESCAPE HATCH) -->
        <div class="absolute top-4 right-4 sm:top-8 sm:right-8 z-10">
            <template v-if="user">
                <Link :href="route('logout')" method="post" as="button"
                      class="group flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-slate-700 bg-white/80 backdrop-blur-md border border-slate-200 rounded-full shadow-sm hover:text-red-600 hover:border-red-200 hover:bg-red-50 transition-all duration-300">
                    <div class="w-2 h-2 rounded-full bg-green-500 group-hover:animate-pulse"></div>
                    <span>{{ user.name }} (Đăng xuất)</span>
                    <svg class="w-4 h-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                </Link>
            </template>
            <template v-else>
                <Link :href="route('login')"
                      class="flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-slate-700 bg-white/80 backdrop-blur-md border border-slate-200 rounded-full shadow-sm hover:text-blue-600 hover:border-blue-200 hover:bg-blue-50 transition-all duration-300">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                    </svg>
                    Quản trị viên
                </Link>
            </template>
        </div>

        <!-- NỘI DUNG CHÍNH -->
        <div class="w-full max-w-2xl bg-white/40 backdrop-blur-xl border border-white/60 shadow-[0_8px_30px_rgb(0,0,0,0.04)] rounded-3xl p-8 sm:p-12 flex flex-col items-center text-center relative">

            <!-- Animated Gears (Bánh răng chuyển động) -->
            <div class="relative w-32 h-32 mb-10">
                <svg class="w-20 h-20 text-blue-500 animate-[spin_8s_linear_infinite] absolute top-0 left-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <svg class="w-14 h-14 text-slate-400 animate-[spin_12s_linear_infinite_reverse] absolute bottom-0 right-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            </div>

            <h1 class="text-3xl sm:text-4xl font-black text-slate-900 mb-4 tracking-tight">
                Hệ thống đang bảo trì
            </h1>

            <p class="text-slate-600 text-lg leading-relaxed max-w-md mb-10">
                Chúng tôi đang thực hiện một số nâng cấp quan trọng cho <span class="font-bold text-blue-600">Procureflow</span> để mang lại trải nghiệm tốt hơn.
            </p>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row gap-4 w-full justify-center items-center">
                <button @click="refreshPage"
                        class="w-full sm:w-auto px-8 py-3 bg-slate-900 text-white rounded-2xl font-bold hover:bg-slate-800 transition-all active:scale-95 flex items-center justify-center gap-2 shadow-lg shadow-slate-200">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Tải lại trang
                </button>

                <a href="mailto:it-support@yourcompany.com"
                   class="w-full sm:w-auto px-8 py-3 bg-white text-slate-700 border border-slate-200 rounded-2xl font-bold hover:bg-slate-50 transition-all flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    Liên hệ hỗ trợ
                </a>
            </div>

            <!-- Footer info -->
            <div class="mt-12 pt-8 border-t border-slate-100 w-full flex flex-col sm:flex-row items-center justify-between text-slate-400 text-sm gap-4">
                <div class="flex items-center gap-2">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-blue-500"></span>
                    </span>
                    Trạng thái: Đang nâng cấp cơ sở dữ liệu
                </div>
                <div class="font-medium uppercase tracking-widest text-[10px]">
                    &copy; {{ new Date().getFullYear() }} Procureflow 
                </div>
            </div>
        </div>

        <!-- Sub text -->
        <p class="mt-8 text-slate-400 text-sm">
            Thời gian dự kiến hoàn thành: <span class="text-slate-600 font-semibold">15 phút nữa</span>
        </p>
    </div>
</template>

<style scoped>
</style>
