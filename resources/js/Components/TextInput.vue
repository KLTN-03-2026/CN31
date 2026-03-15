<script setup>
// 1. Dùng defineModel (Vue 3.4+) để hứng v-model từ cha truyền xuống
// Đây là cách mới cực xịn, thay thế cho cách cũ (props + emit)
const model = defineModel({
    type: null,
    required: true,
});

// 2. Nhận các tham số khác (Label, Type, Error Message)
defineProps({
    name: {
        type: String,
        required: true,
    },
    type: {
        type: String,
        default: 'text', // Mặc định là text, nếu cần password thì truyền vào
    },
    message: String, // Lỗi validation
});
</script>

<template>
    <div class="mb-6">
        <label>{{ name }}</label>
        <input
         :type="type"
         v-model="model"
         />

        <small class="text-red-500" v-if="message">{{ message }}</small>
    </div>
</template>
