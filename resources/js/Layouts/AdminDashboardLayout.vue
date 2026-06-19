<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import Toast from '@/Components/Toast.vue';
import axios from 'axios';

defineProps({
    title: String,
});

const isSidebarOpen = ref(true);
const page = usePage();
const showNotifications = ref(false);

const isKuisActive = computed(() => {
    return route().current('admin.quizzes.*') || 
           route().current('admin.questions.*') || 
           route().current('admin.quiz.*') || 
           route().current('admin.leaderboard.*');
});

const isUserActive = computed(() => {
    return route().current('admin.users.*');
});

const isInsidenActive = computed(() => {
    return route().current('admin.incidents.*');
});

const isFatigueActive = computed(() => {
    return route().current('admin.fatigue-checks.*') || 
           route().current('admin.fatigue-questions.*');
});

const isMasterActive = computed(() => {
    return route().current('admin.locations.*') || 
           route().current('admin.categories.*');
});

const isSettingActive = computed(() => {
    return route().current('profile.edit');
});

const openMenus = ref({
    kuis: isKuisActive.value,
    user: isUserActive.value,
    insiden: isInsidenActive.value,
    fatigue: isFatigueActive.value,
    master: isMasterActive.value,
    setting: isSettingActive.value,
});

const toggleMenu = (menuName) => {
    if (!isSidebarOpen.value) {
        isSidebarOpen.value = true;
        openMenus.value[menuName] = true;
    } else {
        openMenus.value[menuName] = !openMenus.value[menuName];
    }
};

const adminNotifications = ref(
    page.props.auth.notifications?.filter(n => n.type === 'App\\Notifications\\NewIncidentReported') || []
);

const fetchNotifications = async () => {
    try {
        const response = await axios.get(route('notifications.unread'));
        adminNotifications.value = response.data.filter(n => n.type === 'App\\Notifications\\NewIncidentReported');
    } catch (error) {
        console.error('Failed to fetch notifications', error);
    }
};

const markAdminNotificationsAsRead = () => {
    router.post(route('notifications.mark-as-read'), {}, {
        preserveScroll: true,
        onSuccess: () => { 
            showNotifications.value = false;
            adminNotifications.value = [];
        }
    });
};

const logout = () => {
    router.post(route('logout'));
};

let pollingInterval = null;

onMounted(() => {
    pollingInterval = setInterval(fetchNotifications, 30000); // Polling setiap 30 detik dengan payload sangat ringan
});

