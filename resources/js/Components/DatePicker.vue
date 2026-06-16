<script setup>
import { ref, computed, onMounted, onBeforeUnmount, watch } from 'vue';

const props = defineProps({
    modelValue: {
        type: String,
        default: ''  // Format: "YYYY-MM-DD" atau string kosong
    }
});

const emit = defineEmits(['update:modelValue']);

const isOpen = ref(false);
const triggerRef = ref(null);
const dropdownRef = ref(null);

// Tahun & Bulan yang sedang ditampilkan di panel navigasi (0-indexed untuk bulan)
const displayYear = ref(new Date().getFullYear());
const displayMonth = ref(new Date().getMonth());

// Tanggal hari ini
const todayDate = new Date();
const todayYear = todayDate.getFullYear();
const todayMonth = todayDate.getMonth();
const todayDay = todayDate.getDate();

const monthNamesFull = [
    'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
];

const dayNames = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];

// Parse modelValue menjadi objek Date atau null
const selectedParsed = computed(() => {
    if (!props.modelValue) return null;
    const [y, m, d] = props.modelValue.split('-').map(Number);
    return { year: y, month: m - 1, date: d }; // month 0-indexed
});

// Sync displayMonth & displayYear saat modelValue berubah atau saat dropdown dibuka
watch(() => props.modelValue, (newVal) => {
    if (newVal) {
        const [y, m] = newVal.split('-').map(Number);
        displayYear.value = y;
        displayMonth.value = m - 1;
    }
}, { immediate: true });

// Teks yang ditampilkan di trigger box
const displayText = computed(() => {
    if (!selectedParsed.value) return '';
    const { year, month, date } = selectedParsed.value;
    return `${date} ${monthNamesFull[month]} ${year}`;
});

const toggleDropdown = () => {
    isOpen.value = !isOpen.value;
    if (isOpen.value && selectedParsed.value) {
        displayYear.value = selectedParsed.value.year;
        displayMonth.value = selectedParsed.value.month;
    }
};

const prevMonth = () => {
    if (displayMonth.value === 0) {
        displayMonth.value = 11;
        displayYear.value--;
    } else {
        displayMonth.value--;
    }
};

const nextMonth = () => {
    if (displayMonth.value === 11) {
        displayMonth.value = 0;
        displayYear.value++;
    } else {
        displayMonth.value++;
    }
};

// Menghitung hari dalam bulan yang ditampilkan
const daysInMonth = computed(() => {
    // Hari pertama di bulan displayMonth
    const firstDayIndex = new Date(displayYear.value, displayMonth.value, 1).getDay();
    // Total hari di bulan displayMonth
    const totalDays = new Date(displayYear.value, displayMonth.value + 1, 0).getDate();
    // Total hari di bulan sebelumnya
    const prevTotalDays = new Date(displayYear.value, displayMonth.value, 0).getDate();

    const days = [];

    // Hari dari bulan sebelumnya (untuk mengisi grid kosong di awal)
    for (let i = firstDayIndex - 1; i >= 0; i--) {
        days.push({
            date: prevTotalDays - i,
            isCurrentMonth: false,
            monthOffset: -1
        });
    }

    // Hari dari bulan ini
    for (let i = 1; i <= totalDays; i++) {
        days.push({
            date: i,
            isCurrentMonth: true,
            monthOffset: 0
        });
    }

    // Hari dari bulan berikutnya (untuk melengkapi grid 42 sel / 6 baris)
    const remainingCells = 42 - days.length;
    for (let i = 1; i <= remainingCells; i++) {
        days.push({
            date: i,
            isCurrentMonth: false,
            monthOffset: 1
        });
    }

    return days;
});

