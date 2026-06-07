<script setup>
import { Head, Link } from '@inertiajs/vue3';
import MobileAppLayout from '@/Layouts/MobileAppLayout.vue';

defineProps({
    quizzes: Array,
});
</script>

<template>
    <Head title="Kuis Harian K3" />

    <MobileAppLayout>
        <div class="py-4">
            <h2 class="text-2xl font-black text-slate-100 mb-6 flex items-center gap-2">
                <span class="w-2 h-6 bg-gradient-to-b from-amber-500 to-orange-500 rounded-full"></span>
                Kuis Harian K3
            </h2>
            
            <div v-if="quizzes && quizzes.length > 0" class="space-y-6">
                <div v-for="quiz in quizzes" :key="quiz.id" class="bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-xl hover:border-slate-700/80 transition-all relative overflow-hidden">
                    
                    <div v-if="quiz.is_daily_quiz" class="absolute top-0 right-0 bg-gradient-to-r from-amber-500 to-orange-500 text-slate-950 font-black px-3 py-1.5 text-[10px] tracking-widest uppercase rounded-bl-xl shadow-md">
                        KUIS HARIAN
                    </div>
                    <div v-else class="absolute top-0 right-0 bg-gradient-to-r from-purple-500 to-indigo-500 text-white font-black px-3 py-1.5 text-[10px] tracking-widest uppercase rounded-bl-xl shadow-md">
                        EVENT QUIZ
                    </div>

                    <h3 class="text-lg font-bold text-slate-100 mb-1 pr-24 leading-snug">{{ quiz.title }}</h3>
                    <p class="text-slate-400 text-xs mb-4">Tema: {{ quiz.theme }}</p>
                    
                    <div v-if="quiz.has_played_today" class="bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 px-4 py-3 rounded-xl mb-4 text-xs font-semibold flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0110 21a3.745 3.745 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0114 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z" />
                        </svg>
                        Anda telah menyelesaikan kuis ini hari ini.
                    </div>

                    <div class="flex flex-col space-y-2.5 mt-4">
                        <Link 
                            v-if="!quiz.has_played_today"
                            :href="route('quiz.play', quiz.id)"
                            class="w-full bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-slate-950 font-black py-3 px-6 rounded-xl text-center shadow-lg shadow-amber-500/10 transition duration-150 ease-in-out block text-sm active:scale-[0.98]"
                        >
                            Mulai Kuis
                        </Link>
                        
                        <button 
                            v-else
                            disabled
                            class="w-full bg-slate-800 text-slate-500 border border-slate-800/50 font-bold py-3 px-6 rounded-xl text-center cursor-not-allowed text-sm"
                        >
                            Selesai Dikerjakan
                        </button>

                        <Link 
                            :href="route('quiz.play', { quiz: quiz.id, demo: 1 })"
                            class="w-full border border-slate-700 hover:bg-slate-800 text-slate-300 font-bold py-3 px-6 rounded-xl text-center shadow transition duration-150 ease-in-out block text-sm active:scale-[0.98]"
                        >
                            Simulasi Kuis (Demo)
                        </Link>
                    </div>
                </div>

                <!-- Bottom Navigation Buttons -->
                <div class="mt-8 pt-6 border-t border-slate-950 space-y-3">
                    <Link 
                        :href="route('quiz.leaderboard')"
                        class="flex items-center justify-between w-full bg-slate-900 border border-slate-800 hover:bg-slate-800/80 text-slate-200 font-bold p-4 rounded-xl transition-all duration-150 active:scale-[0.99] group"
                    >
                        <div class="flex items-center gap-3">
                            <span class="text-lg">🏆</span>
                            <span class="text-sm">Lihat Klasemen (Leaderboard)</span>
                        </div>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4 text-slate-500 group-hover:text-slate-300 group-hover:translate-x-0.5 transition-all">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                        </svg>
                    </Link>

                    <Link 
                        :href="route('quiz.history')"
                        class="flex items-center justify-between w-full bg-slate-900 border border-slate-800 hover:bg-slate-800/80 text-slate-200 font-bold p-4 rounded-xl transition-all duration-150 active:scale-[0.99] group"
                    >
                        <div class="flex items-center gap-3">
                            <span class="text-lg">📊</span>
                            <span class="text-sm">Lihat Riwayat Kuis Saya</span>
                        </div>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4 text-slate-500 group-hover:text-slate-300 group-hover:translate-x-0.5 transition-all">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                        </svg>
                    </Link>
                </div>
            </div>
            
            <div v-else class="text-center py-10 bg-slate-900/50 border border-slate-800/80 rounded-2xl p-6">
                <p class="text-slate-500 text-base">Belum ada kuis yang aktif saat ini.</p>
            </div>
        </div>
    </MobileAppLayout>
</template>
