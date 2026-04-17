<script setup>
import { useForm, Head, Link } from '@inertiajs/vue3';
import { watch } from 'vue';

const props = defineProps({
    usersBanGiao: Array
});

const form = useForm({
    tieu_de: 'Đơn xin nghỉ phép',
    loai_nghi_phep: 'nghi_phep_nam',
    ngay_bat_dau: '',
    ngay_ket_thuc: '',
    so_ngay_nghi: 0,
    nguoi_ban_giao_id: '',
    ly_do: ''
});

watch([() => form.ngay_bat_dau, () => form.ngay_ket_thuc], ([start, end]) => {
    if (start && end) {
        const startDate = new Date(start);
        const endDate = new Date(end);
        if (endDate < startDate) {
            form.so_ngay_nghi = 0;
            return;
        }
        const diffTime = Math.abs(endDate - startDate);
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
        form.so_ngay_nghi = diffDays;
    } else {
        form.so_ngay_nghi = 0;
    }
});

const submit = () => {
    form.post(route('nghiphep.store'));
};
</script>

<template>
    <Head title="Xin Nghỉ Phép" />

    <div class="fixed inset-0 z-[100] bg-slate-900/60 backdrop-blur-sm flex justify-end transition-opacity">

        <Link :href="route('dashboard')" class="absolute inset-0 cursor-default"></Link>

        <div class="relative w-full max-w-[600px] bg-white h-full shadow-2xl flex flex-col animate-slide-in-right">

            <div class="px-6 py-5 border-b border-slate-200 flex justify-between items-center bg-slate-50 shrink-0">
                <div>
                    <h2 class="text-lg font-black text-slate-800 uppercase tracking-wide flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Đơn Xin Nghỉ Phép
                    </h2>
                    <p class="text-xs text-slate-500 mt-1 font-medium">Bàn giao công việc kỹ lưỡng trước khi nghỉ.</p>
                </div>
                <Link :href="route('dashboard')" class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                </Link>
            </div>

            <form @submit.prevent="submit" class="flex flex-col flex-grow overflow-hidden">

                <div class="flex-grow overflow-y-auto px-6 py-6 custom-scrollbar">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-6">
                        <div>
                            <label class="block font-semibold text-xs text-slate-700 uppercase tracking-wide mb-1.5">Loại nghỉ phép <span class="text-red-500">*</span></label>
                            <select v-model="form.loai_nghi_phep" class="w-full border-slate-300 focus:border-blue-600 focus:ring-blue-600 rounded-lg shadow-sm py-2.5 text-sm bg-slate-50">
                                <option value="nghi_phep_nam">Phép năm (Có lương)</option>
                                <option value="nghi_om">Nghỉ ốm (Hưởng BHXH)</option>
                                <option value="viec_rieng">Việc riêng (Không lương)</option>
                                <option value="che_do">Chế độ (Thai sản, Cưới...)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block font-semibold text-xs text-slate-700 uppercase tracking-wide mb-1.5">Tiêu đề đơn <span class="text-red-500">*</span></label>
                            <input v-model="form.tieu_de" type="text" class="w-full border-slate-300 focus:border-blue-600 focus:ring-blue-600 rounded-lg shadow-sm py-2.5 text-sm" placeholder="VD: Xin nghỉ phép cá nhân...">
                            <div v-if="form.errors.tieu_de" class="text-red-500 text-xs mt-1">{{ form.errors.tieu_de }}</div>
                        </div>
                    </div>

                    <div class="bg-slate-50 p-5 rounded-xl border border-slate-200 mb-6">
                        <h3 class="font-bold text-slate-600 mb-4 text-xs uppercase tracking-wider">Thời gian đăng ký nghỉ</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 items-center">
                            <div>
                                <label class="block font-semibold text-xs text-slate-700 mb-1.5">Từ ngày <span class="text-red-500">*</span></label>
                                <input v-model="form.ngay_bat_dau" type="date" class="w-full border-slate-300 focus:border-blue-600 focus:ring-blue-600 rounded-lg shadow-sm py-2 text-sm">
                                <div v-if="form.errors.ngay_bat_dau" class="text-red-500 text-[10px] mt-1">{{ form.errors.ngay_bat_dau }}</div>
                            </div>

                            <div>
                                <label class="block font-semibold text-xs text-slate-700 mb-1.5">Đến hết ngày <span class="text-red-500">*</span></label>
                                <input v-model="form.ngay_ket_thuc" type="date" :min="form.ngay_bat_dau" class="w-full border-slate-300 focus:border-blue-600 focus:ring-blue-600 rounded-lg shadow-sm py-2 text-sm">
                                <div v-if="form.errors.ngay_ket_thuc" class="text-red-500 text-[10px] mt-1">{{ form.errors.ngay_ket_thuc }}</div>
                            </div>

                            <div class="text-center bg-white p-2.5 rounded-lg border border-slate-200 shadow-sm">
                                <p class="text-[10px] text-slate-400 font-bold uppercase mb-0.5">Tổng số ngày</p>
                                <p class="text-2xl font-black text-emerald-600">{{ form.so_ngay_nghi }} <span class="text-xs font-medium text-slate-400">ngày</span></p>
                            </div>
                        </div>
                    </div>

                    <div class="mb-6">
                        <label class="block font-semibold text-xs text-slate-700 uppercase tracking-wide mb-1.5">Bàn giao công việc cho</label>
                        <select v-model="form.nguoi_ban_giao_id" class="w-full border-slate-300 focus:border-blue-600 focus:ring-blue-600 rounded-lg shadow-sm py-2.5 text-sm bg-slate-50">
                            <option value="">-- Không cần bàn giao --</option>
                            <option v-for="user in usersBanGiao" :key="user.id" :value="user.id">{{ user.name }}</option>
                        </select>
                    </div>

                    <div class="mb-2">
                        <label class="block font-semibold text-xs text-slate-700 uppercase tracking-wide mb-1.5">Lý do / Công việc bàn giao <span class="text-red-500">*</span></label>
                        <textarea v-model="form.ly_do" rows="4" class="w-full border-slate-300 focus:border-blue-600 focus:ring-blue-600 rounded-lg shadow-sm py-2.5 text-sm" placeholder="Ghi chú công việc cần người khác theo dõi giúp..."></textarea>
                        <div v-if="form.errors.ly_do" class="text-red-500 text-xs mt-1">{{ form.errors.ly_do }}</div>
                    </div>
                </div>

                <div class="px-6 py-4 border-t border-slate-200 bg-slate-50 flex items-center justify-between shrink-0">
                    <Link :href="route('dashboard')" class="text-sm font-bold text-slate-500 hover:text-slate-800 transition-colors">Hủy bỏ</Link>
                    <button :disabled="form.processing || form.so_ngay_nghi <= 0" class="flex items-center gap-2 bg-blue-600 text-white px-6 py-2.5 rounded-lg font-bold hover:bg-blue-700 shadow-sm transition-all disabled:opacity-50 disabled:cursor-not-allowed text-sm">
                        <svg v-if="!form.processing" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" /></svg>
                        <svg v-else class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        {{ form.processing ? 'ĐANG GỬI...' : 'TRÌNH DUYỆT' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

    <style scoped>
    .animate-slide-in-right { animation: slideInRight 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
    @keyframes slideInRight { 0% { transform: translateX(100%); } 100% { transform: translateX(0); } }
    </style>
