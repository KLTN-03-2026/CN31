<script setup>
import { onMounted, ref } from 'vue';

const model = defineModel({
    type: [String, Number, null],
    required: true,
});

const props = defineProps({
    label: { type: String, required: true },
    type: { type: String, default: 'text' },
    message: String,
    placeholder: { type: String, default: '' },
    id: { type: String, default: () => `input-${Math.random().toString(36).substr(2, 9)}` } 
});

const input = ref(null);

onMounted(() => {
    if (input.value && input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});
</script>

<template>
    <div class="mb-5">
        <label :for="id" class="block text-[11px] font-bold text-slate-500 uppercase tracking-widest mb-2">
            {{ label }}
        </label>
        <div class="relative">
            <input
                :id="id"
                :type="type"
                v-model="model"
                :placeholder="placeholder"
                ref="input"
                v-bind="$attrs"
                class="w-full border-slate-200 focus:border-blue-600 focus:ring focus:ring-blue-600/20 rounded-xl shadow-sm text-sm transition-all bg-slate-50 focus:bg-white py-3 px-4 text-slate-800 font-medium placeholder:text-slate-400"
                :class="{'!border-red-500 focus:!ring-red-500/20 !bg-red-50 text-red-900': message}"
            />
        </div>
        <p class="text-red-500 text-xs mt-1.5 font-bold flex items-center gap-1 animate-fade-in" v-if="message">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4 shrink-0"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-5a.75.75 0 01.75.75v4.5a.75.75 0 01-1.5 0v-4.5A.75.75 0 0110 5zm0 10a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" /></svg>
            {{ message }}
        </p>
    </div>
</template>

<style scoped>
.animate-fade-in { animation: fadeIn 0.2s ease-in-out; }
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-2px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
