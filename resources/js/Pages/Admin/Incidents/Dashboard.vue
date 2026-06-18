<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AdminDashboardLayout from '@/Layouts/AdminDashboardLayout.vue';
import { ref, onMounted } from 'vue';
import apexchart from 'vue3-apexcharts';

const props = defineProps({
    stats: Object,
    trend: Object,
    composition: Object,
    mapIncidents: Array,
    topReporters: Array,
    criticalIncidents: Array,
});

// Trend Chart (Line/Area Chart)
const trendSeries = ref([
    {
        name: 'Jumlah Laporan',
        data: props.trend.data || []
    }
]);

const trendOptions = ref({
    chart: {
        id: 'incidents-trend',
        type: 'area',
        toolbar: { show: false },
        background: 'transparent'
    },
    theme: { mode: 'dark' },
    colors: ['#3b82f6'], // Blue
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
    dataLabels: { enabled: true },
    xaxis: {
        categories: props.trend.labels || [],
        axisBorder: { show: false },
        axisTicks: { show: false },
        labels: {
            style: {
                colors: '#94a3b8',
                fontSize: '11px'
            }
        }
    },
    yaxis: {
        labels: {
            style: {
                colors: '#94a3b8',
                fontSize: '11px'
            }
        }
    },
    grid: {
        borderColor: '#1e293b',
        strokeDashArray: 4
    },
    tooltip: { theme: 'dark' }
});

// Composition Chart (Donut Chart)
const compositionSeries = ref([
    props.composition.unsafe_condition || 0,
    props.composition.unsafe_act || 0,
    props.composition.near_miss || 0,
    props.composition.positive_observation || 0
]);

