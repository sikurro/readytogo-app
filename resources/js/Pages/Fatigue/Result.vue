<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { CheckCircleIcon, XCircleIcon, ExclamationTriangleIcon } from '@heroicons/vue/24/solid';

defineProps({
    result: {
        type: Object,
        required: true
    }
});
</script>

<template>
    <Head title="Hasil Fatigue Check" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Hasil Pemeriksaan Fit to Work</h2>
        </template>

        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-8 text-center">
                        
                        <!-- Lulus / Fit -->
                        <div v-if="result.is_fit" class="mb-8">
                            <CheckCircleIcon class="w-24 h-24 text-emerald-500 mx-auto mb-4" />
                            <h3 class="text-2xl font-bold text-gray-900 mb-2">FIT TO WORK</h3>
                            <p class="text-gray-600">Anda dinyatakan sehat dan fokus untuk bekerja hari ini.</p>
                        </div>

                        <!-- Tidak Lulus / Unfit -->
                        <div v-else class="mb-8">
                            <XCircleIcon class="w-24 h-24 text-red-500 mx-auto mb-4" />
                            <h3 class="text-2xl font-bold text-gray-900 mb-2">UNFIT / WARNING</h3>
                            <p class="text-gray-600">Sistem mendeteksi risiko kelelahan atau kondisi kurang sehat.</p>
                            <div class="mt-4 p-4 bg-red-50 rounded-md text-red-700 max-w-md mx-auto text-sm text-left">
                                <p class="font-semibold mb-1">Catatan Sistem:</p>
                                <ul class="list-disc pl-5">
                                    <li v-if="!result.questionnaire_status">Gagal pada Kuesioner Self-Assessment.</li>
                                    <li v-if="result.reaction_time_ms >= 500">Waktu reaksi terlalu lambat ({{ result.reaction_time_ms }} ms).</li>
                                </ul>
                            </div>
                        </div>

                        <!-- Statistik -->
                        <div class="grid grid-cols-2 gap-4 max-w-md mx-auto mb-10">
                            <div class="bg-gray-50 p-4 rounded-lg border">
                                <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">Kuesioner</p>
                                <p class="text-lg font-bold" :class="result.questionnaire_status ? 'text-emerald-600' : 'text-red-600'">
                                    {{ result.questionnaire_status ? 'Aman' : 'Berisiko' }}
                                </p>
                            </div>
                            <div class="bg-gray-50 p-4 rounded-lg border">
                                <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">Tes Reaksi</p>
                                <p class="text-lg font-bold" :class="result.reaction_time_ms < 500 ? 'text-emerald-600' : 'text-red-600'">
                                    {{ result.reaction_time_ms }} ms
                                </p>
                            </div>
                        </div>

                        <div>
                            <Link :href="route('dashboard')" 
                                  class="inline-flex items-center px-6 py-3 bg-gray-800 border border-transparent rounded-md font-semibold text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Kembali ke Dashboard
                            </Link>
                        </div>
                        
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
