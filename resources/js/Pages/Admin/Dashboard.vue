<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import AdminDashboardLayout from '@/Layouts/AdminDashboardLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import axios from 'axios';
import apexchart from 'vue3-apexcharts';

const props = defineProps({
    stats: Object,
    top10Leaderboard: Array,
});

// Chart Data States
const fatiguePieSeries = ref([
    props.stats?.fitToday || 0,
    props.stats?.unfitToday || 0,
    props.stats?.notTestedFatigueToday || 0
]);

const fatiguePieOptions = ref({
    chart: {
        type: 'donut',
        background: 'transparent'
    },
    theme: { mode: 'dark' },
    labels: ['Bugar (Fit)', 'Kelelahan (Unfit)', 'Belum Tes'],
    colors: ['#10b981', '#ef4444', '#64748b'], // Emerald, Rose, Slate
    legend: { position: 'bottom', labels: { colors: '#94a3b8' } },
    stroke: { show: false },
    dataLabels: { enabled: true },
    plotOptions: {
        pie: {
            donut: {
                size: '70%',
                labels: {
                    show: true,
                    name: { show: true, fontSize: '12px', color: '#94a3b8' },
                    value: { show: true, fontSize: '20px', fontWeight: 'bold', color: '#f8fafc' },
                    total: {
                        show: true,
                        label: 'Total Petugas',
                        color: '#94a3b8',
                        formatter: function (w) {
                            return w.globals.seriesTotals.reduce((a, b) => a + b, 0);
                        }
                    }
                }
            }
        }
    }
});

const fatigueMonthlySeries = ref([
    { name: 'Bugar (Fit)', data: [] },
    { name: 'Kelelahan (Unfit)', data: [] },
    { name: 'Belum Tes', data: [] }
]);

const fatigueMonthlyOptions = ref({
    chart: {
        id: 'fatigue-monthly',
        type: 'area',
        toolbar: { show: false },
        background: 'transparent'
    },
    theme: { mode: 'dark' },
    colors: ['#10b981', '#ef4444', '#64748b'],
    fill: {
        type: 'gradient',
        gradient: {
            shadeIntensity: 1,
            opacityFrom: 0.3,
            opacityTo: 0.05,
            stops: [0, 90, 100]
        }
    },
    stroke: { curve: 'smooth', width: 2 },
    dataLabels: {
        enabled: true,
        style: {
            fontSize: '9px',
            fontWeight: 'bold',
        },
        background: {
            enabled: true,
            foreColor: '#fff',
            padding: 3,
            borderRadius: 4,
            borderWidth: 0,
            opacity: 0.8
        },
        dropShadow: {
            enabled: false
        }
    },
    markers: {
        size: 0,
        hover: { size: 5 }
    },
    xaxis: {
        categories: [],
        axisBorder: { show: false },
        axisTicks: { show: false },
        labels: {
            style: {
                colors: '#64748b',
                fontSize: '10px'
            }
        }
    },
    yaxis: {
        min: 0,
        labels: {
            formatter: (val) => Math.round(val),
            style: {
                colors: '#64748b',
                fontSize: '10px'
            }
        }
    },
    grid: {
        show: true,
        borderColor: '#1e293b',
        strokeDashArray: 4,
        position: 'back',
        xaxis: {
            lines: {
                show: false
            }
        },
        yaxis: {
            lines: {
                show: true
            }
        }
    },
    tooltip: {
        theme: 'dark',
        x: { show: true },
        y: {
            formatter: (val) => `${Math.round(val)} Orang`
        }
    },
    legend: {
        position: 'top',
        horizontalAlign: 'right',
        labels: { colors: '#94a3b8' },
        markers: { radius: 12 }
    }
});

