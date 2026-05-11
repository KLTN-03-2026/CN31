<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: Boolean,
});
</script>

<template>
    <Head title="ProcureFlow | Nền tảng Quản trị & Mua sắm Doanh nghiệp" />

    <div class="min-h-screen bg-[#F8FAFC] font-sans selection:bg-blue-600/20 selection:text-blue-900 flex flex-col relative">

        <!-- HIỆU ỨNG ÁNH SÁNG NỀN VÔ CỰC (Space Real-estate) -->
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[800px] h-[500px] bg-gradient-to-b from-blue-100/60 to-transparent blur-3xl -z-10 rounded-full pointer-events-none"></div>

        <!-- HEADER TRONG SUỐT (Glassmorphism) -->
        <header class="fixed top-0 inset-x-0 z-50 bg-white/70 backdrop-blur-md border-b border-slate-200/50 transition-all">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16 sm:h-20">
                    <!-- Logo -->
                    <div class="flex-shrink-0 flex items-center gap-2.5 cursor-pointer group" @click="window.scrollTo(0,0)">
                          <img
                            src="https://cdn.jsdelivr.net/gh/glincker/thesvg@main/public/icons/iterm2/mono.svg"
                            alt="iTerm2"
                            class="h-6 w-6"
                        />
                        <span class="font-black text-xl tracking-tight text-slate-900">MrGiot<span class="text-blue-600">Tech</span></span>
                    </div>

                    <!-- Navigation Desktop -->
                    <nav class="hidden md:flex gap-8">
                        <a href="#tinh-nang" class="text-sm font-bold text-slate-500 hover:text-slate-900 transition-colors">Hệ sinh thái</a>
                        <a href="#giai-phap" class="text-sm font-bold text-slate-500 hover:text-slate-900 transition-colors">Giải pháp</a>
                        <a href="#lien-he" class="text-sm font-bold text-slate-500 hover:text-slate-900 transition-colors">Tài liệu API</a>
                    </nav>

                    <!-- Auth / CTA -->
                    <div class="flex items-center gap-4">
                        <Link v-if="canLogin" :href="route('login')" class="group px-5 py-2.5 text-sm font-bold text-white bg-slate-900 hover:bg-slate-800 rounded-xl transition-all shadow-sm flex items-center gap-2">
                            Đăng nhập
                            <svg class="w-4 h-4 transform group-hover:translate-x-0.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                        </Link>
                    </div>
                </div>
            </div>
        </header>

        <!-- HERO SECTION -->
        <main class="pt-32 pb-16 sm:pt-44 sm:pb-24 flex-grow relative overflow-hidden z-10">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center animate-fade-in-up">
          
                <h1 class="text-4xl sm:text-6xl lg:text-7xl font-black text-slate-900 tracking-tighter mb-6 leading-[1.15]">
                    Chuyển đổi số toàn diện <br class="hidden md:block"/>
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-indigo-500">
                        Quy trình vận hành nội bộ.
                    </span>
                </h1>

                <p class="max-w-2xl mx-auto text-base sm:text-lg text-slate-500 mb-10 leading-relaxed font-medium">
                    ProcureFlow giúp doanh nghiệp tự động hóa từ khâu đề xuất mua sắm, phê duyệt đa cấp đến quản lý nhân sự. Xóa bỏ giấy tờ, tối ưu hóa 80% thời gian vận hành.
                </p>

                <!-- CTA Actions -->
                <div class="flex flex-col sm:flex-row justify-center gap-4">
                    <Link :href="route('login')" class="px-8 py-3.5 bg-blue-600 hover:bg-blue-700 text-white text-base font-bold rounded-xl shadow-lg shadow-blue-600/20 transition-all hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-600">
                        Đăng nhập Hệ thống
                    </Link>
                    <a href="#tinh-nang" class="px-8 py-3.5 bg-white text-slate-700 border border-slate-200 hover:border-slate-300 hover:bg-slate-50 text-base font-bold rounded-xl transition-all shadow-sm flex items-center justify-center gap-2">
                        Khám phá tính năng
                        <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                    </a>
                </div>

                <!-- 3D DASHBOARD MOCKUP (Tuyệt chiêu CSS-only Dashboard) -->
                <div class="mt-20 mx-auto max-w-5xl relative group perspective px-4">
                    <!-- Ánh sáng phản chiếu tạo độ bóng bẩy -->
                    <div class="absolute inset-0 bg-gradient-to-t from-[#F8FAFC] via-transparent to-transparent z-20 pointer-events-none"></div>

                    <div class="bg-white rounded-t-2xl border-x border-t border-slate-200 shadow-2xl overflow-hidden transform transition-all duration-700 ease-out group-hover:-translate-y-4 animate-float">
                        <!-- Trình duyệt giả lập (Browser Bar) -->
                        <div class="bg-slate-50 border-b border-slate-200 px-4 py-3 flex items-center gap-2">
                            <div class="w-3 h-3 rounded-full bg-rose-400"></div>
                            <div class="w-3 h-3 rounded-full bg-amber-400"></div>
                            <div class="w-3 h-3 rounded-full bg-emerald-400"></div>
                            <div class="ml-4 bg-white text-slate-400 text-[10px] font-bold px-3 py-1.5 rounded-md w-64 text-center border border-slate-200/60 shadow-sm flex items-center justify-center gap-1.5">
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                                procureflow.mrgiot.com
                            </div>
                        </div>

                        <!-- UI Dashboard Ảo -->
                        <div class="h-[400px] bg-slate-100/50 flex">
                            <!-- Fake Sidebar -->
                            <div class="w-48 bg-white border-r border-slate-200 p-4 hidden sm:flex flex-col gap-3">
                                <div class="w-full h-6 bg-slate-100 rounded mb-4"></div>
                                <div class="w-full h-8 bg-blue-50 rounded border border-blue-100"></div>
                                <div class="w-3/4 h-8 bg-slate-50 rounded"></div>
                                <div class="w-5/6 h-8 bg-slate-50 rounded"></div>
                                <div class="w-2/3 h-8 bg-slate-50 rounded"></div>
                            </div>

                            <!-- Fake Main Content -->
                            <div class="flex-grow p-6 flex flex-col gap-6">
                                <!-- TopNav Skeleton -->
                                <div class="flex justify-between items-center">
                                    <div class="w-40 h-6 bg-slate-200 rounded-md"></div>
                                    <div class="flex gap-3">
                                        <div class="w-8 h-8 bg-slate-200 rounded-full"></div>
                                        <div class="w-8 h-8 bg-slate-200 rounded-full"></div>
                                    </div>
                                </div>

                                <!-- KPI Cards Skeleton -->
                                <div class="grid grid-cols-3 gap-4">
                                    <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex flex-col gap-2">
                                        <div class="w-16 h-4 bg-slate-100 rounded"></div>
                                        <div class="w-24 h-8 bg-slate-200 rounded"></div>
                                    </div>
                                    <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex flex-col gap-2">
                                        <div class="w-16 h-4 bg-slate-100 rounded"></div>
                                        <div class="w-16 h-8 bg-slate-200 rounded"></div>
                                    </div>
                                    <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex flex-col gap-2">
                                        <div class="w-16 h-4 bg-slate-100 rounded"></div>
                                        <div class="w-20 h-8 bg-slate-200 rounded"></div>
                                    </div>
                                </div>

                                <!-- Table Skeleton -->
                                <div class="bg-white flex-grow rounded-xl border border-slate-200 shadow-sm p-4 flex flex-col gap-3">
                                    <div class="w-full h-8 bg-slate-50 rounded border border-slate-100"></div>
                                    <div class="w-full h-8 bg-slate-50 rounded border border-slate-100"></div>
                                    <div class="w-full h-8 bg-slate-50 rounded border border-slate-100"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <!-- HỆ SINH THÁI TÍNH NĂNG -->
        <section id="tinh-nang" class="py-24 bg-white border-y border-slate-200 relative z-20">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-sm font-black text-blue-600 uppercase tracking-widest mb-2">Tính năng cốt lõi</h2>
                    <h3 class="text-3xl sm:text-4xl font-black text-slate-900 tracking-tight">Hệ sinh thái duy nhất bạn cần</h3>
                    <p class="text-slate-500 mt-4 font-medium text-lg max-w-2xl mx-auto">Mọi công cụ để số hóa và quản trị doanh nghiệp được quy tụ tại một nền tảng, thiết kế với trải nghiệm người dùng tuyệt hảo.</p>
                </div>

                <div class="grid md:grid-cols-3 gap-8">
                    <!-- Feature Card 1 -->
                    <div class="bg-slate-50 rounded-2xl p-8 border border-slate-100 hover:border-blue-200 hover:bg-blue-50/50 transition-all duration-300 group">
                        <div class="w-14 h-14 bg-white border border-slate-200 text-blue-600 rounded-xl flex items-center justify-center mb-6 shadow-sm group-hover:scale-110 group-hover:bg-blue-600 group-hover:text-white group-hover:border-blue-600 transition-all">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                        </div>
                        <h4 class="text-xl font-bold text-slate-900 mb-3 group-hover:text-blue-700 transition-colors">Quy trình Mua sắm</h4>
                        <p class="text-slate-600 leading-relaxed text-sm font-medium">Số hóa toàn bộ quy trình yêu cầu vật tư. Phê duyệt đa cấp bậc từ Trưởng phòng đến Giám đốc. Theo dõi ngân sách theo thời gian thực.</p>
                    </div>

                    <!-- Feature Card 2 -->
                    <div class="bg-slate-50 rounded-2xl p-8 border border-slate-100 hover:border-emerald-200 hover:bg-emerald-50/50 transition-all duration-300 group">
                        <div class="w-14 h-14 bg-white border border-slate-200 text-emerald-600 rounded-xl flex items-center justify-center mb-6 shadow-sm group-hover:scale-110 group-hover:bg-emerald-600 group-hover:text-white group-hover:border-emerald-600 transition-all">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                        </div>
                        <h4 class="text-xl font-bold text-slate-900 mb-3 group-hover:text-emerald-700 transition-colors">Quản trị Nhân sự</h4>
                        <p class="text-slate-600 leading-relaxed text-sm font-medium">Quản lý quỹ phép năm tự động. Nhân sự tạo đơn xin nghỉ nhanh chóng, HR dễ dàng xuất báo cáo tổng hợp ngày công mỗi tháng.</p>
                    </div>

                    <!-- Feature Card 3 -->
                    <div class="bg-slate-50 rounded-2xl p-8 border border-slate-100 hover:border-purple-200 hover:bg-purple-50/50 transition-all duration-300 group">
                        <div class="w-14 h-14 bg-white border border-slate-200 text-purple-600 rounded-xl flex items-center justify-center mb-6 shadow-sm group-hover:scale-110 group-hover:bg-purple-600 group-hover:text-white group-hover:border-purple-600 transition-all">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9.5a2.5 2.5 0 00-2.5-2.5H15M9 11l3 3L22 4" /></svg>
                        </div>
                        <h4 class="text-xl font-bold text-slate-900 mb-3 group-hover:text-purple-700 transition-colors">Truyền thông Nội bộ</h4>
                        <p class="text-slate-600 leading-relaxed text-sm font-medium">Truyền tải thông báo, sự kiện và quy chế đến toàn thể công ty trực quan. Tích hợp trình soạn thảo chuyên sâu đa phương tiện.</p>
                    </div>
                </div>
            </div>
        </section>

    </div>
</template>

<style>
/* Kích hoạt smooth scroll cho các anchor link */
html { scroll-behavior: smooth; }

/* Phối cảnh 3D cho Dashboard Mockup */
.perspective { perspective: 1200px; }
.perspective > div { transform: rotateX(8deg) rotateY(-5deg) rotateZ(1deg); }
.group:hover .perspective > div { transform: rotateX(4deg) rotateY(-2deg) rotateZ(0deg) translateY(-10px); }

/* Animation làm nổi bật content lúc mới load */
.animate-fade-in-up {
    animation: fadeInUp 0.8s cubic-bezier(0.2, 0.8, 0.2, 1) forwards;
}

@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Animation làm cho Dashboard "thở" bay bổng nhẹ nhàng */
.animate-float {
    animation: floating 6s ease-in-out infinite;
}

@keyframes floating {
    0% { transform: translateY(0px) rotateX(8deg) rotateY(-5deg); }
    50% { transform: translateY(-15px) rotateX(9deg) rotateY(-4deg); }
    100% { transform: translateY(0px) rotateX(8deg) rotateY(-5deg); }
}
</style>
