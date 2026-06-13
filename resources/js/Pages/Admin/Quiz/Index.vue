<script setup>
import AdminDashboardLayout from '@/Layouts/AdminDashboardLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    quizzes: Object,
    filters: Object,
});

const search = ref(props.filters?.search || '');
const status = ref(props.filters?.status || 'aktif');
const per_page = ref(props.filters?.per_page || '10');
const sortField = ref(props.filters?.sort_field || 'created_at');
const sortDirection = ref(props.filters?.sort_direction || 'desc');

const handleSearch = () => {
    router.get(route('admin.quizzes.index'), {
        search: search.value,
        status: status.value,
        per_page: per_page.value,
        sort_field: sortField.value,
        sort_direction: sortDirection.value,
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true
    });
};

watch([search, status, per_page], () => {
    handleSearch();
});

const sortBy = (field) => {
    if (sortField.value === field) {
        sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortField.value = field;
        sortDirection.value = 'desc';
    }
    handleSearch();
};

const deleteQuiz = (id) => {
    if(confirm('Apakah Anda yakin ingin menghapus kuis ini?')) {
        useForm({}).delete(route('admin.quizzes.destroy', id), {
            preserveScroll: true,
            preserveState: true,
        });
    }
}
const formatDateTime = (dateString) => {
    if (!dateString) return '-';
    const d = new Date(dateString);
    const day = String(d.getDate()).padStart(2, '0');
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const year = d.getFullYear();
    const hours = String(d.getHours()).padStart(2, '0');
    const minutes = String(d.getMinutes()).padStart(2, '0');
    return `${day}/${month}/${year} ${hours}:${minutes}`;
};
</script>

