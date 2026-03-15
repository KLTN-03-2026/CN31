<script setup>
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    phieu: Object
});

// Lấy thông tin User hiện tại
const page = usePage();
const user = computed(() => page.props.auth.user);

// Logic hiển thị nút Duyệt
// Chỉ hiện khi: User là Trưởng phòng VÀ Phiếu đang chờ duyệt
const canApprove = computed(() => {
    return user.value.role === 'truong_phong' &&
        (props.phieu.trang_thai_color === 'yellow'); // Màu vàng tương ứng 'cho_duyet'
});

// Hàm xử lý hành động (Gọi xuống Backend)
const xuLyPhieu = (hanhDong) => {
    if (!confirm(`Bạn chắc chắn muốn ${hanhDong === 'duyet' ? 'DUYỆT' : 'TỪ CHỐI'} phiếu này?`)) return;

    router.post(route('phieu.duyet', props.phieu.id), {
        hanh_dong: hanhDong, // 'duyet' hoặc 'tu_choi'
        ghi_chu: '' // Sau này có thể mở popup nhập lý do từ chối
    });
};



</script>

<template>

    <Head :title="`Chi tiết ${phieu.ma_phieu}`" />

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">

            <div class="mb-4">
                <Link :href="route('dashboard')"
                    class="text-gray-500 hover:text-blue-600 font-medium flex items-center gap-1">
                    &larr; Quay lại Dashboard
                </Link>
            </div>

            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 border-b border-gray-200 bg-slate-50 flex justify-between items-start">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-800">Chi Tiết Yêu Cầu</h1>
                        <p class="text-sm text-gray-600 mt-1">
                            Mã phiếu: <span class="font-mono font-bold text-black">{{ phieu.ma_phieu }}</span>
                        </p>
                        <p class="text-sm text-gray-600">
                            Người tạo: <span class="font-medium">{{ phieu.nguoi_tao }}</span> - {{ phieu.ngay_tao }}
                        </p>
                    </div>

                    <div>
                        <span class="px-3 py-1 rounded-full text-sm font-bold border" :class="{
                            'bg-gray-100 text-gray-800 border-gray-300': phieu.trang_thai_color === 'gray',
                            'bg-yellow-100 text-yellow-800 border-yellow-300': phieu.trang_thai_color === 'yellow',
                            'bg-blue-100 text-blue-800 border-blue-300': phieu.trang_thai_color === 'blue',
                            'bg-green-100 text-green-800 border-green-300': phieu.trang_thai_color === 'green',
                            'bg-red-100 text-red-800 border-red-300': phieu.trang_thai_color === 'red',
                        }">
                            {{ phieu.trang_thai_label }}
                        </span>
                    </div>
                </div>

                <div class="p-8">
                    <div class="mb-8 grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase">Tiêu đề phiếu</label>
                            <p class="mt-1 text-lg text-gray-900 font-medium">{{ phieu.tieu_de }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase">Lý do mua sắm</label>
                            <div class="mt-1 text-gray-700 bg-gray-50 p-3 rounded border border-gray-100 italic">
                                "{{ phieu.ly_do || 'Không có ghi chú' }}"
                            </div>
                        </div>
                    </div>

                    <h3 class="text-lg font-bold text-gray-800 border-b pb-2 mb-4">Danh sách hàng hóa</h3>
                    <div class="overflow-x-auto border rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">STT</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Tên sản
                                        phẩm</th>
                                    <th class="px-4 py-3 text-right text-xs font-bold text-gray-500 uppercase">SL</th>
                                    <th class="px-4 py-3 text-right text-xs font-bold text-gray-500 uppercase">Đơn giá
                                    </th>
                                    <th class="px-4 py-3 text-right text-xs font-bold text-gray-500 uppercase">Thành
                                        tiền</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                <tr v-for="(item, index) in phieu.chi_tiet" :key="item.id">
                                    <td class="px-4 py-3 text-sm text-center text-gray-500">{{ index + 1 }}</td>
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ item.ten_san_pham }}</td>
                                    <td class="px-4 py-3 text-sm text-right text-gray-900">{{ item.so_luong }}</td>
                                    <td class="px-4 py-3 text-sm text-right text-gray-900">{{
                                        Number(item.don_gia).toLocaleString() }}</td>
                                    <td class="px-4 py-3 text-sm text-right font-bold text-gray-900">{{
                                        Number(item.thanh_tien).toLocaleString() }}</td>
                                </tr>
                            </tbody>
                            <tfoot class="bg-gray-50">
                                <tr>
                                    <td colspan="4" class="px-4 py-3 text-right font-bold text-gray-700 uppercase">Tổng
                                        cộng</td>
                                    <td class="px-4 py-3 text-right font-bold text-blue-600 text-lg">
                                        {{ Number(phieu.tong_tien).toLocaleString() }} đ
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <div class="bg-gray-50 px-6 py-4 flex justify-between items-center border-t">
                    <a :href="route('phieu.print', phieu.id)" target="_blank"
                        class="flex items-center gap-1 text-gray-600 hover:text-blue-600 font-medium transition">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75v-4.125c0-.621-.504-1.125-1.125-1.125H5.375c-.621 0-1.125.504-1.125 1.125v4.125c0 .621.504 1.125 1.125 1.125H6.358m12.284 0h.008v.008h-.008V18zM6.75 6H17.25a2.25 2.25 0 012.25 2.25v2.25H4.5v-2.25A2.25 2.25 0 016.75 6zM5.25 9h13.5" />
                        </svg>
                        In phiếu PDF
                    </a>

                    <div v-if="canApprove" class="flex gap-3">
                        <button @click="xuLyPhieu('tu_choi')"
                            class="bg-red-100 text-red-700 px-4 py-2 rounded-md font-bold hover:bg-red-200 transition text-sm">
                            Từ chối
                        </button>

                        <button @click="xuLyPhieu('duyet')"
                            class="bg-blue-600 text-white px-6 py-2 rounded-md font-bold hover:bg-blue-700 shadow transition text-sm">
                            Phê duyệt
                        </button>

                    </div>
                    <div v-else class="italic text-gray-500 text-sm">
                        {{ phieu.trang_thai_label }}
                    </div>

                </div>

            </div>
        </div>
    </div>
</template>
