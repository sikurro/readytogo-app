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
            text: 'Luar Biasa! Nilai Sempurna',
            colorClass: 'text-transparent bg-clip-text bg-gradient-to-r from-amber-400 via-orange-400 to-yellow-500 drop-shadow',
            borderColor: 'border-amber-500/50',
            icon: '🏆'
        };
    } else if (p >= 60) {
        return {
            text: 'Selamat! Anda Lulus Kuis',
            colorClass: 'text-transparent bg-clip-text bg-gradient-to-r from-emerald-400 to-teal-400',
            borderColor: 'border-emerald-500/50',
            icon: '🎉'
        };
    } else {
        return {
            text: 'Semangat! Coba Lagi Besok',
            colorClass: 'text-transparent bg-clip-text bg-gradient-to-r from-slate-300 to-slate-100',
            borderColor: 'border-slate-800',
            icon: '💪'
        };
    }
});

</script>

<template>
    <Head title="Ringkasan Kuis" />

    <MobileAppLayout>
        <div class="flex flex-col items-center justify-center py-4">
            
            <!-- Celebration Card -->
            <div class="w-full bg-slate-900/80 backdrop-blur border border-slate-800 rounded-3xl p-6 shadow-xl text-center mb-6 transform transition-all duration-300">
                
                <div v-if="is_demo" class="mb-4 inline-block bg-amber-500/10 border border-amber-500/30 text-amber-500 text-[10px] font-black px-3 py-1 rounded-full uppercase tracking-widest">
                    Mode Simulasi (Demo)
                </div>

                <div class="text-5xl mb-4 animate-bounce duration-1000">{{ celebration.icon }}</div>
                
                <h1 class="text-2xl md:text-3xl font-black mb-6 leading-tight" :class="celebration.colorClass">
                    {{ celebration.text }}
                </h1>

                <div class="grid grid-cols-2 gap-4 mb-2">
                    <!-- Score Badge -->
                    <div class="col-span-2 flex justify-center py-2">
                        <div class="relative w-36 h-36 rounded-full flex flex-col items-center justify-center bg-slate-950 border-4 border-slate-800 shadow-[0_0_20px_rgba(245,158,11,0.15)] overflow-hidden">
                            <div class="absolute inset-0 bg-gradient-to-tr from-amber-500/5 to-transparent"></div>
                            <span class="text-[11px] tracking-wider text-slate-400 font-bold uppercase relative z-10">Skor Anda</span>
                            <span class="text-5xl font-black text-amber-500 relative z-10 mt-1 drop-shadow-[0_2px_10px_rgba(245,158,11,0.3)]">{{ attempt.score }}</span>
                        </div>
                    </div>

                    <!-- Correct Answers -->
                    <div class="bg-slate-950/50 border border-slate-800/80 rounded-2xl p-4 flex flex-col items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-emerald-400 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="text-2xl font-black text-slate-100">{{ attempt.correct_answers }}</span>
                        <span class="text-slate-400 text-xs font-medium mt-0.5">Jawaban Benar</span>
                    </div>

                    <!-- Incorrect Answers -->
                    <div class="bg-slate-950/50 border border-slate-800/80 rounded-2xl p-4 flex flex-col items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-rose-500 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="text-2xl font-black text-slate-100">{{ incorrect_answers }}</span>
                        <span class="text-slate-400 text-xs font-medium mt-0.5">Jawaban Salah</span>
                    </div>

                    <!-- Time Taken -->
                    <div class="col-span-2 bg-slate-950/50 border border-slate-800/80 rounded-2xl p-4 flex items-center justify-center space-x-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div class="flex flex-col text-left">
                            <span class="text-sm font-bold text-slate-200">{{ formattedTime }}</span>
                            <span class="text-slate-400 text-xs font-medium">Waktu Pengerjaan</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="w-full space-y-3">
                <Link 
                    :href="route('quiz.index')"
                    class="flex items-center justify-center gap-2 w-full bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-slate-950 font-black py-4 px-6 rounded-2xl text-center shadow-lg shadow-amber-500/10 transition-all duration-200 active:scale-[0.98] text-base"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                    </svg>
                    Kembali ke Halaman Kuis
                </Link>
                
                <Link 
                    v-if="!is_demo"
                    :href="route('quiz.leaderboard')"
                    class="flex items-center justify-center gap-2 w-full bg-slate-900 hover:bg-slate-800 text-slate-100 border border-slate-800 font-extrabold py-4 px-6 rounded-2xl text-center shadow-lg transition-all duration-200 active:scale-[0.98] text-base"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-amber-500">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 013 3h-15a3 3 0 013-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.75a1.125 1.125 0 01-1.125-1.125V11.25M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.75A1.125 1.125 0 0110.5 13.125V11.25m-1.25 0h2.5m-2.5 0a3.75 3.75 0 112.5 0M9 3.75h6m-6 0a1.5 1.5 0 00-1.5 1.5M9 3.75V5.25m6-1.5a1.5 1.5 0 011.5 1.5M15 3.75V5.25m-7.5 1.5h9" />
                    </svg>
                    Lihat Klasemen
                </Link>
            </div>
            
        </div>
    </MobileAppLayout>
</template>
