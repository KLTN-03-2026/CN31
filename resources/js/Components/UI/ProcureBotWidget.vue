<script setup>
import { ref, nextTick, onMounted } from 'vue'; // Bổ sung onMounted
import axios from 'axios';

const isOpen = ref(false);
const isLoading = ref(false);
const newMessage = ref('');
const chatBox = ref(null);
const inputField = ref(null);

const messages = ref([]);

const scrollToBottom = async () => {
    await nextTick();
    if (chatBox.value) {
        chatBox.value.scrollTop = chatBox.value.scrollHeight;
    }
};

//  TẢI LỊCH SỬ CHAT TỪ DATABASE
const fetchHistory = async () => {
    try {
        const response = await axios.get('/ai/history');
        if (response.data.success && response.data.history.length > 0) {
            // Map dữ liệu từ DB (role: user/assistant) sang định dạng của Vue (sender: user/bot)
            messages.value = response.data.history.map(msg => ({
                sender: msg.role === 'user' ? 'user' : 'bot',
                text: msg.content
            }));
        } else {
            // Nếu chưa có lịch sử nào (User mới), hiển thị câu chào mặc định
            messages.value = [
                { sender: 'bot', text: 'Xin chào! Tôi là Trợ lý AI của hệ thống. Tôi có thể giúp bạn tra cứu mã phiếu mua sắm hoặc kiểm tra ngân sách. Bạn cần hỗ trợ gì?' }
            ];
        }
        scrollToBottom();
    } catch (error) {
        console.error("Lỗi lấy lịch sử AI:", error);
        // Fallback: Nếu lỗi mạng, vẫn hiện câu chào
        messages.value = [
            { sender: 'bot', text: 'Xin chào! Tôi là Trợ lý AI của hệ thống. Tôi có thể giúp bạn tra cứu mã phiếu mua sắm. Bạn cần hỗ trợ gì?' }
        ];
    }
};

// Tự động chạy hàm lấy lịch sử khi Component vừa được khởi tạo
onMounted(() => {
    fetchHistory();
});

const toggleChat = async () => {
    isOpen.value = !isOpen.value;
    if (isOpen.value) {
        await nextTick();
        inputField.value?.focus();
        scrollToBottom();
    }
};

const sendMessage = async () => {
    const text = newMessage.value.trim();
    if (!text) return;

    messages.value.push({ sender: 'user', text: text });
    newMessage.value = '';
    isLoading.value = true;
    scrollToBottom();

    inputField.value?.focus();

    try {
        const response = await axios.post('/ai/chat', { message: text });
        if (response.data.success) {
            messages.value.push({ sender: 'bot', text: response.data.reply });
        } else {
            messages.value.push({ sender: 'bot', text: response.data.reply || 'Hệ thống AI đang bận, vui lòng thử lại sau.' });
        }
    } catch (error) {
        messages.value.push({ sender: 'bot', text: 'Mất kết nối đến máy chủ. Vui lòng kiểm tra mạng.' });
    } finally {
        isLoading.value = false;
        scrollToBottom();
    }
};
</script>

