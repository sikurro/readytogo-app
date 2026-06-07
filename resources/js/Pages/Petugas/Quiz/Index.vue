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
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Kuis Harian K3</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    
                    <div v-if="quizzes && quizzes.length > 0" class="space-y-8">
                        <div v-for="quiz in quizzes" :key="quiz.id" class="border rounded-lg p-6 bg-slate-50 shadow-sm relative overflow-hidden">
                            
                            <div v-if="quiz.is_daily_quiz" class="absolute top-0 right-0 bg-purple-600 text-white text-xs font-bold px-3 py-1 rounded-bl-lg">
                                KUIS HARIAN
                            </div>
                            <div v-else class="absolute top-0 right-0 bg-blue-600 text-white text-xs font-bold px-3 py-1 rounded-bl-lg">
                                EVENT QUIZ
                            </div>

                            <h3 class="text-2xl font-bold text-gray-900 mb-1">{{ quiz.title }}</h3>
                            <p class="text-gray-600 mb-4">Tema: {{ quiz.theme }}</p>
                            
                            <div v-if="quiz.has_played_today" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4 text-sm">
                                Anda telah menyelesaikan kuis ini hari ini.
                            </div>

                            <div class="flex flex-col space-y-3 mt-4">
                                <Link 
                                    v-if="!quiz.has_played_today"
                                    :href="route('quiz.play', quiz.id)"
                                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg text-center shadow transition duration-150 ease-in-out block"
                                >
                                    Mulai Kuis
                                </Link>
                                
                                <button 
                                    v-else
                                    disabled
                                    class="w-full bg-gray-400 text-white font-bold py-3 px-6 rounded-lg text-center cursor-not-allowed"
                                >
                                    Selesai Dikerjakan
                                </button>
                            </div>
                        </div>

                        <div class="mt-8 border-t pt-8 space-y-4">
                            <Link 
                                :href="route('quiz.leaderboard')"
                                class="w-full bg-gray-100 hover:bg-gray-200 text-gray-800 font-bold py-3 px-6 rounded-lg shadow transition duration-150 ease-in-out block text-center"
                            >
                                Lihat Klasemen (Leaderboard)
                            </Link>

                            <Link 
                                :href="route('quiz.history')"
                                class="w-full bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold py-3 px-6 rounded-lg shadow transition duration-150 ease-in-out block text-center"
                            >
                                Lihat Riwayat Kuis Saya
                            </Link>
                        </div>
                    </div>
                    
                    <div v-else class="text-center py-10">
                        <p class="text-gray-500 text-lg">Belum ada kuis yang aktif saat ini.</p>
                    </div>

                </div>
            </div>
        </div>
    </MobileAppLayout>
</template>
