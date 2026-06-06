<script setup>
import AdminDashboardLayout from '@/Layouts/AdminDashboardLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    history: Object, // Laravel Paginated object
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
    <Head title="Riwayat Kuis Petugas - Admin" />

    <AdminDashboardLayout>
        <template #header>
            <span>Riwayat / Log Kuis Petugas</span>
        </template>

        <div class="space-y-6">
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl space-y-4">
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                    <h3 class="font-bold text-slate-200">Seluruh Riwayat Pengerjaan Kuis</h3>
                </div>

                <!-- Info Banner Bulan Berjalan -->
                <div class="bg-amber-500/10 border border-amber-500/20 text-amber-500 rounded-xl p-4 flex items-center gap-3 text-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 shrink-0">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 111.083 1.083l-.042.02a.75.75 0 01-1.083-1.083zM12 20.25a8.25 8.25 0 100-16.5 8.25 8.25 0 000 16.5z" />
                    </svg>
                    <span><strong>Catatan:</strong> Data log kuis yang ditampilkan pada tabel di bawah ini difilter hanya untuk aktivitas bulan berjalan ({{ currentMonth }}).</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-slate-300">
                        <thead>
                            <tr class="border-b border-slate-800 text-left text-xs font-bold uppercase tracking-wider text-slate-400">
                                <th class="py-3 px-4">Petugas</th>
                                <th class="py-3 px-4">NIP</th>
                                <th class="py-3 px-4">Tanggal</th>
                                <th class="py-3 px-4">Nama Kuis</th>
                                <th class="py-3 px-4 text-center">Skor</th>
                                <th class="py-3 px-4 text-center">Jawaban Benar</th>
                                <th class="py-3 px-4 text-center">Durasi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800 text-sm">
                            <tr v-for="attempt in history.data" :key="attempt.id" class="hover:bg-slate-800/30 transition-colors duration-150">
                                <td class="py-3 px-4 font-semibold text-slate-200">
                                    {{ attempt.user ? attempt.user.name : 'Unknown User' }}
                                </td>
                                <td class="py-3 px-4 text-xs">
                                    {{ attempt.user ? attempt.user.nip : '-' }}
                                </td>
                                <td class="py-3 px-4 text-xs whitespace-nowrap">
                                    {{ formatDate(attempt.created_at) }}
                                </td>
                                <td class="py-3 px-4">
                                    {{ attempt.quiz ? attempt.quiz.title : 'Kuis dihapus' }}
                                </td>
                                <td class="py-3 px-4 text-center font-bold text-amber-500">
                                    {{ attempt.score }}
                                </td>
                                <td class="py-3 px-4 text-center text-emerald-400 font-semibold">
                                    {{ attempt.correct_answers }}
                                </td>
                                <td class="py-3 px-4 text-center text-xs whitespace-nowrap">
                                    {{ formatTime(attempt.time_ms) }}
                                </td>
                            </tr>
                            <tr v-if="history.data.length === 0">
                                <td colspan="7" class="py-8 text-center text-slate-500 text-xs">
                                    Belum ada data pengerjaan kuis.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="pt-4 flex justify-center">
                    <Pagination :links="history.links" />
                </div>
            </div>
        </div>
    </AdminDashboardLayout>
</template>
