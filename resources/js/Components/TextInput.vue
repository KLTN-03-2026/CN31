<script setup>
const model = defineModel({
    type: null,
    required: true,
});

defineProps({
    name: { type: String, required: true },
    type: { type: String, default: 'text' },
    message: String,
    placeholder: { type: String, default: '' }
});
</script>

<template>
    <div class="mb-5">
        <label class="block font-bold text-sm text-gray-700 mb-1.5">{{ name }}</label>
        <input
            :type="type"
            v-model="model"
            :placeholder="placeholder"
            class="w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm text-sm transition-colors"
            :class="{'border-red-500 focus:border-red-500 focus:ring-red-500 bg-red-50': message}"
         />
        <p class="text-red-500 text-xs mt-1.5 font-medium flex items-center gap-1" v-if="message">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-5a.75.75 0 01.75.75v4.5a.75.75 0 01-1.5 0v-4.5A.75.75 0 0110 5zm0 10a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" /></svg>
            {{ message }}
        </p>
    </div>
</template>
