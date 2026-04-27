<script setup>
import { ref, watch, onMounted, onUnmounted } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import axios from 'axios';

const page = usePage();
const user = page.props.auth?.user;

const notifications = ref(page.props.auth?.notifications || { list: [], unread_count: 0 });
const activeToast = ref(null);
let toastTimer = null;

// 1. Lắng nghe thông báo Realtime từ Backend
watch(() => page.props.auth?.notifications, (newVal) => {
    if (newVal) notifications.value = newVal;
}, { deep: true });

// 2. Lắng nghe thông báo Session (Flash Messages: Đăng nhập, Cập nhật...)
watch(() => page.props.flash, (flash) => {
    if (flash?.success || flash?.error || flash?.message) {
        const type = flash.error ? 'LỖI' : 'THÔNG BÁO';
        const msg = flash.success || flash.error || flash.message;

        // Mượn giao diện của Toast để hiển thị
        activeToast.value = {
            data: { ma_phieu: type, thong_diep: msg },
            created_at_label: 'Hệ thống'
        };

        if (toastTimer) clearTimeout(toastTimer);
        toastTimer = setTimeout(() => { activeToast.value = null; }, 5000);
    }
}, { deep: true, immediate: true });

const handleNotificationClick = (n) => {
    activeToast.value = null;
    if (!n.read_at && n.id) {
        axios.post(route('notifications.read', n.id)).then(response => {
            router.visit(response.data.url);
        });
    } else if (n.data?.phieu_id) {
        router.visit(route('phieu.show', n.data.phieu_id));
    }
};

onMounted(() => {
    if (user) {
        if (Notification.permission !== "granted" && Notification.permission !== "denied") {
            Notification.requestPermission();
        }

        window.Echo.private(`App.Models.User.${user.id}`)
            .notification((notification) => {
                if (!notifications.value) notifications.value = { list: [], unread_count: 0 };
                notifications.value.unread_count++;

                const newNoti = {
                    id: notification.id || Date.now(),
                    data: notification,
                    read_at: null,
                    created_at_label: notification.created_at_label || 'Vừa xong'
                };
                notifications.value.list.unshift(newNoti);

                activeToast.value = newNoti;
                if (toastTimer) clearTimeout(toastTimer);
                toastTimer = setTimeout(() => { activeToast.value = null; }, 6000);

                if (document.hidden && Notification.permission === "granted") {
                    new Notification(`${notification.ma_phieu}`, { body: notification.thong_diep });
                }
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
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>
            <span v-if="notifications?.unread_count > 0" class="absolute top-0 right-0 flex h-4 w-4">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-4 w-4 bg-red-500 text-[10px] text-white font-bold items-center justify-center">
                    {{ notifications.unread_count > 9 ? '9+' : notifications.unread_count }}
                </span>
            </span>
        </button>

        <div class="absolute right-0 top-full pt-2 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50 w-80">
            <div class="bg-white border border-slate-200 shadow-2xl rounded-2xl overflow-hidden flex flex-col">
                <div class="px-4 py-3 border-b border-slate-100"><h3 class="font-black text-lg text-slate-900">Thông báo</h3></div>

                <div class="overflow-y-auto max-h-[400px] custom-scrollbar">
                    <div v-if="notifications?.list.length > 0">
                        <div v-for="n in notifications.list" :key="n.id" @click="handleNotificationClick(n)"
                             class="px-4 py-3 hover:bg-slate-50 cursor-pointer border-b border-slate-50 transition flex gap-3"
                             :class="!n.read_at ? 'bg-blue-50/40' : ''">
                            <div class="mt-2 shrink-0"><div class="w-2 h-2 rounded-full" :class="!n.read_at ? 'bg-blue-600' : 'bg-transparent'"></div></div>
                            <div class="flex-1 min-w-0">
                                <p class="text-[11px] font-bold text-blue-600 uppercase">{{ n.data.ma_phieu }}</p>
                                <p class="text-sm text-slate-800 leading-snug mb-1" :class="!n.read_at ? 'font-bold' : 'font-medium'">{{ n.data.thong_diep }}</p>
                                <p class="text-[10px] text-slate-400 font-bold uppercase">{{ n.created_at_label }}</p>
                            </div>
                        </div>
                    </div>
                    <div v-else class="p-8 text-center"><p class="text-sm font-bold text-slate-400">Bạn chưa có thông báo nào</p></div>
                </div>
            </div>
        </div>
    </div>

    <transition enter-active-class="transform ease-out duration-300 transition" enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-4" enter-to-class="translate-y-0 opacity-100 sm:translate-x-0" leave-active-class="transition ease-in duration-200" leave-from-class="opacity-100" leave-to-class="opacity-0">
        <div v-if="activeToast" class="fixed bottom-6 right-6 z-[100] w-full max-w-sm cursor-pointer hover:scale-[1.02] transition-transform" @click="handleNotificationClick(activeToast)">
            <div class="bg-white rounded-xl shadow-2xl border border-slate-100 flex items-start p-4 relative">
                <button @click.stop="activeToast = null" class="absolute top-2 right-2 text-slate-400 hover:text-slate-600"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg></button>
                <div class="shrink-0"><div class="w-10 h-10 rounded-full flex items-center justify-center bg-blue-100 text-blue-600"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg></div></div>
                <div class="ml-3 w-0 flex-1">
                    <p class="text-[11px] font-black text-blue-600 uppercase">{{ activeToast.data.ma_phieu }}</p>
                    <p class="text-sm font-bold text-slate-900 leading-snug mt-0.5">{{ activeToast.data.thong_diep }}</p>
                    <p class="text-[10px] text-slate-400 font-bold mt-1 uppercase">Vừa xong</p>
                </div>
            </div>
        </div>
    </transition>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
</style>
