<script setup>
import { Head, Link } from '@inertiajs/vue3';
import MobileAppLayout from '@/Layouts/MobileAppLayout.vue';

defineProps({
    todayCheck: Object,
    stats: Object,
    dailyTip: String,
});
</script>

<template>
    <Head title="Modul Fatigue - Ready To GO" />

    <MobileAppLayout>
        <div class="space-y-6">
            <!-- Header section -->
            <div class="flex items-center gap-3">
                <span class="w-1.5 h-8 bg-gradient-to-b from-sky-400 to-indigo-500 rounded-full"></span>
                <div>
                    <h2 class="text-xl font-black text-slate-100 tracking-tight">Fatigue Hub</h2>
                    <p class="text-xs text-slate-400 font-medium mt-0.5">Pantau status kebugaran Anda hari ini</p>
                </div>
            </div>

            <!-- Hero Section: Hasil Hari Ini -->
            <div class="bg-slate-900/60 backdrop-blur border border-slate-800 rounded-2xl overflow-hidden shadow-xl">
                <div class="p-4 border-b border-slate-800/60 bg-slate-800/30 flex justify-between items-center">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Status Hari Ini</span>
                    <span class="text-[10px] font-semibold text-slate-500">{{ new Date().toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long' }) }}</span>
                </div>
                
                <div class="p-6">
                    <template v-if="!todayCheck">
                        <div class="text-center space-y-4 py-2">
                            <div class="w-16 h-16 mx-auto bg-amber-500/10 rounded-full flex items-center justify-center border border-amber-500/20">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-8 h-8 text-amber-500">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-slate-200">Anda Belum Tes Fatigue</h3>
                                <p class="text-xs text-slate-400 mt-1">Lakukan pemeriksaan kesiapan kerja sebelum memulai aktivitas di lapangan.</p>
                            </div>
                            <Link :href="route('fatigue.questionnaire')" class="inline-flex w-full justify-center items-center gap-2 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-slate-950 font-black py-3 px-4 rounded-xl shadow-lg shadow-amber-500/20 transition-all active:scale-[0.98] uppercase text-xs tracking-wider">
                                Mulai Tes Sekarang
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg>
                            </Link>
                        </div>
                    </template>
                    <template v-else>
                        <div class="text-center space-y-4">
                            <!-- Circular Status Indicator -->
                            <div class="relative w-24 h-24 mx-auto rounded-full flex items-center justify-center"
                                :class="todayCheck.is_fit ? 'bg-emerald-500/10 border border-emerald-500/30' : 'bg-rose-500/10 border border-rose-500/30'">
                                <div class="absolute inset-0 rounded-full animate-[ping_3s_ease-in-out_infinite] opacity-20"
                                    :class="todayCheck.is_fit ? 'bg-emerald-500' : 'bg-rose-500'"></div>
                                
                                <svg v-if="todayCheck.is_fit" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-10 h-10 text-emerald-400">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0110 21a3.745 3.745 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0114 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z" />
                                </svg>
                                <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-10 h-10 text-rose-400">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                            
                            <div>
                                <h3 class="text-xl font-black uppercase tracking-widest" :class="todayCheck.is_fit ? 'text-emerald-400' : 'text-rose-400'">
                                    {{ todayCheck.is_fit ? 'FIT TO WORK' : 'UNFIT' }}
                                </h3>
                                <p class="text-xs font-semibold text-slate-300 mt-1">Waktu Reaksi: <span class="text-white">{{ todayCheck.reaction_time_ms }} ms</span></p>
                            </div>
                            
                            <p class="text-[11px] text-slate-400 bg-slate-950/50 rounded-lg px-3 py-2 mt-2">
                                {{ todayCheck.is_fit ? 'Kondisi fisik dan konsentrasi Anda optimal hari ini. Selamat bertugas!' : 'Waktu reaksi Anda lambat atau Anda kurang istirahat. Hubungi pengawas.' }}
                            </p>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Tren & Statistik Section -->
            <div class="space-y-3">
                <h3 class="text-[11px] font-bold text-slate-400 uppercase tracking-widest px-1">Tren 30 Hari Terakhir</h3>
                <div class="grid grid-cols-2 gap-3">
                    <div class="bg-slate-900 border border-slate-800 rounded-xl p-4 flex flex-col justify-center relative overflow-hidden">
                        <div class="absolute -right-4 -bottom-4 opacity-5">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-24 h-24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" /></svg>
                        </div>
                        <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Rata-Rata Reaksi</span>
                        <div class="flex items-baseline gap-1 mt-1">
                            <span class="text-2xl font-black text-slate-100">{{ stats.avg_reaction_time }}</span>
                            <span class="text-xs font-bold text-slate-500">ms</span>
                        </div>
                    </div>
                    
                    <div class="bg-slate-900 border border-slate-800 rounded-xl p-4 flex flex-col justify-center relative overflow-hidden">
                        <div class="absolute -right-4 -bottom-4 opacity-5">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-24 h-24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0110 21a3.745 3.745 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0114 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z" /></svg>
                        </div>
                        <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Tingkat Kebugaran</span>
                        <div class="flex items-baseline gap-1 mt-1">
                            <span class="text-2xl font-black" :class="stats.fit_rate >= 80 ? 'text-emerald-400' : 'text-amber-500'">{{ stats.fit_rate }}</span>
                            <span class="text-xs font-bold text-slate-500">%</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tombol Navigasi Riwayat -->
            <Link :href="route('fatigue.history')" class="w-full bg-slate-800/50 border border-slate-700 hover:bg-slate-800 hover:border-slate-600 text-slate-300 font-bold py-3.5 px-4 rounded-xl flex items-center justify-between transition-all active:scale-95 group">
                <div class="flex items-center gap-3">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-slate-400 group-hover:text-amber-500 transition-colors">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="text-sm">Lihat Semua Riwayat Fatigue</span>
                </div>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4 text-slate-500 group-hover:translate-x-1 transition-transform">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                </svg>
            </Link>

            <!-- Daily Tips (Micro-Learning) -->
            <div class="bg-gradient-to-br from-indigo-900/40 to-slate-900/60 border border-indigo-500/20 rounded-2xl p-5 relative overflow-hidden">
                <div class="absolute -right-2 -top-2 w-20 h-20 bg-indigo-500/10 rounded-full blur-xl"></div>
                <div class="flex items-start gap-3 relative z-10">
                    <div class="p-2 bg-indigo-500/20 rounded-lg shrink-0 border border-indigo-500/30">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-indigo-400">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 18v-5.25m0 0a6.01 6.01 0 001.5-.189m-1.5.189a6.01 6.01 0 01-1.5-.189m3.75 7.478a12.06 12.06 0 01-4.5 0m3.75 2.383a14.406 14.406 0 01-3 0M14.25 18v-.192c0-.983.658-1.82 1.508-2.316a7.5 7.5 0 10-7.517 0c.85.496 1.509 1.333 1.509 2.316V18" />
                        </svg>
                    </div>
                    <div>
                        <h4 class="text-xs font-black text-indigo-300 uppercase tracking-widest mb-1.5">Tips Kebugaran Hari Ini</h4>
                        <p class="text-xs text-slate-300 leading-relaxed">{{ dailyTip }}</p>
                    </div>
                </div>
            </div>
            
        </div>
    </MobileAppLayout>
</template>
