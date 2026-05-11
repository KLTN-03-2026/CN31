<script setup>
import { ref, watch, onMounted, nextTick, onBeforeUnmount } from 'vue';
import { Link, router, Head } from '@inertiajs/vue3';
import Chart from 'chart.js/auto';
import StatusBadge from '@/Components/UI/StatusBadge.vue';

const props = defineProps({
    roleData: Object
});

// --- HÀM FORMAT LOẠI NGHỈ PHÉP ---
const formatLoaiNghi = (loai) => {
    if (!loai) return 'Không xác định';
    const dict = {
        'viec_rieng': 'Việc riêng',
        'nghi_om': 'Nghỉ ốm',
        'phep_nam': 'Phép năm',
        'thai_san': 'Thai sản',
        'khong_luong': 'Không lương',
        'che_do': 'Nghỉ chế độ'
    };

    return dict[loai] || loai.charAt(0).toUpperCase() + loai.slice(1).replace('_', ' ');
};

// --- QUẢN LÝ TAB & BỘ LỌC ---
const activeTab = ref(props.roleData.filters?.tab || 'workspace');
const searchQuery = ref(props.roleData.filters?.search || '');
const searchTeamQuery = ref(props.roleData.filters?.search_team || '');
const selectedPhongBan = ref(props.roleData.filters?.phong_ban_id || '');

const currentYear = new Date().getFullYear();
const availableYears = [currentYear - 1, currentYear, currentYear + 1];
const selectedYear = ref(props.roleData?.chartData?.selectedYear || currentYear);
let searchTimeout = null;

const fetchData = () => {
    router.get(window.location.pathname, {
        tab: activeTab.value,
        search: activeTab.value === 'workspace' ? searchQuery.value : null,
        search_team: activeTab.value === 'quota' ? searchTeamQuery.value : null,
        phong_ban_id: activeTab.value === 'quota' ? selectedPhongBan.value : null,
        year: activeTab.value === 'analytics' ? selectedYear.value : null
    }, { preserveState: true, preserveScroll: true, only: ['roleData'] });
};

watch([searchQuery, searchTeamQuery, selectedPhongBan], () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => fetchData(), 500);
});

const formatPagination = (label) => {
    if (label.includes('Previous')) return '«';
    if (label.includes('Next')) return '»';
    return label;
};

// --- LOGIC BIỂU ĐỒ (TAB 3) ---
const hrChart = ref(null);
let chartInstance = null;

