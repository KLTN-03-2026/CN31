<script setup>

const model = defineModel({
    type :null,
    required:true

});

defineProps({
    name:{
        type:String,
        required:true
    },
    type:{
        type:String,
        default:'text'
    },
    Message : String
});

</script>

<template>
    <label> {{ name }}</label>
    <input type="type"
           v-model="model"
    >
    <small>{{ Message }}</small>

</template>