const selectDate = (day) => {
    let targetMonth = displayMonth.value + day.monthOffset;
    let targetYear = displayYear.value;

    if (targetMonth < 0) {
        targetMonth = 11;
        targetYear--;
    } else if (targetMonth > 11) {
        targetMonth = 0;
        targetYear++;
    }

    const yyyy = targetYear;
    const mm = String(targetMonth + 1).padStart(2, '0');
    const dd = String(day.date).padStart(2, '0');

    const value = `${yyyy}-${mm}-${dd}`;
    emit('update:modelValue', value);
    isOpen.value = false;
};

const clearValue = () => {
    emit('update:modelValue', '');
    isOpen.value = false;
};

// Cek apakah hari tertentu adalah hari yang sedang dipilih
const isSelected = (day) => {
    if (!selectedParsed.value) return false;
    let targetMonth = displayMonth.value + day.monthOffset;
    let targetYear = displayYear.value;

    if (targetMonth < 0) {
        targetMonth = 11;
        targetYear--;
    } else if (targetMonth > 11) {
        targetMonth = 0;
        targetYear++;
    }

    return selectedParsed.value.year === targetYear &&
           selectedParsed.value.month === targetMonth &&
           selectedParsed.value.date === day.date;
};

// Cek apakah hari tertentu adalah hari berjalan (today)
const isToday = (day) => {
    let targetMonth = displayMonth.value + day.monthOffset;
    let targetYear = displayYear.value;

    if (targetMonth < 0) {
        targetMonth = 11;
        targetYear--;
    } else if (targetMonth > 11) {
        targetMonth = 0;
        targetYear++;
    }

    return todayYear === targetYear && todayMonth === targetMonth && todayDay === day.date;
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
            <span v-else class="text-slate-500 select-none">Pilih tanggal...</span>

            <!-- Tombol Clear (kanan), hanya tampil jika ada value -->
            <span
                v-if="modelValue"
                @click.stop="clearValue"
                class="absolute right-3 top-2.5 text-slate-500 hover:text-red-400 cursor-pointer transition-colors duration-200 z-10"
                title="Hapus filter tanggal"
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
                class="absolute left-0 mt-2 w-72 bg-slate-800 border border-slate-700 rounded-xl shadow-2xl z-50 p-4"
            >
                <!-- Navigasi Bulan & Tahun -->
                <div class="flex items-center justify-between mb-3">
                    <button
                        @click.stop="prevMonth"
                        type="button"
                        class="p-1 rounded-lg text-slate-400 hover:text-amber-500 hover:bg-slate-700 transition-colors duration-150"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                        </svg>
                    </button>
                    <span class="text-slate-200 font-bold text-sm select-none">
                        {{ monthNamesFull[displayMonth] }} {{ displayYear }}
                    </span>
                    <button
                        @click.stop="nextMonth"
                        type="button"
                        class="p-1 rounded-lg text-slate-400 hover:text-amber-500 hover:bg-slate-700 transition-colors duration-150"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                        </svg>
                    </button>
                </div>

                <!-- Header Nama Hari -->
                <div class="grid grid-cols-7 gap-1 text-center mb-1">
                    <span v-for="dName in dayNames" :key="dName" class="text-[10px] font-bold text-slate-500 uppercase select-none">
                        {{ dName }}
                    </span>
                </div>

                <!-- Grid Tanggal -->
                <div class="grid grid-cols-7 gap-1">
                    <button
                        v-for="(day, index) in daysInMonth"
                        :key="index"
                        @click.stop="selectDate(day)"
                        type="button"
                        class="relative py-1.5 text-xs font-medium rounded-lg transition-all duration-150 text-center"
                        :class="[
                            day.isCurrentMonth ? 'text-slate-200' : 'text-slate-500 opacity-40',
                            isSelected(day)
                                ? 'bg-amber-500 text-slate-900 font-bold shadow-lg shadow-amber-500/25'
                                : 'hover:bg-slate-700 hover:text-slate-100',
                            isToday(day) && !isSelected(day) ? 'ring-1 ring-amber-500/50' : ''
                        ]"
                    >
                        {{ day.date }}
                    </button>
                </div>
            </div>
        </Transition>
    </div>
</template>
