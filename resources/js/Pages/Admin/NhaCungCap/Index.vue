<script setup>
import { ref, watch } from 'vue';
import { Link, router, Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    nhaCungCaps: Object,
    filters: Object
});

const searchQuery = ref(props.filters?.search || '');
let searchTimeout = null;

watch(searchQuery, (value) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get(route('admin.nhacungcap.index'), { search: value }, {
            preserveState: true,
            preserveScroll: true,
            replace: true
        });
    }, 500);
});

const showForm = ref(false);
const editingId = ref(null);

const form = useForm({
    ten_nha_cung_cap: '',
    ma_so_thue: '',
    so_dien_thoai: '',
    dia_chi: '',
    ngan_hang: '',
    so_tai_khoan: '',
    chu_tai_khoan: '',
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

const openEdit = (ncc) => {
    editingId.value = ncc.id;
    form.ten_nha_cung_cap = ncc.ten_nha_cung_cap;
    form.ma_so_thue = ncc.ma_so_thue || '';
    form.so_dien_thoai = ncc.so_dien_thoai || '';
    form.dia_chi = ncc.dia_chi || '';
    form.ngan_hang = ncc.ngan_hang || '';
    form.so_tai_khoan = ncc.so_tai_khoan || '';
    form.chu_tai_khoan = ncc.chu_tai_khoan || '';
    form.clearErrors();
    showForm.value = true;
    scrollToTop();
};

const submit = () => {
    if (editingId.value) {
        form.put(route('admin.nhacungcap.update', editingId.value), {
            preserveScroll: true,
            onSuccess: () => { showForm.value = false; form.reset(); }
        });
    } else {
        form.post(route('admin.nhacungcap.store'), {
            preserveScroll: true,
            onSuccess: () => { showForm.value = false; form.reset(); }
        });
    }
};

const deleteItem = (id, name) => {
    if (confirm(`CẢNH BÁO: Xóa nhà cung cấp "${name}" có thể ảnh hưởng đến lịch sử thanh toán.\n\nBạn vẫn chắc chắn muốn xóa?`)) {
        router.delete(route('admin.nhacungcap.destroy', id), { preserveScroll: true });
    }
};

const formatPagination = (label) => {
    if (label.includes('Previous')) return '«';
    if (label.includes('Next')) return '»';
    return label;
};
</script>

<template>
    <Head title="Nhà cung cấp - Admin"/>

    <div class="py-8 bg-[#F8FAFC] min-h-[calc(100vh-64px)]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-6 border-b border-slate-200 pb-5">
                <div class="w-full md:w-1/2 lg:w-1/3">
                    <h2 class="font-black text-2xl text-slate-900 tracking-tight">Đối tác & Nhà cung cấp</h2>
                    <p class="text-sm font-medium text-slate-500 mt-1 mb-4">Quản lý danh bạ đối tác và thông tin thanh toán</p>
                    <div class="relative w-full">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                        <input type="text" v-model="searchQuery" placeholder="Tìm tên đối tác, MST..."
                               class="w-full pl-9 pr-3 py-2 text-sm font-medium border border-slate-300 rounded-lg focus:ring-1 focus:ring-slate-900 focus:border-slate-900 shadow-sm transition-all placeholder:text-slate-400">
                    </div>
                </div>

                <button @click="openAdd" class="shrink-0 bg-slate-900 hover:bg-slate-800 text-white text-sm font-bold py-2.5 px-5 rounded-lg shadow-sm hover:shadow transition-all flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                    Thêm đối tác mới
                </button>
            </div>

            <div class="animate-fade-in">

                <transition enter-active-class="transition ease-out duration-200" enter-from-class="opacity-0 -translate-y-2" enter-to-class="opacity-100 translate-y-0" leave-active-class="transition ease-in duration-150" leave-from-class="opacity-100" leave-to-class="opacity-0">
                    <div v-if="showForm" class="bg-white rounded-xl border border-slate-200 shadow-sm mb-8 overflow-hidden">

                        <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                            <div class="flex items-center gap-3">
                                <div class="w-2 h-6 rounded-full" :class="editingId ? 'bg-amber-400' : 'bg-emerald-500'"></div>
                                <h3 class="font-black text-lg text-slate-900">{{ editingId ? 'Cập nhật thông tin đối tác' : 'Tạo hồ sơ đối tác mới' }}</h3>
                            </div>
                            <button @click="showForm = false" class="text-slate-400 hover:text-slate-700 bg-white p-1 rounded-md border border-slate-200 shadow-sm transition-colors">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </div>

                        <form @submit.prevent="submit" class="flex flex-col">
                            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-x-10 gap-y-8">

                                <div class="space-y-4">
                                    <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100 pb-2 mb-4">1. Thông tin liên hệ</h4>

                                    <div class="space-y-2">
                                        <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-widest">Tên Nhà cung cấp <span class="text-red-500">*</span></label>
                                        <input v-model="form.ten_nha_cung_cap" type="text" class="w-full px-3 py-2.5 text-sm font-medium border border-slate-300 rounded-lg focus:ring-1 focus:ring-slate-900 focus:border-slate-900 transition-all" placeholder="VD: Công ty TNHH ABC" required>
                                        <p v-if="form.errors.ten_nha_cung_cap" class="text-red-500 text-xs font-medium">{{ form.errors.ten_nha_cung_cap }}</p>
                                    </div>

                                    <div class="grid grid-cols-2 gap-4">
                                        <div class="space-y-2">
                                            <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-widest">Mã số thuế</label>
                                            <input v-model="form.ma_so_thue" type="text" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-1 focus:ring-slate-900 focus:border-slate-900 transition-all font-mono" placeholder="0123456789">
                                        </div>
                                        <div class="space-y-2">
                                            <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-widest">Số điện thoại</label>
                                            <input v-model="form.so_dien_thoai" type="text" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-1 focus:ring-slate-900 focus:border-slate-900 transition-all" placeholder="09xx...">
                                        </div>
                                    </div>

                                    <div class="space-y-2">
                                        <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-widest">Địa chỉ trụ sở</label>
                                        <input v-model="form.dia_chi" type="text" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-1 focus:ring-slate-900 focus:border-slate-900 transition-all" placeholder="Số nhà, đường, quận/huyện...">
                                    </div>
                                </div>

                                <div class="space-y-4">
                                    <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100 pb-2 mb-4">2. Hồ sơ thanh toán</h4>

                                    <div class="space-y-2">
                                        <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-widest">Ngân hàng thụ hưởng</label>
                                        <input v-model="form.ngan_hang" type="text" placeholder="VD: Vietcombank, MBBank..." class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-1 focus:ring-slate-900 focus:border-slate-900 transition-all">
                                    </div>

                                    <div class="grid grid-cols-2 gap-4">
                                        <div class="space-y-2">
                                            <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-widest">Số tài khoản</label>
                                            <input v-model="form.so_tai_khoan" type="text" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-1 focus:ring-slate-900 focus:border-slate-900 transition-all font-mono font-black text-slate-800" placeholder="Chỉ nhập số">
                                        </div>
                                        <div class="space-y-2">
                                            <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-widest">Chủ tài khoản</label>
                                            <input v-model="form.chu_tai_khoan" type="text" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-1 focus:ring-slate-900 focus:border-slate-900 transition-all uppercase font-medium" placeholder="Viết không dấu">
                                        </div>
                                    </div>
                                </div>

                            </div>

                            <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex justify-end gap-3 mt-auto">
                                <button type="button" @click="showForm = false" class="px-5 py-2 text-sm font-bold text-slate-600 bg-white border border-slate-300 hover:bg-slate-100 rounded-lg transition-colors shadow-sm">Hủy bỏ</button>
                                <button type="submit" :disabled="form.processing" class="px-6 py-2 text-sm font-bold text-white bg-slate-900 hover:bg-slate-800 rounded-lg shadow-sm transition-colors disabled:opacity-50 flex items-center gap-2">
                                    <span v-if="form.processing" class="w-4 h-4 border-2 border-white/20 border-t-white rounded-full animate-spin"></span>
                                    {{ editingId ? 'Lưu cập nhật' : 'Thêm đối tác' }}
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
                                    <th class="px-6 py-4">Hồ sơ đối tác</th>
                                    <th class="px-6 py-4">Liên hệ & Địa chỉ</th>
                                    <th class="px-6 py-4">Tài khoản nhận tiền</th>
                                    <th class="px-6 py-4 text-right">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-for="ncc in nhaCungCaps.data" :key="ncc.id" class="hover:bg-slate-50/70 transition-colors group">
                                    <td class="px-6 py-4">
                                        <p class="text-sm font-bold text-slate-900 group-hover:text-blue-600 transition-colors">{{ ncc.ten_nha_cung_cap }}</p>
                                        <p class="text-[11px] font-mono text-slate-500 mt-1" v-if="ncc.ma_so_thue">MST: <span class="font-bold text-slate-700 bg-slate-100 px-1.5 py-0.5 rounded border border-slate-200">{{ ncc.ma_so_thue }}</span></p>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-col gap-1">
                                            <span class="text-xs font-bold text-slate-700 flex items-center gap-1.5">
                                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>
                                                {{ ncc.so_dien_thoai || '--' }}
                                            </span>
                                            <span class="text-[11px] font-medium text-slate-500 w-48 truncate" :title="ncc.dia_chi">{{ ncc.dia_chi || '--' }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div v-if="ncc.ngan_hang || ncc.so_tai_khoan" class="flex flex-col bg-white border border-slate-200 shadow-sm rounded-lg px-3 py-2 w-fit relative overflow-hidden">
                                            <div class="absolute left-0 top-0 bottom-0 w-1 bg-emerald-500"></div>
                                            <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider mb-0.5">{{ ncc.ngan_hang }}</span>
                                            <span class="text-sm font-mono font-black text-slate-800 tracking-tight">{{ ncc.so_tai_khoan }}</span>
                                            <span class="text-[10px] font-bold text-slate-600 uppercase mt-0.5">{{ ncc.chu_tai_khoan }}</span>
                                        </div>
                                        <span v-else class="text-[11px] italic text-slate-400 bg-slate-50 px-2 py-1 rounded-md border border-slate-100">Chưa có thông tin</span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                            <button @click="openEdit(ncc)" title="Chỉnh sửa"
                                                    class="p-1.5 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                            </button>
                                            <button @click="deleteItem(ncc.id, ncc.ten_nha_cung_cap)" title="Xóa nhà cung cấp"
                                                    class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="!nhaCungCaps.data.length">
                                    <td colspan="4" class="px-6 py-12 text-center flex flex-col items-center justify-center">
                                        <svg class="w-10 h-10 text-slate-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                                        <p class="text-sm font-bold text-slate-700">Chưa có nhà cung cấp nào</p>
                                        <p class="text-xs text-slate-500 mt-1">Vui lòng thêm mới hồ sơ đối tác.</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="border-t border-slate-100 bg-white p-4 flex justify-between items-center" v-if="nhaCungCaps.links.length > 3">
                        <p class="text-xs font-medium text-slate-500 hidden sm:block">
                            Hiển thị <span class="font-bold text-slate-800">{{ nhaCungCaps.from || 0 }}</span> đến <span class="font-bold text-slate-800">{{ nhaCungCaps.to || 0 }}</span>
                        </p>
                        <div class="flex flex-wrap gap-1">
                            <template v-for="(link, index) in nhaCungCaps.links" :key="index">
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
</style>
