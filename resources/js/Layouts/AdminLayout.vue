<script setup>
import AdminSidebar from '@/Components/Layout/AdminSidebar.vue';
import ProcureBotWidget from '@/Components/UI/ProcureBotWidget.vue'; // 1. Import Bot
</script>

<template>
    <div class="flex h-screen bg-white overflow-hidden font-sans text-slate-800 relative">

        <AdminSidebar :user="$page.props.auth.user" />

        <main class="flex-1 flex flex-col w-full h-screen overflow-hidden bg-white md:ml-[72px] relative z-0 transition-all duration-300">

            <div class="flex-1 overflow-y-auto w-full custom-scrollbar-main">

                <div class="max-w-5xl mx-auto w-full p-4 pb-24 sm:p-8 md:py-10">

                    <slot />

                </div>
            </div>

        </main>

        <ProcureBotWidget />

    </div>
</template>

<style scoped>
.custom-scrollbar-main::-webkit-scrollbar { width: 6px; }
.custom-scrollbar-main::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar-main::-webkit-scrollbar-thumb { background-color: #cbd5e1; border-radius: 10px; }
.custom-scrollbar-main::-webkit-scrollbar-thumb:hover { background-color: #94a3b8; }
</style>
