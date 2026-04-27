<script setup>
import { ref, watch } from 'vue';
import { Link, router, Head, useForm } from '@inertiajs/vue3';
import StatusBadge from '@/Components/UI/StatusBadge.vue';

const props = defineProps({
    users: Object,
    phongBans: Array,
    filters: Object
});

// --- TÌM KIẾM (SEARCH) ---
const searchQuery = ref(props.filters?.search || '');
let searchTimeout = null;

watch(searchQuery, (value) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get(route('admin.users.index'), { search: value }, {
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
    name: '',
    email: '',
    password: '',
    vai_tro: 'nhan_vien',
    phong_ban_id: '',
    tong_ngay_phep: 12,
    trang_thai: true,
});

const roleLabels = {
    'admin': 'Quản trị viên',
    'nhan_vien': 'Nhân viên',
    'truong_phong': 'Trưởng phòng',
    'giam_doc': 'Giám đốc',
    'ke_toan': 'Kế toán',
    'nhan_su': 'Nhân sự',
    'nhan_vien_mua_sam': 'Mua sắm'
};

// FIX UX: Tự động cuộn lên đầu trang khi mở form
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

const openEdit = (user) => {
    editingId.value = user.id;
    form.name = user.name;
    form.email = user.email;
    form.password = '';
    form.vai_tro = user.vai_tro;
    form.phong_ban_id = user.phong_ban_id || '';
    form.tong_ngay_phep = user.tong_ngay_phep;
    form.trang_thai = user.trang_thai;
    form.clearErrors();
    showForm.value = true;
    scrollToTop();
};

const submit = () => {
    if (editingId.value) {
        form.put(route('admin.users.update', editingId.value), {
            preserveScroll: true,
            onSuccess: () => { showForm.value = false; form.reset(); }
        });
    } else {
        form.post(route('admin.users.store'), {
            preserveScroll: true,
            onSuccess: () => { showForm.value = false; form.reset(); }
        });
    }
};

const deleteUser = (id, name) => {
    if (confirm(`CẢNH BÁO: Việc xóa nhân viên "${name}" có thể làm mất dữ liệu phiếu yêu cầu liên quan.\n\nKhuyên dùng: Sửa trạng thái thành "Đã nghỉ việc".\n\nBạn vẫn chắc chắn muốn xóa cứng?`)) {
        router.delete(route('admin.users.destroy', id), { preserveScroll: true });
    }
};

const formatPagination = (label) => {
    if (label.includes('Previous')) return '«';
    if (label.includes('Next')) return '»';
    return label;
};
</script>

<template>
    <Head title="Quản lý Nhân sự - Admin"/>

    <div class="py-8 bg-[#F8FAFC] min-h-[calc(100vh-64px)]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-6 pb-5 border-b border-slate-200">
                <div class="w-full md:w-1/2 lg:w-1/3">
                    <h2 class="font-black text-2xl text-slate-900 tracking-tight">Hồ sơ Nhân sự</h2>
                    <p class="text-sm font-medium text-slate-500 mt-1 mb-4">Quản lý tài khoản, phòng ban và phân quyền hệ thống</p>
                    <div class="relative w-full">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                        <input type="text" v-model="searchQuery" placeholder="Tìm tên hoặc email nhân viên..."
                               class="w-full pl-9 pr-3 py-2 text-sm font-medium border border-slate-300 rounded-lg focus:ring-1 focus:ring-slate-900 focus:border-slate-900 shadow-sm transition-all placeholder:text-slate-400">
                    </div>
                </div>

                <button @click="openAdd" class="shrink-0 bg-slate-900 hover:bg-slate-800 text-white text-sm font-bold py-2.5 px-5 rounded-lg shadow-sm hover:shadow transition-all flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                    Thêm nhân sự mới
                </button>
            </div>

            <div class="animate-fade-in">

                <transition enter-active-class="transition ease-out duration-200" enter-from-class="opacity-0 -translate-y-2" enter-to-class="opacity-100 translate-y-0" leave-active-class="transition ease-in duration-150" leave-from-class="opacity-100" leave-to-class="opacity-0">
                    <div v-if="showForm" class="bg-white rounded-xl border border-slate-200 shadow-sm mb-8 overflow-hidden">

                        <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                            <div class="flex items-center gap-3">
                                <div class="w-2 h-6 rounded-full" :class="editingId ? 'bg-amber-400' : 'bg-emerald-500'"></div>
                                <h3 class="font-black text-lg text-slate-900">{{ editingId ? 'Cập nhật hồ sơ nhân sự' : 'Tạo tài khoản nhân sự mới' }}</h3>
                            </div>
                            <button @click="showForm = false" class="text-slate-400 hover:text-slate-700 bg-white p-1 rounded-md border border-slate-200 shadow-sm transition-colors">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </div>

                        <form @submit.prevent="submit" class="flex flex-col">
                            <div class="p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                <div class="space-y-2">
                                    <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-widest">Họ và Tên <span class="text-red-500">*</span></label>
                                    <input v-model="form.name" type="text" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-1 focus:ring-slate-900 focus:border-slate-900 transition-all placeholder:text-slate-400" placeholder="Nguyễn Văn A" required>
                                    <p v-if="form.errors.name" class="text-red-500 text-xs font-medium">{{ form.errors.name }}</p>
                                </div>

                                <div class="space-y-2">
                                    <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-widest">Email <span class="text-red-500">*</span></label>
                                    <input v-model="form.email" type="email" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-1 focus:ring-slate-900 focus:border-slate-900 transition-all placeholder:text-slate-400" placeholder="email@congty.com" required>
                                    <p v-if="form.errors.email" class="text-red-500 text-xs font-medium">{{ form.errors.email }}</p>
                                </div>

                                <div class="space-y-2">
                                    <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-widest">Mật khẩu <span v-if="!editingId" class="text-red-500">*</span></label>
                                    <input v-model="form.password" type="password" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-1 focus:ring-slate-900 focus:border-slate-900 transition-all placeholder:text-slate-400" :placeholder="editingId ? 'Bỏ trống nếu không đổi' : '••••••••'" :required="!editingId">
                                    <p v-if="form.errors.password" class="text-red-500 text-xs font-medium">{{ form.errors.password }}</p>
                                </div>

                                <div class="space-y-2">
                                    <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-widest">Phòng ban</label>
                                    <select v-model="form.phong_ban_id" class="w-full px-3 py-2 text-sm font-medium border border-slate-300 rounded-lg focus:ring-1 focus:ring-slate-900 focus:border-slate-900 transition-all cursor-pointer bg-white">
                                        <option value="">-- Ban Giám Đốc / Chưa xếp --</option>
                                        <option v-for="pb in phongBans" :key="pb.id" :value="pb.id">{{ pb.ten_phong_ban }}</option>
                                    </select>
                                </div>

                                <div class="space-y-2">
                                    <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-widest">Phân quyền (Role) <span class="text-red-500">*</span></label>
                                    <select v-model="form.vai_tro" class="w-full px-3 py-2 text-sm font-medium border border-slate-300 rounded-lg focus:ring-1 focus:ring-slate-900 focus:border-slate-900 transition-all cursor-pointer bg-white" required>
                                        <option v-for="(label, value) in roleLabels" :key="value" :value="value">{{ label }}</option>
                                    </select>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div class="space-y-2">
                                        <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-widest">Phép năm</label>
                                        <input v-model="form.tong_ngay_phep" type="number" step="0.5" min="0" class="w-full px-3 py-2 text-sm font-bold border border-slate-300 rounded-lg focus:ring-1 focus:ring-slate-900 focus:border-slate-900 transition-all">
                                    </div>

                                    <div v-if="editingId" class="space-y-2">
                                        <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-widest">Trạng thái</label>
                                        <select v-model="form.trang_thai" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-1 focus:ring-slate-900 focus:border-slate-900 transition-all cursor-pointer" :class="form.trang_thai ? 'bg-emerald-50 text-emerald-700 font-bold border-emerald-200' : 'bg-rose-50 text-rose-700 font-bold border-rose-200'">
                                            <option :value="true">Hoạt động</option>
                                            <option :value="false">Đã nghỉ việc</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex justify-end gap-3 mt-auto">
                                <button type="button" @click="showForm = false" class="px-5 py-2 text-sm font-bold text-slate-600 bg-white border border-slate-300 hover:bg-slate-100 rounded-lg transition-colors shadow-sm">Hủy bỏ</button>
                                <button type="submit" :disabled="form.processing" class="px-6 py-2 text-sm font-bold text-white bg-slate-900 hover:bg-slate-800 rounded-lg shadow-sm transition-colors disabled:opacity-50 flex items-center gap-2">
                                    <span v-if="form.processing" class="w-4 h-4 border-2 border-white/20 border-t-white rounded-full animate-spin"></span>
                                    {{ editingId ? 'Lưu cập nhật' : 'Tạo tài khoản' }}
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
                                    <th class="px-6 py-4">Nhân sự</th>
                                    <th class="px-6 py-4">Chức vụ & Phòng ban</th>
                                    <th class="px-6 py-4 text-center">Phép năm</th>
                                    <th class="px-6 py-4 text-center">Trạng thái</th>
                                    <th class="px-6 py-4 text-right">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-for="user in users.data" :key="user.id" class="hover:bg-slate-50 transition-colors group">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3.5">
                                            <img v-if="user.avatar" :src="'/storage/' + user.avatar" class="w-10 h-10 rounded-full object-cover border border-slate-200 shadow-sm">
                                            <div v-else class="w-10 h-10 rounded-full bg-slate-100 text-slate-600 font-bold flex items-center justify-center text-sm shadow-sm border border-slate-200">
                                                {{ user.name.charAt(0).toUpperCase() }}
                                            </div>
                                            <div>
                                                <p class="text-sm font-bold text-slate-900">{{ user.name }}</p>
                                                <p class="text-[11px] font-medium text-slate-500">{{ user.email }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-col items-start gap-1.5">
                                            <span class="inline-flex px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider ring-1 ring-inset"
                                                  :class="user.vai_tro === 'admin' ? 'bg-purple-50 text-purple-700 ring-purple-200/50' : 'bg-slate-50 text-slate-600 ring-slate-200'">
                                                {{ roleLabels[user.vai_tro] }}
                                            </span>
                                            <span class="text-[11px] font-medium text-slate-500">
                                                {{ user.phong_ban ? user.phong_ban.ten_phong_ban : 'Chưa xếp phòng' }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="inline-flex flex-col items-center">
                                            <span class="text-sm font-black text-slate-800">{{ user.tong_ngay_phep - user.ngay_phep_da_dung }} <span class="text-[11px] font-medium text-slate-400">/ {{ user.tong_ngay_phep }}</span></span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider ring-1 ring-inset"
                                              :class="user.trang_thai ? 'bg-emerald-50 text-emerald-700 ring-emerald-200/50' : 'bg-rose-50 text-rose-700 ring-rose-200/50'">
                                            <span class="w-1.5 h-1.5 rounded-full" :class="user.trang_thai ? 'bg-emerald-500' : 'bg-rose-500'"></span>
                                            {{ user.trang_thai ? 'Hoạt động' : 'Đã nghỉ' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                            <button @click="openEdit(user)" title="Chỉnh sửa"
                                                    class="p-1.5 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                            </button>
                                            <button @click="deleteUser(user.id, user.name)" title="Xóa tài khoản"
                                                    class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="!users.data.length">
                                    <td colspan="5" class="px-6 py-12 text-center flex flex-col items-center justify-center">
                                        <svg class="w-10 h-10 text-slate-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                                        <p class="text-sm font-bold text-slate-700">Không tìm thấy nhân sự</p>
                                        <p class="text-xs text-slate-500 mt-1">Không có dữ liệu nào khớp với từ khóa tìm kiếm.</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="border-t border-slate-100 bg-white p-4 flex justify-between items-center" v-if="users.links.length > 3">
                        <p class="text-xs font-medium text-slate-500 hidden sm:block">
                            Hiển thị <span class="font-bold text-slate-800">{{ users.from || 0 }}</span> đến <span class="font-bold text-slate-800">{{ users.to || 0 }}</span> trong tổng <span class="font-bold text-slate-800">{{ users.total }}</span> nhân sự
                        </p>
                        <div class="flex flex-wrap gap-1">
                            <template v-for="(link, index) in users.links" :key="index">
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
