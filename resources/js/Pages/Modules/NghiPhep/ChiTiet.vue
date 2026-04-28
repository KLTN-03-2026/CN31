<script setup>
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

import StatusBadge from '@/Components/UI/StatusBadge.vue';

const props = defineProps({
    phieu: Object
});

const page = usePage();
const user = computed(() => page.props.auth.user);

// 1. LOGIC PHÂN QUYỀN NGHỈ PHÉP
const canApproveTruongPhong = computed(() => {
    return user.value.vai_tro === 'truong_phong' && props.phieu.trang_thai_color === 'yellow';
});

const canApproveNhanSu = computed(() => {
    return user.value.vai_tro === 'nhan_su' && props.phieu.trang_thai_color === 'pink';
});

const canCancel = computed(() => {
    return user.value.name === props.phieu.nguoi_tao && props.phieu.trang_thai_color === 'yellow';
});

const getTenLoaiPhep = (maLoai) => {
    const map = {
        'nghi_phep_nam': 'Phép năm (Có lương)',
        'nghi_om': 'Nghỉ ốm (Hưởng BHXH)',
        'viec_rieng': 'Việc riêng (Không lương)',
        'che_do': 'Nghỉ chế độ (Thai sản, Cưới hỏi...)'
    };
    return map[maLoai] || maLoai;
};

// 2. XỬ LÝ PHÊ DUYỆT (OPTIMISTIC UI)
const ghiChu = ref('');
const isProcessingApprove = ref(false);
const showTuChoiModal = ref(false);

const confirmTuChoi = () => {
    if (!ghiChu.value.trim()) {
        alert('Vui lòng nhập lý do từ chối!');
        return;
    }
    xuLyPhieu('tu_choi');
};

const xuLyPhieu = (hanhDong) => {
    isProcessingApprove.value = true;
    router.post(route('phieu.duyet', props.phieu.id), {
        hanh_dong: hanhDong, ghi_chu: ghiChu.value
    }, {
        preserveScroll: true,
        onFinish: () => { isProcessingApprove.value = false; showTuChoiModal.value = false; }
    });
};

const isProcessingCancel = ref(false);
const huyPhieu = () => {
    if (!confirm('Bạn có chắc chắn muốn THU HỒI đơn này không?')) return;
    isProcessingCancel.value = true;
    router.post(route('phieu.cancel', props.phieu.id), { ghi_chu: ghiChu.value }, {
        preserveScroll: true,
        onFinish: () => { isProcessingCancel.value = false; }
    });
};

// Format Link tự động
const formatGhiChu = (text) => {
    if (!text) return 'Không có ghi chú.';
    const urlRegex = /(https?:\/\/[^\s]+)/g;
    return text.replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(urlRegex, (url) =>
        `<a href="${url}" target="_blank" class="text-pink-600 font-bold hover:underline">Xem Link</a>`
    );
};
</script>

