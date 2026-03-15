<script setup>
import { ref, computed } from 'vue';
import { usePage, Link } from '@inertiajs/vue3';

const showingNavigationDropdown = ref(false);
const page = usePage();

// Dùng computed để lấy user an toàn
// Dấu ?. giúp tránh lỗi crash nếu auth bị null
const user = computed(() => page.props.auth?.user);
</script>

<template>
    <div>
        <div class="min-h-screen bg-gray-100">
            <nav class="bg-white border-b border-gray-100">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between h-16">
                        <div class="flex">
                            <div class="shrink-0 flex items-center">
                                <Link :href="route('home')" class="font-bold text-blue-600 text-xl">
                                    ProcureFlow
                                </Link>
                            </div>

                            <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                                <Link :href="route('dashboard')" :active="route().current('dashboard')"
                                    class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium leading-5 transition duration-150 ease-in-out"
                                    :class="route().current('dashboard') ? 'border-indigo-400 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'">
                                    Dashboard
                                </Link>

                                <Link :href="route('phieu.create')" :active="route().current('phieu.create')"
                                    class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium leading-5 transition duration-150 ease-in-out"
                                    :class="route().current('phieu.create') ? 'border-indigo-400 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'">
                                    Tạo Phiếu Mua Sắm
                                </Link>
                            </div>
                        </div>

                        <div class="hidden sm:flex sm:items-center sm:ml-6">
                            <div v-if="user" class="flex items-center">
                                <img class="h-9 w-9 rounded-full object-cover border border-gray-300 shadow-sm"
                                    :src="user.avatar ? '/storage/' + user.avatar : '/storage/avatars/default.png'"
                                    alt="Avatar" />

                                <div class="ml-3 relative">
                                    <div class="font-medium text-base text-gray-800">{{ user.name }}</div>
                                </div>

                                <Link :href="route('logout')" method="post" as="button"
                                    class="ml-4 text-sm text-red-600 hover:text-red-900  font-medium transition-colors">
                                    Đăng xuất
                                </Link>
                            </div>
                            <div v-else class="flex items-center gap-4">
                                <Link :href="route('login')" class="text-sm text-gray-700">Đăng nhập</Link>

                            </div>
                        </div>

                        <div class="-mr-2 flex items-center sm:hidden">
                            <button @click="showingNavigationDropdown = !showingNavigationDropdown"
                                class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none transition duration-150 ease-in-out">
                                <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                    <path
                                        :class="{ 'hidden': showingNavigationDropdown, 'inline-flex': !showingNavigationDropdown }"
                                        stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16" />
                                    <path
                                        :class="{ 'hidden': !showingNavigationDropdown, 'inline-flex': showingNavigationDropdown }"
                                        stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div :class="{ 'block': showingNavigationDropdown, 'hidden': !showingNavigationDropdown }"
                    class="sm:hidden">
                    <div class="pt-2 pb-3 space-y-1">
                        <Link :href="route('dashboard')"
                            class="block w-full pl-3 pr-4 py-2 border-l-4 text-left text-base font-medium transition duration-150 ease-in-out"
                            :class="route().current('dashboard') ? 'border-indigo-400 text-indigo-700 bg-indigo-50' : 'border-transparent text-gray-600 hover:text-gray-800 hover:border-gray-300'">
                            Dashboard
                        </Link>
                        <Link :href="route('phieu.create')"
                            class="block w-full pl-3 pr-4 py-2 border-l-4 text-left text-base font-medium transition duration-150 ease-in-out"
                            :class="route().current('phieu.create') ? 'border-indigo-400 text-indigo-700 bg-indigo-50' : 'border-transparent text-gray-600 hover:text-gray-800 hover:border-gray-300'">
                            Tạo Phiếu Mua Sắm
                        </Link>
                    </div>

                    <div class="pt-4 pb-1 border-t border-gray-200">
                        <div v-if="user" class="px-4">
                            <div class="font-medium text-base text-gray-800">{{ user.name }}</div>
                            <div class="mt-3">
                                <Link :href="route('logout')" method="post" as="button"
                                    class="block w-full text-left text-base font-medium text-gray-600 hover:text-gray-800">
                                    Đăng xuất
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </nav>

            <header class="bg-white shadow" v-if="$slots.header">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <main>
                <slot />
            </main>
        </div>
    </div>
</template>
