<script setup>
import { Head, Link } from '@inertiajs/vue3';
import MobileAppLayout from '@/Layouts/MobileAppLayout.vue';

defineProps({
    history: Array,
    currentMonth: String,
});

const formatTime = (ms) => {
    const seconds = Math.floor(ms / 1000);
    const minutes = Math.floor(seconds / 60);
    const remainingSeconds = seconds % 60;
    return `${minutes}m ${remainingSeconds}s`;
};

const formatDate = (dateString) => {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};
</script>

<template>
    <Head title="Riwayat Kuis" />

    <MobileAppLayout>
        <div class="py-4">
            <!-- Header Section -->
            <div class="mb-6 flex items-center justify-between">
                <h2 class="text-2xl font-black text-slate-100 flex items-center gap-2">
                    <span class="w-2 h-6 bg-gradient-to-b from-amber-500 to-orange-500 rounded-full"></span>
                    Riwayat Kuis Saya
                </h2>
                
                <Link 
                    :href="route('quiz.index')" 
                    class="flex items-center gap-1.5 text-xs text-amber-500 hover:text-amber-400 font-bold bg-slate-900 border border-slate-800 hover:bg-slate-800/80 px-3.5 py-2 rounded-xl transition-all duration-200 active:scale-95"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3.5 h-3.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                    </svg>
                    Kembali
                </Link>
            </div>

            <!-- Info Banner Bulan Berjalan (Dark Mode) -->
            <div class="bg-slate-900 border border-slate-800 text-slate-300 rounded-2xl p-4 flex items-start gap-3 shadow-md mb-6">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-5 w-5 text-cyan-400 shrink-0 mt-0.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 111.063.852l-.708 2.836a.75.75 0 001.063.852l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="text-xs leading-relaxed">
                    <strong class="text-cyan-400">Catatan:</strong> Riwayat yang ditampilkan adalah aktivitas khusus untuk bulan berjalan (<span class="font-extrabold text-slate-100">{{ currentMonth }}</span>).
                </span>
            </div>

            <!-- List Riwayat (Card-based Mobile Layout) -->
            <div v-if="history && history.length > 0" class="space-y-4">
                <div 
                    v-for="attempt in history" 
                    :key="attempt.id" 
                    class="bg-slate-900 border border-slate-800/80 rounded-2xl p-4 shadow-lg hover:border-slate-700/80 transition-all duration-200"
                >
                    <div class="flex items-start justify-between gap-3 mb-3">
                        <div>
                            <h3 class="font-bold text-slate-100 text-base leading-snug">
                                {{ attempt.quiz ? attempt.quiz.title : 'Kuis dihapus' }}
                            </h3>
                            <span class="text-[10px] text-slate-500 font-medium block mt-1">
                                {{ formatDate(attempt.created_at) }}
                            </span>
                        </div>
                        
                        <!-- Score badge -->
                        <div class="flex flex-col items-end">
                            <span class="text-[10px] text-slate-400 font-extrabold uppercase tracking-wider">Skor</span>
                            <span class="text-xl font-black text-amber-500">{{ attempt.score }}</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3 pt-3 border-t border-slate-800/60">
                        <!-- Correct Answers -->
                        <div class="flex items-center gap-2 bg-slate-950/40 px-3 py-2 rounded-xl">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-emerald-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div class="flex flex-col">
                                <span class="text-[10px] text-slate-400 font-medium leading-none">Benar</span>
                                <span class="text-xs font-bold text-emerald-400 mt-0.5">{{ attempt.correct_answers }}</span>
                            </div>
                        </div>

                        <!-- Duration -->
                        <div class="flex items-center gap-2 bg-slate-950/40 px-3 py-2 rounded-xl">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                              <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div class="flex flex-col">
                                <span class="text-[10px] text-slate-400 font-medium leading-none">Durasi</span>
                                <span class="text-xs font-bold text-slate-200 mt-0.5">{{ formatTime(attempt.time_ms) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="text-center py-12 bg-slate-900/50 border border-slate-800/80 rounded-2xl p-6">
                <div class="text-4xl mb-3">📊</div>
                <p class="text-slate-500 text-sm font-medium">Anda belum pernah mengerjakan kuis bulan ini.</p>
            </div>
        </div>
    </MobileAppLayout>
</template>
