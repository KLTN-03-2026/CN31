<script setup>
import { onMounted, onBeforeUnmount, ref, watch } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import Chart from 'chart.js/auto';
import StatusBadge from '@/Components/UI/StatusBadge.vue';

const props = defineProps({
    roleData: {
        type: Object,
        default: () => ({ stats: {}, danhSachChoThanhToan: { data: [] }, chartData: {} })
    }
});

const formatCurrency = (value) => {
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value || 0);
};

// --- LOGIC LỌC NĂM VÀ TƯƠNG TÁC BIỂU ĐỒ ---
const currentYear = new Date().getFullYear();
const currentMonth = new Date().getMonth(); // 0-11
const availableYears = [currentYear - 2, currentYear - 1, currentYear, currentYear + 1, currentYear + 2];

// Trạng thái hiển thị Năm và Tháng trên UI
const selectedYear = ref(props.roleData?.chartData?.selectedYear || currentYear);
const activeMonthLabel = ref(`Tháng ${currentMonth + 1}`);
const activeMonthValue = ref(0);


// Hàm gọi API load lại data khi đổi năm
const changeYear = () => {
    // ĐỔI route('dashboard') THÀNH route('accountant.index')
    router.get(route('accountant.index'), { year: selectedYear.value }, {
        preserveState: true,
        preserveScroll: true,
        only: ['roleData']
    });
};

// ... (code dưới giữ nguyên) ...

// --- LOGIC RENDER BIỂU ĐỒ ---
const financeChart = ref(null);
let chartInstance = null;

const renderChart = () => {
    if (!financeChart.value) return;
    if (chartInstance) chartInstance.destroy();

    const chartLabels = props.roleData?.chartData?.labels || ['T1', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'T8', 'T9', 'T10', 'T11', 'T12'];
    const thucChiData = props.roleData?.chartData?.thucChi || Array(12).fill(0);
    const nganSachData = props.roleData?.chartData?.nganSach || Array(12).fill(0);

    // Cập nhật giá trị hiển thị ban đầu dựa trên tháng đang được focus
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
                    borderRadius: 4,
                },
                {
                    label: 'Ngân sách cấp (VNĐ)',
                    data: nganSachData,
                    backgroundColor: '#e2e8f0',
                    borderRadius: 4,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            // SỰ KIỆN CLICK VÀO CỘT
            onClick: (event, elements) => {
                if (elements.length > 0) {
                    const dataIndex = elements[0].index; // Lấy ra index cột (0-11)
                    activeMonthLabel.value = `Tháng ${dataIndex + 1}`;
                    activeMonthValue.value = thucChiData[dataIndex];
                }
            },
            plugins: {
                legend: { position: 'top', align: 'end', labels: { boxWidth: 12, font: { size: 11 } } },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) label += ': ';
                            if (context.parsed.y !== null) {
                                label += new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(context.parsed.y);
                            }
                            return label;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { borderDash: [4, 4] },
                    ticks: {
                        callback: function(value) {
                            if (value >= 1000000000) return (value / 1000000000) + ' Tỷ';
                            if (value >= 1000000) return (value / 1000000) + ' Tr';
                            if (value >= 1000) return (value / 1000) + ' K';
                            return value;
                        }
                    }
                },
                x: { grid: { display: false } }
            }
        }
    });
};

onMounted(() => {
    renderChart();
});

watch(
    () => props.roleData?.chartData,
    () => {
        renderChart();
    },
    { deep: true }
);

onBeforeUnmount(() => {
    if (chartInstance) chartInstance.destroy();
});
</script>

