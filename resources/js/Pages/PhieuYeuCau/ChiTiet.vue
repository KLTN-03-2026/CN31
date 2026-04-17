<script setup>
import { Head, Link, usePage, router, useForm } from "@inertiajs/vue3";
import { computed, ref } from "vue";
import StatusBadge from "@/Components/UI/StatusBadge.vue";

const props = defineProps({
  phieu: Object,
  nhaCungCaps: Array,
  nganSach: Object,
});

const page = usePage();
const user = computed(() => page.props.auth.user);

// 1. LOGIC PHÂN QUYỀN (RBAC) & FIX LỖI 403
const canApprove = computed(() => {
  if (user.value.vai_tro === "truong_phong" && props.phieu.trang_thai_color === "yellow") return true;
  if (user.value.vai_tro === "giam_doc" && props.phieu.trang_thai_color === "orange") return true;
  return false;
});

const canThanhToan = computed(() => {
  return user.value.vai_tro === "ke_toan" && props.phieu.trang_thai_label === "Chờ thanh toán";
});

const canCancel = computed(() => {
  return user.value.name === props.phieu.nguoi_tao && props.phieu.trang_thai_color === "yellow";
});

const canUpdateBaoGia = computed(() => {
  return user.value.vai_tro === "nhan_vien_mua_sam" && props.phieu.trang_thai_color === "purple";
});

const canXacNhanNhanHang = computed(() => {
  return props.phieu.nguoi_tao === user.value.name && props.phieu.trang_thai_label === "Đã thanh toán";
});

// 2. LOGIC DRAG & DROP & FORM HANDLING
const baoGiaForm = useForm({
  nha_cung_cap_id: "",
  file_bao_gia: null,
  san_pham: props.phieu.chi_tiet.map((sp) => ({
    id: sp.id, don_gia: sp.don_gia || 0, so_luong: sp.so_luong, ten_san_pham: sp.ten_san_pham,
  })),
});

const isDraggingBaoGia = ref(false);
const handleDropBaoGia = (e) => {
  e.preventDefault();
  isDraggingBaoGia.value = false;
  if (e.dataTransfer.files?.length) baoGiaForm.file_bao_gia = e.dataTransfer.files[0];
};

const capNhatBaoGia = () => {
  baoGiaForm.post(route("phieu.bao_gia", props.phieu.id), { preserveScroll: true });
};

const showNhanHangModal = ref(false);
const nhanHangForm = useForm({ file_nhan_hang: null, ghi_chu_nhan_hang: "" });
const isDraggingNhanHang = ref(false);

const handleDropNhanHang = (e) => {
  e.preventDefault();
  isDraggingNhanHang.value = false;
  if (e.dataTransfer.files?.length) nhanHangForm.file_nhan_hang = e.dataTransfer.files[0];
};

const submitNhanHang = () => {
  nhanHangForm.post(route("phieu.nhan_hang", props.phieu.id), {
    onSuccess: () => { showNhanHangModal.value = false; nhanHangForm.reset(); },
  });
};

// 3. XỬ LÝ PHÊ DUYỆT (OPTIMISTIC UI)
const ghiChu = ref("");
const isProcessingApprove = ref(false);
const showTuChoiModal = ref(false);
const showPaymentModal = ref(false);

const xuLyPhieu = (hanhDong) => {
  isProcessingApprove.value = true;
  router.post(route("phieu.duyet", props.phieu.id), { hanh_dong: hanhDong, ghi_chu: ghiChu.value }, {
      preserveScroll: true,
      onFinish: () => { isProcessingApprove.value = false; showTuChoiModal.value = false; },
  });
};

const isProcessingCancel = ref(false);
const huyPhieu = () => {
  if (!confirm("Bạn có chắc chắn muốn THU HỒI yêu cầu này không?")) return;
  isProcessingCancel.value = true;
  router.post(route("phieu.cancel", props.phieu.id), { ghi_chu: ghiChu.value }, {
      preserveScroll: true,
      onFinish: () => { isProcessingCancel.value = false; },
  });
};

const formatGhiChu = (text) => {
  if (!text) return "Không có ghi chú.";
  const urlRegex = /(https?:\/\/[^\s]+)/g;
  return text.replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(urlRegex, (url) =>
      `<a href="${url}" target="_blank" class="text-blue-600 font-bold hover:underline">Xem Link</a>`
  );
};
</script>

