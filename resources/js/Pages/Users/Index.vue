<script setup>
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    users: Array,
    phongBans: Array
});

// Danh sách các vai trò trong hệ thống
const roles = [
    { value: 'nhan_vien', label: 'Nhân viên' },
    { value: 'truong_phong', label: 'Trưởng phòng' },
    { value: 'ke_toan', label: 'Kế toán' },
    { value: 'giam_doc', label: 'Giám đốc' },
    { value: 'admin', label: 'Quản trị viên (Admin)' }
];

// Biến lưu trạng thái đang xử lý để khóa nút bấm
const processingId = ref(null);

const capNhatNhanSu = (user) => {
    processingId.value = user.id;

    router.put(route('users.update', user.id), {
        vai_tro: user.vai_tro,
        phong_ban_id: user.phong_ban_id
    }, {
        preserveScroll: true,
        onFinish: () => processingId.value = null
    });
};
</script>

<template>
    <Head title="Quản lý Nhân sự" />

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 bg-slate-50 border-b border-gray-200">
                    <h2 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        Quản lý Nhân sự & Phân quyền
                    </h2>
                    <p class="text-sm text-gray-600 mt-1">Gán chức vụ và phòng ban cho nhân sự trong công ty.</p>
                </div>

                <div class="overflow-x-auto p-4">
                    <table class="min-w-full divide-y divide-gray-200 border rounded-lg">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase">Nhân viên</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase">Phòng ban</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase">Chức vụ</th>
                                <th class="px-6 py-3 text-center text-xs font-bold text-gray-600 uppercase">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="user in users" :key="user.id" class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <img class="h-10 w-10 rounded-full object-cover border"
                                             :src="user.avatar ? '/storage/' + user.avatar : 'https://ui-avatars.com/api/?name=' + user.name + '&background=random'" alt="">
                                        <div class="ml-4">
                                            <div class="text-sm font-bold text-gray-900">{{ user.name }}</div>
                                            <div class="text-sm text-gray-500">{{ user.email }}</div>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap">
                                    <select v-model="user.phong_ban_id" class="text-sm border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 w-full font-medium text-gray-700">
                                        <option v-for="pb in phongBans" :key="pb.id" :value="pb.id">
                                            {{ pb.ten_phong_ban }}
                                        </option>
                                    </select>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap">
                                    <select v-model="user.vai_tro" class="text-sm border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 w-full font-medium"
                                        :class="{
                                            'text-red-600': user.vai_tro === 'admin',
                                            'text-orange-600': user.vai_tro === 'giam_doc',
                                            'text-blue-600': user.vai_tro === 'truong_phong',
                                            'text-green-600': user.vai_tro === 'ke_toan',
                                            'text-gray-700': user.vai_tro === 'nhan_vien'
                                        }">
                                        <option v-for="role in roles" :key="role.value" :value="role.value">
                                            {{ role.label }}
                                        </option>
                                    </select>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <button @click="capNhatNhanSu(user)" :disabled="processingId === user.id"
                                        class="bg-indigo-600 text-white px-4 py-2 rounded-md font-bold text-sm hover:bg-indigo-700 transition-colors shadow-sm disabled:opacity-50">
                                        {{ processingId === user.id ? 'Đang lưu...' : 'Lưu cập nhật' }}
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</template>
