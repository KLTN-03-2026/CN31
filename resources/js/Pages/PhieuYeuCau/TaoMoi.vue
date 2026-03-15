<script setup>
import { useForm, Head } from '@inertiajs/vue3';

const form = useForm({
    tieu_de: '',
    ly_do: '',
    san_pham: [
        { ten_san_pham: '', so_luong: 1, don_gia: 0, ghi_chu: '' }
    ]
});

const themDong = () => {
    form.san_pham.push({ ten_san_pham: '', so_luong: 1, don_gia: 0, ghi_chu: '' });
};

const xoaDong = (index) => {
    if (form.san_pham.length > 1) {
        form.san_pham.splice(index, 1);
    }
};

const tongTienHienThi = () => {
    return form.san_pham.reduce((acc, item) => acc + (item.so_luong * item.don_gia), 0);
};

const submit = () => {
    form.post(route('phieu.store'));
};
</script>

<template>

    <Head title="Tạo Yêu Cầu Mua Sắm" />

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

                <form @submit.prevent="submit">
                    <div class="grid grid-cols-1 gap-4 mb-6">
                        <div>
                            <label class="block font-medium text-sm text-gray-700">Tiêu đề phiếu</label>
                            <input v-model="form.tieu_de" type="text"
                                class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full mt-1"
                                placeholder="VD: Mua Laptop cho nhân viên mới...">
                            <div v-if="form.errors.tieu_de" class="text-red-500 text-sm mt-1">{{ form.errors.tieu_de }}
                            </div>
                        </div>

                        <div>
                            <label class="block font-medium text-sm text-gray-700">Lý do mua sắm</label>
                            <textarea v-model="form.ly_do" rows="3"
                                class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full mt-1"></textarea>
                        </div>
                    </div>

                    <div class="border-t pt-4">
                        <h3 class="font-bold text-lg mb-4 text-blue-600">Danh sách hàng hóa</h3>

                        <div class="flex gap-2 mb-2 font-bold text-sm text-gray-600">
                            <div class="w-5/12">Tên sản phẩm</div>
                            <div class="w-2/12">Số lượng</div>
                            <div class="w-3/12">Đơn giá (VND)</div>
                            <div class="w-1/12">Thao tác</div>
                        </div>

                        <div v-for="(item, index) in form.san_pham" :key="index" class="flex gap-2 mb-3 items-start">
                            <div class="w-5/12">
                                <input v-model="item.ten_san_pham" type="text"
                                    class="w-full border-gray-300 rounded-md shadow-sm text-sm"
                                    placeholder="Nhập tên...">
                                <div v-if="form.errors[`san_pham.${index}.ten_san_pham`]" class="text-red-500 text-xs">
                                    Bắt buộc nhập</div>
                            </div>
                            <div class="w-2/12">
                                <input v-model="item.so_luong" type="number" min="1"
                                    class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                            </div>
                            <div class="w-3/12">
                                <input v-model="item.don_gia" type="number" min="0"
                                    class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                            </div>
                            <div class="w-1/12">
                                <button type="button" @click="xoaDong(index)"
                                    class="text-red-500 hover:text-red-700 font-bold px-2 py-1">Xóa</button>
                            </div>
                        </div>

                        <div class="flex justify-between items-center mt-4">
                            <button type="button" @click="themDong"
                                class="bg-gray-200 text-gray-700 px-4 py-2 rounded hover:bg-gray-300 text-sm font-medium">
                                + Thêm dòng sản phẩm
                            </button>
                            <div class="text-xl font-bold">
                                Tổng tiền: <span class="text-green-600">{{ tongTienHienThi().toLocaleString() }}
                                    đ</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end mt-8 border-t pt-4">
                        <button :disabled="form.processing"
                            class="ml-4 bg-blue-600 text-white px-6 py-3 rounded-md font-bold hover:bg-blue-700 shadow-lg uppercase tracking-widest text-xs transition ease-in-out duration-150">
                            {{ form.processing ? 'Đang gửi...' : 'Gửi Yêu Cầu' }}
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</template>