<template>
    <AdminDashboardLayout>
        <Head title="Kelola Kuis - Admin" />

        <template #header>
            <span>Kelola Kuis & Event</span>
        </template>

        <div class="space-y-6">
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl space-y-4">
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                    <h3 class="font-bold text-slate-200">Daftar Kuis Tersedia</h3>
                    <Link 
                        :href="route('admin.quizzes.create')"
                        class="flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white font-semibold text-sm rounded-lg transition-colors duration-200 shadow-lg shadow-blue-900/30"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        Tambah Kuis Baru
                    </Link>
                </div>

                <!-- Filters -->
                <div class="flex flex-col md:flex-row md:items-center gap-4 bg-slate-800/40 p-4 rounded-xl border border-slate-800">
                    <div class="flex-1 relative min-w-[200px]">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-slate-400">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                            </svg>
                        </div>
                        <input v-model="search" type="text" placeholder="Cari kuis..." class="w-full bg-slate-900 border border-slate-700 rounded-lg py-2 pl-10 px-4 text-sm text-slate-200 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition-colors duration-200" />
                    </div>
                    <div class="w-full md:w-48 relative">
                        <select v-model="status" class="w-full bg-slate-900 border border-slate-700 rounded-lg py-2 px-4 text-sm text-slate-200 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition-colors duration-200">
                            <option value="semua">Semua Status</option>
                            <option value="aktif">Aktif</option>
                            <option value="nonaktif">Nonaktif</option>
                        </select>
                    </div>
                    <div class="w-full md:w-32 relative">
                        <select v-model="per_page" class="w-full bg-slate-900 border border-slate-700 rounded-lg py-2 px-4 text-sm text-slate-200 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition-colors duration-200">
                            <option value="10">10 Kuis</option>
                            <option value="15">15 Kuis</option>
                            <option value="25">25 Kuis</option>
                            <option value="50">50 Kuis</option>
                            <option value="100">100 Kuis</option>
                        </select>
                    </div>
                </div>

                <!-- Data Table -->
                <div class="overflow-x-auto">
                    <table class="min-w-full text-slate-300">
                        <thead>
                            <tr class="border-b border-slate-800 text-left text-xs font-bold uppercase tracking-wider text-slate-400">
                                <th class="py-3 px-4 text-center w-12">No</th>
                                <th @click="sortBy('judul')" class="py-3 px-4 cursor-pointer select-none hover:text-slate-200 transition-colors duration-150">
                                    <div class="flex items-center gap-1">
                                        Judul Kuis
                                        <span v-if="sortField === 'judul'">{{ sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                    </div>
                                </th>
                                <th @click="sortBy('tipe')" class="py-3 px-4 text-center cursor-pointer select-none hover:text-slate-200 transition-colors duration-150">
                                    <div class="flex items-center justify-center gap-1">
                                        Tipe
                                        <span v-if="sortField === 'tipe'">{{ sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                    </div>
                                </th>
                                <th @click="sortBy('tema')" class="py-3 px-4 cursor-pointer select-none hover:text-slate-200 transition-colors duration-150">
                                    <div class="flex items-center gap-1">
                                        Tema
                                        <span v-if="sortField === 'tema'">{{ sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                    </div>
                                </th>
                                <th @click="sortBy('durasi')" class="py-3 px-4 text-center cursor-pointer select-none hover:text-slate-200 transition-colors duration-150">
                                    <div class="flex items-center justify-center gap-1">
                                        Durasi / Limit Soal
                                        <span v-if="sortField === 'durasi'">{{ sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                    </div>
                                </th>
                                <th @click="sortBy('waktu')" class="py-3 px-4 text-center cursor-pointer select-none hover:text-slate-200 transition-colors duration-150">
                                    <div class="flex items-center justify-center gap-1">
                                        Waktu Event
                                        <span v-if="sortField === 'waktu'">{{ sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                    </div>
                                </th>
                                <th @click="sortBy('status')" class="py-3 px-4 text-center cursor-pointer select-none hover:text-slate-200 transition-colors duration-150">
                                    <div class="flex items-center justify-center gap-1">
                                        Status
                                        <span v-if="sortField === 'status'">{{ sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                    </div>
                                </th>
                                <th class="py-3 px-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800 text-sm">
                            <tr v-for="(quiz, index) in quizzes.data" :key="quiz.id" class="hover:bg-slate-800/30 transition-colors duration-150">
                                <td class="py-4 px-4 text-center font-medium text-slate-400">
                                    {{ quizzes.from + index }}
                                </td>
                                <td class="py-4 px-4 font-medium text-slate-200">
                                    {{ quiz.title }}
                                </td>
                                <td class="py-4 px-4 text-center whitespace-nowrap">
                                    <span v-if="quiz.is_daily_quiz" class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-900/30 text-purple-400 border border-purple-800/30">
                                        Kuis Harian
                                    </span>
                                    <span v-else class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-slate-800 text-slate-300 border border-slate-700">
                                        Event Quiz
                                    </span>
                                </td>
                                <td class="py-4 px-4">
                                    <span class="text-slate-300 font-medium">{{ quiz.theme }}</span>
                                </td>
                                <td class="py-4 px-4 text-center whitespace-nowrap">
                                    <div class="text-slate-300">{{ quiz.duration_minutes }} Menit</div>
                                    <div v-if="quiz.is_daily_quiz" class="text-xs text-purple-400 font-medium mt-0.5">Limit: {{ quiz.daily_question_limit }} Soal Acak</div>
                                    <div v-else class="text-xs text-slate-500 mt-0.5">{{ quiz.questions_count }} Soal Terpilih</div>
                                </td>
                                <td class="py-4 px-4 text-center whitespace-nowrap">
                                    <div v-if="!quiz.is_daily_quiz && quiz.start_time">
                                        <div class="text-xs text-slate-300">Mulai: {{ formatDateTime(quiz.start_time) }}</div>
                                        <div class="text-xs text-slate-300 mt-1">Selesai: {{ formatDateTime(quiz.end_time) }}</div>
                                    </div>
                                    <div v-else class="text-xs text-slate-500">-</div>
                                </td>
                                <td class="py-4 px-4 text-center whitespace-nowrap">
                                    <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full border"
                                          :class="quiz.is_active 
                                              ? 'bg-emerald-900/30 text-emerald-400 border-emerald-800/50' 
                                              : 'bg-rose-900/30 text-rose-400 border-rose-800/50'">
                                        {{ quiz.is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="py-4 px-4 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-4">
                                        <!-- Kelola Soal (tidak perlu untuk Kuis Harian) -->
                                        <Link 
                                            v-if="!quiz.is_daily_quiz" 
                                            :href="route('admin.quizzes.show', quiz.id)" 
                                            class="text-blue-400 hover:text-blue-300 transition-colors" 
                                            title="Kelola Soal"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM3.75 12h.007v.008H3.75V12Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm-3.75 5.25h.007v.008H3.75v-.008Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                                            </svg>
                                        </Link>
                                        <span v-else class="w-5 h-5 block" title="Kuis harian otomatis acak dari bank soal"></span>

                                        <Link 
                                            :href="route('admin.quizzes.edit', quiz.id)" 
                                            class="text-indigo-400 hover:text-indigo-300 transition-colors" 
                                            title="Edit"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" />
                                            </svg>
                                        </Link>
                                        <button 
                                            @click="deleteQuiz(quiz.id)" 
                                            class="text-rose-500 hover:text-rose-400 transition-colors" 
                                            title="Hapus"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="quizzes.data.length === 0">
                                <td colspan="8" class="py-8 text-center text-slate-500">Belum ada data kuis.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Section -->
                <div class="mt-6 flex flex-col sm:flex-row justify-between items-center gap-4 border-t border-slate-800 pt-6" v-if="quizzes.data.length > 0">
                    <div class="text-sm text-slate-400">
                        Menampilkan 
                        <span class="font-medium text-slate-300">{{ quizzes.from }}</span> 
                        sampai 
                        <span class="font-medium text-slate-300">{{ quizzes.to }}</span> 
                        dari 
                        <span class="font-medium text-slate-300">{{ quizzes.total }}</span> kuis
                    </div>
                    <Pagination :links="quizzes.links" />
                </div>
            </div>
        </div>
    </AdminDashboardLayout>
</template>
