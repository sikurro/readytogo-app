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
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Riwayat Kuis Saya</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    
                    <div class="mb-6 flex justify-between items-center border-b border-gray-100 pb-4">
                        <h3 class="text-2xl font-bold text-gray-900">Aktivitas Pengerjaan Kuis</h3>
                        <Link :href="route('quiz.index')" class="text-blue-600 hover:text-blue-800 font-semibold">
                            &larr; Kembali ke Kuis
                        </Link>
                    </div>

                    <!-- Info Banner Bulan Berjalan -->
                    <div class="bg-blue-50 border border-blue-200 text-blue-700 px-4 py-3 rounded-lg mb-6 text-sm flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                        <span><strong>Catatan:</strong> Riwayat yang ditampilkan di bawah ini adalah aktivitas khusus untuk bulan berjalan ({{ currentMonth }}).</span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white border border-gray-200">
                            <thead>
                                <tr class="bg-gray-100 text-gray-600 uppercase text-xs leading-normal">
                                    <th class="py-3 px-6 text-left">Tanggal</th>
                                    <th class="py-3 px-6 text-left">Nama Kuis</th>
                                    <th class="py-3 px-6 text-center">Skor</th>
                                    <th class="py-3 px-6 text-center">Jawaban Benar</th>
                                    <th class="py-3 px-6 text-center">Durasi</th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-600 text-sm font-light">
                                <tr v-for="attempt in history" 
                                    :key="attempt.id" 
                                    class="border-b border-gray-200 hover:bg-gray-50"
                                >
                                    <td class="py-3 px-6 text-left whitespace-nowrap text-xs">
                                        {{ formatDate(attempt.created_at) }}
                                    </td>
                                    <td class="py-3 px-6 text-left font-medium text-gray-900">
                                        {{ attempt.quiz ? attempt.quiz.title : 'Kuis dihapus' }}
                                    </td>
                                    <td class="py-3 px-6 text-center font-bold text-gray-700">
                                        {{ attempt.score }}
                                    </td>
                                    <td class="py-3 px-6 text-center font-semibold text-green-600">
                                        {{ attempt.correct_answers }}
                                    </td>
                                    <td class="py-3 px-6 text-center whitespace-nowrap">
                                        {{ formatTime(attempt.time_ms) }}
                                    </td>
                                </tr>
                                
                                <tr v-if="history.length === 0">
                                    <td colspan="5" class="py-6 text-center text-gray-500">
                                        Anda belum pernah mengerjakan kuis.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </MobileAppLayout>
</template>
