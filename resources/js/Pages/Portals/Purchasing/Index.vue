<script setup>
import { onMounted, onBeforeUnmount, ref, watch } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import Chart from 'chart.js/auto';
import StatusBadge from '@/Components/UI/StatusBadge.vue';

const props = defineProps({
    roleData: {
        type: Object,
        default: () => ({ stats: {}, danhSachChoBaoGia: { data: [] }, danhSachTheoDoi: { data: [] }, chartData: {}, filters: {}, trangThais: [] })
    }
});

// --- LỌC NĂM ---
const currentYear = new Date().getFullYear();
const availableYears = [currentYear - 1, currentYear, currentYear + 1];
const selectedYear = ref(props.roleData?.chartData?.monthly?.year || currentYear);

// --- TABS & TÌM KIẾM ---
const urlParams = new URLSearchParams(window.location.search);
const activeTab = ref(urlParams.get('tab') || 'cho_bao_gia');
const searchQuery = ref(props.roleData?.filters?.search || '');
const statusFilter = ref(props.roleData?.filters?.status || 'all');

let searchTimeout = null;

// Hàm gọi API khi đổi Tab hoặc Tìm kiếm
const fetchData = () => {
    router.get(route('purchasing.index'), {
        tab: activeTab.value,
        search: searchQuery.value,
        status: activeTab.value === 'theo_doi' ? statusFilter.value : null,
    }, {
        preserveState: true, preserveScroll: true, only: ['roleData']
    });
};

// Chờ gõ xong mới search (Tránh spam server)
watch(searchQuery, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => fetchData(), 500);
});

// Hàm gỡ chữ "Previous" và "Next" quá dài của Laravel để tránh rớt dòng
const formatPagination = (label) => {
    if (label.includes('Previous')) return '«';
    if (label.includes('Next')) return '»';
    return label;
};

// --- BIỂU ĐỒ TRÒN ---
const supplierChart = ref(null);
let chartInstance = null;

