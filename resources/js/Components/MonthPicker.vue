<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';

const props = defineProps({
    modelValue: {
        type: String,
        default: ''  // Format: "YYYY-MM" atau string kosong
    }
});

const emit = defineEmits(['update:modelValue']);

const isOpen = ref(false);
const triggerRef = ref(null);
const dropdownRef = ref(null);

// Tahun yang sedang ditampilkan di panel navigasi
const displayYear = ref(new Date().getFullYear());

// Bulan & tahun saat ini untuk penanda visual
const currentMonth = new Date().getMonth(); // 0-11
const currentYear = new Date().getFullYear();

// Nama bulan dalam Bahasa Indonesia
const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
const monthNamesFull = [
    'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
];

// Parse modelValue menjadi objek { year, month } atau null
const selectedParsed = computed(() => {
    if (!props.modelValue) return null;
    const [y, m] = props.modelValue.split('-').map(Number);
    return { year: y, month: m - 1 }; // month 0-indexed agar mudah dibandingkan
});

// Teks yang ditampilkan di trigger box
const displayText = computed(() => {
    if (!selectedParsed.value) return '';
    const { year, month } = selectedParsed.value;
    return `${monthNamesFull[month]} ${year}`;
});

const toggleDropdown = () => {
    isOpen.value = !isOpen.value;
    if (isOpen.value && selectedParsed.value) {
        // Ketika buka dropdown, set displayYear ke tahun yang sedang dipilih
        displayYear.value = selectedParsed.value.year;
    }
};

const prevYear = () => {
    displayYear.value--;
};

const nextYear = () => {
    displayYear.value++;
};

const selectMonth = (monthIndex) => {
    // monthIndex: 0-11
    const mm = String(monthIndex + 1).padStart(2, '0');
    const value = `${displayYear.value}-${mm}`;
    emit('update:modelValue', value);
    isOpen.value = false;
};

const clearValue = () => {
    emit('update:modelValue', '');
    isOpen.value = false;
};

// Helper: cek apakah bulan tertentu adalah bulan yang sedang dipilih
const isSelected = (monthIndex) => {
    if (!selectedParsed.value) return false;
    return selectedParsed.value.year === displayYear.value && selectedParsed.value.month === monthIndex;
};

// Helper: cek apakah bulan tertentu adalah bulan berjalan (saat ini)
const isCurrent = (monthIndex) => {
    return currentYear === displayYear.value && currentMonth === monthIndex;
};

const handleClickOutside = (event) => {
    if (
        triggerRef.value && !triggerRef.value.contains(event.target) &&
        dropdownRef.value && !dropdownRef.value.contains(event.target)
    ) {
        isOpen.value = false;
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onBeforeUnmount(() => {
    document.removeEventListener('click', handleClickOutside);
});
</script>

<template>
    <div class="relative">
        <!-- Trigger Box -->
        <div
            ref="triggerRef"
            @click="toggleDropdown"
            class="w-full bg-slate-900 border rounded-lg py-2 pl-10 pr-8 text-sm cursor-pointer transition-colors duration-200 flex items-center relative"
            :class="[
                isOpen ? 'border-amber-500 ring-1 ring-amber-500' : 'border-slate-700 hover:border-slate-600'
            ]"
        >
            <!-- Icon Kalender (kiri) -->
            <span 
                class="absolute left-3 top-2.5 transition-colors duration-200 pointer-events-none"
                :class="isOpen ? 'text-amber-500' : 'text-slate-400'"
            >
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                </svg>
            </span>

            <!-- Display Text -->
            <span v-if="displayText" class="text-slate-200 select-none">{{ displayText }}</span>
            <span v-else class="text-slate-500 select-none">Pilih bulan...</span>

            <!-- Tombol Clear (kanan), hanya tampil jika ada value -->
            <span
                v-if="modelValue"
                @click.stop="clearValue"
                class="absolute right-3 top-2.5 text-slate-500 hover:text-red-400 cursor-pointer transition-colors duration-200 z-10"
                title="Hapus filter bulan"
            >
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </span>
        </div>

        <!-- Dropdown Panel -->
        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0 translate-y-1"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100 translate-y-0"
            leave-to-class="opacity-0 translate-y-1"
        >
            <div
                v-if="isOpen"
                ref="dropdownRef"
                class="absolute left-0 mt-2 w-64 bg-slate-800 border border-slate-700 rounded-xl shadow-2xl z-50 p-4"
            >
                <!-- Navigasi Tahun -->
                <div class="flex items-center justify-between mb-3">
                    <button
                        @click.stop="prevYear"
                        type="button"
                        class="p-1 rounded-lg text-slate-400 hover:text-amber-500 hover:bg-slate-700 transition-colors duration-150"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                        </svg>
                    </button>
                    <span class="text-slate-200 font-bold text-sm select-none">{{ displayYear }}</span>
                    <button
                        @click.stop="nextYear"
                        type="button"
                        class="p-1 rounded-lg text-slate-400 hover:text-amber-500 hover:bg-slate-700 transition-colors duration-150"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                        </svg>
                    </button>
                </div>

                <!-- Grid Bulan 3x4 -->
                <div class="grid grid-cols-3 gap-1.5">
                    <button
                        v-for="(name, index) in monthNames"
                        :key="index"
                        @click.stop="selectMonth(index)"
                        type="button"
                        class="relative py-2 text-xs font-medium rounded-lg transition-all duration-150 text-center"
                        :class="[
                            isSelected(index)
                                ? 'bg-amber-500 text-slate-900 font-bold shadow-lg shadow-amber-500/25'
                                : 'text-slate-300 hover:bg-slate-700 hover:text-slate-100',
                            isCurrent(index) && !isSelected(index) ? 'ring-1 ring-amber-500/50' : ''
                        ]"
                    >
                        {{ name }}
                        <!-- Dot indicator untuk bulan berjalan -->
                        <span
                            v-if="isCurrent(index) && !isSelected(index)"
                            class="absolute bottom-0.5 left-1/2 -translate-x-1/2 w-1 h-1 rounded-full bg-amber-500"
                        ></span>
                    </button>
                </div>
            </div>
        </Transition>
    </div>
</template>
