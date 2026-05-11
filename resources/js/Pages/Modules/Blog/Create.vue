<!-- KHỐI 1: XỬ LÝ LAYOUT ĐỘNG (Dynamic Layout) -->
<script>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Layout from '@/Layouts/Layout.vue'; // Layout mặc định cho Nhân sự

export default {
    layout: (h, page) => {
        // Lấy cờ isAdmin từ Middleware HandleInertiaRequests
        const isAdmin = page.props.auth.user?.isAdmin;
        // Nếu là Admin thì dùng AdminLayout, ngược lại dùng Layout mặc định
        return h(isAdmin ? AdminLayout : Layout, () => h(page));
    }
}
</script>

<!-- KHỐI 2: LOGIC COMPONENT BÌNH THƯỜNG -->
<script setup>
import { useForm, Head } from '@inertiajs/vue3';
import TiptapEditor from '@/Components/UI/TiptapEditor.vue';

const form = useForm({
    tieu_de: '',
    loai_bai_viet: 'tin_tuc',
    noi_dung: '',
    anh_bia: null,
});

const submit = () => {
    form.post(route('admin.blog.store'));
};
</script>

<template>
    <Head title="Soạn bài viết mới" />

    <div class="min-h-[calc(100vh-64px)] bg-[#F8FAFC] py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-black text-slate-900 tracking-tight">Soạn bài viết mới</h1>
                    <p class="text-sm font-medium text-slate-500 mt-1">Chia sẻ thông báo, sự kiện đến toàn thể nhân sự.</p>
                </div>
            </div>

            <form @submit.prevent="submit" class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">

                <div class="p-6 sm:p-8 space-y-6">

                    <div class="space-y-2 pb-6 border-b border-slate-100">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide">Ảnh bìa bài viết</label>
                        <input type="file" accept="image/*" @input="form.anh_bia = $event.target.files[0]"
                            class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200 file:cursor-pointer file:transition-colors cursor-pointer border border-slate-200 rounded-lg p-1"/>
                        <p v-if="form.errors.anh_bia" class="text-red-500 text-xs font-medium mt-1">{{ form.errors.anh_bia }}</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="md:col-span-2 space-y-2">
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide">Tiêu đề bài viết <span class="text-red-500">*</span></label>
                            <input v-model="form.tieu_de" type="text" placeholder="Nhập tiêu đề ngắn gọn, súc tích..."
                                class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-1 focus:ring-slate-900 focus:border-slate-900 transition-all placeholder:text-slate-400" required>
                            <p v-if="form.errors.tieu_de" class="text-red-500 text-xs font-medium">{{ form.errors.tieu_de }}</p>
                        </div>

                        <div class="md:col-span-1 space-y-2">
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide">Chuyên mục <span class="text-red-500">*</span></label>
                            <select v-model="form.loai_bai_viet"
                                class="w-full px-3 py-2 text-sm font-medium border border-slate-300 rounded-lg focus:ring-1 focus:ring-slate-900 focus:border-slate-900 transition-all bg-white cursor-pointer">
                                <option value="tin_tuc">Tin tức nội bộ</option>
                                <option value="su_kien">Sự kiện công ty</option>
                                <option value="noi_quy">Nội quy & Quy định</option>
                            </select>
                            <p v-if="form.errors.loai_bai_viet" class="text-red-500 text-xs font-medium">{{ form.errors.loai_bai_viet }}</p>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide">Nội dung chi tiết <span class="text-red-500">*</span></label>
                        <TiptapEditor v-model="form.noi_dung" />
                        <p v-if="form.errors.noi_dung" class="text-red-500 text-xs font-medium mt-1">{{ form.errors.noi_dung }}</p>
                    </div>
                </div>

                <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-3">
                    <button type="button" @click="window.history.back()"
                        class="px-4 py-2 text-sm font-bold text-slate-600 bg-white border border-slate-300 hover:bg-slate-50 hover:text-slate-900 rounded-lg transition-colors">
                        Hủy bỏ
                    </button>

                    <button type="submit" :disabled="form.processing"
                        class="px-6 py-2 text-sm font-bold text-white bg-slate-900 hover:bg-slate-800 rounded-lg shadow-sm transition-colors flex items-center gap-2 disabled:opacity-70 disabled:cursor-not-allowed">
                        <span v-if="form.processing">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            Đang xuất bản...
                        </span>
                        <span v-else>Xuất bản bài viết</span>
                    </button>
                </div>

            </form>
        </div>
    </div>
</template>
