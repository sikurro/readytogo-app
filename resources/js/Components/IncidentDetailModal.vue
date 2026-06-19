<script setup>
import { useForm } from '@inertiajs/vue3';
import { watch, ref, onMounted, nextTick } from 'vue';

const props = defineProps({
    isOpen: Boolean,
    incident: Object,
});

const emit = defineEmits(['close', 'updated']);

const statusForm = useForm({
    status: '',
    admin_feedback: '',
});

watch(() => props.incident, (newVal) => {
    if (newVal) {
        statusForm.status = newVal.status;
        statusForm.admin_feedback = newVal.admin_feedback || '';
    }
}, { immediate: true });

const submitStatusUpdate = () => {
    statusForm.put(route('admin.incidents.update-status', props.incident.id), {
        onSuccess: () => {
            emit('updated');
        }
    });
};

const closeModal = () => {
    statusForm.reset();
    statusForm.clearErrors();
    emit('close');
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
        case 'positive_observation': return 'bg-emerald-500/10 text-emerald-405 text-emerald-400 border border-emerald-500/20';
        default: return 'bg-slate-800 text-slate-400';
    }
};

const getSeverityBadgeClass = (severity) => {
    switch (severity) {
        case 'low': return 'bg-emerald-500/10 text-emerald-450 text-emerald-400 border border-emerald-500/20';
        case 'medium': return 'bg-amber-500/10 text-amber-400 border border-amber-500/20';
        case 'high': return 'bg-rose-500/10 text-rose-400 border border-rose-500/20';
        default: return 'bg-slate-800 text-slate-400';
    }
};
</script>

