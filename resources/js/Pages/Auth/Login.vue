<script setup>
import { useForm } from "@inertiajs/vue3";
import TextInput from "@/Components/TextInput.vue"; // ⚠️ Lỗi trắng trang thường do quên dòng này

const form = useForm({
    email: null,
    password: null,
    remember: null,
});

const submit = () => {
    form.post(route("login.store"), {
        onError: () => form.reset("password"),
    });
};
</script>

<template>
    <Head title="Login" />

    <h1 class="title">Đăng nhập</h1>

    <div class="w-2/4 mx-auto">
        <form @submit.prevent="submit">

            <TextInput
                name="Email"
                type="email"
                v-model="form.email"
                :message="form.errors.email"
            />

            <TextInput
                name="Mật khẩu"
                type="password"
                v-model="form.password"
                :message="form.errors.password"
            />

            <div class="flex items-center justify-between mb-2">
                <div class="flex items-center gap-2">
                    <input type="checkbox" v-model="form.remember" id="remember" />
                    <label for="remember">Ghi nhớ đăng nhập</label>
                </div>
            </div>

            <div>
                <button class="primary-btn" :disabled="form.processing">Đăng nhập</button>
            </div>

        </form>
    </div>
</template>
