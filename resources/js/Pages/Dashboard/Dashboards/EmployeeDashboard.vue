<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch, onMounted } from 'vue';
import debounce from 'lodash/debounce';
import Chart from 'chart.js/auto';

import KpiCard from '@/Components/UI/KpiCard.vue';
import StatusBadge from '@/Components/UI/StatusBadge.vue';

const props = defineProps({
    stats: { type: Object, default: () => ({ total: 0, cho_xuly: 0, hoan_tat: 0, that_bai: 0, da_thanh_toan: 0 }) },
    recentRequests: { type: Object, default: () => ({ data: [], links: [] }) },
    filters: { type: Object, default: () => ({ search: '', status: 'all' }) },
    trangThais: { type: Array, default: () => [] }
});

const search = ref(new URLSearchParams(window.location.search).get('search') || '');
const status = ref(new URLSearchParams(window.location.search).get('status') || 'all');

watch([search, status], debounce(function ([newSearch, newStatus]) {
    router.get(route('dashboard'), { search: newSearch, status: newStatus }, {
        preserveState: true, replace: true
    });
}, 300));

// VẼ BIỂU ĐỒ - Dùng màu Pastel dịu mắt, TẮT legend mặc định
const chartCanvas = ref(null);
onMounted(() => {
    if (chartCanvas.value) {
        new Chart(chartCanvas.value, {
            type: 'doughnut',
            data: {
                labels: ['Cần xử lý', 'Đã thanh toán', 'Hoàn tất', 'Thất bại'],
                datasets: [{
                    data: [
                        props.stats?.cho_xuly || 0,
                        props.stats?.da_thanh_toan || 0,
                        props.stats?.hoan_tat || 0,
                        props.stats?.that_bai || 0
                    ],
                    backgroundColor: ['#fbbf24', '#60a5fa', '#34d399', '#f87171'],
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false // Đã tắt chú thích mặc định của thư viện
                    }
                },
                cutout: '75%'
            }
        });
    }
});
</script>