<template>
    <Head title="Sổ quỹ - Kế toán" />
    <div class="py-6 animate-fade-in bg-slate-50/50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="flex flex-col lg:flex-row gap-6">

                <div class="w-full lg:w-1/3 flex flex-col">
                    <div class="mb-4 shrink-0 flex justify-between items-end">
                        <div>
                            <h2 class="font-black text-xl text-slate-900 tracking-tight">Cần thanh toán</h2>
                            <p class="text-xs font-medium text-slate-500 mt-1">
                                Bạn có <strong class="text-amber-600">{{ roleData?.stats?.cho_thanh_toan || 0 }}</strong> yêu cầu đang chờ
                            </p>
                        </div>
                    </div>

                    <div class="flex flex-col space-y-3 pb-2">
                        <Link v-for="phieu in roleData?.danhSachChoThanhToan?.data" :key="phieu.id" :href="route('phieu.show', phieu.id)" class="block bg-white p-4 rounded-xl border border-slate-200 shadow-sm hover:border-blue-400 hover:shadow-md transition-all group">
                            <div class="flex justify-between items-start mb-2">
                                <span class="font-mono text-[10px] font-bold text-slate-400 uppercase tracking-widest group-hover:text-blue-500 transition-colors">{{ phieu.ma_phieu }}</span>
                                <StatusBadge :label="phieu.trang_thai_label" :color="phieu.trang_thai_color" class="!px-2 !py-0.5 scale-90 origin-top-right" />
                            </div>
                            <h4 class="font-bold text-slate-800 text-sm mb-3 line-clamp-2 leading-snug group-hover:text-blue-700 transition-colors" :title="phieu.tieu_de">{{ phieu.tieu_de }}</h4>
                            <div class="flex justify-between items-center pt-3 border-t border-slate-100 pl-1">
                                <div class="flex items-center gap-2">
                                    <div class="w-5 h-5 rounded-full bg-slate-100 flex items-center justify-center text-[9px] font-bold text-slate-600 uppercase border border-slate-200">{{ phieu.nguoi_tao ? phieu.nguoi_tao[0] : '?' }}</div>
                                    <span class="text-[11px] font-semibold text-slate-600 truncate max-w-[100px]">{{ phieu.nguoi_tao }}</span>
                                </div>
                                <span class="text-[11px] text-slate-500 font-medium flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    {{ phieu.ngay_tao }}
                                </span>
                            </div>
                        </Link>

                        <div v-if="!roleData?.danhSachChoThanhToan?.data?.length" class="bg-white p-8 rounded-xl border border-slate-200 border-dashed text-center">
                            <div class="w-10 h-10 bg-emerald-50 rounded-full flex items-center justify-center mx-auto mb-3"><svg class="w-5 h-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg></div>
                            <p class="text-sm font-bold text-slate-700">Đã hoàn tất sổ sách</p>
                            <p class="text-[11px] text-slate-500 mt-1">Không có phiếu nào chờ thanh toán.</p>
                        </div>

                        <div class="pt-2 flex justify-center" v-if="roleData?.danhSachChoThanhToan?.links?.length > 3">
                            <div class="flex flex-wrap justify-center gap-1">
                                <template v-for="(link, index) in roleData.danhSachChoThanhToan.links" :key="index">
                                    <Link v-if="link.url" :href="link.url" v-html="link.label" class="px-2 py-1 rounded-md text-xs font-bold transition-all" :class="link.active ? 'bg-slate-800 text-white shadow-md' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'" />
                                    <span v-else v-html="link.label" class="px-2 py-1 rounded-md text-xs font-bold text-slate-300 bg-transparent cursor-not-allowed" />
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="w-full lg:w-2/3 flex flex-col">
                    <div class="mb-4 shrink-0 flex justify-between items-end">
                        <div>
                            <h2 class="font-black text-xl text-slate-900 tracking-tight">Biểu đồ tài chính</h2>
                            <p class="text-xs font-medium text-slate-500 mt-1">Tổng quan dòng tiền từng năm</p>
                        </div>

                        <div class="flex items-center gap-3">
                            <select v-model="selectedYear" @change="changeYear" class="text-sm font-bold border-slate-200 rounded-lg focus:ring-blue-500 focus:border-blue-500 py-1.5 pl-3 pr-8 cursor-pointer shadow-sm text-slate-700">
                                <option v-for="year in availableYears" :key="year" :value="year">Năm {{ year }}</option>
                            </select>
                            <button class="inline-flex items-center justify-center gap-1.5 bg-slate-800 text-white px-3 py-1.5 rounded-lg text-xs font-bold shadow-sm hover:bg-slate-700 transition-all">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                Báo cáo
                            </button>
                        </div>
                    </div>

                    <div class="flex-grow flex flex-col space-y-5 pb-2">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 shrink-0">
                            <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm relative overflow-hidden flex flex-col justify-center">
                                <div class="absolute right-0 top-0 w-16 h-16 bg-red-50 rounded-bl-full -mr-4 -mt-4"></div>
                                <p class="text-[10px] font-bold text-red-500 uppercase tracking-widest mb-1">Tổng tiền cần chi</p>
                                <h3 class="text-2xl font-black text-red-500 truncate" :title="formatCurrency(roleData?.stats?.tong_tien_cho_chi)">
                                    {{ formatCurrency(roleData?.stats?.tong_tien_cho_chi) }}
                                </h3>
                            </div>

                            <div class="bg-white p-5 rounded-xl border border-blue-200 shadow-sm relative overflow-hidden flex flex-col justify-center ring-1 ring-blue-50 transition-all">
                                <div class="absolute right-0 top-0 w-16 h-16 bg-blue-50 rounded-bl-full -mr-4 -mt-4"></div>
                                <p class="text-[10px] font-bold text-emerald-600 uppercase tracking-widest mb-1 flex items-center gap-1.5">
                                    Thực chi {{ activeMonthLabel }} / {{ selectedYear }}
                                    <span class="inline-flex w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                                </p>
                                <h3 class="text-2xl font-black text-emerald-700 truncate transition-all duration-300" :key="activeMonthValue" :title="formatCurrency(activeMonthValue)">
                                    {{ formatCurrency(activeMonthValue) }}
                                </h3>
                            </div>
                        </div>

                        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm flex flex-col flex-grow min-h-[250px]">
                            <div class="flex justify-between items-center mb-4 shrink-0">
                                <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wide">Thống kê chi tiêu hàng tháng</h3>
                                <span class="text-[10px] font-medium text-slate-400 italic">Click vào cột để xem chi tiết tháng</span>
                            </div>
                            <div class="relative w-full flex-grow">
                                <canvas ref="financeChart" class="cursor-pointer"></canvas>
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
