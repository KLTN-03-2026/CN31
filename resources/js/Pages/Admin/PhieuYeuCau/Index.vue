<script setup>
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineOptions({ layout: AdminLayout });

const props = defineProps({
    phieuYeuCaus: Object,
    danhMucs: Array,
    filters: Object
});

const search = ref(props.filters.search || '');
const danhMucId = ref(props.filters.danh_muc_id || '');
const sortTongTien = ref(props.filters.sort_tong_tien || '');

let searchTimeout = null;

watch([search, danhMucId, sortTongTien], ([newSearch, newDanhMuc, newSort]) => {
    clearTimeout(searchTimeout);

    searchTimeout = setTimeout(() => {
        router.get(
            route('admin.phieu_yeu_cau.index'),
            {
                search: newSearch,
                danh_muc_id: newDanhMuc,
                sort_tong_tien: newSort
            },
            {
                preserveState: true, // Giữ nguyên state hiện tại của Vue
                preserveScroll: true, // Không cuộn trang lên đầu
                replace: true // Ghi đè URL history để không bị rác nút Back
            }
        );
    }, 500);
});

// --- UTILS FORMAT ---
const formatCurrency = (value) => {
    if (!value) return 'Chưa chốt giá';
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value);
};

const formatDate = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('vi-VN', { day: '2-digit', month: '2-digit', year: 'numeric' });
};
</script>

