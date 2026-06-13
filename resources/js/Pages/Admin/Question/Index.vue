<script setup>
import AdminDashboardLayout from '@/Layouts/AdminDashboardLayout.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import Pagination from '@/Components/Pagination.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    questions: Object,
    categories: Array,
    filters: Object,
});

const search = ref(props.filters?.search || '');
const category_id = ref(props.filters?.category_id || '');
const risk_level = ref(props.filters?.risk_level || '');
const per_page = ref(props.filters?.per_page || '10');

// State loading saat request filter sedang berjalan
const isLoading = ref(false);

const handleSearch = () => {
    isLoading.value = true;
    router.get(route('admin.questions.index'), {
        search: search.value,
        category_id: category_id.value,
        risk_level: risk_level.value,
        per_page: per_page.value,
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
        onFinish: () => { isLoading.value = false; }
    });
};

// Auto search on filter change
watch([search, category_id, risk_level, per_page], () => {
    handleSearch();
});

const isUploadModalOpen = ref(false);
const uploadForm = useForm({
    file: null,
});

const submitUpload = () => {
    uploadForm.post(route('admin.questions.import'), {
        onSuccess: () => {
            isUploadModalOpen.value = false;
            uploadForm.reset();
        },
    });
};

// Delete confirmation modal state
const confirmingQuestionDeletion = ref(false);
const questionToDelete = ref(null);
const isDeleting = ref(false);

const confirmQuestionDeletion = (id) => {
    questionToDelete.value = id;
    confirmingQuestionDeletion.value = true;
};

const deleteQuestion = () => {
    isDeleting.value = true;
    router.delete(route('admin.questions.destroy', questionToDelete.value), {
        preserveScroll: true,
        onSuccess: () => {
            confirmingQuestionDeletion.value = false;
            questionToDelete.value = null;
            isDeleting.value = false;
        },
        onError: () => {
            isDeleting.value = false;
        }
    });
};
</script>

