<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import MobileAppLayout from '@/Layouts/MobileAppLayout.vue';

const props = defineProps({
    attempt: Object,
    quiz: Object,
    total_questions: Number,
    incorrect_answers: Number,
    is_demo: Boolean
});

// Calculate percentage
const percentage = computed(() => {
    if (props.total_questions === 0) return 0;
    return (props.attempt.correct_answers / props.total_questions) * 100;
});

// Format time
const formattedTime = computed(() => {
    const ms = props.attempt.time_ms;
    const totalSeconds = Math.floor(ms / 1000);
    const minutes = Math.floor(totalSeconds / 60);
    const seconds = totalSeconds % 60;
    
    if (minutes > 0) {
        return `${minutes} menit ${seconds} detik`;
    }
    return `${seconds} detik`;
});

// Dynamic celebration message and styles
const celebration = computed(() => {
    const p = percentage.value;
    if (p >= 100) {
        return {
            text: `Selamat Anda mendapatkan Nilai ${props.attempt.score}`,
            colorClass: 'text-yellow-400 drop-shadow-md',
            borderColor: 'border-yellow-400',
            icon: '🏆'
        };
    } else if (p >= 60) {
        return {
            text: `Selamat Anda mendapatkan Nilai ${props.attempt.score}`,
            colorClass: 'text-green-400',
            borderColor: 'border-green-400',
            icon: '🎉'
        };
    } else {
        return {
            text: `Semangat! Anda mendapatkan Nilai ${props.attempt.score}. Coba lagi besok untuk hasil lebih baik!`,
            colorClass: 'text-gray-300',
            borderColor: 'border-gray-500',
            icon: '💪'
        };
    }
});
</script>

<template>
    <Head title="Ringkasan Kuis" />

    <MobileAppLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Ringkasan Pengerjaan Kuis</h2>
        </template>

        <div class="py-12 bg-gray-900 min-h-screen text-white">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 flex flex-col items-center justify-center min-h-[60vh]">
                
                <!-- Celebration Card -->
                <div class="w-full max-w-lg bg-gray-800 rounded-3xl p-8 shadow-2xl border-4 text-center mb-8 transform transition-all duration-500 hover:scale-105"
                     :class="celebration.borderColor">
                    
                    <div v-if="is_demo" class="mb-4 inline-block bg-yellow-600 text-white text-xs font-bold px-3 py-1 rounded-full uppercase tracking-widest">
                        Mode Simulasi (Demo)
                    </div>

                    <div class="text-6xl mb-6">{{ celebration.icon }}</div>
                    
                    <h1 class="text-3xl md:text-4xl font-extrabold mb-8 leading-tight" :class="celebration.colorClass">
                        {{ celebration.text }}
                    </h1>

                    <div class="grid grid-cols-2 gap-6 mb-8">
                        <!-- Score Badge -->
                        <div class="col-span-2 flex justify-center">
                            <div class="w-32 h-32 rounded-full border-8 border-gray-700 bg-gray-900 flex flex-col items-center justify-center shadow-inner">
                                <span class="text-gray-400 text-sm font-bold uppercase tracking-wider">Skor</span>
                                <span class="text-4xl font-black text-white">{{ attempt.score }}</span>
                            </div>
                        </div>

                        <!-- Correct Answers -->
                        <div class="bg-gray-700 rounded-2xl p-4 shadow flex flex-col items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-green-400 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="text-3xl font-bold">{{ attempt.correct_answers }}</span>
                            <span class="text-gray-400 text-sm">Jawaban Benar</span>
                        </div>

                        <!-- Incorrect Answers -->
                        <div class="bg-gray-700 rounded-2xl p-4 shadow flex flex-col items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-red-400 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="text-3xl font-bold">{{ incorrect_answers }}</span>
                            <span class="text-gray-400 text-sm">Jawaban Salah</span>
                        </div>

                        <!-- Time Taken -->
                        <div class="col-span-2 bg-gray-700 rounded-2xl p-4 shadow flex items-center justify-center space-x-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div class="flex flex-col text-left">
                                <span class="text-xl font-bold">{{ formattedTime }}</span>
                                <span class="text-gray-400 text-sm">Waktu Pengerjaan</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="w-full max-w-lg space-y-4">
                    <Link 
                        :href="route('quiz.index')"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 px-6 rounded-xl text-center shadow-lg transition duration-200 ease-in-out block text-lg"
                    >
                        Kembali ke Halaman Kuis
                    </Link>
                    
                    <Link 
                        v-if="!is_demo"
                        :href="route('quiz.leaderboard')"
                        class="w-full bg-gray-700 hover:bg-gray-600 text-white font-bold py-4 px-6 rounded-xl text-center shadow-lg transition duration-200 ease-in-out block text-lg"
                    >
                        Lihat Klasemen
                    </Link>
                </div>
                
            </div>
        </div>
    </MobileAppLayout>
</template>
