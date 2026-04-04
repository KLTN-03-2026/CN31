<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import { usePage, Link, router } from '@inertiajs/vue3';
import axios from 'axios';

const showingNavigationDropdown = ref(false);
const page = usePage();

const user = computed(() => page.props.auth?.user);

const roleLabels = {
    'admin': 'Quản trị viên',
    'nhan_vien': 'Nhân viên',
    'truong_phong': 'Trưởng phòng',
    'giam_doc': 'Giám đốc',
    'ke_toan': 'Kế toán',
    'nhan_su': 'Nhân sự',
    'nhan_vien_mua_sam': 'Mua sắm'
};
const userRoleLabel = computed(() => user.value ? roleLabels[user.value.vai_tro] : '');

const flashSuccess = computed(() => page.props.flash?.success);
const flashError = computed(() => page.props.flash?.error || page.props.errors?.error);

const showFlash = ref(true);
watch([flashSuccess, flashError], () => {
    showFlash.value = true;
    setTimeout(() => { showFlash.value = false; }, 4000);
});

// [MỚI] Dùng ref để giao diện luôn "cảm nhận" được sự thay đổi
const notifications = ref(page.props.auth?.notifications || { list: [], unread_count: 0 });
// Khi Inertia load lại trang (F5 hoặc chuyển trang), cập nhật lại data từ Database
watch(() => page.props.auth?.notifications, (newVal) => {
    if (newVal) notifications.value = newVal;
}, { deep: true });

const handleNotificationClick = (n) => {
    if (!n.read_at) {
        // Nếu chưa đọc thì gọi API đánh dấu đã đọc
        axios.post(route('notifications.read', n.id)).then(response => {
            router.visit(response.data.url);
        }).catch(error => {
            console.error("Lỗi khi đọc thông báo:", error);
        });
    } else {
        // Đã đọc rồi thì bay thẳng tới trang chi tiết
        router.visit(route('phieu.show', n.data.phieu_id));
    }
};
// --- BẮT SÓNG WEBSOCKETS (REALTIME) ---
onMounted(() => {
    if (user.value) {
        // Lắng nghe ở kênh private của user hiện tại
        window.Echo.private(`App.Models.User.${user.value.id}`)
            .notification((notification) => {
                // Khi có thông báo mới bay tới:

                // 1. Tăng số đếm màu đỏ lên 1
                if (!notifications.value) {
                    page.props.auth.notifications = { list: [], unread_count: 0 };
                }
                notifications.value.unread_count++;

                // 2. Chèn thông báo mới tinh này lên ĐẦU danh sách dropdown
                // Định dạng lại data cho khớp với cấu trúc Database Notification
                const newNoti = {
                    id: notification.id,
                    data: notification,
                    read_at: null,
                    created_at: notification.created_at
                };

                notifications.value.list.unshift(newNoti);

                // 3. (Tùy chọn) Bật Flash Message góc màn hình cho nó xịn!
                page.props.flash.success = "Ting! Bạn có thông báo mới: " + notification.thong_diep;
                showFlash.value = true;
                setTimeout(() => { showFlash.value = false; }, 5000);
            });
    }
});

onUnmounted(() => {
    // Tắt đài khi rời khỏi layout
    if (user.value) {
        window.Echo.leave(`App.Models.User.${user.value.id}`);
    }
});
</script>

