<script setup>
import { ref, computed, watch } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import debounce from 'lodash/debounce'; // Import thư viện Debounce
import NotificationDropdown from './NotificationDropdown.vue';
import UserDropdown from './UserDropdown.vue';

defineOptions({ layout: null });

const props = defineProps({
    user: { type: Object, default: null }
});

const page = usePage();
const showingNavigationDropdown = ref(false);

// 1. LẤY TỪ KHÓA CŨ TỪ SERVER TRẢ VỀ (Để giữ nguyên chữ trên ô input khi load)
const currentSearch = page.props.roleData?.filters?.search || page.props.filters?.search || '';
const globalSearchQuery = ref(currentSearch);

// 2. LIVE SEARCH VỚI DEBOUNCE (Đợi 500ms sau khi ngừng gõ mới gọi API)
watch(globalSearchQuery, debounce((value) => {
    // Chỉ gửi request nếu value khác rỗng hoặc URL hiện tại đang có tham số search (để có thể xóa search)
    router.get(
        page.url, // Gọi về chính cái URL người dùng đang đứng
        { search: value }, // Tham số query string
        {
            preserveState: true,  // Giữ nguyên state của các component
            preserveScroll: true, // Không giật trang lên trên cùng
            replace: true         // Ghi đè lịch sử trình duyệt, không làm rác nút Back
        }
    );
}, 500));

const navigationMenu = computed(() => {
    const role = props.user?.vai_tro;
    let menu = [];

    if (role === 'admin') {
        menu.push({ name: 'Dashboard', route: 'admin.users.index', active: 'admin.users.*' });
        // menu.push({ name: 'Dashboard', route: 'admin.danhmuc.index', active: 'admin.danhmuc.*' });
    }
    else if (role === 'nhan_vien') {
        menu.push({ name: 'Dashboard', route: 'dashboard' });
    }
    else if (role === 'truong_phong') {
        menu.push({ name: 'Dashboard', route: 'manager.approvals' });
    }
    else if (role === 'giam_doc') {
        menu.push({ name: 'Dashboard', route: 'director.approvals' });
    }
    else if (role === 'nhan_su') {
        menu.push({ name: 'Dashboard', route: 'hr.index' });
    }
    else if (role === 'ke_toan') {
        menu.push({ name: 'Dashboard', route: 'accountant.index' });
    }
    else if (role === 'nhan_vien_mua_sam') {
        menu.push({ name: 'Dashboard', route: 'purchasing.index' });
    }

    menu.push({ name: 'Bảng tin', route: 'blog.index', active: 'blog.*' });

    return menu;
});
</script>

