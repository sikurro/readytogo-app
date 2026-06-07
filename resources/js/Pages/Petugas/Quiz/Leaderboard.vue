<script setup>
import { Head, Link } from '@inertiajs/vue3';
import MobileAppLayout from '@/Layouts/MobileAppLayout.vue';
import { usePage } from '@inertiajs/vue3';

defineProps({
    leaderboard: Array,
    currentMonth: String
});

const formatTime = (ms) => {
    const seconds = Math.floor(ms / 1000);
    const minutes = Math.floor(seconds / 60);
    const remainingSeconds = seconds % 60;
    return `${minutes}m ${remainingSeconds}s`;
};

const formatDate = (dateString) => {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const currentUserId = usePage().props.auth.user.id;
</script>

<template>
    <Head title="Leaderboard Kuis" />

    <MobileAppLayout>
        <div class="py-4">
            <!-- Header Section -->
            <div class="mb-6 flex items-center justify-between">
                <h2 class="text-2xl font-black text-slate-100 flex items-center gap-2">
                    <span class="w-2 h-6 bg-gradient-to-b from-amber-500 to-orange-500 rounded-full"></span>
                    Klasemen Petugas
                </h2>
                
                <Link 
                    :href="route('quiz.index')" 
                    class="flex items-center gap-1.5 text-xs text-amber-500 hover:text-amber-400 font-bold bg-slate-900 border border-slate-800 hover:bg-slate-800/80 px-3.5 py-2 rounded-xl transition-all duration-200 active:scale-95"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3.5 h-3.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                    </svg>
                    Kembali
                </Link>
            </div>

            <!-- Subtitle/Month Info -->
            <p class="text-xs text-slate-400 mb-6 -mt-3">
                Periode Aktif: <span class="text-amber-500 font-bold">{{ currentMonth }}</span>
            </p>

            <div v-if="leaderboard && leaderboard.length > 0">
                <!-- Top 3 Podium Highlight (Visual Highlight) -->
                <div class="grid grid-cols-3 gap-3 items-end mb-8 pt-4 pb-2 px-1">
                    <!-- Rank 2 -->
                    <div v-if="leaderboard[1]" class="flex flex-col items-center">
                        <div class="relative mb-2">
                            <div class="w-16 h-16 rounded-full bg-slate-900 border-2 border-slate-300 flex items-center justify-center text-slate-300 font-black text-lg shadow-lg">
                                {{ leaderboard[1].user.name.substring(0, 2).toUpperCase() }}
                            </div>
                            <span class="absolute -bottom-1 -right-1 bg-slate-300 text-slate-950 font-black text-[10px] w-5 h-5 rounded-full flex items-center justify-center border border-slate-950">2</span>
                        </div>
                        <span class="text-xs font-bold text-slate-200 truncate w-full text-center">{{ leaderboard[1].user.name }}</span>
                        <span class="text-xs font-extrabold text-amber-500 mt-0.5">{{ leaderboard[1].total_score }} pts</span>
                    </div>
                    <div v-else class="flex flex-col items-center opacity-30">
                        <div class="w-16 h-16 rounded-full bg-slate-900 border-2 border-dashed border-slate-800 flex items-center justify-center text-slate-600 text-lg font-bold">2</div>
                    </div>

                    <!-- Rank 1 (Center, Bigger, Glowing) -->
                    <div v-if="leaderboard[0]" class="flex flex-col items-center">
                        <div class="relative mb-2 transform -translate-y-2">
                            <div class="absolute -top-6 left-1/2 -translate-x-1/2 text-2xl animate-bounce">👑</div>
                            <div class="w-20 h-20 rounded-full bg-slate-900 border-4 border-amber-500 flex items-center justify-center text-amber-500 font-black text-xl shadow-[0_0_20px_rgba(245,158,11,0.3)]">
                                {{ leaderboard[0].user.name.substring(0, 2).toUpperCase() }}
                            </div>
                            <span class="absolute -bottom-1 -right-1 bg-amber-500 text-slate-950 font-black text-xs w-6 h-6 rounded-full flex items-center justify-center border border-slate-950">1</span>
                        </div>
                        <span class="text-sm font-black text-slate-100 truncate w-full text-center">{{ leaderboard[0].user.name }}</span>
                        <span class="text-sm font-black text-amber-500 mt-0.5">{{ leaderboard[0].total_score }} pts</span>
                    </div>

                    <!-- Rank 3 -->
                    <div v-if="leaderboard[2]" class="flex flex-col items-center">
                        <div class="relative mb-2">
                            <div class="w-16 h-16 rounded-full bg-slate-900 border-2 border-amber-700/80 flex items-center justify-center text-amber-600 font-black text-lg shadow-lg">
                                {{ leaderboard[2].user.name.substring(0, 2).toUpperCase() }}
                            </div>
                            <span class="absolute -bottom-1 -right-1 bg-amber-700 text-white font-black text-[10px] w-5 h-5 rounded-full flex items-center justify-center border border-slate-950">3</span>
                        </div>
                        <span class="text-xs font-bold text-slate-200 truncate w-full text-center">{{ leaderboard[2].user.name }}</span>
                        <span class="text-xs font-extrabold text-amber-500 mt-0.5">{{ leaderboard[2].total_score }} pts</span>
                    </div>
                    <div v-else class="flex flex-col items-center opacity-30">
                        <div class="w-16 h-16 rounded-full bg-slate-900 border-2 border-dashed border-slate-800 flex items-center justify-center text-slate-600 text-lg font-bold">3</div>
                    </div>
                </div>

                <!-- Leaderboard List Items -->
                <div class="space-y-3">
                    <div 
                        v-for="(entry, index) in leaderboard" 
                        :key="entry.user_id"
                        class="flex items-center justify-between bg-slate-900 border rounded-2xl p-4 transition-all duration-150"
                        :class="[
                            entry.user_id === currentUserId 
                                ? 'border-amber-500 bg-slate-900/90 shadow-[0_0_12px_rgba(245,158,11,0.1)]' 
                                : 'border-slate-800/80'
                        ]"
                    >
                        <div class="flex items-center gap-3.5 min-w-0">
                            <!-- Rank indicator / Medal -->
                            <div class="w-8 flex justify-center shrink-0">
                                <span v-if="index === 0" class="text-2xl">🥇</span>
                                <span v-else-if="index === 1" class="text-2xl">🥈</span>
                                <span v-else-if="index === 2" class="text-2xl">🥉</span>
                                <span v-else class="font-extrabold text-slate-500 text-sm">#{{ index + 1 }}</span>
                            </div>

                            <!-- Avatar placeholder -->
                            <div class="w-10 h-10 rounded-xl bg-slate-950 border border-slate-800 flex items-center justify-center text-slate-300 font-bold shrink-0">
                                {{ entry.user.name.substring(0, 2).toUpperCase() }}
                            </div>

                            <!-- User info -->
                            <div class="min-w-0">
                                <div class="flex items-center gap-1.5">
                                    <span class="font-bold text-slate-100 text-sm truncate leading-none">{{ entry.user.name }}</span>
                                    <span v-if="entry.user_id === currentUserId" class="bg-amber-500/10 border border-amber-500/30 text-amber-500 text-[9px] font-black px-1.5 py-0.5 rounded-md uppercase tracking-wider">Anda</span>
                                </div>
                                <span class="text-[10px] text-slate-400 block mt-1">{{ entry.user.position || 'Petugas' }}</span>
                            </div>
                        </div>

                        <!-- Stats (Score & Details) -->
                        <div class="text-right shrink-0">
                            <span class="text-lg font-black text-amber-500">{{ entry.total_score }}</span>
                            <div class="text-[9px] text-slate-400 font-medium mt-0.5">
                                <span class="text-emerald-400 font-bold">{{ entry.total_correct }}</span> benar • {{ formatTime(entry.total_time) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="text-center py-12 bg-slate-900/50 border border-slate-800/80 rounded-2xl p-6">
                <div class="text-4xl mb-3">🏆</div>
                <p class="text-slate-500 text-sm font-medium">Belum ada data klasemen bulan ini.</p>
            </div>
        </div>
    </MobileAppLayout>
</template>