<template>
    <div class="fixed bottom-6 right-6 z-[100]">

        <transition
            enter-active-class="transition ease-out duration-200 origin-bottom-right"
            enter-from-class="opacity-0 scale-95 translate-y-2"
            enter-to-class="opacity-100 scale-100 translate-y-0"
            leave-active-class="transition ease-in duration-150 origin-bottom-right"
            leave-from-class="opacity-100 scale-100 translate-y-0"
            leave-to-class="opacity-0 scale-95 translate-y-2"
        >
            <div v-if="isOpen" class="bg-white w-[340px] sm:w-[380px] h-[500px] max-h-[80vh] shadow-xl rounded-xl flex flex-col mb-4 border border-slate-200 overflow-hidden">

                <div class="bg-slate-900 text-white px-4 py-3 flex justify-between items-center shrink-0">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-white rounded-lg flex items-center justify-center shadow-sm shrink-0 overflow-hidden">
                             <img
                                src="https://cdn.jsdelivr.net/gh/glincker/thesvg@main/public/icons/azure-bot-services/default.svg"
                                alt="Bot Services"
                                class="h-5 w-5 object-contain"
                            />
                        </div>
                        <div>
                            <h3 class="font-bold text-sm leading-tight">Trợ lý AI MrGiotTech</h3>
                            <p class="text-[11px] text-slate-400 font-medium">Trực tuyến</p>
                        </div>
                    </div>
                    <button @click="toggleChat" class="text-slate-400 hover:text-white p-1 transition-colors">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <div class="flex-1 p-4 overflow-y-auto bg-white custom-scrollbar flex flex-col gap-4" ref="chatBox">
                    <div v-for="(msg, index) in messages" :key="index" class="flex" :class="msg.sender === 'user' ? 'justify-end' : 'justify-start'">

                        <div v-if="msg.sender === 'bot'" class="w-7 h-7 rounded-full bg-slate-50 border border-slate-200 flex items-center justify-center shrink-0 mr-2 mt-0.5 overflow-hidden">
                            <img
                                src="https://cdn.jsdelivr.net/gh/glincker/thesvg@main/public/icons/azure-bot-services/default.svg"
                                alt="Bot Avatar"
                                class="h-4 w-4 object-contain"
                            />
                        </div>

                        <div class="max-w-[85%] px-3.5 py-2.5 text-[13px] leading-relaxed"
                             :class="msg.sender === 'user' ? 'bg-blue-600 text-white rounded-xl rounded-tr-sm shadow-sm' : 'bg-slate-100 text-slate-800 rounded-xl rounded-tl-sm'">
                           <span class="whitespace-pre-wrap" v-html="msg.text"></span>
                        </div>
                    </div>

                    <div v-if="isLoading" class="flex justify-start">
                        <div class="w-7 h-7 rounded-full bg-slate-50 border border-slate-200 flex items-center justify-center shrink-0 mr-2 mt-0.5 overflow-hidden">
                            <img
                                src="https://cdn.jsdelivr.net/gh/glincker/thesvg@main/public/icons/azure-bot-services/default.svg"
                                alt="Bot Avatar"
                                class="h-4 w-4 object-contain"
                            />
                        </div>
                        <div class="bg-slate-100 rounded-xl rounded-tl-sm px-3.5 py-3.5 flex items-center gap-1 h-[36px]">
                            <div class="w-1.5 h-1.5 bg-slate-400 rounded-full animate-bounce"></div>
                            <div class="w-1.5 h-1.5 bg-slate-400 rounded-full animate-bounce" style="animation-delay: 0.15s"></div>
                            <div class="w-1.5 h-1.5 bg-slate-400 rounded-full animate-bounce" style="animation-delay: 0.3s"></div>
                        </div>
                    </div>
                </div>

                <div class="p-3 bg-white border-t border-slate-100 flex items-end gap-2 shrink-0">
                    <textarea
                        ref="inputField"
                        v-model="newMessage"
                        @keyup.enter.prevent="sendMessage"
                        rows="1"
                        placeholder="Nhập tin nhắn..."
                        class="flex-1 bg-slate-50 border border-slate-200 rounded-lg px-3 py-2.5 text-[13px] focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all outline-none resize-none max-h-24 custom-scrollbar placeholder:text-slate-400"
                        :disabled="isLoading"
                    ></textarea>

                    <button
                        @click="sendMessage"
                        :disabled="!newMessage.trim() || isLoading"
                        class="w-10 h-10 shrink-0 flex items-center justify-center rounded-lg transition-colors text-blue-600 hover:bg-blue-50 disabled:opacity-30 disabled:hover:bg-transparent disabled:cursor-not-allowed mb-0.5"
                    >
                        <svg class="w-5 h-5 transform rotate-90" fill="currentColor" viewBox="0 0 20 20"><path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z" /></svg>
                    </button>
                </div>
            </div>
        </transition>

        <div v-show="!isOpen" @click="toggleChat" class="flex flex-col items-center gap-1.5 group cursor-pointer animate-float-widget hover:animate-none" title="Mở chat hỗ trợ">

            <div class="relative">
                <button class="w-14 h-14 bg-white rounded-full shadow-lg border border-slate-100 flex items-center justify-center group-hover:shadow-xl group-hover:scale-115 transition-all duration-300 focus:outline-none">
                    <img
                        src="https://cdn.jsdelivr.net/gh/glincker/thesvg@main/public/icons/openchat/color.svg"
                        alt="OpenChat"
                        class="h-8 w-8 object-contain"
                    />
                </button>

                <span class="absolute top-0 right-0 flex h-3.5 w-3.5">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3.5 w-3.5 bg-emerald-500 border-2 border-white"></span>
                </span>
            </div>

            <span class="text-[11px] font-bold text-slate-600 bg-white/90 backdrop-blur-sm px-3 py-1 rounded-full shadow-sm border border-slate-100 group-hover:text-blue-600 transition-colors">
                Hỗ trợ
            </span>
        </div>

    </div>
</template>

<style scoped>
/* Thanh cuộn nhỏ gọn */
.custom-scrollbar::-webkit-scrollbar { width: 5px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

/* Hiệu ứng lơ lửng (Floating) cho widget */
.animate-float-widget {
    animation: floatWidget 3s ease-in-out infinite;
}

@keyframes floatWidget {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-10px); }
}
</style>
