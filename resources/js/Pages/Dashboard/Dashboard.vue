<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch, onMounted } from 'vue';
import debounce from 'lodash/debounce';
import Chart from 'chart.js/auto';

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

// VẼ BIỂU ĐỒ (Cập nhật màu sắc cho hợp tone)
const chartCanvas = ref(null);
onMounted(() => {
    if (chartCanvas.value) {
        new Chart(chartCanvas.value, {
            type: 'doughnut',
            data: {
                labels: ['Cần xử lý', 'Đã thanh toán', 'Hoàn tất', 'Thất bại/Hủy'],
                datasets: [{
                    data: [
                        props.stats?.cho_xuly || 0,
                        props.stats?.da_thanh_toan || 0,
                        props.stats?.hoan_tat || 0,
                        props.stats?.that_bai || 0
                    ],
                    backgroundColor: ['#f59e0b', '#3b82f6', '#10b981', '#ef4444'], // Amber, Blue, Emerald, Red
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, padding: 20 } } },
                cutout: '70%'
            }
        });
    }
});
</script>

<template>
    <Head title="Tổng quan" />

    <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8">

        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4 px-4 sm:px-0">
            <div>
                <h2 class="font-black text-3xl text-gray-900 tracking-tight">Dashboard</h2>
                <p class="text-sm text-gray-500 mt-1">Tổng quan tình hình xử lý yêu cầu và ngân sách</p>
            </div>
            </div>

        <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-8 px-4 sm:px-0">
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 hover:shadow-md transition">
                <div class="text-gray-400 text-xs font-bold uppercase tracking-wider mb-1">Tổng phiếu</div>
                <div class="text-3xl font-black text-indigo-900">{{ stats?.total || 0 }}</div>
            </div>
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-amber-100 relative overflow-hidden hover:shadow-md transition">
                <div class="absolute top-0 right-0 w-16 h-16 bg-amber-50 rounded-bl-full -z-10"></div>
                <div class="text-amber-600 text-xs font-bold uppercase tracking-wider mb-1">Cần xử lý</div>
                <div class="text-3xl font-black text-amber-500">{{ stats?.cho_xuly || 0 }}</div>
            </div>
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-blue-100 relative overflow-hidden hover:shadow-md transition">
                <div class="absolute top-0 right-0 w-16 h-16 bg-blue-50 rounded-bl-full -z-10"></div>
                <div class="text-blue-600 text-xs font-bold uppercase tracking-wider mb-1">Đã thanh toán</div>
                <div class="text-3xl font-black text-blue-500">{{ stats?.da_thanh_toan || 0 }}</div>
            </div>
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-emerald-100 relative overflow-hidden hover:shadow-md transition">
                <div class="absolute top-0 right-0 w-16 h-16 bg-emerald-50 rounded-bl-full -z-10"></div>
                <div class="text-emerald-600 text-xs font-bold uppercase tracking-wider mb-1">Hoàn tất</div>
                <div class="text-3xl font-black text-emerald-500">{{ stats?.hoan_tat || 0 }}</div>
            </div>
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-red-100 relative overflow-hidden hover:shadow-md transition">
                <div class="absolute top-0 right-0 w-16 h-16 bg-red-50 rounded-bl-full -z-10"></div>
                <div class="text-red-600 text-xs font-bold uppercase tracking-wider mb-1">Thất bại / Hủy</div>
                <div class="text-3xl font-black text-red-500">{{ stats?.that_bai || 0 }}</div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 px-4 sm:px-0">
            <div class="lg:col-span-1 bg-white shadow-sm rounded-2xl border border-gray-100 p-6 flex flex-col">
                <h3 class="text-base font-bold text-gray-800 mb-6 flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" /></svg>
                    Tỷ lệ trạng thái
                </h3>
                <div class="relative w-full flex-grow flex items-center justify-center min-h-[250px]">
                    <canvas ref="chartCanvas"></canvas>
                    <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none mt-[-20px]">
                        <span class="text-3xl font-black text-gray-800">{{ stats?.total || 0 }}</span>
                        <span class="text-xs font-bold text-gray-400 uppercase">Yêu cầu</span>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-2 bg-white shadow-sm rounded-2xl border border-gray-100 flex flex-col">
                <div class="p-6 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <h3 class="text-base font-bold text-gray-800 flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                        Luồng công việc gần đây
                    </h3>

                    <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
                        <div class="relative">
                            <svg class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 transform -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                            <input v-model="search" type="text" class="w-full sm:w-48 pl-9 pr-3 py-2 text-sm border-gray-200 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 bg-gray-50" placeholder="Mã phiếu, tiêu đề...">
                        </div>
                        <select v-model="status" class="w-full sm:w-40 py-2 text-sm border-gray-200 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 bg-gray-50 font-medium">
                            <option value="all">Tất cả trạng thái</option>
                            <option v-for="tt in trangThais" :key="tt.value" :value="tt.value">{{ tt.label }}</option>
                        </select>
                    </div>
                </div>

                <div class="overflow-x-auto flex-grow">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50/50">
                            <tr>
                                <th class="px-6 py-3 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wider">Mã / Tiêu đề</th>
                                <th class="px-6 py-3 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wider">Phân loại</th>
                                <th class="px-6 py-3 text-right text-[11px] font-bold text-gray-500 uppercase tracking-wider">Giá trị</th>
                                <th class="px-6 py-3 text-center text-[11px] font-bold text-gray-500 uppercase tracking-wider">Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <tr v-for="phieu in recentRequests?.data" :key="phieu.id" class="hover:bg-indigo-50/50 transition-colors group">
                                <td class="px-6 py-4">
                                    <div class="font-bold text-indigo-600 group-hover:text-indigo-800">
                                        <Link :href="route('phieu.show', phieu.id)">{{ phieu.ma_phieu }}</Link>
                                    </div>
                                    <div class="text-sm font-medium text-gray-900 mt-0.5 truncate max-w-xs">{{ phieu.tieu_de }}</div>
                                    <div class="text-xs text-gray-400 mt-1">{{ phieu.nguoi_tao }} • {{ phieu.ngay_tao }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-bold"
                                        :class="phieu.loai_phieu === 'nghi_phep' ? 'bg-pink-50 text-pink-700' : 'bg-blue-50 text-blue-700'">
                                        {{ phieu.loai_phieu === 'nghi_phep' ? '🏖️ Nghỉ phép' : '🛒 Mua sắm' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <span v-if="phieu.loai_phieu === 'nghi_phep'" class="text-gray-300">-</span>
                                    <span v-else-if="phieu.tong_tien && phieu.tong_tien !== '0 VNĐ'" class="font-black text-gray-800">{{ phieu.tong_tien }}</span>
                                    <span v-else class="text-orange-500 text-xs font-bold bg-orange-50 px-2 py-1 rounded">Chờ báo giá</span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="px-3 py-1.5 inline-flex text-[11px] font-black uppercase tracking-wider rounded-full border"
                                        :class="{
                                            'bg-gray-50 text-gray-600 border-gray-200': phieu.trang_thai_color === 'gray',
                                            'bg-yellow-50 text-yellow-700 border-yellow-200': phieu.trang_thai_color === 'yellow',
                                            'bg-purple-50 text-purple-700 border-purple-200': phieu.trang_thai_color === 'purple',
                                            'bg-orange-50 text-orange-700 border-orange-200': phieu.trang_thai_color === 'orange',
                                            'bg-blue-50 text-blue-700 border-blue-200': phieu.trang_thai_color === 'blue',
                                            'bg-emerald-50 text-emerald-700 border-emerald-200': phieu.trang_thai_color === 'green',
                                            'bg-red-50 text-red-700 border-red-200': phieu.trang_thai_color === 'red',
                                        }">
                                        {{ phieu.trang_thai_label }}
                                    </span>
                                </td>
                            </tr>
                            <tr v-if="!recentRequests?.data || recentRequests.data.length === 0">
                                <td colspan="4" class="px-6 py-16 text-center text-gray-400 text-sm font-medium">Không có dữ liệu phù hợp.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="px-6 py-4 border-t border-gray-100 flex justify-center bg-gray-50/50 rounded-b-2xl" v-if="recentRequests?.links && recentRequests.links.length > 3">
                    <template v-for="(link, index) in recentRequests.links" :key="index">
                        <Link v-if="link.url" :href="link.url" v-html="link.label"
                            class="mx-1 px-3 py-1.5 rounded-md text-sm font-medium transition-colors"
                            :class="link.active ? 'bg-indigo-600 text-white shadow-sm' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200'" />
                        <span v-else v-html="link.label" class="mx-1 px-3 py-1.5 rounded-md text-sm text-gray-400 bg-transparent cursor-not-allowed" />
                    </template>
                </div>
            </div>
        </div>

    </div>
</template>
