<script setup>
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    phieu: Object
});

const page = usePage();
const user = computed(() => page.props.auth.user);

// --- 1. LOGIC PHÂN QUYỀN NGHỈ PHÉP ---
const canApproveTruongPhong = computed(() => {
    return user.value.vai_tro === 'truong_phong' && props.phieu.trang_thai_color === 'yellow';
});

const canApproveNhanSu = computed(() => {
    return user.value.vai_tro === 'nhan_su' && props.phieu.trang_thai_color === 'pink'; // Trạng thái CHO_NHAN_SU_DUYET
});

const canCancel = computed(() => {
    return user.value.name === props.phieu.nguoi_tao && props.phieu.trang_thai_color === 'yellow';
});

// Hàm map tên Loại nghỉ phép cho đẹp
const getTenLoaiPhep = (maLoai) => {
    const map = {
        'phep_nam': 'Phép năm (Có lương)',
        'nghi_om': 'Nghỉ ốm (Hưởng BHXH)',
        'viec_rieng': 'Việc riêng (Không lương)',
        'che_do': 'Nghỉ chế độ (Thai sản, Cưới hỏi...)'
    };
    return map[maLoai] || maLoai;
};

// --- 2. HÀM XỬ LÝ (ACTION) ---
const ghiChu = ref('');

const xuLyPhieu = (hanhDong) => {
    let msg = hanhDong === 'duyet' ? 'DUYỆT' : 'TỪ CHỐI';
    if (!confirm(`Bạn chắc chắn muốn ${msg} đơn xin nghỉ phép này?`)) return;

    router.post(route('phieu.duyet', props.phieu.id), {
        hanh_dong: hanhDong,
        ghi_chu: ghiChu.value
    });
};

const huyPhieu = () => {
    if (!confirm('Bạn có chắc chắn muốn THU HỒI đơn này không?')) return;
    router.post(route('phieu.cancel', props.phieu.id), { ghi_chu: ghiChu.value });
};

// Hàm xử lý link tự động
const formatGhiChu = (text) => {
    if (!text) return 'Không có ghi chú.';
    let safeText = text.replace(/</g, "&lt;").replace(/>/g, "&gt;");
    const urlRegex = /(https?:\/\/[^\s]+)/g;
    return safeText.replace(urlRegex, (url) => {
        return `<a href="${url}" target="_blank" class="text-pink-600 hover:text-pink-800 underline font-bold inline-flex items-center gap-1 bg-pink-50 px-2 py-0.5 rounded transition">Xem Link</a>`;
    });
};
</script>

