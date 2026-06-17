<script setup>
import AdminDashboardLayout from '@/Layouts/AdminDashboardLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import axios from 'axios';
import DatePicker from '@/Components/DatePicker.vue';

const props = defineProps({
    fatigueChecks: Object,
    filters: Object,
    summary: Object,
    notTestedUsers: Array,
});

const search = ref(props.filters?.search || '');
const status = ref(props.filters?.status || '');
const date = ref(props.filters?.date || new Date().toLocaleDateString('en-CA'));
const isLoading = ref(false);

const displayDate = computed(() => {
    const d = date.value ? new Date(date.value) : new Date();
    return d.toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric'
    });
});

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

const isExporting = ref(false);
const exportProgress = ref(0);

const exportData = () => {
    isExporting.value = true;
    exportProgress.value = 0;

    const interval = setInterval(() => {
        if (exportProgress.value < 90) {
            exportProgress.value += Math.floor(Math.random() * 10) + 5;
            if (exportProgress.value > 90) exportProgress.value = 90;
        }
    }, 200);

    axios.get(route('admin.fatigue-checks.export'), {
        params: {
            search: search.value,
            status: status.value,
            date: date.value
        },
        responseType: 'blob'
    }).then(response => {
        exportProgress.value = 100;
        clearInterval(interval);
        
        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', 'fatigue-checks.xlsx');
        document.body.appendChild(link);
        link.click();
        
        window.URL.revokeObjectURL(url);
        document.body.removeChild(link);
        
        setTimeout(() => {
            isExporting.value = false;
        }, 500);
    }).catch(error => {
        clearInterval(interval);
        isExporting.value = false;
        console.error("Export failed:", error);
        alert("Gagal mengekspor data. Silakan coba lagi.");
    });
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
            <div class="pb-2">
                <h3 class="font-bold text-lg text-slate-200">Statistik Pemeriksaan Fatigue: {{ displayDate }}</h3>
            </div>
            <!-- Summary Stats Widgets -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                <!-- Total Checks -->
                <div @click="status = ''" class="bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-xl flex items-center gap-4 cursor-pointer hover:-translate-y-1 hover:shadow-2xl hover:border-slate-500 transition-all duration-300">
                    <div class="h-12 w-12 rounded-xl bg-blue-500/10 flex items-center justify-center text-blue-400 border border-blue-500/20">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.03 0 1.9.693 2.166 1.638m-7.377 0A48.536 48.536 0 0 1 12 3" />
                        </svg>
                    </div>
                    <div>
                        <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Check</span>
                        <span class="text-2xl font-black text-slate-100">{{ summary.total_today }}</span>
                    </div>
                </div>

                <!-- Fit Today -->
                <div @click="status = 'fit'" class="bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-xl flex items-center gap-4 cursor-pointer hover:-translate-y-1 hover:shadow-2xl hover:border-emerald-500 transition-all duration-300">
                    <div class="h-12 w-12 rounded-xl bg-emerald-500/10 flex items-center justify-center text-emerald-400 border border-emerald-500/20">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0110 21a3.745 3.745 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0114 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z" />
                        </svg>
                    </div>
                    <div>
                        <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Petugas Fit</span>
                        <span class="text-2xl font-black text-emerald-400">{{ summary.fit_today }}</span>
                    </div>
                </div>

                <!-- Unfit Today -->
                <div @click="status = 'unfit'" class="bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-xl flex items-center gap-4 cursor-pointer hover:-translate-y-1 hover:shadow-2xl hover:border-rose-500 transition-all duration-300">
                    <div class="h-12 w-12 rounded-xl bg-rose-500/10 flex items-center justify-center text-rose-400 border border-rose-500/20">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                        </svg>
                    </div>
                    <div>
                        <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Petugas Unfit</span>
                        <span class="text-2xl font-black text-rose-400">{{ summary.unfit_today }}</span>
                    </div>
                </div>

                <!-- Belum Check Fatigue -->
                <div @click="status = 'belum_check'" class="bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-xl flex items-center gap-4 cursor-pointer hover:-translate-y-1 hover:shadow-2xl hover:border-amber-500 transition-all duration-300">
                    <div class="h-12 w-12 rounded-xl bg-amber-500/10 flex items-center justify-center text-amber-450 border border-amber-500/20">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
                        </svg>
                    </div>
                    <div>
                        <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Belum Check</span>
                        <span class="text-2xl font-black text-amber-450">{{ summary.belum_check }}</span>
                    </div>
                </div>

                <!-- Avg Reaction Time -->
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-xl flex items-center gap-4">
                    <div class="h-12 w-12 rounded-xl bg-slate-500/10 flex items-center justify-center text-slate-400 border border-slate-500/20">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                    </div>
                    <div>
                        <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Rata-rata Reaksi</span>
                        <span class="text-2xl font-black text-slate-200">{{ summary.avg_reaction_time_today }} ms</span>
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
                            <option value="belum_check">Belum Check</option>
                        </select>
                    </div>
                    <div>
                        <DatePicker v-model="date" />
                    </div>
                    <div class="flex gap-2">
                        <button @click="clearFilters" class="w-full md:w-auto px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-350 hover:text-slate-200 font-semibold text-sm rounded-lg transition-colors border border-slate-700/60">
                            Reset Filter
                        </button>
                        <button @click="exportData" class="w-full md:w-auto px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white font-semibold text-sm rounded-lg transition-colors border border-emerald-500/60 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                            </svg>
                            Export Excel
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
                                <th class="py-3 px-4 text-center">Konklusi</th>
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
                                    <span v-if="check.is_belum_tes" class="text-slate-500 font-medium">-</span>
                                    <span v-else>{{ formatDate(check.created_at) }}</span>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <!-- questionnaire_status can be nullable/boolean -->
                                    <span v-if="check.is_belum_tes || check.questionnaire_status === null" class="text-slate-500 font-medium">-</span>
                                    <span v-else-if="check.questionnaire_status" class="bg-emerald-500/10 text-emerald-400 border border-emerald-500/25 px-2.5 py-0.5 rounded-full text-xs font-semibold">
                                        Lolos
                                    </span>
                                    <span v-else class="bg-rose-500/10 text-rose-400 border border-rose-500/25 px-2.5 py-0.5 rounded-full text-xs font-semibold">
                                        Ada Keluhan
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-center font-bold text-slate-100">
                                    <span v-if="check.is_belum_tes || !check.reaction_time_ms" class="text-slate-500 font-normal">-</span>
                                    <span v-else>{{ check.reaction_time_ms }} ms</span>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <span v-if="check.is_belum_tes" class="bg-slate-700 text-slate-300 px-3 py-1 rounded-full text-xs font-extrabold shadow-lg shadow-slate-700/20">
                                        BELUM TES
                                    </span>
                                    <span v-else-if="check.is_fit === null" class="text-slate-500 font-medium">-</span>
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

        <!-- Export Progress Modal -->
        <div v-if="isExporting" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/80 backdrop-blur-sm">
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-2xl w-full max-w-md">
                <div class="flex items-center gap-4 mb-4">
                    <div class="h-10 w-10 rounded-xl bg-emerald-500/10 flex items-center justify-center text-emerald-400 border border-emerald-500/20">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6 animate-pulse">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-slate-100">Mengekspor Data</h3>
                        <p class="text-sm text-slate-400">Harap tunggu, sedang menyiapkan file Excel...</p>
                    </div>
                </div>
                
                <div class="space-y-2">
                    <div class="flex justify-between text-xs font-semibold">
                        <span class="text-slate-400">Progress</span>
                        <span class="text-emerald-400">{{ exportProgress }}%</span>
                    </div>
                    <div class="w-full bg-slate-800 rounded-full h-2.5 overflow-hidden">
                        <div class="bg-emerald-500 h-2.5 rounded-full transition-all duration-300 ease-out" :style="{ width: exportProgress + '%' }"></div>
                    </div>
                </div>
            </div>
        </div>

    </AdminDashboardLayout>
</template>