// Quiz Donut Chart (Today's Participation)
const quizDonutSeries = ref([props.stats?.quizTakenToday || 0, props.stats?.quizNotTakenToday || 0]);
const quizDonutOptions = ref({
    chart: {
        type: 'donut',
        background: 'transparent'
    },
    theme: { mode: 'dark' },
    labels: ['Mengerjakan', 'Belum Mengerjakan'],
    colors: ['#3b82f6', '#475569'],
    legend: { position: 'bottom', labels: { colors: '#94a3b8' } },
    stroke: { show: false },
    dataLabels: { enabled: true },
    plotOptions: {
        pie: {
            donut: {
                size: '70%',
                labels: {
                    show: true,
                    name: { show: true, fontSize: '12px', color: '#94a3b8' },
                    value: { show: true, fontSize: '20px', fontWeight: 'bold', color: '#f8fafc' },
                    total: {
                        show: true,
                        label: 'Total Petugas',
                        color: '#94a3b8',
                        formatter: function (w) {
                            return w.globals.seriesTotals.reduce((a, b) => a + b, 0);
                        }
                    }
                }
            }
        }
    }
});

// Quiz Knowledge Trend (30 Days)
const quizTrendScoreSeries = ref([
    { name: 'Rata-rata Skor', data: [] }
]);

const quizTrendScoreOptions = ref({
    chart: {
        id: 'quiz-score-trend',
        type: 'area',
        toolbar: { show: false },
        background: 'transparent'
    },
    theme: { mode: 'dark' },
    colors: ['#3b82f6'],
    fill: {
        type: 'gradient',
        gradient: {
            shadeIntensity: 1,
            opacityFrom: 0.4,
            opacityTo: 0.1,
            stops: [0, 90, 100]
        }
    },
    stroke: { curve: 'smooth', width: 2 },
    xaxis: {
        categories: [],
        axisBorder: { show: false },
        axisTicks: { show: false }
    },
    yaxis: {
        min: 0,
        max: 100,
        labels: {
            formatter: (val) => Math.round(val)
        }
    },
    grid: {
        borderColor: '#1e293b',
        strokeDashArray: 4
    },
    tooltip: { theme: 'dark' }
});

// Quiz Comprehension Trend (30 Days)
const quizTrendAccuracySeries = ref([
    { name: 'Persentase Tingkat Pemahaman (%)', data: [] }
]);

const quizTrendAccuracyOptions = ref({
    chart: {
        id: 'quiz-accuracy-trend',
        type: 'line',
        toolbar: { show: false },
        background: 'transparent'
    },
    theme: { mode: 'dark' },
    colors: ['#10b981'],
    stroke: { curve: 'smooth', width: 3 },
    xaxis: {
        categories: [],
        axisBorder: { show: false },
        axisTicks: { show: false }
    },
    yaxis: {
        min: 0,
        max: 100,
        labels: {
            formatter: (val) => Math.round(val) + '%'
        }
    },
    grid: {
        borderColor: '#1e293b',
        strokeDashArray: 4
    },
    tooltip: { theme: 'dark' }
});

let pollingInterval = null;

const fetchChartData = async () => {
    try {
        const response = await axios.get('/admin/dashboard/chart-data');
        const data = response.data;

        // Update Fatigue Today Donut Chart
        if (data.fatigueToday) {
            fatiguePieSeries.value = [
                data.fatigueToday.fit,
                data.fatigueToday.unfit,
                data.fatigueToday.notTested
            ];
        }

        // Update Fatigue Monthly
        fatigueMonthlySeries.value = [
            { name: 'Bugar (Fit)', data: data.fatigueMonthly.fit },
            { name: 'Kelelahan (Unfit)', data: data.fatigueMonthly.unfit },
            { name: 'Belum Tes', data: data.fatigueMonthly.notTested }
        ];
        fatigueMonthlyOptions.value = {
            ...fatigueMonthlyOptions.value,
            xaxis: { ...fatigueMonthlyOptions.value.xaxis, categories: data.fatigueMonthly.labels }
        };

        // Update Quiz 30-day Trend
        quizTrendScoreSeries.value = [
            { name: 'Rata-rata Skor', data: data.quizTrend.avgScore }
        ];
        quizTrendScoreOptions.value = {
            ...quizTrendScoreOptions.value,
            xaxis: { ...quizTrendScoreOptions.value.xaxis, categories: data.quizTrend.labels }
        };

        quizTrendAccuracySeries.value = [
            { name: 'Persentase Tingkat Pemahaman (%)', data: data.quizTrend.avgAccuracy }
        ];
        quizTrendAccuracyOptions.value = {
            ...quizTrendAccuracyOptions.value,
            xaxis: { ...quizTrendAccuracyOptions.value.xaxis, categories: data.quizTrend.labels }
        };

    } catch (error) {
        console.error('Gagal mengambil data chart:', error);
    }
};

