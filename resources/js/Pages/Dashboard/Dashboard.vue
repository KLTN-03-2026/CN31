<script setup>

import { ref, watch, onMounted } from 'vue'; // Thêm onMounted
import debounce from 'lodash/debounce';
import Chart from 'chart.js/auto'; // Import thư viện vẽ biểu đồ

const props = defineProps({
    stats: { type: Object, default: () => ({ total: 0, cho_xuly: 0, hoan_tat: 0, that_bai: 0 }) },
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

// VẼ BIỂU ĐỒ
const chartCanvas = ref(null); // Tạo thẻ <canvas> ảo

onMounted(() => {
    // Chỉ vẽ khi trang đã load xong và có dữ liệu
    if (chartCanvas.value) {
        new Chart(chartCanvas.value, {
            type: 'doughnut', // Loại biểu đồ tròn rỗng ruột
            data: {
                labels: ['Cần xử lý', 'Đã hoàn tất', 'Thất bại / Hủy'],
                datasets: [{
                    data: [
                        props.stats?.cho_xuly || 0,
                        props.stats?.hoan_tat || 0,
                        props.stats?.that_bai || 0
                    ],
                    backgroundColor: [
                        '#eab308', // Vàng (Cần xử lý)
                        '#22c55e', // Xanh lá (Hoàn tất)
                        '#ef4444'  // Đỏ (Hủy)
                    ],
                    borderWidth: 2,
                    hoverOffset: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
    }
});
</script>
<template>
    <Head title="Tổng quan" />

    <div class="py-6 px-8 max-w-7xl mx-auto sm:px-6 lg:px-8 flex justify-between items-center">
        <h2 class="font-semibold text-2xl text-gray-800 leading-tight">Dashboard - Tổng Quan</h2>
        <Link :href="route('phieu.create')"
            class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded shadow transition">
            + Tạo Phiếu Mới
        </Link>
    </div>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-indigo-500">
                    <div class="text-gray-500 text-sm font-medium uppercase">Tổng phiếu tạo</div>
                    <div class="mt-2 text-3xl font-bold text-gray-900">{{ stats?.total || 0 }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-yellow-500">
                    <div class="text-gray-500 text-sm font-medium uppercase">Cần xử lý</div>
                    <div class="mt-2 text-3xl font-bold text-yellow-600">{{ stats?.cho_xuly || 0 }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-green-500">
                    <div class="text-gray-500 text-sm font-medium uppercase">Đã hoàn tất</div>
                    <div class="mt-2 text-3xl font-bold text-green-600">{{ stats?.hoan_tat || 0 }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-red-500">
                    <div class="text-gray-500 text-sm font-medium uppercase">Thất bại / Hủy</div>
                    <div class="mt-2 text-3xl font-bold text-red-600">{{ stats?.that_bai || 0 }}</div>
                </div>
            </div>
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-100 mb-8 p-6 flex flex-col items-center">
                <h3 class="text-lg font-bold text-gray-800 mb-4 w-full border-b pb-2">Tỷ lệ trạng thái phiếu mua sắm</h3>
                <div class="relative w-full h-64 flex justify-center">
                    <canvas ref="chartCanvas"></canvas>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-100">

                <div class="p-4 bg-slate-50 border-b border-gray-200 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <h3 class="text-lg font-bold text-gray-800">Các yêu cầu gần đây</h3>

                    <div class="flex flex-col md:flex-row gap-3 w-full md:w-1/2 justify-end">
                        <div class="relative w-full md:w-2/3">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <input v-model="search" type="text" class="block w-full p-2 pl-9 text-sm text-gray-900 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" placeholder="Tìm Mã phiếu, Tiêu đề...">
                        </div>

                        <div class="w-full md:w-1/3">
                            <select v-model="status" class="block w-full p-2 text-sm text-gray-900 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 font-medium">
                                <option value="all">Tất cả trạng thái</option>
                                <option v-for="tt in trangThais" :key="tt.value" :value="tt.value">
                                    {{ tt.label }}
                                </option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Mã phiếu</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Tiêu đề</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Người tạo</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Ngày tạo</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Tổng tiền</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="phieu in recentRequests?.data" :key="phieu.id" class="hover:bg-blue-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap font-bold text-gray-700">
                                    <Link :href="route('phieu.show', phieu.id)" class="text-blue-600 hover:text-blue-800 hover:underline">
                                        {{ phieu.ma_phieu }}
                                    </Link>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-gray-600">{{ phieu.tieu_de }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-gray-600">{{ phieu.nguoi_tao }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-gray-500 text-sm">{{ phieu.ngay_tao }}</td>
                                <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-800">{{ phieu.tong_tien }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full border"
                                        :class="{
                                            'bg-gray-100 text-gray-800 border-gray-200': phieu.trang_thai_color === 'gray',
                                            'bg-yellow-100 text-yellow-800 border-yellow-200': phieu.trang_thai_color === 'yellow',
                                            'bg-orange-100 text-orange-800 border-orange-200': phieu.trang_thai_color === 'orange',
                                            'bg-blue-100 text-blue-800 border-blue-200': phieu.trang_thai_color === 'blue',
                                            'bg-green-100 text-green-800 border-green-200': phieu.trang_thai_color === 'green',
                                            'bg-red-100 text-red-800 border-red-200': phieu.trang_thai_color === 'red',
                                            'bg-slate-100 text-slate-800 border-slate-200': phieu.trang_thai_color === 'slate',
                                        }">
                                        {{ phieu.trang_thai_label }}
                                    </span>
                                </td>
                            </tr>

                            <tr v-if="!recentRequests?.data || recentRequests.data.length === 0">
                                <td colspan="6" class="px-6 py-10 text-center text-gray-500 italic">
                                    Không tìm thấy phiếu yêu cầu nào phù hợp.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="px-6 py-4 border-t flex justify-center bg-gray-50" v-if="recentRequests?.links && recentRequests.links.length > 3">
                    <template v-for="(link, index) in recentRequests.links" :key="index">
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            v-html="link.label"
                            class="mx-1 px-4 py-2 border rounded text-sm transition-colors"
                            :class="link.active ? 'bg-blue-600 text-white border-blue-600 font-bold' : 'bg-white text-gray-700 hover:bg-gray-100'"
                        />
                        <span
                            v-else
                            v-html="link.label"
                            class="mx-1 px-4 py-2 border rounded text-sm text-gray-400 bg-gray-50 cursor-not-allowed"
                        />
                    </template>
                </div>

            </div>
        </div>
    </div>
</template>
