<script setup>
import AdminDashboardLayout from '@/Layouts/AdminDashboardLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    fatigueChecks: Object,
    filters: Object,
    summary: Object,
});

const search = ref(props.filters?.search || '');
const status = ref(props.filters?.status || '');
const date = ref(props.filters?.date || '');
const isLoading = ref(false);

const handleSearch = () => {
    isLoading.value = true;
    router.get(route('admin.fatigue-checks.index'), {
        search: search.value,
        status: status.value,
        date: date.value,
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
        onFinish: () => { isLoading.value = false; }
    });
};

watch([search, status, date], () => {
    handleSearch();
});

const clearFilters = () => {
    search.value = '';
    status.value = '';
    date.value = '';
};

const formatDate = (dateStr) => {
    if (!dateStr) return '-';
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
    <Head title="Laporan Kelelahan (Fatigue Check) - Admin" />

    <AdminDashboardLayout>
        <template #header>
            <span>Laporan & Analisis Kelelahan Kerja</span>
        </template>

        <div class="space-y-6">
            <!-- Summary Stats Widgets -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Total Checks -->
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-xl flex items-center gap-4">
                    <div class="h-12 w-12 rounded-xl bg-blue-500/10 flex items-center justify-center text-blue-400 border border-blue-500/20">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.03 0 1.9.693 2.166 1.638m-7.377 0A48.536 48.536 0 0 1 12 3" />
                        </svg>
                    </div>
                    <div>
                        <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Tes Hari Ini</span>
                        <span class="text-2xl font-black text-slate-100">{{ summary.total_today }}</span>
                    </div>
                </div>

                <!-- Fit Today -->
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-xl flex items-center gap-4">
                    <div class="h-12 w-12 rounded-xl bg-emerald-500/10 flex items-center justify-center text-emerald-400 border border-emerald-500/20">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0110 21a3.745 3.745 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0114 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z" />
                        </svg>
                    </div>
                    <div>
                        <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Petugas Fit (Hari Ini)</span>
                        <span class="text-2xl font-black text-emerald-400">{{ summary.fit_today }}</span>
                    </div>
                </div>

                <!-- Unfit Today -->
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-xl flex items-center gap-4">
                    <div class="h-12 w-12 rounded-xl bg-rose-500/10 flex items-center justify-center text-rose-400 border border-rose-500/20">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                        </svg>
                    </div>
                    <div>
                        <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Petugas Unfit (Hari Ini)</span>
                        <span class="text-2xl font-black text-rose-455 text-rose-400">{{ summary.unfit_today }}</span>
                    </div>
                </div>

                <!-- Avg Reaction Time -->
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-xl flex items-center gap-4">
                    <div class="h-12 w-12 rounded-xl bg-amber-500/10 flex items-center justify-center text-amber-400 border border-amber-500/20">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                    </div>
                    <div>
                        <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Rata-rata Reaksi (Hari Ini)</span>
                        <span class="text-2xl font-black text-amber-450 text-amber-400">{{ summary.avg_reaction_time_today }} ms</span>
                    </div>
                </div>
            </div>

            <!-- Filter & History Table -->
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between border-b border-slate-800 pb-4 gap-4">
                    <div>
                        <h3 class="font-bold text-lg text-slate-200">Riwayat Pemeriksaan Fatigue</h3>
                        <p class="text-xs text-slate-400">Gunakan filter untuk menyaring riwayat fatigue check user.</p>
                    </div>
                </div>

                <!-- Filters -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 bg-slate-800/40 p-4 rounded-xl border border-slate-800">
                    <div class="relative">
                        <input v-model="search" type="text" placeholder="Cari nama/NIP..." class="w-full bg-slate-900 border border-slate-700 rounded-lg py-2 pl-3 px-4 text-sm text-slate-200 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition-colors duration-200" />
                    </div>
                    <div>
                        <select v-model="status" class="w-full bg-slate-900 border border-slate-700 rounded-lg py-2 px-3 text-sm text-slate-200 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition-colors duration-200">
                            <option value="">Semua Status</option>
                            <option value="fit">Fit</option>
                            <option value="unfit">Tidak Fit (Unfit)</option>
                        </select>
                    </div>
                    <div>
                        <input v-model="date" type="date" class="w-full bg-slate-900 border border-slate-700 rounded-lg py-2 px-3 text-sm text-slate-200 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition-colors duration-200" />
                    </div>
                    <div class="flex">
                        <button @click="clearFilters" class="w-full md:w-auto px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-350 hover:text-slate-200 font-semibold text-sm rounded-lg transition-colors border border-slate-700/60">
                            Reset Filter
                        </button>
                    </div>
                </div>

                <!-- Table -->
                <div class="overflow-x-auto rounded-lg border border-slate-800 relative transition-opacity duration-200" :class="{ 'opacity-50 pointer-events-none': isLoading }">
                    <div v-if="isLoading" class="absolute inset-0 z-10 flex flex-col items-center justify-center gap-3 bg-slate-900/60 rounded-lg backdrop-blur-sm">
                        <svg class="animate-spin w-7 h-7 text-amber-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        <span class="text-xs text-amber-400 font-medium">Memuat data...</span>
                    </div>
                    <table class="min-w-full text-slate-300">
                        <thead>
                            <tr class="bg-slate-950 border-b border-slate-800 text-left text-xs font-bold uppercase tracking-wider text-slate-400">
                                <th class="py-3 px-4">Petugas</th>
                                <th class="py-3 px-4">Lokasi/Unit</th>
                                <th class="py-3 px-4">Waktu Pemeriksaan</th>
                                <th class="py-3 px-4 text-center">Status Kuesioner</th>
                                <th class="py-3 px-4 text-center">Kecepatan Reaksi</th>
                                <th class="py-3 px-4 text-center">Kesimpulan Akhir</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800 text-sm">
                            <tr v-for="check in fatigueChecks.data" :key="check.id" class="hover:bg-slate-850/50 transition-colors duration-150">
                                <td class="py-3 px-4">
                                    <div class="font-bold text-slate-100">{{ check.user?.name || 'User Terhapus' }}</div>
                                    <div class="text-xs text-slate-400">NIP: {{ check.user?.nip || '-' }}</div>
                                </td>
                                <td class="py-3 px-4 text-slate-300">
                                    {{ check.user?.location?.name || 'Tidak ada lokasi' }}
                                </td>
                                <td class="py-3 px-4 text-slate-350">
                                    {{ formatDate(check.created_at) }}
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <!-- questionnaire_status can be nullable/boolean -->
                                    <span v-if="check.questionnaire_status === null" class="text-slate-500 font-medium">-</span>
                                    <span v-else-if="check.questionnaire_status" class="bg-emerald-500/10 text-emerald-400 border border-emerald-500/25 px-2.5 py-0.5 rounded-full text-xs font-semibold">
                                        Lolos
                                    </span>
                                    <span v-else class="bg-rose-500/10 text-rose-400 border border-rose-500/25 px-2.5 py-0.5 rounded-full text-xs font-semibold">
                                        Ada Keluhan
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-center font-bold text-slate-100">
                                    {{ check.reaction_time_ms ? `${check.reaction_time_ms} ms` : '-' }}
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <span v-if="check.is_fit === null" class="text-slate-500 font-medium">-</span>
                                    <span v-else-if="check.is_fit" class="bg-emerald-500 text-slate-950 px-3 py-1 rounded-full text-xs font-extrabold shadow-lg shadow-emerald-500/20">
                                        FIT TO WORK
                                    </span>
                                    <span v-else class="bg-rose-600 text-slate-100 px-3 py-1 rounded-full text-xs font-extrabold shadow-lg shadow-rose-600/20">
                                        UNFIT
                                    </span>
                                </td>
                            </tr>
                            <tr v-if="fatigueChecks.data.length === 0">
                                <td colspan="6" class="py-8 text-center text-slate-500">
                                    Tidak ada data pemeriksaan fatigue yang ditemukan.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="flex items-center justify-between border-t border-slate-800 pt-4">
                    <div class="text-xs text-slate-400">
                        Menampilkan {{ fatigueChecks.from || 0 }} sampai {{ fatigueChecks.to || 0 }} dari {{ fatigueChecks.total }} data.
                    </div>
                    <Pagination :links="fatigueChecks.links" />
                </div>
            </div>
        </div>
    </AdminDashboardLayout>
</template>
