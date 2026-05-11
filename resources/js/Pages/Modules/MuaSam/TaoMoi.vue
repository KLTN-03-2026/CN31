<script setup>
import { useForm, Head, Link } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    danhMucs: Array
});

const form = useForm({
    tieu_de: '',
    ly_do: '',
    san_pham: [
        { ten_san_pham: '', danh_muc_id: '', so_luong: 1 }
    ]
});

const selectedTemplate = ref('');
const isCustomTitle = ref(false);

const requestTemplates = [
    {
        label: 'Mua sắm Thiết bị IT (Laptop, Chuột, Phím...)',
        title: 'Đề xuất mua sắm Thiết bị IT cho nhân viên',
        reason: 'Thiết bị hiện tại đã hỏng/xuống cấp, cần trang bị mới để đảm bảo hiệu suất công việc.'
    },
    {
        label: 'Mua sắm Văn phòng phẩm (Giấy, Bút, Bìa...)',
        title: 'Đề xuất cấp phát Văn phòng phẩm hàng tháng',
        reason: 'Bổ sung vật tư văn phòng phẩm định kỳ cho bộ phận sử dụng.'
    },
    {
        label: 'Mua sắm Tài sản cố định (Bàn, Ghế, Tủ...)',
        title: 'Đề xuất mua sắm Tài sản cố định / Nội thất',
        reason: 'Trang bị nội thất cho nhân sự mới / thay thế nội thất cũ.'
    },
    {
        label: 'Mua sắm Công cụ dụng cụ',
        title: 'Đề xuất cấp Công cụ dụng cụ làm việc',
        reason: 'Trang bị công cụ làm việc chuyên dụng cho dự án mới.'
    },
    {
        label: 'Khác (Tự nhập tiêu đề)',
        title: 'khac',
        reason: ''
    }
];

watch(selectedTemplate, (newVal) => {
    if (newVal === 'khac') {
        isCustomTitle.value = true;
        form.tieu_de = '';
        form.ly_do = '';
    } else {
        isCustomTitle.value = false;
        const template = requestTemplates.find(t => t.title === newVal);
        if (template) {
            form.tieu_de = template.title;
            form.ly_do = template.reason;
            form.clearErrors('tieu_de');
        }
    }
});

const themDong = () => {
    form.san_pham.push({ ten_san_pham: '', danh_muc_id: '', so_luong: 1 });
};

const xoaDong = (index) => {
    if (form.san_pham.length > 1) {
        form.san_pham.splice(index, 1);
    }
};

const submit = () => {
    form.post(route('phieu.store'));
};
</script>

