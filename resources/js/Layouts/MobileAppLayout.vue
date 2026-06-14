<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';

defineProps({
    title: String,
});

const showingNavigationDropdown = ref(false);

const logout = () => {
    router.post(route('logout'));
};
</script>

<template>
    <div class="min-h-screen bg-slate-950 text-slate-100 flex flex-col font-sans pb-20 selection:bg-amber-500 selection:text-slate-900">
        <!-- Top App Bar -->
        <header class="sticky top-0 z-50 bg-slate-900/80 backdrop-blur-md border-b border-slate-800 px-4 py-3 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="bg-gradient-to-r from-amber-500 to-orange-500 text-slate-950 font-black px-2.5 py-1 rounded-md text-sm tracking-wider">R2G</span>
                <h1 class="text-base font-bold tracking-tight text-slate-100">Ready To GO</h1>
            </div>
            
            <div class="flex items-center gap-3">
                <div class="text-right hidden sm:block">
                    <p class="text-xs text-slate-400 font-medium">{{ $page.props.auth.user.name }}</p>
                    <p class="text-[10px] text-amber-500 font-bold uppercase tracking-wider">{{ $page.props.auth.user.position }}</p>
                </div>
                <!-- Simple Status Indicator -->
                <span class="flex h-2.5 w-2.5 relative">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                </span>
                
                <!-- Logout Button -->
                <button @click="logout" class="p-1.5 text-slate-400 hover:text-red-400 transition-colors rounded-lg hover:bg-slate-800" title="Logout">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                    </svg>
                </button>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1 max-w-lg mx-auto w-full px-4 py-6">
            <slot />
        </main>

        <!-- Sticky Bottom Navigation Bar (Mobile-First) -->
        <nav class="fixed bottom-0 left-0 right-0 z-50 bg-slate-900/90 backdrop-blur-lg border-t border-slate-800 max-w-lg mx-auto rounded-t-2xl shadow-2xl shadow-amber-500/10">
            <div class="grid grid-cols-4 h-16">
                <!-- Home / Dashboard -->
                <Link :href="route('dashboard')" class="flex flex-col items-center justify-center transition-all group" :class="route().current('dashboard') ? 'text-amber-500' : 'text-slate-400 hover:text-slate-200'">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 group-active:scale-90 transition-transform duration-100">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                    </svg>
                    <span class="text-[10px] font-bold mt-1 tracking-wide uppercase">Home</span>
                </Link>

                <!-- Daily Quiz -->
                <Link :href="route('quiz.index')" class="flex flex-col items-center justify-center transition-all group" :class="route().current('quiz.*') ? 'text-amber-500' : 'text-slate-400 hover:text-slate-200'">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 group-active:scale-90 transition-transform duration-100">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 21l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                    </svg>
                    <span class="text-[10px] font-bold mt-1 tracking-wide uppercase">Kuis</span>
                </Link>

                <!-- Riwayat Fatigue -->
                <Link :href="route('fatigue.history')" class="flex flex-col items-center justify-center transition-all group" :class="route().current('fatigue.history') ? 'text-amber-500' : 'text-slate-400 hover:text-slate-200'">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 group-active:scale-90 transition-transform duration-100">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.03 0 1.9.693 2.166 1.638m-7.377 0A48.536 48.536 0 0 1 12 3m0 0c-1.135.094-1.976 1.057-1.976 2.192V16.5A2.25 2.25 0 0 0 12 18.75h.007v.008H12v-.008Z" />
                    </svg>
                    <span class="text-[10px] font-bold mt-1 tracking-wide uppercase">Riwayat</span>
                </Link>

                <!-- Fatigue Check -->
                <Link :href="route('fatigue.questionnaire')" class="flex flex-col items-center justify-center transition-all group" :class="route().current('fatigue.*') ? 'text-amber-500' : 'text-slate-400 hover:text-slate-200'">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 group-active:scale-90 transition-transform duration-100">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0110 21a3.745 3.745 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0114 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z" />
                    </svg>
                    <span class="text-[10px] font-bold mt-1 tracking-wide uppercase">Tes Fit</span>
                </Link>
            </div>
        </nav>
    </div>
</template>
