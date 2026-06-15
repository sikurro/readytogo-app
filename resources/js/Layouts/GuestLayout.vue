<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="min-h-screen flex flex-col justify-center items-center px-4 py-10 bg-slate-950 text-slate-100 font-sans relative overflow-hidden selection:bg-amber-500 selection:text-slate-900">
        <!-- Background Light Glows -->
        <div class="absolute top-[-20%] left-[-10%] w-[500px] h-[500px] bg-amber-500/5 rounded-full blur-[150px] pointer-events-none"></div>
        <div class="absolute bottom-[-10%] right-[-10%] w-[400px] h-[400px] bg-orange-600/5 rounded-full blur-[130px] pointer-events-none"></div>

        <div class="relative z-10">
            <Link href="/" class="flex flex-col items-center gap-2 hover:opacity-90 transition-opacity">
                <span class="bg-gradient-to-r from-amber-500 to-orange-500 text-slate-950 font-black px-4 py-2 rounded-xl text-lg tracking-widest shadow-lg shadow-amber-500/15">R2G</span>
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Ready To GO</span>
            </Link>
        </div>

        <div
            class="w-full max-w-md mt-6 px-6 py-5 bg-slate-900/80 backdrop-blur-md border border-slate-800/80 shadow-2xl shadow-amber-500/5 overflow-hidden rounded-2xl relative z-10"
        >
            <slot />
        </div>
    </div>
</template>
