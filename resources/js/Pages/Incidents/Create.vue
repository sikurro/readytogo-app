<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import MobileAppLayout from '@/Layouts/MobileAppLayout.vue';
import { ref, onMounted } from 'vue';

const locationStatus = ref('pending'); // 'pending', 'fetching', 'success', 'error'
const imagePreview = ref(null);
const isProcessingImage = ref(false);

const form = useForm({
    category: '',
    severity: '',
    description: '',
    latitude: null,
    longitude: null,
    image: null,
});

// Auto-tagging Location on mount
const fetchLocation = () => {
    locationStatus.value = 'fetching';
    if (!navigator.geolocation) {
        locationStatus.value = 'error';
        return;
    }

    navigator.geolocation.getCurrentPosition(
        (position) => {
            form.latitude = position.coords.latitude;
            form.longitude = position.coords.longitude;
            locationStatus.value = 'success';
        },
        (error) => {
            console.error('Error fetching location:', error);
            locationStatus.value = 'error';
        },
        { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
    );
};

onMounted(() => {
    fetchLocation();
});

// Image Input, Compression, and Watermark
const handleImageUpload = (event) => {
    const file = event.target.files[0];
    if (!file) return;

    // Show processing status
    isProcessingImage.value = true;
    
    const reader = new FileReader();
    reader.readAsDataURL(file);
    reader.onload = (e) => {
        const img = new Image();
        img.src = e.target.result;
        img.onload = () => {
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');

            // Define maximum dimensions
            const MAX_WIDTH = 1080;
            const MAX_HEIGHT = 1080;
            let width = img.width;
            let height = img.height;

            if (width > height) {
                if (width > MAX_WIDTH) {
                    height *= MAX_WIDTH / width;
                    width = MAX_WIDTH;
                }
            } else {
                if (height > MAX_HEIGHT) {
                    width *= MAX_HEIGHT / height;
                    height = MAX_HEIGHT;
                }
            }

            canvas.width = width;
            canvas.height = height;

            // Draw image on canvas
            ctx.drawImage(img, 0, 0, width, height);

            // Watermark styling
            const dateText = new Date().toLocaleString('id-ID', {
                timeZoneName: 'short',
                day: 'numeric',
                month: 'short',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });
            const locationText = form.latitude && form.longitude
                ? `GPS: ${form.latitude.toFixed(6)}, ${form.longitude.toFixed(6)}`
                : 'GPS: Tidak Terdeteksi';
            
            const watermarkText = `${dateText} | ${locationText}`;

            // Configure text properties
            const fontSize = Math.max(14, Math.floor(width / 35));
            ctx.font = `bold ${fontSize}px sans-serif`;
            ctx.textAlign = 'right';
            ctx.textBaseline = 'bottom';

            // Draw shadow/outline for readability on any background
            ctx.fillStyle = 'rgba(0, 0, 0, 0.8)';
            ctx.fillText(watermarkText, width - (fontSize / 2) + 2, height - (fontSize / 2) + 2);
            ctx.fillText(watermarkText, width - (fontSize / 2) - 2, height - (fontSize / 2) - 2);
            ctx.fillText(watermarkText, width - (fontSize / 2) + 2, height - (fontSize / 2) - 2);
            ctx.fillText(watermarkText, width - (fontSize / 2) - 2, height - (fontSize / 2) + 2);

            // Draw front text
            ctx.fillStyle = '#ffffff';
            ctx.fillText(watermarkText, width - (fontSize / 2), height - (fontSize / 2));

            // Convert canvas to blob (JPEG format with 0.8 quality compression)
            canvas.toBlob((blob) => {
                // Set form image payload as the compressed and watermarked blob file
                const watermarkedFile = new File([blob], file.name, {
                    type: 'image/jpeg',
                    lastModified: Date.now()
                });
                
                form.image = watermarkedFile;
                imagePreview.value = URL.createObjectURL(watermarkedFile);
                isProcessingImage.value = false;
            }, 'image/jpeg', 0.8);
        };
    };
};

const submit = () => {
    form.post(route('incidents.store'), {
        forceFormData: true,
    });
};
</script>

<template>
    <Head title="Buat Laporan Insiden K3" />

    <MobileAppLayout>
        <div class="py-4 space-y-6">
            <!-- Header Section -->
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-black text-slate-100 flex items-center gap-2">
                    <span class="w-1.5 h-8 bg-gradient-to-b from-amber-500 to-orange-500 rounded-full"></span>
                    Lapor Insiden Baru
                </h2>
                
                <Link 
                    :href="route('incidents.index')" 
                    class="flex items-center gap-1 text-xs text-slate-400 hover:text-slate-200 font-bold bg-slate-900 border border-slate-800 hover:bg-slate-800 px-3 py-2 rounded-xl transition-all duration-200 active:scale-95"
                >
                    Batal
                </Link>
            </div>

            <!-- Main Form -->
            <form @submit.prevent="submit" class="space-y-5">
                
                <!-- Category Select (Custom Card-style Buttons) -->
                <div class="space-y-2">
                    <label class="text-xs font-black text-slate-400 uppercase tracking-widest block">Kategori Laporan</label>
                    <div class="grid grid-cols-2 gap-3">
                        <button 
                            type="button" 
                            @click="form.category = 'unsafe_condition'"
                            class="p-4 rounded-xl text-left border transition-all flex flex-col justify-between h-24"
                            :class="form.category === 'unsafe_condition' 
                                ? 'bg-amber-500/10 border-amber-500 text-amber-400 shadow-lg shadow-amber-500/5' 
                                : 'bg-slate-900 border-slate-800 text-slate-400 hover:bg-slate-800/50'"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <div>
                                <span class="block text-xs font-bold">Kondisi Bahaya</span>
                                <span class="text-[9px] font-medium text-slate-500">Unsafe Condition</span>
                            </div>
                        </button>

                        <button 
                            type="button" 
                            @click="form.category = 'unsafe_act'"
                            class="p-4 rounded-xl text-left border transition-all flex flex-col justify-between h-24"
                            :class="form.category === 'unsafe_act' 
                                ? 'bg-orange-500/10 border-orange-500 text-orange-400 shadow-lg shadow-orange-500/5' 
                                : 'bg-slate-900 border-slate-800 text-slate-400 hover:bg-slate-800/50'"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
                            </svg>
                            <div>
                                <span class="block text-xs font-bold">Tindakan Bahaya</span>
                                <span class="text-[9px] font-medium text-slate-500">Unsafe Act</span>
                            </div>
                        </button>

                        <button 
                            type="button" 
                            @click="form.category = 'near_miss'"
                            class="p-4 rounded-xl text-left border transition-all flex flex-col justify-between h-24"
                            :class="form.category === 'near_miss' 
                                ? 'bg-rose-500/10 border-rose-500 text-rose-455 text-rose-400 shadow-lg shadow-rose-500/5' 
                                : 'bg-slate-900 border-slate-800 text-slate-400 hover:bg-slate-800/50'"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                            </svg>
                            <div>
                                <span class="block text-xs font-bold">Hampir Celaka</span>
                                <span class="text-[9px] font-medium text-slate-500">Near-Miss</span>
                            </div>
                        </button>

                        <button 
                            type="button" 
                            @click="form.category = 'positive_observation'"
                            class="p-4 rounded-xl text-left border transition-all flex flex-col justify-between h-24"
                            :class="form.category === 'positive_observation' 
                                ? 'bg-emerald-500/10 border-emerald-500 text-emerald-450 text-emerald-400 shadow-lg shadow-emerald-500/5' 
                                : 'bg-slate-900 border-slate-800 text-slate-400 hover:bg-slate-800/50'"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0110 21a3.745 3.745 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0114 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z" />
                            </svg>
                            <div>
                                <span class="block text-xs font-bold">Observasi Aman</span>
                                <span class="text-[9px] font-medium text-slate-500">Positive Obs.</span>
                            </div>
                        </button>
                    </div>
                    <p v-if="form.errors.category" class="text-[10px] text-rose-400 font-semibold">{{ form.errors.category }}</p>
                </div>

                <!-- Severity Select (Risk Matrix Button Group) -->
                <div class="space-y-2">
                    <label class="text-xs font-black text-slate-400 uppercase tracking-widest block">Tingkat Keparahan (Severity)</label>
                    <div class="grid grid-cols-3 gap-2">
                        <button 
                            type="button" 
                            @click="form.severity = 'low'"
                            class="py-3.5 px-4 rounded-xl text-xs font-black uppercase tracking-wider border transition-all text-center"
                            :class="form.severity === 'low' 
                                ? 'bg-emerald-500 border-emerald-400 text-slate-950 font-black shadow-lg shadow-emerald-500/10' 
                                : 'bg-slate-900 border-slate-800 text-emerald-400 hover:bg-slate-800/50'"
                        >
                            🟢 Low
                        </button>
                        <button 
                            type="button" 
                            @click="form.severity = 'medium'"
                            class="py-3.5 px-4 rounded-xl text-xs font-black uppercase tracking-wider border transition-all text-center"
                            :class="form.severity === 'medium' 
                                ? 'bg-amber-500 border-amber-400 text-slate-950 font-black shadow-lg shadow-amber-500/10' 
                                : 'bg-slate-900 border-slate-800 text-amber-500 hover:bg-slate-800/50'"
                        >
                            🟠 Medium
                        </button>
                        <button 
                            type="button" 
                            @click="form.severity = 'high'"
                            class="py-3.5 px-4 rounded-xl text-xs font-black uppercase tracking-wider border transition-all text-center"
                            :class="form.severity === 'high' 
                                ? 'bg-rose-600 border-rose-500 text-slate-100 font-black shadow-lg shadow-rose-600/10' 
                                : 'bg-slate-900 border-slate-800 text-rose-500 hover:bg-slate-800/50'"
                        >
                            🔴 High
                        </button>
                    </div>
                    <p v-if="form.errors.severity" class="text-[10px] text-rose-400 font-semibold">{{ form.errors.severity }}</p>
                </div>

                <!-- Description Text Area -->
                <div class="space-y-2">
                    <label for="description" class="text-xs font-black text-slate-400 uppercase tracking-widest block">Deskripsi Temuan</label>
                    <textarea 
                        id="description" 
                        v-model="form.description" 
                        rows="4" 
                        placeholder="Deskripsikan secara detail apa yang terjadi, lokasi spesifik, atau saran tindakan perbaikan. (Anda juga bisa menggunakan tombol mikrofon pada keyboard HP Anda untuk mendikte)." 
                        class="w-full bg-slate-900 border border-slate-800 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 rounded-xl text-xs text-slate-150 p-4 transition-all"
                    ></textarea>
                    <p v-if="form.errors.description" class="text-[10px] text-rose-400 font-semibold">{{ form.errors.description }}</p>
                </div>

                <!-- Location Tagging Status (Hidden coordinates sent via form) -->
                <div class="bg-slate-900/40 border border-slate-850 rounded-xl p-3 flex items-center justify-between text-[10px] font-bold">
                    <span class="text-slate-500 uppercase tracking-wider">Auto-Tagging Lokasi</span>
                    <span class="flex items-center gap-1.5">
                        <span v-if="locationStatus === 'fetching'" class="text-amber-500 animate-pulse flex items-center gap-1">
                            <svg class="animate-spin h-3.5 w-3.5" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            Mendeteksi GPS...
                        </span>
                        <span v-else-if="locationStatus === 'success'" class="text-emerald-400 flex items-center gap-1">
                            📍 Tersemat ({{ form.latitude?.toFixed(5) }}, {{ form.longitude?.toFixed(5) }})
                        </span>
                        <button v-else-if="locationStatus === 'error'" type="button" @click="fetchLocation" class="text-rose-455 text-rose-400 hover:underline">
                            ⚠️ Gagal. Klik untuk coba lagi.
                        </button>
                    </span>
                </div>

                <!-- Upload Image File (Supports camera & gallery) -->
                <div class="space-y-2">
                    <label class="text-xs font-black text-slate-400 uppercase tracking-widest block">Unggah Foto Lampiran</label>
                    <div class="relative">
                        <input 
                            type="file" 
                            id="image" 
                            accept="image/*" 
                            @change="handleImageUpload" 
                            class="hidden" 
                        />
                        <label 
                            for="image" 
                            class="flex flex-col items-center justify-center border-2 border-dashed border-slate-800 rounded-xl p-6 bg-slate-900/40 hover:bg-slate-900 transition-all cursor-pointer text-center"
                            :class="imagePreview ? 'border-amber-500/30' : ''"
                        >
                            <template v-if="!imagePreview">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8 text-slate-500 mb-2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zM18.75 10.5h.008v.008h-.008V10.5z" />
                                </svg>
                                <span class="text-xs font-bold text-slate-400">Pilih Foto atau Kamera</span>
                                <span class="text-[9px] text-slate-600 mt-1">Kompresi & Watermark otomatis</span>
                            </template>
                            <template v-else>
                                <div class="relative w-full max-h-48 rounded-lg overflow-hidden flex items-center justify-center bg-slate-950 border border-slate-800">
                                    <img :src="imagePreview" alt="Preview" class="object-contain max-h-48 w-full" />
                                </div>
                                <span class="text-[10px] text-amber-500 font-bold mt-2 hover:underline">Ganti Foto</span>
                            </template>
                        </label>
                    </div>
                    
                    <!-- Processing Overlay -->
                    <div v-if="isProcessingImage" class="text-xs font-bold text-amber-500 flex items-center justify-center gap-1.5 py-1">
                        <svg class="animate-spin h-4 w-4" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        Memproses & Memberi Watermark...
                    </div>
                    <p v-if="form.errors.image" class="text-[10px] text-rose-400 font-semibold">{{ form.errors.image }}</p>
                </div>

                <!-- Submit Button -->
                <button 
                    type="submit" 
                    :disabled="form.processing || isProcessingImage"
                    class="w-full bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-slate-950 font-black py-4 px-4 rounded-xl shadow-lg shadow-amber-500/10 transition-all active:scale-[0.98] uppercase text-xs tracking-wider disabled:opacity-40 disabled:pointer-events-none"
                >
                    {{ form.processing ? 'Mengirim...' : 'Kirim Laporan' }}
                </button>
            </form>
        </div>
    </MobileAppLayout>
</template>
