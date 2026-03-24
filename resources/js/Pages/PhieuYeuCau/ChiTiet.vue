<script setup>
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    phieu: Object
});

const page = usePage();
const user = computed(() => page.props.auth.user);

// --- LOGIC PHÂN QUYỀN ĐA CẤP ---
// 1. Quyền Duyệt/Từ chối của Sếp
const canApprove = computed(() => {
    // Trưởng phòng duyệt khi màu Vàng (Chờ TP)
    if (user.value.vai_tro === 'truong_phong' && props.phieu.trang_thai_color === 'yellow') return true;

    // Giám đốc duyệt khi màu Cam (Chờ GĐ)
    if (user.value.vai_tro === 'giam_doc' && props.phieu.trang_thai_color === 'orange') return true;

    return false;
});

// 2. Quyền Thanh toán của Kế toán
const canThanhToan = computed(() => {
    // Kế toán thấy nút khi màu Xanh dương (Chờ Thanh Toán)
    return user.value.vai_tro === 'ke_toan' && props.phieu.trang_thai_color === 'blue';
});

// 3. Quyền Hủy của Nhân viên tạo đơn
const canCancel = computed(() => {
    // Chỉ người tạo mới được hủy, và chỉ hủy khi phiếu đang màu Vàng (Sếp chưa rờ tới)
    return user.value.name === props.phieu.nguoi_tao && props.phieu.trang_thai_color === 'yellow';
});

// --- HÀM XỬ LÝ ---
const ghiChu = ref(''); // Biến lưu lý do từ chối/hủy

const xuLyPhieu = (hanhDong) => {
    let msg = hanhDong === 'duyet' ? 'DUYỆT' : 'TỪ CHỐI';
    if (!confirm(`Bạn chắc chắn muốn ${msg} phiếu này?`)) return;

    router.post(route('phieu.duyet', props.phieu.id), {
        hanh_dong: hanhDong,
        ghi_chu: ghiChu.value
    });
};

const huyPhieu = () => {
    if (!confirm('Bạn có chắc chắn muốn HỦY yêu cầu này không?')) return;
    router.post(route('phieu.cancel', props.phieu.id), {
        ghi_chu: ghiChu.value
    });
};
</script>

