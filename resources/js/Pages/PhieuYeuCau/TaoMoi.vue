<script setup>
import { useForm, Head } from '@inertiajs/vue3';

// Nhận danh sách Danh mục từ Backend
defineProps({
    danhMucs: Array
});

// FORM CHỈ CÒN LẠI THÔNG TIN CẦN THIẾT
const form = useForm({
    tieu_de: '',
    ly_do: '',
    san_pham: [
        { ten_san_pham: '', danh_muc_id: '', so_luong: 1 } // BỎ don_gia
    ]
});

const themDong = () => {
    form.san_pham.push({ ten_san_pham: '', danh_muc_id: '', so_luong: 1 }); // BỎ don_gia
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
    <Head title="Tạo Yêu Cầu Mua Sắm | ProcureFlow" />

    <div class="py-10">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-2xl sm:rounded-2xl p-8 border border-gray-100">

                <div class="mb-8 border-b pb-5">
                    <h2 class="text-3xl font-extrabold text-gray-900 flex items-center gap-3">
                        📝 Tạo Phiếu Đề Xuất Mua Sắm
                    </h2>
                    <p class="text-gray-500 mt-2 text-sm font-medium">Bạn chỉ cần liệt kê nhu cầu, phòng Mua Sắm sẽ lo phần khảo giá.</p>
                </div>

                <form @submit.prevent="submit">

                    <div class="bg-gray-50 p-6 rounded-xl border border-gray-200 mb-8">
                        <h3 class="font-bold text-lg mb-4 text-blue-700 flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            1. Thông tin chung
                        </h3>

                        <div class="grid grid-cols-1 gap-5">
                            <div>
                                <label class="block font-bold text-sm text-gray-800 mb-1">Tiêu đề phiếu <span class="text-red-500">*</span></label>
                                <input v-model="form.tieu_de" type="text"
                                    class="border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-lg shadow-sm w-full py-2.5"
                                    placeholder="VD: Đề xuất cấp Laptop cho nhân sự mới phòng IT...">
                                <div v-if="form.errors.tieu_de" class="text-red-500 text-xs mt-1.5 font-medium">{{ form.errors.tieu_de }}</div>
                            </div>

                            <div>
                                <label class="block font-bold text-sm text-gray-800 mb-1">Lý do mua sắm</label>
                                <textarea v-model="form.ly_do" rows="3"
                                    class="border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-lg shadow-sm w-full py-2.5"
                                    placeholder="Trình bày rõ lý do hoặc đính kèm link sản phẩm (nếu có)..."></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
                        <h3 class="font-bold text-lg mb-5 text-blue-700 flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            2. Danh sách hàng hóa cần mua
                        </h3>

                        <div class="grid grid-cols-12 gap-3 mb-3 font-extrabold text-xs text-gray-500 uppercase tracking-wider bg-gray-50 p-3 rounded-lg">
                            <div class="col-span-5 pl-2">Tên sản phẩm / Cấu hình</div>
                            <div class="col-span-4">Danh mục</div>
                            <div class="col-span-2 text-center">Số lượng</div>
                            <div class="col-span-1 text-center">Xóa</div>
                        </div>

                        <div v-for="(item, index) in form.san_pham" :key="index" class="grid grid-cols-12 gap-3 mb-4 items-start group">

                            <div class="col-span-5">
                                <input v-model="item.ten_san_pham" type="text"
                                    class="w-full border-gray-300 rounded-lg shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500 py-2.5"
                                    placeholder="VD: Laptop Dell Latitude 5420, i5 11th, 16GB RAM...">
                                <div v-if="form.errors[`san_pham.${index}.ten_san_pham`]" class="text-red-500 text-xs mt-1 font-medium">Bắt buộc nhập tên sản phẩm</div>
                            </div>

                            <div class="col-span-4">
                                <select v-model="item.danh_muc_id" class="w-full border-gray-300 rounded-lg shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500 py-2.5 bg-gray-50">
                                    <option value="" disabled>-- Chọn danh mục --</option>
                                    <option v-for="dm in danhMucs" :key="dm.id" :value="dm.id">
                                        {{ dm.ten_danh_muc }}
                                    </option>
                                </select>
                                <div v-if="form.errors[`san_pham.${index}.danh_muc_id`]" class="text-red-500 text-xs mt-1 font-medium">Vui lòng chọn danh mục</div>
                            </div>

                            <div class="col-span-2">
                                <input v-model="item.so_luong" type="number" min="1"
                                    class="w-full border-gray-300 rounded-lg shadow-sm text-sm text-center focus:border-blue-500 focus:ring-blue-500 py-2.5 font-bold text-blue-700">
                            </div>

                            <div class="col-span-1 flex justify-center mt-1">
                                <button type="button" @click="xoaDong(index)"
                                    class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                                    title="Xóa dòng này"
                                    :class="{'opacity-50 cursor-not-allowed': form.san_pham.length === 1}"
                                    :disabled="form.san_pham.length === 1">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div class="mt-2">
                            <button type="button" @click="themDong"
                                class="inline-flex items-center gap-2 text-blue-600 hover:text-blue-800 font-bold text-sm bg-blue-50 hover:bg-blue-100 px-4 py-2 rounded-lg transition">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                </svg>
                                Thêm mặt hàng khác
                            </button>
                        </div>
                    </div>

                    <div class="flex items-center justify-end mt-8">
                        <button :disabled="form.processing"
                            class="flex items-center gap-2 bg-blue-600 text-white px-8 py-3.5 rounded-xl font-extrabold hover:bg-blue-700 shadow-lg hover:shadow-xl transition-all disabled:opacity-50">
                            <svg v-if="!form.processing" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <svg v-else class="animate-spin w-5 h-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            {{ form.processing ? 'ĐANG GỬI...' : 'GỬI ĐỀ XUẤT' }}
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</template>
