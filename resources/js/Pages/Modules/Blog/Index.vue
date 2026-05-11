<script setup>
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    baiViets: Object,
    filters: Object,
    isAdmin: Boolean,
});

const activeTab = ref(props.filters.loai || '');

// Lắng nghe khi đổi Tab thì gọi API lấy dữ liệu mới
watch(activeTab, (value) => {
    router.get(route('blog.index'), { loai: value }, { preserveState: true, preserveScroll: true });
});

// Chuyển cấu hình nhãn (Badge) ra object cố định
const getCategoryConfig = (loai) => {
    const dict = {
        'tin_tuc': { label: 'Tin tức', class: 'text-blue-700 bg-blue-50 ring-1 ring-blue-200/50' },
        'su_kien': { label: 'Sự kiện', class: 'text-purple-700 bg-purple-50 ring-1 ring-purple-200/50' },
        'noi_quy': { label: 'Nội quy', class: 'text-orange-700 bg-orange-50 ring-1 ring-orange-200/50' },
    };
    return dict[loai] || { label: 'Khác', class: 'text-slate-700 bg-slate-50 ring-1 ring-slate-200/50' };
};
</script>

<template>
    <Head title="Bảng tin nội bộ" />

    <div class="min-h-[calc(100vh-64px)] bg-[#F8FAFC] py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-end gap-4 mb-8">
                <div>
                    <h1 class="text-3xl font-black text-slate-900 tracking-tight">Bảng tin nội bộ</h1>
                    <p class="text-sm font-medium text-slate-500 mt-1">Cập nhật tin tức, sự kiện và quy định mới nhất từ công ty.</p>
                </div>
              <Link v-if="isAdmin" :href="route('admin.blog.manage')" class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 bg-slate-900 text-white rounded-lg text-sm font-bold hover:bg-slate-800 transition-colors shadow-sm shrink-0">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                Quản lý Bảng tin
              </Link>
            </div>

            <div class="flex space-x-6 border-b border-slate-200 mb-8 overflow-x-auto pb-px custom-scrollbar">
                <button @click="activeTab = ''" :class="activeTab === '' ? 'border-slate-900 text-slate-900' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'" class="pb-3 border-b-2 font-bold text-sm whitespace-nowrap transition-colors">Tất cả</button>
                <button @click="activeTab = 'tin_tuc'" :class="activeTab === 'tin_tuc' ? 'border-slate-900 text-slate-900' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'" class="pb-3 border-b-2 font-bold text-sm whitespace-nowrap transition-colors">Tin tức</button>
                <button @click="activeTab = 'su_kien'" :class="activeTab === 'su_kien' ? 'border-slate-900 text-slate-900' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'" class="pb-3 border-b-2 font-bold text-sm whitespace-nowrap transition-colors">Sự kiện</button>
                <button @click="activeTab = 'noi_quy'" :class="activeTab === 'noi_quy' ? 'border-slate-900 text-slate-900' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'" class="pb-3 border-b-2 font-bold text-sm whitespace-nowrap transition-colors">Nội quy</button>
            </div>

            <!-- LƯỚI BÀI VIẾT (CARD GRID) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                <Link v-for="bai in baiViets?.data" :key="bai.id" :href="route('blog.show', bai.slug)"
                      class="group flex flex-col bg-white rounded-xl border border-slate-200 hover:border-slate-300 hover:shadow-md transition-all overflow-hidden h-full">

                    <div v-if="bai.anh_bia" class="w-full aspect-video overflow-hidden border-b border-slate-100 bg-slate-50 shrink-0 relative">
                        <img :src="bai.anh_bia" :alt="bai.tieu_de" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 ease-out" />
                    </div>

                    <div class="p-4 flex flex-col flex-grow">
                        <div class="flex justify-between items-start mb-3 gap-2">
                            <span :class="getCategoryConfig(bai.loai_bai_viet).class" class="px-2 py-0.5 rounded text-[9px] font-bold uppercase tracking-wider shrink-0">
                                {{ getCategoryConfig(bai.loai_bai_viet).label }}
                            </span>
                            <span class="text-[10px] font-medium text-slate-400 flex items-center gap-1 shrink-0">
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                {{ bai.ngay_dang }}
                            </span>
                        </div>

                        <h3 class="text-base font-bold text-slate-900 group-hover:text-blue-600 transition-colors line-clamp-2 leading-snug mb-2">
                            {{ bai.tieu_de }}
                        </h3>

                        <p v-if="bai.tom_tat" class="text-[13px] text-slate-500 line-clamp-2 leading-relaxed mb-4">
                            {{ bai.tom_tat }}
                        </p>

                        <div class="flex items-center gap-2 pt-3 border-t border-slate-100 mt-auto">
                            <div class="w-6 h-6 rounded-full bg-slate-100 text-slate-600 font-bold flex items-center justify-center text-[10px] border border-slate-200 shrink-0">
                                {{ bai.tac_gia ? bai.tac_gia[0] : '?' }}
                            </div>
                            <span class="text-xs font-medium text-slate-700 truncate">{{ bai.tac_gia }}</span>
                        </div>
                    </div>
                </Link>
            </div>

            <!-- THANH PHÂN TRANG (PAGINATION) -->
            <div class="mt-10 mb-4 flex justify-center w-full" v-if="baiViets && baiViets.total > baiViets.per_page">
                <div class="flex flex-wrap items-center gap-1 bg-white px-2 py-1.5 rounded-xl border border-slate-200 shadow-sm">
                    <template v-for="(link, index) in baiViets.links" :key="index">

                        <Link v-if="link.url"
                              :href="link.url"
                              :preserve-scroll="true"
                              v-html="link.label.replace('Previous', '«').replace('Next', '»')"
                              class="px-3.5 py-2 rounded-lg text-[9px] font-bold transition-all duration-200"
                              :class="link.active
                                  ? 'bg-slate-900 text-white shadow-md'
                                  : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900'"
                        />

                        <span v-else
                              v-html="link.label.replace('Previous', '«').replace('Next', '»')"
                              class="px-3.5 py-2 rounded-lg text-[9px] font-bold text-slate-300 cursor-not-allowed">
                        </span>

                    </template>
                </div>
            </div>

            <!-- TRẠNG THÁI TRỐNG (EMPTY STATE) -->
            <div v-if="!baiViets?.data?.length" class="py-20 bg-white border border-slate-200 border-dashed rounded-xl flex flex-col items-center justify-center text-center">
                <svg class="w-10 h-10 text-slate-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 002-2V9.5a2.5 2.5 0 00-2.5-2.5H15M9 11l3 3L22 4" /></svg>
                <h3 class="text-sm font-bold text-slate-700">Chuyên mục trống</h3>
                <p class="text-xs font-medium text-slate-500 mt-1">Hiện chưa có bài viết nào được đăng tải.</p>
            </div>

        </div>
    </div>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { height: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background-color: #e2e8f0; border-radius: 10px; }
</style>
