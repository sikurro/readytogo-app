<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import Toast from '@/Components/Toast.vue';

defineProps({
    title: String,
});

const isSidebarOpen = ref(true);

const logout = () => {
    router.post(route('logout'));
};
</script>

<template>
    <div class="min-h-screen bg-slate-900 text-slate-100 flex font-sans selection:bg-amber-500 selection:text-slate-900">
        <!-- Sidebar -->
        <aside :class="[isSidebarOpen ? 'w-64' : 'w-20', 'bg-slate-950 border-r border-slate-800 transition-all duration-300 flex flex-col z-40 relative']">
            <!-- Sidebar Header -->
            <div class="h-16 flex items-center justify-between px-4 border-b border-slate-800">
                <div class="flex items-center gap-2" v-if="isSidebarOpen">
                    <span class="bg-gradient-to-r from-amber-500 to-orange-500 text-slate-950 font-black px-2.5 py-1 rounded-md text-sm tracking-wider">R2G</span>
                    <span class="font-bold text-sm text-slate-100 tracking-tight">Admin Panel</span>
                </div>
                <div v-else class="mx-auto">
                    <span class="bg-gradient-to-r from-amber-500 to-orange-500 text-slate-950 font-black px-2 py-1 rounded-md text-xs tracking-wider">R2G</span>
                </div>
                <button @click="isSidebarOpen = !isSidebarOpen" class="p-1 hover:bg-slate-800 rounded-lg text-slate-400 hover:text-slate-100">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                </button>
            </div>

            <!-- Navigation Links -->
            <nav class="flex-1 py-4 px-3 space-y-1">
                <!-- Dashboard -->
                <Link :href="route('admin.dashboard')" :class="[route().current('admin.dashboard') ? 'bg-blue-500/10 text-blue-500 border-l-4 border-blue-500' : 'text-slate-400 hover:bg-slate-800/50 hover:text-slate-100', 'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors']">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                    </svg>
                    <span v-if="isSidebarOpen">Dashboard</span>
                </Link>

                <!-- Bank Soal -->
                <Link :href="route('admin.questions.index')" :class="[route().current('admin.questions.*') ? 'bg-amber-500/10 text-amber-500 border-l-4 border-amber-500' : 'text-slate-400 hover:bg-slate-800/50 hover:text-slate-100', 'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors']">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0 0 12 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75Z" />
                    </svg>
                    <span v-if="isSidebarOpen">Bank Soal</span>
                </Link>

                <!-- Quizzes Management -->
                <Link :href="route('admin.quizzes.index')" :class="[route().current('admin.quizzes.*') ? 'bg-amber-500/10 text-amber-500 border-l-4 border-amber-500' : 'text-slate-400 hover:bg-slate-800/50 hover:text-slate-100', 'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors']">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 21l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                    </svg>
                    <span v-if="isSidebarOpen">Kelola Kuis</span>
                </Link>

                <!-- Quizzes History -->
                <Link :href="route('admin.quiz.history')" :class="[route().current('admin.quiz.history') ? 'bg-amber-500/10 text-amber-500 border-l-4 border-amber-500' : 'text-slate-400 hover:bg-slate-800/50 hover:text-slate-100', 'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors']">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.03 0 1.9.693 2.166 1.638m-7.377 0A48.536 48.536 0 0 1 12 3m0 0c-1.135.094-1.976 1.057-1.976 2.192V16.5A2.25 2.25 0 0 0 12 18.75h.007v.008H12v-.008Z" />
                    </svg>
                    <span v-if="isSidebarOpen">Riwayat Kuis</span>
                </Link>

                <!-- Manajemen User -->
                <Link :href="route('admin.users.index')" :class="[route().current('admin.users.*') ? 'bg-amber-500/10 text-amber-500 border-l-4 border-amber-500' : 'text-slate-400 hover:bg-slate-800/50 hover:text-slate-100', 'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors']">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.109A2.25 2.25 0 0112.75 21.5h-1.5a2.25 2.25 0 01-2.25-2.263V19.13m-2.625.372A9.336 9.336 0 011.5 18.553a4.125 4.125 0 017.533-2.493m0 0a9.07 9.07 0 013.217-3.185M9.813 15.904L9 21m8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M12 12.75a3.75 3.75 0 100-7.5 3.75 3.75 0 000 7.5z" />
                    </svg>
                    <span v-if="isSidebarOpen">Manajemen User</span>
                </Link>

                <!-- Incidents Reporting -->
                <Link :href="route('admin.dashboard')" class="text-slate-400 hover:bg-slate-800/50 hover:text-slate-100 flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                    </svg>
                    <span v-if="isSidebarOpen">Laporan Insiden</span>
                </Link>

                <!-- Fatigue Reports -->
                <Link :href="route('admin.dashboard')" class="text-slate-400 hover:bg-slate-800/50 hover:text-slate-100 flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0110 21a3.745 3.745 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0114 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z" />
                    </svg>
                    <span v-if="isSidebarOpen">Laporan Kelelahan</span>
                </Link>
            </nav>

            <!-- Sidebar Footer / Profile -->
            <div class="p-4 border-t border-slate-800 bg-slate-950/80">
                <div class="flex items-center gap-3" v-if="isSidebarOpen">
                    <div class="h-9 w-9 rounded-full bg-slate-800 border border-slate-700 flex items-center justify-center font-bold text-amber-500 uppercase">
                        {{ $page.props.auth.user.name.charAt(0) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-bold text-slate-200 truncate">{{ $page.props.auth.user.name }}</p>
                        <p class="text-[10px] text-amber-500 font-medium tracking-wide uppercase truncate">Administrator</p>
                    </div>
                    <button @click="logout" class="text-slate-400 hover:text-red-400 p-1 rounded-lg hover:bg-slate-800">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                        </svg>
                    </button>
                </div>
                <div v-else class="flex justify-center">
                    <button @click="logout" class="text-slate-400 hover:text-red-400 p-2 rounded-lg hover:bg-slate-800" title="Logout">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                        </svg>
                    </button>
                </div>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            <!-- Header bar -->
            <header class="h-16 bg-slate-900 border-b border-slate-800 flex items-center justify-between px-6 z-30">
                <h2 class="text-base font-bold tracking-tight text-slate-100 flex items-center gap-2">
                    <slot name="header" />
                </h2>
                
                <div class="flex items-center gap-4">
                    <span class="text-xs text-slate-400">Status Database: <span class="text-emerald-400 font-semibold">Online</span></span>
                </div>
            </header>

            <!-- Slot -->
            <main class="flex-1 overflow-y-auto p-6 bg-slate-950/20">
                <div class="max-w-7xl mx-auto">
                    <slot />
                </div>
            </main>
        </div>
        <Toast />
    </div>
</template>
