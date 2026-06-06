<script setup>
import MobileAppLayout from '@/Layouts/MobileAppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    auth: Object,
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
                    <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Status Bugar (K3)</span>
                    <div class="mt-2 flex items-center gap-2">
                        <span class="h-3 w-3 rounded-full bg-amber-500 animate-pulse"></span>
                        <span class="text-xs font-bold text-amber-500 uppercase tracking-wider">Belum Tes</span>
                    </div>
                </div>
            </div>

            <!-- Warning Alert: Mandatory Fatigue Check -->
            <div class="bg-amber-500/10 border border-amber-500/30 rounded-xl p-4 flex gap-3 items-start">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-amber-500 shrink-0 mt-0.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                </svg>
                <div>
                    <h4 class="text-xs font-bold text-amber-500">Fatigue Check Wajib!</h4>
                    <p class="text-[11px] text-slate-300 mt-1 leading-relaxed">Anda diwajibkan untuk melakukan pemeriksaan kesiapan kerja (reaksi mata) sebelum turun ke lapangan hari ini.</p>
                </div>
            </div>

            <!-- Quick Action Buttons (Touch Friendly) -->
            <div class="space-y-4">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest px-1">Menu Operasional K3</h3>
                
                <!-- Action 1: Fatigue Test -->
                <button class="w-full bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-slate-950 font-extrabold py-4 px-5 rounded-xl flex items-center justify-between shadow-lg shadow-amber-500/10 transition-all active:scale-95 group">
                    <div class="flex items-center gap-3 text-left">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0110 21a3.745 3.745 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0114 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z" />
                        </svg>
                        <div>
                            <span class="block text-sm">Mulai Fatigue Check</span>
                            <span class="block text-[10px] opacity-75 font-semibold">Uji reaksi mata sebelum bertugas</span>
                        </div>
                    </div>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="w-5 h-5 group-hover:translate-x-1 transition-transform">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                    </svg>
                </button>

                <!-- Action 2: Daily Quiz -->
                <button class="w-full bg-slate-900 border border-slate-800 hover:bg-slate-800/80 text-slate-200 font-extrabold py-4 px-5 rounded-xl flex items-center justify-between transition-all active:scale-95 group">
                    <div class="flex items-center gap-3 text-left">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6 text-amber-500">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                        </svg>
                        <div>
                            <span class="block text-sm text-slate-100">Kuis K3 Harian</span>
                            <span class="block text-[10px] text-slate-400 font-medium">Mainkan game kuis edukasi keselamatan</span>
                        </div>
                    </div>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="w-5 h-5 text-slate-500 group-hover:translate-x-1 transition-transform">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                    </svg>
                </button>

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
