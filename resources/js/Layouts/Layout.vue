<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import TopNav from '@/Components/Layout/TopNav.vue';
import AppFooter from '@/Components/Layout/AppFooter.vue';

const page = usePage();
const user = computed(() => page.props.auth?.user);
</script>

<template>
    <div class="min-h-screen bg-slate-50 flex flex-col font-sans text-slate-900">

        <TopNav :user="user" />

        <main class="flex-grow w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <slot />
        </main>

        <AppFooter />

    </div>
</template>
