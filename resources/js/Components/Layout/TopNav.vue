<script setup>
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import NotificationDropdown from './NotificationDropdown.vue';
import UserDropdown from './UserDropdown.vue';

const props = defineProps({
    user: { type: Object, default: null }
});

const showingNavigationDropdown = ref(false);

const navigationMenu = computed(() => {
    const role = props.user?.vai_tro;
    let menu = [];

    // Tùy theo Role mà Push đúng Menu chính vào
    if (role === 'admin') {
        menu.push({ name: 'Hồ sơ Nhân sự', route: 'admin.users.index', active: 'admin.users.*' });
        menu.push({ name: 'Danh mục', route: 'admin.danhmuc.index', active: 'admin.danhmuc.*' });
        menu.push({ name: 'Phòng ban', route: 'admin.phongban.index', active: 'admin.phongban.*' });
        menu.push({ name: 'Nhà cung cấp', route: 'admin.nhacungcap.index', active: 'admin.nhacungcap.*' });
    }
    else if (role === 'nhan_vien') {
        menu.push({ name: 'Dashboard', route: 'dashboard' });
    }
    else if (role === 'truong_phong') {
        menu.push({ name: 'Trưởng phòng', route: 'manager.approvals' });
    }
    else if (role === 'giam_doc') {
        menu.push({ name: 'Giám đốc', route: 'director.approvals' });
    }
    else if (role === 'nhan_su') {
        menu.push({ name: 'Nhân sự', route: 'hr.index' });
    }
    else if (role === 'ke_toan') {
        menu.push({ name: 'Kế toán', route: 'accountant.index' });
    }
    else if (role === 'nhan_vien_mua_sam') {
        menu.push({ name: 'Mua sắm', route: 'purchasing.index' });
    }

    // TẤT CẢ mọi người đều có menu Bảng tin
    menu.push({ name: 'Bảng tin', route: 'blog.index', active: 'blog.*' });

    return menu;
});
</script>

<template>
    <nav class="bg-white border-b border-slate-200 sticky top-0 z-40 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">

                <div class="flex items-center gap-8">
                    <Link :href="route('dashboard')" class="font-black text-blue-600 text-xl tracking-tight transition-transform hover:scale-105">
                        Procure<span class="text-slate-900">Flow</span>
                    </Link>

                    <div class="hidden sm:flex items-center gap-6">
                        <Link
                            v-for="item in navigationMenu"
                            :key="item.name"
                            :href="route(item.route)"
                            class="text-sm font-semibold transition-colors duration-200 py-2 border-b-2"
                            :class="route().current(item.active || item.route) ? 'border-blue-600 text-blue-600' : 'border-transparent text-slate-500 hover:text-slate-900'"
                        >
                            {{ item.name }}
                        </Link>
                    </div>
                </div>

                <div class="hidden sm:flex items-center gap-4">

                    <div class="relative group" v-if="user && user.vai_tro === 'nhan_vien'">
                        <button class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold py-2 px-4 rounded-lg shadow-sm hover:shadow transition-all flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                            Tạo yêu cầu
                        </button>
                        <div class="absolute right-0 top-full pt-2 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50 w-56">
                            <div class="bg-white border border-slate-100 shadow-xl rounded-xl overflow-hidden p-1.5 flex flex-col gap-1">
                                <Link :href="route('phieu.create')" class="flex items-start gap-3 px-3 py-2.5 rounded-lg hover:bg-slate-50 transition group/item">
                                    <div class="mt-0.5 text-slate-400 group-hover/item:text-blue-600 transition-colors"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg></div>
                                    <div>
                                        <p class="text-sm font-semibold text-slate-800 group-hover/item:text-blue-600 transition-colors">Mua sắm thiết bị</p>
                                        <p class="text-[11px] text-slate-500">Xin cấp tài sản, chi tiêu</p>
                                    </div>
                                </Link>
                                <Link :href="route('nghiphep.create')" class="flex items-start gap-3 px-3 py-2.5 rounded-lg hover:bg-slate-50 transition group/item">
                                    <div class="mt-0.5 text-slate-400 group-hover/item:text-blue-600 transition-colors"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg></div>
                                    <div>
                                        <p class="text-sm font-semibold text-slate-800 group-hover/item:text-blue-600 transition-colors">Nghỉ phép</p>
                                        <p class="text-[11px] text-slate-500">Xin nghỉ năm, ốm đau</p>
                                    </div>
                                </Link>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-1 border-l border-slate-200 pl-4 ml-1">
                        <NotificationDropdown v-if="user" />
                        <UserDropdown v-if="user" :user="user" />
                    </div>
                </div>

                <div class="-mr-2 flex items-center sm:hidden">
                    <button @click="showingNavigationDropdown = !showingNavigationDropdown" class="inline-flex items-center justify-center p-2 rounded-md text-slate-400 hover:text-slate-500 hover:bg-slate-100 focus:outline-none transition duration-150 ease-in-out">
                        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path :class="{ 'hidden': showingNavigationDropdown, 'inline-flex': !showingNavigationDropdown }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            <path :class="{ 'hidden': !showingNavigationDropdown, 'inline-flex': showingNavigationDropdown }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <div :class="{ 'block': showingNavigationDropdown, 'hidden': !showingNavigationDropdown }" class="sm:hidden border-t border-slate-200 bg-white">
            <div class="pt-2 pb-3 space-y-1">
                <Link
                    v-for="item in navigationMenu"
                    :key="'mobile-'+item.name"
                    :href="route(item.route)"
                    class="block w-full pl-4 pr-4 py-3 border-l-4 font-medium"
                    :class="route().current(item.active || item.route) ? 'border-blue-600 text-blue-700 bg-blue-50' : 'border-transparent text-slate-600 hover:bg-slate-50'"
                >
                    {{ item.name }}
                </Link>

                <template v-if="user?.vai_tro === 'nhan_vien'">
                    <div class="border-t border-slate-100 my-2"></div>
                    <Link :href="route('phieu.create')" class="block w-full pl-4 pr-4 py-3 border-l-4 border-transparent text-slate-600 hover:bg-slate-50 font-medium">Tạo Mua sắm</Link>
                    <Link :href="route('nghiphep.create')" class="block w-full pl-4 pr-4 py-3 border-l-4 border-transparent text-slate-600 hover:bg-slate-50 font-medium">Tạo Nghỉ phép</Link>
                </template>
            </div>
        </div>
    </nav>
</template>