<template>
    <Head :title="`Chi tiết ${phieu.ma_phieu}`" />

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-4">
                <Link :href="route('dashboard')" class="text-gray-500 hover:text-blue-600 font-medium flex items-center gap-1">
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
                            'bg-orange-100 text-orange-800 border-orange-300': phieu.trang_thai_color === 'orange',
                            'bg-blue-100 text-blue-800 border-blue-300': phieu.trang_thai_color === 'blue',
                            'bg-green-100 text-green-800 border-green-300': phieu.trang_thai_color === 'green',
                            'bg-red-100 text-red-800 border-red-300': phieu.trang_thai_color === 'red',
                            'bg-slate-100 text-slate-800 border-slate-300': phieu.trang_thai_color === 'slate',
                        }">
                            {{ phieu.trang_thai_label }}
                        </span>
                    </div>
                </div>

                <div class="p-8">
                    </div> <div class="p-8 border-t bg-white">
                    <h3 class="text-lg font-bold text-gray-800 mb-6 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6 text-blue-600">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Lịch sử xử lý (Audit Trail)
                    </h3>

                    <div class="relative border-l border-gray-200 ml-4 mt-4">
                        <div v-for="(log, index) in phieu.nhat_ky" :key="log.id" class="mb-8 ml-6">

                            <span class="absolute flex items-center justify-center w-6 h-6 bg-blue-100 rounded-full -left-3 ring-8 ring-white">
                                <div class="w-2.5 h-2.5 bg-blue-600 rounded-full" :class="{'bg-green-500': index === 0 && phieu.trang_thai_color === 'green', 'bg-red-500': phieu.trang_thai_color === 'red' && index === 0}"></div>
                            </span>

                            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-1">
                                <h4 class="font-bold text-gray-900 text-base flex items-center gap-2">
                                    {{ log.hanh_dong_label }}
                                    <span v-if="index === 0" class="bg-blue-100 text-blue-800 text-[10px] font-bold px-2 py-0.5 rounded uppercase">Mới nhất</span>
                                </h4>
                                <time class="text-sm font-medium text-gray-500 mt-1 sm:mt-0">{{ log.thoi_gian }}</time>
                            </div>

                            <p class="text-sm text-gray-700 mt-1">
                                Thực hiện bởi: <span class="font-bold text-gray-900">{{ log.nguoi_thuc_hien }}</span>
                            </p>

                            <div v-if="log.ghi_chu" class="mt-2 text-sm text-gray-700 bg-gray-50 border border-gray-200 p-3 rounded-r-lg rounded-bl-lg italic relative">
                                <div class="absolute w-3 h-3 bg-gray-50 border-t border-l border-gray-200 transform rotate-45 -top-1.5 left-4"></div>
                                <span class="font-semibold not-italic">Ghi chú:</span> "{{ log.ghi_chu }}"
                            </div>

                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-6 py-4 flex flex-col md:flex-row justify-between items-center border-t gap-4">
                    </div>


                <div class="bg-gray-50 px-6 py-4 flex flex-col md:flex-row justify-between items-center border-t gap-4">

                    <a :href="route('phieu.print', phieu.id)" target="_blank"
                        class="flex items-center gap-1 text-gray-600 hover:text-blue-600 font-medium transition">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75v-4.125c0-.621-.504-1.125-1.125-1.125H5.375c-.621 0-1.125.504-1.125 1.125v4.125c0 .621.504 1.125 1.125 1.125H6.358m12.284 0h.008v.008h-.008V18zM6.75 6H17.25a2.25 2.25 0 012.25 2.25v2.25H4.5v-2.25A2.25 2.25 0 016.75 6zM5.25 9h13.5" />
                        </svg>
                        In phiếu PDF
                    </a>

                    <div v-if="canApprove" class="flex flex-col items-end gap-2 w-full md:w-auto">
                        <input v-model="ghiChu" type="text" placeholder="Ghi chú (Tùy chọn, bắt buộc nếu Từ chối)..." class="text-sm border-gray-300 rounded w-full md:w-64">
                        <div class="flex gap-2">
                            <button @click="xuLyPhieu('tu_choi')" class="bg-red-100 text-red-700 px-4 py-2 rounded font-bold hover:bg-red-200 text-sm transition">Từ chối</button>
                            <button @click="xuLyPhieu('duyet')" class="bg-blue-600 text-white px-6 py-2 rounded font-bold hover:bg-blue-700 shadow text-sm transition">Phê duyệt</button>
                        </div>
                    </div>
                    <div v-else-if="canThanhToan" class="flex gap-2">
                        <Link :href="route('vnpay.create', phieu.id)" method="post" as="button"
                            class="bg-emerald-600 text-white px-6 py-2 rounded font-bold hover:bg-emerald-700 shadow text-sm transition">
                            Tiến hành Thanh toán VNPAY
                        </Link>
                    </div>

                    <div v-else-if="canCancel" class="flex flex-col items-end gap-2">
                        <input v-model="ghiChu" type="text" placeholder="Lý do hủy..." class="text-sm border-gray-300 rounded w-full md:w-64">
                        <button @click="huyPhieu" class="bg-slate-200 text-slate-700 px-4 py-2 rounded font-bold hover:bg-slate-300 text-sm transition">Thu hồi phiếu</button>
                    </div>

                    <div v-else class="italic text-gray-500 text-sm font-medium">
                        Phiếu đang ở trạng thái: {{ phieu.trang_thai_label }}
                    </div>

                </div>
            </div>
        </div>
    </div>
</template>
