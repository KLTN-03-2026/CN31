<script setup>
import { Head, Link, usePage, router, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

// 1. KHAI BÁO PROPS ĐẦU TIÊN
const props = defineProps({
    phieu: Object,
    nhaCungCaps: Array,
    nganSach: Object
});

const page = usePage();
const user = computed(() => page.props.auth.user);

// --- BIẾN CHO MODAL NHẬN HÀNG (MINI GRN) ---
const showNhanHangModal = ref(false);

const nhanHangForm = useForm({
    file_nhan_hang: null,
    ghi_chu_nhan_hang: ''
});

// Hàm check quyền: Chỉ hiện nút khi là Người tạo phiếu VÀ phiếu Đã thanh toán
const canXacNhanNhanHang = computed(() => {
    return props.phieu.nguoi_tao === user.value.name && props.phieu.trang_thai_label === 'Đã thanh toán';
});

// Hàm xử lý upload file
const handleFileNhanHang = (e) => {
    nhanHangForm.file_nhan_hang = e.target.files[0];
};

// Hàm submit nhận hàng
const submitNhanHang = () => {
    nhanHangForm.post(route('phieu.nhan_hang', props.phieu.id), {
        preserveScroll: true,
        onSuccess: () => {
            showNhanHangModal.value = false;
            nhanHangForm.reset();
        }
    });
};

// --- LOGIC PHÂN QUYỀN ĐA CẤP CŨ ---
const canApprove = computed(() => {
    if (user.value.vai_tro === 'truong_phong' && props.phieu.trang_thai_color === 'yellow') return true;
    if (user.value.vai_tro === 'giam_doc' && props.phieu.trang_thai_color === 'orange') return true;
    return false;
});

const canThanhToan = computed(() => {
    return user.value.vai_tro === 'ke_toan' && props.phieu.trang_thai_color === 'blue';
});

const canCancel = computed(() => {
    return user.value.name === props.phieu.nguoi_tao && props.phieu.trang_thai_color === 'yellow';
});

const canUpdateBaoGia = computed(() => {
    return user.value.vai_tro === 'nhan_vien_mua_sam' && props.phieu.trang_thai_color === 'purple';
});

// --- FORM DÀNH CHO MUA SẮM ---
const baoGiaForm = useForm({
    nha_cung_cap_id: '',
    file_bao_gia: null,
    san_pham: props.phieu.chi_tiet.map(sp => ({
        id: sp.id,
        ten_san_pham: sp.ten_san_pham,
        so_luong: sp.so_luong,
        don_gia: sp.don_gia || 0
    }))
});

const tongTienTamTinh = computed(() => {
    return baoGiaForm.san_pham.reduce((total, item) => total + (item.so_luong * (item.don_gia || 0)), 0);
});

// --- HÀM XỬ LÝ (ACTION) ---
const ghiChu = ref('');

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
    router.post(route('phieu.cancel', props.phieu.id), { ghi_chu: ghiChu.value });
};

const capNhatBaoGia = () => {
    if (tongTienTamTinh.value === 0) {
        alert('Vui lòng nhập giá cho các sản phẩm!');
        return;
    }
    baoGiaForm.post(route('phieu.bao_gia', props.phieu.id), {
        preserveScroll: true,
        onSuccess: () => {
            if (tongTienTamTinh.value <= 20000000) {
                alert('Đã cập nhật báo giá thành công! Phiếu đã chuyển cho Kế toán thanh toán.');
            } else {
                alert('Đã cập nhật báo giá thành công! Phiếu đã chuyển cho Giám đốc duyệt.');
            }
        }
    });
};

// --- HÀM XỬ LÝ TEXT TỰ ĐỘNG NHẬN DIỆN LINK ---
const formatGhiChu = (text) => {
    if (!text) return 'Không có ghi chú.';
    let safeText = text.replace(/</g, "&lt;").replace(/>/g, "&gt;");
    const urlRegex = /(https?:\/\/[^\s]+)/g;
    return safeText.replace(urlRegex, (url) => {
        return `<a href="${url}" target="_blank" class="text-blue-600 hover:text-blue-800 underline font-bold inline-flex items-center gap-1 bg-blue-50 px-2 py-0.5 rounded transition">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
            </svg>
            Xem Link
        </a>`;
    });
};

