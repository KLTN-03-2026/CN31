<script setup>
defineProps({
    title: { type: String, required: true },
    value: { type: [Number, String], default: 0 },
    colorType: { type: String, default: 'slate' }
});
</script>
<template>
    <div class="bg-white rounded-xl px-4 py-3.5 shadow-sm border border-slate-200 hover:shadow-md hover:border-slate-300 hover:-translate-y-0.5 transition-all duration-300 flex flex-col justify-center cursor-default">
        <div class="flex items-center gap-2 mb-1.5">
            <span class="w-2 h-2 rounded-full shadow-sm" :class="`bg-${colorType}-500`"></span>
            <span class="text-slate-500 text-[10px] font-bold uppercase tracking-wider">{{ title }}</span>
        </div>
        <div class="text-2xl font-black tracking-tight" :class="colorType === 'slate' ? 'text-slate-800' : `text-${colorType}-600`">
            {{ value }}
        </div>
    </div>
</template>