<template>
    <nav class="bg-slate-50/90 backdrop-blur-md sticky top-0 z-40 border-b border-slate-200/60 transition-all duration-300">
        <div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center gap-4 lg:gap-8">

                <!-- ==========================================
                     CỘT TRÁI: LOGO & MENU CÙNG MỘT HÀNG
                =========================================== -->
                <div class="flex items-center gap-6 shrink-0">
                    <Link :href="route('dashboard')" class="font-black text-blue-600 text-xl tracking-tight transition-transform hover:scale-105 shrink-0">
                        MrGiot<span class="text-slate-900">Tech</span>
                    </Link>

                    <div class="hidden lg:flex items-center gap-6 ml-2">
                        <Link
                            v-for="item in navigationMenu"
                            :key="item.name"
                            :href="route(item.route)"
                            class="group relative flex flex-col items-center justify-center pt-1 outline-none"
                        >
                            <span aria-hidden="true" class="relative inline-flex overflow-y-clip h-6 leading-6 text-[13.5px] font-bold transition-colors duration-200"
                                  :class="route().current(item.active || item.route) ? 'text-blue-600' : 'text-slate-500 hover:text-slate-700'">
                                <span class="flex flex-col transition-transform duration-300 ease-out will-change-transform group-hover:-translate-y-6">
                                    <span class="block whitespace-nowrap">{{ item.name }}</span>
                                    <span class="block whitespace-nowrap text-blue-600">{{ item.name }}</span>
                                </span>
                            </span>
                            <span class="h-[2px] w-full mt-1 rounded-full transition-colors duration-300"
                                  :class="route().current(item.active || item.route) ? 'bg-blue-600' : 'bg-transparent group-hover:bg-blue-100'"></span>
                        </Link>
                    </div>
                </div>

                <!-- ==========================================
                     CỘT GIỮA: THANH TÌM KIẾM (GLOBAL SEARCH)
                =========================================== -->
                <div class="flex-1 flex justify-center max-w-2xl hidden sm:flex">
                    <!-- Dùng @submit.prevent trống để ngăn chặn submit form mặc định của HTML khi bấm Enter -->
                    <form @submit.prevent class="w-full relative group">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-4 w-4 text-slate-400 group-focus-within:text-blue-600 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>

                        <input
                            type="text"
                            v-model="globalSearchQuery"
                            placeholder="Tìm kiếm mã phiếu, tên tài sản..."
                            class="w-full pl-10 pr-12 py-2 bg-white border border-slate-300 rounded-full text-sm placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all shadow-sm"
                        >

                        <div class="absolute inset-y-1 right-1">
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white p-1.5 rounded-full transition-colors shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-blue-600">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- ==========================================
                     CỘT PHẢI: ACTIONS & PROFILE
                =========================================== -->
                <div class="flex items-center justify-end gap-2 md:gap-4 shrink-0">

                    <div class="relative group hidden md:block" v-if="user && user.vai_tro === 'nhan_vien'">
                        <button class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold py-1.5 px-3.5 rounded-full shadow-sm hover:shadow transition-all flex items-center gap-1.5 outline-none">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                            Tạo
                        </button>

                        <div class="absolute right-0 top-full pt-2 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50 w-56 origin-top-right">
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

                    <div class="flex items-center gap-1 border-l border-slate-200/80 pl-3 md:pl-4 ml-1">
                        <NotificationDropdown v-if="user" />
                        <UserDropdown v-if="user" :user="user" />
                    </div>
                </div>

                <!-- Nút Hamburger Mobile -->
                <div class="-mr-2 flex items-center lg:hidden ml-2">
                    <button @click="showingNavigationDropdown = !showingNavigationDropdown" class="inline-flex items-center justify-center p-2 rounded-md text-slate-400 hover:text-slate-500 hover:bg-slate-100 focus:outline-none transition duration-150 ease-in-out">
                        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path :class="{ 'hidden': showingNavigationDropdown, 'inline-flex': !showingNavigationDropdown }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            <path :class="{ 'hidden': !showingNavigationDropdown, 'inline-flex': showingNavigationDropdown }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- ==========================================
             MENU MOBILE GẮN KÈM SEARCH MOBILE
        =========================================== -->
        <div :class="{ 'block': showingNavigationDropdown, 'hidden': !showingNavigationDropdown }" class="lg:hidden border-t border-slate-200/50 bg-slate-50/95 backdrop-blur-md">
            <div class="p-4 sm:hidden">
                <form @submit.prevent class="relative">
                    <input type="text" v-model="globalSearchQuery" placeholder="Tìm kiếm phiếu..." class="w-full pl-4 pr-10 py-2.5 bg-white border border-slate-300 rounded-lg text-sm focus:outline-none focus:border-blue-600">
                    <button type="submit" class="absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 p-1">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    </button>
                </form>
            </div>

            <div class="pt-2 pb-3 space-y-1">
                <Link
                    v-for="item in navigationMenu"
                    :key="'mobile-'+item.name"
                    :href="route(item.route)"
                    class="block w-full pl-4 pr-4 py-3 border-l-4 font-bold text-sm"
                    :class="route().current(item.active || item.route) ? 'border-blue-600 text-blue-700 bg-blue-100/50' : 'border-transparent text-slate-600 hover:bg-slate-200/50'"
                >
                    {{ item.name }}
                </Link>

                <template v-if="user?.vai_tro === 'nhan_vien'">
                    <div class="border-t border-slate-200/50 my-2"></div>
                    <Link :href="route('phieu.create')" class="block w-full pl-4 pr-4 py-3 border-l-4 border-transparent text-slate-600 hover:bg-slate-200/50 font-bold text-sm">Tạo Mua sắm</Link>
                    <Link :href="route('nghiphep.create')" class="block w-full pl-4 pr-4 py-3 border-l-4 border-transparent text-slate-600 hover:bg-slate-200/50 font-bold text-sm">Tạo Nghỉ phép</Link>
                </template>
            </div>
        </div>
    </nav>
</template>