<template>
    <Head title="Tạo Yêu Cầu Mua Sắm" />

   <!-- Đổi thành justify-end để căn phải, bỏ padding để panel dính mép -->
   <div class="fixed inset-0 z-[100] bg-slate-900/60 backdrop-blur-sm flex justify-end transition-opacity">

        <Link :href="route('dashboard')" class="absolute inset-0 cursor-default"></Link>

        <!-- Đổi thành h-full, bỏ bo góc, giữ max-w-[700px] vì có chứa table, dùng animate-slide-in-right -->
        <div class="relative w-full max-w-[700px] bg-white h-full shadow-2xl flex flex-col animate-slide-in-right">

            <!-- Bỏ bo góc ở Header (rounded-t-2xl) -->
            <div class="px-6 py-5 border-b border-slate-200 flex justify-between items-center bg-slate-50 shrink-0">
                <div>
                    <h2 class="text-lg font-black text-slate-800 uppercase tracking-wide flex items-center gap-2">
                         Tạo Đề Xuất Mua Sắm
                    </h2>
                    <p class="text-xs text-slate-500 mt-1 font-medium">Chọn mẫu yêu cầu hoặc tự điền nhu cầu của bạn.</p>
                </div>
                <Link :href="route('dashboard')" class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                </Link>
            </div>

            <form @submit.prevent="submit" class="flex flex-col flex-grow overflow-hidden">

                <div class="flex-grow overflow-y-auto px-6 py-6 custom-scrollbar space-y-8">

                    <div>
                        <h3 class="font-bold text-sm text-slate-800 mb-4 uppercase tracking-wider flex items-center gap-2 border-b border-slate-100 pb-2">
                        1. Thông tin chung
                        </h3>

                        <div class="bg-blue-50/50 p-5 rounded-xl border border-blue-100 space-y-5">

                            <div>
                                <label class="block font-semibold text-[11px] text-blue-800 uppercase tracking-wider mb-2">Loại yêu cầu <span class="text-red-500">*</span></label>
                                <select v-model="selectedTemplate" class="border-blue-200 focus:border-blue-600 focus:ring-blue-600 rounded-lg shadow-sm w-full py-2.5 text-sm transition-colors bg-white font-medium text-slate-700">
                                    <option value="" disabled>-- Chọn loại đề xuất mua sắm --</option>
                                    <option v-for="temp in requestTemplates" :key="temp.title" :value="temp.title">{{ temp.label }}</option>
                                </select>
                            </div>

                            <div v-show="isCustomTitle" class="animate-fade-in">
                                <label class="block font-semibold text-[11px] text-slate-600 uppercase tracking-wider mb-2">Tiêu đề tự chọn <span class="text-red-500">*</span></label>
                                <input v-model="form.tieu_de" type="text" class="border-slate-300 focus:border-blue-600 focus:ring-blue-600 rounded-lg shadow-sm w-full py-2.5 text-sm transition-colors" placeholder="VD: Đề xuất cấp Laptop cho nhân sự mới...">
                                <div v-if="form.errors.tieu_de" class="text-red-500 text-xs mt-1.5 font-medium">{{ form.errors.tieu_de }}</div>
                            </div>

                            <div v-if="form.errors.tieu_de && !isCustomTitle && !selectedTemplate" class="text-red-500 text-xs -mt-3 font-medium">Vui lòng chọn Loại yêu cầu hoặc tự nhập.</div>

                            <div>
                                <label class="block font-semibold text-[11px] text-slate-600 uppercase tracking-wider mb-2">Lý do mua sắm</label>
                                <textarea v-model="form.ly_do" rows="3" class="border-slate-300 focus:border-blue-600 focus:ring-blue-600 rounded-lg shadow-sm w-full py-2.5 text-sm transition-colors" placeholder="Trình bày rõ lý do hoặc đính kèm link sản phẩm (nếu có)..."></textarea>
                            </div>
                        </div>
                    </div>

                    <div>
                        <h3 class="font-bold text-sm text-slate-800 mb-4 uppercase tracking-wider flex items-center gap-2 border-b border-slate-100 pb-2">
                            2. Danh sách hàng hóa đề xuất cần mua
                        </h3>

                        <div class="grid grid-cols-12 gap-3 mb-2 font-bold text-[10px] text-slate-500 uppercase tracking-wider bg-slate-50 p-2.5 rounded-lg border border-slate-200">
                            <div class="col-span-5 pl-1">Tên sản phẩm</div>
                            <div class="col-span-4">Thuộc Danh mục</div>
                            <div class="col-span-2 text-center">Số Lượng</div>
                            <div class="col-span-1 text-center"></div>
                        </div>

                        <div v-for="(item, index) in form.san_pham" :key="index" class="grid grid-cols-12 gap-3 mb-3 items-start group">
                            <div class="col-span-5">
                                <input v-model="item.ten_san_pham" type="text" class="w-full border-slate-300 rounded-lg shadow-sm text-sm focus:border-blue-600 focus:ring-blue-600 py-2.5 transition-colors" placeholder="Tên sp, cấu hình...">
                                <div v-if="form.errors[`san_pham.${index}.ten_san_pham`]" class="text-red-500 text-[10px] mt-1.5 font-bold uppercase tracking-wide">Bắt buộc nhập</div>
                            </div>
                            <div class="col-span-4">
                                <select v-model="item.danh_muc_id" class=" text-center w-full border-slate-300 rounded-lg shadow-sm text-sm focus:border-blue-600 focus:ring-blue-600 py-2.5 bg-slate-50 transition-colors">
                                    <option value="" disabled>-- Chọn danh mục --</option>
                                    <option v-for="dm in danhMucs" :key="dm.id" :value="dm.id">{{ dm.ten_danh_muc }}</option>
                                </select>
                            </div>
                            <div class="col-span-2">
                                <input v-model="item.so_luong" type="number" min="1" class="w-full border-slate-300 rounded-lg shadow-sm text-sm text-center focus:border-blue-600 focus:ring-blue-600 py-2.5 font-black text-slate-800 transition-colors">
                            </div>
                            <div class="col-span-1 flex justify-center mt-1">
                                <button type="button" @click="xoaDong(index)" class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors" :class="{'opacity-30 cursor-not-allowed hover:bg-transparent hover:text-slate-400': form.san_pham.length === 1}" :disabled="form.san_pham.length === 1">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                </button>
                            </div>
                        </div>

                        <div class="mt-5">
                            <button type="button" @click="themDong" class="inline-flex items-center justify-center gap-1.5 w-full text-blue-600 hover:text-blue-800 font-bold text-xs bg-blue-50 border border-blue-100 border-dashed hover:bg-blue-100 hover:border-blue-200 p-3 rounded-xl transition-all uppercase tracking-wide">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                                Thêm mặt hàng khác
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Bỏ bo góc ở Footer (rounded-b-2xl) -->
                <div class="px-6 py-5 border-t border-slate-200 bg-slate-50 flex items-center justify-between shrink-0">
                    <Link :href="route('dashboard')" class="text-sm font-bold text-slate-500 hover:text-slate-800 transition-colors">Hủy bỏ</Link>
                    <button :disabled="form.processing" class="flex items-center gap-2 bg-blue-600 text-white px-8 py-2.5 rounded-lg font-black hover:bg-blue-700 shadow-md hover:shadow-lg transition-all disabled:opacity-50 text-sm">
                        <svg v-if="!form.processing" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <svg v-else class="animate-spin w-4 h-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        {{ form.processing ? 'ĐANG GỬI...' : 'GỬI ĐỀ XUẤT' }}
                    </button>
                </div>

            </form>
        </div>
    </div>
</template>

<style scoped>
.animate-slide-in-right { animation: slideInRight 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
@keyframes slideInRight { 0% { transform: translateX(100%); } 100% { transform: translateX(0); } }

.animate-fade-in { animation: fadeIn 0.3s ease-in-out forwards; }
@keyframes fadeIn {
    0% { opacity: 0; transform: translateY(-5px); }
    100% { opacity: 1; transform: translateY(0); }
}

.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background-color: #cbd5e1;
  border-radius: 10px;
}
.custom-scrollbar:hover::-webkit-scrollbar-thumb {
  background-color: #94a3b8;
}
</style>