<template>
    <Head title="Master Bank Soal - Admin" />

    <AdminDashboardLayout>
        <template #header>
            <span>Master Bank Soal</span>
        </template>

        <div class="space-y-6">
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl space-y-4">
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                    <h3 class="font-bold text-slate-200">Daftar Soal Tersedia</h3>
                    <div class="flex gap-2">
                        <Link 
                            :href="route('admin.questions.create')"
                            class="flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white font-semibold text-sm rounded-lg transition-colors duration-200 shadow-lg shadow-blue-900/30"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                            Tambah Soal Manual
                        </Link>
                        <a 
                            :href="route('admin.questions.template')" 
                            class="flex items-center gap-2 px-4 py-2 bg-slate-600 hover:bg-slate-500 text-white font-semibold text-sm rounded-lg transition-colors duration-200 shadow-lg shadow-slate-900/30"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m6.75 12-3-3m0 0-3 3m3-3v6m-1.5-15H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                            </svg>
                            Download Template
                        </a>
                        <a 
                            :href="route('admin.questions.export')" 
                            class="flex items-center gap-2 px-4 py-2 bg-amber-600 hover:bg-amber-500 text-white font-semibold text-sm rounded-lg transition-colors duration-200 shadow-lg shadow-amber-900/30"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                            </svg>
                            Export Excel
                        </a>
                        <button 
                            @click="isUploadModalOpen = true" 
                            class="flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white font-semibold text-sm rounded-lg transition-colors duration-200 shadow-lg shadow-emerald-900/30"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" />
                            </svg>
                            Upload Excel
                        </button>
                    </div>
                </div>

                <!-- Filters -->
                <div class="flex flex-col md:flex-row md:items-center gap-4 bg-slate-800/40 p-4 rounded-xl border border-slate-800">
                    <div class="flex-1 relative min-w-[200px]">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-slate-400">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                            </svg>
                        </div>
                        <input v-model="search" type="text" placeholder="Cari pertanyaan..." class="w-full bg-slate-900 border border-slate-700 rounded-lg py-2 pl-10 px-4 text-sm text-slate-200 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition-colors duration-200" />
                    </div>
                    <div class="w-full md:w-48 relative">
                        <select v-model="category_id" class="w-full bg-slate-900 border border-slate-700 rounded-lg py-2 px-4 text-sm text-slate-200 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition-colors duration-200">
                            <option value="">Semua Kategori</option>
                            <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                        </select>
                    </div>
                    <div class="w-full md:w-48 relative">
                        <select v-model="risk_level" class="w-full bg-slate-900 border border-slate-700 rounded-lg py-2 px-4 text-sm text-slate-200 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition-colors duration-200">
                            <option value="">Semua Level Resiko</option>
                            <option value="Low">Low</option>
                            <option value="Medium">Medium</option>
                            <option value="High">High</option>
                        </select>
                    </div>
                    <div class="w-full md:w-32 relative">
                        <select v-model="per_page" class="w-full bg-slate-900 border border-slate-700 rounded-lg py-2 px-4 text-sm text-slate-200 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition-colors duration-200">
                            <option value="10">10 Soal</option>
                            <option value="15">15 Soal</option>
                            <option value="25">25 Soal</option>
                            <option value="50">50 Soal</option>
                            <option value="100">100 Soal</option>
                        </select>
                    </div>
                </div>

                <!-- Data Table -->
                <div class="overflow-x-auto relative transition-opacity duration-200" :class="{ 'opacity-50 pointer-events-none': isLoading }">
                    <!-- Loading overlay -->
                    <div v-if="isLoading" class="absolute inset-0 z-10 flex flex-col items-center justify-center gap-3 bg-slate-900/60 rounded-lg backdrop-blur-sm">
                        <svg class="animate-spin w-7 h-7 text-amber-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        <span class="text-xs text-amber-400 font-medium">Memuat data...</span>
                    </div>
                    <table class="min-w-full text-slate-300">
                        <thead>
                            <tr class="border-b border-slate-800 text-left text-xs font-bold uppercase tracking-wider text-slate-400">
                                <th class="py-3 px-4 text-center w-12">No</th>
                                <th class="py-3 px-4">Pertanyaan</th>
                                <th class="py-3 px-4">Kategori</th>
                                <th class="py-3 px-4 text-center">Resiko</th>
                                <th class="py-3 px-4 text-center">Dipakai (Kuis)</th>
                                <th class="py-3 px-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800 text-sm">
                            <tr v-for="(q, index) in questions.data" :key="q.id" class="hover:bg-slate-800/30 transition-colors duration-150">
                                <td class="py-3 px-4 text-center font-medium text-slate-400">
                                    {{ questions.from + index }}
                                </td>
                                <td class="py-3 px-4 font-medium text-slate-200">
                                    <div class="line-clamp-2 max-w-md">{{ q.question_text }}</div>
                                    <span v-if="q.question_image" class="inline-flex mt-1 items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-900/30 text-blue-400 border border-blue-800/50">
                                        Ada Gambar
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="flex flex-wrap gap-1">
                                        <span v-for="cat in q.categories" :key="cat.id" class="px-2 py-1 bg-slate-800 border border-slate-700 rounded text-xs text-slate-300">{{ cat.name }}</span>
                                    </div>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full"
                                          :class="{
                                              'bg-emerald-900/30 text-emerald-400 border border-emerald-800/50': q.risk_level === 'Low',
                                              'bg-amber-900/30 text-amber-400 border border-amber-800/50': q.risk_level === 'Medium',
                                              'bg-rose-900/30 text-rose-400 border border-rose-800/50': q.risk_level === 'High'
                                          }">
                                        {{ q.risk_level }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    {{ q.quizzes_count }}
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <div class="flex items-center justify-end gap-3">
                                        <Link :href="route('admin.questions.edit', q.id)" class="text-indigo-400 hover:text-indigo-300 transition-colors" title="Edit">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" />
                                            </svg>
                                        </Link>
                                        <button @click="confirmQuestionDeletion(q.id)" class="text-rose-500 hover:text-rose-400 transition-colors" title="Hapus">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="questions.data.length === 0">
                                <td colspan="6" class="py-8 text-center text-slate-500">Belum ada soal di Master Bank.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Section -->
                <div class="mt-6 flex flex-col sm:flex-row justify-between items-center gap-4 border-t border-slate-800 pt-6" v-if="questions.data.length > 0">
                    <div class="text-sm text-slate-400">
                        Menampilkan 
                        <span class="font-medium text-slate-300">{{ questions.from }}</span> 
                        sampai 
                        <span class="font-medium text-slate-300">{{ questions.to }}</span> 
                        dari 
                        <span class="font-medium text-slate-300">{{ questions.total }}</span> soal
                    </div>
                    <Pagination :links="questions.links" />
                </div>
            </div>
        </div>

        <!-- Upload Modal -->
        <div v-if="isUploadModalOpen" class="fixed z-50 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm transition-opacity" @click="!uploadForm.processing && (isUploadModalOpen = false)"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-bottom bg-slate-900 border border-slate-700 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <form @submit.prevent="submitUpload">
                        <div class="px-6 pt-6 pb-6">
                            <h3 class="text-xl font-bold text-slate-200" id="modal-title">Upload Bank Soal</h3>
                            <div class="mt-2">
                                <p class="text-sm text-slate-400">Unggah file template (Excel/CSV) untuk menambahkan banyak soal sekaligus ke Master Bank Soal.</p>
                                <div class="mt-6">
                                    <input type="file" @change="e => uploadForm.file = e.target.files[0]" accept=".csv,.xlsx,.xls" :disabled="uploadForm.processing" required class="block w-full text-sm text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-600 file:text-white hover:file:bg-blue-500 cursor-pointer transition-colors disabled:opacity-50 disabled:cursor-not-allowed" />
                                </div>
                                <div v-if="uploadForm.errors.file" class="text-rose-500 text-sm mt-2">{{ uploadForm.errors.file }}</div>

                                <!-- Progress & Processing States -->
                                <div v-if="uploadForm.progress" class="w-full bg-slate-800 rounded-full h-2.5 mt-4">
                                    <div class="bg-blue-600 h-2.5 rounded-full transition-all duration-150" :style="{ width: uploadForm.progress.percentage + '%' }"></div>
                                    <p class="text-xs text-slate-400 mt-1.5 text-right">Mengunggah: {{ uploadForm.progress.percentage }}%</p>
                                </div>
                                <div v-if="uploadForm.processing" class="flex items-center justify-center gap-3 mt-4 p-3 bg-slate-800/40 rounded-xl border border-slate-700/50 animate-pulse">
                                    <svg class="animate-spin h-5 w-5 text-emerald-500" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span class="text-sm text-slate-300 font-medium">Sedang memproses & mengimpor soal...</span>
                                </div>
                            </div>
                        </div>
                        <div class="bg-slate-800/50 px-6 py-4 flex flex-row-reverse gap-3 border-t border-slate-800">
                            <button type="submit" :disabled="uploadForm.processing" class="inline-flex justify-center rounded-lg border border-transparent px-4 py-2 bg-blue-600 text-sm font-semibold text-white hover:bg-blue-500 focus:outline-none transition-colors disabled:opacity-50">
                                {{ uploadForm.processing ? 'Memproses...' : 'Upload & Proses' }}
                            </button>
                            <button type="button" @click="isUploadModalOpen = false" :disabled="uploadForm.processing" class="inline-flex justify-center rounded-lg border border-slate-700 px-4 py-2 bg-slate-800 text-sm font-semibold text-slate-300 hover:bg-slate-700 focus:outline-none transition-colors disabled:opacity-50">
                                Batal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <ConfirmationModal
            :show="confirmingQuestionDeletion"
            title="Hapus Soal Master Bank"
            message="Apakah Anda yakin ingin menghapus soal ini? Tindakan ini akan menghapus soal secara permanen dari Master Bank dan menghapusnya dari semua kuis yang terhubung."
            confirm-text="Ya, Hapus"
            cancel-text="Batal"
            :loading="isDeleting"
            @close="confirmingQuestionDeletion = false"
            @confirm="deleteQuestion"
        />
    </AdminDashboardLayout>
</template>
