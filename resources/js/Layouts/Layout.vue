<script setup>
import { computed, ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import TopNav from '@/Components/Layout/TopNav.vue';
import AppFooter from '@/Components/Layout/AppFooter.vue';

const page = usePage();
const user = computed(() => page.props.auth?.user);

const flashSuccess = computed(() => page.props.flash?.success);
const flashError = computed(() => page.props.flash?.error || page.props.errors?.error);
const showFlash = ref(false);

watch([flashSuccess, flashError], () => {
    if (flashSuccess.value || flashError.value) {
        showFlash.value = true;
        setTimeout(() => { showFlash.value = false; }, 4000);
    }
}, { immediate: true });
</script>

<template>
    <div class="min-h-screen bg-slate-50 flex flex-col font-sans text-slate-900">

        <div class="fixed top-6 right-6 z-[60] flex flex-col gap-3 transition-all duration-300 pointer-events-none"
             :class="showFlash ? 'translate-x-0 opacity-100' : 'translate-x-10 opacity-0'">
            <div v-if="flashSuccess" class="bg-slate-900 text-white px-5 py-3.5 rounded-lg shadow-2xl font-medium flex items-center gap-3 pointer-events-auto text-sm border border-slate-700">
                <span class="flex items-center justify-center w-6 h-6 rounded-full bg-green-500/20 text-green-400">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                </span>
                {{ flashSuccess }}
            </div>
            <div v-if="flashError" class="bg-slate-900 text-white px-5 py-3.5 rounded-lg shadow-2xl font-medium flex items-center gap-3 pointer-events-auto text-sm border border-slate-700">
                <span class="flex items-center justify-center w-6 h-6 rounded-full bg-red-500/20 text-red-400">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </span>
                {{ flashError }}
            </div>
        </div>
 
        <TopNav :user="user" />

        <main class="flex-grow w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <slot />
        </main>

        <AppFooter />

    </div>
</template>
