<script setup>
import AdminDashboardLayout from '@/Layouts/AdminDashboardLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { VueDatePicker } from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css';

const props = defineProps({
    filters: Object,
});

const form = useForm({
    title: '',
    theme: '',
    duration_minutes: 30,
    is_active: true,
    is_daily_quiz: false,
    daily_question_limit: 10,
    start_time: '',
    end_time: '',
});

const submit = () => {
    form.post(route('admin.quizzes.store', props.filters));
};


</script>

<template>
    <AdminDashboardLayout>
        <Head title="Tambah Kuis Baru - Admin" />

        <template #header>
            <span>Tambah Kuis Baru</span>
        </template>

        <div class="max-w-4xl mx-auto space-y-6">
            <div class="flex items-center justify-between">
                <Link 
                    :href="route('admin.quizzes.index', filters)" 
                    class="flex items-center gap-2 text-slate-400 hover:text-slate-200 transition-colors duration-200 text-sm font-medium"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                    </svg>
                    Kembali ke Daftar Kuis
                </Link>
            </div>

            <form @submit.prevent="submit" class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl space-y-6">
                <!-- Form Header -->
                <div class="border-b border-slate-800 pb-4">
                    <h3 class="text-lg font-bold text-slate-200">Formulir Kuis Baru</h3>
                    <p class="text-sm text-slate-400">Silakan tentukan judul, tema, topik, durasi, dan tipe kuis di bawah ini.</p>
                </div>

                <!-- Errors list if any -->
                <div v-if="Object.keys(form.errors).length > 0" class="p-4 bg-rose-950/40 border border-rose-800/50 rounded-xl text-sm text-rose-400 space-y-1">
                    <p class="font-semibold">Mohon perbaiki kesalahan berikut:</p>
                    <ul class="list-disc pl-5 space-y-1">
                        <li v-for="(error, key) in form.errors" :key="key">{{ error }}</li>
                    </ul>
                </div>

                <!-- Kuis Harian Toggle Option -->
                <div class="p-5 bg-purple-950/20 border border-purple-900/40 rounded-xl flex items-start gap-4">
                    <div class="pt-0.5">
                        <input 
                            v-model="form.is_daily_quiz" 
                            id="is_daily_quiz" 
                            type="checkbox" 
                            class="h-5 w-5 rounded border-slate-700 bg-slate-950 text-purple-600 focus:ring-purple-500 focus:ring-offset-slate-900 cursor-pointer" 
                        />
                    </div>
                    <div>
                        <label for="is_daily_quiz" class="block text-base font-bold text-purple-400 cursor-pointer">Jadikan sebagai Kuis Harian</label>
                        <p class="text-sm text-slate-400 mt-1">Jika dicentang, kuis ini akan menarik soal secara otomatis dan acak dari Master Bank Soal setiap kali dimainkan oleh Petugas. Anda tidak perlu memilih soal secara manual.</p>
                    </div>
                </div>

                <!-- Daily Question Limit Input (shows only if daily quiz is active) -->
                <div v-if="form.is_daily_quiz" class="p-5 bg-slate-950/40 border border-slate-800 rounded-xl space-y-2">
                    <label class="block text-sm font-semibold text-slate-300">Batas Jumlah Soal Acak yang Dimunculkan</label>
                    <input 
                        v-model="form.daily_question_limit" 
                        type="number" 
                        min="1" 
                        required
                        class="block w-48 bg-slate-950 border border-slate-800 rounded-lg py-2 px-4 text-sm text-slate-200 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition-colors" 
                    />
                    <p class="text-xs text-slate-500">Contoh: 10. Maka sistem akan mengambil 10 soal acak dari Master Bank Soal.</p>
                    <InputError :message="form.errors.daily_question_limit" class="mt-1" />
                </div>

                <!-- Title Input -->
                <div class="space-y-2">
                    <label class="block text-sm font-semibold text-slate-300">Judul Kuis</label>
                    <input 
                        v-model="form.title" 
                        type="text" 
                        required
                        placeholder="Contoh: Kuis Harian: Alat Keselamatan Kerja"
                        class="w-full bg-slate-950 border border-slate-800 rounded-lg py-2.5 px-4 text-sm text-slate-200 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition-colors"
                    />
                    <InputError :message="form.errors.title" class="mt-1" />
                </div>

                <!-- Theme Input -->
                <div class="space-y-2">
                    <label class="block text-sm font-semibold text-slate-300">Tema</label>
                    <input 
                        v-model="form.theme" 
                        type="text" 
                        required
                        placeholder="Contoh: K3 Pelabuhan & APAR"
                        class="w-full bg-slate-950 border border-slate-800 rounded-lg py-2.5 px-4 text-sm text-slate-200 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition-colors"
                    />
                    <InputError :message="form.errors.theme" class="mt-1" />
                </div>


                <!-- Duration Input -->
                <div class="space-y-2">
                    <label class="block text-sm font-semibold text-slate-300">Durasi Pengerjaan (Menit)</label>
                    <input 
                        v-model="form.duration_minutes" 
                        type="number" 
                        min="1" 
                        required
                        class="w-full bg-slate-950 border border-slate-800 rounded-lg py-2.5 px-4 text-sm text-slate-200 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition-colors"
                    />
                    <InputError :message="form.errors.duration_minutes" class="mt-1" />
                </div>

                <!-- Event Schedule Inputs (shows only if it is NOT a daily quiz) -->
                <div v-if="!form.is_daily_quiz" class="grid grid-cols-1 sm:grid-cols-2 gap-4 p-5 bg-blue-950/20 border border-blue-900/40 rounded-xl">
                    <div class="col-span-1 sm:col-span-2">
                        <h4 class="text-base font-bold text-blue-400">Jadwal Event Kuis</h4>
                        <p class="text-sm text-slate-400 mt-1">Tentukan kapan kuis ini bisa diakses oleh petugas. Kosongkan jika kuis selalu tersedia.</p>
                    </div>
                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-slate-300">Waktu Mulai (Start Time)</label>
                        <VueDatePicker 
                            v-model="form.start_time" 
                            format="dd/MM/yyyy HH:mm"
                            model-type="iso"
                            dark
                            text-input
                            :time-config="{ timePickerInline: true }"
                            placeholder="Pilih Waktu Mulai"
                            input-class-name="w-full bg-slate-950 border border-slate-800 rounded-lg py-2.5 text-sm text-slate-200 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition-colors"
                        />
                        <InputError :message="form.errors.start_time" class="mt-1" />
                    </div>
                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-slate-300">Waktu Selesai (End Time)</label>
                        <VueDatePicker 
                            v-model="form.end_time" 
                            format="dd/MM/yyyy HH:mm"
                            model-type="iso"
                            dark
                            text-input
                            :time-config="{ timePickerInline: true }"
                            placeholder="Pilih Waktu Selesai"
                            input-class-name="w-full bg-slate-950 border border-slate-800 rounded-lg py-2.5 text-sm text-slate-200 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition-colors"
                        />
                        <InputError :message="form.errors.end_time" class="mt-1" />
                    </div>
                </div>

                <!-- Active Toggle Option -->
                <div class="flex items-center gap-3">
                    <input 
                        v-model="form.is_active" 
                        id="is_active" 
                        type="checkbox" 
                        class="h-5 w-5 rounded border-slate-700 bg-slate-950 text-blue-600 focus:ring-blue-500 focus:ring-offset-slate-900 cursor-pointer" 
                    />
                    <label for="is_active" class="text-sm font-semibold text-slate-300 cursor-pointer">Aktif (Bisa dikerjakan petugas)</label>
                </div>

                <!-- Actions -->
                <div class="flex justify-end gap-3 pt-4 border-t border-slate-800">
                    <Link 
                        :href="route('admin.quizzes.index', filters)" 
                        class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-700 rounded-lg font-medium text-sm transition-colors"
                    >
                        Batal
                    </Link>
                    <button 
                        type="submit" 
                        :disabled="form.processing" 
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-lg font-semibold text-sm transition-colors shadow-lg shadow-blue-900/30 disabled:opacity-50"
                    >
                        Simpan Kuis
                    </button>
                </div>
            </form>
        </div>
    </AdminDashboardLayout>
</template>