const renderChart = () => {
    if (!supplierChart.value) return;
    if (chartInstance) chartInstance.destroy();

    chartInstance = new Chart(supplierChart.value, {
        type: 'doughnut',
        data: {
            labels: props.roleData?.chartData?.labels || [],
            datasets: [{
                data: props.roleData?.chartData?.data || [],
                backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#64748b'],
                borderWidth: 0, hoverOffset: 4
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false, cutout: '70%',
            plugins: {
                legend: { position: 'right', labels: { boxWidth: 12, font: { size: 12 } } },
                tooltip: { callbacks: { label: (ctx) => ` ${ctx.label}: ${ctx.parsed}%` } }
            }
        }
    });
};

onMounted(() => renderChart());
onBeforeUnmount(() => { if (chartInstance) chartInstance.destroy(); });
</script>

<template>
    <Head title="Purchasing"/>
    <div class="py-6 animate-fade-in bg-slate-50/50 min-h-[calc(100vh-64px)]">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex flex-col lg:flex-row gap-6">

                <div class="w-full lg:w-1/3 flex flex-col h-full lg:h-[calc(100vh-120px)]">

                    <div class="mb-3 shrink-0 bg-slate-200/60 p-1.5 rounded-xl flex">
                        <button @click="activeTab = 'cho_bao_gia'; fetchData()"
                                :class="activeTab === 'cho_bao_gia' ? 'bg-white text-purple-700 shadow-sm' : 'text-slate-500 hover:text-slate-700'"
                                class="flex-1 py-1.5 text-[13px] font-bold rounded-lg transition-all flex justify-center items-center gap-1.5">
                            Cần chốt
                            <span v-if="activeTab === 'cho_bao_gia'" class="bg-purple-100 text-purple-600 px-1.5 py-0.5 rounded text-[10px] leading-none">{{ roleData?.stats?.cho_bao_gia || 0 }}</span>
                        </button>
                        <button @click="activeTab = 'theo_doi'; fetchData()"
                                :class="activeTab === 'theo_doi' ? 'bg-white text-blue-700 shadow-sm' : 'text-slate-500 hover:text-slate-700'"
                                class="flex-1 py-1.5 text-[13px] font-bold rounded-lg transition-all flex justify-center items-center gap-1.5">
                            Đang theo dõi
                            <span v-if="activeTab === 'theo_doi'" class="bg-blue-100 text-blue-600 px-1.5 py-0.5 rounded text-[10px] leading-none">{{ roleData?.stats?.da_xu_ly_thang || 0 }}</span>
                        </button>
                    </div>

                    <div class="mb-3 shrink-0 flex gap-2">
                        <input type="text" v-model="searchQuery" placeholder="Tìm mã phiếu, tiêu đề..." class="flex-1 text-[13px] border-slate-200 rounded-lg focus:ring-purple-500 focus:border-purple-500 py-2 shadow-sm">
                        <select v-if="activeTab === 'theo_doi'" v-model="statusFilter" @change="fetchData" class="text-[13px] border-slate-200 rounded-lg focus:ring-blue-500 focus:border-blue-500 py-2 shadow-sm w-28">
                            <option value="all">Tất cả TT</option>
                            <option v-for="st in roleData.trangThais" :key="st.value" :value="st.value">{{ st.label }}</option>
                        </select>
                    </div>

                    <div class="flex-grow overflow-y-auto space-y-3 custom-scrollbar pr-1 pb-4">

                        <template v-if="activeTab === 'cho_bao_gia'">
                            <Link v-for="phieu in roleData?.danhSachChoBaoGia?.data" :key="phieu.id" :href="route('phieu.show', phieu.id)" class="block bg-white p-4 rounded-xl border border-slate-200 shadow-sm hover:border-purple-400 hover:shadow-md transition-all group relative overflow-hidden">
                                <div class="absolute left-0 top-0 bottom-0 w-1 bg-purple-500 rounded-l-xl opacity-0 group-hover:opacity-100 transition-opacity"></div>
                                <div class="flex justify-between items-start mb-2 pl-1">
                                    <span class="font-mono text-[10px] font-bold text-slate-400 uppercase tracking-widest group-hover:text-purple-600 transition-colors">{{ phieu.ma_phieu }}</span>
                                    <StatusBadge :label="phieu.trang_thai_label" :color="phieu.trang_thai_color" class="!px-2 !py-0.5 scale-90 origin-top-right" />
                                </div>
                                <h4 class="font-bold text-slate-800 text-sm mb-3 line-clamp-2 leading-snug group-hover:text-purple-700 transition-colors pl-1" :title="phieu.tieu_de">{{ phieu.tieu_de }}</h4>
                                <div class="flex justify-between items-center pt-3 border-t border-slate-100 pl-1">
                                    <div class="flex items-center gap-2">
                                        <div class="w-5 h-5 rounded-full bg-slate-100 flex items-center justify-center text-[9px] font-bold text-slate-600 uppercase border border-slate-200">{{ phieu.nguoi_tao ? phieu.nguoi_tao[0] : '?' }}</div>
                                        <span class="text-[11px] font-semibold text-slate-600 truncate max-w-[100px]">{{ phieu.nguoi_tao }}</span>
                                    </div>
                                    <span class="text-[10px] text-slate-400 font-medium">{{ phieu.ngay_tao }}</span>
                                </div>
                            </Link>

                            <div v-if="!roleData?.danhSachChoBaoGia?.data?.length" class="bg-white p-8 rounded-xl border border-slate-200 border-dashed text-center">
                                <p class="text-sm font-bold text-slate-700">Trống</p>
                                <p class="text-[11px] text-slate-500 mt-1">Không tìm thấy yêu cầu nào.</p>
                            </div>

                            <div class="pt-2 flex justify-center" v-if="roleData?.danhSachChoBaoGia?.links?.length > 3">
                                <div class="flex justify-center gap-1">
                                    <template v-for="(link, index) in roleData.danhSachChoBaoGia.links" :key="index">
                                        <Link v-if="link.url" :href="link.url" v-html="formatPagination(link.label)" class="px-2 py-1 rounded-md text-[11px] font-bold transition-all" :class="link.active ? 'bg-slate-800 text-white shadow' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'" />
                                        <span v-else v-html="formatPagination(link.label)" class="px-2 py-1 rounded-md text-[11px] font-bold text-slate-300 bg-transparent cursor-not-allowed" />
                                    </template>
                                </div>
                            </div>
                        </template>

                        <template v-if="activeTab === 'theo_doi'">
                            <Link v-for="phieu in roleData?.danhSachTheoDoi?.data" :key="'td-'+phieu.id" :href="route('phieu.show', phieu.id)" class="block bg-white p-4 rounded-xl border border-slate-200 shadow-sm hover:border-blue-400 hover:shadow-md transition-all group relative overflow-hidden">
                                <div class="absolute left-0 top-0 bottom-0 w-1 bg-blue-500 rounded-l-xl opacity-0 group-hover:opacity-100 transition-opacity"></div>
                                <div class="flex justify-between items-start mb-2 pl-1">
                                    <span class="font-mono text-[10px] font-bold text-slate-400 uppercase tracking-widest group-hover:text-blue-600 transition-colors">{{ phieu.ma_phieu }}</span>
                                    <StatusBadge :label="phieu.trang_thai_label" :color="phieu.trang_thai_color" class="!px-2 !py-0.5 scale-90 origin-top-right" />
                                </div>
                                <h4 class="font-bold text-slate-800 text-sm mb-3 line-clamp-2 leading-snug group-hover:text-blue-700 transition-colors pl-1" :title="phieu.tieu_de">{{ phieu.tieu_de }}</h4>
                                <div class="flex justify-between items-center pt-3 border-t border-slate-100 pl-1">
                                    <span class="text-[10px] text-slate-400 font-medium">Cập nhật:</span>
                                    <span class="text-[10px] font-bold text-slate-600">{{ phieu.ngay_tao }}</span>
                                </div>
                            </Link>

                            <div v-if="!roleData?.danhSachTheoDoi?.data?.length" class="bg-white p-8 rounded-xl border border-slate-200 border-dashed text-center">
                                <p class="text-sm font-bold text-slate-700">Trống</p>
                                <p class="text-[11px] text-slate-500 mt-1">Không tìm thấy dữ liệu.</p>
                            </div>

                            <div class="pt-2 flex justify-center" v-if="roleData?.danhSachTheoDoi?.links?.length > 3">
                                <div class="flex justify-center gap-1">
                                    <template v-for="(link, index) in roleData.danhSachTheoDoi.links" :key="index">
                                        <Link v-if="link.url" :href="link.url" v-html="formatPagination(link.label)" class="px-2 py-1 rounded-md text-[11px] font-bold transition-all" :class="link.active ? 'bg-blue-600 text-white shadow' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'" />
                                        <span v-else v-html="formatPagination(link.label)" class="px-2 py-1 rounded-md text-[11px] font-bold text-slate-300 bg-transparent cursor-not-allowed" />
                                    </template>
                                </div>
                            </div>
                        </template>

                    </div>
                </div>

                <div class="w-full lg:w-2/3 flex flex-col h-full lg:h-[calc(100vh-120px)]">
                    <div class="mb-4 shrink-0 flex justify-between items-end">
                        <div>
                            <h2 class="font-black text-xl text-slate-900 tracking-tight">Tổng quan Mua sắm</h2>
                            <p class="text-xs font-medium text-slate-500 mt-1">Hiệu suất xử lý và quản lý nhà cung cấp</p>
                        </div>
                    </div>

                    <div class="flex-grow flex flex-col space-y-5 overflow-y-auto custom-scrollbar pr-1 pb-4">
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 shrink-0">
                            <div @click="activeTab = 'theo_doi'; fetchData()" class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm cursor-pointer hover:border-blue-300 transition-colors">
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Đã chốt giá tháng này</p>
                                <div class="flex items-end gap-2">
                                    <h3 class="text-3xl font-black text-slate-800">{{ roleData?.stats?.da_xu_ly_thang || 0 }}</h3>
                                    <span class="text-xs font-semibold text-emerald-500 mb-1.5 flex items-center"><svg class="w-3 h-3 mr-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" /></svg> Tốt</span>
                                </div>
                            </div>

                            <div @click="activeTab = 'cho_bao_gia'; fetchData()" class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm ring-1 ring-yellow-50 cursor-pointer hover:border-yellow-300 transition-colors">
                                <p class="text-[10px] font-bold text-yellow-500 uppercase tracking-widest mb-2">Đang chờ xử lý</p>
                                <h3 class="text-3xl font-black text-yellow-600">{{ roleData?.stats?.cho_bao_gia || 0 }}</h3>
                            </div>

                            <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Nhà Cung Cấp Đang Hợp Tác</p>
                                <h3 class="text-3xl font-black text-slate-800">{{ roleData?.stats?.tong_nha_cung_cap || 0 }}</h3>
                            </div>
                        </div>

                        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm flex flex-col flex-grow min-h-[300px]">
                            <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wide mb-6 shrink-0">Tỷ trọng chi tiêu theo Nhà cung cấp</h3>
                            <div class="relative w-full flex-grow flex items-center justify-center">
                                <div class="w-full max-w-md h-full min-h-[250px] relative">
                                    <canvas ref="supplierChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</template>
