<script setup>
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AdminDashboardLayout from '@/Layouts/AdminDashboardLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import IncidentDetailModal from '@/Components/IncidentDetailModal.vue';
import { ref, onMounted, watch } from 'vue';
import axios from 'axios';

const props = defineProps({
    incidents: Object,
    summaryStats: Object,
    mapIncidents: Array,
    filters: Object,
});

// Dynamic filter states
const filterStatus = ref(props.filters.status || '');
const filterCategory = ref(props.filters.category || '');
const filterSeverity = ref(props.filters.severity || '');

// Modal state
const isModalOpen = ref(false);
const selectedIncident = ref(null);
const mapContainer = ref(null);
let map = null;
let markersGroup = null;

// Export state
const isExporting = ref(false);
const exportProgress = ref(0);

const openModal = (incident) => {
    selectedIncident.value = incident;
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    selectedIncident.value = null;
};

const handleIncidentUpdated = () => {
    closeModal();
    updateMapMarkers();
};

const resetFilters = () => {
    filterStatus.value = '';
    filterCategory.value = '';
    filterSeverity.value = '';
    applyFilters();
};

const applyFilters = () => {
    router.get(route('admin.incidents.index'), {
        status: filterStatus.value,
        category: filterCategory.value,
        severity: filterSeverity.value,
    }, {
        preserveState: true,
        replace: true,
    });
};

