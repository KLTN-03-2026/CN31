<script setup>
import { ref, watch, onMounted, nextTick, onBeforeUnmount } from 'vue';
import { Link, router, Head } from '@inertiajs/vue3';
import Chart from 'chart.js/auto';
import StatusBadge from '@/Components/UI/StatusBadge.vue';

const props = defineProps({
    roleData: Object
});

const formatCurrency = (value) => {
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value || 0);
};

// --- TABS & SEARCH ---
const activeTab = ref(props.roleData.filters?.tab || 'approvals');
const searchQuery = ref(props.roleData.filters?.search || '');
let searchTimeout = null;

// --- YEAR FILTER ---
const currentYear = new Date().getFullYear();
const currentMonth = new Date().getMonth();
const availableYears = [currentYear - 2, currentYear - 1, currentYear, currentYear + 1, currentYear + 2];
const selectedYear = ref(props.roleData?.chartData?.selectedYear || currentYear);

const fetchData = () => {
    router.get(route('director.approvals'), {
        tab: activeTab.value,
        search: activeTab.value === 'approvals' ? searchQuery.value : null,
        year: activeTab.value === 'approvals' ? selectedYear.value : null
    }, { preserveState: true, preserveScroll: true, only: ['roleData'] });
};

watch(searchQuery, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => fetchData(), 500);
});

const changeYear = () => { fetchData(); };
const formatPagination = (label) => {
    if (label.includes('Previous')) return '«';
    if (label.includes('Next')) return '»';
    return label;
};

// --- CHART.JS ---
const financeChart = ref(null);
let chartInstance = null;
const activeMonthLabel = ref(`Tháng ${currentMonth + 1}`);
const activeMonthValue = ref(0);

const renderChart = async () => {
    await nextTick();
    if (activeTab.value !== 'approvals' || !financeChart.value) return;
    if (chartInstance) chartInstance.destroy();

    const chartLabels = props.roleData?.chartData?.bar?.labels || [];
    const thucChiData = props.roleData?.chartData?.bar?.thucChi || [];
    const nganSachData = props.roleData?.chartData?.bar?.nganSach || [];

    const monthIndex = parseInt(activeMonthLabel.value.replace('Tháng ', '')) - 1;
    activeMonthValue.value = thucChiData[monthIndex] || 0;

    chartInstance = new Chart(financeChart.value, {
        type: 'bar',
        data: {
            labels: chartLabels,
            datasets: [
                { label: 'Thực chi (VNĐ)', data: thucChiData, backgroundColor: '#3b82f6', borderRadius: 4 },
                { label: 'Ngân sách cấp (VNĐ)', data: nganSachData, type: 'line', borderColor: '#f59e0b', borderWidth: 2, fill: false, pointRadius: 0 }
            ]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            onClick: (event, elements) => {
                if (elements.length > 0) {
                    const dataIndex = elements[0].index;
                    activeMonthLabel.value = `Tháng ${dataIndex + 1}`;
                    activeMonthValue.value = thucChiData[dataIndex];
                }
            },
            plugins: {
                legend: { position: 'top', align: 'end', labels: { boxWidth: 10, font: { size: 10 } } },
                tooltip: { callbacks: { label: function(context) { return context.dataset.label + ': ' + new Intl.NumberFormat('vi-VN').format(context.parsed.y) + ' đ'; } } }
            },
            scales: {
                y: { beginAtZero: true, grid: { borderDash: [4, 4] }, ticks: { font: { size: 10 }, callback: function(value) { if (value >= 1000000000) return (value / 1000000000) + ' Tỷ'; if (value >= 1000000) return (value / 1000000) + ' Tr'; return value; } } },
                x: { grid: { display: false }, ticks: { font: { size: 10 } } }
            }
        }
    });
};

watch([activeTab, () => props.roleData?.chartData], () => { renderChart(); }, { deep: true });
onMounted(() => { if (activeTab.value === 'approvals') renderChart(); });
onBeforeUnmount(() => { if (chartInstance) chartInstance.destroy(); });
</script>

