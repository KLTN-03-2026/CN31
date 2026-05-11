<script setup>
import { ref, watch } from 'vue';
import { Link, router, Head, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineOptions({
    layout: AdminLayout
});
const props = defineProps({
    danhMucs: Object,
    filters: Object
});

// --- TÌM KIẾM (SEARCH DEBOUNCE) ---
const searchQuery = ref(props.filters?.search || '');
let searchTimeout = null;

watch(searchQuery, (value) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get(route('admin.danhmuc.index'), { search: value }, {
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
    ten_danh_muc: '',
    mo_ta: '',
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

const openEdit = (dm) => {
    editingId.value = dm.id;
    form.ten_danh_muc = dm.ten_danh_muc;
    form.mo_ta = dm.mo_ta || '';
    form.clearErrors();
    showForm.value = true;
    scrollToTop();
};

const submit = () => {
    if (editingId.value) {
        form.put(route('admin.danhmuc.update', editingId.value), {
            preserveScroll: true,
            onSuccess: () => { showForm.value = false; form.reset(); }
        });
    } else {
        form.post(route('admin.danhmuc.store'), {
            preserveScroll: true,
            onSuccess: () => { showForm.value = false; form.reset(); }
        });
    }
};

const deleteItem = (id, name) => {
    if (confirm(`CẢNH BÁO: Bạn có chắc chắn muốn xóa danh mục "${name}"?\n\nLưu ý: Nếu danh mục này đã được sử dụng trong các Phiếu yêu cầu, hệ thống sẽ chặn lệnh xóa để bảo vệ dữ liệu.`)) {
        router.delete(route('admin.danhmuc.destroy', id), { preserveScroll: true });
    }
};

const formatPagination = (label) => {
    if (label.includes('Previous')) return '«';
    if (label.includes('Next')) return '»';
    return label;
};
</script>

<template>
    <Head title="Danh mục - Admin"/>

   <div class="py-6 min-h-[calc(100vh-64px)] bg-transparent">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-6 border-b border-slate-200 pb-5">
                <div class="w-full md:w-1/2">
                    <h2 class="font-black text-2xl text-slate-900 tracking-tight">Danh mục Tài sản</h2>
                    <p class="text-sm font-medium text-slate-500 mt-1 mb-4">Quản lý các hạng mục cho phép nhân viên mua sắm</p>
                    <div class="relative w-full">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                        <input type="text" v-model="searchQuery" placeholder="Tìm kiếm tên danh mục..."
                               class="w-full pl-9 pr-3 py-2 text-sm font-medium border border-slate-300 rounded-lg focus:ring-1 focus:ring-slate-900 focus:border-slate-900 shadow-sm transition-all placeholder:text-slate-400">
                    </div>
                </div>

                <button @click="openAdd" class="shrink-0 bg-slate-900 hover:bg-slate-800 text-white text-sm font-bold py-2.5 px-5 rounded-lg shadow-sm hover:shadow transition-all flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                    Thêm danh mục mới
                </button>
            </div>

            <div class="animate-fade-in">

                <transition enter-active-class="transition ease-out duration-200" enter-from-class="opacity-0 -translate-y-2" enter-to-class="opacity-100 translate-y-0" leave-active-class="transition ease-in duration-150" leave-from-class="opacity-100" leave-to-class="opacity-0">
                    <div v-if="showForm" class="bg-white rounded-xl border border-slate-200 shadow-sm mb-8 overflow-hidden">

                        <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                            <div class="flex items-center gap-3">
                                <div class="w-2 h-6 rounded-full" :class="editingId ? 'bg-amber-400' : 'bg-emerald-500'"></div>
                                <h3 class="font-black text-lg text-slate-900">{{ editingId ? 'Cập nhật Danh mục' : 'Tạo Danh mục mới' }}</h3>
                            </div>
                            <button @click="showForm = false" class="text-slate-400 hover:text-slate-700 bg-white p-1 rounded-md border border-slate-200 shadow-sm transition-colors">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </div>

                        <form @submit.prevent="submit" class="flex flex-col">
                            <div class="p-6 space-y-5">
                                <div class="space-y-2">
                                    <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-widest">Tên danh mục <span class="text-red-500">*</span></label>
                                    <input v-model="form.ten_danh_muc" type="text" placeholder="VD: Trang thiết bị IT, Văn phòng phẩm..."
                                           class="w-full px-3 py-2.5 text-sm border border-slate-300 rounded-lg focus:ring-1 focus:ring-slate-900 focus:border-slate-900 transition-all placeholder:text-slate-400" required>
                                    <p v-if="form.errors.ten_danh_muc" class="text-red-500 text-xs font-medium">{{ form.errors.ten_danh_muc }}</p>
                                </div>

                                <div class="space-y-2">
                                    <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-widest">Mô tả chi tiết</label>
                                    <textarea v-model="form.mo_ta" rows="3" placeholder="Ghi chú thêm về phạm vi của danh mục này (không bắt buộc)..."
                                              class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-1 focus:ring-slate-900 focus:border-slate-900 transition-all resize-none placeholder:text-slate-400"></textarea>
                                </div>
                            </div>

                            <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex justify-end gap-3 mt-auto">
                                <button type="button" @click="showForm = false" class="px-5 py-2 text-sm font-bold text-slate-600 bg-white border border-slate-300 hover:bg-slate-100 rounded-lg transition-colors shadow-sm">Hủy bỏ</button>
                                <button type="submit" :disabled="form.processing" class="px-6 py-2 text-sm font-bold text-white bg-slate-900 hover:bg-slate-800 rounded-lg shadow-sm transition-colors disabled:opacity-50 flex items-center gap-2">
                                    <span v-if="form.processing" class="w-4 h-4 border-2 border-white/20 border-t-white rounded-full animate-spin"></span>
                                    {{ editingId ? 'Lưu cập nhật' : 'Lưu danh mục' }}
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
                                    <th class="px-6 py-4 w-1/3">Tên danh mục</th>
                                    <th class="px-6 py-4 w-1/2">Mô tả</th>
                                    <th class="px-6 py-4 text-right">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-for="dm in danhMucs.data" :key="dm.id" class="hover:bg-slate-50 transition-colors group">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3.5">
                                            <div class="w-9 h-9 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 border border-emerald-100/50">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" /></svg>
                                            </div>
                                            <span class="text-sm font-bold text-slate-900 group-hover:text-blue-600 transition-colors">{{ dm.ten_danh_muc }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="text-xs font-medium text-slate-500 whitespace-normal line-clamp-2 leading-relaxed">
                                            {{ dm.mo_ta || '--' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                            <button @click="openEdit(dm)" title="Chỉnh sửa"
                                                    class="p-1.5 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                            </button>
                                            <button @click="deleteItem(dm.id, dm.ten_danh_muc)" title="Xóa danh mục"
                                                    class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="!danhMucs.data.length">
                                    <td colspan="3" class="px-6 py-12 text-center flex flex-col items-center justify-center">
                                        <svg class="w-10 h-10 text-slate-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" /></svg>
                                        <p class="text-sm font-bold text-slate-700">Chưa có danh mục nào</p>
                                        <p class="text-xs text-slate-500 mt-1">Dữ liệu trống hoặc không khớp với tìm kiếm.</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="border-t border-slate-100 bg-white p-4 flex justify-between items-center" v-if="danhMucs.links.length > 3">
                        <p class="text-xs font-medium text-slate-500 hidden sm:block">
                            Hiển thị <span class="font-bold text-slate-800">{{ danhMucs.from || 0 }}</span> đến <span class="font-bold text-slate-800">{{ danhMucs.to || 0 }}</span> trong tổng <span class="font-bold text-slate-800">{{ danhMucs.total }}</span> mục
                        </p>
                        <div class="flex flex-wrap gap-1">
                            <template v-for="(link, index) in danhMucs.links" :key="index">
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
