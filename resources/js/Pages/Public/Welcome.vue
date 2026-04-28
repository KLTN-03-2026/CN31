
<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: Boolean,
});
</script>


<template>
    <Head title="ProcureFlow | Giải pháp quản trị & Mua sắm doanh nghiệp" />

    <div class="min-h-screen bg-[#F8FAFC] font-sans selection:bg-blue-900 selection:text-white flex flex-col">

        <header class="fixed top-0 inset-x-0 z-50 bg-white/90 backdrop-blur-sm border-b border-slate-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <div class="flex-shrink-0 flex items-center gap-2 cursor-pointer" @click="window.scrollTo(0,0)">
                        <div class="w-8 h-8 bg-blue-600 rounded flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                        </div>
                        <span class="font-black text-xl tracking-tight text-slate-900">Procure<span class="text-blue-600">Flow</span></span>
                    </div>

                    <nav class="hidden md:flex gap-8">
                        <a href="#tinh-nang" class="text-sm font-semibold text-slate-600 hover:text-blue-600 transition-colors">Tính năng lõi</a>
                        <a href="#giai-phap" class="text-sm font-semibold text-slate-600 hover:text-blue-600 transition-colors">Giải pháp</a>
                        <a href="#lien-he" class="text-sm font-semibold text-slate-600 hover:text-blue-600 transition-colors">Liên hệ Demo</a>
                    </nav>

                    <div class="flex items-center gap-4">
                        <Link v-if="canLogin" :href="route('login')" class="px-5 py-2 text-sm font-bold text-white bg-slate-900 hover:bg-slate-800 rounded-lg transition-colors shadow-sm">
                            Đăng nhập Hệ thống &rarr;
                        </Link>
                    </div>
                </div>
            </div>
        </header>

        <main class="pt-32 pb-16 sm:pt-40 sm:pb-24 flex-grow relative overflow-hidden">
            <div class="absolute top-0 right-0 -mr-20 -mt-20 w-[500px] h-[500px] bg-blue-100/50 rounded-full blur-3xl -z-10"></div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-slate-100 border border-slate-200 text-slate-700 text-xs font-bold mb-8">
                    Phiên bản 1.0.0 đã chính thức phát hành
                </div>

                <h1 class="text-4xl sm:text-6xl lg:text-7xl font-black text-slate-900 tracking-tight mb-6 leading-tight">
                    Chuyển đổi số toàn diện <br class="hidden sm:block"/>
                    Quy trình vận hành nội bộ.
                </h1>

                <p class="max-w-2xl mx-auto text-base sm:text-lg text-slate-500 mb-10 leading-relaxed font-medium">
                    ProcureFlow giúp doanh nghiệp tự động hóa từ khâu đề xuất mua sắm, phê duyệt đa cấp đến quản lý nhân sự và thông tin nội bộ. Xóa bỏ giấy tờ, tối ưu hóa thời gian và chi phí.
                </p>

                <div class="flex flex-col sm:flex-row justify-center gap-4">
                    <Link :href="route('login')" class="px-8 py-3.5 bg-blue-600 hover:bg-blue-700 text-white text-base font-bold rounded-xl shadow-lg shadow-blue-600/20 transition-all hover:-translate-y-0.5">
                        Trải nghiệm hệ thống
                    </Link>
                    <a href="#tinh-nang" class="px-8 py-3.5 bg-white text-slate-700 border border-slate-200 hover:border-slate-300 hover:bg-slate-50 text-base font-bold rounded-xl transition-all shadow-sm">
                        Khám phá tính năng
                    </a>
                </div>

                <div class="mt-16 mx-auto max-w-5xl relative group perspective">
                    <div class="absolute inset-0 bg-gradient-to-t from-[#F8FAFC] via-transparent to-transparent z-10"></div>
                    <div class="bg-white rounded-t-2xl border-x border-t border-slate-200 shadow-2xl overflow-hidden transform transition-transform duration-500 group-hover:-translate-y-2">
                        <div class="bg-slate-100 border-b border-slate-200 px-4 py-3 flex items-center gap-2">
                            <div class="w-3 h-3 rounded-full bg-red-400"></div>
                            <div class="w-3 h-3 rounded-full bg-amber-400"></div>
                            <div class="w-3 h-3 rounded-full bg-green-400"></div>
                            <div class="ml-4 bg-white text-slate-400 text-[10px] font-medium px-3 py-1 rounded w-48 text-center border border-slate-200">procureflow.test</div>
                        </div>
                        <div class="h-[400px] bg-slate-50 p-8 flex flex-col gap-4">
                            <div class="h-10 bg-white rounded-lg border border-slate-200 w-full"></div>
                            <div class="flex gap-4">
                                <div class="w-64 h-64 bg-white rounded-lg border border-slate-200 shrink-0"></div>
                                <div class="flex-grow flex flex-col gap-4">
                                    <div class="h-32 bg-white rounded-lg border border-slate-200"></div>
                                    <div class="h-32 bg-white rounded-lg border border-slate-200"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <section id="tinh-nang" class="py-24 bg-white border-y border-slate-200 relative z-20">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-3xl font-black text-slate-900 tracking-tight">Hệ sinh thái tính năng</h2>
                    <p class="text-slate-500 mt-3 font-medium text-lg">Mọi công cụ bạn cần để quản trị doanh nghiệp, quy tụ tại một nền tảng.</p>
                </div>

                <div class="grid md:grid-cols-3 gap-8">
                    <div class="bg-[#F8FAFC] rounded-2xl p-8 border border-slate-100 hover:border-blue-200 transition-colors group">
                        <div class="w-12 h-12 bg-white border border-slate-200 text-blue-600 rounded-xl flex items-center justify-center mb-6 shadow-sm group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                        </div>
                        <h3 class="text-xl font-bold text-slate-900 mb-3">Quy trình Mua sắm (E-Procurement)</h3>
                        <p class="text-slate-600 leading-relaxed text-sm font-medium">Số hóa toàn bộ quy trình yêu cầu vật tư. Phê duyệt đa cấp bậc từ Trưởng phòng đến Giám đốc. Theo dõi trạng thái đơn hàng theo thời gian thực.</p>
                    </div>

                    <div class="bg-[#F8FAFC] rounded-2xl p-8 border border-slate-100 hover:border-blue-200 transition-colors group">
                        <div class="w-12 h-12 bg-white border border-slate-200 text-emerald-600 rounded-xl flex items-center justify-center mb-6 shadow-sm group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                        </div>
                        <h3 class="text-xl font-bold text-slate-900 mb-3">Quản trị Nhân sự & Nghỉ phép</h3>
                        <p class="text-slate-600 leading-relaxed text-sm font-medium">Quản lý quỹ phép năm tự động. Nhân viên tạo đơn xin nghỉ nhanh chóng, HR dễ dàng tổng hợp báo cáo ngày công cuối tháng.</p>
                    </div>

                    <div class="bg-[#F8FAFC] rounded-2xl p-8 border border-slate-100 hover:border-blue-200 transition-colors group">
                        <div class="w-12 h-12 bg-white border border-slate-200 text-purple-600 rounded-xl flex items-center justify-center mb-6 shadow-sm group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9.5a2.5 2.5 0 00-2.5-2.5H15M9 11l3 3L22 4" /></svg>
                        </div>
                        <h3 class="text-xl font-bold text-slate-900 mb-3">Bảng tin Nội bộ (Internal CMS)</h3>
                        <p class="text-slate-600 leading-relaxed text-sm font-medium">Truyền tải thông báo, sự kiện và nội quy đến toàn thể nhân viên một cách trực quan với trình soạn thảo giàu định dạng và thư viện ảnh.</p>
                    </div>
                </div>
            </div>
        </section>


    </div>
</template>

<style>
html { scroll-behavior: smooth; }
/* Tạo hiệu ứng 3D cho mockup UI */
.perspective { perspective: 1000px; }
</style>
