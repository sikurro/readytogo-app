<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    status: {
        type: Number,
        required: true,
    },
});

const errorDetails = computed(() => {
    return {
        503: {
            code: '503',
            title: 'Layanan Tidak Tersedia',
            description: 'Maaf, sistem kami sedang dalam pemeliharaan berkala atau mengalami kelebihan beban. Silakan coba beberapa saat lagi.',
            emoji: '🛠️',
        },
        500: {
            code: '500',
            title: 'Kesalahan Server Internal',
            description: 'Terjadi kesalahan sistem internal pada server kami. Tim teknis sedang berupaya memperbaikinya.',
            emoji: '⚙️',
        },
        403: {
            code: '403',
            title: 'Akses Dilarang',
            description: 'Maaf, Anda tidak memiliki izin atau hak akses untuk membuka halaman ini.',
            emoji: '🚫',
        },
        404: {
            code: '404',
            title: 'Halaman Tidak Ditemukan',
            description: 'Maaf, halaman yang Anda cari tidak tersedia, telah dihapus, atau dipindahkan ke alamat lain.',
            emoji: '🔍',
        },
    }[props.status] || {
        code: props.status.toString(),
        title: 'Terjadi Kesalahan',
        description: 'Maaf, terjadi kesalahan tak terduga saat memproses permintaan Anda.',
        emoji: '⚠️',
    };
});
</script>

<template>
    <Head :title="errorDetails.title" />

    <div class="min-h-screen flex flex-col justify-center items-center px-4 py-10 bg-slate-950 text-slate-100 font-sans relative overflow-hidden selection:bg-amber-500 selection:text-slate-900">
        <!-- Background Light Glows -->
        <div class="absolute top-[-20%] left-[-10%] w-[500px] h-[500px] bg-amber-500/5 rounded-full blur-[150px] pointer-events-none"></div>
        <div class="absolute bottom-[-10%] right-[-10%] w-[400px] h-[400px] bg-orange-600/5 rounded-full blur-[130px] pointer-events-none"></div>

        <!-- Error Card -->
        <div class="w-full max-w-md bg-slate-900/80 backdrop-blur-md border border-slate-800/80 shadow-2xl shadow-amber-500/5 p-8 rounded-2xl text-center relative z-10">
            <!-- Icon/Emoji Wrapper with Pulse Glow -->
            <div class="w-20 h-20 mx-auto bg-amber-500/10 rounded-2xl flex items-center justify-center border border-amber-500/20 text-4xl shadow-lg shadow-amber-500/5 relative mb-6">
                <span class="animate-ping absolute inset-0 rounded-2xl bg-amber-500/5 opacity-75"></span>
                <span class="relative">{{ errorDetails.emoji }}</span>
            </div>

            <!-- Error Code -->
            <h1 class="text-6xl font-black bg-gradient-to-r from-amber-400 via-amber-500 to-orange-500 bg-clip-text text-transparent tracking-tight">
                {{ errorDetails.code }}
            </h1>

            <!-- Error Title -->
            <h2 class="text-xl font-bold text-slate-100 mt-4 leading-tight">
                {{ errorDetails.title }}
            </h2>

            <!-- Error Description -->
            <p class="text-xs text-slate-400 mt-3 leading-relaxed">
                {{ errorDetails.description }}
            </p>

            <!-- Action Button -->
            <div class="mt-8">
                <Link
                    :href="$page.props.auth?.user ? route('dashboard') : '/'"
                    class="inline-flex w-full justify-center items-center gap-2 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-slate-950 font-black py-3 px-4 rounded-xl shadow-lg shadow-amber-500/15 transition-all hover:scale-[1.01] active:scale-[0.99] uppercase text-xs tracking-wider"
                >
                    Kembali Ke Beranda
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg>
                </Link>
            </div>
        </div>

        <!-- Footer -->
        <div class="mt-8 text-[10px] text-slate-600 font-semibold tracking-wider uppercase relative z-10">
            Ready To GO &bull; Safety & Health
        </div>
    </div>
</template>
