<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    user: {
        type: Object,
        required: true
    }
});

const roleLabels = {
    'admin': 'Quản trị viên',
    'nhan_vien': 'Nhân viên',
    'truong_phong': 'Trưởng phòng',
    'giam_doc': 'Giám đốc',
    'ke_toan': 'Kế toán',
    'nhan_su': 'Nhân sự',
    'nhan_vien_mua_sam': 'Mua sắm'
};


const userRoleLabel = computed(() => roleLabels[props.user?.vai_tro] || 'Nhân viên');
</script>

<template>
    <div class="relative group ml-2">
        <button class="flex items-center gap-2 hover:bg-slate-50 p-1 rounded-full border border-transparent hover:border-slate-200 transition focus:outline-none">
            <img class="h-9 w-9 rounded-full object-cover border border-slate-200"
                 :src="user.avatar ? '/storage/' + user.avatar : `https://ui-avatars.com/api/?name=${user.name}&background=f8fafc&color=0f172a`"
                 alt="Avatar" />
            <div class="hidden md:flex flex-col text-left mr-2">
                <span class="text-sm font-semibold text-slate-800 leading-tight">{{ user.name }}</span>
                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">{{ userRoleLabel }}</span>
            </div>
            <svg class="w-4 h-4 text-slate-400 group-hover:text-slate-600 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
        </button>

        <div class="absolute right-0 top-full pt-2 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50 min-w-[200px]">
            <div class="bg-white border border-slate-200 shadow-xl rounded-xl overflow-hidden py-1">

                <Link :href="route('profile.edit')" class="w-full text-left px-4 py-2.5 text-sm font-semibold text-slate-700 hover:text-blue-600 hover:bg-slate-50 flex items-center gap-2 transition">
                    <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" /></svg>
                    Đổi mật khẩu
                </Link>

                <div class="border-t border-slate-100 my-1"></div>

                <Link :href="route('logout')" method="post" as="button" class="w-full text-left px-4 py-2.5 text-sm font-semibold text-red-600 hover:bg-red-50 flex items-center gap-2 transition">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                    Đăng xuất
                </Link>

            </div>
        </div>
    </div>
</template>