const renderChart = async () => {
    await nextTick();
    if (activeTab.value !== 'analytics' || !hrChart.value) return;
    if (chartInstance) chartInstance.destroy();

    const chartLabels = props.roleData?.chartData?.bar?.labels || [];
    const soNgayNghiData = props.roleData?.chartData?.bar?.soNgayNghi || [];

    chartInstance = new Chart(hrChart.value, {
        type: 'bar',
        data: {
            labels: chartLabels,
            datasets: [{
                label: 'Tổng số ngày nghỉ (Toàn công ty)',
                data: soNgayNghiData,
                backgroundColor: '#10b981', // Màu Emerald cho HR
                hoverBackgroundColor: '#059669',
                borderRadius: 4,
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { position: 'top', align: 'end' } },
            scales: {
                y: { beginAtZero: true, grid: { borderDash: [4, 4] } },
                x: { grid: { display: false } }
            }
        }
    });
};

watch([activeTab, () => props.roleData?.chartData], () => { renderChart(); }, { deep: true });
onMounted(() => { if (activeTab.value === 'analytics') renderChart(); });
onBeforeUnmount(() => { if (chartInstance) chartInstance.destroy(); });
</script>

<template>
    <Head title="Nhân sự - ProcureFlow"/>
    <div class="py-5 bg-slate-50 min-h-[calc(100vh-64px)]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="mb-4 border-b border-slate-200">
                <nav class="-mb-px flex space-x-6" aria-label="Tabs">
                    <button @click="activeTab = 'workspace'; fetchData()" :class="activeTab === 'workspace' ? 'border-emerald-600 text-emerald-700' : 'border-transparent text-slate-500 hover:text-slate-700'" class="whitespace-nowrap flex py-2.5 border-b-2 font-bold text-sm transition-colors">
                        Xử lý đơn từ <span v-if="roleData.stats.so_luong_cho > 0" class="ml-1.5 bg-red-100 text-red-600 py-0.5 px-1.5 rounded text-[10px]">{{ roleData.stats.so_luong_cho }}</span>
                    </button>
                    <button @click="activeTab = 'quota'; fetchData()" :class="activeTab === 'quota' ? 'border-emerald-600 text-emerald-700' : 'border-transparent text-slate-500 hover:text-slate-700'" class="whitespace-nowrap py-2.5 border-b-2 font-bold text-sm transition-colors">
                        Quản lý Quỹ phép
                    </button>
                    <button @click="activeTab = 'analytics'; fetchData()" :class="activeTab === 'analytics' ? 'border-emerald-600 text-emerald-700' : 'border-transparent text-slate-500 hover:text-slate-700'" class="whitespace-nowrap py-2.5 border-b-2 font-bold text-sm transition-colors">
                        Thống kê & Báo cáo
                    </button>
                </nav>
            </div>

            <div class="animate-fade-in">

                <div v-if="activeTab === 'workspace'" class="flex flex-col lg:flex-row gap-5 items-stretch">

                    <div class="w-full lg:w-1/3 flex flex-col">
                        <div class="mb-3 shrink-0">
                            <h2 class="font-black text-lg text-slate-900 tracking-tight">Đơn xin nghỉ phép</h2>
                            <p class="text-[11px] font-medium text-slate-500 mt-0.5 mb-2.5">Bạn có <strong class="text-emerald-600">{{ roleData.stats.so_luong_cho }}</strong> đơn cần xử lý</p>
                            <div class="relative w-full">
                                <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                                <input type="text" v-model="searchQuery" placeholder="Tìm mã đơn, tên nhân viên..." class="w-full pl-8 pr-3 py-1.5 text-xs font-medium border border-slate-300 rounded-lg focus:ring-1 focus:ring-emerald-500 shadow-sm transition-all placeholder:text-slate-400">
                            </div>
                        </div>

                        <div class="flex flex-col flex-grow pb-1">
                            <div class="space-y-2.5">
                                <Link v-for="phieu in roleData.danhSachChoDuyet.data" :key="phieu.id" :href="route('phieu.show', phieu.id)" class="block bg-white p-3.5 rounded-xl border border-slate-200 shadow-sm hover:border-emerald-400 hover:shadow transition-all group border-l-4 border-l-pink-400">
                                    <div class="flex justify-between items-start mb-2">
                                        <span class="font-mono text-[9px] font-bold text-slate-400 uppercase tracking-widest">{{ phieu.ma_phieu }}</span>
                                        <StatusBadge :label="phieu.trang_thai_label" :color="phieu.trang_thai_color" class="!px-2 !py-0.5 text-[9px] scale-90 origin-top-right" />
                                    </div>
                                    <h4 class="font-bold text-slate-800 text-[13px] mb-3 line-clamp-2 leading-snug group-hover:text-emerald-700">{{ phieu.tieu_de }}</h4>

                                    <div class="flex justify-between items-center pt-2.5 border-t border-slate-100">
                                        <div class="flex flex-col">
                                            <span class="text-[9px] font-bold text-slate-400 uppercase">{{ phieu.ten_phong_ban }}</span>
                                            <span class="text-[10px] font-semibold text-slate-600">{{ phieu.nguoi_tao }}</span>
                                        </div>
                                        <span class="text-[10px] font-medium text-slate-500">{{ phieu.ngay_tao }}</span>
                                    </div>
                                </Link>

                                <div v-if="!roleData.danhSachChoDuyet.data.length" class="bg-white p-6 rounded-xl border border-slate-200 border-dashed text-center">
                                    <div class="w-10 h-10 bg-emerald-50 rounded-full flex items-center justify-center mx-auto mb-3"><svg class="w-5 h-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg></div>
                                    <p class="text-sm font-bold text-slate-700">Tuyệt vời!</p>
                                    <p class="text-[10px] text-slate-500 mt-1">Tất cả đơn từ đã được giải quyết.</p>
                                </div>
                            </div>

                            <div class="mt-auto pt-4 flex justify-center" v-if="roleData.danhSachChoDuyet.links.length > 3">
                                <div class="flex flex-wrap justify-center gap-1">
                                    <template v-for="(link, index) in roleData.danhSachChoDuyet.links" :key="index">
                                        <Link v-if="link.url" :href="link.url" v-html="formatPagination(link.label)" class="px-2 py-0.5 rounded-md text-[11px] font-bold transition-all border" :class="link.active ? 'bg-emerald-600 text-white border-emerald-600' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-100'" />
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="w-full lg:w-7/12 flex flex-col">
                        <div class="mb-3 shrink-0">
                            <h2 class="font-black text-lg text-slate-900 tracking-tight">Bảng tin Nhân sự</h2>
                            <p class="text-[11px] font-medium text-slate-500 mt-0.5">Cập nhật tình hình quân số hôm nay</p>
                        </div>

                        <div class="flex-grow flex flex-col space-y-4 pb-1">
                            <div class="grid grid-cols-2 gap-4 shrink-0">
                                <div @click="activeTab = 'quota'; fetchData()"
                                     class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex items-center justify-between cursor-pointer hover:border-emerald-300 hover:shadow transition-all group" title="Click để xem danh sách nhân sự">
                                    <div>
                                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1 group-hover:text-emerald-600 transition-colors">Tổng nhân sự</p>
                                        <p class="text-2xl font-black text-slate-800">{{ roleData.stats.tong_nhan_su }}</p>
                                    </div>
                                    <div class="w-10 h-10 bg-blue-50 text-blue-500 rounded-full flex items-center justify-center group-hover:bg-emerald-50 group-hover:text-emerald-600 transition-colors">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                                    </div>
                                </div>
                                <div class="bg-white p-4 rounded-xl border border-rose-200 shadow-sm flex items-center justify-between ring-1 ring-rose-50">
                                    <div>
                                        <p class="text-[10px] font-bold text-rose-500 uppercase tracking-widest mb-1">Vắng mặt hôm nay</p>
                                        <p class="text-2xl font-black text-rose-600">{{ roleData.stats.so_nguoi_nghi }}</p>
                                    </div>
                                    <div class="w-10 h-10 bg-rose-50 text-rose-500 rounded-full flex items-center justify-center">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex flex-col flex-grow">
                                <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wide mb-4">Danh sách nhân sự đang nghỉ</h3>

                                <div v-if="roleData.nhanVienNghiHomNay.length > 0" class="space-y-3">
                                    <div v-for="(nv, index) in roleData.nhanVienNghiHomNay" :key="index" class="flex items-center justify-between p-3 bg-slate-50 rounded-lg border border-slate-100">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full bg-white text-slate-600 flex items-center justify-center font-bold text-xs border border-slate-200 shadow-sm">{{ nv.ten[0] }}</div>
                                            <div>
                                                <p class="text-sm font-bold text-slate-800">{{ nv.ten }}</p>
                                                <p class="text-[10px] font-medium text-slate-500">{{ nv.phong_ban }}</p>
                                            </div>
                                        </div>
                                        <span class="text-[10px] font-bold text-rose-600 bg-rose-50 px-2 py-1 rounded border border-rose-100">{{ formatLoaiNghi(nv.loai) }}</span>
                                    </div>
                                </div>
                                <div v-else class="flex-grow flex flex-col items-center justify-center text-slate-500">
                                    <p class="text-sm font-medium">Hôm nay toàn công ty đi làm đầy đủ </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="activeTab === 'quota'">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-end gap-3 mb-5">
                        <h2 class="text-xl font-black text-slate-900">Tra cứu Quỹ phép</h2>
                        <div class="flex gap-2 w-full sm:w-auto">
                            <select v-model="selectedPhongBan" @change="fetchData" class="text-xs font-bold border-slate-300 rounded-lg focus:ring-emerald-500 py-1.5 pl-3 pr-8 shadow-sm text-slate-700 w-1/3 sm:w-auto">
                                <option value="">Tất cả phòng ban</option>
                                <option v-for="pb in roleData.danhSachPhongBan" :key="pb.id" :value="pb.id">{{ pb.ten_phong_ban }}</option>
                            </select>

                            <div class="relative w-2/3 sm:w-64">
                                <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                                <input type="text" v-model="searchTeamQuery" placeholder="Tìm tên nhân viên..." class="w-full pl-8 pr-3 py-1.5 text-xs font-medium border border-slate-300 rounded-lg focus:ring-1 focus:ring-emerald-500 shadow-sm transition-all placeholder:text-slate-400">
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4">
                        <div v-for="nv in roleData.danhSachNhanSu.data" :key="nv.id" class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm hover:border-slate-300 transition-colors flex flex-col justify-between">
                            <div class="flex items-center gap-3 mb-3 pb-3 border-b border-slate-50">
                                <div class="w-8 h-8 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-xs border border-emerald-100 shrink-0">{{ nv.name[0] }}</div>
                                <div class="min-w-0">
                                    <h3 class="font-bold text-slate-900 text-sm truncate">{{ nv.name }}</h3>
                                    <p class="text-[10px] font-medium text-slate-500 truncate">{{ nv.vai_tro_label }} • {{ nv.ten_phong_ban }}</p>
                                </div>
                            </div>
                            <div>
                                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Quỹ phép năm</p>
                                <div class="flex gap-1.5 text-center">
                                    <div class="bg-slate-50 py-1.5 rounded-lg flex-1 border border-slate-100">
                                        <p class="text-[8px] text-slate-500 uppercase font-bold">Tổng</p>
                                        <p class="font-black text-slate-800 text-xs">{{ nv.tong_ngay_phep }}</p>
                                    </div>
                                    <div class="bg-slate-50 py-1.5 rounded-lg flex-1 border border-slate-100">
                                        <p class="text-[8px] text-slate-500 uppercase font-bold">Đã dùng</p>
                                        <p class="font-black text-slate-800 text-xs">{{ nv.ngay_phep_da_dung }}</p>
                                    </div>
                                    <div class="py-1.5 rounded-lg flex-1 border" :class="nv.ngay_phep_con_lai <= 2 ? 'bg-red-50 border-red-100' : 'bg-emerald-50 border-emerald-100'">
                                        <p class="text-[8px] uppercase font-bold" :class="nv.ngay_phep_con_lai <= 2 ? 'text-red-500' : 'text-emerald-600'">Còn lại</p>
                                        <p class="font-black text-xs" :class="nv.ngay_phep_con_lai <= 2 ? 'text-red-600' : 'text-emerald-700'">{{ nv.ngay_phep_con_lai }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-center" v-if="roleData.danhSachNhanSu.links.length > 3">
                        <div class="flex flex-wrap justify-center gap-1">
                            <template v-for="(link, index) in roleData.danhSachNhanSu.links" :key="index">
                                <Link v-if="link.url" :href="link.url" v-html="formatPagination(link.label)" class="px-2 py-1 rounded-md text-xs font-bold transition-all border" :class="link.active ? 'bg-emerald-600 text-white border-emerald-600 shadow-md' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'" />
                            </template>
                        </div>
                    </div>
                </div>

                <div v-if="activeTab === 'analytics'" class="space-y-6">
                    <div class="flex justify-between items-end mb-4">
                        <div>
                            <h2 class="text-xl font-black text-slate-900">Biểu đồ Nghỉ phép</h2>
                            <p class="text-xs text-slate-500 mt-1">Theo dõi tần suất nghỉ phép của nhân sự qua các tháng</p>
                        </div>
                        <select v-model="selectedYear" @change="fetchData" class="text-xs font-bold border-slate-200 rounded-lg focus:ring-emerald-500 py-1.5 pl-3 pr-8 cursor-pointer shadow-sm text-slate-700">
                            <option v-for="year in availableYears" :key="year" :value="year">Năm {{ year }}</option>
                        </select>
                    </div>

                    <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm min-h-[400px] flex flex-col">
                        <div class="relative w-full flex-grow">
                            <canvas ref="hrChart"></canvas>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</template>

<style scoped>
.animate-fade-in { animation: fadeIn 0.2s ease-out; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(3px); } to { opacity: 1; transform: translateY(0); } }
</style>
