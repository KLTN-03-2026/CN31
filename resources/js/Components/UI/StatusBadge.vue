<script setup>
defineProps({
    label: { type: String, default: '' },
    color: { type: String, default: 'gray' }
});

const dotColors = {
    gray: 'bg-slate-400',
    yellow: 'bg-amber-400',
    orange: 'bg-orange-400',
    blue: 'bg-blue-500',
    green: 'bg-emerald-400',
    red: 'bg-red-400',
    purple: 'bg-purple-400'
};
</script>

<template>
    <span class="inline-flex items-center justify-center gap-1.5 min-w-[130px] px-3 py-1.5 rounded-md bg-slate-50 border border-slate-200 text-[11px] font-semibold text-slate-700 uppercase tracking-wider shadow-sm transition-all hover:shadow">
        <span class="w-1.5 h-1.5 rounded-full shrink-0" :class="dotColors[color] || dotColors.gray"></span>

        <span class="truncate">
            <slot>{{ label }}</slot>
        </span>
    </span>
</template>