<template>
    <Head :title="`Chi tiết ${phieu.ma_phieu}`" />

    <div class="py-6 min-h-screen">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">

            <div class="mb-4">
                <Link :href="route('dashboard')" class="text-slate-500 hover:text-slate-900 font-semibold text-sm flex items-center gap-1 transition-colors w-fit">
                    &larr; Quay lại Dashboard
                </Link>
            </div>

            <div class="flex flex-col lg:flex-row gap-5">

                <div class="lg:w-2/3 space-y-5">

                    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                        <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row justify-between items-start gap-4">
                            <div>
                                <div class="flex items-center gap-3 mb-2">
                                    <span class="font-mono text-[11px] font-bold text-pink-700 bg-pink-50 px-2 py-0.5 rounded">{{ phieu.ma_phieu }}</span>
                                    <span class="text-xs text-slate-400 font-medium">{{ phieu.ngay_tao }}</span>
                                </div>
                                <h1 class="text-2xl font-black text-slate-900 leading-tight">{{ phieu.tieu_de }}</h1>
                                <div class="mt-3 flex items-center gap-2 text-xs text-slate-600">
                                    <div class="w-5 h-5 rounded-full bg-slate-100 flex items-center justify-center border border-slate-200 font-bold text-[9px] uppercase">{{ phieu.nguoi_tao[0] }}</div>
                                    <span>Người nộp đơn: <strong class="text-slate-800">{{ phieu.nguoi_tao }}</strong></span>
                                </div>
                            </div>

                            <div class="shrink-0 pt-1">
                                <StatusBadge :label="phieu.trang_thai_label" :color="phieu.trang_thai_color" class="!px-4 !py-1.5 shadow-sm" />
                            </div>
                        </div>
                        <div class="p-5 bg-slate-50/50">
                            <h3 class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Lý do nghỉ / Chi tiết bàn giao công việc</h3>
                            <div class="text-slate-800 text-sm leading-relaxed" v-html="formatGhiChu(phieu.ly_do)"></div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
                        <h3 class="font-bold text-slate-800 mb-5 uppercase text-xs tracking-widest">Lịch sử xét duyệt</h3>

                        <div class="space-y-6 relative border-l-2 border-pink-100 ml-3">
                            <div v-for="(log, idx) in phieu.nhat_ky" :key="log.id" class="relative pl-6 group">
                                <span class="absolute flex items-center justify-center w-6 h-6 bg-pink-50 rounded-full -left-[13px] -top-0.5 ring-4 ring-white transition-transform duration-300 group-hover:scale-110">
                                    <div class="w-2.5 h-2.5 rounded-full shadow-sm"
                                         :class="[
                                             idx === 0 && phieu.trang_thai_color === 'green' ? 'bg-emerald-500' :
                                             idx === 0 && phieu.trang_thai_color === 'red' ? 'bg-red-400' :
                                             'bg-pink-400'
                                         ]">
                                    </div>
                                </span>

                                <div class="flex justify-between items-start mb-0.5">
                                    <h4 class="text-sm font-bold text-slate-900">{{ log.hanh_dong_label }}</h4>
                                    <span class="text-[10px] font-medium text-slate-400">{{ log.thoi_gian }}</span>
                                </div>
                                <p class="text-[11px] text-slate-500 mb-1">Bởi: <strong class="text-slate-700">{{ log.nguoi_thuc_hien }}</strong></p>
                                <div v-if="log.ghi_chu" class="text-xs text-slate-600 bg-slate-50 p-2.5 rounded-lg border border-slate-100 italic mt-1.5">
                                    "{{ log.ghi_chu }}"
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="lg:w-1/3 space-y-5">

                    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                        <div class="px-5 py-3 border-b border-slate-100 bg-slate-50">
                            <h3 class="text-[11px] font-bold text-slate-600 uppercase tracking-wider">Thông tin nghỉ phép</h3>
                        </div>
                        <div class="p-5 space-y-4">
                            <div>
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wide mb-1.5">Phân loại</p>
                                <div class="inline-flex items-center px-2.5 py-1 rounded-md bg-pink-50 text-pink-700 font-bold text-xs border border-pink-100">
                                    {{ getTenLoaiPhep(phieu.chi_tiet_nghi_phep?.loai_nghi_phep) }}
                                </div>
                            </div>

                            <div class="flex justify-between items-center bg-slate-50 p-3 rounded-lg border border-slate-100">
                                <div>
                                    <p class="text-[9px] font-bold text-slate-400 uppercase mb-0.5">Từ ngày</p>
                                    <p class="font-bold text-slate-900 text-sm">{{ phieu.chi_tiet_nghi_phep?.ngay_bat_dau }}</p>
                                </div>
                                <div class="text-slate-300">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                                </div>
                                <div class="text-right">
                                    <p class="text-[9px] font-bold text-slate-400 uppercase mb-0.5">Đến hết ngày</p>
                                    <p class="font-bold text-slate-900 text-sm">{{ phieu.chi_tiet_nghi_phep?.ngay_ket_thuc }}</p>
                                </div>
                            </div>

                            <div class="flex justify-between items-end border-t border-slate-100 pt-3">
                                <span class="text-[13px] font-bold text-slate-500">Tổng cộng:</span>
                                <span class="text-xl font-black text-pink-600 leading-none">{{ phieu.chi_tiet_nghi_phep?.so_ngay_nghi }} <span class="text-[9px] uppercase font-bold text-pink-400 tracking-wider">ngày</span></span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                        <div class="px-5 py-3 border-b border-slate-100 bg-slate-50">
                            <h3 class="text-[11px] font-bold text-slate-600 uppercase tracking-wider">Bàn giao công việc</h3>
                        </div>
                        <div class="p-5 flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 font-bold border border-slate-200 shrink-0">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                            </div>
                            <div class="min-w-0">
                                <p class="text-[10px] font-bold text-slate-400 uppercase">Người nhận bàn giao</p>
                                <p class="font-bold text-slate-800 text-sm mt-0.5 truncate">{{ phieu.chi_tiet_nghi_phep?.nguoi_ban_giao || 'Không có' }}</p>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <div class="mt-6 flex flex-col sm:flex-row justify-between items-center gap-4 bg-white p-4 rounded-xl shadow-sm border border-slate-200 sticky bottom-4 z-40">

                <div class="text-[11px] text-slate-500 font-bold bg-slate-50 px-4 py-2.5 rounded-lg border border-slate-200 uppercase tracking-wider w-full sm:w-auto text-center sm:text-left">
                    Quy trình: <span class="text-slate-800">Trưởng phòng</span> &rarr; <span class="text-pink-600">Nhân sự (HR)</span>
                </div>

                <div v-if="canApproveTruongPhong || canApproveNhanSu" class="flex gap-2 w-full sm:w-auto">
                    <button @click="showTuChoiModal = true" :disabled="isProcessingApprove" class="flex-1 sm:flex-none px-6 py-2.5 bg-white text-red-600 border border-slate-200 rounded-lg font-bold hover:bg-red-50 transition-all text-sm">
                        Từ chối
                    </button>
                    <button @click="xuLyPhieu('duyet')" :disabled="isProcessingApprove" class="flex-1 sm:flex-none px-5 py-2.5 bg-pink-600 text-white rounded-lg font-bold shadow-sm hover:bg-pink-700 transition-all flex items-center justify-center gap-2 text-sm w-40">
                        <svg v-if="isProcessingApprove" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        <span v-else>Phê Duyệt Đơn</span>
                    </button>
                </div>

                <div v-else-if="canCancel" class="flex gap-2 w-full sm:w-auto">
                    <button @click="huyPhieu" :disabled="isProcessingCancel" class="w-full sm:w-auto px-6 py-2.5 bg-white border border-slate-300 text-slate-700 rounded-lg font-bold hover:bg-red-50 hover:text-red-600 hover:border-red-200 transition-colors shadow-sm text-sm">
                        {{ isProcessingCancel ? 'Đang thu hồi...' : 'Thu hồi đơn' }}
                    </button>
                </div>
            </div>

            <div v-if="showTuChoiModal" class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/40 backdrop-blur-sm p-4">
                <div class="bg-white rounded-xl shadow-xl w-full max-w-md overflow-hidden">
                    <div class="p-5 border-b border-slate-100 bg-red-50 flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-red-100 text-red-600 flex items-center justify-center"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg></div>
                        <h3 class="text-base font-bold text-red-900">Xác nhận Từ chối</h3>
                    </div>
                    <div class="p-5">
                        <label class="block text-sm font-bold text-slate-700 mb-2">Lý do từ chối <span class="text-red-500">*</span></label>
                        <textarea v-model="ghiChu" rows="3" class="w-full border-slate-200 rounded-lg text-sm focus:ring-red-500 focus:border-red-500" placeholder="Nhập lý do để người tạo đơn cập nhật..."></textarea>
                    </div>
                    <div class="px-5 py-3 bg-slate-50 flex justify-end gap-2 border-t border-slate-100">
                        <button @click="showTuChoiModal = false" class="text-sm font-bold text-slate-500 px-3 py-1.5 hover:text-slate-800">Hủy</button>
                        <button @click="confirmTuChoi" class="bg-red-600 text-white px-5 py-2 rounded-lg text-sm font-bold hover:bg-red-700">Xác nhận Từ chối</button>
                    </div>
                </div>
            </div>

        </div>
    </div>
</template>
