<!-- KHỐI 1: XỬ LÝ LAYOUT ĐỘNG (Dynamic Layout) -->
<script>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Layout from '@/Layouts/Layout.vue'; // Layout mặc định cho Nhân sự

export default {
    layout: (h, page) => {
        // Lấy cờ isAdmin từ Middleware HandleInertiaRequests
        const isAdmin = page.props.auth.user?.isAdmin;
        // Nếu là Admin thì dùng AdminLayout, ngược lại dùng Layout mặc định
        return h(isAdmin ? AdminLayout : Layout, () => h(page));
    }
}
</script>

<!-- KHỐI 2: LOGIC COMPONENT BÌNH THƯỜNG -->
<script setup>
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    baiViets: Object,
    filters: Object,
});

const search = ref(props.filters?.search || '');
let searchTimeout = null;

watch(search, (newValue) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get(
            route('admin.blog.manage'),
            { search: newValue },
            { preserveState: true, preserveScroll: true, replace: true }
        );
    }, 500);
});

const handleTrash = (id, title) => {
    if (confirm(`Bạn có chắc chắn muốn CHUYỂN VÀO LƯU TRỮ bài viết "${title}"?\nBài viết sẽ bị ẩn khỏi bảng tin của nhân viên.`)) {
        router.delete(route('admin.blog.destroy', id), {
            preserveScroll: true,
        });
    }
};

const handleRestore = (id, title) => {
    if (confirm(`Bạn có muốn KHÔI PHỤC bài viết "${title}"?\nBài viết sẽ xuất hiện lại trên bảng tin.`)) {
        router.post(route('admin.blog.restore', id), {}, {
            preserveScroll: true,
        });
    }
};

// --- UTILS ---
const getCategoryLabel = (type) => {
    const dict = { 'tin_tuc': 'Tin tức', 'su_kien': 'Sự kiện', 'noi_quy': 'Nội quy' };
    return dict[type] || 'Khác';
};
</script>

