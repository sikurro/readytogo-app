<script setup>
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import MobileAppLayout from '@/Layouts/MobileAppLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import { ref, computed } from 'vue';

const page = usePage();
const incidentStatusNotifications = computed(() => {
    return page.props.auth.notifications?.filter(n => n.type === 'App\\Notifications\\IncidentStatusUpdated') || [];
});

const markNotificationsAsRead = () => {
    router.post(route('notifications.mark-as-read'), {}, {
        preserveScroll: true
    });
};

defineProps({
    incidents: Object,
});

const activeIncidentDetails = ref(null);

const toggleDetails = (id) => {
    if (activeIncidentDetails.value === id) {
        activeIncidentDetails.value = null;
    } else {
        activeIncidentDetails.value = id;
    }
};

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
            return 'Hampir Celaka (Near-Miss)';
        case 'positive_observation':
            return 'Observasi Positif';
        default:
            return category;
    }
};

const getCategoryClass = (category) => {
    switch (category) {
        case 'unsafe_condition':
            return 'bg-amber-500/10 text-amber-400 border border-amber-500/20';
        case 'unsafe_act':
            return 'bg-orange-500/10 text-orange-400 border border-orange-500/20';
        case 'near_miss':
            return 'bg-rose-500/10 text-rose-455 text-rose-400 border border-rose-500/20';
        case 'positive_observation':
            return 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20';
        default:
            return 'bg-slate-800 text-slate-400';
    }
};

const getSeverityLabel = (severity) => {
    switch (severity) {
        case 'low':
            return 'Low';
        case 'medium':
            return 'Medium';
        case 'high':
            return 'High';
        default:
            return severity;
    }
};

const getSeverityClass = (severity) => {
    switch (severity) {
        case 'low':
            return 'bg-emerald-500 text-slate-950';
        case 'medium':
            return 'bg-amber-500 text-slate-950';
        case 'high':
            return 'bg-rose-600 text-slate-100';
        default:
            return 'bg-slate-700 text-slate-100';
    }
};

const getStatusLabel = (status) => {
    switch (status) {
        case 'open':
            return 'Open';
        case 'investigating':
            return 'Investigating';
        case 'closed':
            return 'Closed / Resolved';
        default:
            return status;
    }
};

const getStatusClass = (status) => {
    switch (status) {
        case 'open':
            return 'border border-blue-500/30 bg-blue-500/10 text-blue-400';
        case 'investigating':
            return 'border border-yellow-500/30 bg-yellow-500/10 text-yellow-400';
        case 'closed':
            return 'border border-emerald-500/30 bg-emerald-500/10 text-emerald-450 text-emerald-400';
        default:
            return 'border border-slate-700 bg-slate-800 text-slate-400';
    }
};
</script>