<template>
    <Head :title="`Chi tiết ${phieu.ma_phieu}`" />

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

            <div class="mb-4">
                <Link :href="route('dashboard')" class="text-gray-500 hover:text-pink-600 font-medium flex items-center gap-1 transition">
                    &larr; Quay lại Dashboard
                </Link>
            </div>

            <div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl border border-gray-100">

                <div class="p-8 border-b border-gray-100 bg-rose-50/30 flex justify-between items-start rounded-t-2xl">
                    <div class="flex gap-4">
                        <div class="bg-pink-100 p-3 rounded-full text-pink-600 h-fit mt-1">
                            <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-2xl font-extrabold text-gray-900">{{ phieu.tieu_de }}</h1>
                            <p class="text-sm text-gray-600 mt-2">
                                Mã đơn: <span class="font-mono font-bold text-pink-600 bg-pink-50 px-2 py-0.5 rounded border border-pink-100">{{ phieu.ma_phieu }}</span>
                            </p>
                            <p class="text-sm text-gray-600 mt-1">
                                Người nộp đơn: <span class="font-bold text-gray-800">{{ phieu.nguoi_tao }}</span> <span class="text-gray-300 mx-1">|</span> Nộp lúc: {{ phieu.ngay_tao }}
                            </p>
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="px-4 py-1.5 rounded-full text-sm font-bold border shadow-sm" :class="{
                            'bg-gray-100 text-gray-800 border-gray-300': phieu.trang_thai_color === 'gray',
                            'bg-yellow-100 text-yellow-800 border-yellow-300': phieu.trang_thai_color === 'yellow',
                            'bg-pink-100 text-pink-800 border-pink-300': phieu.trang_thai_color === 'pink',
                            'bg-green-100 text-green-800 border-green-300': phieu.trang_thai_color === 'green',
                            'bg-red-100 text-red-800 border-red-300': phieu.trang_thai_color === 'red',
                            'bg-slate-100 text-slate-800 border-slate-300': phieu.trang_thai_color === 'slate',
                        }">
                            {{ phieu.trang_thai_label }}
                        </span>
                    </div>
                </div>

                <div class="p-8 bg-white">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">

                        <div class="bg-gray-50 rounded-xl p-5 border border-gray-100">
                            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-4">Thời gian nghỉ</h3>

                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <p class="text-sm text-gray-500 mb-1">Từ ngày</p>
                                    <p class="text-lg font-bold text-gray-900">{{ phieu.chi_tiet_nghi_phep?.ngay_bat_dau }}</p>
                                </div>
                                <div class="text-gray-300">
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm text-gray-500 mb-1">Đến hết ngày</p>
                                    <p class="text-lg font-bold text-gray-900">{{ phieu.chi_tiet_nghi_phep?.ngay_ket_thuc }}</p>
                                </div>
                            </div>

                            <div class="border-t border-gray-200 pt-3 flex justify-between items-center">
                                <span class="text-sm font-medium text-gray-600">Tổng số ngày nghỉ:</span>
                                <span class="text-xl font-black text-pink-600">{{ phieu.chi_tiet_nghi_phep?.so_ngay_nghi }} <span class="text-sm font-medium text-gray-500">ngày</span></span>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Phân loại</h3>
                                <div class="inline-flex items-center px-3 py-1 rounded-md bg-purple-50 text-purple-700 font-bold border border-purple-100">
                                    {{ getTenLoaiPhep(phieu.chi_tiet_nghi_phep?.loai_nghi_phep) }}
                                </div>
                            </div>

                            <div>
                                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1 mt-4">Người nhận bàn giao</h3>
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center text-gray-600 font-bold">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                    </div>
                                    <span class="font-bold text-gray-800">{{ phieu.chi_tiet_nghi_phep?.nguoi_ban_giao }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="min-w-0">
                        <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Lý do nghỉ / Chi tiết bàn giao công việc</h3>
                        <div class="text-gray-800 font-medium bg-rose-50/50 p-4 rounded-xl border border-rose-100 min-h-[80px] break-words whitespace-pre-wrap leading-relaxed"
                             v-html="formatGhiChu(phieu.ly_do)">
                        </div>
                    </div>
                </div>

                <div class="p-8 border-t border-gray-100 bg-white">
                    <h3 class="text-lg font-bold text-gray-800 mb-6 flex items-center gap-2">
                        <svg class="w-6 h-6 text-pink-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        Lịch sử xét duyệt
                    </h3>

                    <div class="relative border-l-2 border-gray-100 ml-4 mt-4">
                        <div v-for="(log, index) in phieu.nhat_ky" :key="log.id" class="mb-8 ml-6 relative group">
                            <span class="absolute flex items-center justify-center w-6 h-6 bg-pink-100 rounded-full -left-9 ring-8 ring-white transition-transform group-hover:scale-110">
                                <div class="w-2.5 h-2.5 bg-pink-500 rounded-full" :class="{'bg-green-500': index === 0 && phieu.trang_thai_color === 'green', 'bg-red-500': phieu.trang_thai_color === 'red' && index === 0}"></div>
                            </span>

                            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-1">
                                <h4 class="font-bold text-gray-900 text-base flex items-center gap-2">
                                    {{ log.hanh_dong_label }}
                                    <span v-if="index === 0" class="bg-pink-100 text-pink-800 text-[10px] font-extrabold px-2 py-0.5 rounded uppercase">Mới nhất</span>
                                </h4>
                                <time class="text-sm font-medium text-gray-500 mt-1 sm:mt-0 bg-gray-50 px-2 py-1 rounded">{{ log.thoi_gian }}</time>
                            </div>

                            <p class="text-sm text-gray-700 mt-1">
                                Thực hiện bởi: <span class="font-bold text-gray-900 bg-gray-100 px-2 py-0.5 rounded">{{ log.nguoi_thuc_hien }}</span>
                            </p>

                            <div v-if="log.ghi_chu" class="mt-3 text-sm text-gray-700 bg-gray-50 border border-gray-200 p-4 rounded-xl rounded-tl-none italic relative shadow-sm">
                                <span class="font-bold not-italic text-gray-900">Ghi chú:</span> "{{ log.ghi_chu }}"
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-slate-50 px-8 py-5 flex flex-col md:flex-row justify-between items-center border-t border-gray-200 gap-4 rounded-b-2xl">
                    <div class="text-sm text-gray-500 font-medium">
                        Quy trình: <span class="font-bold text-gray-700">Trưởng phòng duyệt</span> &rarr; <span class="font-bold text-pink-600">Nhân sự (HR) duyệt</span>
                    </div>

                    <div v-if="canApproveTruongPhong || canApproveNhanSu" class="flex flex-col md:flex-row items-center gap-3 w-full md:w-auto">
                        <input v-model="ghiChu" type="text" placeholder="Ghi chú (Bắt buộc nếu Từ chối)..." class="text-sm border-gray-300 focus:border-pink-500 focus:ring-pink-500 rounded-lg w-full md:w-64 py-2.5 shadow-sm">
                        <div class="flex gap-2 w-full md:w-auto">
                            <button @click="xuLyPhieu('tu_choi')" class="flex-1 md:flex-none bg-white text-red-600 border border-red-200 px-5 py-2.5 rounded-lg font-bold hover:bg-red-50 transition shadow-sm">Từ chối</button>
                            <button @click="xuLyPhieu('duyet')" class="flex-1 md:flex-none bg-pink-600 text-white px-8 py-2.5 rounded-lg font-extrabold hover:bg-pink-700 shadow-md hover:shadow-lg transition">Phê Duyệt Đơn</button>
                        </div>
                    </div>

                    <div v-else-if="canCancel" class="flex flex-col md:flex-row items-center gap-3 w-full md:w-auto">
                        <input v-model="ghiChu" type="text" placeholder="Lý do hủy..." class="text-sm border-gray-300 focus:border-red-500 focus:ring-red-500 rounded-lg w-full md:w-64 py-2.5 shadow-sm">
                        <button @click="huyPhieu" class="w-full md:w-auto bg-white border border-gray-300 text-gray-700 px-6 py-2.5 rounded-lg font-bold hover:bg-gray-50 hover:text-red-600 transition shadow-sm">Thu hồi đơn</button>
                    </div>

                    <div v-else class="italic text-gray-500 text-sm font-medium bg-gray-100 px-4 py-2 rounded-lg border border-gray-200">
                        Trạng thái hiện tại: <span class="font-bold text-gray-700">{{ phieu.trang_thai_label }}</span>
                    </div>

                </div>
            </div>
        </div>
    </div>
</template>