const exportExcel = () => {
    isExporting.value = true;
    exportProgress.value = 0;

    const interval = setInterval(() => {
        if (exportProgress.value < 90) {
            exportProgress.value += Math.floor(Math.random() * 10) + 5;
            if (exportProgress.value > 90) exportProgress.value = 90;
        }
    }, 200);

    axios.get(route('admin.incidents.export'), {
        params: {
            status: filterStatus.value,
            category: filterCategory.value,
            severity: filterSeverity.value
        },
        responseType: 'blob'
    }).then(response => {
        exportProgress.value = 100;
        clearInterval(interval);
        
        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', 'laporan-insiden.xlsx');
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

// Watch for local filter changes to automatically trigger filter
watch([filterStatus, filterCategory, filterSeverity], () => {
    applyFilters();
});

const formatDate = (dateStr) => {
    const dateObj = new Date(dateStr);
    return dateObj.toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const getCategoryLabel = (category) => {
    switch (category) {
        case 'unsafe_condition':
            return 'Kondisi Tidak Aman';
        case 'unsafe_act':
            return 'Tindakan Tidak Aman';
        case 'near_miss':
            return 'Hampir Celaka';
        case 'positive_observation':
            return 'Observasi Positif';
        default:
            return category;
    }
};

const getCategoryBadgeClass = (category) => {
    switch (category) {
        case 'unsafe_condition':
            return 'bg-amber-500/10 text-amber-400 border border-amber-500/20';
        case 'unsafe_act':
            return 'bg-orange-500/10 text-orange-400 border border-orange-500/20';
        case 'near_miss':
            return 'bg-rose-500/10 text-rose-400 border border-rose-500/20';
        case 'positive_observation':
            return 'bg-emerald-500/10 text-emerald-405 text-emerald-400 border border-emerald-500/20';
        default:
            return 'bg-slate-800 text-slate-400';
    }
};

const getSeverityBadgeClass = (severity) => {
    switch (severity) {
        case 'low':
            return 'bg-emerald-500/10 text-emerald-450 text-emerald-400 border border-emerald-500/20';
        case 'medium':
            return 'bg-amber-500/10 text-amber-400 border border-amber-500/20';
        case 'high':
            return 'bg-rose-500/10 text-rose-400 border border-rose-500/20';
        default:
            return 'bg-slate-800 text-slate-400';
    }
};

const getStatusBadgeClass = (status) => {
    switch (status) {
        case 'open':
            return 'bg-blue-500/10 text-blue-400 border border-blue-500/20';
        case 'investigating':
            return 'bg-yellow-500/10 text-yellow-400 border border-yellow-500/20';
        case 'closed':
            return 'bg-emerald-500/10 text-emerald-450 text-emerald-400 border border-emerald-500/20';
        default:
            return 'bg-slate-800 text-slate-400';
    }
};

// Dynamic CDNs for Leaflet
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

    // Center to the latest reported incident with coordinates if available
    if (props.mapIncidents && props.mapIncidents.length > 0) {
        centerLat = parseFloat(props.mapIncidents[0].latitude);
        centerLng = parseFloat(props.mapIncidents[0].longitude);
    }

    map = L.map('leaflet-map').setView([centerLat, centerLng], 8);

    L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>',
        subdomains: 'abcd',
        maxZoom: 20
    }).addTo(map);

    markersGroup = L.layerGroup().addTo(map);
    updateMapMarkers();
};

const updateMapMarkers = () => {
    if (!map || !markersGroup || !window.L) return;

    // Clear existing markers
    markersGroup.clearLayers();
    const L = window.L;

    const reportedWithCoords = props.mapIncidents || [];
    
    reportedWithCoords.forEach((incident) => {
        // Colored icons based on severity
        let markerColor = '#10B981'; // Green for low
        if (incident.severity === 'medium') markerColor = '#F59E0B'; // Orange
        if (incident.severity === 'high') markerColor = '#EF4444'; // Red

        // Create Custom HTML DivIcon
        const customIcon = L.divIcon({
            html: `<div style="background-color: ${markerColor}; width: 14px; height: 14px; border-radius: 50%; border: 2px solid white; box-shadow: 0 0 8px rgba(0,0,0,0.5);"></div>`,
            className: 'custom-leaflet-icon',
            iconSize: [14, 14],
            iconAnchor: [7, 7]
        });

        const popupContent = `
            <div style="font-family: sans-serif; color: #1e293b; padding: 2px;">
                <b style="font-size: 12px; display: block; margin-bottom: 2px;">${getCategoryLabel(incident.category)}</b>
                <span style="font-size: 10px; color: #64748b; display: block; margin-bottom: 6px;">Oleh: ${incident.user?.name}</span>
                <span style="font-size: 11px; display: block; max-width: 200px; margin-bottom: 8px;" class="line-clamp-2">${incident.description}</span>
                <button onclick="window.openIncidentModal(${incident.id})" style="background: #f59e0b; color: #0f172a; border: none; font-weight: bold; font-size: 9px; padding: 4px 8px; border-radius: 4px; cursor: pointer; text-transform: uppercase;">Kelola Laporan</button>
            </div>
        `;

        const marker = L.marker([parseFloat(incident.latitude), parseFloat(incident.longitude)], { icon: customIcon })
            .bindPopup(popupContent);
        
        markersGroup.addLayer(marker);
    });

    // Fit map bounds to show all markers if any
    if (reportedWithCoords.length > 0) {
        const bounds = L.latLngBounds(reportedWithCoords.map(i => [parseFloat(i.latitude), parseFloat(i.longitude)]));
        map.fitBounds(bounds, { padding: [30, 30] });
        
        // Memaksa zoom out 4 tingkat setelah bounds terbentuk agar titik-titik terlihat jauh/luas
        map.zoomOut(4);
    }
};

// Global helper so popup button can trigger modal
onMounted(() => {
    window.openIncidentModal = (id) => {
        const incident = props.mapIncidents.find(i => i.id === id);
        if (incident) openModal(incident);
    };

    loadLeaflet().then((L) => {
        initializeMap(L);
    });
});

// Watch for changes in mapIncidents props to update markers dynamically
watch(() => props.mapIncidents, () => {
    updateMapMarkers();
}, { deep: true });
</script>

<template>
    <Head title="Manajemen Laporan K3 - Admin" />

    <AdminDashboardLayout>
        <template #header>
            Manajemen Laporan Insiden K3
        </template>

        <div class="space-y-6">
            
            <!-- Stats & Map Section Side-by-Side (Saving Vertical Space) -->
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                <!-- Left: 4 Summary Status Cards (Stacked Vertically) -->
                <div class="flex flex-col gap-4 lg:col-span-1 justify-between">
                    <!-- Total Laporan -->
                    <div @click="filterStatus = ''" class="bg-slate-900 border border-slate-800 rounded-2xl p-4 shadow-xl flex items-center gap-4 cursor-pointer hover:-translate-y-1 hover:shadow-2xl hover:border-slate-500 transition-all duration-300 flex-1">
                        <div class="h-10 w-10 rounded-xl bg-blue-500/10 flex items-center justify-center text-blue-400 border border-blue-500/20">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.03 0 1.9.693 2.166 1.638m-7.377 0A48.536 48.536 0 0 1 12 3" />
                            </svg>
                        </div>
                        <div>
                            <span class="block text-[10px] font-bold text-slate-450 uppercase tracking-wider">Total Laporan</span>
                            <span class="text-xl font-black text-slate-100">{{ summaryStats?.total || 0 }}</span>
                        </div>
                    </div>

                    <!-- Laporan Terbuka -->
                    <div @click="filterStatus = 'open'" class="bg-slate-900 border border-slate-800 rounded-2xl p-4 shadow-xl flex items-center gap-4 cursor-pointer hover:-translate-y-1 hover:shadow-2xl hover:border-blue-500 transition-all duration-300 flex-1">
                        <div class="h-10 w-10 rounded-xl bg-blue-500/10 flex items-center justify-center text-blue-400 border border-blue-500/20">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 animate-pulse">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                            </svg>
                        </div>
                        <div>
                            <span class="block text-[10px] font-bold text-slate-455 uppercase tracking-wider text-blue-400">Laporan Terbuka</span>
                            <span class="text-xl font-black text-blue-400">{{ summaryStats?.open || 0 }}</span>
                        </div>
                    </div>

                    <!-- Dalam Investigasi -->
                    <div @click="filterStatus = 'investigating'" class="bg-slate-900 border border-slate-800 rounded-2xl p-4 shadow-xl flex items-center gap-4 cursor-pointer hover:-translate-y-1 hover:shadow-2xl hover:border-amber-500 transition-all duration-300 flex-1">
                        <div class="h-10 w-10 rounded-xl bg-amber-500/10 flex items-center justify-center text-amber-400 border border-amber-500/20">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.637 10.637z" />
                            </svg>
                        </div>
                        <div>
                            <span class="block text-[10px] font-bold text-slate-455 uppercase tracking-wider text-amber-400">Dalam Investigasi</span>
                            <span class="text-xl font-black text-amber-400">{{ summaryStats?.investigating || 0 }}</span>
                        </div>
                    </div>

                    <!-- Selesai (Closed) -->
                    <div @click="filterStatus = 'closed'" class="bg-slate-900 border border-slate-800 rounded-2xl p-4 shadow-xl flex items-center gap-4 cursor-pointer hover:-translate-y-1 hover:shadow-2xl hover:border-emerald-500 transition-all duration-300 flex-1">
                        <div class="h-10 w-10 rounded-xl bg-emerald-500/10 flex items-center justify-center text-emerald-450 border border-emerald-500/20">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0110 21a3.745 3.745 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0114 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z" />
                            </svg>
                        </div>
                        <div>
                            <span class="block text-[10px] font-bold text-slate-455 uppercase tracking-wider text-emerald-400">Selesai (Closed)</span>
                            <span class="text-xl font-black text-emerald-400">{{ summaryStats?.closed || 0 }}</span>
                        </div>
                    </div>
                </div>

                <!-- Right: Leaflet Map Container -->
                <div class="lg:col-span-3 bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-lg flex flex-col justify-between">
                    <div class="p-3 border-b border-slate-800/60 bg-slate-800/30 flex justify-between items-center">
                        <h3 class="text-xs font-black text-slate-200 uppercase tracking-widest">Peta Sebaran Insiden (Map View)</h3>
                        <span class="text-[10px] text-slate-450 font-bold">Menampilkan {{ mapIncidents?.length || 0 }} Laporan Ber-GPS</span>
                    </div>
                    <!-- Leaflet container -->
                    <div id="leaflet-map" class="h-96 w-full bg-slate-950 z-10 flex-1"></div>
                </div>
            </div>

            <!-- Filtering Area & Data Table -->
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl space-y-4">
                
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between border-b border-slate-800 pb-4 gap-4">
                    <div>
                        <h3 class="font-bold text-lg text-slate-200">Riwayat Laporan Insiden K3</h3>
                        <p class="text-xs text-slate-400">Daftar lengkap berkas laporan insiden dan tindakan yang telah diambil.</p>
                    </div>

                    <!-- Filters inputs aligned next to the title -->
                    <div class="flex flex-wrap gap-3 items-center">
                        <select 
                            v-model="filterStatus"
                            class="bg-slate-950 border border-slate-800 focus:border-amber-500 rounded-xl text-xs text-slate-300 px-3 py-2"
                        >
                            <option value="">Semua Status</option>
                            <option value="open">Open</option>
                            <option value="investigating">Investigating</option>
                            <option value="closed">Closed / Resolved</option>
                        </select>

                        <select 
                            v-model="filterCategory"
                            class="bg-slate-950 border border-slate-800 focus:border-amber-500 rounded-xl text-xs text-slate-300 px-3 py-2"
                        >
                            <option value="">Semua Kategori</option>
                            <option value="unsafe_condition">Kondisi Tidak Aman</option>
                            <option value="unsafe_act">Tindakan Tidak Aman</option>
                            <option value="near_miss">Hampir Celaka</option>
                            <option value="positive_observation">Observasi Positif</option>
                        </select>

                        <select 
                            v-model="filterSeverity"
                            class="bg-slate-950 border border-slate-800 focus:border-amber-500 rounded-xl text-xs text-slate-300 px-3 py-2"
                        >
                            <option value="">Semua Severity</option>
                            <option value="low">Low</option>
                            <option value="medium">Medium</option>
                            <option value="high">High</option>
                        </select>

                        <button 
                            @click="resetFilters"
                            class="text-xs font-bold text-slate-450 hover:text-slate-200 border border-slate-800 hover:bg-slate-800 px-3.5 py-2 rounded-xl transition-all"
                        >
                            Reset Filter
                        </button>

                        <button 
                            @click="exportExcel"
                            class="text-xs font-bold text-emerald-400 hover:text-emerald-300 border border-emerald-500/30 hover:bg-emerald-500/10 px-3.5 py-2 rounded-xl transition-all flex items-center gap-1.5"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3.5 h-3.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                            </svg>
                            Export Excel
                        </button>
                    </div>
                </div>
                
                <!-- Table Content -->
                <div class="overflow-x-auto rounded-lg border border-slate-800 relative">
                    <table class="min-w-full text-slate-300">
                        <thead>
                            <tr class="bg-slate-950 border-b border-slate-800 text-left text-xs font-bold uppercase tracking-wider text-slate-400">
                                <th class="py-3 px-4">Tanggal</th>
                                <th class="py-3 px-4">Pelapor</th>
                                <th class="py-3 px-4">Kategori</th>
                                <th class="py-3 px-4">Severity</th>
                                <th class="py-3 px-4">Deskripsi Temuan</th>
                                <th class="py-3 px-4">Status</th>
                                <th class="py-3 px-4 text-center w-20">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800 text-sm">
                            <tr v-for="incident in incidents.data" :key="incident.id" class="hover:bg-slate-850/50 transition-colors duration-150">
                                <td class="py-3 px-4 whitespace-nowrap text-slate-400">{{ formatDate(incident.created_at) }}</td>
                                <td class="py-3 px-4 font-bold text-slate-100">{{ incident.user?.name }}</td>
                                <td class="py-3 px-4">
                                    <span :class="getCategoryBadgeClass(incident.category)" class="px-2.5 py-0.5 rounded-lg text-[10px] font-bold">
                                        {{ getCategoryLabel(incident.category) }}
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    <span :class="getSeverityBadgeClass(incident.severity)" class="px-2.5 py-0.5 rounded-lg text-[10px] font-bold uppercase">
                                        {{ incident.severity }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 max-w-xs truncate text-slate-350" :title="incident.description">{{ incident.description }}</td>
                                <td class="py-3 px-4">
                                    <span :class="getStatusBadgeClass(incident.status)" class="px-2.5 py-0.5 rounded-full text-[9px] font-extrabold uppercase">
                                        {{ incident.status }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-center whitespace-nowrap">
                                    <button 
                                        @click="openModal(incident)"
                                        title="Kelola Laporan"
                                        class="p-2 bg-amber-500 hover:bg-amber-600 text-slate-950 rounded-lg transition-all duration-200 active:scale-95 inline-flex items-center justify-center shadow-lg shadow-amber-500/20"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="incidents.data.length === 0">
                                <td colspan="7" class="p-8 text-center text-slate-500 font-bold uppercase">
                                    Tidak ada data laporan ditemukan.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Table Pagination -->
                <div class="p-4 border-t border-slate-800/60 flex justify-center" v-if="incidents.data.length > 0">
                    <Pagination :links="incidents.links" />
                </div>
            </div>
        </div>

        <IncidentDetailModal 
            :is-open="isModalOpen"
            :incident="selectedIncident"
            @close="closeModal"
            @updated="handleIncidentUpdated"
        />

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

<style scoped>
@keyframes fadeIn {
    from { opacity: 0; transform: scale(0.98); }
    to { opacity: 1; transform: scale(1); }
}
.animate-fadeIn {
    animation: fadeIn 0.2s ease-out forwards;
}
</style>
