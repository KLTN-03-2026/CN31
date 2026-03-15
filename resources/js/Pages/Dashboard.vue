<script setup>


// Nhận dữ liệu từ Controller truyền sang
defineProps({
    stats: Object,
    recentRequests: Array
});
</script>

<template>

    <Head title="Tổng quan" />
    <template>
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Dashboard - Tổng Quan</h2>
            <Link :href="route('phieu.create')"
                class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-sm shadow">
            + Tạo Phiếu Mới
            </Link>
        </div>
    </template>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-indigo-500">
                    <div class="text-gray-500 text-sm font-medium uppercase">Tổng phiếu tạo</div>
                    <div class="mt-2 text-3xl font-bold text-gray-900">{{ stats.total }}</div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-yellow-500">
                    <div class="text-gray-500 text-sm font-medium uppercase">Đang chờ duyệt</div>
                    <div class="mt-2 text-3xl font-bold text-yellow-600">{{ stats.cho_duyet }}</div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-green-500">
                    <div class="text-gray-500 text-sm font-medium uppercase">Đã được duyệt</div>
                    <div class="mt-2 text-3xl font-bold text-green-600">{{ stats.da_duyet }}</div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-red-500">
                    <div class="text-gray-500 text-sm font-medium uppercase">Bị từ chối</div>
                    <div class="mt-2 text-3xl font-bold text-red-600">{{ stats.tu_choi }}</div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">Các yêu cầu gần đây</h3>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Mã phiếu</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Tiêu đề</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Ngày tạo</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Tổng tiền</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="phieu in recentRequests" :key="phieu.id">
                                    <td class="px-6 py-4 whitespace-nowrap font-bold text-gray-700">
                                        <Link :href="route('phieu.show', phieu.id)"
                                            class="text-blue-600 hover:text-blue-800 hover:underline">
                                        {{ phieu.ma_phieu }}
                                        </Link>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-600">
                                        {{ phieu.tieu_de }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-500 text-sm">
                                        {{ phieu.ngay_tao }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-800">
                                        {{ phieu.tong_tien }} đ
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                            :class="{
                                                'bg-yellow-100 text-yellow-800': phieu.trang_thai_color === 'yellow',
                                                'bg-green-100 text-green-800': phieu.trang_thai_color === 'green',
                                                'bg-red-100 text-red-800': phieu.trang_thai_color === 'red',
                                                'bg-blue-100 text-blue-800': phieu.trang_thai_color === 'blue',
                                                'bg-gray-100 text-gray-800': phieu.trang_thai_color === 'gray',
                                            }">
                                            {{ phieu.trang_thai_label }}
                                        </span>
                                    </td>
                                </tr>

                                <tr v-if="recentRequests.length === 0">
                                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                        Bạn chưa tạo phiếu yêu cầu nào.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>

</template>
