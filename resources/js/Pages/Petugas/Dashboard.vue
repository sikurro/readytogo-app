<script setup>
import MobileAppLayout from '@/Layouts/MobileAppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    auth: Object,
    activeEventQuiz: Object,
    statusBugarHariIni: [Boolean, null],
    hasAttemptedEventQuiz: Boolean,
    safetyTip: String,
    hasCompletedDailyQuizToday: Boolean,
});

const today = new Date().toLocaleDateString('id-ID', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric'
});
</script>

<template>
    <Head title="Home - Ready To GO" />

    <MobileAppLayout>
        <div class="space-y-6">
            <!-- Profile Welcome Card -->
            <div class="bg-gradient-to-br from-slate-900 to-slate-800 border border-slate-800 rounded-2xl p-5 flex items-center gap-4 shadow-xl">
                <div class="h-14 w-14 rounded-xl bg-gradient-to-tr from-amber-500 to-orange-500 flex items-center justify-center font-bold text-slate-950 text-xl shadow-lg shadow-amber-500/20">
                    {{ auth.user.name.charAt(0) }}
                </div>
                <div>
                    <h3 class="text-xs text-slate-400 font-semibold uppercase tracking-wider">Selamat Bekerja,</h3>
                    <h2 class="text-lg font-bold text-slate-100 tracking-tight">{{ auth.user.name }}</h2>
                    <p class="text-xs text-amber-500 font-semibold mt-0.5">{{ auth.user.position || 'Petugas Lapangan' }}</p>
                </div>
            </div>

            <!-- Date and Status Card -->
            <div class="grid grid-cols-2 gap-4">
                <!-- Date Widget -->
                <div class="bg-slate-900/60 backdrop-blur border border-slate-800 rounded-xl p-4 flex flex-col justify-between">
                    <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Tanggal</span>
                    <span class="text-sm font-extrabold text-slate-200 mt-2 leading-tight">{{ today }}</span>
                </div>
                <!-- Status Fit Widget -->
                <div class="bg-slate-900/60 backdrop-blur border border-slate-800 rounded-xl p-4 flex flex-col justify-between">
                    <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Status Fatigue Check</span>
                    <div v-if="statusBugarHariIni === null" class="mt-2 flex items-center gap-2">
                        <span class="h-3 w-3 rounded-full bg-amber-500 animate-pulse"></span>
                        <span class="text-xs font-bold text-amber-500 uppercase tracking-wider">Belum Tes</span>
                    </div>
                    <div v-else-if="statusBugarHariIni === true" class="mt-2 flex items-center gap-2">
                        <span class="h-3 w-3 rounded-full bg-emerald-500 shadow-[0_0_10px_rgba(16,185,129,0.5)]"></span>
                        <span class="text-xs font-extrabold text-emerald-400 uppercase tracking-wider">FIT</span>
                    </div>
                    <div v-else-if="statusBugarHariIni === false" class="mt-2 flex items-center gap-2">
                        <span class="h-3 w-3 rounded-full bg-rose-500 shadow-[0_0_10px_rgba(239,68,68,0.5)]"></span>
                        <span class="text-xs font-extrabold text-rose-400 uppercase tracking-wider">UNFIT</span>
                    </div>
                </div>
            </div>

            <!-- Warning Alert: Mandatory Fatigue Check -->
            <div v-if="statusBugarHariIni === null" class="bg-amber-500/10 border border-amber-500/30 rounded-xl p-4 flex gap-3 items-start">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-amber-500 shrink-0 mt-0.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                </svg>
                <div class="flex-1">
                    <h4 class="text-xs font-bold text-amber-500">Fatigue Check Wajib!</h4>
                    <p class="text-[11px] text-slate-300 mt-1 leading-relaxed">Anda diwajibkan untuk melakukan pemeriksaan kesiapan kerja (reaksi mata) sebelum turun ke lapangan hari ini.</p>
                    <Link :href="route('fatigue.questionnaire')" class="inline-flex items-center gap-1.5 mt-3 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-slate-950 text-[10px] font-black uppercase tracking-wider py-2 px-3.5 rounded-lg shadow-lg shadow-amber-500/20 transition-all active:scale-[0.98]">
                        Cek Sekarang
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="w-3 h-3"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg>
                    </Link>
                </div>
            </div>

            <!-- Micro Education: Tips Keselamatan Kerja Pelabuhan (Hanya muncul jika sudah tes fatigue) -->
            <div v-else class="bg-gradient-to-br from-amber-900/30 to-slate-900/60 border border-amber-500/20 rounded-2xl p-5 relative overflow-hidden shadow-lg shadow-amber-500/5">
                <div class="absolute -right-2 -top-2 w-20 h-20 bg-amber-500/10 rounded-full blur-xl"></div>
                <div class="flex items-start gap-3 relative z-10">
                    <div class="p-2 bg-amber-500/20 rounded-lg shrink-0 border border-amber-500/30">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-amber-400">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 18v-5.25m0 0a6.01 6.01 0 001.5-.189m-1.5.189a6.01 6.01 0 01-1.5-.189m3.75 7.478a12.06 12.06 0 01-4.5 0m3.75 2.383a14.406 14.406 0 01-3 0M14.25 18v-.192c0-.983.658-1.82 1.508-2.316a7.5 7.5 0 10-7.517 0c.85.496 1.509 1.333 1.509 2.316V18" />
                        </svg>
                    </div>
                    <div>
                        <h4 class="text-xs font-black text-amber-500 uppercase tracking-widest mb-1.5">Tips Keselamatan Pelabuhan</h4>
                        <p class="text-xs text-slate-300 leading-relaxed">{{ safetyTip }}</p>
                    </div>
                </div>
            </div>

            <!-- Event Quiz Interactive Banner -->
            <div v-if="activeEventQuiz" class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-purple-600 via-indigo-600 to-blue-600 p-1 shadow-2xl shadow-indigo-500/30 animate-[pulse_3s_ease-in-out_infinite]">
                <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyMCIgaGVpZ2h0PSIyMCI+PGNpcmNsZSBjeD0iMiIgY3k9IjIiIHI9IjIiIGZpbGw9IiNmZmZmZmYiIGZpbGwtb3BhY2l0eT0iMC4xIi8+PC9zdmc+')] opacity-30"></div>
                
                <div class="relative bg-slate-950/80 backdrop-blur-sm rounded-xl p-5 border border-white/10">
                    <div class="flex items-start justify-between">
                        <div class="space-y-1">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-indigo-500/20 text-indigo-300 text-[10px] font-black tracking-widest uppercase border border-indigo-500/30">
                                <span class="w-1.5 h-1.5 rounded-full bg-indigo-400 animate-ping"></span>
                                SPECIAL EVENT LIVE!
                            </span>
                            <h2 class="text-xl font-black text-white leading-tight mt-2">{{ activeEventQuiz.title }}</h2>
                            <p class="text-xs text-indigo-200 font-medium line-clamp-1">Tema: {{ activeEventQuiz.theme }}</p>
                        </div>
                        <div class="h-10 w-10 shrink-0 bg-indigo-500/20 rounded-full flex items-center justify-center border border-indigo-500/30">
                            <span class="text-xl">🏆</span>
                        </div>
                    </div>
                    
                    <div v-if="activeEventQuiz.end_time" class="mt-3 text-[10px] text-indigo-300 font-semibold flex items-center gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3.5 h-3.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Tersedia s.d {{ new Date(activeEventQuiz.end_time).toLocaleString('id-ID', {day: 'numeric', month: 'short', hour: '2-digit', minute:'2-digit'}) }}
                    </div>

                    <div 
                        v-if="hasAttemptedEventQuiz"
                        class="mt-4 block w-full bg-slate-800/80 text-slate-400 border border-slate-700/60 font-black py-3 px-4 rounded-lg text-center text-sm shadow-inner cursor-not-allowed uppercase tracking-wider"
                    >
                        ANDA SUDAH MENGIKUTI EVENT
                    </div>
                    <Link 
                        v-else
                        :href="route('quiz.play', activeEventQuiz.id)" 
                        class="mt-4 block w-full bg-gradient-to-r from-indigo-500 to-purple-500 hover:from-indigo-400 hover:to-purple-400 text-white font-black py-3 px-4 rounded-lg text-center text-sm shadow-lg shadow-indigo-500/25 transition-all active:scale-[0.98]"
                    >
                        IKUTI EVENT SEKARANG
                    </Link>
                </div>
            </div>

            <!-- Quick Action Buttons (Touch Friendly) -->
            <div class="space-y-4">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest px-1">Menu Operasional K3</h3>
                
                <!-- Action 1: Modul Fatigue -->
                <Link :href="route('fatigue.hub')" class="w-full bg-slate-900 border border-slate-800 hover:bg-slate-800/80 text-slate-200 font-extrabold py-4 px-5 rounded-xl flex items-center justify-between transition-all active:scale-95 group block">
                    <div class="flex items-center gap-3 text-left">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6 text-sky-500">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0110 21a3.745 3.745 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043a3.746 3.746 0 0114 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z" />
                        </svg>
                        <div>
                            <span class="block text-sm text-slate-100">Modul Fatigue</span>
                            <span class="block text-[10px] text-slate-400 font-medium">Lihat hasil hari ini, riwayat, dan info fatigue</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <!-- Status Indicator -->
                        <span v-if="statusBugarHariIni === null" class="flex h-3 w-3 relative" title="Belum Tes Fatigue">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-3 w-3 bg-amber-500"></span>
                        </span>
                        <svg v-else xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5 text-emerald-500" title="Sudah Tes Fatigue">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.859-9.809a.75.75 0 00-1.218-.882l-3.446 4.757-1.943-1.802a.75.75 0 00-1.018 1.102l2.5 2.315a.75.75 0 001.117-.075l4-5.515z" clip-rule="evenodd" />
                        </svg>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="w-5 h-5 text-slate-500 group-hover:translate-x-1 transition-transform">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                        </svg>
                    </div>
                </Link>
 
                <!-- Action 2: Daily Quiz -->
                <Link :href="route('quiz.index')" class="w-full bg-slate-900 border border-slate-800 hover:bg-slate-800/80 text-slate-200 font-extrabold py-4 px-5 rounded-xl flex items-center justify-between transition-all active:scale-95 group block">
                    <div class="flex items-center gap-3 text-left">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6 text-amber-500">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                        </svg>
                        <div>
                            <span class="block text-sm text-slate-100">Kuis K3 Harian</span>
                            <span class="block text-[10px] text-slate-400 font-medium">Mainkan game kuis edukasi keselamatan</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <!-- Status Indicator -->
                        <span v-if="!hasCompletedDailyQuizToday" class="flex h-3 w-3 relative" title="Belum Mengerjakan Kuis Harian">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-3 w-3 bg-amber-500"></span>
                        </span>
                        <svg v-else xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5 text-emerald-500" title="Sudah Mengerjakan Kuis Harian">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.859-9.809a.75.75 0 00-1.218-.882l-3.446 4.757-1.943-1.802a.75.75 0 00-1.018 1.102l2.5 2.315a.75.75 0 001.117-.075l4-5.515z" clip-rule="evenodd" />
                        </svg>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="w-5 h-5 text-slate-500 group-hover:translate-x-1 transition-transform">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                        </svg>
                    </div>
                </Link>

                <!-- Action 3: Report Incident -->
                <button class="w-full bg-slate-900 border border-slate-800 hover:bg-slate-800/80 text-slate-200 font-extrabold py-4 px-5 rounded-xl flex items-center justify-between transition-all active:scale-95 group">
                    <div class="flex items-center gap-3 text-left">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6 text-red-500">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                        </svg>
                        <div>
                            <span class="block text-sm text-slate-100">Lapor Bahaya/Insiden</span>
                            <span class="block text-[10px] text-slate-400 font-medium">Laporkan temuan bahaya secara cepat</span>
                        </div>
                    </div>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="w-5 h-5 text-slate-500 group-hover:translate-x-1 transition-transform">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                    </svg>
                </button>
            </div>
        </div>
    </MobileAppLayout>
</template>