onUnmounted(() => {
    if (pollingInterval) clearInterval(pollingInterval);
});
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
            <nav class="flex-1 py-4 px-3 space-y-1 overflow-y-auto max-h-[calc(100vh-10rem)]">
                <!-- Dashboard -->
                <Link :href="route('admin.dashboard')" :class="[route().current('admin.dashboard') ? 'bg-blue-500/10 text-blue-500 border-l-4 border-blue-500' : 'text-slate-400 hover:bg-slate-800/50 hover:text-slate-100', 'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors']">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                    </svg>
                    <span v-if="isSidebarOpen">Dashboard</span>
                </Link>

                <!-- Manajemen Kuis -->
                <div class="space-y-1">
                    <button @click="toggleMenu('kuis')" :class="[isKuisActive ? 'bg-amber-500/10 text-amber-500 border-l-4 border-amber-500' : 'text-slate-400 hover:bg-slate-800/50 hover:text-slate-100', 'w-full flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition-colors focus:outline-none', isSidebarOpen ? 'justify-between' : 'justify-center']">
                        <div class="flex items-center gap-3">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 21l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                            </svg>
                            <span v-if="isSidebarOpen">Manajemen Kuis</span>
                        </div>
                        <svg v-if="isSidebarOpen" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" :class="[openMenus.kuis ? 'rotate-180' : '', 'w-3.5 h-3.5 transition-transform duration-200 text-slate-500']">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>
                    <div v-show="isSidebarOpen && openMenus.kuis" class="mt-1 space-y-1 pl-4 border-l border-slate-800 ml-5">
                        <Link :href="route('admin.leaderboard.daily')" :class="[route().current('admin.leaderboard.daily') ? 'text-amber-500 font-semibold' : 'text-slate-400 hover:text-slate-200', 'block py-1.5 text-xs transition-colors']">
                            Leaderboard Harian
                        </Link>
                        <Link :href="route('admin.leaderboard.event')" :class="[route().current('admin.leaderboard.event') ? 'text-amber-500 font-semibold' : 'text-slate-400 hover:text-slate-200', 'block py-1.5 text-xs transition-colors']">
                            Leaderboard Event
                        </Link>
                        <Link :href="route('admin.quiz.history')" :class="[route().current('admin.quiz.history') ? 'text-amber-500 font-semibold' : 'text-slate-400 hover:text-slate-200', 'block py-1.5 text-xs transition-colors']">
                            Riwayat Kuis
                        </Link>
                        <Link :href="route('admin.quizzes.index')" :class="[route().current('admin.quizzes.*') ? 'text-amber-500 font-semibold' : 'text-slate-400 hover:text-slate-200', 'block py-1.5 text-xs transition-colors']">
                            Kelola Kuis
                        </Link>
                        <Link :href="route('admin.questions.index')" :class="[route().current('admin.questions.*') ? 'text-amber-500 font-semibold' : 'text-slate-400 hover:text-slate-200', 'block py-1.5 text-xs transition-colors']">
                            Bank Soal
                        </Link>
                    </div>
                </div>

                <!-- Manajemen User -->
                <div class="space-y-1">
                    <button @click="toggleMenu('user')" :class="[isUserActive ? 'bg-amber-500/10 text-amber-500 border-l-4 border-amber-500' : 'text-slate-400 hover:bg-slate-800/50 hover:text-slate-100', 'w-full flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition-colors focus:outline-none', isSidebarOpen ? 'justify-between' : 'justify-center']">
                        <div class="flex items-center gap-3">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.109A2.25 2.25 0 0112.75 21.5h-1.5a2.25 2.25 0 01-2.25-2.263V19.13m-2.625.372A9.336 9.336 0 011.5 18.553a4.125 4.125 0 017.533-2.493m0 0a9.07 9.07 0 013.217-3.185M9.813 15.904L9 21m8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M12 12.75a3.75 3.75 0 100-7.5 3.75 3.75 0 000 7.5z" />
                            </svg>
                            <span v-if="isSidebarOpen">Manajemen User</span>
                        </div>
                        <svg v-if="isSidebarOpen" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" :class="[openMenus.user ? 'rotate-180' : '', 'w-3.5 h-3.5 transition-transform duration-200 text-slate-500']">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>
                    <div v-show="isSidebarOpen && openMenus.user" class="mt-1 space-y-1 pl-4 border-l border-slate-800 ml-5">
                        <Link :href="route('admin.users.index')" :class="[route().current('admin.users.*') ? 'text-amber-500 font-semibold' : 'text-slate-400 hover:text-slate-200', 'block py-1.5 text-xs transition-colors']">
                            User
                        </Link>
                    </div>
                </div>

                <!-- Manajemen Insiden -->
                <div class="space-y-1">
                    <button @click="toggleMenu('insiden')" :class="[isInsidenActive ? 'bg-amber-500/10 text-amber-500 border-l-4 border-amber-500' : 'text-slate-400 hover:bg-slate-800/50 hover:text-slate-100', 'w-full flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition-colors focus:outline-none', isSidebarOpen ? 'justify-between' : 'justify-center']">
                        <div class="flex items-center gap-3">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                            </svg>
                            <span v-if="isSidebarOpen">Manajemen Insiden</span>
                        </div>
                        <svg v-if="isSidebarOpen" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" :class="[openMenus.insiden ? 'rotate-180' : '', 'w-3.5 h-3.5 transition-transform duration-200 text-slate-500']">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>
                    <div v-show="isSidebarOpen && openMenus.insiden" class="mt-1 space-y-1 pl-4 border-l border-slate-800 ml-5">
                        <Link :href="route('admin.incidents.dashboard')" :class="[route().current('admin.incidents.dashboard') ? 'text-amber-500 font-semibold' : 'text-slate-400 hover:text-slate-200', 'block py-1.5 text-xs transition-colors']">
                            Dashboard Insiden
                        </Link>
                        <Link :href="route('admin.incidents.index')" :class="[route().current('admin.incidents.index') ? 'text-amber-500 font-semibold' : 'text-slate-400 hover:text-slate-200', 'block py-1.5 text-xs transition-colors']">
                            Laporan Insiden
                        </Link>
                    </div>
                </div>

                <!-- Manajemen Fatigue -->
                <div class="space-y-1">
                    <button @click="toggleMenu('fatigue')" :class="[isFatigueActive ? 'bg-amber-500/10 text-amber-500 border-l-4 border-amber-500' : 'text-slate-400 hover:bg-slate-800/50 hover:text-slate-100', 'w-full flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition-colors focus:outline-none', isSidebarOpen ? 'justify-between' : 'justify-center']">
                        <div class="flex items-center gap-3">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0110 21a3.745 3.745 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0114 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z" />
                            </svg>
                            <span v-if="isSidebarOpen">Manajemen Fatigue</span>
                        </div>
                        <svg v-if="isSidebarOpen" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" :class="[openMenus.fatigue ? 'rotate-180' : '', 'w-3.5 h-3.5 transition-transform duration-200 text-slate-500']">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>
                    <div v-show="isSidebarOpen && openMenus.fatigue" class="mt-1 space-y-1 pl-4 border-l border-slate-800 ml-5">
                        <Link :href="route('admin.fatigue-checks.index')" :class="[route().current('admin.fatigue-checks.*') ? 'text-amber-500 font-semibold' : 'text-slate-400 hover:text-slate-200', 'block py-1.5 text-xs transition-colors']">
                            Laporan Kelelahan
                        </Link>
                        <Link :href="route('admin.fatigue-questions.index')" :class="[route().current('admin.fatigue-questions.*') ? 'text-amber-500 font-semibold' : 'text-slate-400 hover:text-slate-200', 'block py-1.5 text-xs transition-colors']">
                            Pertanyaan Fatigue
                        </Link>
                    </div>
                </div>

                <!-- Master -->
                <div class="space-y-1">
                    <button @click="toggleMenu('master')" :class="[isMasterActive ? 'bg-amber-500/10 text-amber-500 border-l-4 border-amber-500' : 'text-slate-400 hover:bg-slate-800/50 hover:text-slate-100', 'w-full flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition-colors focus:outline-none', isSidebarOpen ? 'justify-between' : 'justify-center']">
                        <div class="flex items-center gap-3">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375m16.5 0v3.75m-16.5-3.75v3.75m16.5 0c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V10.125m16.5 0v3.75m-16.5-3.75v3.75m16.5 0c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125v-3.75" />
                            </svg>
                            <span v-if="isSidebarOpen">Master</span>
                        </div>
                        <svg v-if="isSidebarOpen" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" :class="[openMenus.master ? 'rotate-180' : '', 'w-3.5 h-3.5 transition-transform duration-200 text-slate-500']">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>
                    <div v-show="isSidebarOpen && openMenus.master" class="mt-1 space-y-1 pl-4 border-l border-slate-800 ml-5">
                        <Link :href="route('admin.locations.index')" :class="[route().current('admin.locations.*') ? 'text-amber-500 font-semibold' : 'text-slate-400 hover:text-slate-200', 'block py-1.5 text-xs transition-colors']">
                            Lokasi
                        </Link>
                        <Link :href="route('admin.categories.index')" :class="[route().current('admin.categories.*') ? 'text-amber-500 font-semibold' : 'text-slate-400 hover:text-slate-200', 'block py-1.5 text-xs transition-colors']">
                            Kategori
                        </Link>
                    </div>
                </div>

                <!-- Setting -->
                <div class="space-y-1">
                    <button @click="toggleMenu('setting')" :class="[isSettingActive ? 'bg-amber-500/10 text-amber-500 border-l-4 border-amber-500' : 'text-slate-400 hover:bg-slate-800/50 hover:text-slate-100', 'w-full flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition-colors focus:outline-none', isSidebarOpen ? 'justify-between' : 'justify-center']">
                        <div class="flex items-center gap-3">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.43l-1.003.828c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.43l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            </svg>
                            <span v-if="isSidebarOpen">Setting</span>
                        </div>
                        <svg v-if="isSidebarOpen" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" :class="[openMenus.setting ? 'rotate-180' : '', 'w-3.5 h-3.5 transition-transform duration-200 text-slate-500']">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>
                    <div v-show="isSidebarOpen && openMenus.setting" class="mt-1 space-y-1 pl-4 border-l border-slate-800 ml-5">
                        <Link :href="route('profile.edit')" :class="[route().current('profile.edit') ? 'text-amber-500 font-semibold' : 'text-slate-400 hover:text-slate-200', 'block py-1.5 text-xs transition-colors']">
                            Profil / Pengaturan
                        </Link>
                    </div>
                </div>
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
                    <!-- Notifications -->
                    <div class="relative">
                        <button @click="showNotifications = !showNotifications" class="relative p-2 text-slate-400 hover:text-slate-100 transition-colors rounded-full hover:bg-slate-800 focus:outline-none">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                            </svg>
                            <span v-if="adminNotifications.length > 0" class="absolute top-1.5 right-1.5 w-2.5 h-2.5 bg-red-500 border-2 border-slate-900 rounded-full animate-pulse"></span>
                        </button>

                        <!-- Dropdown -->
                        <div v-if="showNotifications" class="absolute right-0 mt-2 w-80 bg-slate-800 border border-slate-700 rounded-xl shadow-xl z-50 overflow-hidden">
                            <div class="px-4 py-3 border-b border-slate-700 flex justify-between items-center bg-slate-800/50">
                                <h3 class="text-sm font-bold text-slate-100">Notifikasi</h3>
                                <button v-if="adminNotifications.length > 0" @click="markAdminNotificationsAsRead" class="text-xs font-medium text-amber-500 hover:text-amber-400">Tandai sudah dibaca</button>
                            </div>
                            <div class="max-h-80 overflow-y-auto">
                                <div v-if="adminNotifications.length === 0" class="px-4 py-6 text-center text-slate-400 text-sm">
                                    Tidak ada notifikasi baru
                                </div>
                                <div v-else class="divide-y divide-slate-700/50">
                                    <Link v-for="notif in adminNotifications" :key="notif.id" :href="route('admin.incidents.index')" class="block px-4 py-3 hover:bg-slate-700/50 transition-colors">
                                        <p class="text-sm font-medium text-slate-200">{{ notif.data.title }}</p>
                                        <p class="text-xs text-slate-400 mt-0.5 line-clamp-2">{{ notif.data.message }}</p>
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>

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
