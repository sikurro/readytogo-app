<script setup>
import AdminDashboardLayout from '@/Layouts/AdminDashboardLayout.vue';
import MonthPicker from '@/Components/MonthPicker.vue';
import Pagination from '@/Components/Pagination.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import { Line, Pie } from 'vue-chartjs';
import ChartDataLabels from 'chartjs-plugin-datalabels';
import { 
    Chart as ChartJS, 
    Title, 
    Tooltip, 
    Legend, 
    LineElement,
    PointElement,
    CategoryScale, 
    LinearScale, 
    ArcElement,
    Filler
} from 'chart.js';

ChartJS.register(Title, Tooltip, Legend, LineElement, PointElement, CategoryScale, LinearScale, ArcElement, Filler);

const props = defineProps({
    leaderboard: Object,
    locations: Array,
    dailyProgressData: Array,
    pieChartData: Object,
    filters: Object,
});

const month = ref(props.filters?.month || '');
const searchQuery = ref(props.filters?.search || '');
const filterLocation = ref(props.filters?.location || '');
const sortKey = ref(props.filters?.sort_key || 'score');
const sortDirection = ref(props.filters?.sort_dir || 'desc');

// State loading saat request filter sedang berjalan
const isLoading = ref(false);

const updateFilters = () => {
    isLoading.value = true;
    router.get(route('admin.leaderboard.daily'), {
        month: month.value,
        search: searchQuery.value,
        location: filterLocation.value,
        sort_key: sortKey.value,
        sort_dir: sortDirection.value,
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
        onFinish: () => { isLoading.value = false; }
    });
};

watch(month, () => {
    updateFilters();
});

watch(filterLocation, () => {
    updateFilters();
});

let searchTimeout;
watch(searchQuery, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        updateFilters();
    }, 450);
});

const resetFilters = () => {
    const today = new Date();
    const mm = String(today.getMonth() + 1).padStart(2, '0');
    const yyyy = today.getFullYear();
    month.value = `${yyyy}-${mm}`;
    searchQuery.value = '';
    filterLocation.value = '';
};

const exportToExcel = () => {
    const params = new URLSearchParams();
    if (month.value) params.append('month', month.value);
    if (searchQuery.value) params.append('search', searchQuery.value);
    if (filterLocation.value) params.append('location', filterLocation.value);
    if (sortKey.value) params.append('sort_key', sortKey.value);
    if (sortDirection.value) params.append('sort_dir', sortDirection.value);
    window.location.href = route('admin.leaderboard.daily.export') + '?' + params.toString();
};

// Line Chart Setup: Daily K3 Progress
const lineData = computed(() => {
    const labels = props.dailyProgressData.map(item => item.day);
    const avgAccuracies = props.dailyProgressData.map(item => item.avg_accuracy);
    
    return {
        labels,
        datasets: [
            {
                label: 'Akurasi Jawaban Benar (%)',
                borderColor: '#10b981', // emerald-500
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                data: avgAccuracies,
                tension: 0.35,
                fill: true,
                pointRadius: 4,
                pointHoverRadius: 6,
            }
        ]
    };
});

const lineOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            labels: {
                color: '#cbd5e1',
            }
        },
        tooltip: {
            mode: 'index',
            intersect: false,
            callbacks: {
                title: function(context) {
                    return 'Tanggal ' + context[0].label;
                },
                label: function(context) {
                    let value = context.parsed.y;
                    let formattedValue = value.toString().replace('.', ',');
                    return 'Akurasi Jawaban Benar: ' + formattedValue + '%';
                }
            }
        }
    },
    scales: {
        x: {
            grid: {
                color: '#334155',
            },
            ticks: {
                color: '#cbd5e1',
            },
            title: {
                display: true,
                text: 'Tanggal (Hari)',
                color: '#94a3b8',
                font: {
                    size: 11
                }
            }
        },
        y: {
            grid: {
                color: '#334155',
            },
            ticks: {
                color: '#cbd5e1',
            },
            min: 0,
            max: 100,
            title: {
                display: true,
                text: 'Akurasi (%)',
                color: '#94a3b8',
                font: {
                    size: 11
                }
            }
        }
    }
};

