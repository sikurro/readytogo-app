<script setup>
import { Head, Link } from '@inertiajs/vue3';
import MobileAppLayout from '@/Layouts/MobileAppLayout.vue';
import { usePage } from '@inertiajs/vue3';

defineProps({
    leaderboard: Array,
    currentMonth: String
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

const currentUserId = usePage().props.auth.user.id;
</script>

<template>
    <Head title="Leaderboard Kuis" />

    <MobileAppLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Leaderboard Kuis - {{ currentMonth }}</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    
                    <div class="mb-6 flex justify-between items-center">
                        <h3 class="text-2xl font-bold text-gray-900">Klasemen Petugas</h3>
                        <Link :href="route('quiz.index')" class="text-blue-600 hover:text-blue-800 font-semibold">
                            &larr; Kembali ke Kuis
                        </Link>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white border border-gray-200">
                            <thead>
                                <tr class="bg-gray-100 text-gray-600 uppercase text-sm leading-normal">
                                    <th class="py-3 px-6 text-center">Rank</th>
                                    <th class="py-3 px-6 text-left">Nama Petugas</th>
                                    <th class="py-3 px-6 text-center">Skor Akumulasi</th>
                                    <th class="py-3 px-6 text-center">Jawaban Benar</th>
                                    <th class="py-3 px-6 text-center">Terakhir Bermain</th>
                                    <th class="py-3 px-6 text-center">Total Waktu</th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-600 text-sm font-light">
                                <tr v-for="(entry, index) in leaderboard" 
                                    :key="entry.user_id" 
                                    class="border-b border-gray-200 hover:bg-gray-50"
                                    :class="{'bg-yellow-50 font-semibold': entry.user_id === currentUserId}"
                                >
                                    <td class="py-3 px-6 text-center whitespace-nowrap">
                                        <span v-if="index === 0" class="text-2xl" title="Peringkat 1">🥇</span>
                                        <span v-else-if="index === 1" class="text-2xl" title="Peringkat 2">🥈</span>
                                        <span v-else-if="index === 2" class="text-2xl" title="Peringkat 3">🥉</span>
                                        <span v-else class="font-bold text-gray-700">{{ index + 1 }}</span>
                                    </td>
                                    <td class="py-3 px-6 text-left">
                                        <div class="flex items-center">
                                            <div class="font-medium text-gray-900">{{ entry.user.name }}</div>
                                            <span v-if="entry.user_id === currentUserId" class="ml-2 bg-blue-100 text-blue-800 text-xs px-2 rounded-full">Anda</span>
                                        </div>
                                        <div class="text-xs text-gray-500">{{ entry.user.position || 'Petugas' }}</div>
                                    </td>
                                    <td class="py-3 px-6 text-center font-bold text-gray-700">
                                        {{ entry.total_score }}
                                    </td>
                                    <td class="py-3 px-6 text-center font-semibold text-green-600">
                                        {{ entry.total_correct }}
                                    </td>
                                    <td class="py-3 px-6 text-center text-gray-500 text-xs whitespace-nowrap">
                                        {{ formatDate(entry.last_played) }}
                                    </td>
                                    <td class="py-3 px-6 text-center">
                                        {{ formatTime(entry.total_time) }}
                                    </td>
                                </tr>
                                
                                <tr v-if="leaderboard.length === 0">
                                    <td colspan="6" class="py-6 text-center text-gray-500">
                                        Belum ada data partisipasi untuk bulan ini.
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
