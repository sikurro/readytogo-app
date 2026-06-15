<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
    laravelVersion: {
        type: String,
        required: true,
    },
    phpVersion: {
        type: String,
        required: true,
    },
});
</script>

<template>
    <Head title="Selamat Datang di Ready To GO" />

    <div class="min-h-screen bg-slate-950 text-slate-100 flex flex-col font-sans relative overflow-hidden selection:bg-amber-500 selection:text-slate-900">
        <!-- Background Light Glows -->
        <div class="absolute top-[-20%] left-[-10%] w-[600px] h-[600px] bg-amber-500/5 rounded-full blur-[150px] pointer-events-none"></div>
        <div class="absolute bottom-[-10%] right-[-10%] w-[500px] h-[500px] bg-orange-600/5 rounded-full blur-[130px] pointer-events-none"></div>

        <!-- Header -->
        <header class="w-full max-w-7xl mx-auto px-6 py-5 flex items-center justify-between relative z-10">
            <div class="flex items-center gap-2.5">
                <span class="bg-gradient-to-r from-amber-500 to-orange-500 text-slate-950 font-black px-3 py-1.5 rounded-lg text-sm tracking-wider shadow-lg shadow-amber-500/10">R2G</span>
                <h1 class="text-lg font-bold tracking-tight text-slate-100">Ready To GO</h1>
            </div>

            <div v-if="canLogin" class="flex items-center gap-4">
                <Link
                    v-if="$page.props.auth.user"
                    :href="route('dashboard')"
                    class="bg-slate-900 hover:bg-slate-800 text-slate-200 font-bold py-2 px-5 rounded-xl border border-slate-800 transition-all duration-200 text-xs tracking-wide shadow-sm"
                >
                    Dashboard
                </Link>

                <template v-else>
                    <Link
                        :href="route('login')"
                        class="bg-gradient-to-r from-amber-500 to-orange-500 text-slate-950 font-extrabold py-2.5 px-5 rounded-xl transition-all duration-200 hover:opacity-95 text-xs tracking-wider uppercase shadow-md shadow-amber-500/10"
                    >
                        Masuk
                    </Link>
                </template>
            </div>
        </header>

        <!-- Main Body -->
        <main class="flex-1 flex flex-col items-center justify-center max-w-5xl mx-auto px-6 py-12 relative z-10 w-full">
            <!-- Hero Badge -->
            <div class="inline-flex items-center gap-2 bg-slate-900/80 border border-slate-800 px-3.5 py-1.5 rounded-full text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-6 backdrop-blur-sm">
                <span class="flex h-2 w-2 relative">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-amber-500"></span>
                </span>
                Occupational Health & Safety (K3) Hub
            </div>

            <!-- Hero Headline -->
            <div class="text-center max-w-3xl space-y-6">
                <h2 class="text-4xl sm:text-6xl font-black tracking-tight leading-none text-slate-100">
                    Kelola Kesiapan Kerja & Keselamatan Bersama 
                    <span class="bg-gradient-to-r from-amber-400 via-amber-500 to-orange-500 bg-clip-text text-transparent block mt-2">Ready To GO</span>
                </h2>
                <p class="text-slate-400 text-sm sm:text-base max-w-xl mx-auto font-medium leading-relaxed">
                    Aplikasi mobile-first terintegrasi untuk melacak tingkat kelelahan (*fatigue*), menyelesaikan kuis harian keselamatan K3, dan memantau status kesiapan bekerja kru di lapangan secara berkala.
                </p>
            </div>

            <!-- Hero CTA -->
            <div class="mt-8 flex justify-center w-full">
                <Link
                    v-if="$page.props.auth.user"
                    :href="route('dashboard')"
                    class="inline-flex items-center gap-2 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-slate-950 font-black py-3.5 px-8 rounded-2xl shadow-xl shadow-amber-500/15 transition-all hover:scale-[1.02] active:scale-[0.98] uppercase text-xs tracking-wider"
                >
                    Masuk ke Dashboard
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg>
                </Link>
                <Link
                    v-else
                    :href="route('login')"
                    class="inline-flex items-center gap-2 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-slate-950 font-black py-3.5 px-8 rounded-2xl shadow-xl shadow-amber-500/15 transition-all hover:scale-[1.02] active:scale-[0.98] uppercase text-xs tracking-wider"
                >
                    Mulai Sesi Login
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg>
                </Link>
            </div>

            <!-- Features Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-16 w-full max-w-4xl">
                <!-- Feature 1: Kuis K3 -->
                <div class="bg-slate-900/60 backdrop-blur-md border border-slate-800/80 rounded-2xl p-6 hover:border-amber-500/30 transition-all duration-200 group">
                    <div class="w-12 h-12 rounded-xl bg-amber-500/10 flex items-center justify-center border border-amber-500/20 text-xl group-hover:bg-amber-500/20 transition-colors">🎯</div>
                    <h3 class="text-base font-bold text-slate-200 mt-4">Kuis Harian & Event K3</h3>
                    <p class="text-xs text-slate-400 mt-2 leading-relaxed">
                        Selesaikan tantangan kuis harian maupun kuis event untuk menguji pemahaman keselamatan kerja Anda dan dapatkan skor terbaik Anda.
                    </p>
                </div>

                <!-- Feature 2: Fatigue Check -->
                <div class="bg-slate-900/60 backdrop-blur-md border border-slate-800/80 rounded-2xl p-6 hover:border-amber-500/30 transition-all duration-200 group">
                    <div class="w-12 h-12 rounded-xl bg-amber-500/10 flex items-center justify-center border border-amber-500/20 text-xl group-hover:bg-amber-500/20 transition-colors">📋</div>
                    <h3 class="text-base font-bold text-slate-200 mt-4">Uji Fatigue & Reaksi Cepat</h3>
                    <p class="text-xs text-slate-400 mt-2 leading-relaxed">
                        Evaluasi tingkat kelelahan fisik melalui kuesioner dan uji waktu reaksi visual sebelum memulai pekerjaan berisiko tinggi.
                    </p>
                </div>

                <!-- Feature 3: Lapor Insiden -->
                <div class="bg-slate-900/60 backdrop-blur-md border border-slate-800/80 rounded-2xl p-6 hover:border-amber-500/30 transition-all duration-200 group">
                    <div class="w-12 h-12 rounded-xl bg-amber-500/10 flex items-center justify-center border border-amber-500/20 text-xl group-hover:bg-amber-500/20 transition-colors">🚨</div>
                    <h3 class="text-base font-bold text-slate-200 mt-4">Pelaporan Insiden Cepat</h3>
                    <p class="text-xs text-slate-400 mt-2 leading-relaxed">
                        Laporkan bahaya, kecelakaan kerja, atau kejadian hampir celaka secara langsung dari smartphone untuk respon penanganan yang cepat.
                    </p>
                </div>

                <!-- Feature 4: Leaderboard & Rekor -->
                <div class="bg-slate-900/60 backdrop-blur-md border border-slate-800/80 rounded-2xl p-6 hover:border-amber-500/30 transition-all duration-200 group">
                    <div class="w-12 h-12 rounded-xl bg-amber-500/10 flex items-center justify-center border border-amber-500/20 text-xl group-hover:bg-amber-500/20 transition-colors">🏆</div>
                    <h3 class="text-base font-bold text-slate-200 mt-4">Leaderboard & Papan Peringkat</h3>
                    <p class="text-xs text-slate-400 mt-2 leading-relaxed">
                        Tingkatkan kepatuhan K3 Anda secara proaktif dan bersaing dengan rekan kerja Anda untuk menempati posisi teratas di papan skor.
                    </p>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="w-full max-w-7xl mx-auto px-6 py-6 border-t border-slate-900 flex flex-col sm:flex-row items-center justify-between text-xs text-slate-500 relative z-10 mt-auto">
            <p>&copy; {{ new Date().getFullYear() }} Ready To GO. All rights reserved.</p>
            <p class="mt-2 sm:mt-0 font-medium">Laravel v{{ laravelVersion }} (PHP v{{ phpVersion }})</p>
        </footer>
    </div>
</template>