<template>
    <Head title="Quản trị Bảng tin nội bộ" />

    <div class="min-h-[calc(100vh-64px)] bg-[#F8FAFC] py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- HEADER & ACTIONS -->
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-6 pb-5 border-b border-slate-200">
                <div class="w-full md:w-1/2">
                    <div class="flex items-center gap-3 mb-1">
                        <Link :href="route('blog.index')" class="text-sm font-bold text-slate-500 hover:text-slate-900 transition-colors flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                            Về Bảng tin
                        </Link>
                    </div>
                    <h1 class="text-2xl font-black text-slate-900 tracking-tight">Quản trị nội dung (CMS)</h1>
                    <p class="text-sm font-medium text-slate-500 mt-1 mb-4">Kiểm soát, lưu trữ và khôi phục các bài viết trên hệ thống.</p>

                    <!-- Search Input -->
                    <div class="relative w-full sm:w-96">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                        <input type="text" v-model="search" placeholder="Tìm kiếm tiêu đề bài viết..."
                               class="w-full pl-9 pr-3 py-2 text-sm font-medium border border-slate-300 rounded-lg focus:ring-1 focus:ring-slate-900 focus:border-slate-900 shadow-sm transition-all placeholder:text-slate-400">
                    </div>
                </div>

                <div class="shrink-0">
                    <Link :href="route('admin.blog.create')"
                        class="inline-flex items-center gap-2 px-4 py-2.5 bg-slate-900 text-white rounded-lg text-sm font-bold hover:bg-slate-800 transition-colors shadow-sm">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                        Viết bài mới
                    </Link>
                </div>
            </div>

            <!-- BẢNG DỮ LIỆU -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full text-left border-collapse whitespace-nowrap">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-[11px] uppercase tracking-wider text-slate-600 font-bold">
                                <th class="px-6 py-4">Bài viết</th>
                                <th class="px-6 py-4">Chuyên mục</th>
                                <th class="px-6 py-4 text-center">Tương tác</th>
                                <th class="px-6 py-4 text-center">Trạng thái</th>
                                <th class="px-6 py-4 text-right">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <!-- Dùng class có điều kiện để làm mờ dòng nếu bị xóa mềm -->
                            <tr v-for="bai in baiViets?.data" :key="bai.id"
                                class="transition-colors hover:bg-slate-50"
                                :class="{ 'opacity-60 bg-slate-50/50': bai.is_deleted }">

                                <td class="px-6 py-4">
                                    <div class="flex flex-col gap-1 max-w-md">
                                        <!-- Tiêu đề có gạch ngang nếu đã bị xóa -->
                                        <span class="text-sm font-bold text-slate-900 truncate" :class="{ 'line-through text-slate-500': bai.is_deleted }" :title="bai.tieu_de">
                                            {{ bai.tieu_de }}
                                        </span>
                                        <div class="flex items-center gap-2 text-[11px] font-medium text-slate-500">
                                            <span>{{ bai.tac_gia }}</span>
                                            <span>•</span>
                                            <span>{{ bai.ngay_dang }}</span>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-6 py-4">
                                    <span class="px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider bg-slate-100 text-slate-600 border border-slate-200">
                                        {{ getCategoryLabel(bai.loai_bai_viet) }}
                                    </span>
                                </td>

                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center gap-1 text-xs font-bold text-slate-600">
                                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                        {{ bai.luot_xem }}
                                    </span>
                                </td>

                                <td class="px-6 py-4 text-center">
                                    <span v-if="!bai.is_deleted" class="inline-flex px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider bg-emerald-100 text-emerald-700 border border-emerald-200">
                                        Đang xuất bản
                                    </span>
                                    <span v-else class="inline-flex px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider bg-rose-100 text-rose-700 border border-rose-200">
                                        Đã lưu trữ
                                    </span>
                                </td>

                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <!-- NẾU BÀI CHƯA XÓA: Hiện nút Xóa (Lưu trữ) -->
                                        <button v-if="!bai.is_deleted" @click="handleTrash(bai.id, bai.tieu_de)"
                                            class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors" title="Chuyển vào lưu trữ">
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                        </button>

                                        <!-- NẾU BÀI ĐÃ XÓA: Hiện nút Khôi phục -->
                                        <button v-else @click="handleRestore(bai.id, bai.tieu_de)"
                                            class="p-1.5 text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors" title="Khôi phục bài viết">
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <!-- Empty state -->
                            <tr v-if="!baiViets?.data?.length">
                                <td colspan="5" class="px-6 py-12 text-center text-slate-500">
                                    <p class="text-sm font-medium">Không tìm thấy bài viết nào.</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Phân trang (Tương tự như các trang khác) -->
                <div class="border-t border-slate-100 bg-white p-4 flex justify-between items-center" v-if="baiViets && baiViets.total > baiViets.per_page">
                    <p class="text-xs font-medium text-slate-500 hidden sm:block">
                        Hiển thị <span class="font-bold text-slate-900">{{ baiViets.from || 0 }}</span> - <span class="font-bold text-slate-900">{{ baiViets.to || 0 }}</span> / <span class="font-bold text-slate-900">{{ baiViets.total }}</span>
                    </p>
                    <div class="flex flex-wrap gap-1">
                        <template v-for="(link, index) in baiViets.links" :key="index">

                            <!-- Nút có thể click -->
                            <Link v-if="link.url"
                                  :href="link.url"
                                  :preserve-scroll="true"
                                  v-html="link.label.replace('Previous', '«').replace('Next', '»')"
                                  class="px-3 py-1.5 rounded-md text-xs font-bold transition-all border shadow-sm"
                                  :class="link.active
                                    ? 'bg-slate-900 text-white border-slate-900'
                                    : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50 hover:text-slate-900'"
                            />

                            <!-- Nút bị vô hiệu hóa (Disable) -->
                            <span v-else
                                  v-html="link.label.replace('Previous', '«').replace('Next', '»')"
                                  class="px-3 py-1.5 rounded-md text-xs font-bold text-slate-300 cursor-not-allowed border border-transparent">
                            </span>

                        </template>
                    </div>
                </div>
            </div>

        </div>
    </div>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { height: 6px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background-color: #cbd5e1; border-radius: 10px; }
</style>
