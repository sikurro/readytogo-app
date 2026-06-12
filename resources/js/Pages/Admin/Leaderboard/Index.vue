<script setup>
import AdminDashboardLayout from '@/Layouts/AdminDashboardLayout.vue';
import MonthPicker from '@/Components/MonthPicker.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import { Line, Pie } from 'vue-chartjs';
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
    leaderboard: Array,
    dailyProgressData: Array,
    pieChartData: Object,
    filters: Object,
});

const month = ref(props.filters?.month || '');

const updateFilters = () => {
    router.get(route('admin.leaderboard.index'), {
        month: month.value,
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true
    });
};

watch(month, () => {
    updateFilters();
});

const resetFilters = () => {
    // Default to current month year (YYYY-MM)
    const today = new Date();
    const mm = String(today.getMonth() + 1).padStart(2, '0');
    const yyyy = today.getFullYear();
    month.value = `${yyyy}-${mm}`;
};

const exportToExcel = () => {
    const params = new URLSearchParams();
    if (month.value) params.append('month', month.value);
    window.location.href = route('admin.leaderboard.export') + '?' + params.toString();
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
        }
    }
};

const hasDailyData = computed(() => {
    return props.dailyProgressData.some(item => item.total_attempts > 0);
});
</script>

<template>
    <Head title="Leaderboard & Rangkuman Data Quiz" />

    <AdminDashboardLayout>
        <template #header>
            <span>Leaderboard & Rangkuman Data Quiz</span>
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
                        <Pie v-if="pieChartData.correct > 0 || pieChartData.wrong > 0" :data="pieData" :options="pieOptions" />
                        <div v-else class="h-full flex items-center justify-center text-slate-500 text-sm">
                            Tidak ada data jawaban kuis untuk periode grafik ini
                        </div>
                    </div>
                </div>
            </div>

            <!-- Leaderboard Table -->
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl space-y-4">
                <h3 class="font-bold text-slate-200 border-b border-slate-800 pb-3">Klasemen Kuis Petugas</h3>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-slate-300">
                        <thead>
                            <tr class="border-b border-slate-800 text-left text-xs font-bold uppercase tracking-wider text-slate-400">
                                <th class="py-3 px-4 text-center w-12">Pos</th>
                                <th class="py-3 px-4">Nama Petugas</th>
                                <th class="py-3 px-4">NIP</th>
                                <th class="py-3 px-4">Jabatan</th>
                                <th class="py-3 px-4">Lokasi/Unit Kerja</th>
                                <th class="py-3 px-4 text-center">Total Skor</th>
                                <th class="py-3 px-4 text-center">Total Soal</th>
                                <th class="py-3 px-4 text-center">Benar</th>
                                <th class="py-3 px-4 text-center">Salah</th>
                                <th class="py-3 px-4 text-center">Persentase (%)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800 text-sm">
                            <tr v-for="(row, idx) in leaderboard" :key="row.user_id" class="hover:bg-slate-800/30 transition-colors duration-150">
                                <td class="py-4 px-4 text-center font-bold">
                                    <span v-if="idx === 0" class="flex items-center justify-center w-6 h-6 rounded-full bg-amber-500 text-slate-950 mx-auto text-xs" title="Juara 1">1</span>
                                    <span v-else-if="idx === 1" class="flex items-center justify-center w-6 h-6 rounded-full bg-slate-300 text-slate-950 mx-auto text-xs" title="Juara 2">2</span>
                                    <span v-else-if="idx === 2" class="flex items-center justify-center w-6 h-6 rounded-full bg-amber-700 text-slate-100 mx-auto text-xs" title="Juara 3">3</span>
                                    <span v-else class="text-slate-400">{{ idx + 1 }}</span>
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
                            <tr v-if="leaderboard.length === 0">
                                <td colspan="10" class="py-8 text-center text-slate-500 text-xs">
                                    Tidak ada data kuis untuk periode ini.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AdminDashboardLayout>
</template>
