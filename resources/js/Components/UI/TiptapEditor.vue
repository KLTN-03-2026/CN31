<script setup>
import { useEditor, EditorContent } from '@tiptap/vue-3'
import StarterKit from '@tiptap/starter-kit'
import Placeholder from '@tiptap/extension-placeholder'
import Image from '@tiptap/extension-image'
import axios from 'axios'
import { watch, onBeforeUnmount } from 'vue'

const props = defineProps({
    modelValue: {
        type: String,
        default: '',
    },
})

const emit = defineEmits(['update:modelValue'])

const editor = useEditor({
    content: props.modelValue,
    extensions: [
        StarterKit,
        Placeholder.configure({
            placeholder: 'Nhập nội dung chi tiết...',
        }),
        Image.configure({
            HTMLAttributes: {
                class: 'rounded-xl shadow-sm border border-slate-200 max-w-full my-6'
            },
        }),
    ],
    editorProps: {
        attributes: {
            class: 'prose prose-sm sm:prose-base max-w-none focus:outline-none min-h-[250px] p-5',
        },
    },
    onUpdate: ({ editor }) => {
        emit('update:modelValue', editor.getHTML())
    },
})

watch(() => props.modelValue, (value) => {
    const isSame = editor.value.getHTML() === value
    if (isSame) return
    editor.value.commands.setContent(value, false)
})

const addImage = () => {
    const input = document.createElement('input');
    input.type = 'file';
    input.accept = 'image/*';
    input.onchange = async () => {
        if (!input.files?.length) return;

        const file = input.files[0];
        const formData = new FormData();
        formData.append('file', file);

        try {
            const { data } = await axios.post('/blog/upload-image', formData, {
                headers: { 'Content-Type': 'multipart/form-data' }
            });

            if (data && data.url) {
                editor.value.chain().focus().setImage({ src: data.url }).run();
            }
        } catch (error) {
            console.error('Lỗi upload ảnh:', error);
            alert('Không thể tải ảnh lên. Vui lòng thử lại.');
        }
    };
    input.click();
}

onBeforeUnmount(() => {
    editor.value?.destroy()
})
</script>

<template>
    <div class="border border-slate-200 rounded-xl overflow-hidden bg-white shadow-sm focus-within:border-slate-400 focus-within:ring-1 focus-within:ring-slate-400 transition-all flex flex-col">

        <div v-if="editor" class="flex flex-wrap items-center gap-1 p-1.5 border-b border-slate-100 bg-slate-50/80 shrink-0">

            <button @click="editor.chain().focus().toggleBold().run()" type="button" title="In đậm"
                :class="{ 'bg-slate-200 text-slate-800': editor.isActive('bold'), 'text-slate-500 hover:bg-slate-200/50 hover:text-slate-700': !editor.isActive('bold') }"
                class="p-1.5 rounded-lg w-8 h-8 flex items-center justify-center transition-all">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 4h8a4 4 0 014 4 4 4 0 01-4 4H6z" /><path stroke-linecap="round" stroke-linejoin="round" d="M6 12h9a4 4 0 014 4 4 4 0 01-4 4H6z" /></svg>
            </button>
            <button @click="editor.chain().focus().toggleItalic().run()" type="button" title="In nghiêng"
                :class="{ 'bg-slate-200 text-slate-800': editor.isActive('italic'), 'text-slate-500 hover:bg-slate-200/50 hover:text-slate-700': !editor.isActive('italic') }"
                class="p-1.5 rounded-lg w-8 h-8 flex items-center justify-center transition-all">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10 20l4-16m4 4l4-8m-8 0l4-8" transform="matrix(1 0 -0.2 1 4 0)"/></svg>
            </button>
            <button @click="editor.chain().focus().toggleStrike().run()" type="button" title="Gạch ngang"
                :class="{ 'bg-slate-200 text-slate-800': editor.isActive('strike'), 'text-slate-500 hover:bg-slate-200/50 hover:text-slate-700': !editor.isActive('strike') }"
                class="p-1.5 rounded-lg w-8 h-8 flex items-center justify-center transition-all">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 12h16M9 4h.01M15 20h.01M8 8a4 4 0 018 0c0 2-3 3-3 3s-3 1-3 3a4 4 0 008 0" /></svg>
            </button>

            <div class="w-px h-4 bg-slate-200 mx-1"></div>

            <button @click="editor.chain().focus().toggleHeading({ level: 2 }).run()" type="button" title="Tiêu đề 1"
                :class="{ 'bg-slate-200 text-slate-800 font-black': editor.isActive('heading', { level: 2 }), 'text-slate-500 hover:bg-slate-200/50 hover:text-slate-700 font-bold': !editor.isActive('heading', { level: 2 }) }"
                class="p-1.5 rounded-lg text-xs px-2 h-8 flex items-center justify-center transition-all">H1</button>
            <button @click="editor.chain().focus().toggleHeading({ level: 3 }).run()" type="button" title="Tiêu đề 2"
                :class="{ 'bg-slate-200 text-slate-800 font-black': editor.isActive('heading', { level: 3 }), 'text-slate-500 hover:bg-slate-200/50 hover:text-slate-700 font-bold': !editor.isActive('heading', { level: 3 }) }"
                class="p-1.5 rounded-lg text-xs px-2 h-8 flex items-center justify-center transition-all">H2</button>

            <div class="w-px h-4 bg-slate-200 mx-1"></div>

            <button @click="editor.chain().focus().toggleBulletList().run()" type="button" title="Danh sách chấm"
                :class="{ 'bg-slate-200 text-slate-800': editor.isActive('bulletList'), 'text-slate-500 hover:bg-slate-200/50 hover:text-slate-700': !editor.isActive('bulletList') }"
                class="p-1.5 rounded-lg w-8 h-8 flex items-center justify-center transition-all">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16M4 6h.01M4 12h.01M4 18h.01" /></svg>
            </button>
            <button @click="editor.chain().focus().toggleOrderedList().run()" type="button" title="Danh sách số"
                :class="{ 'bg-slate-200 text-slate-800': editor.isActive('orderedList'), 'text-slate-500 hover:bg-slate-200/50 hover:text-slate-700': !editor.isActive('orderedList') }"
                class="p-1.5 rounded-lg w-8 h-8 flex items-center justify-center transition-all">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 6h13M7 12h13M7 18h13M3 6h.01M3 12h.01M3 18h.01" /></svg>
            </button>

            <div class="w-px h-4 bg-slate-200 mx-1"></div>

            <button @click="addImage" type="button" title="Chèn hình ảnh"
                class="text-slate-500 hover:bg-slate-200/50 hover:text-slate-700 p-1.5 rounded-lg w-8 h-8 flex items-center justify-center transition-all">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
            </button>

        </div>

        <div class="flex-grow cursor-text bg-white" @click="editor?.commands.focus()">
            <EditorContent :editor="editor" />
        </div>
    </div>
</template>

<style>
.ProseMirror { outline: none !important; }
.ProseMirror p.is-editor-empty:first-child::before {
    color: #94a3b8;
    content: attr(data-placeholder);
    float: left;
    height: 0;
    pointer-events: none;
    font-style: italic;
}
.ProseMirror img {
    height: auto;
    display: block;
    margin-left: auto;
    margin-right: auto;
}
</style>
