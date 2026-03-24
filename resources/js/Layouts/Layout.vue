<script setup>
import { ref, computed, watch } from 'vue';
import { usePage, Link } from '@inertiajs/vue3';

const showingNavigationDropdown = ref(false);
const page = usePage();

const user = computed(() => page.props.auth?.user);

// 1. TỐI ƯU: Map (Ánh xạ) biến vai_tro trong DB thành chữ tiếng Việt hiển thị cho đẹp
const roleLabels = {
    'admin': 'Quản trị viên',
    'nhan_vien': 'Nhân viên',
    'truong_phong': 'Trưởng phòng',
    'giam_doc': 'Giám đốc',
    'ke_toan': 'Kế toán'
};
const userRoleLabel = computed(() => user.value ? roleLabels[user.value.vai_tro] : '');

// 2. TỐI ƯU: Hứng Flash Message từ Backend
const flashSuccess = computed(() => page.props.flash?.success);
const flashError = computed(() => page.props.flash?.error || page.props.errors?.error);

// Tự động tắt thông báo sau 3 giây để màn hình đỡ rác
const showFlash = ref(true);
watch([flashSuccess, flashError], () => {
    showFlash.value = true;
    setTimeout(() => { showFlash.value = false; }, 3000);
});
</script>

<template>
    <div class="min-h-screen bg-gray-100">

        <div v-if="showFlash && flashSuccess" class="bg-green-600 text-white px-4 py-3 text-center font-medium shadow-md transition-all duration-500">
            {{ flashSuccess }}
        </div>
        <div v-if="showFlash && flashError" class="bg-red-600 text-white px-4 py-3 text-center font-medium shadow-md transition-all duration-500">
            {{ flashError }}
        </div>

        <nav class="bg-white border-b border-gray-100 shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex">
                        <div class="shrink-0 flex items-center">
                            <Link :href="route('home')" class="font-extrabold text-blue-700 text-2xl tracking-tight">
                                Procure<span class="text-gray-800">Flow</span>
                            </Link>
                        </div>

                        <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                            <Link :href="route('dashboard')"
                                class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium leading-5 transition duration-150 ease-in-out"
                                :class="route().current('dashboard') ? 'border-blue-600 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'">
                                Dashboard
                            </Link>

                            <Link :href="route('phieu.create')"
                                class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium leading-5 transition duration-150 ease-in-out"
                                :class="route().current('phieu.create') ? 'border-blue-600 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'">
                                + Tạo Phiếu Mua Sắm
                            </Link>
                            <Link v-if="user?.vai_tro === 'admin'" :href="route('users.index')"
                                class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium leading-5 transition duration-150 ease-in-out"
                                :class="route().current('users.index') ? 'border-blue-600 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'">
                                Quản lý Nhân sự
                            </Link>
                        </div>
                    </div>

                    <div class="hidden sm:flex sm:items-center sm:ml-6">
                        <div v-if="user" class="flex items-center">
                            <img class="h-10 w-10 rounded-full object-cover border-2 border-gray-200 shadow-sm"
                             :src="user.avatar ? (user.avatar.startsWith('http') ? user.avatar : '/storage/' + user.avatar) : 'https://ui-avatars.com/api/?name=' + user.name + '&background=random'"
                                alt="Avatar" />

                            <div class="ml-3 relative flex flex-col justify-center">
                                <div class="font-bold text-sm text-gray-800">{{ user.name }}</div>
                                <div class="text-xs font-semibold text-blue-600 uppercase">{{ userRoleLabel }}</div>
                            </div>

                            <Link :href="route('logout')" method="post" as="button"
                                class="ml-6 px-3 py-1.5 rounded text-sm text-red-600 bg-red-50 hover:bg-red-100 hover:text-red-900 font-bold transition-colors border border-red-100">
                                Đăng xuất
                            </Link>
                        </div>
                        <div v-else class="flex items-center gap-4">
                            <Link :href="route('login')" class="text-sm font-bold text-blue-600 hover:underline">Đăng nhập</Link>
                        </div>
                    </div>

                    <div class="-mr-2 flex items-center sm:hidden">
                        <button @click="showingNavigationDropdown = !showingNavigationDropdown"
                            class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none transition duration-150 ease-in-out">
                            <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                <path :class="{ 'hidden': showingNavigationDropdown, 'inline-flex': !showingNavigationDropdown }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                <path :class="{ 'hidden': !showingNavigationDropdown, 'inline-flex': showingNavigationDropdown }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <div :class="{ 'block': showingNavigationDropdown, 'hidden': !showingNavigationDropdown }" class="sm:hidden border-t">
                <div class="pt-2 pb-3 space-y-1">
                    <Link :href="route('dashboard')"
                        class="block w-full pl-3 pr-4 py-2 border-l-4 text-left text-base font-medium transition duration-150 ease-in-out"
                        :class="route().current('dashboard') ? 'border-blue-500 text-blue-700 bg-blue-50' : 'border-transparent text-gray-600 hover:text-gray-800 hover:bg-gray-50'">
                        Dashboard
                    </Link>
                    <Link :href="route('phieu.create')"
                        class="block w-full pl-3 pr-4 py-2 border-l-4 text-left text-base font-medium transition duration-150 ease-in-out"
                        :class="route().current('phieu.create') ? 'border-blue-500 text-blue-700 bg-blue-50' : 'border-transparent text-gray-600 hover:text-gray-800 hover:bg-gray-50'">
                        Tạo Phiếu Mua Sắm
                    </Link>
                </div>

                <div class="pt-4 pb-1 border-t border-gray-200 bg-gray-50">
                    <div v-if="user" class="px-4">
                        <div class="font-bold text-base text-gray-800">{{ user.name }}</div>
                        <div class="font-medium text-sm text-blue-600">{{ userRoleLabel }}</div>
                        <div class="mt-3">
                            <Link :href="route('logout')" method="post" as="button"
                                class="block w-full text-left text-base font-bold text-red-600 hover:text-red-800">
                                Đăng xuất
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <header class="bg-white shadow-sm border-b" v-if="$slots.header">
            <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
                <slot name="header" />
            </div>
        </header>

        <main>
            <slot />
        </main>
    </div>
</template>
