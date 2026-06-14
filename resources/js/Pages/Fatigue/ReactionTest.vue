<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    questionnaireStatus: {
        type: Boolean,
        required: true
    }
});

const gameState = ref('waiting'); // 'waiting', 'ready', 'active', 'finished'
const message = ref('Ketuk layar untuk memulai tes');
const results = ref([]);
const maxAttempts = 3;

let timeoutId = null;
let startTime = 0;

const startTest = () => {
    if (results.value.length >= maxAttempts) return;
    
    gameState.value = 'ready';
    message.value = 'Tunggu warna hijau...';
    
    // Random delay between 2 to 6 seconds
    const delay = Math.floor(Math.random() * 4000) + 2000;
    
    timeoutId = setTimeout(() => {
        gameState.value = 'active';
        message.value = 'KETUK SEKARANG!';
        startTime = Date.now();
    }, delay);
};

const handleTap = (e) => {
    // Prevent default touch behavior if needed
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

    <div class="h-screen w-screen flex flex-col select-none overflow-hidden touch-manipulation"
         @mousedown="handleTap" @touchstart="handleTap"
         :class="{
             'bg-slate-800': gameState === 'waiting' || gameState === 'finished',
             'bg-red-600': gameState === 'ready',
             'bg-emerald-500': gameState === 'active'
         }">
         
        <div class="flex-1 flex flex-col items-center justify-center p-6 text-center">
            <h1 class="text-3xl md:text-5xl font-bold text-white mb-6 tracking-wide drop-shadow-md">
                {{ message }}
            </h1>
            
            <div v-if="gameState === 'waiting' && results.length === 0" class="text-slate-300 max-w-md mx-auto text-lg mt-8">
                <p>Instruksi:</p>
                <p>Layar akan menjadi <b>MERAH</b>.</p>
                <p>Saat layar berubah menjadi <b>HIJAU</b>, segera ketuk layar secepat mungkin!</p>
            </div>

            <div v-if="results.length > 0 && gameState !== 'finished'" class="mt-12">
                <p class="text-white opacity-80 mb-2">Percobaan {{ results.length }} / {{ maxAttempts }}</p>
                <div class="flex justify-center space-x-2">
                    <div v-for="(res, idx) in results" :key="idx" class="px-3 py-1 bg-white/20 rounded-full text-white text-sm">
                        {{ res }} ms
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