<template>
    <Head title="Quản lý Phiếu Yêu Cầu (God View) - Admin" />

    <div class="py-6 min-h-[calc(100vh-64px)] bg-transparent">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- HEADER & FILTERS -->
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-6 pb-5 border-b border-slate-200">
                <div class="w-full md:w-1/3">
                    <h2 class="font-black text-2xl text-slate-900 tracking-tight">Giám sát Phiếu Yêu Cầu</h2>
                    <p class="text-sm font-medium text-slate-500 mt-1 mb-4">God View: Toàn cảnh luồng ngân sách và mua sắm</p>

                    <!-- Search Input -->
                    <div class="relative w-full">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                        <input type="text" v-model="search" placeholder="Tìm mã phiếu, tiêu đề..."
                               class="w-full pl-9 pr-3 py-2 text-sm font-medium border border-slate-300 rounded-lg focus:ring-1 focus:ring-slate-900 focus:border-slate-900 shadow-sm transition-all placeholder:text-slate-400">
                    </div>
                </div>

                <!-- Dropdown Filters -->
                <div class="flex flex-col sm:flex-row gap-3 w-full md:w-auto">
                    <!-- Lọc theo Danh mục -->
                    <select v-model="danhMucId" class="px-3 py-2 text-sm font-medium border border-slate-300 rounded-lg focus:ring-1 focus:ring-slate-900 focus:border-slate-900 bg-white cursor-pointer shadow-sm min-w-[160px]">
                        <option value="">Tất cả danh mục</option>
                        <option v-for="dm in danhMucs" :key="dm.id" :value="dm.id">{{ dm.ten_danh_muc }}</option>
                    </select>

                    <!-- Sắp xếp theo Giá trị -->
                    <select v-model="sortTongTien" class="px-3 py-2 text-sm font-medium border border-slate-300 rounded-lg focus:ring-1 focus:ring-slate-900 focus:border-slate-900 bg-white cursor-pointer shadow-sm min-w-[160px]">
                        <option value="">Sắp xếp Mặc định</option>
                        <option value="desc">Giá trị: Cao → Thấp</option>
                        <option value="asc">Giá trị: Thấp → Cao</option>
                    </select>
                </div>
            </div>

            <!-- BẢNG DỮ LIỆU -->
            <div class="animate-fade-in bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse whitespace-nowrap">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-[10px] uppercase tracking-widest text-slate-600 font-bold">
                                <th class="px-6 py-4">Mã Phiếu & Tiêu Đề</th>
                                <th class="px-6 py-4">Người Yêu Cầu</th>
                                <th class="px-6 py-4 text-right">Tổng Tiền</th>
                                <th class="px-6 py-4 text-center">Trạng Thái</th>
                                <th class="px-6 py-4 text-right">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="phieu in phieuYeuCaus.data" :key="phieu.id" class="hover:bg-slate-50 transition-colors group">

                                <!-- Cột 1: Thông tin cơ bản -->
                                <td class="px-6 py-4">
                                    <div class="flex flex-col gap-1">
                                        <span class="text-sm font-black text-slate-900">{{ phieu.ma_phieu }}</span>
                                        <span class="text-[12px] font-medium text-slate-600 truncate max-w-[200px]" :title="phieu.tieu_de">{{ phieu.tieu_de }}</span>
                                        <span class="text-[10px] text-slate-400 font-medium">{{ formatDate(phieu.created_at) }}</span>
                                    </div>
                                </td>

                                <!-- Cột 2: Nguồn gốc -->
                                <td class="px-6 py-4">
                                    <div class="flex flex-col gap-1">
                                        <span class="text-sm font-bold text-slate-700">{{ phieu.nguoi_tao?.name || 'Không xác định' }}</span>
                                        <span class="inline-flex px-1 py-0.5 rounded text-[10px]  uppercase tracking-wider bg-slate-100 text-slate-700 border border-slate-200">
                                            {{ phieu.nguoi_tao?.phong_ban?.ten_phong_ban || 'Chưa xếp phòng' }}
                                        </span>
                                    </div>
                                </td>

                                <!-- Cột 3: Giá trị -->
                                <td class="px-10 py-4 text-right">
                                    <span :class="phieu.tong_tien ? 'text-[13px] font-black text-emerald-600 ' : 'text-[13px] font-bold text-yellow-600'">
                                        {{ formatCurrency(phieu.tong_tien) }}
                                    </span>
                                </td>

                                <td class="px-6 py-4 text-center">

                                    <span class="inline-flex px-1 py-0.5 rounded text-[10px]  uppercase tracking-wider bg-slate-100 text-slate-700 border border-slate-200">
                                        {{ phieu.trang_thai }}
                                    </span>
                                </td>

                                <!-- Cột 5: Hành động (Chỉ xem) -->
                                <td class="px-6 py-4 text-right">
                                    <!-- Dùng route phieu.show hiện có của bạn để Admin vào xem chi tiết nhưng không có nút duyệt -->
                                    <Link :href="route('phieu.show', phieu.id)"
                                          class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 hover:text-blue-600 rounded-lg text-[12px] font-bold transition-all shadow-sm">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                        Chi tiết
                                    </Link>
                                </td>
                            </tr>

                            <!-- Empty State -->
                            <tr v-if="!phieuYeuCaus.data.length">
                                <td colspan="5" class="px-6 py-12 text-center flex flex-col items-center justify-center">
                                    <svg class="w-10 h-10 text-slate-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>
                                    <p class="text-sm font-bold text-slate-700">Không tìm thấy phiếu yêu cầu</p>
                                    <p class="text-xs text-slate-500 mt-1">Hệ thống chưa có dữ liệu hoặc không khớp bộ lọc.</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- PHÂN TRANG (PAGINATION) TƯƠNG TỰ TRANG USER -->
                <div class="border-t border-slate-100 bg-white p-4 flex justify-between items-center" v-if="phieuYeuCaus.links.length > 3">
                    <p class="text-xs font-medium text-slate-500 hidden sm:block">
                        Hiển thị <span class="font-bold text-slate-800">{{ phieuYeuCaus.from || 0 }}</span> đến <span class="font-bold text-slate-800">{{ phieuYeuCaus.to || 0 }}</span> trong tổng <span class="font-bold text-slate-800">{{ phieuYeuCaus.total }}</span> phiếu
                    </p>
                    <div class="flex flex-wrap gap-1">
                        <template v-for="(link, index) in phieuYeuCaus.links" :key="index">
                            <Link v-if="link.url" :href="link.url" v-html="link.label.replace('Previous', '«').replace('Next', '»')"
                                  class="px-2.5 py-1.5 rounded-md text-xs font-bold transition-all border"
                                  :class="link.active ? 'bg-slate-900 text-white border-slate-900 shadow-sm' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'" />
                            <span v-else v-html="link.label.replace('Previous', '«').replace('Next', '»')" class="px-2.5 py-1.5 rounded-md text-xs font-bold text-slate-400 cursor-not-allowed border border-transparent"></span>
                        </template>
                    </div>
                </div>
            </div>

        </div>
    </div>
</template>

<style scoped>
.animate-fade-in { animation: fadeIn 0.2s ease-out; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }
</style>
