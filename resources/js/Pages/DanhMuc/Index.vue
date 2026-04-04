<script setup>
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    danhMucs: Array,
});

// Quản lý trạng thái Modal (Đóng/Mở, Thêm hay Sửa)
const isModalOpen = ref(false);
const isEditing = ref(false);
const editId = ref(null);

// Form data (Dùng useForm của Inertia để tự động bắt lỗi)
const form = useForm({
    ten_danh_muc: '',
    mo_ta: '',
});

// Hàm mở Modal
const openModal = (danhMuc = null) => {
    isModalOpen.value = true;
    if (danhMuc) {
        // Nếu có truyền data vào -> Chế độ SỬA
        isEditing.value = true;
        editId.value = danhMuc.id;
        form.ten_danh_muc = danhMuc.ten_danh_muc;
        form.mo_ta = danhMuc.mo_ta;
    } else {
        // Không có data -> Chế độ THÊM MỚI
        isEditing.value = false;
        editId.value = null;
        form.reset();
        form.clearErrors();
    }
};

// Hàm đóng Modal
const closeModal = () => {
    isModalOpen.value = false;
    form.reset();
    form.clearErrors();
};

// Hàm Lưu dữ liệu (Gộp cả Thêm và Sửa)
const submitForm = () => {
    if (isEditing.value) {
        form.put(route('danhmuc.update', editId.value), {
            preserveScroll: true,
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('danhmuc.store'), {
            preserveScroll: true,
            onSuccess: () => closeModal(),
        });
    }
};

// Hàm Xóa
const deleteCategory = (id) => {
    if (confirm('Bạn có chắc chắn muốn xóa danh mục này không? Các phiếu mua sắm liên quan có thể bị ảnh hưởng!')) {
        router.delete(route('danhmuc.destroy', id), {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <Head title="Quản lý Danh Mục" />

    <div class="py-6 px-8 max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-6">
            <h2 class="font-semibold text-2xl text-gray-800 leading-tight">Quản lý Danh Mục</h2>
            <button @click="openModal()" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded shadow transition">
                + Thêm Danh Mục
            </button>
        </div>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-100">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Tên Danh Mục</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Mô Tả</th>
                        <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Thao Tác</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="(dm, index) in danhMucs" :key="dm.id" class="hover:bg-blue-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap text-gray-500">{{ index + 1 }}</td>
                        <td class="px-6 py-4 whitespace-nowrap font-bold text-gray-800">{{ dm.ten_danh_muc }}</td>
                        <td class="px-6 py-4 text-gray-600">{{ dm.mo_ta || 'Không có mô tả' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <button @click="openModal(dm)" class="text-indigo-600 hover:text-indigo-900 mr-4">Sửa</button>
                            <button @click="deleteCategory(dm.id)" class="text-red-600 hover:text-red-900">Xóa</button>
                        </td>
                    </tr>
                    <tr v-if="danhMucs.length === 0">
                        <td colspan="4" class="px-6 py-8 text-center text-gray-500 italic">Chưa có danh mục nào.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

<div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4">

        <div class="absolute inset-0 bg-gray-800 bg-opacity-75 transition-opacity" @click="closeModal"></div>

        <div class="relative bg-white rounded-lg shadow-2xl w-full max-w-lg overflow-hidden transform transition-all">
            <form @submit.prevent="submitForm">
                <div class="bg-white px-6 pt-6 pb-4">
                    <h3 class="text-xl font-bold text-gray-900 border-b pb-3 mb-4">
                        {{ isEditing ? 'Cập Nhật Danh Mục' : 'Thêm Danh Mục Mới' }}
                    </h3>

                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Tên danh mục <span class="text-red-500">*</span></label>
                        <input v-model="form.ten_danh_muc" type="text" class="shadow-sm appearance-none border border-gray-300 rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="VD: Thiết bị IT">
                        <div v-if="form.errors.ten_danh_muc" class="text-red-500 text-xs mt-1 font-medium">{{ form.errors.ten_danh_muc }}</div>
                    </div>

                    <div class="mb-2">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Mô tả chi tiết</label>
                        <textarea v-model="form.mo_ta" rows="3" class="shadow-sm appearance-none border border-gray-300 rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Mô tả các mặt hàng thuộc danh mục này..."></textarea>
                    </div>
                </div>

                <div class="bg-gray-50 px-6 py-4 flex flex-row-reverse rounded-b-lg border-t border-gray-100">
                    <button type="submit" :disabled="form.processing" class="inline-flex justify-center rounded-md border border-transparent shadow-sm px-6 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:text-sm transition-colors">
                        {{ isEditing ? 'Lưu Thay Đổi' : 'Tạo Mới' }}
                    </button>
                    <button type="button" @click="closeModal" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-6 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-100 focus:outline-none sm:mt-0 sm:w-auto sm:text-sm transition-colors">
                        Hủy Bỏ
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
