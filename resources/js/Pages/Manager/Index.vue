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

// --- QUẢN LÝ TAB & TÌM KIẾM ---
const activeTab = ref(props.roleData.filters?.tab || 'workspace');
const searchQuery = ref(props.roleData.filters?.search || '');
const searchTeamQuery = ref(props.roleData.filters?.search_team || '');
let searchTimeout = null;

// --- LỌC NĂM ---
const currentYear = new Date().getFullYear();
const currentMonth = new Date().getMonth();
const availableYears = [currentYear - 2, currentYear - 1, currentYear, currentYear + 1, currentYear + 2];
const selectedYear = ref(props.roleData?.chartData?.selectedYear || currentYear);

const fetchData = () => {
    router.get(route('manager.approvals'), {
        tab: activeTab.value,
        search: activeTab.value === 'workspace' ? searchQuery.value : null,
        search_team: activeTab.value === 'team' ? searchTeamQuery.value : null,
        year: activeTab.value === 'workspace' ? selectedYear.value : null
    }, {
        preserveState: true, preserveScroll: true, only: ['roleData']
    });
};

watch([searchQuery, searchTeamQuery], () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => fetchData(), 500);
});

const changeYear = () => { fetchData(); };

const formatPagination = (label) => {
    if (label.includes('Previous')) return '«';
    if (label.includes('Next')) return '»';
    return label;
};

// --- LOGIC BIỂU ĐỒ ---
const financeChart = ref(null);
let chartInstance = null;
const activeMonthLabel = ref(`Tháng ${currentMonth + 1}`);
const activeMonthValue = ref(0);