<template>
    <div class="min-h-screen bg-slate-50 flex flex-col font-sans text-gray-900">

        <div class="fixed top-4 left-1/2 transform -translate-x-1/2 z-50 w-full max-w-lg px-4 transition-all duration-500 pointer-events-none"
             :class="(showFlash && (flashSuccess || flashError)) ? 'translate-y-0 opacity-100' : '-translate-y-10 opacity-0'">
            <div v-if="flashSuccess" class="bg-emerald-600 text-white px-6 py-3 rounded-xl shadow-2xl font-bold flex items-center justify-center gap-2 pointer-events-auto">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                {{ flashSuccess }}
            </div>
            <div v-if="flashError" class="bg-red-600 text-white px-6 py-3 rounded-xl shadow-2xl font-bold flex items-center justify-center gap-2 pointer-events-auto">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                {{ flashError }}
            </div>
        </div>

        <nav class="bg-white border-b border-gray-200 shadow-sm sticky top-0 z-40">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex">
                        <div class="shrink-0 flex items-center">
                            <Link :href="route('dashboard')" class="font-black text-indigo-700 text-2xl tracking-tighter hover:scale-105 transition-transform">
                                Procure<span class="text-slate-800">Flow</span>
                            </Link>
                        </div>

                        <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                            <Link :href="route('dashboard')"
                                class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-bold leading-5 transition duration-150 ease-in-out"
                                :class="route().current('dashboard') ? 'border-indigo-600 text-indigo-700' : 'border-transparent text-gray-500 hover:text-gray-800 hover:border-gray-300'">
                                Dashboard
                            </Link>

                            <div v-if="user?.vai_tro === 'admin' || user?.vai_tro === 'truong_phong'"
                                class="relative group inline-flex items-center px-1 pt-1 border-b-2 transition duration-150 ease-in-out cursor-pointer"
                                :class="(route().current('users.index') || route().current('danhmuc.index')) ? 'border-indigo-600 text-indigo-700' : 'border-transparent text-gray-500 hover:text-gray-800 hover:border-gray-300'">
                                <span class="flex items-center gap-1 font-bold">Hệ thống <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg></span>

                                <div class="absolute left-0 top-full w-56 pt-2 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
                                    <div class="bg-white border border-gray-100 shadow-xl rounded-xl overflow-hidden py-2">
                                        <Link v-if="user?.vai_tro === 'admin'" :href="route('users.index')"
                                            class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-700 font-medium transition"
                                            :class="route().current('users.index') ? 'bg-indigo-50 text-indigo-700 font-bold' : ''">
                                            <span>👥</span> Quản lý Nhân sự
                                        </Link>
                                        <Link :href="route('danhmuc.index')"
                                            class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-700 font-medium transition"
                                            :class="route().current('danhmuc.index') ? 'bg-indigo-50 text-indigo-700 font-bold' : ''">
                                            <span>📦</span> Danh Mục Hàng Hóa
                                        </Link>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="hidden sm:flex sm:items-center sm:ml-6 gap-4">

                        <div class="relative group" v-if="user">
                            <button class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold py-2 px-4 rounded-lg shadow hover:shadow-md transition-all flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                                Tạo yêu cầu
                            </button>
                            <div class="absolute right-0 top-full pt-2 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50 w-56">
                                <div class="bg-white border border-gray-100 shadow-xl rounded-xl overflow-hidden py-2">
                                    <Link :href="route('phieu.create')" class="flex items-start gap-3 px-4 py-3 hover:bg-indigo-50 transition group/item">
                                        <div class="bg-blue-100 text-blue-600 p-2 rounded-lg group-hover/item:bg-blue-600 group-hover/item:text-white transition">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                                        </div>
                                        <div>
                                            <p class="text-sm font-bold text-gray-800">Mua sắm thiết bị</p>
                                            <p class="text-xs text-gray-500">Xin cấp tài sản, chi tiêu</p>
                                        </div>
                                    </Link>
                                    <Link :href="route('nghiphep.create')" class="flex items-start gap-3 px-4 py-3 hover:bg-pink-50 transition group/item">
                                        <div class="bg-pink-100 text-pink-600 p-2 rounded-lg group-hover/item:bg-pink-600 group-hover/item:text-white transition">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                        </div>
                                        <div>
                                            <p class="text-sm font-bold text-gray-800">Nghỉ phép (E-Leave)</p>
                                            <p class="text-xs text-gray-500">Xin nghỉ năm, ốm đau...</p>
                                        </div>
                                    </Link>
                                </div>
                            </div>
                        </div>

                        <div class="relative group" v-if="user">
                            <button class="relative p-2 text-gray-400 hover:text-indigo-600 transition rounded-full hover:bg-indigo-50 focus:outline-none">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>

                                <span v-if="notifications?.unread_count > 0" class="absolute top-0 right-0 flex h-4 w-4">
                                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                                  <span class="relative inline-flex rounded-full h-4 w-4 bg-red-500 text-[10px] text-white font-bold items-center justify-center">
                                    {{ notifications.unread_count > 9 ? '9+' : notifications.unread_count }}
                                  </span>
                                </span>
                            </button>

                            <div class="absolute right-0 top-full pt-2 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50 w-80">
                                <div class="bg-white border border-gray-100 shadow-2xl rounded-xl overflow-hidden flex flex-col max-h-[400px]">
                                    <div class="px-4 py-3 bg-slate-50 border-b border-gray-100 flex justify-between items-center shrink-0">
                                        <h3 class="font-extrabold text-sm text-gray-800">Thông báo</h3>
                                        <span v-if="notifications?.unread_count > 0" class="text-xs font-bold text-indigo-600 bg-indigo-100 px-2 py-0.5 rounded-full">
                                            {{ notifications.unread_count }} mới
                                        </span>
                                    </div>

                                    <div class="overflow-y-auto flex-grow custom-scrollbar">
                                        <div v-for="n in notifications?.list" :key="n.id"
                                             @click="handleNotificationClick(n)"
                                             class="px-4 py-3 hover:bg-slate-50 cursor-pointer border-b border-gray-50 last:border-0 transition relative"
                                             :class="!n.read_at ? 'bg-indigo-50/50' : 'opacity-70'">

                                            <div v-if="!n.read_at" class="absolute left-2 top-1/2 -translate-y-1/2 w-1.5 h-1.5 bg-indigo-600 rounded-full"></div>

                                            <div class="pl-3">
                                                <p class="text-xs font-black mb-1" :class="n.data.loai === 'error' ? 'text-red-600' : (n.data.loai === 'success' ? 'text-emerald-600' : (n.data.loai === 'warning' ? 'text-amber-600' : 'text-indigo-600'))">
                                                    {{ n.data.ma_phieu }}
                                                </p>
                                                <p class="text-sm text-gray-800 leading-snug font-medium line-clamp-2" :class="!n.read_at ? 'font-bold' : ''">
                                                    {{ n.data.thong_diep }}
                                                </p>
                                                <p class="text-[10px] text-gray-400 mt-1.5 uppercase font-bold tracking-wider">
                                                    {{ new Date(n.created_at).toLocaleDateString('vi-VN') }} {{ new Date(n.created_at).toLocaleTimeString('vi-VN', {hour: '2-digit', minute:'2-digit'}) }}
                                                </p>
                                            </div>
                                        </div>

                                        <div v-if="!notifications?.list || notifications.list.length === 0" class="px-4 py-10 flex flex-col items-center justify-center text-gray-400">
                                            <svg class="w-10 h-10 mb-2 opacity-20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>
                                            <span class="text-sm font-medium">Bạn chưa có thông báo nào.</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-if="user" class="relative group ml-2">
                            <button class="flex items-center gap-2 hover:bg-gray-50 p-1 rounded-full border border-transparent hover:border-gray-200 transition">
                                <img class="h-9 w-9 rounded-full object-cover shadow-sm"
                                    :src="user.avatar ? (user.avatar.startsWith('http') ? user.avatar : '/storage/' + user.avatar) : 'https://ui-avatars.com/api/?name=' + user.name + '&background=e0e7ff&color=4338ca'"
                                    alt="Avatar" />
                                <div class="hidden md:flex flex-col text-left mr-2">
                                    <span class="text-sm font-bold text-gray-800 leading-tight">{{ user.name }}</span>
                                    <span class="text-[10px] font-black text-indigo-600 uppercase tracking-wider">{{ userRoleLabel }}</span>
                                </div>
                                <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                            </button>

                            <div class="absolute right-0 top-full pt-2 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50 min-w-[200px]">
                                <div class="bg-white border border-gray-100 shadow-xl rounded-xl overflow-hidden py-1">
                                    <div class="px-4 py-3 border-b border-gray-100 md:hidden">
                                        <p class="text-sm text-gray-900 font-bold">{{ user.name }}</p>
                                        <p class="text-xs text-gray-500 truncate">{{ userRoleLabel }}</p>
                                    </div>
                                    <Link :href="route('logout')" method="post" as="button" class="w-full text-left px-4 py-2.5 text-sm font-bold text-red-600 hover:bg-red-50 flex items-center gap-2 transition">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                                        Đăng xuất
                                    </Link>
                                </div>
                            </div>
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
                    <Link :href="route('dashboard')" class="block w-full pl-3 pr-4 py-2 border-l-4 border-indigo-500 text-indigo-700 bg-indigo-50 text-base font-bold">Dashboard</Link>
                    <Link :href="route('phieu.create')" class="block w-full pl-3 pr-4 py-2 border-l-4 border-transparent text-gray-600 hover:bg-gray-50 font-medium">Tạo Mua sắm</Link>
                    <Link :href="route('nghiphep.create')" class="block w-full pl-3 pr-4 py-2 border-l-4 border-transparent text-gray-600 hover:bg-gray-50 font-medium">Tạo Nghỉ phép</Link>
                </div>
            </div>
        </nav>

        <header class="bg-white shadow-sm border-b" v-if="$slots.header">
            <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
                <slot name="header" />
            </div>
        </header>

        <main class="flex-grow">
            <slot />
        </main>

        <footer class="bg-white border-t border-gray-200 mt-8">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row justify-between items-center gap-4">
                <div class="text-sm text-gray-500">
                    &copy; 2026 <span class="font-bold text-indigo-700">ProcureFlow ERP</span>. Bản quyền thuộc về Công ty XYZ.
                </div>
                <div class="flex gap-4 text-sm font-medium text-gray-400">
                    <a href="#" class="hover:text-indigo-600 transition">Hỗ trợ kỹ thuật</a>
                    <a href="#" class="hover:text-indigo-600 transition">Tài liệu HDSD</a>
                </div>
            </div>
        </footer>

    </div>
</template>
