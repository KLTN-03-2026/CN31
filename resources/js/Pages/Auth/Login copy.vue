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

    <Head title="Đăng nhập" />

    <div class="login-wrapper">
        <!-- Background decoration -->
        <div class="login-background"></div>

        <!-- Login card -->
        <div class="login-card">
            <!-- Header -->
            <div class="login-header">
                <h1 class="login-title">Đăng nhập</h1>
                <p class="login-subtitle">Tiếp tục vào ứng dụng của bạn</p>
            </div>

            <!-- Form -->
            <form @submit.prevent="submit" class="login-form">
                <!-- Email field -->
                <div class="form-field">
                    <TextInput id="email" name="Email" type="email" v-model="form.email" :message="form.errors.email"
                        class="w-full" />
                </div>

                <!-- Password field -->
                <div class="form-field">
                    <TextInput id="password" name="Mật khẩu" type="password" v-model="form.password"
                        :message="form.errors.password" class="w-full" />
                </div>

                <!-- Remember checkbox -->
                <div class="remember-wrapper">
                    <input type="checkbox" v-model="form.remember" id="remember" class="remember-checkbox" />
                    <label for="remember" class="remember-label">Ghi nhớ đăng nhập lần tới</label>
                </div>

                <!-- Submit button -->
                <button type="submit" :disabled="form.processing" class="submit-btn">
                    <span v-if="!form.processing">Đăng nhập</span>
                    <span v-else class="flex items-center gap-2">
                        <span
                            class="inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                        Đang xử lý
                    </span>
                </button>
            </form>

            <!-- Footer -->
            <div class="login-footer">
                <p class="footer-text">
                    Chưa có tài khoản?
                    <a href="/register" class="footer-link">Đăng ký ngay</a>
                </p>
            </div>
        </div>
    </div>
</template>

<style scoped>
.login-wrapper {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, hsl(240, 100%, 97%) 0%, hsl(250, 95%, 94%) 100%);
    padding: 1rem;
    position: relative;
    overflow: hidden;
}

.login-background {
    position: absolute;
    top: -40%;
    right: -10%;
    width: 500px;
    height: 500px;
    background: radial-gradient(circle, rgba(139, 92, 246, 0.1) 0%, rgba(139, 92, 246, 0) 70%);
    border-radius: 50%;
    pointer-events: none;
}

.login-card {
    background: white;
    border-radius: 1.5rem;
    padding: 3rem 2.5rem;
    max-width: 420px;
    width: 100%;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.08);
    animation: slideInUp 0.5s ease-out;
    position: relative;
    z-index: 10;
}

@keyframes slideInUp {
    from {
        opacity: 0;
        transform: translateY(2rem);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.login-header {
    text-align: center;
    margin-bottom: 2rem;
}

.login-title {
    font-size: 1.75rem;
    font-weight: 700;
    color: hsl(220, 13%, 13%);
    margin: 0;
    letter-spacing: -0.02em;
}

.login-subtitle {
    font-size: 0.95rem;
    color: hsl(220, 8%, 50%);
    margin: 0.5rem 0 0 0;
    font-weight: 400;
}

.login-form {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}

.form-field {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.form-label {
    font-size: 0.875rem;
    font-weight: 600;
    color: hsl(220, 13%, 26%);
    text-transform: uppercase;
    letter-spacing: 0.03em;
}

.remember-wrapper {
    display: flex;
    align-items: center;
    gap: 0.625rem;
    padding: 0.5rem 0;
}

.remember-checkbox {
    width: 1.25rem;
    height: 1.25rem;
    cursor: pointer;
    accent-color: hsl(270, 100%, 60%);
    border-radius: 0.375rem;
}

.remember-label {
    font-size: 0.9375rem;
    color: hsl(220, 8%, 45%);
    cursor: pointer;
    font-weight: 500;
    user-select: none;
}

.submit-btn {
    background: linear-gradient(135deg, hsl(270, 100%, 60%) 0%, hsl(280, 85%, 55%) 100%);
    color: white;
    border: none;
    border-radius: 0.875rem;
    padding: 0.875rem 1.5rem;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 4px 16px rgba(139, 92, 246, 0.3);
    margin-top: 0.5rem;
}

.submit-btn:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(139, 92, 246, 0.4);
}

.submit-btn:active:not(:disabled) {
    transform: translateY(0);
}

.submit-btn:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}

.login-footer {
    margin-top: 1.5rem;
    padding-top: 1.5rem;
    border-top: 1px solid hsl(220, 13%, 91%);
    text-align: center;
}

.footer-text {
    font-size: 0.9375rem;
    color: hsl(220, 8%, 50%);
    margin: 0;
}

.footer-link {
    color: hsl(270, 100%, 60%);
    text-decoration: none;
    font-weight: 600;
    transition: color 0.3s ease;
}

.footer-link:hover {
    color: hsl(280, 85%, 55%);
}

/* Responsive design */
@media (max-width: 640px) {
    .login-wrapper {
        padding: 1.5rem;
    }

    .login-card {
        padding: 2rem 1.5rem;
    }

    .login-title {
        font-size: 1.5rem;
    }

    .login-subtitle {
        font-size: 0.875rem;
    }

    .submit-btn {
        font-size: 0.9375rem;
        padding: 0.75rem 1.25rem;
    }
}
</style>