<template>
    <div v-if="isOpen && incident" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
        <div @click="closeModal" class="absolute inset-0 bg-slate-950/80 backdrop-blur-sm"></div>

        <div class="relative bg-slate-900 border border-slate-800 rounded-2xl w-full max-w-2xl overflow-hidden shadow-2xl animate-fadeIn">
            <!-- Header -->
            <div class="p-5 border-b border-slate-800 bg-slate-950/40 flex items-center justify-between">
                <div>
                    <h4 class="text-sm font-black text-slate-200 uppercase tracking-widest">Detail & Tindak Lanjut Laporan</h4>
                    <span class="text-[10px] text-slate-500">Laporan ID: #{{ incident.id }} | Dibuat: {{ formatDate(incident.created_at) }}</span>
                </div>
                <button @click="closeModal" class="text-slate-400 hover:text-slate-200 font-black p-1">✕</button>
            </div>

            <!-- Body -->
            <div class="p-6 overflow-y-auto max-h-[75vh] space-y-6 text-xs leading-relaxed">
                <div class="grid grid-cols-2 gap-4 bg-slate-950/40 border border-slate-850 rounded-xl p-4">
                    <div>
                        <span class="text-[9px] font-bold text-slate-500 uppercase tracking-widest block mb-0.5">Nama Pelapor</span>
                        <span class="font-bold text-slate-200 block">{{ incident.user?.name || '-' }}</span>
                        <span v-if="incident.user?.email" class="text-[10px] text-slate-400">{{ incident.user?.email }}</span>
                    </div>
                    <div>
                        <span class="text-[9px] font-bold text-slate-500 uppercase tracking-widest block mb-1">Status Kategori & Severity</span>
                        <div class="flex flex-wrap gap-1.5">
                            <span :class="getCategoryBadgeClass(incident.category)" class="px-2 py-0.5 rounded-md text-[9px] font-bold">
                                {{ getCategoryLabel(incident.category) }}
                            </span>
                            <span :class="getSeverityBadgeClass(incident.severity)" class="px-2 py-0.5 rounded-md text-[9px] font-bold uppercase">
                                {{ incident.severity }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="space-y-1">
                    <span class="text-[9px] font-bold text-slate-500 uppercase tracking-widest block">Deskripsi Kejadian / Bahaya</span>
                    <p class="text-slate-300 whitespace-pre-wrap leading-relaxed">{{ incident.description }}</p>
                </div>

                <div v-if="incident.image_path" class="space-y-1.5">
                    <span class="text-[9px] font-bold text-slate-500 uppercase tracking-widest block">Foto Bukti Lampiran</span>
                    <div class="relative rounded-xl overflow-hidden border border-slate-850 bg-slate-950/80 p-2 max-h-72 flex justify-center">
                        <img :src="`/storage/${incident.image_path}`" alt="Lampiran" class="object-contain max-h-64 rounded-lg" />
                    </div>
                </div>

                <div v-if="incident.latitude && incident.longitude" class="bg-slate-950/40 border border-slate-850 rounded-xl p-4 flex items-center justify-between">
                    <div>
                        <span class="text-[9px] font-bold text-slate-500 uppercase tracking-widest block">Titik Koordinat GPS</span>
                        <span class="text-slate-300 font-mono">{{ incident.latitude }}, {{ incident.longitude }}</span>
                    </div>
                    <a 
                        :href="`https://www.google.com/maps/search/?api=1&query=${incident.latitude},${incident.longitude}`" 
                        target="_blank" 
                        class="bg-slate-800 hover:bg-slate-750 border border-slate-700 text-slate-300 font-bold px-3.5 py-2 rounded-xl transition-all"
                    >
                        Buka di Google Maps
                    </a>
                </div>

                <!-- Form CAPA -->
                <form @submit.prevent="submitStatusUpdate" class="border-t border-slate-800 pt-5 space-y-4">
                    <h4 class="text-xs font-black text-slate-200 uppercase tracking-widest">Penanganan & Tindakan (CAPA)</h4>
                    
                    <div class="space-y-2">
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Perubahan Status Tiket</label>
                        <div class="grid grid-cols-3 gap-3">
                            <button 
                                type="button" 
                                @click="statusForm.status = 'open'"
                                class="py-2.5 rounded-xl border text-center font-bold tracking-wider uppercase transition-all"
                                :class="statusForm.status === 'open' ? 'bg-blue-500/10 border-blue-500 text-blue-400 shadow-md' : 'bg-slate-950 border-slate-850 text-slate-500 hover:bg-slate-850'"
                            >Open</button>
                            <button 
                                type="button" 
                                @click="statusForm.status = 'investigating'"
                                class="py-2.5 rounded-xl border text-center font-bold tracking-wider uppercase transition-all"
                                :class="statusForm.status === 'investigating' ? 'bg-yellow-500/10 border-yellow-500 text-yellow-400 shadow-md' : 'bg-slate-950 border-slate-850 text-slate-500 hover:bg-slate-850'"
                            >Investigate</button>
                            <button 
                                type="button" 
                                @click="statusForm.status = 'closed'"
                                class="py-2.5 rounded-xl border text-center font-bold tracking-wider uppercase transition-all"
                                :class="statusForm.status === 'closed' ? 'bg-emerald-500/10 border-emerald-500 text-emerald-400 shadow-md' : 'bg-slate-950 border-slate-850 text-slate-500 hover:bg-slate-850'"
                            >Close / Resolve</button>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label for="admin_feedback" class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">
                            Tindakan Korektif & Pencegahan
                            <span v-if="statusForm.status === 'closed'" class="text-rose-450 text-rose-400 font-black">* (Wajib diisi)</span>
                        </label>
                        <textarea 
                            id="admin_feedback"
                            v-model="statusForm.admin_feedback"
                            rows="3"
                            placeholder="Jelaskan tindakan yang telah diambil..."
                            class="w-full bg-slate-950 border border-slate-850 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 rounded-xl p-3 text-slate-200 text-xs"
                        ></textarea>
                        <p v-if="statusForm.errors.admin_feedback" class="text-[10px] text-rose-400 font-semibold">{{ statusForm.errors.admin_feedback }}</p>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-2">
                        <button type="button" @click="closeModal" class="bg-slate-950 border border-slate-800 hover:bg-slate-850 text-slate-350 font-bold px-4 py-2.5 rounded-xl transition-all">Batal</button>
                        <button type="submit" :disabled="statusForm.processing || (statusForm.status === 'closed' && !statusForm.admin_feedback?.trim())" class="bg-amber-500 hover:bg-amber-600 disabled:opacity-40 disabled:pointer-events-none text-slate-950 font-black px-5 py-2.5 rounded-xl uppercase tracking-wider transition-all duration-200">
                            {{ statusForm.processing ? 'Menyimpan...' : 'Simpan Tindakan' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
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
