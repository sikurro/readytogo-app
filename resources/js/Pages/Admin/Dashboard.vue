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

const liveStats = ref({ ...props.stats });
const liveLeaderboard = ref(props.top10Leaderboard || []);

// Chart Data States
const fatiguePieSeries = ref([
    liveStats.value?.fitToday || 0,
    liveStats.value?.unfitToday || 0,
    liveStats.value?.notTestedFatigueToday || 0
]);

const fatiguePieOptions = ref({
    chart: {
        type: 'donut',
        background: 'transparent'
    },
    theme: { mode: 'dark' },
    labels: ['Bugar (Fit)', 'Kelelahan (Unfit)', 'Belum Tes'],
    colors: ['#10b981', '#ef4444', '#64748b'], // Emerald, Rose, Slate
    legend: { show: false },
    stroke: { show: false },
    dataLabels: { enabled: true },
    plotOptions: {
        pie: {
            donut: {
                size: '75%',
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
        formatter: function (val, opts) {
            const fit = opts.w.globals.series[0][opts.dataPointIndex] || 0;
            const unfit = opts.w.globals.series[1][opts.dataPointIndex] || 0;
            if (fit > 0 || unfit > 0) {
                return val;
            }
            return '';
        },
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
            show: true,
            rotate: -45,
            rotateAlways: true,
            maxHeight: 45,
            style: {
                colors: '#64748b',
                fontSize: '9px'
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
        padding: {
            bottom: -10
        },
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
        show: false
    }
});

// Quiz Donut Chart (Today's Participation)
const quizDonutSeries = ref([liveStats.value?.quizTakenToday || 0, liveStats.value?.quizNotTakenToday || 0]);
const quizDonutOptions = ref({
    chart: {
        type: 'donut',
        background: 'transparent'
    },
    theme: { mode: 'dark' },
    labels: ['Mengerjakan', 'Belum Mengerjakan'],
    colors: ['#3b82f6', '#475569'],
    legend: { show: false },
    stroke: { show: false },
    dataLabels: { enabled: true },
    plotOptions: {
        pie: {
            donut: {
                size: '75%',
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

// Quiz 30-day Comprehension Donut Chart
const quiz30DaysDonutSeries = ref([
    props.stats?.quiz30DaysCorrect || 0,
    props.stats?.quiz30DaysWrong || 0
]);

const quiz30DaysDonutOptions = ref({
    chart: {
        type: 'donut',
        background: 'transparent'
    },
    theme: { mode: 'dark' },
    labels: ['Jawaban Benar', 'Jawaban Salah'],
    colors: ['#10b981', '#ef4444'], // Emerald, Rose
    legend: { show: false },
    stroke: { show: false },
    dataLabels: { enabled: true },
    plotOptions: {
        pie: {
            donut: {
                size: '75%',
                labels: {
                    show: true,
                    name: { show: true, fontSize: '12px', color: '#94a3b8' },
                    value: { show: true, fontSize: '20px', fontWeight: 'bold', color: '#f8fafc' },
                    total: {
                        show: true,
                        label: 'Total Soal',
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
        axisTicks: { show: false },
        labels: {
            show: true,
            rotate: -45,
            rotateAlways: true,
            maxHeight: 45,
            style: {
                colors: '#64748b',
                fontSize: '9px'
            }
        }
    },
    yaxis: {
        min: 0,
        max: 100,
        decimalsInFloat: 0,
        labels: {
            formatter: (val) => Math.round(val),
            style: {
                colors: '#64748b',
                fontSize: '10px'
            }
        }
    },
    grid: {
        borderColor: '#1e293b',
        strokeDashArray: 4,
        padding: {
            bottom: -10
        }
    },
    tooltip: { theme: 'dark' }
});

const getPercentage = (value, total) => {
    if (!total) return '0';
    return ((value / total) * 100).toFixed(1).replace('.0', '');
};

let pollingInterval = null;

const fetchChartData = async () => {
    try {
        const response = await axios.get('/admin/dashboard/chart-data');
        const data = response.data;

        // Update Fatigue Today Donut Chart
        if (data.fatigueToday) {
            liveStats.value.fitToday = data.fatigueToday.fit;
            liveStats.value.unfitToday = data.fatigueToday.unfit;
            liveStats.value.notTestedFatigueToday = data.fatigueToday.notTested;
            liveStats.value.testedFatigueToday = data.fatigueToday.fit + data.fatigueToday.unfit;

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

        // Update Quiz 30-day Donut Chart
        if (data.quiz30Days) {
            liveStats.value.quiz30DaysCorrect = data.quiz30Days.correct;
            liveStats.value.quiz30DaysWrong = data.quiz30Days.wrong;

            quiz30DaysDonutSeries.value = [
                data.quiz30Days.correct,
                data.quiz30Days.wrong
            ];
        }

        // Update Quiz Today
        if (data.quizToday) {
            liveStats.value.quizTakenToday = data.quizToday.taken;
            liveStats.value.quizNotTakenToday = data.quizToday.notTaken;

            quizDonutSeries.value = [
                data.quizToday.taken,
                data.quizToday.notTaken
            ];
        }

        // Update Leaderboard
        if (data.top10Leaderboard) {
            liveLeaderboard.value = data.top10Leaderboard;
        }

        // Update Quiz 30-day Trend Line Chart
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
                        <span class="text-2xl font-extrabold text-slate-100 mt-2">{{ liveStats.totalUsers }}</span>
                    </div>
                    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 flex flex-col justify-between shadow-lg">
                        <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Sudah Tes Hari Ini</span>
                        <span class="text-2xl font-extrabold text-indigo-400 mt-2">{{ liveStats.testedFatigueToday }}</span>
                    </div>
                    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 flex flex-col justify-between shadow-lg">
                        <span class="text-[10px] text-emerald-400 font-bold uppercase tracking-wider">Fit Hari Ini</span>
                        <span class="text-2xl font-extrabold text-emerald-400 mt-2">{{ liveStats.fitToday }}</span>
                    </div>
                    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 flex flex-col justify-between shadow-lg">
                        <span class="text-[10px] text-rose-500 font-bold uppercase tracking-wider">Unfit/Fatigue</span>
                        <span class="text-2xl font-extrabold text-rose-500 mt-2">{{ liveStats.unfitToday }}</span>
                    </div>
                    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 flex flex-col justify-between shadow-lg">
                        <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Belum Tes Hari Ini</span>
                        <span class="text-2xl font-extrabold text-slate-400 mt-2">{{ liveStats.notTestedFatigueToday }}</span>
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
                        <div class="flex-1 flex items-center justify-center min-h-[220px]">
                            <apexchart
                                type="donut"
                                height="220"
                                width="100%"
                                :options="fatiguePieOptions"
                                :series="fatiguePieSeries"
                            />
                        </div>
                        <div class="grid grid-cols-3 gap-2 pt-4 border-t border-slate-800 text-center">
                            <div>
                                <span class="inline-flex items-center gap-1 text-[10px] text-emerald-400 uppercase tracking-wider font-semibold">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Fit
                                </span>
                                <span class="text-lg font-bold text-emerald-400 block mt-1">
                                    {{ liveStats.fitToday }}
                                    <span class="text-xs text-slate-500 font-normal">({{ getPercentage(liveStats.fitToday, (liveStats.fitToday || 0) + (liveStats.unfitToday || 0) + (liveStats.notTestedFatigueToday || 0)) }}%)</span>
                                </span>
                            </div>
                            <div>
                                <span class="inline-flex items-center gap-1 text-[10px] text-rose-500 uppercase tracking-wider font-semibold">
                                    <span class="w-2 h-2 rounded-full bg-rose-500"></span> Unfit
                                </span>
                                <span class="text-lg font-bold text-rose-500 block mt-1">
                                    {{ liveStats.unfitToday }}
                                    <span class="text-xs text-slate-500 font-normal">({{ getPercentage(liveStats.unfitToday, (liveStats.fitToday || 0) + (liveStats.unfitToday || 0) + (liveStats.notTestedFatigueToday || 0)) }}%)</span>
                                </span>
                            </div>
                            <div>
                                <span class="inline-flex items-center gap-1 text-[10px] text-slate-400 uppercase tracking-wider font-semibold">
                                    <span class="w-2 h-2 rounded-full bg-slate-500"></span> Belum
                                </span>
                                <span class="text-lg font-bold text-slate-400 block mt-1">
                                    {{ liveStats.notTestedFatigueToday }}
                                    <span class="text-xs text-slate-500 font-normal">({{ getPercentage(liveStats.notTestedFatigueToday, (liveStats.fitToday || 0) + (liveStats.unfitToday || 0) + (liveStats.notTestedFatigueToday || 0)) }}%)</span>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Monthly Chart (Tren Fatigue Bulan Ini) -->
                    <div class="lg:col-span-2 bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl flex flex-col justify-between space-y-4">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div>
                                <h3 class="font-bold text-slate-200 text-sm">Tren Fatigue Bulan Ini</h3>
                                <p class="text-xs text-slate-500">Total volume uji fatigue harian bulan berjalan.</p>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="flex items-center gap-3 text-[10px]">
                                    <span class="flex items-center gap-1.5 text-slate-400 font-medium">
                                        <span class="w-2.5 h-2.5 rounded-full bg-[#10b981]"></span> Bugar
                                    </span>
                                    <span class="flex items-center gap-1.5 text-slate-400 font-medium">
                                        <span class="w-2.5 h-2.5 rounded-full bg-[#ef4444]"></span> Kelelahan
                                    </span>
                                    <span class="flex items-center gap-1.5 text-slate-400 font-medium">
                                        <span class="w-2.5 h-2.5 rounded-full bg-[#64748b]"></span> Belum Tes
                                    </span>
                                </div>
                                <span class="text-[10px] bg-slate-800 text-emerald-400 border border-emerald-500/20 px-2.5 py-1 rounded-full uppercase tracking-wider">Harian</span>
                            </div>
                        </div>
                        <div class="flex-1 min-h-[280px]">
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
                        <div class="flex-1 flex items-center justify-center min-h-[220px]">
                            <apexchart
                                type="donut"
                                height="220"
                                width="100%"
                                :options="quizDonutOptions"
                                :series="quizDonutSeries"
                            />
                        </div>
                        <div class="grid grid-cols-2 gap-4 pt-4 border-t border-slate-800 text-center">
                            <div>
                                <span class="inline-flex items-center gap-1 text-[10px] text-blue-400 uppercase tracking-wider font-semibold">
                                    <span class="w-2 h-2 rounded-full bg-blue-500"></span> Ikut Kuis
                                </span>
                                <span class="text-lg font-bold text-blue-400 block mt-1">
                                    {{ liveStats.quizTakenToday }}
                                    <span class="text-xs text-slate-500 font-normal">({{ getPercentage(liveStats.quizTakenToday, (liveStats.quizTakenToday || 0) + (liveStats.quizNotTakenToday || 0)) }}%)</span>
                                </span>
                            </div>
                            <div>
                                <span class="inline-flex items-center gap-1 text-[10px] text-slate-400 uppercase tracking-wider font-semibold">
                                    <span class="w-2 h-2 rounded-full bg-slate-500"></span> Belum Ikut
                                </span>
                                <span class="text-lg font-bold text-slate-300 block mt-1">
                                    {{ liveStats.quizNotTakenToday }}
                                    <span class="text-xs text-slate-500 font-normal">({{ getPercentage(liveStats.quizNotTakenToday, (liveStats.quizTakenToday || 0) + (liveStats.quizNotTakenToday || 0)) }}%)</span>
                                </span>
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

                            <div v-if="liveLeaderboard.length === 0" class="text-center py-12 text-slate-500 text-xs">
                                Belum ada petugas yang mengerjakan kuis harian pada bulan ini.
                            </div>

                            <div v-else class="max-h-[350px] overflow-y-auto pr-2 mt-2 divide-y divide-slate-800">
                                <div v-for="(user, index) in liveLeaderboard" :key="user.id" class="flex items-center justify-between py-3">
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
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Tingkat Pemahaman K3 (30 Hari Terakhir) Donut Chart -->
                    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl flex flex-col justify-between space-y-4">
                        <div>
                            <h3 class="font-bold text-slate-200 text-sm">Tingkat Pemahaman K3 (30 Hari Terakhir)</h3>
                            <p class="text-xs text-slate-500 mb-4">Perbandingan jawaban benar dan salah dari kuis 30 hari terakhir.</p>
                        </div>
                        <div class="flex-1 flex items-center justify-center min-h-[220px]">
                            <apexchart
                                type="donut"
                                height="220"
                                width="100%"
                                :options="quiz30DaysDonutOptions"
                                :series="quiz30DaysDonutSeries"
                            />
                        </div>
                        <div class="grid grid-cols-2 gap-4 pt-4 border-t border-slate-800 text-center">
                            <div>
                                <span class="inline-flex items-center gap-1 text-[10px] text-emerald-400 uppercase tracking-wider font-semibold">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Jawaban Benar
                                </span>
                                <span class="text-lg font-bold text-emerald-400 block mt-1">
                                    {{ liveStats.quiz30DaysCorrect }}
                                    <span class="text-xs text-slate-500 font-normal">({{ getPercentage(liveStats.quiz30DaysCorrect, (liveStats.quiz30DaysCorrect || 0) + (liveStats.quiz30DaysWrong || 0)) }}%)</span>
                                </span>
                            </div>
                            <div>
                                <span class="inline-flex items-center gap-1 text-[10px] text-rose-500 uppercase tracking-wider font-semibold">
                                    <span class="w-2 h-2 rounded-full bg-rose-500"></span> Jawaban Salah
                                </span>
                                <span class="text-lg font-bold text-rose-500 block mt-1">
                                    {{ liveStats.quiz30DaysWrong }}
                                    <span class="text-xs text-slate-500 font-normal">({{ getPercentage(liveStats.quiz30DaysWrong, (liveStats.quiz30DaysCorrect || 0) + (liveStats.quiz30DaysWrong || 0)) }}%)</span>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Comprehension Trend (Avg Accuracy) -->
                    <div class="lg:col-span-2 bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl flex flex-col justify-between space-y-4">
                        <div>
                            <h3 class="font-bold text-slate-200 text-sm">Persentase Tingkat Pemahaman K3 (30 Hari Terakhir)</h3>
                            <p class="text-xs text-slate-500">Tingkat akurasi jawaban benar petugas dalam kuis harian.</p>
                        </div>
                        <div class="flex-1 min-h-[280px]">
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
