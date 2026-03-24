<script setup>
import { useForm } from "@inertiajs/vue3";
import TextInput from "@/Components/TextInput.vue";

// Khai báo form
const form = useForm({
    name: null,
    email: null,
    password: null,
    password_confirmation: null,
    avatar: null,  // Biến chứa file để gửi lên server
    preview: null, // Biến chứa đường dẫn ảnh ảo để xem trước
});

// Hàm xử lý khi chọn file
const change = (e) => {
    // Lấy file gán vào form
    form.avatar = e.target.files[0];
    // Tạo link ảnh xem trước
    form.preview = URL.createObjectURL(e.target.files[0]);
};

const submit = () => {
    form.post(route("register.store"), {
        onError: () => form.reset("password", "password_confirmation"),
    });
};
</script>

<template>
    <Head title="Đăng ký" />

    <div class="register-wrapper">
        <!-- Background decoration -->
        <div class="register-background"></div>

        <!-- Register card -->
        <div class="register-card">
            <!-- Header -->
            <div class="register-header">
                <h1 class="register-title">Tạo tài khoản</h1>
                <p class="register-subtitle">Tham gia cộng đồng của chúng tôi</p>
            </div>

            <!-- Form -->
            <form @submit.prevent="submit" class="register-form">
                <!-- Avatar Section -->
                <div class="avatar-section">
                    <div class="avatar-container">
                        <input
                            type="file"
                            id="avatar"
                            @input="change"
                            hidden
                            accept="image/*"
                            class="avatar-input"
                        />
                        <label for="avatar" class="avatar-label">
                            <img
                                class="avatar-image"
                                :src="form.preview ?? 'storage/avatars/default.png'"
                                alt="Avatar Preview"
                            />
                            <div class="avatar-overlay">
                                <span class="avatar-icon">📷</span>
                            </div>
                        </label>
                    </div>
                    <p class="avatar-text">Chọn ảnh đại diện</p>
                    <p v-if="form.errors.avatar" class="error-message">{{ form.errors.avatar }}</p>
                </div>

                <!-- Name field -->
                <div class="form-field">

                    <TextInput
                        id="name"
                        name="Họ tên"
                        v-model="form.name"
                        :message="form.errors.name"
                        class="w-full"
                    />
                </div>

                <!-- Email field -->
                <div class="form-field">

                    <TextInput
                        id="email"
                        name="Email"
                        type="email"
                        v-model="form.email"
                        :message="form.errors.email"
                        class="w-full"
                    />
                </div>

                <!-- Password field -->
                <div class="form-field">

                    <TextInput
                        id="password"
                        name="Mật khẩu"
                        type="password"
                        v-model="form.password"
                        :message="form.errors.password"
                        class="w-full"
                    />
                </div>

                <!-- Confirm Password field -->
                <div class="form-field">
                    <TextInput
                        id="password_confirmation"
                        name="Nhập lại mật khẩu"
                        type="password"
                        v-model="form.password_confirmation"
                        :message="form.errors.password_confirmation"
                        class="w-full"
                    />
                </div>

                <!-- Submit button -->
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="submit-btn"
                >
                    <span v-if="!form.processing">Đăng ký ngay</span>
                    <span v-else class="flex items-center gap-2">
                        <span class="inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                        Đang xử lý
                    </span>
                </button>
            </form>

            <!-- Footer -->
            <div class="register-footer">
                <p class="footer-text">
                    Đã có tài khoản?
                    <a href="/login" class="footer-link">Đăng nhập ngay</a>
                </p>
            </div>
        </div>
    </div>
</template>

<style scoped>
.register-wrapper {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, hsl(240, 100%, 97%) 0%, hsl(250, 95%, 94%) 100%);
    padding: 1rem;
    position: relative;
    overflow: hidden;
}

.register-background {
    position: absolute;
    top: -40%;
    right: -10%;
    width: 500px;
    height: 500px;
    background: radial-gradient(circle, rgba(139, 92, 246, 0.1) 0%, rgba(139, 92, 246, 0) 70%);
    border-radius: 50%;
    pointer-events: none;
}

.register-card {
    background: white;
    border-radius: 1.5rem;
    padding: 3rem 2.5rem;
    max-width: 480px;
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

.register-header {
    text-align: center;
    margin-bottom: 2rem;
}

.register-title {
    font-size: 1.75rem;
    font-weight: 700;
    color: hsl(220, 13%, 13%);
    margin: 0;
    letter-spacing: -0.02em;
}

.register-subtitle {
    font-size: 0.95rem;
    color: hsl(220, 8%, 50%);
    margin: 0.5rem 0 0 0;
    font-weight: 400;
}

.register-form {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.avatar-section {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.75rem;
    padding: 1.5rem;
    background: hsl(240, 100%, 97%);
    border-radius: 1rem;
}

.avatar-container {
    position: relative;
}

.avatar-input {
    display: none;
}

.avatar-label {
    position: relative;
    display: block;
    width: 7rem;
    height: 7rem;
    cursor: pointer;
    border-radius: 50%;
    overflow: hidden;
    transition: transform 0.3s ease;
}

.avatar-label:hover {
    transform: scale(1.05);
}

.avatar-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

.avatar-overlay {
    position: absolute;
    inset: 0;
    background: rgba(0, 0, 0, 0);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.75rem;
    opacity: 0;
    transition: all 0.3s ease;
}

.avatar-label:hover .avatar-overlay {
    background: rgba(0, 0, 0, 0.3);
    opacity: 1;
}

.avatar-text {
    font-size: 0.875rem;
    color: hsl(220, 8%, 45%);
    margin: 0;
    font-weight: 500;
}

.error-message {
    font-size: 0.8125rem;
    color: hsl(0, 84%, 60%);
    margin: 0;
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

.register-footer {
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
    .register-wrapper {
        padding: 1.5rem;
    }

    .register-card {
        padding: 2rem 1.5rem;
    }

    .register-title {
        font-size: 1.5rem;
    }

    .register-subtitle {
        font-size: 0.875rem;
    }

    .avatar-label {
        width: 6rem;
        height: 6rem;
    }

    .avatar-icon {
        font-size: 1.25rem;
    }

    .submit-btn {
        font-size: 0.9375rem;
        padding: 0.75rem 1.25rem;
    }
}
</style>
