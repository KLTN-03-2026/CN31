<script setup>
import { ref, watch } from 'vue';
import { Link, router, Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    phongBans: Object,
    users: Array,
    filters: Object
});

// --- TÌM KIẾM (SEARCH DEBOUNCE) ---
const searchQuery = ref(props.filters?.search || '');
let searchTimeout = null;

watch(searchQuery, (value) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get(route('admin.phongban.index'), { search: value }, {
            preserveState: true,
            preserveScroll: true,
            replace: true
        });
    }, 500);
});

// --- QUẢN LÝ FORM ---
const showForm = ref(false);
const editingId = ref(null);

const form = useForm({
    ma_phong_ban: '',
    ten_phong_ban: '',
    truong_phong_id: '',
    ngan_sach_tong: 0,
});

// FIX UX: Tự động cuộn lên đầu trang
const scrollToTop = () => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
};

const openAdd = () => {
    editingId.value = null;
    form.reset();
    form.clearErrors();
    showForm.value = true;
    scrollToTop();
};

const openEdit = (pb) => {
    editingId.value = pb.id;
    form.ma_phong_ban = pb.ma_phong_ban;
    form.ten_phong_ban = pb.ten_phong_ban;
    form.truong_phong_id = pb.truong_phong_id || '';
    form.ngan_sach_tong = pb.ngan_sach_tong;
    form.clearErrors();
    showForm.value = true;
    scrollToTop();
};

const submit = () => {
    if (editingId.value) {
        form.put(route('admin.phongban.update', editingId.value), {
            preserveScroll: true,
            onSuccess: () => { showForm.value = false; form.reset(); }
        });
    } else {
        form.post(route('admin.phongban.store'), {
            preserveScroll: true,
            onSuccess: () => { showForm.value = false; form.reset(); }
        });
    }
};

const deleteItem = (id, name) => {
    if (confirm(`CẢNH BÁO: Xóa phòng ban "${name}" sẽ ảnh hưởng đến sơ đồ tổ chức.\n\nBạn có chắc chắn muốn xóa?`)) {
        router.delete(route('admin.phongban.destroy', id), { preserveScroll: true });
    }
};

// --- TIỆN ÍCH HIỂN THỊ ---
const formatCurrency = (value) => {
    return new Intl.NumberFormat('vi-VN').format(value || 0); // Bỏ chữ '₫' mặc định để dễ format UI hơn
};

const calcPercent = (used, total) => {
    if (!total || total <= 0) return 0;
    const percent = (used / total) * 100;
    return percent > 100 ? 100 : percent.toFixed(1);
};

const formatPagination = (label) => {
    if (label.includes('Previous')) return '«';
    if (label.includes('Next')) return '»';
    return label;
};
</script>

