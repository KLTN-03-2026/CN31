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

// Logic tự động tính số ngày nghỉ
watch([() => form.ngay_bat_dau, () => form.ngay_ket_thuc], ([start, end]) => {
    if (start && end) {
        const startDate = new Date(start);
        const endDate = new Date(end);

        // Nếu ngày kết thúc nhỏ hơn ngày bắt đầu thì reset
        if (endDate < startDate) {
            form.so_ngay_nghi = 0;
            return;
        }

        // Tính khoảng cách giữa 2 ngày (Đơn giản: 1 ngày = 24 * 60 * 60 * 1000 ms)
        const diffTime = Math.abs(endDate - startDate);
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1; // +1 để tính luôn ngày hôm đó

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
    <Head title="Xin Nghỉ Phép | ProcureFlow" />

    <div class="py-10">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-4">
                <Link :href="route('dashboard')" class="text-gray-500 hover:text-pink-600 font-medium flex items-center gap-1">
                    &larr; Quay lại Dashboard
                </Link>
            </div>

            <div class="bg-white overflow-hidden shadow-2xl sm:rounded-2xl p-8 border border-gray-100">

                <div class="mb-8 border-b pb-5 flex items-center gap-4">
                    <div class="bg-pink-100 p-3 rounded-full text-pink-600">
                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-3xl font-extrabold text-gray-900">Đơn Xin Nghỉ Phép</h2>
                        <p class="text-gray-500 mt-1 text-sm font-medium">Lưu ý: Bàn giao công việc kỹ lưỡng trước khi nghỉ.</p>
                    </div>
                </div>

                <form @submit.prevent="submit">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block font-bold text-sm text-gray-800 mb-2">Loại nghỉ phép <span class="text-red-500">*</span></label>
                            <select v-model="form.loai_nghi_phep" class="border-gray-300 focus:border-pink-500 focus:ring-pink-500 rounded-lg shadow-sm w-full py-2.5">
                                <option value="nghi_phep_nam">Phép năm (Có lương)</option>
                                <option value="nghi_om">Nghỉ ốm (Hưởng BHXH)</option>
                                <option value="viec_rieng">Việc riêng (Không lương)</option>
                                <option value="che_do">Nghỉ chế độ (Thai sản, Cưới hỏi...)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block font-bold text-sm text-gray-800 mb-2">Tiêu đề đơn <span class="text-red-500">*</span></label>
                            <input v-model="form.tieu_de" type="text" class="border-gray-300 focus:border-pink-500 focus:ring-pink-500 rounded-lg shadow-sm w-full py-2.5" placeholder="VD: Xin nghỉ phép đi du lịch...">
                            <div v-if="form.errors.tieu_de" class="text-red-500 text-xs mt-1">{{ form.errors.tieu_de }}</div>
                        </div>
                    </div>

                    <div class="bg-pink-50 p-6 rounded-xl border border-pink-100 mb-6">
                        <h3 class="font-bold text-pink-800 mb-4 text-sm uppercase tracking-wider">Thời gian đăng ký nghỉ</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
                            <div>
                                <label class="block font-bold text-sm text-gray-700 mb-2">Từ ngày <span class="text-red-500">*</span></label>
                                <input v-model="form.ngay_bat_dau" type="date" class="border-gray-300 focus:border-pink-500 focus:ring-pink-500 rounded-lg shadow-sm w-full py-2">
                                <div v-if="form.errors.ngay_bat_dau" class="text-red-500 text-xs mt-1">{{ form.errors.ngay_bat_dau }}</div>
                            </div>

                            <div>
                                <label class="block font-bold text-sm text-gray-700 mb-2">Đến hết ngày <span class="text-red-500">*</span></label>
                                <input v-model="form.ngay_ket_thuc" type="date" :min="form.ngay_bat_dau" class="border-gray-300 focus:border-pink-500 focus:ring-pink-500 rounded-lg shadow-sm w-full py-2">
                                <div v-if="form.errors.ngay_ket_thuc" class="text-red-500 text-xs mt-1">{{ form.errors.ngay_ket_thuc }}</div>
                            </div>

                            <div class="text-center bg-white p-3 rounded-lg border border-pink-200 shadow-sm">
                                <p class="text-xs text-gray-500 font-bold uppercase mb-1">Tổng số ngày</p>
                                <p class="text-3xl font-black text-pink-600">{{ form.so_ngay_nghi }} <span class="text-base font-medium text-gray-500">ngày</span></p>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block font-bold text-sm text-gray-800 mb-2">Bàn giao công việc cho</label>
                            <select v-model="form.nguoi_ban_giao_id" class="border-gray-300 focus:border-pink-500 focus:ring-pink-500 rounded-lg shadow-sm w-full py-2.5">
                                <option value="">-- Không cần bàn giao --</option>
                                <option v-for="user in usersBanGiao" :key="user.id" :value="user.id">
                                    {{ user.name }}
                                </option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-8">
                        <label class="block font-bold text-sm text-gray-800 mb-2">Lý do nghỉ / Chi tiết công việc bàn giao <span class="text-red-500">*</span></label>
                        <textarea v-model="form.ly_do" rows="4" class="border-gray-300 focus:border-pink-500 focus:ring-pink-500 rounded-lg shadow-sm w-full py-2.5" placeholder="Trình bày rõ lý do hoặc ghi chú công việc cần người khác theo dõi giúp..."></textarea>
                        <div v-if="form.errors.ly_do" class="text-red-500 text-xs mt-1">{{ form.errors.ly_do }}</div>
                    </div>

                    <div class="flex items-center justify-end border-t pt-6">
                        <button :disabled="form.processing || form.so_ngay_nghi <= 0"
                            class="flex items-center gap-2 bg-pink-600 text-white px-8 py-3 rounded-xl font-extrabold hover:bg-pink-700 shadow-lg hover:shadow-xl transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg v-if="!form.processing" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" /></svg>
                            <svg v-else class="animate-spin w-5 h-5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            {{ form.processing ? 'ĐANG GỬI...' : 'TRÌNH DUYỆT ĐƠN' }}
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</template>
