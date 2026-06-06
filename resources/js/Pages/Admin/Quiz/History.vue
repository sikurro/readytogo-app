<script setup>
import AdminDashboardLayout from '@/Layouts/AdminDashboardLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    history: Object, // Laravel Paginated object
    filters: Object,
});

const search = ref(props.filters?.search || '');
const month = ref(props.filters?.month || '');
const monthInputRef = ref(null);

const triggerMonthPicker = () => {
    if (monthInputRef.value) {
        try {
            monthInputRef.value.showPicker();
        } catch (error) {
            console.error("showPicker not supported or failed:", error);
        }
    }
};

const clearMonth = () => {
    month.value = '';
};
const sortField = ref(props.filters?.sort_field || 'tanggal');
const sortDirection = ref(props.filters?.sort_direction || 'desc');

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

const updateFilters = () => {
    router.get(route('admin.quiz.history'), {
        search: search.value,
        month: month.value,
        sort_field: sortField.value,
        sort_direction: sortDirection.value,
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true
    });
};

// Debounce search input
let debounceTimeout = null;
watch(search, () => {
    clearTimeout(debounceTimeout);
    debounceTimeout = setTimeout(() => {
        updateFilters();
    }, 400);
});

// Watch month and update immediately
watch(month, () => {
    updateFilters();
});

const sortBy = (field) => {
    if (sortField.value === field) {
        sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortField.value = field;
        sortDirection.value = 'desc';
    }
    updateFilters();
};

const resetFilters = () => {
    search.value = '';
    month.value = '';
    sortField.value = 'tanggal';
    sortDirection.value = 'desc';
    updateFilters();
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

                <!-- Filter Controls -->
                <div class="flex flex-col md:flex-row md:items-center gap-4 bg-slate-800/40 p-4 rounded-xl border border-slate-800">
                    <div class="flex-1 relative">
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Cari nama petugas atau NIP..."
                            class="w-full bg-slate-900 border border-slate-700 rounded-lg py-2 pl-10 pr-4 text-sm text-slate-200 placeholder-slate-500 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition-colors duration-200"
                        />
                        <span class="absolute left-3 top-2.5 text-slate-500">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.604 10.604z" />
                            </svg>
                        </span>
                    </div>

                    <div 
                        class="w-full md:w-48 relative cursor-pointer group"
                        @click="triggerMonthPicker"
                    >
                        <input
                            ref="monthInputRef"
                            v-model="month"
                            type="month"
                            class="w-full bg-slate-900 border border-slate-700 rounded-lg py-2 pl-10 pr-8 text-sm text-slate-200 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition-colors duration-200 cursor-pointer"
                        />
                        <span class="absolute left-3 top-2.5 text-slate-400 group-hover:text-amber-500 transition-colors duration-200 pointer-events-none">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5m-9-6h.008v.008H12v-.008zM12 15h.008v.008H12V15zm0 2.25h.008v.008H12v-.008zM9.75 15h.008v.008H9.75V15zm0 2.25h.008v.008H9.75v-.008zM7.5 15h.008v.008H7.5V15zm0 2.25h.008v.008H7.5v-.008zm6.75-4.5h.008v.008h-.008v-.008zm0 2.25h.008v.008h-.008V15zm0 2.25h.008v.008h-.008v-.008zm2.25-4.5h.008v.008H16.5v-.008zm0 2.25h.008v.008H16.5V15z" />
                            </svg>
                        </span>
                        <span 
                            v-if="month"
                            @click.stop="clearMonth"
                            class="clear-btn absolute right-3 top-2.5 text-slate-500 hover:text-red-400 cursor-pointer transition-colors duration-200 z-10"
                            title="Hapus filter bulan"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </span>
                    </div>

                    <div>
                        <button
                            @click="resetFilters"
                            class="w-full md:w-auto px-4 py-2 bg-slate-700 hover:bg-slate-600 text-slate-200 font-semibold text-sm rounded-lg transition-colors duration-200"
                        >
                            Reset
                        </button>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-slate-300">
                        <thead>
                            <tr class="border-b border-slate-800 text-left text-xs font-bold uppercase tracking-wider text-slate-400">
                                <th @click="sortBy('petugas')" class="py-3 px-4 cursor-pointer select-none hover:text-slate-200 transition-colors duration-150">
                                    <div class="flex items-center gap-1">
                                        Petugas
                                        <span v-if="sortField === 'petugas'">{{ sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                    </div>
                                </th>
                                <th @click="sortBy('nip')" class="py-3 px-4 cursor-pointer select-none hover:text-slate-200 transition-colors duration-150">
                                    <div class="flex items-center gap-1">
                                        NIP
                                        <span v-if="sortField === 'nip'">{{ sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                    </div>
                                </th>
                                <th @click="sortBy('tanggal')" class="py-3 px-4 cursor-pointer select-none hover:text-slate-200 transition-colors duration-150">
                                    <div class="flex items-center gap-1">
                                        Tanggal
                                        <span v-if="sortField === 'tanggal'">{{ sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                    </div>
                                </th>
                                <th @click="sortBy('kuis')" class="py-3 px-4 cursor-pointer select-none hover:text-slate-200 transition-colors duration-150">
                                    <div class="flex items-center gap-1">
                                        Nama Kuis
                                        <span v-if="sortField === 'kuis'">{{ sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                    </div>
                                </th>
                                <th @click="sortBy('score')" class="py-3 px-4 text-center cursor-pointer select-none hover:text-slate-200 transition-colors duration-150">
                                    <div class="flex items-center justify-center gap-1">
                                        Skor
                                        <span v-if="sortField === 'score'">{{ sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                    </div>
                                </th>
                                <th @click="sortBy('correct_answers')" class="py-3 px-4 text-center cursor-pointer select-none hover:text-slate-200 transition-colors duration-150">
                                    <div class="flex items-center justify-center gap-1">
                                        Jawaban Benar
                                        <span v-if="sortField === 'correct_answers'">{{ sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                    </div>
                                </th>
                                <th @click="sortBy('durasi')" class="py-3 px-4 text-center cursor-pointer select-none hover:text-slate-200 transition-colors duration-150">
                                    <div class="flex items-center justify-center gap-1">
                                        Durasi
                                        <span v-if="sortField === 'durasi'">{{ sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                    </div>
                                </th>
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

<style scoped>
/* Stretches the calendar picker indicator to make the entire input clickable */
input[type="month"]::-webkit-calendar-picker-indicator {
    background: transparent;
    bottom: 0;
    color: transparent;
    cursor: pointer;
    height: auto;
    left: 0;
    position: absolute;
    right: 0;
    top: 0;
    width: auto;
}
</style>
