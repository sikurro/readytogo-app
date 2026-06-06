<script setup>
import { Head, Link } from '@inertiajs/vue3';
import MobileAppLayout from '@/Layouts/MobileAppLayout.vue';

defineProps({
    quiz: Object,
    hasPlayedToday: Boolean,
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
                    
                    <div v-if="quiz" class="text-center">
                        <h3 class="text-2xl font-bold text-gray-900 mb-2">{{ quiz.title }}</h3>
                        <p class="text-gray-600 mb-6">Tema: {{ quiz.theme }}</p>
                        
                        <div v-if="hasPlayedToday" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                            Anda telah menyelesaikan kuis hari ini. Kembali lagi besok!
                        </div>

                        <div class="flex flex-col space-y-4">
                            <Link 
                                v-if="!hasPlayedToday"
                                :href="route('quiz.play')"
                                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 px-6 rounded-lg text-lg shadow transition duration-150 ease-in-out block"
                            >
                                Mulai Kuis Sekarang
                            </Link>
                            
                            <button 
                                v-else
                                disabled
                                class="w-full bg-gray-400 text-white font-bold py-4 px-6 rounded-lg text-lg cursor-not-allowed"
                            >
                                Kuis Hari Ini Telah Selesai
                            </button>

                            <Link 
                                :href="route('quiz.leaderboard')"
                                class="w-full bg-gray-100 hover:bg-gray-200 text-gray-800 font-bold py-3 px-6 rounded-lg shadow transition duration-150 ease-in-out block"
                            >
                                Lihat Klasemen (Leaderboard)
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
