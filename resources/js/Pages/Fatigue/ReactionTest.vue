<script setup>
import { ref, onUnmounted } from 'vue';
import { Head, router, Link } from '@inertiajs/vue3';

const props = defineProps({
    questionnaireStatus: {
        type: Boolean,
        required: true
    }
});

const gameState = ref('waiting'); // 'waiting', 'ready', 'active', 'finished'
const message = ref('Ketuk area di bawah untuk memulai');
const results = ref([]);
const maxAttempts = 3;

let timeoutId = null;
let startTime = 0;

const startTest = () => {
    if (results.value.length >= maxAttempts) return;
    
    gameState.value = 'ready';
    message.value = 'Tunggu warna berubah hijau...';
    
    const delay = Math.floor(Math.random() * 4000) + 2000;
    
    timeoutId = setTimeout(() => {
        gameState.value = 'active';
        message.value = 'KETUK SEKARANG!';
        startTime = Date.now();
    }, delay);
};

const handleTap = (e) => {
    e.preventDefault();

    if (gameState.value === 'waiting') {
        startTest();
        return;
    }

    if (gameState.value === 'ready') {
        clearTimeout(timeoutId);
        message.value = 'Terlalu Cepat! Ketuk untuk mengulangi.';
        gameState.value = 'waiting';
        return;
    }

    if (gameState.value === 'active') {
        const reactionTime = Date.now() - startTime;
        results.value.push(reactionTime);
        
        if (results.value.length < maxAttempts) {
            message.value = `Waktu: ${reactionTime} ms — Ketuk untuk lanjut`;
            gameState.value = 'waiting';
        } else {
            finishTest();
        }
    }
};

const finishTest = () => {
    gameState.value = 'finished';
    
    const sum = results.value.reduce((a, b) => a + b, 0);
    const average = Math.round(sum / results.value.length);
    message.value = `Selesai! Rata-rata: ${average} ms`;
    
    const isFit = props.questionnaireStatus && average < 500;

    setTimeout(() => {
        router.post(route('fatigue.store'), {
            questionnaire_status: props.questionnaireStatus,
            reaction_time_ms: average,
            is_fit: isFit
        });
    }, 1500);
};

onUnmounted(() => {
    if (timeoutId) clearTimeout(timeoutId);
});
</script>

<template>
    <Head title="Fatigue Check - Tes Reaksi" />

    <div class="min-h-screen bg-slate-950 text-slate-100 flex flex-col font-sans selection:bg-amber-500 selection:text-slate-900">
        <!-- Minimal Top Bar -->
        <header class="sticky top-0 z-50 bg-slate-900/80 backdrop-blur-md border-b border-slate-800 px-4 py-3 flex items-center justify-between shrink-0">
            <div class="flex items-center gap-3">
                <Link :href="route('fatigue.questionnaire')" class="p-1.5 text-slate-400 hover:text-amber-500 transition-colors rounded-lg hover:bg-slate-800">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                    </svg>
                </Link>
                <div>
                    <h1 class="text-sm font-bold tracking-tight text-slate-100">Tes Reaksi Mata</h1>
                    <p class="text-[10px] text-slate-400 font-medium">Percobaan {{ results.length }}/{{ maxAttempts }}</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <div v-for="i in maxAttempts" :key="i" 
                    class="h-2 w-6 rounded-full transition-all duration-300"
                    :class="i <= results.length ? 'bg-amber-500' : 'bg-slate-700'">
                </div>
            </div>
        </header>

        <!-- Main Reaction Area (Full remaining height) -->
        <main class="flex-1 flex flex-col p-4 gap-4">
            <!-- Instruction Card (shown only initially) -->
            <div v-if="gameState === 'waiting' && results.length === 0" 
                class="bg-slate-900/60 backdrop-blur border border-slate-800 rounded-2xl p-5 shrink-0">
                <div class="flex items-start gap-3">
                    <div class="h-10 w-10 rounded-xl bg-amber-500/10 flex items-center justify-center border border-amber-500/20 shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-amber-500">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-200 mb-1">Instruksi:</p>
                        <p class="text-[11px] text-slate-400 leading-relaxed">Area di bawah akan berubah menjadi <span class="text-red-400 font-bold">MERAH</span>. Saat berubah menjadi <span class="text-emerald-400 font-bold">HIJAU</span>, ketuk secepat mungkin!</p>
                    </div>
                </div>
            </div>

            <!-- Tap Zone -->
            <div 
                class="flex-1 flex flex-col items-center justify-center rounded-3xl p-6 text-center select-none touch-manipulation cursor-pointer border-2 transition-all duration-150"
                @mousedown="handleTap" @touchstart="handleTap"
                :class="{
                    'bg-slate-800/80 border-slate-700 shadow-[0_0_20px_rgba(30,41,59,0.5)]': gameState === 'waiting' || gameState === 'finished',
                    'bg-red-500 border-red-400 shadow-[0_0_40px_rgba(239,68,68,0.6)]': gameState === 'ready',
                    'bg-emerald-500 border-emerald-400 shadow-[0_0_50px_rgba(16,185,129,0.8)] scale-[1.01]': gameState === 'active'
                }"
            >
                <!-- Icon -->
                <div class="h-20 w-20 rounded-full flex items-center justify-center mb-5"
                    :class="{
                        'bg-slate-700/60 text-slate-400': gameState === 'waiting' || gameState === 'finished',
                        'bg-red-600/80 text-white animate-pulse': gameState === 'ready',
                        'bg-emerald-600/80 text-white animate-bounce': gameState === 'active'
                    }">
                    <svg v-if="gameState === 'waiting' || gameState === 'finished'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-10 h-10">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.042 21.672L13.684 16.6m0 0l-2.51 2.225.569-9.47 5.227 7.917-3.286-.672zM12 2.25V4.5m5.834.166l-1.591 1.591M20.25 10.5H18M7.757 14.743l-1.59 1.59M6 10.5H3.75m4.007-4.54l-1.59-1.59" />
                    </svg>
                    <svg v-else-if="gameState === 'ready'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-10 h-10">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-10 h-10">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.042 21.672L13.684 16.6m0 0l-2.51 2.225.569-9.47 5.227 7.917-3.286-.672zM12 2.25V4.5m5.834.166l-1.591 1.591M20.25 10.5H18M7.757 14.743l-1.59 1.59M6 10.5H3.75m4.007-4.54l-1.59-1.59" />
                    </svg>
                </div>
                
                <h1 class="text-2xl md:text-3xl font-black text-white mb-3 tracking-wide drop-shadow-lg leading-tight">
                    {{ message }}
                </h1>

                <p v-if="gameState === 'waiting' && results.length > 0" class="text-slate-400 text-xs font-medium">
                    Ketuk untuk percobaan berikutnya
                </p>
            </div>

            <!-- Results Pills (shown after at least 1 attempt) -->
            <div v-if="results.length > 0" class="shrink-0 flex justify-center gap-3 pb-2">
                <div v-for="(res, idx) in results" :key="idx" 
                    class="bg-slate-800 border border-slate-700 rounded-full px-4 py-2 text-center">
                    <p class="text-[10px] text-slate-500 font-bold uppercase">#{{ idx + 1 }}</p>
                    <p class="text-sm font-black text-amber-500">{{ res }}<span class="text-[10px] text-slate-500 ml-0.5">ms</span></p>
                </div>
            </div>
        </main>
    </div>
</template>