// Pie Chart Setup
const pieData = computed(() => {
    return {
        labels: ['Jawaban Benar', 'Jawaban Salah'],
        datasets: [
            {
                backgroundColor: ['#10b981', '#f43f5e'],
                data: [props.pieChartData.correct, props.pieChartData.wrong]
            }
        ]
    };
});

const pieOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: 'bottom',
            labels: {
                color: '#cbd5e1',
            }
        },
        datalabels: {
            color: '#ffffff',
            font: {
                weight: 'bold',
                size: 14
            },
            formatter: (value, context) => {
                let total = context.dataset.data.reduce((a, b) => a + b, 0);
                if (total > 0 && value > 0) {
                    return ((value / total) * 100).toFixed(2).replace('.', ',') + '%';
                }
                return '';
            }
        },
        tooltip: {
            callbacks: {
                label: function(context) {
                    let label = context.label || '';
                    let value = context.parsed;
                    let total = context.dataset.data.reduce((a, b) => a + b, 0);
                    let percentage = 0;
                    if (total > 0) {
                        percentage = ((value / total) * 100).toFixed(2).replace('.', ',');
                    }
                    if (label) {
                        label += ' : ';
                    }
                    return label + value + ' (' + percentage + '%)';
                }
            }
        }
    }
};

const hasDailyData = computed(() => {
    return props.dailyProgressData.some(item => item.total_attempts > 0);
});

// Search & Sorting Actions
const sortBy = (key) => {
    if (sortKey.value === key) {
        sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortKey.value = key;
        sortDirection.value = 'desc';
    }
    updateFilters();
};
</script>