<template>
    <Head title="Tổng quan" />

    <div class="py-4">
        <div class="mb-6">
            <h2 class="font-black text-2xl text-slate-900 tracking-tight">Dashboard</h2>
            <p class="text-sm text-slate-500 mt-1 font-medium">Tổng quan tình hình xử lý yêu cầu và ngân sách</p>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
            <KpiCard title="Tổng phiếu" :value="stats?.total" colorType="slate" />
            <KpiCard title="Cần xử lý" :value="stats?.cho_xuly" colorType="amber" />
            <KpiCard title="Đã thanh toán" :value="stats?.da_thanh_toan" colorType="blue" />
            <KpiCard title="Hoàn tất" :value="stats?.hoan_tat" colorType="emerald" />
            <KpiCard title="Thất bại / Hủy" :value="stats?.that_bai" colorType="red" />
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">

            <div class="lg:col-span-1 bg-white shadow-sm rounded-2xl border border-slate-200 p-6 flex flex-col h-full">

                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest shrink-0 text-center mb-4">Tỷ lệ trạng thái</h3>

                <div class="flex-grow flex flex-col items-center justify-center w-full">

                    <div class="relative w-full h-[180px] flex items-center justify-center shrink-0">
                        <canvas ref="chartCanvas"></canvas>
                        <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none mt-1">
                            <span class="text-3xl font-black text-slate-800">{{ stats?.total || 0 }}</span>
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Yêu cầu</span>
                        </div>
                    </div>

                    <div class="w-full mt-8 flex flex-col gap-3.5 px-2">
                        <div class="flex items-center justify-between group cursor-default">
                            <div class="flex items-center gap-2.5">
                                <span class="w-3 h-3 rounded-full bg-yellow-500 shadow-sm"></span>
                                <span class="text-sm font-medium text-slate-600 group-hover:text-slate-900 transition-colors">Cần xử lý</span>
                            </div>
                            <span class="text-sm font-bold text-slate-800">{{ stats?.cho_xuly || 0 }}</span>
                        </div>

                        <div class="flex items-center justify-between group cursor-default">
                            <div class="flex items-center gap-2.5">
                                <span class="w-3 h-3 rounded-full bg-[#60a5fa] shadow-sm"></span>
                                <span class="text-sm font-medium text-slate-600 group-hover:text-slate-900 transition-colors">Đã thanh toán</span>
                            </div>
                            <span class="text-sm font-bold text-slate-800">{{ stats?.da_thanh_toan || 0 }}</span>
                        </div>

                        <div class="flex items-center justify-between group cursor-default">
                            <div class="flex items-center gap-2.5">
                                <span class="w-3 h-3 rounded-full bg-[#34d399] shadow-sm"></span>
                                <span class="text-sm font-medium text-slate-600 group-hover:text-slate-900 transition-colors">Hoàn tất</span>
                            </div>
                            <span class="text-sm font-bold text-slate-800">{{ stats?.hoan_tat || 0 }}</span>
                        </div>

                        <div class="flex items-center justify-between group cursor-default">
                            <div class="flex items-center gap-2.5">
                                <span class="w-3 h-3 rounded-full bg-[#f87171] shadow-sm"></span>
                                <span class="text-sm font-medium text-slate-600 group-hover:text-slate-900 transition-colors">Thất bại / Hủy</span>
                            </div>
                            <span class="text-sm font-bold text-slate-800">{{ stats?.that_bai || 0 }}</span>
                        </div>
                    </div>

                </div>
            </div>

            <div class="lg:col-span-3 bg-white shadow-sm rounded-2xl border border-slate-200 flex flex-col overflow-hidden">

                <div class="p-5 border-b border-slate-100 bg-slate-50/50 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wide">Danh sách yêu cầu gần đây</h3>
                    <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
                        <div class="relative">
                            <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 transform -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                            <input v-model="search" type="text" class="w-full sm:w-60 pl-10 pr-4 py-2 text-sm border-slate-200 rounded-lg focus:ring-blue-600 focus:border-blue-600 bg-white shadow-sm transition-all placeholder:text-slate-400" placeholder="Mã phiếu, tiêu đề...">
                        </div>
                        <select v-model="status" class="w-full sm:w-44 py-2 pl-4 pr-8 text-sm border-slate-200 rounded-lg focus:ring-blue-600 focus:border-blue-600 bg-white font-medium shadow-sm transition-all text-slate-600 cursor-pointer">
                            <option value="all">Tất cả trạng thái</option>
                            <option v-for="tt in trangThais" :key="tt.value" :value="tt.value">{{ tt.label }}</option>
                        </select>
                    </div>
                </div>

                <div class="overflow-x-auto flex-grow">
                    <table class="min-w-full divide-y divide-slate-100">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-6 py-4 text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider">Mã / Tiêu đề</th>
                                <th class="px-6 py-4 w-[140px] text-center text-[11px] font-bold text-slate-400 uppercase tracking-wider">Phân loại</th>
                                <th class="px-6 py-4 w-[160px] text-right text-[11px] font-bold text-slate-400 uppercase tracking-wider">Giá trị</th>
                                <th class="px-6 py-4 w-[180px] text-center text-[11px] font-bold text-slate-400 uppercase tracking-wider">Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50 bg-white">
                            <tr v-for="phieu in recentRequests?.data" :key="phieu.id" class="hover:bg-slate-50/80 transition-colors group">
                                <td class="px-6 py-4 align-middle">
                                    <div class="font-bold text-blue-600 group-hover:text-blue-800 transition-colors">
                                        <Link :href="route('phieu.show', phieu.id)">{{ phieu.ma_phieu }}</Link>
                                    </div>
                                    <div class="text-sm font-semibold text-slate-800 mt-1 truncate max-w-[200px] sm:max-w-xs" :title="phieu.tieu_de">{{ phieu.tieu_de }}</div>
                                </td>

                                <td class="px-6 py-4 align-middle text-center">
                                    <span class="inline-flex justify-center items-center min-w-[100px] px-3 py-1.5 rounded-md bg-slate-100 text-slate-600 text-[11px] font-bold uppercase tracking-wider border border-slate-200">
                                        {{ phieu.loai_phieu === 'nghi_phep' ? 'Nghỉ phép' : 'Mua sắm' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 align-middle text-right">
                                    <span v-if="phieu.loai_phieu === 'nghi_phep'" class="text-slate-300 font-bold">-</span>

                                    <span v-else-if="phieu.tong_tien && phieu.tong_tien !== '0 VNĐ'"
                                        class="whitespace-nowrap text-sm font-bold text-slate-700 tabular-nums">
                                        {{ phieu.tong_tien }}
                                    </span>

                                    <span v-else class="text-amber-600 text-[10px] font-bold bg-amber-50 border border-amber-100 px-2 py-1 rounded uppercase tracking-wider inline-block">Chờ báo giá</span>
                                </td>
                                <td class="px-6 py-4 align-middle text-center">
                                    <StatusBadge :label="phieu.trang_thai_label" :color="phieu.trang_thai_color" />
                                </td>
                            </tr>

                            <tr v-if="!recentRequests?.data || recentRequests.data.length === 0">
                                <td colspan="4" class="px-6 py-16 text-center">
                                    <div class="flex flex-col items-center justify-center text-slate-400">
                                        <svg class="w-12 h-12 mb-3 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                        <p class="text-sm font-semibold text-slate-500">Không tìm thấy yêu cầu nào phù hợp.</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="px-5 py-3 border-t border-slate-100 bg-slate-50 flex items-center justify-center" v-if="recentRequests?.links && recentRequests.links.length > 0">
                    <div class="flex flex-wrap justify-center gap-1">
                        <template v-for="(link, index) in recentRequests.links" :key="index">
                            <Link v-if="link.url" :href="link.url" v-html="link.label"
                                  class="px-3.5 py-1.5 rounded-md text-sm font-bold transition-all"
                                  :class="link.active ? 'bg-slate-800 text-white shadow-md' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'" />
                            <span v-else v-html="link.label" class="px-3.5 py-1.5 rounded-md text-sm font-bold text-slate-300 bg-transparent cursor-not-allowed" />
                        </template>
                    </div>
                </div>

            </div>
        </div>
    </div>
</template>
