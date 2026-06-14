<script setup>
import { Head, Link } from '@inertiajs/vue3';
import MobileAppLayout from '@/Layouts/MobileAppLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import { ref } from 'vue';

defineProps({
    fatigueChecks: Object,
});

const activeCheckDetails = ref(null);

const toggleDetails = (id) => {
    if (activeCheckDetails.value === id) {
        activeCheckDetails.value = null;
    } else {
        activeCheckDetails.value = id;
    }
};

const formatDate = (dateStr) => {
    const dateObj = new Date(dateStr);
    return dateObj.toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};
</script>

<template>
    <Head title="Riwayat Fatigue Check" />

    <MobileAppLayout>
        <div class="py-4 space-y-6">
            <!-- Header Section -->
            <div class="flex items-center justify-between">
                <h2 class="text-2xl font-black text-slate-100 flex items-center gap-2">
                    <span class="w-2 h-6 bg-gradient-to-b from-amber-500 to-orange-500 rounded-full"></span>
                    Riwayat Fatigue Saya
                </h2>
            </div>

            <!-- Checks List -->
            <div class="space-y-4">
                <div v-for="check in fatigueChecks.data" :key="check.id" 
                    class="bg-slate-800/40 backdrop-blur border border-slate-700/50 rounded-2xl p-5 shadow-lg space-y-4">
                    
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <span class="block text-[11px] text-slate-500 font-bold tracking-wide uppercase">{{ formatDate(check.created_at) }}</span>
                            <span class="text-xs text-slate-350 font-bold block mt-1">Reaksi: {{ check.reaction_time_ms }} ms</span>
                        </div>
                        <div class="flex flex-col items-end gap-1.5">
                            <span :class="check.is_fit ? 'bg-emerald-500 text-slate-950 shadow-emerald-500/20' : 'bg-rose-600 text-slate-100 shadow-rose-600/20'" 
                                class="px-3 py-1 rounded-full text-[10px] font-extrabold tracking-wider uppercase shadow-md leading-none">
                                {{ check.is_fit ? 'FIT TO WORK' : 'UNFIT' }}
                            </span>
                            <button @click="toggleDetails(check.id)" class="text-[10px] text-amber-500 font-bold hover:text-amber-400 flex items-center gap-0.5">
                                <span>{{ activeCheckDetails === check.id ? 'Sembunyikan Detail' : 'Lihat Detail' }}</span>
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3 h-3 transition-transform" :class="{ 'rotate-180': activeCheckDetails === check.id }">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Detail Section (Collapsible) -->
                    <div v-if="activeCheckDetails === check.id" class="bg-slate-900/60 rounded-xl p-4 space-y-3 border border-white/5 animate-fadeIn">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-800 pb-1.5">Hasil Kuesioner:</p>
                        <div class="space-y-2">
                            <div v-for="ans in check.answers" :key="ans.id" class="flex items-start justify-between gap-3 text-xs leading-relaxed">
                                <span class="text-slate-400">{{ ans.question?.question_text || 'Pertanyaan Terhapus' }}</span>
                                <span :class="ans.answer === !!ans.question?.safe_answer ? 'text-emerald-400 font-bold' : 'text-rose-455 font-bold text-rose-400'" class="shrink-0">
                                    {{ ans.answer ? 'Ya' : 'Tidak' }}
                                </span>
                            </div>
                        </div>
                    </div>

                </div>

                <div v-if="fatigueChecks.data.length === 0" class="text-center py-12 bg-slate-800/20 rounded-2xl border border-slate-800">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-10 h-10 text-slate-600 mx-auto mb-3">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.03 0 1.9.693 2.166 1.638m-7.377 0A48.536 48.536 0 0 1 12 3" />
                    </svg>
                    <p class="text-sm font-semibold text-slate-500">Belum ada riwayat fatigue check.</p>
                </div>
            </div>

            <!-- Pagination -->
            <div class="flex justify-center pt-4" v-if="fatigueChecks.data.length > 0">
                <Pagination :links="fatigueChecks.links" />
            </div>

        </div>
    </MobileAppLayout>
</template>

<style scoped>
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-4px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-fadeIn {
    animation: fadeIn 0.2s ease-out forwards;
}
</style>
