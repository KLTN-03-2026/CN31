<script setup>
import { ref, watch, onMounted, onUnmounted } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import axios from 'axios';

const page = usePage();
const user = page.props.auth?.user;

// Quản lý state độc lập
const notifications = ref(page.props.auth?.notifications || { list: [], unread_count: 0 });

watch(() => page.props.auth?.notifications, (newVal) => {
    if (newVal) notifications.value = newVal;
}, { deep: true });

const handleNotificationClick = (n) => {
    if (!n.read_at) {
        axios.post(route('notifications.read', n.id)).then(response => {
            router.visit(response.data.url);
        }).catch(error => console.error("Lỗi:", error));
    } else {
        router.visit(route('phieu.show', n.data.phieu_id));
    }
};

// Logic WebSockets được gói gọn hoàn toàn ở đây
onMounted(() => {
    if (user) {
        window.Echo.private(`App.Models.User.${user.id}`)
            .notification((notification) => {
                if (!notifications.value) notifications.value = { list: [], unread_count: 0 };
                notifications.value.unread_count++;

                const newNoti = {
                    id: notification.id,
                    data: notification,
                    read_at: null,
                    created_at: notification.created_at
                };
                notifications.value.list.unshift(newNoti);
            });
    }
});

onUnmounted(() => {
    if (user) window.Echo.leave(`App.Models.User.${user.id}`);
});
</script>

<template>
    <div class="relative group">
        <button class="relative p-2 text-slate-400 hover:text-blue-600 transition rounded-full hover:bg-blue-50 focus:outline-none">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>

            <span v-if="notifications?.unread_count > 0" class="absolute top-0 right-0 flex h-4 w-4">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-4 w-4 bg-red-500 text-[10px] text-white font-bold items-center justify-center">
                    {{ notifications.unread_count > 9 ? '9+' : notifications.unread_count }}
                </span>
            </span>
        </button>

        <div class="absolute right-0 top-full pt-2 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50 w-80">
            <div class="bg-white border border-slate-200 shadow-xl rounded-xl overflow-hidden flex flex-col max-h-[400px]">
                <div class="px-4 py-3 bg-slate-50 border-b border-slate-100 flex justify-between items-center shrink-0">
                    <h3 class="font-bold text-sm text-slate-800">Thông báo</h3>
                </div>

                <div class="overflow-y-auto flex-grow custom-scrollbar">
                    <div v-for="n in notifications?.list" :key="n.id"
                         @click="handleNotificationClick(n)"
                         class="px-4 py-3 hover:bg-slate-50 cursor-pointer border-b border-slate-50 last:border-0 transition relative"
                         :class="!n.read_at ? 'bg-blue-50/30' : 'opacity-70'">
                        <div v-if="!n.read_at" class="absolute left-2 top-1/2 -translate-y-1/2 w-1.5 h-1.5 bg-blue-600 rounded-full"></div>
                        <div class="pl-3">
                            <p class="text-xs font-bold text-slate-500 mb-1">{{ n.data.ma_phieu }}</p>
                            <p class="text-sm text-slate-800 leading-snug" :class="!n.read_at ? 'font-semibold' : ''">{{ n.data.thong_diep }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
