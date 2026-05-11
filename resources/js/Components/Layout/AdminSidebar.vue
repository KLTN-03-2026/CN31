<script setup>
import { computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';

const props = defineProps({
    user: { type: Object, default: null }
});

const icons = {
    'users': `<path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.952-3.138m1.54 3.097a11.99 11.99 0 003.11-1.54M12 11.25a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zm8.25 1.5a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0z" />`,
    'document': `<path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />`,
    'category': `<path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />`,
    'supplier': `<path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9H6.75a9 9 0 00-9 9V14.25m17.25 4.5v-18" />`,
    'department': `<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008z" />`,
    'settings': `<path stroke-linecap="round" stroke-linejoin="round" d="M10.343 3.94c.09-.542.56-.94 1.11-.94h1.093c.55 0 1.02.398 1.11.94l.149.894c.07.424.384.764.78.93.398.164.855.142 1.205-.108l.737-.527a1.125 1.125 0 011.45.12l.773.774c.39.389.44 1.002.12 1.45l-.527.737c-.25.35-.272.806-.107 1.204.165.397.505.71.93.78l.893.15c.543.09.94.56.94 1.109v1.094c0 .55-.397 1.02-.94 1.11l-.893.149c-.425.07-.765.383-.93.78-.165.398-.143-.854-.107 1.204l.527.738c.32.447.269 1.06-.12 1.45l-.774.773a1.125 1.125 0 01-1.449.12l-.738-.527c-.35-.25-.806-.272-1.203-.107-.397.165-.71-.505-.781.929l-.149.894c-.09.542-.56.94-1.11.94h-1.094c-.55 0-1.019-.398-1.11-.94l-.148-.894c-.071-.424-.384-.764-.781-.93-.398-.164-.854-.142-1.204.108l-.738.527c-.447.32-1.06.269-1.45-.12l-.773-.774a1.125 1.125 0 01-.12-1.45l.527-.737c.25-.35.273-.806.108-1.204-.165-.397-.505-.71-.93-.78l-.894-.15c-.542-.09-.94-.56-.94-1.109v-1.094c0-.55.398-1.02.94-1.11l.894-.149c.424-.07.765-.383.93-.78.165-.398.143-.854-.107-1.204l-.527-.738a1.125 1.125 0 01.12-1.45l.773-.773a1.125 1.125 0 011.45-.12l.737.527c.35.25.807.272 1.204.107.397-.165.71-.505.78-.929l.15-.894z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />`,
    'more': `<path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />`
};

const adminMenu = computed(() => [
    { name: 'Hồ sơ Nhân sự', route: 'admin.users.index', active: 'admin.users.*', icon: icons['users'] },
    { name: 'Phiếu Yêu Cầu', route: 'admin.phieu_yeu_cau.index', active: 'admin.phieu_yeu_cau.*', icon: icons['document'] },
    { name: 'Phòng ban', route: 'admin.phongban.index', active: 'admin.phongban.*', icon: icons['department'] },
    { name: 'Danh mục', route: 'admin.danhmuc.index', active: 'admin.danhmuc.*', icon: icons['category'] },
    { name: 'Nhà cung cấp', route: 'admin.nhacungcap.index', active: 'admin.nhacungcap.*', icon: icons['supplier'] },
    { name: 'Cài đặt hệ thống', route: 'admin.settings.index', active: 'admin.settings.*', icon: icons['settings'] },
]);

const handleLogout = () => router.post(route('logout'));
</script>

<template>
    <aside class="hidden md:flex flex-col fixed left-0 top-0 h-screen bg-white border-r border-slate-200 z-50 transition-all duration-300 w-[72px] hover:w-[244px] group/sidebar overflow-hidden">
        <div class="h-24 flex items-center px-4 pt-4 shrink-0 cursor-pointer">
            <Link :href="route('admin.users.index')" class="flex itexpms-center gap-3">
                <img src="https://cdn.jsdelivr.net/gh/glincker/thesvg@main/public/icons/google/mono.svg" alt="Google" class="h-6 w-6"/>
            </Link>
        </div>

        <div class="flex-1 overflow-y-auto px-2 mt-4 flex flex-col gap-1.5 custom-scrollbar">
            <Link
                v-for="item in adminMenu" :key="item.name" :href="route(item.route)"
                class="flex items-center gap-4 p-3 rounded-xl transition-all duration-200 group/item outline-none"
                :class="route().current(item.active) ? 'text-slate-900 bg-slate-50' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'"
            >
                <div class="w-6 h-6 flex items-center justify-center shrink-0 transition-transform group-hover/item:scale-110">
                    <svg class="w-6 h-6"
                         :fill="route().current(item.active) ? 'currentColor' : 'none'"
                         :stroke="route().current(item.active) ? 'none' : 'currentColor'"
                         stroke-width="1.5"
                         viewBox="0 0 24 24"
                         v-html="item.icon">
                    </svg>
                </div>
                <span class="text-[15px] whitespace-nowrap opacity-0 group-hover/sidebar:opacity-100 transition-opacity duration-300"
                      :class="route().current(item.active) ? 'font-bold' : 'font-medium'">
                    {{ item.name }}
                </span>
            </Link>
        </div>

        <div class="mt-auto px-2 pb-6 shrink-0 relative group/more">
            <button class="w-full flex items-center gap-4 p-3 rounded-xl text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition-all outline-none">
                <div class="w-6 h-6 flex items-center justify-center shrink-0 transition-transform group-hover/more:scale-110">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" v-html="icons['more']"></svg>
                </div>
                <span class="font-medium text-[15px] whitespace-nowrap opacity-0 group-hover/sidebar:opacity-100 transition-opacity duration-300">
                    Xem thêm
                </span>
            </button>

            <div class="absolute bottom-full left-0 mb-2 ml-2 w-56 bg-white rounded-2xl shadow-[0_4px_24px_rgba(0,0,0,0.12)] border border-slate-100 opacity-0 invisible group-hover/more:opacity-100 group-hover/more:visible transition-all duration-200 z-50 overflow-hidden">
                <div class="p-2">
                    <button @click="handleLogout" class="flex items-center gap-3 w-full px-4 py-3 rounded-xl hover:bg-red-50 text-sm font-semibold text-red-600 transition-colors">
                        Đăng xuất hệ thống
                    </button>
                </div>
            </div>
        </div>
    </aside>

    <nav class="md:hidden fixed bottom-0 left-0 w-full h-[60px] bg-white border-t border-slate-200 z-50 flex justify-around items-center px-2 pb-safe shadow-lg">
        <Link
            v-for="item in adminMenu" :key="'mob-'+item.name" :href="route(item.route)"
            class="flex items-center justify-center w-full h-full transition-colors"
            :class="route().current(item.active) ? 'text-blue-600' : 'text-slate-500 hover:text-slate-800'"
        >
            <svg class="w-6 h-6" :fill="route().current(item.active) ? 'currentColor' : 'none'" :stroke="route().current(item.active) ? 'none' : 'currentColor'" stroke-width="1.5" viewBox="0 0 24 24" v-html="item.icon"></svg>
        </Link>
    </nav>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { width: 0px; }
</style>
