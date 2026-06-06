<script setup>
import AdminDashboardLayout from '@/Layouts/AdminDashboardLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    auth: Object,
});
</script>

<template>
    <Head title="Admin Dashboard - Ready To GO" />

    <AdminDashboardLayout>
        <template #header>
            <span>Ringkasan Operasional Keselamatan (K3)</span>
        </template>

        <div class="space-y-6">
            <!-- Alert Info -->
            <div class="bg-amber-500/10 border border-amber-500/20 text-amber-500 rounded-xl p-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 111.083 1.083l-.042.02a.75.75 0 01-1.083-1.083zM12 20.25a8.25 8.25 0 100-16.5 8.25 8.25 0 000 16.5z" />
                    </svg>
                    <span class="text-xs font-semibold">Selamat datang di Panel Administrasi R2G. Pantau data kesehatan kerja, kuis harian, dan insiden maritim pelabuhan.</span>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <!-- Stat Card 1 -->
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 flex flex-col justify-between shadow-xl">
                    <span class="text-xs text-slate-400 font-bold uppercase tracking-wider">Total Laporan Insiden</span>
                    <div class="mt-4 flex items-baseline justify-between">
                        <span class="text-3xl font-extrabold text-slate-100">0</span>
                        <span class="text-[10px] bg-slate-800 text-slate-400 font-bold px-2 py-0.5 rounded-full uppercase">Hari ini</span>
                    </div>
                </div>

                <!-- Stat Card 2 -->
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 flex flex-col justify-between shadow-xl">
                    <span class="text-xs text-slate-400 font-bold uppercase tracking-wider">Bugar (Fit to Work)</span>
                    <div class="mt-4 flex items-baseline justify-between">
                        <span class="text-3xl font-extrabold text-emerald-400">1</span>
                        <span class="text-[10px] bg-emerald-500/10 text-emerald-400 font-bold px-2 py-0.5 rounded-full uppercase">100% Fit</span>
                    </div>
                </div>

                <!-- Stat Card 3 -->
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 flex flex-col justify-between shadow-xl">
                    <span class="text-xs text-slate-400 font-bold uppercase tracking-wider">Kelelahan (Fatigue)</span>
                    <div class="mt-4 flex items-baseline justify-between">
                        <span class="text-3xl font-extrabold text-red-500">0</span>
                        <span class="text-[10px] bg-red-500/10 text-red-400 font-bold px-2 py-0.5 rounded-full uppercase">0% Lelah</span>
                    </div>
                </div>

                <!-- Stat Card 4 -->
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 flex flex-col justify-between shadow-xl">
                    <span class="text-xs text-slate-400 font-bold uppercase tracking-wider">Ikut Kuis Harian</span>
                    <div class="mt-4 flex items-baseline justify-between">
                        <span class="text-3xl font-extrabold text-blue-400">0</span>
                        <span class="text-[10px] bg-slate-800 text-slate-400 font-bold px-2 py-0.5 rounded-full uppercase">Bulan Ini</span>
                    </div>
                </div>
            </div>

            <!-- Content Area (Incidents & Fatigue Checks) -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Recent Incidents Table Card -->
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                        <h3 class="font-bold text-slate-200">Laporan Bahaya / Insiden Terkini</h3>
                        <button class="text-xs text-amber-500 font-bold hover:underline">Lihat Semua</button>
                    </div>
                    <div class="text-center py-8 text-slate-500 text-xs">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-10 h-10 mx-auto text-slate-600 mb-2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                        </svg>
                        Belum ada laporan insiden hari ini.
                    </div>
                </div>

                <!-- Daily Fatigue Logs Card -->
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                        <h3 class="font-bold text-slate-200">Log Fatigue Check Terkini</h3>
                        <button class="text-xs text-amber-500 font-bold hover:underline">Lihat Semua</button>
                    </div>
                    <div class="text-center py-8 text-slate-500 text-xs">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-10 h-10 mx-auto text-slate-600 mb-2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0110 21a3.745 3.745 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0114 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z" />
                        </svg>
                        Belum ada petugas yang melakukan tes reaksi mandiri.
                    </div>
                </div>
            </div>
        </div>
    </AdminDashboardLayout>
</template>