const compositionOptions = ref({
    chart: {
        type: 'donut',
        background: 'transparent'
    },
    theme: { mode: 'dark' },
    labels: ['Kondisi Tidak Aman', 'Tindakan Tidak Aman', 'Hampir Celaka', 'Observasi Positif'],
    colors: ['#f59e0b', '#f97316', '#f43f5e', '#10b981'], // Amber, Orange, Rose, Emerald
    legend: {
        show: true,
        position: 'bottom',
        fontSize: '11px',
        labels: {
            colors: '#94a3b8'
        }
    },
    stroke: { show: false },
    dataLabels: { enabled: true },
    plotOptions: {
        pie: {
            donut: {
                size: '70%',
                labels: {
                    show: true,
                    name: { show: true, fontSize: '11px', color: '#94a3b8' },
                    value: { show: true, fontSize: '18px', fontWeight: 'bold', color: '#f8fafc' },
                    total: {
                        show: true,
                        label: 'Total Laporan',
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

// Leaflet Map integration
let map = null;
let markersGroup = null;

const loadLeaflet = () => {
    return new Promise((resolve) => {
        if (window.L) {
            resolve(window.L);
            return;
        }

        // Load CSS
        const link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
        document.head.appendChild(link);

        // Load JS
        const script = document.createElement('script');
        script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
        script.onload = () => resolve(window.L);
        document.body.appendChild(script);
    });
};

const initializeMap = (L) => {
    if (map) return;

    // Default center at Tanjung Priok / general Indonesia ports if no reports
    let centerLat = -6.103;
    let centerLng = 106.878;

    const reportedWithCoords = props.mapIncidents.filter(i => i.latitude && i.longitude);
    if (reportedWithCoords.length > 0) {
        centerLat = parseFloat(reportedWithCoords[0].latitude);
        centerLng = parseFloat(reportedWithCoords[0].longitude);
    }

    map = L.map('leaflet-dashboard-map').setView([centerLat, centerLng], 8);

    L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>',
        subdomains: 'abcd',
        maxZoom: 20
    }).addTo(map);

    markersGroup = L.layerGroup().addTo(map);
    updateMapMarkers(L);
};

const updateMapMarkers = (L) => {
    if (!map || !markersGroup || !L) return;

    markersGroup.clearLayers();

    const reportedWithCoords = props.mapIncidents.filter(i => i.latitude && i.longitude);
    
    reportedWithCoords.forEach((incident) => {
        let markerColor = '#10B981'; // Green for positive/low
        if (incident.severity === 'medium') markerColor = '#F59E0B'; // Orange
        if (incident.severity === 'high') markerColor = '#EF4444'; // Red

        const customIcon = L.divIcon({
            html: `<div style="background-color: ${markerColor}; width: 12px; height: 12px; border-radius: 50%; border: 2px solid white; box-shadow: 0 0 6px rgba(0,0,0,0.5);"></div>`,
            className: 'custom-leaflet-icon',
            iconSize: [12, 12],
            iconAnchor: [6, 6]
        });

        const popupContent = `
            <div style="font-family: sans-serif; color: #1e293b; padding: 2px; min-width: 150px;">
                <b style="font-size: 11px; display: block; margin-bottom: 2px;">${getCategoryLabel(incident.category)}</b>
                <span style="font-size: 9px; color: #64748b; display: block; margin-bottom: 4px;">Oleh: ${incident.user?.name || '-'}</span>
                <p style="font-size: 10px; margin: 0; color: #334155;" class="line-clamp-2">${incident.description}</p>
            </div>
        `;

        const marker = L.marker([parseFloat(incident.latitude), parseFloat(incident.longitude)], { icon: customIcon })
            .bindPopup(popupContent);
        
        markersGroup.addLayer(marker);
    });

    if (reportedWithCoords.length > 0) {
        const bounds = L.latLngBounds(reportedWithCoords.map(i => [parseFloat(i.latitude), parseFloat(i.longitude)]));
        map.fitBounds(bounds, { padding: [20, 20] });
        map.zoomOut(4); // Zoom out 4 levels as requested by issue 161
    }
};

onMounted(() => {
    loadLeaflet().then((L) => {
        initializeMap(L);
    });
});

const getCategoryLabel = (category) => {
    switch (category) {
        case 'unsafe_condition': return 'Kondisi Tidak Aman';
        case 'unsafe_act': return 'Tindakan Tidak Aman';
        case 'near_miss': return 'Hampir Celaka';
        case 'positive_observation': return 'Observasi Positif';
        default: return category;
    }
};

const getCategoryBadgeClass = (category) => {
    switch (category) {
        case 'unsafe_condition': return 'bg-amber-500/10 text-amber-400 border border-amber-500/20';
        case 'unsafe_act': return 'bg-orange-500/10 text-orange-400 border border-orange-500/20';
        case 'near_miss': return 'bg-rose-500/10 text-rose-400 border border-rose-500/20';
        case 'positive_observation': return 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20';
        default: return 'bg-slate-800 text-slate-400';
    }
};

const getSeverityBadgeClass = (severity) => {
    switch (severity) {
        case 'low': return 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20';
        case 'medium': return 'bg-amber-500/10 text-amber-400 border border-amber-500/20';
        case 'high': return 'bg-rose-500/10 text-rose-400 border border-rose-500/20';
        default: return 'bg-slate-800 text-slate-400';
    }
};

const formatDate = (dateStr) => {
    if (!dateStr) return '-';
    const dateObj = new Date(dateStr);
    return dateObj.toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'short',
        hour: '2-digit',
        minute: '2-digit'
    });
};
</script>

<template>
    <Head title="Dashboard Pelaporan Insiden K3 - Admin" />

    <AdminDashboardLayout>
        <template #header>
            Dashboard Pelaporan Insiden K3
        </template>

        <div class="space-y-6">
            <!-- Bento-Grid: Row 1 (KPI Cards) -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Monthly Incidents -->
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-xl flex items-center gap-4 transition-all duration-300 hover:-translate-y-1 hover:border-blue-500/30">
                    <div class="h-12 w-12 rounded-xl bg-blue-500/10 flex items-center justify-center text-blue-400 border border-blue-500/20">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                        </svg>
                    </div>
                    <div>
                        <span class="block text-xs font-bold text-slate-400 uppercase tracking-wider">Laporan Bulan Ini</span>
                        <span class="text-2xl font-black text-slate-100 mt-0.5 block">{{ stats.totalMonthly }}</span>
                    </div>
                </div>

                <!-- Priority Open (High Severity) -->
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-xl flex items-center gap-4 transition-all duration-300 hover:-translate-y-1 hover:border-rose-500/30">
                    <div class="h-12 w-12 rounded-xl bg-rose-500/10 flex items-center justify-center text-rose-400 border border-rose-500/20">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6 animate-pulse">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                        </svg>
                    </div>
                    <div>
                        <span class="block text-xs font-bold text-slate-400 uppercase tracking-wider">Prioritas Terbuka</span>
                        <span class="text-2xl font-black text-rose-400 mt-0.5 block">{{ stats.priorityCount }}</span>
                    </div>
                </div>

                <!-- Resolution Rate -->
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-xl flex items-center gap-4 transition-all duration-300 hover:-translate-y-1 hover:border-emerald-500/30">
                    <div class="h-12 w-12 rounded-xl bg-emerald-500/10 flex items-center justify-center text-emerald-400 border border-emerald-500/20">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 10 21a3.745 3.745 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 14 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12z" />
                        </svg>
                    </div>
                    <div>
                        <span class="block text-xs font-bold text-slate-400 uppercase tracking-wider">Tingkat Penyelesaian</span>
                        <span class="text-2xl font-black text-emerald-400 mt-0.5 block">{{ stats.resolutionRate }}%</span>
                    </div>
                </div>

                <!-- Positive Observations -->
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-xl flex items-center gap-4 transition-all duration-300 hover:-translate-y-1 hover:border-amber-500/30">
                    <div class="h-12 w-12 rounded-xl bg-amber-500/10 flex items-center justify-center text-amber-400 border border-amber-500/20">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.182 15.182a4.5 4.5 0 0 1-6.364 0M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0ZM9.75 9.75c0 .414-.168.75-.375.75S9 10.164 9 9.75 9.168 9 9.375 9s.375.336.375.75Zm-.375 0h.008v.015h-.008V9.75Zm5.625 0c0 .414-.168.75-.375.75s-.375-.336-.375-.75.168-.75.375-.75.375.336.375.75Zm-.375 0h.008v.015h-.008V9.75Z" />
                        </svg>
                    </div>
                    <div>
                        <span class="block text-xs font-bold text-slate-400 uppercase tracking-wider">Observasi Positif</span>
                        <span class="text-2xl font-black text-amber-400 mt-0.5 block">{{ stats.positiveObservations }}</span>
                    </div>
                </div>
            </div>

            <!-- Bento-Grid: Row 2 (Charts Side-by-Side) -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Trend Chart -->
                <div class="lg:col-span-2 bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl flex flex-col justify-between">
                    <div>
                        <h3 class="font-bold text-slate-200 text-sm">Tren Laporan Insiden</h3>
                        <p class="text-xs text-slate-500 mb-4">Grafik pertumbuhan jumlah laporan insiden selama 6 bulan terakhir.</p>
                    </div>
                    <div class="flex-1 min-h-[300px]">
                        <apexchart
                            type="area"
                            height="300"
                            width="100%"
                            :options="trendOptions"
                            :series="trendSeries"
                        />
                    </div>
                </div>

                <!-- Composition Chart -->
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl flex flex-col justify-between">
                    <div>
                        <h3 class="font-bold text-slate-200 text-sm">Komposisi Kategori</h3>
                        <p class="text-xs text-slate-500 mb-4">Distribusi laporan berdasarkan 4 klasifikasi utama K3.</p>
                    </div>
                    <div class="flex-1 flex items-center justify-center min-h-[300px]">
                        <apexchart
                            type="donut"
                            height="300"
                            width="100%"
                            :options="compositionOptions"
                            :series="compositionSeries"
                        />
                    </div>
                </div>
            </div>

            <!-- Bento-Grid: Row 3 (Map & Leaderboard) -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Map Container -->
                <div class="lg:col-span-2 bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-xl flex flex-col">
                    <div class="p-4 border-b border-slate-800 bg-slate-900 flex justify-between items-center">
                        <div>
                            <h3 class="font-bold text-slate-200 text-sm">Peta Sebaran Insiden</h3>
                            <p class="text-xs text-slate-500">Visualisasi sebaran spasial lokasi bahaya di pelabuhan.</p>
                        </div>
                        <span class="text-xs font-bold text-amber-500 bg-amber-500/10 px-2.5 py-1 rounded-lg border border-amber-500/20">
                            {{ mapIncidents.length }} Titik GPS
                        </span>
                    </div>
                    <!-- Leaflet map container -->
                    <div id="leaflet-dashboard-map" class="h-[350px] w-full bg-slate-950 z-10"></div>
                </div>

                <!-- Leaderboard Card -->
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl flex flex-col justify-between">
                    <div>
                        <h3 class="font-bold text-slate-200 text-sm">Leaderboard Pelapor Aktif</h3>
                        <p class="text-xs text-slate-500 mb-4">Peringkat 5 petugas teraktif yang berkontribusi dalam budaya keselamatan.</p>
                    </div>
                    <div class="flex-1 space-y-4">
                        <div v-for="(reporter, idx) in topReporters" :key="reporter.id" class="flex items-center justify-between border-b border-slate-800/50 pb-3 last:border-0 last:pb-0">
                            <div class="flex items-center gap-3">
                                <div class="h-8 w-8 rounded-full flex items-center justify-center font-bold text-xs" :class="[
                                    idx === 0 ? 'bg-amber-500 text-slate-950 shadow-lg shadow-amber-500/20' : 
                                    idx === 1 ? 'bg-slate-300 text-slate-900' :
                                    idx === 2 ? 'bg-amber-700 text-slate-100' : 'bg-slate-800 text-slate-400'
                                ]">
                                    {{ idx + 1 }}
                                </div>
                                <div>
                                    <span class="font-bold text-slate-200 text-sm block">{{ reporter.name }}</span>
                                    <span class="text-[10px] text-slate-500">NIP: {{ reporter.nip || '-' }}</span>
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="text-sm font-black text-amber-500">{{ reporter.incidents_count }}</span>
                                <span class="text-[10px] text-slate-500 block">Laporan</span>
                            </div>
                        </div>
                        <div v-if="topReporters.length === 0" class="text-center py-10 text-slate-500 text-sm">
                            Belum ada laporan dari petugas.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bento-Grid: Row 4 (Recent Critical Alerts) -->
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl space-y-4">
                <div class="flex items-center justify-between border-b border-slate-800 pb-4">
                    <div>
                        <h3 class="font-bold text-slate-200 text-sm">Peringatan Insiden Kritis (Open / High Severity / Near-Miss)</h3>
                        <p class="text-xs text-slate-500">Daftar laporan kritis terbaru yang membutuhkan investigasi dan tindak lanjut (CAPA).</p>
                    </div>
                    <Link :href="route('admin.incidents.index')" class="text-xs text-blue-400 font-bold hover:underline">
                        Lihat Semua Laporan &rarr;
                    </Link>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-800 text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                                <th class="py-3 px-4">Tanggal</th>
                                <th class="py-3 px-4">Pelapor</th>
                                <th class="py-3 px-4">Kategori</th>
                                <th class="py-3 px-4">Severity</th>
                                <th class="py-3 px-4">Deskripsi</th>
                                <th class="py-3 px-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/40 text-xs">
                            <tr v-for="incident in criticalIncidents" :key="incident.id" class="hover:bg-slate-800/20 transition-colors">
                                <td class="py-3 px-4 text-slate-400 whitespace-nowrap">{{ formatDate(incident.created_at) }}</td>
                                <td class="py-3 px-4">
                                    <span class="font-bold text-slate-200 block">{{ incident.user?.name || '-' }}</span>
                                    <span class="text-[10px] text-slate-500">NIP: {{ incident.user?.nip || '-' }}</span>
                                </td>
                                <td class="py-3 px-4 whitespace-nowrap">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold" :class="getCategoryBadgeClass(incident.category)">
                                        {{ getCategoryLabel(incident.category) }}
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase" :class="getSeverityBadgeClass(incident.severity)">
                                        {{ incident.severity }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 max-w-xs truncate text-slate-300">{{ incident.description }}</td>
                                <td class="py-3 px-4 text-right">
                                    <Link :href="route('admin.incidents.index', { status: 'open' })" class="inline-flex items-center justify-center bg-amber-500 hover:bg-amber-600 text-slate-950 text-[10px] font-bold px-3 py-1.5 rounded-lg transition-colors">
                                        Tindak Lanjut
                                    </Link>
                                </td>
                            </tr>
                            <tr v-if="criticalIncidents.length === 0">
                                <td colspan="6" class="text-center py-8 text-slate-500">
                                    Tidak ada laporan kritis terbuka saat ini.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AdminDashboardLayout>
</template>
