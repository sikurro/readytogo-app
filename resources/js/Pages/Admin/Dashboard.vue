<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import AdminDashboardLayout from '@/Layouts/AdminDashboardLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import axios from 'axios';
import apexchart from 'vue3-apexcharts';
import Modal from '@/Components/Modal.vue';
import IncidentDetailModal from '@/Components/IncidentDetailModal.vue';

const props = defineProps({
    stats: Object,
    top10Leaderboard: Array,
    latestIncidents: Array,
});

const liveStats = ref({ ...props.stats });
const liveLeaderboard = ref(props.top10Leaderboard || []);
const liveIncidents = ref(props.latestIncidents || []);

// Incident States
const incidentStats = ref({
    total: 0,
    open: 0,
    investigating: 0,
    closed: 0
});

const incidentPieSeries = ref([0, 0, 0]);
const incidentPieOptions = ref({
    chart: {
        type: 'donut',
        background: 'transparent'
    },
    theme: { mode: 'dark' },
    labels: ['Terbuka (Open)', 'Ditindak Lanjuti (Investigating)', 'Selesai (Closed)'],
    colors: ['#ef4444', '#3b82f6', '#10b981'], // Rose, Blue, Emerald
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
                        label: 'Total Insiden',
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

// Modal States for Fatigue
const showFatigueModal = ref(false);
const isFatigueModalLoading = ref(false);
const fatigueModalData = ref([]);
const fatigueModalTitle = ref('');

const openFatigueModal = async (status, title) => {
    showFatigueModal.value = true;
    isFatigueModalLoading.value = true;
    fatigueModalTitle.value = title;
    fatigueModalData.value = [];

    try {
        const response = await axios.get(`/admin/dashboard/fatigue-details?status=${status}`);
        fatigueModalData.value = response.data;
    } catch (error) {
        console.error('Failed to fetch fatigue details:', error);
    } finally {
        isFatigueModalLoading.value = false;
    }
};

// Modal States for Incidents
const showIncidentListModal = ref(false);
const isIncidentListLoading = ref(false);
const incidentListData = ref([]);
const incidentListTitle = ref('');

const openIncidentListModal = async (status, title) => {
    showIncidentListModal.value = true;
    isIncidentListLoading.value = true;
    incidentListTitle.value = title;
    incidentListData.value = [];

    try {
        const response = await axios.get(`/admin/dashboard/incident-details?status=${status}`);
        incidentListData.value = response.data;
    } catch (error) {
        console.error('Failed to fetch incident details:', error);
    } finally {
        isIncidentListLoading.value = false;
    }
};

const showIncidentDetail = ref(false);
const selectedIncident = ref(null);

const openIncidentDetail = (incident) => {
    selectedIncident.value = incident;
    showIncidentDetail.value = true;
};

const handleIncidentClickFromList = (incident) => {
    showIncidentListModal.value = false;
    openIncidentDetail(incident);
};

const handleIncidentUpdated = () => {
    showIncidentDetail.value = false;
    fetchChartData(); // Refresh dashboard data after update
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

        // Update Incidents Stats and List
        if (data.incidentData) {
            incidentStats.value = data.incidentData;
            incidentPieSeries.value = [
                data.incidentData.open,
                data.incidentData.investigating,
                data.incidentData.closed
            ];
        }

        if (data.latestIncidents) {
            liveIncidents.value = data.latestIncidents;
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
                    <div @click="openFatigueModal('total', 'Daftar Total Petugas')" class="bg-slate-900 border border-slate-800 rounded-2xl p-4 flex flex-col justify-between shadow-lg cursor-pointer group hover:-translate-y-1 hover:shadow-xl hover:border-slate-500 transition-all duration-300 relative">
                        <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider group-hover:text-slate-300 transition-colors">Total Petugas</span>
                        <span class="text-2xl font-extrabold text-slate-100 mt-2">{{ liveStats.totalUsers }}</span>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 absolute top-4 right-4 text-slate-700 group-hover:text-slate-400 transition-colors opacity-0 group-hover:opacity-100">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </div>
                    <div @click="openFatigueModal('tested', 'Petugas Sudah Tes Hari Ini')" class="bg-slate-900 border border-slate-800 rounded-2xl p-4 flex flex-col justify-between shadow-lg cursor-pointer group hover:-translate-y-1 hover:shadow-xl hover:border-indigo-500 transition-all duration-300 relative">
                        <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider group-hover:text-slate-300 transition-colors">Sudah Tes Hari Ini</span>
                        <span class="text-2xl font-extrabold text-indigo-400 mt-2">{{ liveStats.testedFatigueToday }}</span>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 absolute top-4 right-4 text-slate-700 group-hover:text-indigo-400 transition-colors opacity-0 group-hover:opacity-100">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </div>
                    <div @click="openFatigueModal('fit', 'Petugas Fit Hari Ini')" class="bg-slate-900 border border-slate-800 rounded-2xl p-4 flex flex-col justify-between shadow-lg cursor-pointer group hover:-translate-y-1 hover:shadow-xl hover:border-emerald-500 transition-all duration-300 relative">
                        <span class="text-[10px] text-emerald-400 font-bold uppercase tracking-wider group-hover:text-emerald-300 transition-colors">Fit Hari Ini</span>
                        <span class="text-2xl font-extrabold text-emerald-400 mt-2">{{ liveStats.fitToday }}</span>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 absolute top-4 right-4 text-slate-700 group-hover:text-emerald-400 transition-colors opacity-0 group-hover:opacity-100">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </div>
                    <div @click="openFatigueModal('unfit', 'Petugas Unfit/Fatigue')" class="bg-slate-900 border border-slate-800 rounded-2xl p-4 flex flex-col justify-between shadow-lg cursor-pointer group hover:-translate-y-1 hover:shadow-xl hover:border-rose-500 transition-all duration-300 relative">
                        <span class="text-[10px] text-rose-500 font-bold uppercase tracking-wider group-hover:text-rose-400 transition-colors">Unfit/Fatigue</span>
                        <span class="text-2xl font-extrabold text-rose-500 mt-2">{{ liveStats.unfitToday }}</span>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 absolute top-4 right-4 text-slate-700 group-hover:text-rose-400 transition-colors opacity-0 group-hover:opacity-100">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </div>
                    <div @click="openFatigueModal('not_tested', 'Petugas Belum Tes Hari Ini')" class="bg-slate-900 border border-slate-800 rounded-2xl p-4 flex flex-col justify-between shadow-lg cursor-pointer group hover:-translate-y-1 hover:shadow-xl hover:border-slate-500 transition-all duration-300 relative">
                        <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider group-hover:text-slate-300 transition-colors">Belum Tes Hari Ini</span>
                        <span class="text-2xl font-extrabold text-slate-400 mt-2">{{ liveStats.notTestedFatigueToday }}</span>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 absolute top-4 right-4 text-slate-700 group-hover:text-slate-400 transition-colors opacity-0 group-hover:opacity-100">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
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

            <!-- SECTION 3: INCIDENT REPORTING WIDGETS -->
            <div class="space-y-6">
                <div class="flex items-center gap-3 border-b border-slate-800 pb-3">
                    <div class="p-2 bg-amber-500/10 rounded-xl text-amber-500">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                        </svg>
                    </div>
                    <h2 class="text-xl font-bold text-slate-100">Pelaporan Insiden & Bahaya</h2>
                </div>

                <!-- Stats Grid Incidents -->
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                    <div @click="openIncidentListModal('all', 'Total Laporan Insiden')" class="bg-slate-900 border border-slate-800 rounded-2xl p-4 flex flex-col justify-between shadow-lg cursor-pointer group hover:-translate-y-1 hover:shadow-xl hover:border-amber-500 transition-all duration-300 relative">
                        <span class="text-[10px] text-amber-400 font-bold uppercase tracking-wider group-hover:text-amber-300 transition-colors">Total Insiden</span>
                        <span class="text-2xl font-extrabold text-amber-400 mt-2">{{ incidentStats.total }}</span>
                    </div>
                    <div @click="openIncidentListModal('open', 'Laporan Insiden Terbuka')" class="bg-slate-900 border border-slate-800 rounded-2xl p-4 flex flex-col justify-between shadow-lg cursor-pointer group hover:-translate-y-1 hover:shadow-xl hover:border-rose-500 transition-all duration-300 relative">
                        <span class="text-[10px] text-rose-500 font-bold uppercase tracking-wider group-hover:text-rose-400 transition-colors">Terbuka (Open)</span>
                        <span class="text-2xl font-extrabold text-rose-500 mt-2">{{ incidentStats.open }}</span>
                    </div>
                    <div @click="openIncidentListModal('investigating', 'Laporan Insiden Ditindak Lanjuti')" class="bg-slate-900 border border-slate-800 rounded-2xl p-4 flex flex-col justify-between shadow-lg cursor-pointer group hover:-translate-y-1 hover:shadow-xl hover:border-blue-500 transition-all duration-300 relative">
                        <span class="text-[10px] text-blue-400 font-bold uppercase tracking-wider group-hover:text-blue-300 transition-colors">Ditindak Lanjuti</span>
                        <span class="text-2xl font-extrabold text-blue-400 mt-2">{{ incidentStats.investigating }}</span>
                    </div>
                    <div @click="openIncidentListModal('closed', 'Laporan Insiden Selesai')" class="bg-slate-900 border border-slate-800 rounded-2xl p-4 flex flex-col justify-between shadow-lg cursor-pointer group hover:-translate-y-1 hover:shadow-xl hover:border-emerald-500 transition-all duration-300 relative">
                        <span class="text-[10px] text-emerald-400 font-bold uppercase tracking-wider group-hover:text-emerald-300 transition-colors">Selesai (Closed)</span>
                        <span class="text-2xl font-extrabold text-emerald-400 mt-2">{{ incidentStats.closed }}</span>
                    </div>
                </div>

                <!-- Incidents Chart & Latest Reports Row -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Donut Chart -->
                    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl flex flex-col justify-between space-y-4">
                        <div>
                            <h3 class="font-bold text-slate-200 text-sm">Status Laporan Insiden</h3>
                            <p class="text-xs text-slate-500 mb-4 font-normal">Rasio penyelesaian laporan insiden & bahaya.</p>
                        </div>
                        <div class="flex-1 flex items-center justify-center min-h-[220px]">
                            <apexchart
                                type="donut"
                                height="220"
                                width="100%"
                                :options="incidentPieOptions"
                                :series="incidentPieSeries"
                            />
                        </div>
                        <div class="grid grid-cols-3 gap-2 pt-4 border-t border-slate-800 text-center">
                            <div>
                                <span class="inline-flex items-center gap-1 text-[10px] text-rose-500 uppercase tracking-wider font-semibold">
                                    <span class="w-2 h-2 rounded-full bg-rose-500"></span> Terbuka
                                </span>
                                <span class="text-base font-bold text-rose-500 block mt-1">
                                    {{ incidentStats.open }}
                                    <span class="text-[10px] text-slate-500 font-normal">({{ getPercentage(incidentStats.open, incidentStats.total) }}%)</span>
                                </span>
                            </div>
                            <div>
                                <span class="inline-flex items-center gap-1 text-[10px] text-blue-500 uppercase tracking-wider font-semibold">
                                    <span class="w-2 h-2 rounded-full bg-blue-500"></span> Proses
                                </span>
                                <span class="text-base font-bold text-blue-500 block mt-1">
                                    {{ incidentStats.investigating }}
                                    <span class="text-[10px] text-slate-500 font-normal">({{ getPercentage(incidentStats.investigating, incidentStats.total) }}%)</span>
                                </span>
                            </div>
                            <div>
                                <span class="inline-flex items-center gap-1 text-[10px] text-emerald-400 uppercase tracking-wider font-semibold">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Selesai
                                </span>
                                <span class="text-base font-bold text-emerald-400 block mt-1">
                                    {{ incidentStats.closed }}
                                    <span class="text-[10px] text-slate-500 font-normal">({{ getPercentage(incidentStats.closed, incidentStats.total) }}%)</span>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Latest 5 reports table -->
                    <div class="lg:col-span-2 bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl flex flex-col space-y-4">
                        <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                            <div>
                                <h3 class="font-bold text-slate-200 text-sm">5 Laporan Insiden Terbaru</h3>
                                <p class="text-xs text-slate-500 font-normal">Laporan insiden keselamatan dan bahaya K3 teranyar.</p>
                            </div>
                            <Link :href="route('admin.incidents.index')" class="text-xs text-blue-400 font-bold hover:underline flex items-center gap-1">
                                Semua Laporan
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3.5 h-3.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                </svg>
                            </Link>
                        </div>

                        <div v-if="liveIncidents.length === 0" class="text-center py-12 text-slate-500 text-xs">
                            Belum ada laporan insiden yang masuk.
                        </div>

                        <div v-else class="overflow-x-auto pr-2 mt-2">
                            <table class="w-full text-left text-sm text-slate-300">
                                <thead class="text-xs uppercase bg-slate-850 text-slate-400">
                                    <tr>
                                        <th scope="col" class="px-3 py-2 rounded-l-lg">Pelapor / Waktu</th>
                                        <th scope="col" class="px-3 py-2">Kategori & Deskripsi</th>
                                        <th scope="col" class="px-3 py-2 text-center rounded-r-lg">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-800">
                                    <tr v-for="incident in liveIncidents" :key="incident.id" @click="openIncidentDetail(incident)" class="border-b border-slate-800/50 hover:bg-slate-800/30 cursor-pointer transition-colors">
                                        <td class="px-3 py-3">
                                            <div class="font-bold text-slate-200 text-xs">{{ incident.user?.name || 'Petugas' }}</div>
                                            <div class="text-[10px] text-slate-500">{{ new Date(incident.created_at).toLocaleDateString('id-ID', {day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit'}) }}</div>
                                        </td>
                                        <td class="px-3 py-3">
                                            <span class="px-2 py-0.5 text-[10px] font-bold rounded bg-slate-800 text-slate-300 border border-slate-700 capitalize">{{ incident.category }}</span>
                                            <div class="text-xs text-slate-400 mt-1 line-clamp-1 max-w-[200px]" :title="incident.description">
                                                {{ incident.description }}
                                            </div>
                                        </td>
                                        <td class="px-3 py-3 text-center">
                                            <span class="px-2.5 py-1 text-[10px] uppercase font-bold rounded-full"
                                                :class="{
                                                    'bg-rose-500/20 text-rose-500': incident.status === 'open',
                                                    'bg-blue-500/20 text-blue-400': incident.status === 'investigating',
                                                    'bg-emerald-500/20 text-emerald-400': incident.status === 'closed'
                                                }">
                                                {{ incident.status === 'open' ? 'Terbuka' : (incident.status === 'investigating' ? 'Proses' : 'Selesai') }}
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Fatigue Detail Modal -->
        <Modal :show="showFatigueModal" @close="showFatigueModal = false" maxWidth="2xl">
            <div class="bg-slate-900 border border-slate-800 rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-800 flex items-center justify-between">
                    <h3 class="text-lg font-bold text-slate-100">{{ fatigueModalTitle }}</h3>
                    <button @click="showFatigueModal = false" class="text-slate-400 hover:text-slate-200">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="p-6">
                    <div v-if="isFatigueModalLoading" class="flex flex-col items-center justify-center py-12">
                        <svg class="animate-spin h-8 w-8 text-blue-500 mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span class="text-sm text-slate-400">Memuat data petugas...</span>
                    </div>
                    <div v-else>
                        <div v-if="fatigueModalData.length === 0" class="text-center py-8 text-slate-500">
                            Tidak ada data petugas untuk kategori ini.
                        </div>
                        <div v-else class="max-h-[60vh] overflow-y-auto pr-2">
                            <table class="w-full text-left text-sm text-slate-300">
                                <thead class="text-xs uppercase bg-slate-800 text-slate-400 sticky top-0">
                                    <tr>
                                        <th scope="col" class="px-4 py-3 rounded-tl-lg">Nama / NIP</th>
                                        <th scope="col" class="px-4 py-3">Lokasi</th>
                                        <th scope="col" class="px-4 py-3 text-center">Waktu Tes</th>
                                        <th scope="col" class="px-4 py-3 text-center rounded-tr-lg">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(user, index) in fatigueModalData" :key="user.id" class="border-b border-slate-800 hover:bg-slate-800/50">
                                        <td class="px-4 py-3">
                                            <div class="font-bold text-slate-200">{{ user.name }}</div>
                                            <div class="text-xs text-slate-500">{{ user.nip }}</div>
                                        </td>
                                        <td class="px-4 py-3 text-slate-400">{{ user.location }}</td>
                                        <td class="px-4 py-3 text-center text-slate-400">{{ user.time || '-' }}</td>
                                        <td class="px-4 py-3 text-center">
                                            <span class="px-2.5 py-1 text-[10px] uppercase font-bold rounded-full"
                                                :class="{
                                                    'bg-emerald-500/20 text-emerald-400': user.status_label === 'Fit',
                                                    'bg-rose-500/20 text-rose-500': user.status_label === 'Unfit',
                                                    'bg-slate-800 text-slate-400': user.status_label === 'Belum Tes'
                                                }">
                                                {{ user.status_label }}
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </Modal>

        <!-- Incident List Modal -->
        <Modal :show="showIncidentListModal" @close="showIncidentListModal = false" maxWidth="3xl">
            <div class="bg-slate-900 border border-slate-800 rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-800 flex items-center justify-between">
                    <h3 class="text-lg font-bold text-slate-100">{{ incidentListTitle }}</h3>
                    <button @click="showIncidentListModal = false" class="text-slate-400 hover:text-slate-200">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="p-6">
                    <div v-if="isIncidentListLoading" class="flex flex-col items-center justify-center py-12">
                        <svg class="animate-spin h-8 w-8 text-amber-500 mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span class="text-sm text-slate-400">Memuat data insiden...</span>
                    </div>
                    <div v-else>
                        <div v-if="incidentListData.length === 0" class="text-center py-8 text-slate-500">
                            Tidak ada data laporan untuk kategori ini.
                        </div>
                        <div v-else class="max-h-[60vh] overflow-y-auto pr-2">
                            <table class="w-full text-left text-sm text-slate-300">
                                <thead class="text-xs uppercase bg-slate-800 text-slate-400 sticky top-0">
                                    <tr>
                                        <th scope="col" class="px-4 py-3 rounded-tl-lg">Pelapor / Waktu</th>
                                        <th scope="col" class="px-4 py-3">Kategori & Deskripsi</th>
                                        <th scope="col" class="px-4 py-3 text-center rounded-tr-lg">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="incident in incidentListData" :key="incident.id" @click="handleIncidentClickFromList(incident)" class="border-b border-slate-800 hover:bg-slate-800/50 cursor-pointer">
                                        <td class="px-4 py-3">
                                            <div class="font-bold text-slate-200">{{ incident.user?.name || 'Petugas' }}</div>
                                            <div class="text-[10px] text-slate-500">{{ new Date(incident.created_at).toLocaleDateString('id-ID', {day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit'}) }}</div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="px-2 py-0.5 text-[10px] font-bold rounded bg-slate-800 text-slate-300 border border-slate-700 capitalize">{{ incident.category }}</span>
                                            <div class="text-xs text-slate-400 mt-1 line-clamp-1 max-w-[300px]" :title="incident.description">
                                                {{ incident.description }}
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <span class="px-2.5 py-1 text-[10px] uppercase font-bold rounded-full"
                                                :class="{
                                                    'bg-rose-500/20 text-rose-500': incident.status === 'open',
                                                    'bg-blue-500/20 text-blue-400': incident.status === 'investigating',
                                                    'bg-emerald-500/20 text-emerald-400': incident.status === 'closed'
                                                }">
                                                {{ incident.status === 'open' ? 'Terbuka' : (incident.status === 'investigating' ? 'Proses' : 'Selesai') }}
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </Modal>

        <!-- Incident Detail Modal -->
        <IncidentDetailModal 
            :is-open="showIncidentDetail"
            :incident="selectedIncident"
            @close="showIncidentDetail = false"
            @updated="handleIncidentUpdated"
        />
    </AdminDashboardLayout>
</template>
