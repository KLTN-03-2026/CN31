<script setup>
import { useForm, usePage, Head } from '@inertiajs/vue3';
import { ref, watch } from 'vue'; // THÊM ref và watch
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineOptions({ layout: AdminLayout });

const props = defineProps({ settings: Array });
const page = usePage();

const showSuccessAlert = ref(false);

watch(
    () => page.props.flash.success,
    (newValue) => {
        if (newValue) {
            showSuccessAlert.value = true;
            setTimeout(() => {
                showSuccessAlert.value = false;
                page.props.flash.success = null;
            }, 3000); // Tắt sau 3000ms (3 giây)
        }
    },
    { immediate: true }
);

const labelDictionary = {
    'vat_tax': 'Thuế giá trị gia tăng (VAT %)',
    'han_muc_giam_doc_duyet': 'Hạn mức Giám đốc duyệt (VNĐ)',
    'thong_bao_bao_tri': 'Chế độ bảo trì hệ thống'
};

const getLabel = (key) => labelDictionary[key] || key.replace(/_/g, ' ').toUpperCase();

const form = useForm({
    settings: props.settings.map(s => ({
        key: s.key,
        value: s.type === 'boolean' ? (s.value === '1' || s.value === 'true') : s.value,
        type: s.type,
        description: s.description
    }))
});

const submit = () => {
    form.post(route('admin.settings.update'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Cài đặt hệ thống - Admin" />

    <div class="py-6 min-h-[calc(100vh-64px)] bg-transparent">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- HEADER -->
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-6 pb-5 border-b border-slate-200">
                <div class="w-full lg:w-1/2">
                    <h2 class="font-black text-2xl text-slate-900 tracking-tight">Cấu hình hệ thống</h2>
                    <p class="text-sm font-medium text-slate-500 mt-1 mb-4">Quản lý các thông số cốt lõi và giới hạn duyệt của hệ thống Procureflow.</p>
                </div>
            </div>

            <!-- HIỂN THỊ THÔNG BÁO THÀNH CÔNG TỪ BACKEND -->
            <transition enter-active-class="transition ease-out duration-300" enter-from-class="opacity-0 -translate-y-2" enter-to-class="opacity-100 translate-y-0" leave-active-class="transition ease-in duration-200" leave-from-class="opacity-100" leave-to-class="opacity-0">
                <div v-if="showSuccessAlert" class="mb-6 p-4 bg-emerald-50 border border-emerald-200 rounded-xl flex items-center gap-3 shadow-sm">
                    <div class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-emerald-800">Thành công!</h4>
                        <!-- Lấy text từ page.props -->
                        <p class="text-[13px] font-medium text-emerald-600">{{ page.props.flash.success }}</p>
                    </div>
                </div>
            </transition>

            <!-- FORM CONTENT -->
            <div class="animate-fade-in bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden mb-8">

                <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                    <div class="flex items-center gap-3">
                        <div class="w-2 h-6 rounded-full bg-emerald-500"></div>
                        <h3 class="font-black text-lg text-slate-900">Thông số cấu hình</h3>
                    </div>
                </div>

                <form @submit.prevent="submit" class="flex flex-col">
                    <div class="p-6 space-y-6">
                        <!-- Lặp qua các mảng Settings -->
                        <div v-for="(setting, index) in form.settings" :key="setting.key"
                             class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pb-6 border-b border-slate-100 last:border-0 last:pb-0">

                            <div class="w-full lg:w-1/2 space-y-1">
                                <!-- ĐÃ SỬA: Gọi hàm getLabel để lấy tiếng Việt -->
                                <label class="block text-[13px] font-bold text-slate-800 uppercase tracking-wide">
                                    {{ getLabel(setting.key) }}
                                </label>
                                <p class="text-[13px] font-medium text-slate-500">{{ setting.description }}</p>
                            </div>

                            <div class="w-full lg:w-1/3">
                                <!-- Type: Boolean (Công tắc Toggle Switch) -->
                                <label v-if="setting.type === 'boolean'" class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" v-model="form.settings[index].value" class="sr-only peer">
                                    <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                                </label>

                                <!-- Type: Number -->
                                <div v-else-if="setting.type === 'number'" class="relative">
                                    <input type="number" v-model="form.settings[index].value"
                                           class="w-full px-3 py-2 text-sm font-bold text-slate-800 border border-slate-300 rounded-lg focus:ring-1 focus:ring-slate-900 focus:border-slate-900 transition-all shadow-sm">
                                </div>

                                <!-- Type: Text -->
                                <input v-else type="text" v-model="form.settings[index].value"
                                       class="w-full px-3 py-2 text-sm font-medium text-slate-800 border border-slate-300 rounded-lg focus:ring-1 focus:ring-slate-900 focus:border-slate-900 transition-all shadow-sm">
                            </div>
                        </div>
                    </div>

                    <!-- FOOTER BUTTON -->
                    <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex justify-end gap-3 mt-auto">
                        <button type="submit" :disabled="form.processing"
                                class="px-6 py-2.5 text-sm font-bold text-white bg-slate-900 hover:bg-slate-800 rounded-lg shadow-sm transition-colors disabled:opacity-50 flex items-center gap-2">
                            <!-- Hiệu ứng xoay khi đang lưu -->
                            <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>

                            {{ form.processing ? 'Đang lưu...' : 'Lưu cấu hình' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<style scoped>
.animate-fade-in { animation: fadeIn 0.2s ease-out; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }
</style>