<template>
  <Head :title="`Chi tiết ${phieu.ma_phieu}`" />

  <div class="py-6 min-h-screen">
    <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
      <div class="mb-4">
        <Link :href="route('dashboard')" class="text-slate-500 hover:text-slate-900 font-semibold text-sm flex items-center gap-1 transition-colors w-fit">&larr; Quay lại Dashboard</Link>
      </div>

      <div class="flex flex-col lg:flex-row gap-5">
        <div class="lg:w-2/3 space-y-5">
          <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex justify-between items-start gap-4">
              <div>
                <div class="flex items-center gap-3 mb-2">
                  <span class="font-mono text-[11px] font-bold text-blue-700 bg-blue-50 px-2 py-0.5 rounded">{{ phieu.ma_phieu }}</span>
                  <span class="text-xs text-slate-400 font-medium">{{ phieu.ngay_tao }}</span>
                </div>
                <h1 class="text-2xl font-black text-slate-900 leading-tight">{{ phieu.tieu_de }}</h1>
                <div class="mt-3 flex items-center gap-2 text-xs text-slate-600">
                  <div class="w-5 h-5 rounded-full bg-slate-100 flex items-center justify-center border border-slate-200 font-bold text-[11px] uppercase">{{ phieu.nguoi_tao[0] }}</div>
                  <span>Người tạo: <strong class="text-slate-800 text-sm ">{{ phieu.nguoi_tao }}</strong></span>
                </div>
              </div>
              <div class="shrink-0 pt-1 hidden sm:block">
                <StatusBadge :label="phieu.trang_thai_label" :color="phieu.trang_thai_color" class="!px-4 !py-1.5 shadow-sm" />
              </div>
            </div>
            <div class="p-5 bg-slate-50/50">
              <h3 class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Lý do / Ghi chú</h3>
              <div class="text-slate-800 text-sm leading-relaxed" v-html="formatGhiChu(phieu.ly_do)"></div>
            </div>
          </div>

          <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-5 py-3 border-b border-slate-100">
              <h3 class="font-bold text-slate-800 text-sm">Danh sách hàng hóa</h3>
            </div>
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-slate-100">
                <thead class="bg-slate-50">
                  <tr>
                    <th class="px-5 py-2.5 text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider">Sản phẩm</th>
                    <th class="whitespace-nowrap px-5 py-2.5 text-center text-[11px] font-bold text-slate-500 uppercase tracking-wider w-20">Số lượng</th>
                    <th class="px-5 py-2.5 text-right text-[11px] font-bold text-slate-500 uppercase tracking-wider">Đơn giá</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-slate-50">
                  <tr v-for="sp in phieu.chi_tiet" :key="sp.id" class="hover:bg-slate-50/50 transition-colors">
                    <td class="px-5 py-3 text-sm font-semibold text-slate-800">{{ sp.ten_san_pham }}</td>
                    <td class="px-5 py-3 text-center font-bold text-slate-600 text-sm">{{ sp.so_luong }}</td>
                    <td class="px-5 py-3 text-right text-sm tabular-nums font-bold text-slate-700">{{ sp.don_gia ? Number(sp.don_gia).toLocaleString() : "Chờ báo giá" }}</td>
                  </tr>
                </tbody>
                <tfoot v-if="phieu.tong_tien" class="bg-slate-50">
                  <tr>
                    <td colspan="2" class="px-5 py-3 text-right text-[11px] font-bold text-slate-500 uppercase">Tổng cộng:</td>
                    <td class="px-5 py-3 text-right font-black text-slate-900 text-base tabular-nums">{{ Number(phieu.tong_tien).toLocaleString() }} đ</td>
                  </tr>
                </tfoot>
              </table>
            </div>
          </div>

          <div v-if="canUpdateBaoGia" class="bg-white rounded-xl border border-blue-200 shadow-sm p-5">
            <form @submit.prevent="capNhatBaoGia" class="space-y-4">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-[11px] font-bold text-slate-500 uppercase mb-1.5">Nhà cung cấp <span class="text-red-500">*</span></label>
                  <select v-model="baoGiaForm.nha_cung_cap_id" class="w-full rounded-lg border-slate-200 focus:ring-blue-500 py-2 text-sm" required>
                    <option value="" disabled>-- Chọn nhà cung cấp --</option>
                    <option v-for="ncc in nhaCungCaps" :key="ncc.id" :value="ncc.id">{{ ncc.ten_nha_cung_cap }}</option>
                  </select>
                </div>
                <div>
                  <label class="block text-[11px] font-bold text-slate-500 uppercase mb-1.5">File Báo giá (PDF)</label>
                  <div @dragover.prevent="isDraggingBaoGia = true" @dragleave.prevent="isDraggingBaoGia = false" @drop="handleDropBaoGia" :class="['relative border border-dashed rounded-lg p-2 text-center transition-all flex items-center justify-center min-h-[38px]', isDraggingBaoGia ? 'border-blue-500 bg-blue-50' : 'border-slate-300 hover:border-slate-400']">
                    <input type="file" @change="(e) => (baoGiaForm.file_bao_gia = e.target.files[0])" class="absolute inset-0 opacity-0 cursor-pointer" accept="application/pdf" />
                    <p class="text-xs font-medium text-slate-500 truncate px-2">{{ baoGiaForm.file_bao_gia ? baoGiaForm.file_bao_gia.name : "Kéo thả file hoặc Click để chọn" }}</p>
                  </div>
                </div>
              </div>
              <div class="bg-slate-50 p-3 rounded-lg space-y-2 border border-slate-100">
                <div v-for="(item, index) in baoGiaForm.san_pham" :key="item.id" class="flex items-center justify-between gap-3">
                  <span class="text-xs font-medium text-slate-700 truncate flex-1">{{ item.ten_san_pham }} <span class="text-slate-400">(x{{ item.so_luong }})</span></span>
                  <input v-model="item.don_gia" type="number" min="1" class="w-28 rounded-md border-slate-200 text-right font-bold text-blue-600 py-1 text-sm shadow-sm" required />
                </div>
              </div>
              <div class="flex justify-end pt-1">
                <button type="submit" :disabled="baoGiaForm.processing" class="bg-blue-600 text-white px-6 py-2 rounded-lg text-sm font-bold shadow-sm hover:bg-blue-700 disabled:opacity-50 transition-all">{{ baoGiaForm.processing ? "Đang lưu..." : "Xác nhận Báo giá" }}</button>
              </div>
            </form>
          </div>

          <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
            <h3 class="font-bold text-slate-800 mb-5 uppercase text-xs tracking-widest">Lịch sử xử lý</h3>
            <div class="space-y-6 relative border-l-2 border-blue-100 ml-3">
              <div v-for="(log, idx) in phieu.nhat_ky" :key="log.id" class="relative pl-6 group">
                <span class="absolute flex items-center justify-center w-6 h-6 bg-blue-50 rounded-full -left-[13px] -top-0.5 ring-4 ring-white transition-transform duration-300 group-hover:scale-110">
                  <div class="w-2.5 h-2.5 rounded-full shadow-sm" :class="[idx === 0 && phieu.trang_thai_color === 'green' ? 'bg-emerald-500' : idx === 0 && phieu.trang_thai_color === 'red' ? 'bg-red-500' : 'bg-blue-500']"></div>
                </span>
                <div class="flex justify-between items-start mb-0.5">
                  <h4 class="text-sm font-bold text-slate-900">{{ log.hanh_dong_label }}</h4>
                  <span class="text-[10px] font-medium text-slate-400">{{ log.thoi_gian }}</span>
                </div>
                <p class="text-[11px] text-slate-500 mb-1">Bởi: <strong class="text-slate-700">{{ log.nguoi_thuc_hien }}</strong></p>
                <div v-if="log.ghi_chu" class="text-xs text-slate-600 bg-slate-50 p-2.5 rounded-lg border border-slate-100 italic mt-1.5">"{{ log.ghi_chu }}"</div>
              </div>
            </div>
          </div>
        </div>

        <div class="lg:w-1/3 space-y-5">
          <div v-if="nganSach" class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
            <h3 class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-4">Ngân sách {{ nganSach.ten_phong }}</h3>
            <div class="flex justify-between items-end mb-2">
              <span class="text-2xl font-black text-slate-900">{{ nganSach.phan_tram }}%</span>
              <div class="text-right">
                <p class="text-[10px] font-bold text-slate-400 uppercase mb-0.5">Khả dụng</p>
                <p class="text-sm font-black text-emerald-600 tabular-nums">{{ Number(nganSach.con_lai).toLocaleString() }} đ</p>
              </div>
            </div>
            <div class="w-full bg-slate-100 rounded-full h-1.5 mb-3 overflow-hidden">
              <div class="h-full bg-emerald-500 rounded-full transition-all duration-1000" :style="`width: ${nganSach.phan_tram}%`"></div>
            </div>
            <div class="flex justify-between text-[12px] font-medium text-slate-500">
              <span>Đã chi: {{ Number(nganSach.da_dung).toLocaleString() }}</span>
              <span>Tổng: {{ Number(nganSach.tong).toLocaleString() }}</span>
            </div>
          </div>

          <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-5 py-3 border-b border-slate-100 bg-slate-50">
              <h3 class="text-[11px] font-bold text-slate-600 uppercase tracking-wider">Nhà cung cấp</h3>
            </div>
            <div class="p-5 flex justify-between items-start gap-4">
              <div class="min-w-0">
                <p class="text-[10px] font-bold text-slate-400 uppercase">Tên nhà cung cấp</p>
                <p class="text-sm font-bold text-slate-900 mt-1 truncate" :title="phieu.nha_cung_cap">{{ phieu.nha_cung_cap || "Chưa xác định" }}</p>
              </div>
              <div class="text-right shrink-0">
                <p class="text-[10px] font-bold text-slate-400 uppercase mb-1">Báo giá</p>
                <a v-if="phieu.file_bao_gia" :href="phieu.file_bao_gia" target="_blank" class="text-[10px] font-bold text-blue-600 hover:text-blue-800 bg-blue-50 px-2 py-1 rounded transition-colors">Xem PDF</a>
                <span v-else class="text-[10px] text-slate-400 italic">Chưa có file.</span>
              </div>
            </div>
          </div>

          <div v-if="phieu.file_nhan_hang" class="bg-white rounded-xl border border-emerald-200 shadow-sm p-5 bg-emerald-50/30">
            <h3 class="text-[11px] font-bold text-emerald-700 uppercase tracking-widest mb-3 flex items-center gap-1.5">
              <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Nghiệm thu
            </h3>
            <p class="text-xs text-slate-600 mb-3 leading-relaxed">{{ phieu.ghi_chu_nhan_hang || "Không có ghi chú" }}</p>
            <a :href="phieu.file_nhan_hang" target="_blank" class="w-full py-1.5 bg-white border border-emerald-200 text-emerald-700 rounded-md text-xs font-bold flex items-center justify-center gap-2 hover:bg-emerald-50 shadow-sm">Xem ảnh thực tế</a>
          </div>
        </div>
      </div>

      <div class="mt-6 flex flex-col sm:flex-row justify-between items-center gap-4 bg-white p-4 rounded-xl shadow-sm border border-slate-200 sticky bottom-4 z-40">
        <a :href="route('phieu.print', phieu.id)" target="_blank" class="text-sm font-bold text-slate-500 hover:text-slate-900 flex items-center gap-2 px-3 py-2 rounded-lg bg-slate-50 hover:bg-slate-100 transition-colors">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 00-2 2h2m2 4h10a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
          In phiếu PDF
        </a>

        <div v-if="canApprove" class="flex gap-2 w-full sm:w-auto">
          <button @click="showTuChoiModal = true" :disabled="isProcessingApprove" class="flex-1 sm:flex-none px-6 py-2.5 bg-white text-red-600 border border-slate-200 rounded-lg font-bold hover:bg-red-50 transition-all text-sm">Từ chối</button>
          <button @click="xuLyPhieu('duyet')" :disabled="isProcessingApprove" class="flex-1 sm:flex-none px-8 py-2.5 bg-blue-600 text-white rounded-lg font-bold shadow-sm hover:bg-blue-700 transition-all flex items-center justify-center gap-2 text-sm w-36">
            <svg v-if="isProcessingApprove" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
            <span v-else>Phê Duyệt</span>
          </button>
        </div>

        <div v-else-if="canThanhToan" class="w-full sm:w-auto">
          <button @click="showPaymentModal = true" class="w-full sm:w-auto px-8 py-2.5 bg-emerald-600 text-white rounded-lg font-bold shadow-sm hover:bg-emerald-700 transition-all text-sm">Tiến hành Thanh toán</button>
        </div>

        <div v-else-if="user.vai_tro === 'ke_toan' && phieu.trang_thai_label === 'Đã thanh toán'" class="text-xs font-bold text-emerald-700 bg-emerald-50 px-4 py-2 rounded-md border border-emerald-100 flex items-center gap-1.5">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
          Đã thanh toán phiếu này
        </div>

        <div v-else-if="canXacNhanNhanHang" class="w-full sm:w-auto">
          <button @click="showNhanHangModal = true" class="w-full px-6 py-2.5 bg-emerald-700 text-white rounded-lg font-bold shadow-sm hover:bg-emerald-800 transition-all flex items-center justify-center gap-2 text-sm">Xác nhận Đã nhận hàng</button>
        </div>

        <div v-else class="text-[11px] font-bold text-slate-500 uppercase bg-slate-50 px-3 py-1.5 rounded border border-slate-100">
          <span class="text-slate-800 ml-1">{{ phieu.trang_thai_label }}</span>
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
            <textarea v-model="ghiChu" rows="3" class="w-full border-slate-200 rounded-lg text-sm focus:ring-red-500 focus:border-red-500" placeholder="Nhập lý do..."></textarea>
          </div>
          <div class="px-5 py-3 bg-slate-50 flex justify-end gap-2 border-t border-slate-100">
            <button @click="showTuChoiModal = false" class="text-sm font-bold text-slate-500 px-3 py-1.5 hover:text-slate-800">Hủy</button>
            <button @click="xuLyPhieu('tu_choi')" class="bg-red-600 text-white px-5 py-2 rounded-lg text-sm font-bold hover:bg-red-700">Xác nhận</button>
          </div>
        </div>
      </div>

      <div v-if="showPaymentModal" class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/40 backdrop-blur-sm p-4">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-lg overflow-hidden">
          <div class="p-5 border-b border-slate-100 flex justify-between items-center">
            <h3 class="text-lg font-black text-slate-900">Chọn phương thức thanh toán</h3>
            <button @click="showPaymentModal = false" class="text-slate-400 hover:text-slate-900"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg></button>
          </div>
          <div class="p-5 space-y-3">
            <Link :href="route('vnpay.create', phieu.id)" method="post" as="button" type="button" class="w-full flex items-center gap-3 p-3 border border-blue-200 bg-blue-50/50 rounded-xl hover:bg-blue-50 transition-all text-left">
              <div class="bg-white p-2 rounded-lg border border-blue-100 font-black text-blue-700 text-lg tracking-tighter">VNPAY</div>
              <div><p class="font-bold text-sm text-slate-900">Tài khoản Doanh nghiệp</p><p class="text-[11px] text-slate-500">Tự động đối soát S2S</p></div>
            </Link>
            <Link :href="route('thanhtoan.show', phieu.id)" class="w-full flex items-center gap-3 p-3 border border-slate-200 rounded-xl hover:border-emerald-500 hover:bg-emerald-50/30 transition-all text-left group">
              <div class="bg-slate-100 p-2 rounded-lg text-slate-600 group-hover:text-emerald-600"><svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm14 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" /></svg></div>
              <div><p class="font-bold text-sm text-slate-900">Tài khoản Cá nhân (VietQR)</p><p class="text-[11px] text-slate-500">Quét mã QR, tải lên biên lai</p></div>
            </Link>
          </div>
        </div>
      </div>

      <div v-if="showNhanHangModal" class="fixed inset-0 z-[100] flex items-center justify-center bg-emerald-900/40 backdrop-blur-sm p-4">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-md overflow-hidden">
          <div class="p-5 border-b border-slate-100 bg-emerald-700 text-white flex justify-between items-center">
            <h3 class="text-base font-bold flex items-center gap-2"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>Nghiệm thu</h3>
            <button @click="showNhanHangModal = false" class="text-slate-400 hover:text-white"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg></button>
          </div>
          <form @submit.prevent="submitNhanHang" class="p-6 space-y-4">
            <div>
              <label class="block text-xs font-bold text-slate-600 mb-2">Hình ảnh / Biên bản <span class="text-red-500">*</span></label>
              <div @dragover.prevent="isDraggingNhanHang = true" @dragleave.prevent="isDraggingNhanHang = false" @drop="handleDropNhanHang" :class="['relative border border-dashed rounded-xl p-4 text-center transition-all', isDraggingNhanHang ? 'border-blue-500 bg-blue-50' : 'border-slate-300']">
                <input type="file" @change="(e) => (nhanHangForm.file_nhan_hang = e.target.files[0])" class="absolute inset-0 opacity-0 cursor-pointer" required />
                <p class="text-xs font-bold text-slate-500 truncate">{{ nhanHangForm.file_nhan_hang ? nhanHangForm.file_nhan_hang.name : "Kéo thả ảnh vào đây" }}</p>
              </div>
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-600 mb-2">Ghi chú tình trạng</label>
              <textarea v-model="nhanHangForm.ghi_chu_nhan_hang" rows="2" class="w-full border-slate-200 rounded-lg text-sm focus:ring-slate-900" placeholder="Nguyên seal..."></textarea>
            </div>
            <div class="flex justify-end pt-2">
              <button type="submit" :disabled="nhanHangForm.processing" class="bg-emerald-600 text-white px-6 py-2 rounded-lg text-sm font-bold hover:bg-emerald-700 w-full">Lưu xác nhận</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>
