<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import MobileAppLayout from '@/Layouts/MobileAppLayout.vue';

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
    
    // Random delay between 2 to 6 seconds
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
        // Tapped too early
        clearTimeout(timeoutId);
        message.value = 'Terlalu Cepat! Ketuk untuk mengulangi.';
        gameState.value = 'waiting';
        return;
    }

    if (gameState.value === 'active') {
        // Valid tap
        const reactionTime = Date.now() - startTime;
        results.value.push(reactionTime);
        
        if (results.value.length < maxAttempts) {
            message.value = `Waktu Anda: ${reactionTime} ms. Ketuk untuk lanjut (${results.value.length}/${maxAttempts})`;
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
    
    // Determine if fit (threshold < 500ms)
    const isFit = props.questionnaireStatus && average < 500;

    // Send results to server
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

    <MobileAppLayout>
        <div class="space-y-6 flex flex-col h-full min-h-[70vh]">
            <!-- Header section -->
            <div class="bg-gradient-to-br from-slate-900 to-slate-800 border border-slate-800 rounded-2xl p-5 shadow-xl shrink-0">
                <div class="flex items-center gap-4">
                    <div class="h-12 w-12 rounded-xl bg-slate-800 flex items-center justify-center border border-slate-700 shadow-inner">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6 text-amber-500">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-slate-100 tracking-tight">Tes Reaksi Mata</h2>
                        <p class="text-[11px] text-slate-400 font-medium mt-0.5 leading-relaxed">Uji kecepatan reaksi Anda sebelum bertugas.</p>
                    </div>
                </div>
            </div>

            <!-- Reaction Area -->
            <div 
                class="flex-1 flex flex-col items-center justify-center rounded-3xl p-6 text-center select-none touch-manipulation transition-all duration-100 cursor-pointer border-4"
                @mousedown="handleTap" @touchstart="handleTap"
                :class="{
                    'bg-slate-800 border-slate-700 shadow-[0_0_15px_rgba(30,41,59,0.5)]': gameState === 'waiting' || gameState === 'finished',
                    'bg-red-500 border-red-400 shadow-[0_0_30px_rgba(239,68,68,0.6)]': gameState === 'ready',
                    'bg-emerald-500 border-emerald-400 shadow-[0_0_40px_rgba(16,185,129,0.8)] scale-[1.02]': gameState === 'active'
                }"
            >
                <div class="h-16 w-16 rounded-full flex items-center justify-center mb-6"
                    :class="{
                        'bg-slate-700 text-slate-400': gameState === 'waiting' || gameState === 'finished',
                        'bg-red-600 text-red-200 animate-pulse': gameState === 'ready',
                        'bg-emerald-600 text-emerald-100 animate-bounce': gameState === 'active'
                    }">
                    <svg v-if="gameState === 'waiting' || gameState === 'finished'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-8 h-8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.042 21.672L13.684 16.6m0 0l-2.51 2.225.569-9.47 5.227 7.917-3.286-.672zM12 2.25V4.5m5.834.166l-1.591 1.591M20.25 10.5H18M7.757 14.743l-1.59 1.59M6 10.5H3.75m4.007-4.54l-1.59-1.59" />
                    </svg>
                    <svg v-else-if="gameState === 'ready'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-8 h-8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-8 h-8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.042 21.672L13.684 16.6m0 0l-2.51 2.225.569-9.47 5.227 7.917-3.286-.672zM12 2.25V4.5m5.834.166l-1.591 1.591M20.25 10.5H18M7.757 14.743l-1.59 1.59M6 10.5H3.75m4.007-4.54l-1.59-1.59" />
                    </svg>
                </div>
                
                <h1 class="text-xl md:text-3xl font-black text-white mb-2 tracking-wide drop-shadow-md">
                    {{ message }}
                </h1>
                
                <div v-if="gameState === 'waiting' && results.length === 0" class="text-slate-400 max-w-xs mx-auto text-sm mt-4 font-medium">
                    <p class="mb-1">Layar akan berubah menjadi <span class="text-red-400 font-bold">MERAH</span>.</p>
                    <p>Saat layar menjadi <span class="text-emerald-400 font-bold">HIJAU</span>, ketuk layar secepat mungkin!</p>
                </div>
            </div>

            <!-- Attempts Indicator -->
            <div class="shrink-0 flex flex-col items-center justify-center py-2">
                <p class="text-slate-400 text-xs font-bold uppercase tracking-widest mb-3">Percobaan ({{ results.length }}/{{ maxAttempts }})</p>
                <div class="flex justify-center space-x-3">
                    <div v-for="i in maxAttempts" :key="i" 
                        class="w-16 py-1.5 rounded-full text-center text-xs font-bold border"
                        :class="i <= results.length ? 'bg-slate-700 border-slate-600 text-slate-200' : 'bg-slate-800 border-slate-700/50 text-slate-600'">
                        <span v-if="i <= results.length">{{ results[i-1] }} ms</span>
                        <span v-else>-</span>
                    </div>
                </div>
            </div>
        </div>
    </MobileAppLayout>
</template>
