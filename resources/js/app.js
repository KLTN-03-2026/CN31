import './bootstrap'
import '../css/app.css'
import { ZiggyVue } from '../../vendor/tightenco/ziggy';

import { createApp, h } from 'vue'
import { createInertiaApp, Link, Head } from '@inertiajs/vue3'

import Layout from './Layouts/Layout.vue';

createInertiaApp({
    progress: {

        color: '#fff',
        // Có hiện cái vòng xoay xoay ở góc trên bên phải không?
        // true = Hiện | false = Ẩn
        showSpinner: true,
        // Mặc định là true. Nếu false thì bạn phải tự viết CSS cho thanh loading
        includeCSS: true,
        // (Mở rộng) Delay: Thời gian chờ tối thiểu trước khi hiện thanh loading (mặc định 250ms)
         //delay: 250,
    },
    title: (title) => title ? `${title} | Procureflow` : 'My App',
    resolve: name => {
        const pages = import.meta.glob('./Pages/**/*.vue', { eager: true })
        let page = pages[`./Pages/${name}.vue`]

        // Logic: Nếu trang đó không có layout riêng -> Dùng Layout mặc định
        page.default.layout = page.default.layout || Layout

        return page
    },
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .component('Link', Link) // đăng ký component Link
            .component('Head', Head) // đăng ký component Head
            .use(ZiggyVue)   // plugin ZiggyVue
            .mount(el) // gắn app vào thẻ el
    },
})