const renderChart = async () => {
    await nextTick();
    if (activeTab.value !== 'workspace' || !financeChart.value) return;
    if (chartInstance) chartInstance.destroy();

    const chartLabels = props.roleData?.chartData?.bar?.labels || ['T1', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'T8', 'T9', 'T10', 'T11', 'T12'];
    const thucChiData = props.roleData?.chartData?.bar?.thucChi || Array(12).fill(0);
    const nganSachData = props.roleData?.chartData?.bar?.nganSach || Array(12).fill(0);

    const monthIndex = parseInt(activeMonthLabel.value.replace('Tháng ', '')) - 1;
    activeMonthValue.value = thucChiData[monthIndex] || 0;

    chartInstance = new Chart(financeChart.value, {
        type: 'bar',
        data: {
            labels: chartLabels,
            datasets: [
                {
                    label: 'Thực chi (VNĐ)',
                    data: thucChiData,
                    backgroundColor: '#3b82f6',
                    hoverBackgroundColor: '#2563eb',
                    borderRadius: 4,
                },
                {
                    label: 'Ngân sách cấp (VNĐ)',
                    data: nganSachData,
                    type: 'line',
                    borderColor: '#f59e0b',
                    borderWidth: 2,
                    fill: false,
                    pointRadius: 0,
                    pointHoverRadius: 4
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            onClick: (event, elements) => {
                if (elements.length > 0) {
                    const dataIndex = elements[0].index;
                    activeMonthLabel.value = `Tháng ${dataIndex + 1}`;
                    activeMonthValue.value = thucChiData[dataIndex];
                }
            },
            onHover: (event, chartElement) => {
                event.native.target.style.cursor = chartElement[0] ? 'pointer' : 'default';
            },
            plugins: {
                legend: { position: 'top', align: 'end', labels: { boxWidth: 10, font: { size: 10 } } },
                tooltip: { callbacks: { label: function(context) { return context.dataset.label + ': ' + new Intl.NumberFormat('vi-VN').format(context.parsed.y) + ' đ'; } } }
            },
            scales: {
                y: { beginAtZero: true, grid: { borderDash: [4, 4] }, ticks: { font: { size: 10 }, callback: function(value) { if (value >= 1000000000) return (value / 1000000000) + ' Tỷ'; if (value >= 1000000) return (value / 1000000) + ' Tr'; if (value >= 1000) return (value / 1000) + ' K'; return value; } } },
                x: { grid: { display: false }, ticks: { font: { size: 10 } } }
            }
        }
    });
};

watch([activeTab, () => props.roleData?.chartData], () => { renderChart(); }, { deep: true });
onMounted(() => { if (activeTab.value === 'workspace') renderChart(); });
onBeforeUnmount(() => { if (chartInstance) chartInstance.destroy(); });

// --- LỊCH ---
const currentDate = new Date();
const currentMonthName = currentDate.toLocaleString('vi-VN', { month: 'long', year: 'numeric' });
const calendarDays = Array.from({ length: 30 }, (_, i) => {
    const d = new Date(currentDate.getFullYear(), currentDate.getMonth(), i + 1);
    const dateStr = `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`;
    const absent = props.roleData.lichVangMat?.filter(v => v.ngay === dateStr) || [];
    return { date: i + 1, isToday: d.getDate() === new Date().getDate(), absents: absent };
});
</script>

<template>
    <Head title="Trưởng phòng - ProcureFlow"/>
    <div class="py-5 bg-slate-50 min-h-[calc(100vh-64px)]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="mb-4 border-b border-slate-200">
                <nav class="-mb-px flex space-x-6" aria-label="Tabs">
                    <button @click="activeTab = 'workspace'; fetchData()" :class="activeTab === 'workspace' ? 'border-slate-900 text-slate-900' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'" class="whitespace-nowrap flex py-2.5 border-b-2 font-bold text-sm transition-colors">
                        Không gian làm việc <span v-if="roleData.stats.so_luong_cho > 0" class="ml-1.5 bg-red-100 text-red-600 py-0.5 px-1.5 rounded text-[10px]">{{ roleData.stats.so_luong_cho }}</span>
                    </button>
                    <button @click="activeTab = 'team'; fetchData()" :class="activeTab === 'team' ? 'border-slate-900 text-slate-900' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'" class="whitespace-nowrap py-2.5 border-b-2 font-bold text-sm transition-colors">Đội ngũ & Lịch</button>
                </nav>
            </div>

            <div class="animate-fade-in">

                <div v-if="activeTab === 'workspace'" class="flex flex-col lg:flex-row gap-5 items-stretch">

                    <div class="w-full lg:w-5/12 flex flex-col">
                        <div class="mb-3 shrink-0">
                            <h2 class="font-black text-lg text-slate-900 tracking-tight">Cần phê duyệt</h2>
                            <p class="text-[11px] font-medium text-slate-500 mt-0.5 mb-2.5">Sếp có <strong class="text-slate-800">{{ roleData.stats.so_luong_cho }}</strong> yêu cầu cần xử lý</p>
                            <div class="relative w-full">
                                <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                                <input type="text" v-model="searchQuery" placeholder="Tìm mã phiếu, tiêu đề..." class="w-full pl-8 pr-3 py-1.5 text-xs font-medium border border-slate-300 rounded-lg focus:ring-1 focus:ring-slate-900 focus:border-slate-900 shadow-sm transition-all placeholder:text-slate-400">
                            </div>
                        </div>

                        <div class="flex flex-col flex-grow pb-1">
                            <div class="space-y-2.5">
                                <Link v-for="phieu in roleData.danhSachChoDuyet.data" :key="phieu.id" :href="route('phieu.show', phieu.id)" class="block bg-white p-3.5 rounded-xl border border-slate-200 shadow-sm hover:border-blue-400 hover:shadow transition-all group relative overflow-hidden">
                                    <div v-if="phieu.is_over_budget" class="absolute left-0 top-0 bottom-0 w-1 bg-red-500"></div>

                                    <div class="flex justify-between items-start mb-1.5" :class="phieu.is_over_budget ? 'pl-1' : ''">
                                        <span class="font-mono text-[11px] font-bold text-slate-400 uppercase tracking-widest group-hover:text-blue-500 transition-colors">{{ phieu.ma_phieu }}</span>
                                        <div class="flex items-center gap-1.5">
                                            <span v-if="phieu.is_over_budget" class="text-[8px] font-bold text-red-600 bg-red-50 border border-red-100 px-1.5 py-0.5 rounded uppercase">Vượt NS</span>
                                            <StatusBadge :label="phieu.trang_thai_label" :color="phieu.trang_thai_color" class="!px-2 !py-0.5 text-[11px] scale-90 origin-top-right" />
                                        </div>
                                    </div>

                                    <h4 class="font-bold text-slate-800 text-[13px] mb-2.5 line-clamp-2 leading-snug group-hover:text-blue-700 transition-colors" :class="phieu.is_over_budget ? 'pl-1' : ''">{{ phieu.tieu_de }}</h4>

                                    <div class="flex justify-between items-center pt-2.5 border-t border-slate-100 pl-0.5" :class="phieu.is_over_budget ? 'ml-1' : ''">
                                        <div class="flex items-center gap-2">
                                            <div class="w-4 h-4 rounded-full bg-slate-100 flex items-center justify-center text-[8px] font-bold text-slate-600 uppercase border border-slate-200">{{ phieu.nguoi_tao[0] }}</div>
                                            <span class="text-[10px] font-semibold text-slate-600 truncate max-w-[100px]">{{ phieu.nguoi_tao }}</span>
                                        </div>
                                        <span class="text-[10px] font-medium text-slate-500 flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                            {{ phieu.ngay_tao }}
                                        </span>
                                    </div>
                                </Link>

                                <div v-if="!roleData.danhSachChoDuyet.data.length" class="bg-white p-6 rounded-xl border border-slate-200 border-dashed text-center">
                                    <p class="text-sm font-bold text-slate-700">Đã sạch việc!</p>
                                    <p class="text-[10px] text-slate-500 mt-1">Không có phiếu nào chờ phê duyệt.</p>
                                </div>
                            </div>

                            <div class="mt-auto pt-4 flex justify-center" v-if="roleData.danhSachChoDuyet.links.length > 3">
                                <div class="flex flex-wrap justify-center gap-1">
                                    <template v-for="(link, index) in roleData.danhSachChoDuyet.links" :key="index">
                                        <Link v-if="link.url" :href="link.url" v-html="formatPagination(link.label)" class="px-2 py-0.5 rounded-md text-[11px] font-bold transition-all border" :class="link.active ? 'bg-slate-800 text-white border-slate-800 shadow-sm' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-100'" />
                                        <span v-else v-html="formatPagination(link.label)" class="px-2 py-0.5 rounded-md text-[11px] font-bold text-slate-300 bg-transparent cursor-not-allowed" />
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="w-full lg:w-7/12 flex flex-col">
                        <div class="mb-3 shrink-0 flex justify-between items-end">
                            <div>
                                <h2 class="font-black text-lg text-slate-900 tracking-tight">Thống kê & Ngân sách</h2>
                                <p class="text-[11px] font-medium text-slate-500 mt-0.5">Tình hình tài chính phòng ban năm nay</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <select v-model="selectedYear" @change="changeYear" class="text-xs font-bold border-slate-200 rounded-lg focus:ring-blue-500 focus:border-blue-500 py-1 pl-2.5 pr-7 cursor-pointer shadow-sm text-slate-700">
                                    <option v-for="year in availableYears" :key="year" :value="year">Năm {{ year }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="flex-grow flex flex-col space-y-4 pb-1">
                            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm relative overflow-hidden shrink-0">
                                <div class="absolute right-0 top-0 w-12 h-12 bg-blue-50 rounded-bl-full -mr-3 -mt-3"></div>
                                <div class="flex justify-between items-start mb-3 relative z-10">
                                    <div>
                                        <p class="text-[11px] font-bold text-slate-500 uppercase tracking-widest mb-0.5">Tổng ngân sách cấp</p>
                                        <h3 class="text-xl font-black text-slate-800">{{ formatCurrency(roleData.stats.ngan_sach_tong) }}</h3>
                                    </div>
                                </div>
                                <div class="mb-2 relative z-10">
                                    <div class="flex justify-between items-end mb-1">
                                        <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Tỷ lệ sử dụng quỹ</span>
                                        <span class="text-xs font-black tabular-nums" :class="roleData.stats.phan_tram > 90 ? 'text-red-600' : 'text-slate-900'">{{ roleData.stats.phan_tram }}%</span>
                                    </div>
                                    <div class="w-full h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                        <div class="h-full rounded-full transition-all duration-1000" :style="{ width: roleData.stats.phan_tram + '%' }" :class="roleData.stats.phan_tram > 90 ? 'bg-red-500' : 'bg-blue-500'"></div>
                                    </div>
                                    <p v-if="roleData.stats.phan_tram > 90" class="text-[11px] font-bold text-red-500 mt-1">Cảnh báo: Sắp hết ngân sách phòng ban!</p>
                                </div>
                                <div class="flex justify-between border-t border-slate-100 pt-2.5 relative z-10">
                                    <div>
                                        <p class="text-[11px] font-medium text-slate-500 mb-0.5">Đã giải ngân</p>
                                        <p class="text-xs font-bold text-slate-900 tabular-nums">{{ formatCurrency(roleData.stats.da_chi) }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-[11px] font-bold text-slate-500 mb-0.5">Hạn mức còn lại</p>
                                        <p class="text-xs font-black text-blue-600 tabular-nums">{{ formatCurrency(roleData.stats.con_lai) }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex flex-col flex-grow min-h-[250px]">
                                <div class="flex justify-between items-start mb-3 shrink-0">
                                    <div>
                                        <h3 class="text-[11px] font-bold text-slate-800 uppercase tracking-wide">Chi tiêu theo tháng</h3>
                                        <p class="text-[11px] font-medium text-slate-400 mt-0.5 italic">Click vào cột để xem</p>
                                    </div>
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

                <div v-if="activeTab === 'team'">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-end gap-3 mb-4">
                        <h2 class="text-lg font-black text-slate-900">Danh sách nhân sự</h2>
                        <div class="relative w-full sm:w-64">
                            <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                            <input type="text" v-model="searchTeamQuery" placeholder="Tìm tên nhân viên..." class="w-full pl-8 pr-3 py-1.5 text-xs font-medium border border-slate-300 rounded-lg focus:ring-1 focus:ring-slate-900 focus:border-slate-900 shadow-sm transition-all placeholder:text-slate-400">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 lg:grid-cols-4 gap-5">
                        <div class="lg:col-span-3">
                            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
                                <div v-for="nv in roleData.danhSachNhanSu.data" :key="nv.id" class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm hover:border-slate-300 transition-colors flex flex-col justify-between">
                                    <div class="flex items-center gap-3 mb-3 pb-3 border-b border-slate-50">
                                        <div class="w-8 h-8 rounded-full bg-slate-100 text-slate-700 flex items-center justify-center font-bold text-xs border border-slate-200 shrink-0">{{ nv.name[0] }}</div>
                                        <div class="min-w-0">
                                            <h3 class="font-bold text-slate-900 text-sm truncate">{{ nv.name }}</h3>
                                            <p class="text-[10px] font-medium text-slate-500 truncate">{{ nv.vai_tro_label }}</p>
                                        </div>
                                    </div>
                                    <div>
                                        <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Quỹ nghỉ phép</p>
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
                            <div class="mt-4 flex justify-center" v-if="roleData.danhSachNhanSu.links.length > 3">
                                <div class="flex flex-wrap justify-center gap-1">
                                    <template v-for="(link, index) in roleData.danhSachNhanSu.links" :key="index">
                                        <Link v-if="link.url" :href="link.url" v-html="formatPagination(link.label)" class="px-2 py-0.5 rounded-md text-[11px] font-bold transition-all border" :class="link.active ? 'bg-slate-800 text-white border-slate-800 shadow-sm' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-100'" />
                                        <span v-else v-html="formatPagination(link.label)" class="px-2 py-0.5 rounded-md text-[11px] font-bold text-slate-300 bg-transparent cursor-not-allowed" />
                                    </template>
                                </div>
                            </div>
                        </div>
                        <div class="lg:col-span-1">
                            <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm sticky top-6">
                                <h3 class="text-xs font-black text-slate-900 mb-3 uppercase tracking-wide">Lịch vắng mặt</h3>
                                <p class="text-[10px] font-bold text-slate-500 text-center mb-2 capitalize">{{ currentMonthName }}</p>
                                <div class="grid grid-cols-7 gap-1 text-center mb-1">
                                    <div class="text-[8px] font-bold text-slate-400" v-for="d in ['T2','T3','T4','T5','T6','T7','CN']" :key="d">{{ d }}</div>
                                </div>
                                <div class="grid grid-cols-7 gap-1">
                                    <div v-for="day in calendarDays" :key="day.date" class="aspect-square flex flex-col items-center justify-center rounded text-[10px] font-medium relative border" :class="day.isToday ? 'bg-slate-900 text-white border-slate-900 font-bold' : 'bg-slate-50 text-slate-600 border-slate-100'">
                                        <span>{{ day.date }}</span>
                                        <div v-if="day.absents.length > 0" class="absolute bottom-0.5 w-1 h-1 rounded-full bg-red-500" :title="day.absents.map(a => a.ten).join(', ')"></div>
                                    </div>
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
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(3px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