<template>
    <Head title="Cơ cấu & Ngân sách - Admin"/>

    <div class="py-8 bg-[#F8FAFC] min-h-[calc(100vh-64px)]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-6 border-b border-slate-200 pb-5">
                <div class="w-full md:w-1/2 lg:w-1/3">
                    <h2 class="font-black text-2xl text-slate-900 tracking-tight">Cơ cấu & Ngân sách</h2>
                    <p class="text-sm font-medium text-slate-500 mt-1 mb-4">Quản lý sơ đồ tổ chức và hạn mức chi tiêu hàng năm</p>
                    <div class="relative w-full">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                        <input type="text" v-model="searchQuery" placeholder="Tìm tên hoặc mã phòng ban..."
                               class="w-full pl-9 pr-3 py-2 text-sm font-medium border border-slate-300 rounded-lg focus:ring-1 focus:ring-slate-900 focus:border-slate-900 shadow-sm transition-all placeholder:text-slate-400">
                    </div>
                </div>

                <button @click="openAdd" class="shrink-0 bg-slate-900 hover:bg-slate-800 text-white text-sm font-bold py-2.5 px-5 rounded-lg shadow-sm hover:shadow transition-all flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                    Tạo phòng ban mới
                </button>
            </div>

            <div class="animate-fade-in">

                <transition enter-active-class="transition ease-out duration-200" enter-from-class="opacity-0 -translate-y-2" enter-to-class="opacity-100 translate-y-0" leave-active-class="transition ease-in duration-150" leave-from-class="opacity-100" leave-to-class="opacity-0">
                    <div v-if="showForm" class="bg-white rounded-xl border border-slate-200 shadow-sm mb-8 overflow-hidden">

                        <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                            <div class="flex items-center gap-3">
                                <div class="w-2 h-6 rounded-full" :class="editingId ? 'bg-amber-400' : 'bg-emerald-500'"></div>
                                <h3 class="font-black text-lg text-slate-900">{{ editingId ? 'Cập nhật cấu hình phòng ban' : 'Thiết lập phòng ban mới' }}</h3>
                            </div>
                            <button @click="showForm = false" class="text-slate-400 hover:text-slate-700 bg-white p-1 rounded-md border border-slate-200 shadow-sm transition-colors">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </div>

                        <form @submit.prevent="submit" class="flex flex-col">
                            <div class="p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                                <div class="space-y-2">
                                    <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-widest">Mã phòng ban <span class="text-red-500">*</span></label>
                                    <input v-model="form.ma_phong_ban" type="text" placeholder="VD: HR, IT, SALE"
                                           class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-1 focus:ring-slate-900 focus:border-slate-900 uppercase font-mono transition-all" required>
                                    <p v-if="form.errors.ma_phong_ban" class="text-red-500 text-xs font-medium">{{ form.errors.ma_phong_ban }}</p>
                                </div>

                                <div class="space-y-2 lg:col-span-2">
                                    <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-widest">Tên phòng ban <span class="text-red-500">*</span></label>
                                    <input v-model="form.ten_phong_ban" type="text" placeholder="Phòng Phát triển Phần mềm"
                                           class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-1 focus:ring-slate-900 focus:border-slate-900 transition-all" required>
                                    <p v-if="form.errors.ten_phong_ban" class="text-red-500 text-xs font-medium">{{ form.errors.ten_phong_ban }}</p>
                                </div>

                                <div class="space-y-2">
                                    <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-widest">Trưởng phòng</label>
                                    <select v-model="form.truong_phong_id" class="w-full px-3 py-2 text-sm font-medium border border-slate-300 rounded-lg focus:ring-1 focus:ring-slate-900 focus:border-slate-900 bg-white cursor-pointer transition-all">
                                        <option value="">-- Chưa bổ nhiệm --</option>
                                        <option v-for="user in users" :key="user.id" :value="user.id">{{ user.name }}</option>
                                    </select>
                                </div>

                                <div class="space-y-2 lg:col-span-2">
                                    <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-widest">Hạn mức ngân sách năm (VNĐ) <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                            <span class="text-slate-400 sm:text-sm font-bold">₫</span>
                                        </div>
                                        <input v-model="form.ngan_sach_tong" type="number" min="0"
                                               class="w-full pl-8 pr-4 py-2 text-sm border border-slate-300 rounded-lg focus:ring-1 focus:ring-slate-900 focus:border-slate-900 font-mono font-bold text-blue-700 transition-all" required>
                                    </div>
                                    <p v-if="form.errors.ngan_sach_tong" class="text-red-500 text-xs font-medium">{{ form.errors.ngan_sach_tong }}</p>
                                    <p class="text-[10px] text-slate-400 mt-1.5 font-medium italic" v-if="form.ngan_sach_tong > 0">Tương đương: {{ formatCurrency(form.ngan_sach_tong) }} VNĐ</p>
                                </div>
                            </div>

                            <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex justify-end gap-3 mt-auto">
                                <button type="button" @click="showForm = false" class="px-5 py-2 text-sm font-bold text-slate-600 bg-white border border-slate-300 hover:bg-slate-100 rounded-lg transition-colors shadow-sm">Hủy bỏ</button>
                                <button type="submit" :disabled="form.processing" class="px-6 py-2 text-sm font-bold text-white bg-slate-900 hover:bg-slate-800 rounded-lg shadow-sm transition-colors disabled:opacity-50 flex items-center gap-2">
                                    <span v-if="form.processing" class="w-4 h-4 border-2 border-white/20 border-t-white rounded-full animate-spin"></span>
                                    {{ editingId ? 'Lưu cấu hình' : 'Tạo phòng ban' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </transition>

                <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse whitespace-nowrap">
                            <thead>
                                <tr class="bg-slate-50 border-b border-slate-200 text-[10px] uppercase tracking-widest text-slate-500 font-bold">
                                    <th class="px-6 py-4">Phòng ban</th>
                                    <th class="px-6 py-4">Lãnh đạo phụ trách</th>
                                    <th class="px-6 py-4 w-[35%] min-w-[300px]">Tiến độ giải ngân Ngân sách</th>
                                    <th class="px-6 py-4 text-right">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-for="pb in phongBans.data" :key="pb.id" class="hover:bg-slate-50 transition-colors group">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <span class="px-2 py-0.5 bg-slate-100 border border-slate-200 rounded text-[10px] font-black text-slate-600 uppercase tracking-widest">{{ pb.ma_phong_ban }}</span>
                                            <span class="text-sm font-bold text-slate-900 group-hover:text-blue-600 transition-colors">{{ pb.ten_phong_ban }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div v-if="pb.truong_phong" class="flex items-center gap-2.5">
                                            <div class="w-8 h-8 rounded-full bg-slate-100 text-slate-600 font-bold flex items-center justify-center text-[11px] border border-slate-200 shadow-sm">
                                                {{ pb.truong_phong.name.charAt(0).toUpperCase() }}
                                            </div>
                                            <span class="text-[13px] font-bold text-slate-800">{{ pb.truong_phong.name }}</span>
                                        </div>
                                        <span v-else class="text-[11px] font-medium italic text-slate-400 bg-slate-50 px-2 py-1 rounded-md border border-slate-100">-- Đang khuyết --</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-col">
                                            <div class="flex justify-between items-end mb-1.5">
                                                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Đã chi: {{ formatCurrency(pb.ngan_sach_su_dung) }} ₫</span>
                                                <span class="text-[13px] font-black text-slate-900 tabular-nums">{{ formatCurrency(pb.ngan_sach_tong) }} <span class="text-[10px] text-slate-400 font-bold">₫</span></span>
                                            </div>

                                            <div class="w-full h-2.5 bg-slate-100 rounded-full overflow-hidden flex mb-1.5 ring-1 ring-inset ring-slate-200">
                                                <div class="h-full rounded-full transition-all duration-1000 ease-out"
                                                     :class="{
                                                        'bg-emerald-500': calcPercent(pb.ngan_sach_su_dung, pb.ngan_sach_tong) < 70,
                                                        'bg-amber-400': calcPercent(pb.ngan_sach_su_dung, pb.ngan_sach_tong) >= 70 && calcPercent(pb.ngan_sach_su_dung, pb.ngan_sach_tong) < 90,
                                                        'bg-rose-500': calcPercent(pb.ngan_sach_su_dung, pb.ngan_sach_tong) >= 90
                                                     }"
                                                     :style="`width: ${calcPercent(pb.ngan_sach_su_dung, pb.ngan_sach_tong)}%`">
                                                </div>
                                            </div>

                                            <div class="flex justify-between items-center">
                                                <span class="text-[10px] font-bold text-slate-400" :class="calcPercent(pb.ngan_sach_su_dung, pb.ngan_sach_tong) >= 90 ? 'text-rose-500' : ''">{{ calcPercent(pb.ngan_sach_su_dung, pb.ngan_sach_tong) }}%</span>
                                                <span class="text-[10px] font-bold uppercase tracking-wider bg-slate-50 px-1.5 py-0.5 rounded border border-slate-100"
                                                      :class="pb.ngan_sach_tong - pb.ngan_sach_su_dung < 0 ? 'text-rose-600' : 'text-emerald-600'">
                                                    Dư: {{ formatCurrency(pb.ngan_sach_tong - pb.ngan_sach_su_dung) }} ₫
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                            <button @click="openEdit(pb)" title="Cấu hình & Cấp vốn"
                                                    class="p-1.5 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                            </button>
                                            <button @click="deleteItem(pb.id, pb.ten_phong_ban)" title="Xóa phòng ban"
                                                    class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="!phongBans.data.length">
                                    <td colspan="4" class="px-6 py-12 text-center flex flex-col items-center justify-center">
                                        <svg class="w-10 h-10 text-slate-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                                        <p class="text-sm font-bold text-slate-700">Chưa có cơ cấu phòng ban</p>
                                        <p class="text-xs text-slate-500 mt-1">Vui lòng tạo phòng ban mới.</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="border-t border-slate-100 bg-white p-4 flex justify-between items-center" v-if="phongBans.links.length > 3">
                        <p class="text-xs font-medium text-slate-500 hidden sm:block">
                            Hiển thị <span class="font-bold text-slate-800">{{ phongBans.from || 0 }}</span> đến <span class="font-bold text-slate-800">{{ phongBans.to || 0 }}</span>
                        </p>
                        <div class="flex flex-wrap gap-1">
                            <template v-for="(link, index) in phongBans.links" :key="index">
                                <Link v-if="link.url" :href="link.url" v-html="formatPagination(link.label)"
                                      class="px-2.5 py-1.5 rounded-md text-xs font-bold transition-all border"
                                      :class="link.active ? 'bg-slate-900 text-white border-slate-900 shadow-sm' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'" />
                                <span v-else v-html="formatPagination(link.label)" class="px-2.5 py-1.5 rounded-md text-xs font-bold text-slate-400 cursor-not-allowed"></span>
                            </template>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</template>

<style scoped>
.animate-fade-in { animation: fadeIn 0.2s ease-out; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }

/* Ẩn mũi tên của input type number */
input[type=number]::-webkit-inner-spin-button,
input[type=number]::-webkit-outer-spin-button {
  -webkit-appearance: none;
  margin: 0;
}
</style>
