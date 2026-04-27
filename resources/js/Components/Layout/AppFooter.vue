<template>
    <footer class="border-t border-slate-200 bg-white mt-auto relative z-10">
        <div class="max-w-7xl mx-auto py-5 px-4 sm:px-6 lg:px-8">

            <div class="flex flex-col md:flex-row items-center justify-between gap-4 md:gap-6">

                <div class="flex flex-col sm:flex-row items-center gap-2 sm:gap-3 text-sm text-slate-500">
                    <div class="flex items-center gap-1.5 text-slate-900 font-black tracking-tight">
                        <span>ProcureFlow</span>
                    </div>

                    <span class="hidden sm:inline-block text-slate-300">|</span>

                    <span class="font-medium text-[13px]">
                        &copy; {{ new Date().getFullYear() }} <span class="hidden sm:inline">Phát triển bởi</span> Khối Công Nghệ Thông Tin.
                    </span>
                </div>

                <div class="flex flex-wrap items-center justify-center gap-4 sm:gap-5 text-[13px] font-semibold text-slate-500">

                    <div class="flex items-center gap-1.5" title="Hệ thống đang hoạt động bình thường">
                        <span class="relative flex h-2 w-2">
                          <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                          <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                        </span>
                        <span class="text-slate-600 hidden sm:inline-block">Hệ thống ổn định</span>
                    </div>

                    <div class="hidden sm:block w-px h-4 bg-slate-200"></div>

                    <a href="mailto:it-support@congty.com" class="hover:text-slate-900 transition-colors flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                        Hỗ trợ (IT Helpdesk)
                    </a>
                    <a href="/docs" target="_blank" class="hover:text-slate-900 transition-colors flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                        Tài liệu HDSD
                    </a>

                    <div class="hidden sm:block w-px h-4 bg-slate-200"></div>

                    <div class="font-mono text-[10px] font-bold bg-slate-100 text-slate-500 px-2 py-1 rounded-md border border-slate-200" title="Phiên bản phát hành">
                        v1.2.0
                    </div>
                </div>

            </div>
        </div>
    </footer>
</template>
