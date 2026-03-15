<script setup>

</script>

<template>
    <Head title="Home" />

    <div class="p-4">
        <h1 v-if="$page.props.auth.user" class="text-2xl font-bold">
            Xin chào, {{ $page.props.auth.user.name }}
        </h1>

        <h1 v-else class="text-2xl font-bold">
            Xin chào Khách!
        </h1>

        <p class="mt-4">Đây là trang chủ.</p>
    </div>
</template>
