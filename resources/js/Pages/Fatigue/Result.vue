<script setup>
import { Head, Link } from '@inertiajs/vue3';
import MobileAppLayout from '@/Layouts/MobileAppLayout.vue';

defineProps({
    result: {
        type: Object,
        required: true
    }
});
</script>

<template>
    <Head title="Hasil Fatigue Check" />

    <MobileAppLayout>
        <div class="space-y-6 flex flex-col h-full min-h-[70vh] justify-center pb-8">
            
            <!-- Result Card -->
            <div class="relative overflow-hidden rounded-3xl p-6 shadow-2xl text-center border-2 transition-all"
                 :class="result.is_fit 
                    ? 'bg-gradient-to-br from-emerald-900 to-slate-900 border-emerald-500/30 shadow-[0_0_30px_rgba(16,185,129,0.15)]' 
                    : 'bg-gradient-to-br from-red-900 to-slate-900 border-red-500/30 shadow-[0_0_30px_rgba(239,68,68,0.15)]'">
                
                <!-- Background Pattern -->
                <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyMCIgaGVpZ2h0PSIyMCI+PGNpcmNsZSBjeD0iMiIgY3k9IjIiIHI9IjIiIGZpbGw9IiNmZmZmZmYiIGZpbGwtb3BhY2l0eT0iMC4wNSIvPjwvc3ZnPg==')] opacity-50"></div>

                <div class="relative z-10">
                    <!-- Icon -->
                    <div class="mx-auto w-24 h-24 rounded-full flex items-center justify-center mb-6 shadow-lg"
                         :class="result.is_fit ? 'bg-emerald-500/20 text-emerald-400' : 'bg-red-500/20 text-red-400'">
                        <svg v-if="result.is_fit" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-12 h-12">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0110 21a3.745 3.745 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0114 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z" />
                        </svg>
                        <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-12 h-12">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2.25m0 2.25h.01M12 18a9 9 0 100-18 9 9 0 000 18z" />
                        </svg>
                    </div>

                    <h3 class="text-3xl font-black mb-2 tracking-tight" :class="result.is_fit ? 'text-emerald-400' : 'text-red-400'">
                        {{ result.is_fit ? 'FIT TO WORK' : 'UNFIT / WARNING' }}
                    </h3>
                    
                    <p class="text-slate-300 text-sm font-medium leading-relaxed mb-6">
                        {{ result.is_fit 
                            ? 'Kondisi Anda dinyatakan sehat dan prima untuk mulai bekerja hari ini.' 
                            : 'Sistem mendeteksi risiko kelelahan atau kondisi kurang sehat.' 
                        }}
                    </p>

                    <!-- Stats Row -->
                    <div class="grid grid-cols-2 gap-3 mb-6">
                        <div class="bg-slate-950/40 rounded-2xl p-4 backdrop-blur-sm border border-white/5">
                            <p class="text-[10px] text-slate-400 uppercase tracking-widest font-bold mb-1">Kuesioner</p>
                            <p class="text-sm font-black" :class="result.questionnaire_status ? 'text-emerald-400' : 'text-red-400'">
                                {{ result.questionnaire_status ? 'Aman' : 'Berisiko' }}
                            </p>
                        </div>
                        <div class="bg-slate-950/40 rounded-2xl p-4 backdrop-blur-sm border border-white/5">
                            <p class="text-[10px] text-slate-400 uppercase tracking-widest font-bold mb-1">Tes Reaksi</p>
                            <p class="text-xl font-black text-white leading-none">
                                {{ result.reaction_time_ms }} <span class="text-[10px] text-slate-500 ml-0.5">ms</span>
                            </p>
                            <p class="text-[10px] font-bold mt-1" :class="result.reaction_time_ms < 500 ? 'text-emerald-400' : 'text-red-400'">
                                {{ result.reaction_time_ms < 500 ? 'Normal' : 'Terlalu Lambat' }}
                            </p>
                        </div>
                    </div>

                    <!-- Warnings (if unfit) -->
                    <div v-if="!result.is_fit" class="bg-red-950/50 rounded-xl p-4 text-left border border-red-500/20 backdrop-blur-sm">
                        <p class="text-xs font-bold text-red-400 mb-2 flex items-center gap-1.5">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                            </svg>
                            Catatan Sistem:
                        </p>
                        <ul class="list-disc pl-5 text-[11px] text-red-200 space-y-1">
                            <li v-if="!result.questionnaire_status">Gagal pada Kuesioner Self-Assessment.</li>
                            <li v-if="result.reaction_time_ms >= 500">Waktu reaksi terlalu lambat ({{ result.reaction_time_ms }} ms).</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Action Button -->
            <Link :href="route('dashboard')" 
                  class="w-full bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-slate-950 font-extrabold py-4 px-5 rounded-xl shadow-lg shadow-amber-500/20 transition-all active:scale-95 flex items-center justify-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                </svg>
                <span>Kembali ke Dashboard</span>
            </Link>
            
        </div>
    </MobileAppLayout>
</template>
