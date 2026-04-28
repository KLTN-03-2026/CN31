<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    baiViet: {
        type: Object,
        required: true
    }
});

const getCategoryConfig = (type) => {
    const categories = {
        'tin_tuc': { label: 'Tin tức nội bộ', class: 'text-blue-700 bg-blue-50 ring-1 ring-blue-200/50' },
        'su_kien': { label: 'Sự kiện công ty', class: 'text-purple-700 bg-purple-50 ring-1 ring-purple-200/50' },
        'noi_quy': { label: 'Nội quy & Quy định', class: 'text-orange-700 bg-orange-50 ring-1 ring-orange-200/50' },
    };
    return categories[type] || { label: 'Chuyên mục khác', class: 'text-slate-700 bg-slate-50 ring-1 ring-slate-200/50' };
};

// MOCK DATA: Giả lập danh sách bài viết ở thanh bên (Sidebar)
// Ở bước sau, bạn có thể truyền biến này từ Backend (Controller) sang
const baiVietKhac = [
    { id: 1, slug: 'bai-1', tieu_de: 'Thông báo lịch nghỉ lễ 30/4', ngay_dang: '2 giờ trước', loai_bai_viet: 'tin_tuc' },
    { id: 2, slug: 'bai-2', tieu_de: 'Quy định mới về chấm công', ngay_dang: 'Hôm qua', loai_bai_viet: 'noi_quy' },
    { id: 3, slug: 'bai-3', tieu_de: 'Tổng kết Teambuilding Quý 1', ngay_dang: '3 ngày trước', loai_bai_viet: 'su_kien' },
];
</script>

<template>
    <Head :title="baiViet?.tieu_de || 'Chi tiết bài viết'" />

    <div class="min-h-[calc(100vh-64px)] bg-[#F8FAFC] py-8 px-4 sm:px-6 lg:px-8 animate-fade-in">
        <div class="max-w-6xl mx-auto">

            <div class="mb-5">
                <Link :href="route('blog.index')" class="inline-flex items-center gap-1.5 text-sm font-bold text-slate-500 hover:text-slate-900 transition-colors group">
                    <svg class="w-4 h-4 transform group-hover:-translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                    Quay lại Bảng tin
                </Link>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                <div class="lg:col-span-2">
                    <article class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 sm:p-10">
                        <header class="mb-8">
                            <div class="flex items-center gap-3 mb-4">
                                <span :class="getCategoryConfig(baiViet.loai_bai_viet).class" class="px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider">
                                    {{ getCategoryConfig(baiViet.loai_bai_viet).label }}
                                </span>
                                <span class="text-[11px] font-medium text-slate-400 flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    {{ baiViet.ngay_dang }}
                                </span>
                            </div>

                            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 leading-snug mb-6 tracking-tight">
                                {{ baiViet.tieu_de }}
                            </h1>

                            <div class="flex items-center justify-between pt-5 border-t border-slate-100">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-slate-100 text-slate-600 font-bold flex items-center justify-center text-xs border border-slate-200 shrink-0">
                                        {{ baiViet.tac_gia ? baiViet.tac_gia[0] : '?' }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-slate-900">{{ baiViet.tac_gia }}</p>
                                        <p class="text-[11px] font-medium text-slate-500">Tác giả</p>
                                    </div>
                                </div>

                                <div class="flex items-center gap-1.5 text-slate-400 shrink-0" title="Lượt xem">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                    <span class="text-xs font-semibold">{{ baiViet.luot_xem }}</span>
                                </div>
                            </div>
                        </header>

                        <div class="prose prose-slate prose-sm sm:prose-base max-w-none prose-headings:font-bold prose-headings:tracking-tight prose-a:text-blue-600 hover:prose-a:text-blue-500 prose-img:rounded-xl prose-img:border prose-img:border-slate-100" v-html="baiViet.noi_dung">
                        </div>
                    </article>

                    <footer class="mt-6 flex justify-between items-center text-[11px] font-medium text-slate-400 px-2">
                        <p>© {{ new Date().getFullYear() }} ProcureFlow.</p>
                        <p>Lưu hành nội bộ</p>
                    </footer>
                </div>

                <div class="lg:col-span-1 space-y-6">
                    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 sticky top-24">
                        <h3 class="text-sm font-black text-slate-900 uppercase tracking-wide mb-4">Mới cập nhật</h3>

                        <div class="flex flex-col space-y-4">
                            <Link v-for="tin in baiVietKhac" :key="tin.id" :href="route('blog.show', tin.slug)"
                                  class="group block border-b border-slate-50 last:border-0 pb-4 last:pb-0">
                                <div class="flex items-center justify-between mb-1.5">
                                    <span :class="getCategoryConfig(tin.loai_bai_viet).class" class="px-2 py-0.5 rounded text-[8px] font-bold uppercase tracking-wider">
                                        {{ getCategoryConfig(tin.loai_bai_viet).label }}
                                    </span>
                                    <span class="text-[10px] text-slate-400 font-medium">{{ tin.ngay_dang }}</span>
                                </div>
                                <h4 class="text-sm font-bold text-slate-800 group-hover:text-blue-600 transition-colors line-clamp-2 leading-snug">
                                    {{ tin.tieu_de }}
                                </h4>
                            </Link>
                        </div>

                        <div class="mt-4 pt-4 border-t border-slate-100 text-center">
                            <Link :href="route('blog.index')" class="text-xs font-bold text-blue-600 hover:text-blue-800 transition-colors">
                                Xem tất cả bảng tin &rarr;
                            </Link>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</template>

<style scoped>
.animate-fade-in { animation: fadeIn 0.3s ease-out; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
</style>
