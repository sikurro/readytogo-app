<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import MobileAppLayout from '@/Layouts/MobileAppLayout.vue';

const props = defineProps({
    quizzes: Array,
    todayAttempt: Object,
    rankData: Object,
    stats: Object,
    dailyTrivia: String,
});

const formattedTime = computed(() => {
    if (!props.todayAttempt || !props.todayAttempt.time_ms) return '';
    const ms = props.todayAttempt.time_ms;
    const totalSeconds = Math.floor(ms / 1000);
    const minutes = Math.floor(totalSeconds / 60);
    const seconds = totalSeconds % 60;
    
    if (minutes > 0) {
        return `${minutes} menit ${seconds} detik`;
    }
    return `${seconds} detik`;
});
</script>

<template>
    <Head title="Kuis Harian K3" />

    <MobileAppLayout>
        <div class="py-4 space-y-6">
            <!-- Header section -->
            <div class="flex items-center gap-3 mb-2">
                <span class="w-1.5 h-8 bg-gradient-to-b from-amber-400 to-orange-500 rounded-full"></span>
                <div>
                    <h2 class="text-xl font-black text-slate-100 tracking-tight">Quiz Hub</h2>
                    <p class="text-xs text-slate-400 font-medium mt-0.5">Asah pengetahuan K3 Anda hari ini</p>
                </div>
            </div>

            <!-- Hero Section: Misi Harian -->
            <div class="bg-slate-900/60 backdrop-blur border border-slate-800 rounded-2xl overflow-hidden shadow-xl">
                <div class="p-4 border-b border-slate-800/60 bg-slate-800/30 flex justify-between items-center">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Misi Hari Ini</span>
                    <span class="text-[10px] font-semibold text-slate-500">{{ new Date().toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long' }) }}</span>
                </div>
                
                <div class="p-6">
                    <template v-if="!todayAttempt">
                        <div class="text-center space-y-4 py-2" v-if="quizzes && quizzes.length > 0">
                            <div class="w-16 h-16 mx-auto bg-amber-500/10 rounded-full flex items-center justify-center border border-amber-500/20">
                                <span class="text-3xl">🎯</span>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-slate-200">Tantangan Belum Dikerjakan</h3>
                                <p class="text-xs text-slate-400 mt-1">Selesaikan kuis hari ini untuk menambah poin Anda.</p>
                            </div>
                            <Link :href="route('quiz.play', quizzes[0].id)" class="inline-flex w-full justify-center items-center gap-2 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-slate-950 font-black py-3 px-4 rounded-xl shadow-lg shadow-amber-500/20 transition-all active:scale-[0.98] uppercase text-xs tracking-wider">
                                Mulai Kuis Sekarang
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg>
                            </Link>
                        </div>
                        <div v-else class="text-center py-4">
                            <p class="text-slate-500 text-sm">Belum ada kuis yang aktif hari ini.</p>
                        </div>
                    </template>
                    <template v-else>
                        <div class="text-center space-y-4">
                            <div class="w-16 h-16 mx-auto bg-emerald-500/10 rounded-full flex items-center justify-center border border-emerald-500/30">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-8 h-8 text-emerald-400">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0110 21a3.745 3.745 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0114 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z" />
                                </svg>
                            </div>
                            
                            <div>
                                <h3 class="text-xl font-black uppercase tracking-widest text-emerald-400">
                                    KUIS SELESAI
                                </h3>
                                <div class="flex justify-center gap-4 mt-3">
                                    <div class="bg-slate-950/50 rounded-xl px-5 py-3 border border-slate-800 text-center min-w-[80px]">
                                        <span class="block text-[10px] text-slate-500 font-bold uppercase tracking-wider mb-0.5">Skor</span>
                                        <span class="text-3xl font-black text-amber-500">{{ todayAttempt.score }}</span>
                                    </div>
                                    <div class="bg-slate-950/50 rounded-xl px-5 py-3 border border-slate-800 text-center min-w-[80px]">
                                        <span class="block text-[10px] text-slate-500 font-bold uppercase tracking-wider mb-0.5">Benar</span>
                                        <span class="text-3xl font-black text-emerald-400">{{ todayAttempt.correct_answers }}</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-3 flex items-center justify-center gap-1.5 text-xs text-slate-400 font-semibold">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4 text-amber-500">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>Waktu pengerjaan: {{ formattedTime }}</span>
                            </div>
                            
                            <p class="text-[11px] text-slate-400 bg-slate-950/50 rounded-lg px-3 py-2 mt-2">
                                Anda telah mendapatkan poin hari ini. Kembali besok untuk tantangan berikutnya!
                            </p>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Mini Podium / Klasemen -->
            <div class="bg-gradient-to-br from-slate-900 to-slate-900/80 border border-slate-800 rounded-2xl p-5 relative overflow-hidden flex items-center justify-between">
                <div class="absolute -right-4 -bottom-4 opacity-5">
                    <span class="text-9xl">🏆</span>
                </div>
                <div class="relative z-10">
                    <h3 class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Posisi Anda Bulan Ini</h3>
                    <div class="flex items-baseline gap-2">
                        <span class="text-2xl font-black text-amber-500">
                            {{ rankData.rank ? '#' + rankData.rank : '-' }}
                        </span>
                        <span class="text-xs text-slate-500 font-semibold">dari {{ rankData.total_participants }} petugas</span>
                    </div>
                    <p class="text-[11px] text-slate-400 mt-1" v-if="rankData.rank && rankData.rank > 3">
                        Tingkatkan terus poin Anda untuk masuk Top 3!
                    </p>
                    <p class="text-[11px] text-amber-500 font-bold mt-1" v-else-if="rankData.rank && rankData.rank <= 3">
                        Luar biasa! Anda berada di papan atas.
                    </p>
                    <p class="text-[11px] text-slate-400 mt-1" v-else>
                        Kerjakan kuis untuk mendapatkan peringkat.
                    </p>
                </div>
                
                <Link :href="route('quiz.leaderboard')" class="relative z-10 shrink-0 bg-slate-800 hover:bg-slate-700 border border-slate-700 text-slate-300 rounded-full w-10 h-10 flex items-center justify-center transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg>
                </Link>
            </div>

            <!-- Tren & Statistik Section -->
            <div class="space-y-3">
                <h3 class="text-[11px] font-bold text-slate-400 uppercase tracking-widest px-1">Statistik Bulan Ini</h3>
                <div class="grid grid-cols-2 gap-3">
                    <div class="bg-slate-900 border border-slate-800 rounded-xl p-4 flex flex-col justify-center relative overflow-hidden">
                        <div class="absolute -right-2 -bottom-2 opacity-5">
                            <span class="text-6xl">⭐</span>
                        </div>
                        <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Total Poin</span>
                        <div class="flex items-baseline gap-1 mt-1">
                            <span class="text-2xl font-black text-amber-500">{{ stats.total_points_30d }}</span>
                            <span class="text-xs font-bold text-slate-500">pts</span>
                        </div>
                    </div>
                    
                    <div class="bg-slate-900 border border-slate-800 rounded-xl p-4 flex flex-col justify-center relative overflow-hidden">
                        <div class="absolute -right-2 -bottom-2 opacity-5">
                            <span class="text-6xl">🔥</span>
                        </div>
                        <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Partisipasi</span>
                        <div class="flex items-baseline gap-1 mt-1">
                            <span class="text-2xl font-black text-emerald-400">{{ stats.total_played }}</span>
                            <span class="text-xs font-bold text-slate-500">kali</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tombol Navigasi Riwayat -->
            <Link :href="route('quiz.history')" class="w-full bg-slate-800/50 border border-slate-700 hover:bg-slate-800 hover:border-slate-600 text-slate-300 font-bold py-3.5 px-4 rounded-xl flex items-center justify-between transition-all active:scale-95 group">
                <div class="flex items-center gap-3">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-slate-400 group-hover:text-amber-500 transition-colors">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="text-sm">Lihat Semua Riwayat Kuis</span>
                </div>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4 text-slate-500 group-hover:translate-x-1 transition-transform">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                </svg>
            </Link>

            <!-- Daily Trivia (Micro-Learning) -->
            <div class="bg-gradient-to-br from-amber-900/30 to-slate-900/60 border border-amber-500/20 rounded-2xl p-5 relative overflow-hidden">
                <div class="absolute -right-2 -top-2 w-20 h-20 bg-amber-500/10 rounded-full blur-xl"></div>
                <div class="flex items-start gap-3 relative z-10">
                    <div class="p-2 bg-amber-500/20 rounded-lg shrink-0 border border-amber-500/30">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-amber-400">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 18v-5.25m0 0a6.01 6.01 0 001.5-.189m-1.5.189a6.01 6.01 0 01-1.5-.189m3.75 7.478a12.06 12.06 0 01-4.5 0m3.75 2.383a14.406 14.406 0 01-3 0M14.25 18v-.192c0-.983.658-1.82 1.508-2.316a7.5 7.5 0 10-7.517 0c.85.496 1.509 1.333 1.509 2.316V18" />
                        </svg>
                    </div>
                    <div>
                        <h4 class="text-xs font-black text-amber-500 uppercase tracking-widest mb-1.5">Trivia K3 Hari Ini</h4>
                        <p class="text-xs text-slate-300 leading-relaxed">{{ dailyTrivia }}</p>
                    </div>
                </div>
            </div>
            
        </div>
    </MobileAppLayout>
</template>