<template>
    <Head title="Laporan Insiden K3" />

    <MobileAppLayout>
        <div class="py-4 space-y-6">
            <!-- Notification Banners -->
            <div v-if="incidentStatusNotifications.length > 0" class="space-y-3">
                <div v-for="notif in incidentStatusNotifications" :key="notif.id" class="bg-indigo-500/10 border border-indigo-500/20 rounded-xl p-4 flex justify-between items-start gap-3 animate-fadeIn">
                    <div class="space-y-1">
                        <span class="text-[9px] font-black text-indigo-400 uppercase tracking-widest block">{{ notif.data.title }}</span>
                        <p class="text-xs text-slate-200 leading-relaxed font-bold">{{ notif.data.message }}</p>
                        <p class="text-[10px] text-slate-400 leading-relaxed italic" v-if="notif.data.admin_feedback">Catatan Admin: "{{ notif.data.admin_feedback }}"</p>
                    </div>
                    <button @click="markNotificationsAsRead" class="text-slate-400 hover:text-indigo-400 font-black text-xs shrink-0">✕</button>
                </div>
            </div>

            <!-- Header Section -->
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl font-black text-slate-100 flex items-center gap-2">
                        <span class="w-1.5 h-8 bg-gradient-to-b from-amber-500 to-orange-500 rounded-full"></span>
                        Laporan Insiden
                    </h2>
                    <p class="text-[11px] text-slate-400 font-semibold mt-0.5">Daftar temuan K3 yang Anda laporkan</p>
                </div>
                
                <Link 
                    :href="route('incidents.create')" 
                    class="flex items-center gap-1.5 text-xs text-slate-950 hover:text-slate-900 font-extrabold bg-gradient-to-r from-amber-500 to-orange-500 px-4 py-2.5 rounded-xl transition-all duration-200 active:scale-95 shadow-lg shadow-amber-500/10 uppercase tracking-wider shrink-0"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="w-3.5 h-3.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Lapor baru
                </Link>
            </div>

            <!-- Toast / Flash Message -->
            <div v-if="$page.props.flash?.success" class="bg-emerald-500/10 border border-emerald-500/20 rounded-xl p-4 text-xs font-bold text-emerald-400 flex items-center gap-2 animate-fadeIn">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4 shrink-0">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0110 21a3.745 3.745 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0114 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z" />
                </svg>
                <span>{{ $page.props.flash.success }}</span>
            </div>

            <!-- Incidents List -->
            <div class="space-y-4">
                <div v-for="incident in incidents.data" :key="incident.id" 
                    class="bg-slate-900/60 backdrop-blur border border-slate-800 rounded-2xl p-5 shadow-lg space-y-4">
                    
                    <!-- Card Header -->
                    <div class="flex items-start justify-between gap-3">
                        <div class="space-y-1.5">
                            <span class="block text-[10px] text-slate-500 font-bold tracking-wide uppercase">{{ formatDate(incident.created_at) }}</span>
                            <span :class="getCategoryClass(incident.category)" class="inline-block px-2.5 py-0.5 rounded-lg text-[10px] font-bold">
                                {{ getCategoryLabel(incident.category) }}
                            </span>
                        </div>
                        <div class="flex flex-col items-end gap-2 shrink-0">
                            <span :class="getStatusClass(incident.status)" class="px-2.5 py-0.5 rounded-full text-[9px] font-extrabold tracking-wider uppercase leading-none">
                                {{ getStatusLabel(incident.status) }}
                            </span>
                            <span :class="getSeverityClass(incident.severity)" class="px-2 py-0.5 rounded-md text-[9px] font-black tracking-wider uppercase leading-none">
                                {{ getSeverityLabel(incident.severity) }}
                            </span>
                        </div>
                    </div>

                    <!-- Description Snippet -->
                    <div>
                        <p class="text-xs text-slate-350 leading-relaxed line-clamp-2">
                            {{ incident.description }}
                        </p>
                    </div>

                    <!-- Action Bar -->
                    <div class="flex items-center justify-between border-t border-slate-800/60 pt-3 text-[10px] font-bold">
                        <span class="text-slate-500">
                            <template v-if="incident.latitude && incident.longitude">
                                <a :href="`https://www.google.com/maps/search/?api=1&query=${incident.latitude},${incident.longitude}`" target="_blank" class="text-slate-400 hover:text-amber-500 inline-flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3.5 h-3.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                                    </svg>
                                    Lihat Peta
                                </a>
                            </template>
                            <template v-else>
                                Lokasi tidak tersemat
                            </template>
                        </span>

                        <button @click="toggleDetails(incident.id)" class="text-amber-500 hover:text-amber-400 flex items-center gap-0.5">
                            <span>{{ activeIncidentDetails === incident.id ? 'Sembunyikan' : 'Detail Laporan' }}</span>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3 h-3 transition-transform" :class="{ 'rotate-180': activeIncidentDetails === incident.id }">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                            </svg>
                        </button>
                    </div>

                    <!-- Collapsible Details -->
                    <div v-if="activeIncidentDetails === incident.id" class="bg-slate-950/80 rounded-xl p-4 space-y-4 border border-white/5 animate-fadeIn text-xs">
                        <div class="space-y-1">
                            <span class="text-[9px] font-bold text-slate-500 uppercase tracking-widest block">Deskripsi Lengkap</span>
                            <p class="text-slate-300 leading-relaxed whitespace-pre-wrap">{{ incident.description }}</p>
                        </div>

                        <div v-if="incident.image_path" class="space-y-1.5">
                            <span class="text-[9px] font-bold text-slate-500 uppercase tracking-widest block">Foto Terlampir (Watermarked)</span>
                            <div class="relative rounded-lg overflow-hidden border border-slate-800 bg-slate-900 max-h-60 flex items-center justify-center">
                                <img :src="`/storage/${incident.image_path}`" alt="Lampiran Insiden" class="object-contain max-h-60 w-full" />
                            </div>
                        </div>

                        <!-- CAPA Feedback Loop -->
                        <div v-if="incident.status === 'closed'" class="bg-emerald-500/10 border border-emerald-500/20 rounded-xl p-4 space-y-2">
                            <div class="flex items-center gap-1.5 text-[10px] font-black text-emerald-400 uppercase tracking-wide">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0110 21a3.745 3.745 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0114 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z" />
                                </svg>
                                Tindakan Korektif (CAPA) Selesai
                            </div>
                            <p class="text-slate-350 leading-relaxed text-[11px]">
                                {{ incident.admin_feedback || 'Laporan telah diselesaikan dan diverifikasi oleh tim K3.' }}
                            </p>
                        </div>
                    </div>

                </div>

                <!-- Empty State -->
                <div v-if="incidents.data.length === 0" class="text-center py-16 bg-slate-900/30 rounded-2xl border border-slate-800/80">
                    <div class="w-12 h-12 mx-auto bg-slate-800/40 rounded-full flex items-center justify-center mb-3">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-slate-500">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                        </svg>
                    </div>
                    <p class="text-xs font-bold text-slate-500 uppercase tracking-widest">Belum Ada Laporan</p>
                    <p class="text-[11px] text-slate-500 mt-1 max-w-[200px] mx-auto">Anda belum pernah mengirimkan laporan insiden K3.</p>
                </div>
            </div>

            <!-- Pagination -->
            <div class="flex justify-center pt-4" v-if="incidents.data.length > 0">
                <Pagination :links="incidents.links" />
            </div>

        </div>
    </MobileAppLayout>
</template>

<style scoped>
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-4px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-fadeIn {
    animation: fadeIn 0.2s ease-out forwards;
}
</style>