onMounted(() => {
    fetchChartData();
    // Polling setiap 10 detik
    pollingInterval = setInterval(fetchChartData, 10000);
});

onUnmounted(() => {
    if (pollingInterval) {
        clearInterval(pollingInterval);
    }
});
</script>

<template>
    <Head title="Admin Dashboard - Ready To GO" />

    <AdminDashboardLayout>
        <template #header>
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-extrabold text-slate-100 tracking-tight">Ringkasan Operasional Keselamatan (K3)</h1>
                    <p class="text-sm text-slate-400 mt-1">Pantau indikator keselamatan kerja secara langsung (realtime).</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="relative flex h-3 w-3">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                    </span>
                    <span class="text-xs text-slate-300 font-bold bg-slate-800 border border-slate-700 px-3 py-1.5 rounded-lg uppercase tracking-wider">Pemantauan Aktif</span>
                </div>
            </div>
        </template>

        <div class="space-y-8 pb-12">
            <!-- Alert Info -->
            <div class="bg-gradient-to-r from-amber-500/10 to-amber-600/5 border border-amber-500/20 text-amber-300 rounded-2xl p-5 shadow-lg flex items-start gap-4">
                <div class="bg-amber-500/20 p-2 rounded-lg text-amber-400">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 111.083 1.083l-.042.02a.75.75 0 01-1.083-1.083zM12 20.25a8.25 8.25 0 100-16.5 8.25 8.25 0 000 16.5z" />
                    </svg>
                </div>
                <div>
                    <h4 class="font-bold text-sm text-slate-100">Selamat datang di Panel Administrasi R2G</h4>
                    <p class="text-xs text-slate-400 mt-1">Pantau kepatuhan K3, tren tingkat kelelahan, performa kuis, dan data kesehatan petugas pelabuhan secara efisien.</p>
                </div>
            </div>

            <!-- SECTION 1: FATIGUE CHECK -->
            <div class="space-y-6">
                <div class="flex items-center gap-3 border-b border-slate-800 pb-3">
                    <div class="p-2 bg-red-500/10 rounded-xl text-red-500">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0110 21a3.745 3.745 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0114 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z" />
                        </svg>
                    </div>
                    <h2 class="text-xl font-bold text-slate-100">Fatigue Check (Uji Reaksi Kelelahan)</h2>
                </div>

                <!-- Stats Grid Fatigue -->
                <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
                    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 flex flex-col justify-between shadow-lg">
                        <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Total Petugas</span>
                        <span class="text-2xl font-extrabold text-slate-100 mt-2">{{ stats.totalUsers }}</span>
                    </div>
                    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 flex flex-col justify-between shadow-lg">
                        <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Sudah Tes Hari Ini</span>
                        <span class="text-2xl font-extrabold text-indigo-400 mt-2">{{ stats.testedFatigueToday }}</span>
                    </div>
                    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 flex flex-col justify-between shadow-lg">
                        <span class="text-[10px] text-emerald-400 font-bold uppercase tracking-wider">Fit Hari Ini</span>
                        <span class="text-2xl font-extrabold text-emerald-400 mt-2">{{ stats.fitToday }}</span>
                    </div>
                    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 flex flex-col justify-between shadow-lg">
                        <span class="text-[10px] text-rose-500 font-bold uppercase tracking-wider">Unfit/Fatigue</span>
                        <span class="text-2xl font-extrabold text-rose-500 mt-2">{{ stats.unfitToday }}</span>
                    </div>
                    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 flex flex-col justify-between shadow-lg">
                        <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Belum Tes Hari Ini</span>
                        <span class="text-2xl font-extrabold text-slate-400 mt-2">{{ stats.notTestedFatigueToday }}</span>
                    </div>
                </div>

                <!-- Fatigue Charts Row -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Today's Fatigue Donut Chart -->
                    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl flex flex-col justify-between space-y-4">
                        <div>
                            <h3 class="font-bold text-slate-200 text-sm">Uji Fatigue Hari Ini</h3>
                            <p class="text-xs text-slate-500 mb-4">Rasio kepatuhan dan status uji fatigue petugas hari ini.</p>
                        </div>
                        <div class="flex-1 flex items-center justify-center">
                            <apexchart
                                width="100%"
                                :options="fatiguePieOptions"
                                :series="fatiguePieSeries"
                            />
                        </div>
                        <div class="grid grid-cols-3 gap-2 pt-4 border-t border-slate-800 text-center">
                            <div>
                                <span class="text-[10px] text-emerald-400 block uppercase tracking-wider">Fit</span>
                                <span class="text-lg font-bold text-emerald-400">{{ stats.fitToday }}</span>
                            </div>
                            <div>
                                <span class="text-[10px] text-rose-500 block uppercase tracking-wider">Unfit</span>
                                <span class="text-lg font-bold text-rose-500">{{ stats.unfitToday }}</span>
                            </div>
                            <div>
                                <span class="text-[10px] text-slate-400 block uppercase tracking-wider">Belum</span>
                                <span class="text-lg font-bold text-slate-400">{{ stats.notTestedFatigueToday }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Monthly Chart (Tren Fatigue Bulan Ini) -->
                    <div class="lg:col-span-2 bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl space-y-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="font-bold text-slate-200 text-sm">Tren Fatigue Bulan Ini</h3>
                                <p class="text-xs text-slate-500">Total volume uji fatigue harian bulan berjalan.</p>
                            </div>
                            <span class="text-[10px] bg-slate-800 text-emerald-400 border border-emerald-500/20 px-2.5 py-1 rounded-full uppercase tracking-wider">Harian</span>
                        </div>
                        <div class="h-80">
                            <apexchart
                                height="100%"
                                width="100%"
                                :options="fatigueMonthlyOptions"
                                :series="fatigueMonthlySeries"
                            />
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECTION 2: DAILY QUIZ & LEADERBOARD -->
            <div class="space-y-6">
                <div class="flex items-center gap-3 border-b border-slate-800 pb-3">
                    <div class="p-2 bg-blue-500/10 rounded-xl text-blue-500">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 013 3h-15a3 3 0 013-3m9 0v-3.375c0-.621-.504-1.125-1.125-1.125h-6.75a1.125 1.125 0 00-1.125 1.125v3.375m9 0h-9M9 10.5h.008v.008H9V10.5zm3 0h.008v.008H12V10.5zm3 0h.008v.008H15V10.5z" />
                        </svg>
                    </div>
                    <h2 class="text-xl font-bold text-slate-100">Kuis Harian & Leaderboard</h2>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Participation Pie/Donut Chart -->
                    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl flex flex-col justify-between space-y-4">
                        <div>
                            <h3 class="font-bold text-slate-200 text-sm">Partisipasi Kuis Hari Ini</h3>
                            <p class="text-xs text-slate-500 mb-4">Rasio petugas yang sudah vs belum menyelesaikan kuis harian.</p>
                        </div>
                        <div class="flex-1 flex items-center justify-center">
                            <apexchart
                                width="100%"
                                :options="quizDonutOptions"
                                :series="quizDonutSeries"
                            />
                        </div>
                        <div class="grid grid-cols-2 gap-4 pt-4 border-t border-slate-800 text-center">
                            <div>
                                <span class="text-[10px] text-slate-400 block uppercase tracking-wider">Ikut Kuis</span>
                                <span class="text-lg font-bold text-blue-400">{{ stats.quizTakenToday }}</span>
                            </div>
                            <div>
                                <span class="text-[10px] text-slate-400 block uppercase tracking-wider">Belum Ikut</span>
                                <span class="text-lg font-bold text-slate-300">{{ stats.quizNotTakenToday }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Top 10 Leaderboard Monthly Card -->
                    <div class="lg:col-span-2 bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl flex flex-col space-y-4">
                        <div>
                            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                                <div>
                                    <h3 class="font-bold text-slate-200 text-sm">10 Peringkat Teratas Bulan Ini</h3>
                                    <p class="text-xs text-slate-500">Leaderboard pengerjaan kuis harian petugas bulan berjalan.</p>
                                </div>
                                <Link :href="route('admin.leaderboard.daily')" class="text-xs text-blue-400 font-bold hover:underline flex items-center gap-1">
                                    Detail Leaderboard
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3.5 h-3.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                    </svg>
                                </Link>
                            </div>

                            <div v-if="top10Leaderboard.length === 0" class="text-center py-12 text-slate-500 text-xs">
                                Belum ada petugas yang mengerjakan kuis harian pada bulan ini.
                            </div>

                            <div v-else class="max-h-[350px] overflow-y-auto pr-2 mt-2 divide-y divide-slate-800">
                                <div v-for="(user, index) in top10Leaderboard" :key="user.id" class="flex items-center justify-between py-3">
                                    <div class="flex items-center gap-3">
                                        <!-- Rank Medal/Badge -->
                                        <div class="flex items-center justify-center w-6 h-6 rounded-full font-extrabold text-xs"
                                            :class="{
                                                'bg-amber-400 text-slate-950': index === 0,
                                                'bg-slate-300 text-slate-950': index === 1,
                                                'bg-amber-700 text-slate-100': index === 2,
                                                'bg-slate-800 text-slate-400': index > 2
                                            }">
                                            {{ index + 1 }}
                                        </div>
                                        <div>
                                            <h4 class="font-bold text-slate-200 text-sm">{{ user.name }}</h4>
                                            <p class="text-[10px] text-slate-400">
                                                NIP. {{ user.nip || '-' }} • {{ user.location?.name || 'Tidak Diketahui' }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-sm font-extrabold text-blue-400 block">{{ user.quiz_attempts_sum_score }} Pts</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quiz Trend Charts (30 Days) -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Knowledge Trend (Avg Score) -->
                    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl space-y-4">
                        <div>
                            <h3 class="font-bold text-slate-200 text-sm">Tren Perkembangan Pengetahuan K3 (30 Hari Terakhir)</h3>
                            <p class="text-xs text-slate-500">Grafik rata-rata skor kuis harian dalam 30 hari terakhir.</p>
                        </div>
                        <div class="h-72">
                            <apexchart
                                height="100%"
                                width="100%"
                                :options="quizTrendScoreOptions"
                                :series="quizTrendScoreSeries"
                            />
                        </div>
                    </div>

                    <!-- Comprehension Trend (Avg Accuracy) -->
                    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl space-y-4">
                        <div>
                            <h3 class="font-bold text-slate-200 text-sm">Persentase Tingkat Pemahaman K3 (30 Hari Terakhir)</h3>
                            <p class="text-xs text-slate-500">Tingkat akurasi jawaban benar petugas dalam kuis harian.</p>
                        </div>
                        <div class="h-72">
                            <apexchart
                                height="100%"
                                width="100%"
                                :options="quizTrendAccuracyOptions"
                                :series="quizTrendAccuracySeries"
                            />
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECTION 3: INCIDENT REPORTING PLACEHOLDER -->
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl space-y-4">
                <div class="flex items-center gap-3 border-b border-slate-800 pb-3">
                    <div class="p-2 bg-amber-500/10 rounded-xl text-amber-500">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                        </svg>
                    </div>
                    <h2 class="text-xl font-bold text-slate-100">Pelaporan Insiden & Bahaya</h2>
                </div>
                <div class="text-center py-10 text-slate-500 text-xs flex flex-col items-center justify-center space-y-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-10 h-10 text-slate-700">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.34 15.84c-.68.34-1.34.72-2 1.14m12.32-6.52A9 9 0 1112 3v9.75l8.66-3.83z" />
                    </svg>
                    <p class="font-semibold text-slate-400">Fitur Pelaporan Insiden & Bahaya</p>
                    <p class="text-slate-500 max-w-md">Modul statistik investigasi insiden maritim dan bahaya K3 pelabuhan akan diimplementasikan pada tahap selanjutnya.</p>
                </div>
            </div>
        </div>
    </AdminDashboardLayout>
</template>
