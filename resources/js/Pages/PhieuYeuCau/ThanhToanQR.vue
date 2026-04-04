<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    phieu: Object,
    nhaCungCap: Object,
    qrUrl: String
});

// Khởi tạo Form để gửi dữ liệu lên Backend
const form = useForm({
    ma_giao_dich_ngan_hang: '',
    hinh_anh_minh_chung: null, // File ảnh Bill
    ghi_chu: '',
});

// Preview ảnh khi chọn file
const imagePreview = ref(null);

const handleFileChange = (e) => {
    const file = e.target.files[0];
    form.hinh_anh_minh_chung = file;

    if (file) {
        const reader = new FileReader();
        reader.onload = (e) => {
            imagePreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
    } else {
        imagePreview.value = null;
    }
};
const xacNhanThanhToan = () => {
    // Vì có đính kèm file nên phải dùng forceFormData
    form.post(route('thanhtoan.xacnhan', props.phieu.id), {
        forceFormData: true,
        preserveScroll: true,
    });
};
</script>

<template>
    <Head :title="`Thanh toán ${phieu.ma_phieu}`" />

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-4">
                <Link :href="route('phieu.show', phieu.id)" class="text-gray-500 hover:text-emerald-600 font-medium flex items-center gap-1 transition">
                    &larr; Quay lại Chi tiết phiếu
                </Link>
            </div>

            <div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl border border-gray-100 flex flex-col md:flex-row">

                <div class="w-full md:w-1/2 p-8 border-b md:border-b-0 md:border-r border-gray-100 bg-slate-50 flex flex-col items-center justify-center">
                    <h2 class="text-xl font-extrabold text-gray-800 mb-2">Quét mã VietQR</h2>
                    <p class="text-sm text-gray-500 mb-6 text-center">Sử dụng App Ngân hàng để quét mã và chuyển khoản chính xác số tiền.</p>

                    <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-200 mb-6">
                        <img :src="qrUrl" alt="VietQR" class="w-64 h-64 object-contain rounded-lg">
                    </div>

                    <div class="w-full bg-white p-4 rounded-xl border border-gray-200 text-sm">
                    <div class="flex justify-between mb-2">
                        <span class="text-gray-500">Người nhận:</span>
                        <span class="font-bold text-gray-900">{{ nhaCungCap.ten_nha_cung_cap }}</span>
                    </div>
                        <div class="flex justify-between mb-2">
                            <span class="text-gray-500">Số tiền:</span>
                            <span class="font-black text-emerald-600 text-lg">{{ Number(phieu.tong_tien).toLocaleString() }} đ</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Nội dung:</span>
                            <span class="font-bold text-gray-800 uppercase">{{ phieu.ma_phieu }}</span>
                        </div>
                    </div>
                </div>

                <div class="w-full md:w-1/2 p-8 bg-white">
                    <div class="flex items-center gap-3 mb-6 border-b pb-4">
                        <div class="bg-emerald-100 p-2 rounded-full text-emerald-600">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </div>
                        <h2 class="text-xl font-extrabold text-emerald-800">Cập nhật Chứng Từ</h2>
                    </div>

                    <form @submit.prevent="xacNhanThanhToan" class="space-y-5">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Mã tham chiếu / Mã giao dịch <span class="text-red-500">*</span></label>
                            <input v-model="form.ma_giao_dich_ngan_hang" type="text" required placeholder="VD: FT2603847291..." class="w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 shadow-sm py-2.5 uppercase">
                            <div v-if="form.errors.ma_giao_dich_ngan_hang" class="text-red-500 text-xs mt-1">{{ form.errors.ma_giao_dich_ngan_hang }}</div>
                            <p class="text-xs text-gray-400 mt-1 italic">Vui lòng nhập mã giao dịch thành công trên App Ngân hàng.</p>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Ảnh chụp Biên lai (Bill) <span class="text-red-500">*</span></label>
                            <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-xl hover:border-emerald-400 transition bg-gray-50 cursor-pointer" @click="$refs.fileInput.click()">
                                <div class="space-y-1 text-center">
                                    <svg v-if="!imagePreview" class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48"><path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" /></svg>
                                    <img v-else :src="imagePreview" class="mx-auto max-h-32 rounded-lg shadow-sm" />
                                    <div class="flex text-sm text-gray-600 justify-center mt-2">
                                        <span class="relative cursor-pointer bg-white rounded-md font-medium text-emerald-600 hover:text-emerald-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-emerald-500">
                                            <span>Tải ảnh lên</span>
                                            <input ref="fileInput" type="file" class="sr-only" @change="handleFileChange" accept="image/jpeg, image/png, image/jpg" required>
                                        </span>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1">PNG, JPG, JPEG (Tối đa 2MB)</p>
                                </div>
                            </div>
                            <div v-if="form.errors.hinh_anh_minh_chung" class="text-red-500 text-xs mt-1">{{ form.errors.hinh_anh_minh_chung }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Ghi chú (Tùy chọn)</label>
                            <textarea v-model="form.ghi_chu" rows="2" class="w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 shadow-sm py-2.5" placeholder="Ghi chú thêm..."></textarea>
                        </div>

                        <div class="pt-4 border-t border-gray-100">
                            <button type="submit" :disabled="form.processing" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold py-3.5 px-4 rounded-xl shadow-lg hover:shadow-xl transition flex items-center justify-center gap-2 disabled:opacity-50">
                                <svg v-if="form.processing" class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                {{ form.processing ? 'Đang tải lên...' : 'CHỐT THANH TOÁN & LƯU CHỨNG TỪ' }}
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</template>