<template>
    <Head title="Leaderboard & Rangkuman Data Quiz" />

    <AdminDashboardLayout>
        <template #header>
            <span>Leaderboard Kuis Harian & Rangkuman Data</span>
        </template>

        <div class="space-y-6">
            <!-- Filter & Action Controls -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-slate-900 border border-slate-800 p-6 rounded-2xl shadow-xl">
                <div class="flex items-center gap-3">
                    <div class="w-48">
                        <MonthPicker v-model="month" />
                    </div>
                    <button
                        @click="resetFilters"
                        class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-slate-200 font-semibold text-sm rounded-lg transition-colors duration-200"
                    >
                        Bulan Ini
                    </button>
                </div>

                <button
                    @click="exportToExcel"
                    class="flex items-center justify-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white font-semibold text-sm rounded-lg transition-colors duration-200 shadow-lg shadow-emerald-900/30"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                    </svg>
                    Export Excel
                </button>
            </div>

            <!-- Charts Section -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Line Chart: Daily Progress -->
                <div class="lg:col-span-2 bg-slate-900 border border-slate-800 p-6 rounded-2xl shadow-xl space-y-4">
                    <h4 class="font-bold text-slate-200 text-sm border-b border-slate-800 pb-3">Tren Perkembangan Pengetahuan K3 Petugas Harian</h4>
                    <div class="h-64 relative">
                        <Line v-if="hasDailyData" :data="lineData" :options="lineOptions" />
                        <div v-else class="h-full flex items-center justify-center text-slate-500 text-sm">
                            Tidak ada data aktivitas kuis untuk periode grafik ini
                        </div>
                    </div>
                </div>

                <!-- Pie Chart: Overall -->
                <div class="bg-slate-900 border border-slate-800 p-6 rounded-2xl shadow-xl space-y-4">
                    <h4 class="font-bold text-slate-200 text-sm border-b border-slate-800 pb-3">Persentase Tingkat Pemahaman</h4>
                    <div class="h-64 relative">
                        <Pie v-if="pieChartData.correct > 0 || pieChartData.wrong > 0" :data="pieData" :options="pieOptions" :plugins="[ChartDataLabels]" />
                        <div v-else class="h-full flex items-center justify-center text-slate-500 text-sm">
                            Tidak ada data jawaban kuis untuk periode grafik ini
                        </div>
                    </div>
                </div>
            </div>

            <!-- Leaderboard Table -->
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl space-y-4">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-800 pb-3">
                    <h3 class="font-bold text-slate-200">Leaderboard Kuis Petugas</h3>
                    <div class="flex flex-col sm:flex-row items-center gap-3 w-full md:w-auto">
                        <select
                            v-model="filterLocation"
                            class="w-full sm:w-48 bg-slate-950 border border-slate-850 text-slate-300 text-xs rounded-xl px-3 py-2 focus:outline-none focus:border-slate-700 transition-colors duration-200"
                        >
                            <option value="">Semua Lokasi</option>
                            <option v-for="loc in locations" :key="loc" :value="loc">{{ loc }}</option>
                        </select>
                        <div class="relative w-full sm:w-64">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-500">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.637 10.637z" />
                                </svg>
                            </span>
                            <input
                                v-model="searchQuery"
                                type="text"
                                placeholder="Cari nama, jabatan..."
                                class="w-full bg-slate-950 border border-slate-800 text-slate-200 placeholder-slate-500 rounded-xl pl-9 pr-4 py-2 text-xs focus:outline-none focus:border-slate-700 transition-colors duration-200"
                            />
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto relative transition-opacity duration-200" :class="{ 'opacity-50 pointer-events-none': isLoading }">
                    <!-- Loading overlay -->
                    <div v-if="isLoading" class="absolute inset-0 z-10 flex flex-col items-center justify-center gap-3 bg-slate-900/60 rounded-lg backdrop-blur-sm">
                        <svg class="animate-spin w-7 h-7 text-amber-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        <span class="text-xs text-amber-400 font-medium">Memuat data...</span>
                    </div>
                    <table class="min-w-full text-slate-300">
                        <thead>
                            <tr class="border-b border-slate-800 text-left text-xs font-bold uppercase tracking-wider text-slate-400">
                                <th class="py-3 px-4 text-center w-12">Pos</th>
                                <th class="py-3 px-4">Nama Petugas</th>
                                <th class="py-3 px-4">NIP</th>
                                <th class="py-3 px-4">Jabatan</th>
                                <th class="py-3 px-4">Lokasi/Unit Kerja</th>
                                <th @click="sortBy('score')" class="py-3 px-4 text-center cursor-pointer select-none hover:text-white transition-colors">
                                    <div class="flex items-center justify-center gap-1">
                                        Total Skor
                                        <span v-if="sortKey === 'score'" class="text-[10px] text-amber-500">{{ sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                    </div>
                                </th>
                                <th @click="sortBy('attendance')" class="py-3 px-4 text-center cursor-pointer select-none hover:text-white transition-colors">
                                    <div class="flex items-center justify-center gap-1">
                                        Kehadiran
                                        <span v-if="sortKey === 'attendance'" class="text-[10px] text-amber-500">{{ sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                    </div>
                                </th>
                                <th @click="sortBy('total_quizzes')" class="py-3 px-4 text-center cursor-pointer select-none hover:text-white transition-colors">
                                    <div class="flex items-center justify-center gap-1">
                                        Total Soal
                                        <span v-if="sortKey === 'total_quizzes'" class="text-[10px] text-amber-500">{{ sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                    </div>
                                </th>
                                <th @click="sortBy('correct')" class="py-3 px-4 text-center cursor-pointer select-none hover:text-white transition-colors">
                                    <div class="flex items-center justify-center gap-1">
                                        Benar
                                        <span v-if="sortKey === 'correct'" class="text-[10px] text-amber-500">{{ sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                    </div>
                                </th>
                                <th @click="sortBy('wrong')" class="py-3 px-4 text-center cursor-pointer select-none hover:text-white transition-colors">
                                    <div class="flex items-center justify-center gap-1">
                                        Salah
                                        <span v-if="sortKey === 'wrong'" class="text-[10px] text-amber-500">{{ sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                    </div>
                                </th>
                                <th @click="sortBy('accuracy')" class="py-3 px-4 text-center cursor-pointer select-none hover:text-white transition-colors">
                                    <div class="flex items-center justify-center gap-1">
                                        Persentase (%)
                                        <span v-if="sortKey === 'accuracy'" class="text-[10px] text-amber-500">{{ sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800 text-sm">
                            <tr v-for="(row, idx) in leaderboard.data" :key="row.user_id" class="hover:bg-slate-800/30 transition-colors duration-150">
                                <td class="py-4 px-4 text-center font-bold">
                                    <span v-if="row.rank === 1 && sortKey === 'score' && sortDirection === 'desc'" class="flex items-center justify-center w-6 h-6 rounded-full bg-amber-500 text-slate-950 mx-auto text-xs" title="Juara 1">1</span>
                                    <span v-else-if="row.rank === 2 && sortKey === 'score' && sortDirection === 'desc'" class="flex items-center justify-center w-6 h-6 rounded-full bg-slate-300 text-slate-950 mx-auto text-xs" title="Juara 2">2</span>
                                    <span v-else-if="row.rank === 3 && sortKey === 'score' && sortDirection === 'desc'" class="flex items-center justify-center w-6 h-6 rounded-full bg-amber-700 text-slate-100 mx-auto text-xs" title="Juara 3">3</span>
                                    <span v-else class="text-slate-400">{{ row.rank }}</span>
                                </td>
                                <td class="py-4 px-4 font-semibold text-slate-200">
                                    {{ row.name }}
                                </td>
                                <td class="py-4 px-4 text-xs font-medium text-slate-400">
                                    {{ row.nip || '-' }}
                                </td>
                                <td class="py-4 px-4 text-xs">
                                    {{ row.position || '-' }}
                                </td>
                                <td class="py-4 px-4 text-xs font-semibold text-amber-500/95">
                                    {{ row.location }}
                                </td>
                                <td class="py-4 px-4 text-center font-black text-amber-500 text-base">
                                    {{ row.total_score }}
                                </td>
                                <td class="py-4 px-4 text-center font-medium">
                                    {{ row.attendance }} Hari
                                </td>
                                <td class="py-4 px-4 text-center font-medium">
                                    {{ row.total_questions }}
                                </td>
                                <td class="py-4 px-4 text-center text-emerald-400 font-bold">
                                    {{ row.total_correct }}
                                </td>
                                <td class="py-4 px-4 text-center text-rose-400 font-bold">
                                    {{ row.total_wrong }}
                                </td>
                                <td class="py-4 px-4 text-center">
                                    <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-bold rounded-full"
                                          :class="row.percentage_correct >= 75 
                                              ? 'bg-emerald-950/40 text-emerald-400 border border-emerald-800/40' 
                                              : row.percentage_correct >= 50 
                                                  ? 'bg-amber-950/40 text-amber-400 border border-amber-800/40' 
                                                  : 'bg-rose-950/40 text-rose-400 border border-rose-800/40'">
                                        {{ row.percentage_correct }}%
                                    </span>
                                </td>
                            </tr>
                            <tr v-if="leaderboard.data.length === 0">
                                <td colspan="10" class="py-8 text-center text-slate-500 text-xs">
                                    Tidak ada data kuis untuk pencarian atau periode ini.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Section -->
                <div class="mt-6 flex justify-center">
                    <Pagination :links="leaderboard.links" />
                </div>
            </div>
        </div>
    </AdminDashboardLayout>
</template>