// --- QUẢN LÝ TRẠNG THÁI MODAL THANH TOÁN ---
const showPaymentModal = ref(false);
</script>

<template>
    <Head :title="`Chi tiết ${phieu.ma_phieu}`" />

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">

            <div class="mb-4">
                <Link :href="route('dashboard')" class="text-gray-500 hover:text-blue-600 font-medium flex items-center gap-1 transition">
                    &larr; Quay lại Dashboard
                </Link>
            </div>

            <div class="bg-white overflow-hidden shadow-xl sm:rounded-xl border border-gray-100 relative">

                <div class="p-6 border-b border-gray-200 bg-slate-50 flex justify-between items-start rounded-t-xl">
                    <div>
                        <h1 class="text-2xl font-extrabold text-gray-900">{{ phieu.tieu_de }}</h1>
                        <p class="text-sm text-gray-600 mt-2">
                            Mã phiếu: <span class="font-mono font-bold text-blue-600 bg-blue-50 px-2 py-0.5 rounded">{{ phieu.ma_phieu }}</span>
                        </p>
                        <p class="text-sm text-gray-600 mt-1">
                            Người tạo: <span class="font-bold text-gray-800">{{ phieu.nguoi_tao }}</span> <span class="text-gray-400">|</span> {{ phieu.ngay_tao }}
                        </p>
                    </div>
                    <div class="text-right">
                        <span class="px-4 py-1.5 rounded-full text-sm font-bold border shadow-sm" :class="{
                            'bg-gray-100 text-gray-800 border-gray-300': phieu.trang_thai_color === 'gray',
                            'bg-yellow-100 text-yellow-800 border-yellow-300': phieu.trang_thai_color === 'yellow',
                            'bg-purple-100 text-purple-800 border-purple-300': phieu.trang_thai_color === 'purple',
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

                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6 bg-white">
                    <div class="min-w-0">
                        <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Lý do / Ghi chú</h3>
                        <p class="text-gray-800 font-medium bg-gray-50 p-3 rounded-lg border border-gray-100 min-h-[60px] break-words whitespace-pre-wrap"
                        v-html="formatGhiChu(phieu.ly_do)">
                        </p>
                    </div>
                    <div class="min-w-0">
                        <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Thông tin Mua sắm</h3>
                        <div class="bg-gray-50 p-3 rounded-lg border border-gray-100 min-h-[60px]">
                            <p class="text-sm text-gray-600 mb-1">Nhà cung cấp: <span class="font-bold text-gray-900">{{ phieu.nha_cung_cap }}</span></p>
                            <p v-if="phieu.file_bao_gia" class="text-sm mt-1">
                                <a :href="phieu.file_bao_gia" target="_blank" class="text-blue-600 font-bold hover:underline inline-flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" /></svg>
                                    Xem File PDF Báo Giá
                                </a>
                            </p>
                            <p v-else class="text-sm text-gray-500 italic mt-1">Chưa có file báo giá đính kèm.</p>
                        </div>
                    </div>
                </div>

                <div v-if="phieu.file_nhan_hang" class="p-6 bg-green-50 border-t border-green-100">
                    <h3 class="text-xs font-bold text-green-700 uppercase tracking-wider mb-4 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        Chứng từ Nghiệm thu / Nhận hàng
                    </h3>
                    <div class="bg-white p-4 rounded-xl border border-green-200 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div>
                            <p class="text-sm text-gray-700 mb-1"><strong class="text-gray-900">Ghi chú tình trạng:</strong> {{ phieu.ghi_chu_nhan_hang || 'Không có ghi chú' }}</p>
                        </div>
                        <a :href="phieu.file_nhan_hang" target="_blank" class="shrink-0 text-sm text-white bg-green-600 hover:bg-green-700 px-4 py-2 rounded-lg font-bold flex items-center gap-2 transition shadow-sm">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
                            Tải / Xem file đính kèm
                        </a>
                    </div>
                </div>

                <div v-if="nganSach" class="p-6 bg-white border-t border-gray-100">
                    <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4 flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        Ngân sách {{ nganSach.ten_phong }} (Năm nay)
                    </h3>

                    <div class="bg-gray-50 rounded-xl p-5 border border-gray-200">
                        <div class="flex justify-between items-end mb-2">
                            <div>
                                <span class="text-3xl font-black"
                                    :class="nganSach.phan_tram > 90 ? 'text-red-600' : (nganSach.phan_tram > 75 ? 'text-orange-500' : 'text-emerald-600')">
                                    {{ nganSach.phan_tram }}%
                                </span>
                                <span class="text-sm text-gray-500 font-medium ml-1">đã sử dụng</span>
                            </div>
                            <div class="text-right">
                                <p class="text-xs text-gray-500 font-bold uppercase mb-1">Còn lại (Khả dụng)</p>
                                <p class="text-lg font-bold text-gray-900">{{ Number(nganSach.con_lai).toLocaleString() }} đ</p>
                            </div>
                        </div>

                        <div class="w-full bg-gray-200 rounded-full h-3 mb-4 overflow-hidden shadow-inner">
                            <div class="h-3 rounded-full transition-all duration-1000 ease-out"
                                :class="nganSach.phan_tram > 90 ? 'bg-red-500' : (nganSach.phan_tram > 75 ? 'bg-orange-500' : 'bg-emerald-500')"
                                :style="`width: ${nganSach.phan_tram > 100 ? 100 : nganSach.phan_tram}%`">
                            </div>
                        </div>

                        <div class="flex justify-between text-xs font-medium text-gray-500">
                            <span>Đã chi: {{ Number(nganSach.da_dung).toLocaleString() }} đ</span>
                            <span>Tổng cấp: {{ Number(nganSach.tong).toLocaleString() }} đ</span>
                        </div>
                    </div>
                </div>

                <div class="p-6 border-t border-gray-100">
                    <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
                        Danh sách Hàng hóa
                    </h3>

                    <div class="overflow-x-auto rounded-lg border border-gray-200">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Sản phẩm</th>
                                    <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase">Số lượng</th>
                                    <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase">Đơn giá</th>
                                    <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase">Thành tiền</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="sp in phieu.chi_tiet" :key="sp.id" class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-gray-900">{{ sp.ten_san_pham }}</div>
                                        <div class="text-xs text-gray-500 mt-1">{{ sp.danh_muc.ten_danh_muc }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-center font-bold text-gray-700">{{ sp.so_luong }}</td>
                                    <td class="px-6 py-4 text-right">
                                        <span v-if="sp.don_gia" class="text-gray-900">{{ Number(sp.don_gia).toLocaleString() }} đ</span>
                                        <span v-else class="text-orange-500 text-xs italic bg-orange-50 px-2 py-1 rounded">Chờ báo giá</span>
                                    </td>
                                    <td class="px-6 py-4 text-right font-bold">
                                        <span v-if="sp.thanh_tien" class="text-blue-700">{{ Number(sp.thanh_tien).toLocaleString() }} đ</span>
                                        <span v-else class="text-gray-300">-</span>
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot v-if="phieu.tong_tien" class="bg-slate-50 border-t-2 border-gray-200">
                                <tr>
                                    <td colspan="3" class="px-6 py-4 text-right font-extrabold text-gray-900 uppercase">Tổng cộng:</td>
                                    <td class="px-6 py-4 text-right font-extrabold text-green-600 text-lg">{{ Number(phieu.tong_tien).toLocaleString() }} đ</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

              <div v-if="canUpdateBaoGia" class="p-6 bg-purple-50 border-t-4 border-purple-500 m-6 rounded-xl shadow-inner relative overflow-hidden">
                    <svg class="absolute top-0 right-0 w-32 h-32 text-purple-100 transform translate-x-8 -translate-y-8" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>

                    <h3 class="text-xl font-black text-purple-800 mb-6 flex items-center gap-2 relative z-10">
                        <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2zM10 8.5a.5.5 0 11-1 0 .5.5 0 011 0zm5 5a.5.5 0 11-1 0 .5.5 0 011 0z" /></svg>
                        Phòng Mua Sắm: Xử lý Đơn hàng
                    </h3>

                    <div class="flex flex-col lg:flex-row gap-6 relative z-10">
                        <div class="lg:w-1/3 bg-white p-5 rounded-xl border border-purple-100 shadow-sm h-fit">
                            <h4 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-4 border-b pb-2 flex items-center gap-2">
                                <svg class="w-4 h-4 text-purple-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z" /></svg>
                                Yêu cầu Giao hàng
                            </h4>
                            <div class="space-y-3">
                                <div>
                                    <p class="text-xs text-gray-400">Người nhận hàng:</p>
                                    <p class="font-bold text-gray-800">{{ phieu.nguoi_tao }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-400">Địa chỉ nhận (Mặc định):</p>
                                    <p class="font-bold text-gray-800">Văn phòng Công ty (Trụ sở chính)</p>
                                </div>
                                <div class="bg-blue-50 p-3 rounded-lg border border-blue-100 mt-2">
                                    <p class="text-xs text-blue-800 font-medium">💡 <span class="font-bold">Ghi chú cho Mua sắm:</span> Hãy cung cấp thông tin người nhận này cho Nhà cung cấp để shipper liên hệ khi giao hàng.</p>
                                </div>
                            </div>
                        </div>

                        <div class="lg:w-2/3">
                            <form @submit.prevent="capNhatBaoGia">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                    <div>
                                        <label class="block font-bold text-sm text-purple-900 mb-2">Chọn Nhà cung cấp tốt nhất <span class="text-red-500">*</span></label>
                                        <select v-model="baoGiaForm.nha_cung_cap_id" class="w-full rounded-lg border-purple-200 focus:border-purple-500 focus:ring-purple-500 py-2.5 bg-white shadow-sm" required>
                                            <option value="" disabled>-- Chọn nhà cung cấp --</option>
                                            <option v-for="ncc in nhaCungCaps" :key="ncc.id" :value="ncc.id">{{ ncc.ten_nha_cung_cap }}</option>
                                        </select>
                                        <div v-if="baoGiaForm.errors.nha_cung_cap_id" class="text-red-500 text-xs mt-1 font-bold">{{ baoGiaForm.errors.nha_cung_cap_id }}</div>
                                    </div>

                                    <div>
                                        <label class="block font-bold text-sm text-purple-900 mb-2">Tải lên File Báo giá (PDF)</label>
                                        <input type="file" @input="baoGiaForm.file_bao_gia = $event.target.files[0]" accept="application/pdf" class="w-full text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-bold file:bg-purple-100 file:text-purple-700 hover:file:bg-purple-200 cursor-pointer bg-white border border-purple-200 rounded-lg shadow-sm" />
                                        <div v-if="baoGiaForm.errors.file_bao_gia" class="text-red-500 text-xs mt-1 font-bold">{{ baoGiaForm.errors.file_bao_gia }}</div>
                                    </div>
                                </div>

                                <div class="bg-white p-4 rounded-lg border border-purple-100 shadow-sm mb-6">
                                    <h4 class="font-bold text-purple-800 mb-3 text-sm">Điền đơn giá (VND) cho từng mặt hàng:</h4>
                                    <div v-for="(item, index) in baoGiaForm.san_pham" :key="item.id" class="grid grid-cols-12 gap-4 items-center mb-3">
                                        <div class="col-span-6 font-medium text-gray-700 text-sm flex items-center gap-2">
                                            <span class="w-6 h-6 bg-gray-100 text-gray-500 rounded-full flex items-center justify-center text-xs font-bold shrink-0">{{ index + 1 }}</span>
                                            <span class="truncate">{{ item.ten_san_pham }}</span>
                                            <span class="text-gray-400 font-normal shrink-0">(SL: {{ item.so_luong }})</span>
                                        </div>
                                        <div class="col-span-6 flex items-center gap-2">
                                            <input v-model="item.don_gia" type="number" min="0" placeholder="Nhập đơn giá..." class="w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500 py-2 shadow-sm font-bold text-blue-700 text-right" required>
                                            <span class="font-bold text-gray-400">đ</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex flex-col md:flex-row items-center justify-between border-t border-purple-200 pt-5 gap-4">
                                    <div class="text-lg font-bold text-purple-900 bg-white px-4 py-2 rounded-lg border border-purple-100 shadow-sm w-full md:w-auto text-center md:text-left">
                                        Tổng tiền: <span class="text-2xl font-black text-green-600 ml-2">{{ tongTienTamTinh.toLocaleString() }} đ</span>
                                    </div>
                                    <button type="submit" :disabled="baoGiaForm.processing" class="w-full md:w-auto bg-purple-600 hover:bg-purple-700 text-white font-extrabold py-3 px-8 rounded-xl shadow-lg hover:shadow-xl transition-all disabled:opacity-50 flex items-center justify-center gap-2">
                                        {{ baoGiaForm.processing ? 'Đang xử lý...' : 'Chốt Giá & Đặt Hàng' }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="p-8 border-t border-gray-100 bg-white">
                    <h3 class="text-lg font-bold text-gray-800 mb-6 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6 text-blue-600">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Lịch sử xử lý (Audit Trail)
                    </h3>

                    <div class="relative border-l-2 border-gray-100 ml-4 mt-4">
                        <div v-for="(log, index) in phieu.nhat_ky" :key="log.id" class="mb-8 ml-6 relative group">

                            <span class="absolute flex items-center justify-center w-6 h-6 bg-blue-100 rounded-full -left-9 ring-8 ring-white transition-transform group-hover:scale-110">
                                <div class="w-2.5 h-2.5 bg-blue-600 rounded-full" :class="{'bg-green-500': index === 0 && phieu.trang_thai_color === 'green', 'bg-red-500': phieu.trang_thai_color === 'red' && index === 0}"></div>
                            </span>

                            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-1">
                                <h4 class="font-bold text-gray-900 text-base flex items-center gap-2">
                                    {{ log.hanh_dong_label }}
                                    <span v-if="index === 0" class="bg-blue-100 text-blue-800 text-[10px] font-extrabold px-2 py-0.5 rounded uppercase">Mới nhất</span>
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

                <div class="bg-slate-50 px-8 py-5 flex flex-col md:flex-row justify-between items-center border-t border-gray-200 gap-4 rounded-b-xl">
                    <a :href="route('phieu.print', phieu.id)" target="_blank"
                        class="flex items-center gap-2 text-gray-600 hover:text-blue-600 font-bold transition bg-white px-4 py-2 rounded-lg border border-gray-200 shadow-sm hover:shadow">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75v-4.125c0-.621-.504-1.125-1.125-1.125H5.375c-.621 0-1.125.504-1.125 1.125v4.125c0 .621.504 1.125 1.125 1.125H6.358m12.284 0h.008v.008h-.008V18zM6.75 6H17.25a2.25 2.25 0 012.25 2.25v2.25H4.5v-2.25A2.25 2.25 0 016.75 6zM5.25 9h13.5" /></svg>
                        In phiếu PDF
                    </a>

                    <div v-if="canApprove" class="flex flex-col md:flex-row items-center gap-3 w-full md:w-auto">
                        <input v-model="ghiChu" type="text" placeholder="Ghi chú (Bắt buộc nếu Từ chối)..." class="text-sm border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-lg w-full md:w-64 py-2.5 shadow-sm">
                        <div class="flex gap-2 w-full md:w-auto">
                            <button @click="xuLyPhieu('tu_choi')" class="flex-1 md:flex-none bg-white text-red-600 border border-red-200 px-5 py-2.5 rounded-lg font-bold hover:bg-red-50 transition shadow-sm">Từ chối</button>
                            <button @click="xuLyPhieu('duyet')" class="flex-1 md:flex-none bg-blue-600 text-white px-8 py-2.5 rounded-lg font-extrabold hover:bg-blue-700 shadow-md hover:shadow-lg transition">Phê Duyệt</button>
                        </div>
                    </div>

                    <div v-else-if="canThanhToan" class="w-full md:w-auto flex flex-col items-end gap-2">
                        <p class="text-sm font-bold text-gray-600">
                            Thanh toán cho NCC: <span class="text-blue-700">{{ phieu.nha_cung_cap }}</span>
                        </p>
                        <button @click="showPaymentModal = true"
                            class="w-full md:w-auto bg-emerald-600 text-white px-8 py-3 rounded-lg font-extrabold hover:bg-emerald-700 shadow-lg hover:shadow-xl transition-all flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm14 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" /></svg>
                            Thực hiện Thanh toán
                        </button>
                    </div>

                    <div v-else-if="canXacNhanNhanHang" class="w-full md:w-auto flex flex-col items-end gap-2">
                        <p class="text-sm font-bold text-gray-600">
                            Kế toán đã thanh toán. Vui lòng kiểm tra hàng hóa!
                        </p>
                        <button @click="showNhanHangModal = true"
                            class="w-full md:w-auto bg-green-600 text-white px-8 py-3 rounded-lg font-extrabold hover:bg-green-700 shadow-lg hover:shadow-xl transition-all flex items-center justify-center gap-2">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            Xác nhận Đã Nhận Hàng
                        </button>
                    </div>

                    <div v-else-if="canCancel" class="flex flex-col md:flex-row items-center gap-3 w-full md:w-auto">
                        <input v-model="ghiChu" type="text" placeholder="Lý do hủy..." class="text-sm border-gray-300 focus:border-red-500 focus:ring-red-500 rounded-lg w-full md:w-64 py-2.5 shadow-sm">
                        <button @click="huyPhieu" class="w-full md:w-auto bg-white border border-gray-300 text-gray-700 px-6 py-2.5 rounded-lg font-bold hover:bg-gray-50 hover:text-red-600 transition shadow-sm">Thu hồi phiếu</button>
                    </div>

                    <div v-else class="italic text-gray-500 text-sm font-medium bg-gray-100 px-4 py-2 rounded-lg border border-gray-200">
                        Trạng thái hiện tại: <span class="font-bold text-gray-700">{{ phieu.trang_thai_label }}</span>
                    </div>

                </div>

                <div v-if="showPaymentModal" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/40 transition-all duration-300">
                    <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full p-6 mx-4 relative overflow-hidden">
                        <button @click="showPaymentModal = false" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>

                        <h3 class="text-2xl font-extrabold text-gray-900 mb-2">Chọn phương thức</h3>
                        <p class="text-sm text-gray-500 mb-6">Vui lòng chọn cổng thanh toán để thực hiện chi tiền cho nhà cung cấp.</p>

                        <div class="space-y-4">
                            <Link :href="route('vnpay.create', phieu.id)" method="post" as="button" type="button" class="w-full text-left block border-2 border-blue-500 bg-blue-50 hover:bg-blue-100 rounded-xl p-4 transition cursor-pointer group relative overflow-hidden">
                                <div class="absolute top-0 right-0 bg-blue-500 text-white text-[10px] font-bold px-2 py-1 rounded-bl-lg uppercase tracking-wider">
                                    Khuyên dùng
                                </div>
                                <div class="flex items-center gap-4">
                                    <div class="bg-white p-2 rounded-lg shadow-sm">
                                        <span class="font-black text-blue-700 text-xl tracking-tighter">VNPAY</span>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-blue-900 text-lg">Tài khoản Doanh nghiệp</h4>
                                        <p class="text-xs text-blue-700 mt-1">Thanh toán tự động S2S qua cổng VNPAY. Hệ thống tự động đối soát và cập nhật trạng thái.</p>
                                    </div>
                                </div>
                            </Link>

                            <Link :href="route('thanhtoan.show', phieu.id)" class="block border-2 border-gray-200 hover:border-emerald-500 hover:bg-emerald-50 bg-white rounded-xl p-4 transition cursor-pointer group">
                                <div class="flex items-center gap-4">
                                    <div class="bg-gray-100 group-hover:bg-white p-2 rounded-lg text-gray-600 group-hover:text-emerald-600 transition shadow-sm">
                                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm14 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" /></svg>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-gray-800 group-hover:text-emerald-800 text-lg transition">Tài khoản Cá nhân (VietQR)</h4>
                                        <p class="text-xs text-gray-500 group-hover:text-emerald-600 mt-1 transition">Quét mã QR bằng App ngân hàng, tải lên biên lai và xác nhận thủ công.</p>
                                    </div>
                                </div>
                            </Link>
                        </div>
                    </div>
                </div>

                <div v-if="showNhanHangModal" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 p-4 transition-all duration-300">
                    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 bg-green-50 flex justify-between items-center">
                            <h3 class="text-lg font-bold text-green-800 flex items-center gap-2">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                Xác nhận Nghiệm thu
                            </h3>
                            <button @click="showNhanHangModal = false" class="text-green-600 hover:text-green-800 transition rounded-full hover:bg-green-100 p-1">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </div>

                        <form @submit.prevent="submitNhanHang" class="p-6">
                            <div class="mb-5">
                                <label class="block text-sm font-extrabold text-gray-800 mb-1">Hình ảnh / Biên bản (Bắt buộc) <span class="text-red-500">*</span></label>
                                <p class="text-xs text-gray-500 mb-3">Vui lòng tải lên ảnh chụp kiện hàng, hóa đơn đỏ, hoặc biên bản giao hàng thực tế.</p>
                                <input type="file" @change="handleFileNhanHang" accept=".jpg,.jpeg,.png,.pdf" required
                                    class="w-full text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-bold file:bg-green-50 file:text-green-700 hover:file:bg-green-100 border border-gray-200 rounded-xl p-2 cursor-pointer shadow-sm transition" />
                                <div v-if="nhanHangForm.errors.file_nhan_hang" class="text-red-500 text-xs mt-1 font-bold">{{ nhanHangForm.errors.file_nhan_hang }}</div>
                            </div>

                            <div class="mb-6">
                                <label class="block text-sm font-extrabold text-gray-800 mb-2">Ghi chú tình trạng hàng hóa</label>
                                <textarea v-model="nhanHangForm.ghi_chu_nhan_hang" rows="3" placeholder="Ví dụ: Máy nguyên seal, hoạt động tốt. Đã nhận đủ các phụ kiện kèm theo..."
                                        class="w-full border-gray-300 rounded-xl shadow-sm focus:border-green-500 focus:ring-green-500 text-sm"></textarea>
                                <div v-if="nhanHangForm.errors.ghi_chu_nhan_hang" class="text-red-500 text-xs mt-1 font-bold">{{ nhanHangForm.errors.ghi_chu_nhan_hang }}</div>
                            </div>

                            <div class="flex justify-end gap-3 border-t border-gray-100 pt-5 mt-2">
                                <button type="button" @click="showNhanHangModal = false" class="px-5 py-2.5 text-sm font-bold text-gray-600 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 transition shadow-sm">
                                    Hủy bỏ
                                </button>
                                <button type="submit" :disabled="nhanHangForm.processing" class="px-6 py-2.5 text-sm font-bold text-white bg-green-600 rounded-xl hover:bg-green-700 shadow-md transition disabled:opacity-50 flex items-center gap-2">
                                    <span v-if="nhanHangForm.processing" class="flex items-center gap-2">
                                        <svg class="animate-spin -ml-1 mr-1 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                        Đang lưu...
                                    </span>
                                    <span v-else>Xác nhận Lưu</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
</template>