<template>
    <Head title="Giám đốc - ProcureFlow"/>
    <div class="py-5 bg-slate-50 min-h-[calc(100vh-64px)]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="mb-4 border-b border-slate-200">
                <nav class="-mb-px flex space-x-6" aria-label="Tabs">
                    <button @click="activeTab = 'approvals'; fetchData()" :class="activeTab === 'approvals' ? 'border-slate-900 text-slate-900' : 'border-transparent text-slate-500 hover:text-slate-700'" class="whitespace-nowrap flex py-2.5 border-b-2 font-bold text-sm transition-colors">
                        Phê duyệt cấp cao <span v-if="roleData.stats.so_luong_cho > 0" class="ml-1.5 bg-red-100 text-red-600 py-0.5 px-1.5 rounded text-[10px]">{{ roleData.stats.so_luong_cho }}</span>
                    </button>
                    <button @click="activeTab = 'radar'; fetchData()" :class="activeTab === 'radar' ? 'border-slate-900 text-slate-900' : 'border-transparent text-slate-500 hover:text-slate-700'" class="whitespace-nowrap py-2.5 border-b-2 font-bold text-sm transition-colors">
                        Radar Ngân sách
                    </button>
                </nav>
            </div>

            <div class="animate-fade-in">

                <div v-if="activeTab === 'approvals'" class="flex flex-col lg:flex-row gap-5 items-stretch">

                    <div class="w-full lg:w-1/3 flex flex-col">
                        <div class="mb-3 shrink-0">
                            <h2 class="font-black text-lg text-slate-900 tracking-tight">Trình ký Giám đốc</h2>
                            <p class="text-[11px] font-medium text-slate-500 mt-0.5 mb-2.5">Các yêu cầu mua sắm giá trị lớn</p>
                            <div class="relative w-full">
                                <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                                <input type="text" v-model="searchQuery" placeholder="Tìm mã phiếu..." class="w-full pl-8 pr-3 py-1.5 text-xs font-medium border border-slate-300 rounded-lg focus:ring-1 focus:ring-slate-900 shadow-sm transition-all placeholder:text-slate-400">
                            </div>
                        </div>

                        <div class="flex flex-col flex-grow pb-1">
                            <div class="space-y-2.5">
                                <Link v-for="phieu in roleData.danhSachChoDuyet.data" :key="phieu.id" :href="route('phieu.show', phieu.id)" class="block bg-white p-3.5 rounded-xl border border-slate-200 shadow-sm hover:border-blue-400 hover:shadow transition-all group border-l-4 border-l-orange-400">
                                    <div class="flex justify-between items-start mb-1.5">
                                        <span class="font-mono text-[9px] font-bold text-slate-400 uppercase tracking-widest">{{ phieu.ma_phieu }}</span>
                                        <StatusBadge :label="phieu.trang_thai_label" :color="phieu.trang_thai_color" class="!px-2 !py-0.5 text-[9px] scale-90 origin-top-right" />
                                    </div>
                                    <h4 class="font-bold text-slate-800 text-[13px] mb-1.5 line-clamp-2 leading-snug group-hover:text-blue-700">{{ phieu.tieu_de }}</h4>
                                    <p class="text-xs font-black text-slate-900 tabular-nums mb-3">{{ phieu.tong_tien }}</p>

                                    <div class="flex justify-between items-center pt-2.5 border-t border-slate-100">
                                        <div class="flex flex-col">
                                            <span class="text-[9px] font-bold text-slate-400 uppercase">{{ phieu.ten_phong_ban }}</span>
                                            <span class="text-[10px] font-semibold text-slate-600">{{ phieu.nguoi_tao }}</span>
                                        </div>
                                        <span class="text-[10px] font-medium text-slate-500">{{ phieu.ngay_tao }}</span>
                                    </div>
                                </Link>

                                <div v-if="!roleData.danhSachChoDuyet.data.length" class="bg-white p-6 rounded-xl border border-slate-200 border-dashed text-center">
                                    <p class="text-sm font-bold text-slate-700">Chưa có tờ trình</p>
                                </div>
                            </div>

                            <div class="mt-auto pt-4 flex justify-center" v-if="roleData.danhSachChoDuyet.links.length > 3">
                                <div class="flex flex-wrap justify-center gap-1">
                                    <template v-for="(link, index) in roleData.danhSachChoDuyet.links" :key="index">
                                        <Link v-if="link.url" :href="link.url" v-html="formatPagination(link.label)" class="px-2 py-0.5 rounded-md text-[11px] font-bold transition-all border" :class="link.active ? 'bg-slate-800 text-white border-slate-800' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-100'" />
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="w-full lg:w-7/12 flex flex-col">
                        <div class="mb-3 shrink-0 flex justify-between items-end">
                            <div>
                                <h2 class="font-black text-lg text-slate-900 tracking-tight">Ngân sách Toàn công ty</h2>
                                <p class="text-[11px] font-medium text-slate-500 mt-0.5">Dòng tiền thực tế so với định mức</p>
                            </div>
                            <select v-model="selectedYear" @change="changeYear" class="text-xs font-bold border-slate-200 rounded-lg focus:ring-blue-500 py-1 pl-2.5 pr-7 cursor-pointer shadow-sm text-slate-700">
                                <option v-for="year in availableYears" :key="year" :value="year">Năm {{ year }}</option>
                            </select>
                        </div>

                        <div class="flex-grow flex flex-col space-y-4 pb-1">
                            <div class="bg-slate-900 p-5 rounded-xl shadow-lg relative overflow-hidden shrink-0 text-white">
                                <div class="absolute right-0 top-0 w-32 h-32 bg-white/5 rounded-full -mr-10 -mt-10 blur-xl"></div>
                                <div class="flex justify-between items-start mb-4 relative z-10">
                                    <div>
                                        <p class="text-[16px] font-bold text-slate-400 uppercase tracking-widest mb-1">Tổng hạn mức công ty</p>
                                        <h3 class="text-3xl font-black">{{ formatCurrency(roleData.stats.ngan_sach_tong) }}</h3>
                                    </div>
                                </div>
                                <div class="mb-3 relative z-10">
                                    <div class="flex justify-between items-end mb-1">
                                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Tiến độ giải ngân</span>
                                        <span class="text-sm font-black tabular-nums" :class="roleData.stats.phan_tram > 90 ? 'text-red-400' : 'text-white'">{{ roleData.stats.phan_tram }}%</span>
                                    </div>
                                    <div class="w-full h-1.5 bg-slate-700 rounded-full overflow-hidden">
                                        <div class="h-full rounded-full transition-all duration-1000" :style="{ width: roleData.stats.phan_tram + '%' }" :class="roleData.stats.phan_tram > 90 ? 'bg-red-500' : 'bg-blue-500'"></div>
                                    </div>
                                </div>
                                <div class="flex justify-between border-t border-slate-700 pt-3 relative z-10">
                                    <div>
                                        <p class="text-[10px] font-medium text-slate-400 mb-0.5">Đã giải ngân</p>
                                        <p class="text-sm font-bold tabular-nums">{{ formatCurrency(roleData.stats.da_chi) }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-[10px] font-bold text-slate-400 mb-0.5">Dư địa còn lại</p>
                                        <p class="text-sm font-black text-emerald-400 tabular-nums">{{ formatCurrency(roleData.stats.con_lai) }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex flex-col flex-grow min-h-[250px]">
                                <div class="flex justify-between items-start mb-3 shrink-0">
                                    <h3 class="text-[16px] font-bold text-slate-800 uppercase tracking-wide">Lưu lượng tiền ra</h3>
                                    <div class="text-right bg-blue-50 border border-blue-100 px-2.5 py-1 rounded-lg">
                                        <p class="text-[8px] font-bold text-blue-600 uppercase tracking-wider mb-0.5">Thực chi {{ activeMonthLabel }}</p>
                                        <p class="text-xs font-black text-blue-700 tabular-nums leading-none">{{ formatCurrency(activeMonthValue) }}</p>
                                    </div>
                                </div>
                                <div class="relative w-full flex-grow">
                                    <canvas ref="financeChart" class="cursor-pointer"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="activeTab === 'radar'" class="space-y-6">
                    <div class="mb-5">
                        <h2 class="text-xl font-black text-slate-900">Radar sức khỏe tài chính</h2>
                        <p class="text-xs text-slate-500 mt-1">Giám sát cảnh báo tỷ lệ tiêu hao ngân sách của từng phòng ban</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
                        <div v-for="phong in roleData.radarNganSach" :key="phong.id" class="bg-white p-5 rounded-xl border shadow-sm relative overflow-hidden" :class="phong.phan_tram_su_dung > 90 ? 'border-red-300 ring-1 ring-red-50' : 'border-slate-200'">
                            <div v-if="phong.phan_tram_su_dung > 90" class="absolute top-0 right-0 bg-red-500 text-white text-[9px] font-bold px-2 py-1 rounded-bl-lg uppercase">Cảnh báo</div>

                            <h3 class="font-bold text-slate-900 mb-4">{{ phong.ten_phong_ban }}</h3>

                            <div class="mb-4">
                                <div class="flex justify-between items-end mb-1">
                                    <span class="text-[10px] font-bold text-slate-500 uppercase">Đã dùng</span>
                                    <span class="text-sm font-black" :class="phong.phan_tram_su_dung > 90 ? 'text-red-600' : 'text-slate-800'">{{ phong.phan_tram_su_dung }}%</span>
                                </div>
                                <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden">
                                    <div class="h-full rounded-full" :style="{ width: phong.phan_tram_su_dung + '%' }" :class="phong.phan_tram_su_dung > 90 ? 'bg-red-500' : (phong.phan_tram_su_dung > 70 ? 'bg-orange-400' : 'bg-emerald-500')"></div>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-3 text-[11px]">
                                <div class="bg-slate-50 p-2 rounded border border-slate-100">
                                    <p class="text-slate-500 mb-0.5">Tổng cấp</p>
                                    <p class="font-bold text-slate-800 truncate" :title="formatCurrency(phong.ngan_sach_tong)">{{ formatCurrency(phong.ngan_sach_tong) }}</p>
                                </div>
                                <div class="p-2 rounded border" :class="phong.phan_tram_su_dung > 90 ? 'bg-red-50 border-red-100' : 'bg-slate-50 border-slate-100'">
                                    <p class="mb-0.5" :class="phong.phan_tram_su_dung > 90 ? 'text-red-500' : 'text-slate-500'">Còn lại</p>
                                    <p class="font-bold truncate" :class="phong.phan_tram_su_dung > 90 ? 'text-red-700' : 'text-blue-600'" :title="formatCurrency(phong.ngan_sach_con_lai)">{{ formatCurrency(phong.ngan_sach_con_lai) }}</p>
                                </div>
                            </div>
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
