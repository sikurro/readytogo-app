<script setup>
import AdminDashboardLayout from '@/Layouts/AdminDashboardLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import Pagination from '@/Components/Pagination.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    questions: Object,
    filters: Object,
});

const search = ref(props.filters?.search || '');
const isLoading = ref(false);

const handleSearch = () => {
    isLoading.value = true;
    router.get(route('admin.fatigue-questions.index'), {
        search: search.value,
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
        onFinish: () => { isLoading.value = false; }
    });
};

watch(search, () => {
    handleSearch();
});

const isCreateModalOpen = ref(false);
const isEditModalOpen = ref(false);
const isDeleteModalOpen = ref(false);
const selectedQuestion = ref(null);

const createForm = useForm({
    question_text: '',
    is_active: true,
});

const submitCreate = () => {
    createForm.post(route('admin.fatigue-questions.store'), {
        onSuccess: () => {
            isCreateModalOpen.value = false;
            createForm.reset();
        }
    });
};

const editForm = useForm({
    question_text: '',
    is_active: true,
});

const openEditModal = (question) => {
    selectedQuestion.value = question;
    editForm.question_text = question.question_text;
    editForm.is_active = !!question.is_active;
    isEditModalOpen.value = true;
};

const submitEdit = () => {
    editForm.put(route('admin.fatigue-questions.update', selectedQuestion.value.id), {
        onSuccess: () => {
            isEditModalOpen.value = false;
            editForm.reset();
        }
    });
};

const openDeleteModal = (question) => {
    selectedQuestion.value = question;
    isDeleteModalOpen.value = true;
};

const submitDelete = () => {
    router.delete(route('admin.fatigue-questions.destroy', selectedQuestion.value.id), {
        onSuccess: () => {
            isDeleteModalOpen.value = false;
        }
    });
};
</script>

<template>
    <Head title="Pertanyaan Fatigue Check - Admin" />

    <AdminDashboardLayout>
        <template #header>
            <span>Kelola Pertanyaan Fatigue Check</span>
        </template>

        <div class="space-y-6">
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between border-b border-slate-800 pb-4 gap-4">
                    <div>
                        <h3 class="font-bold text-lg text-slate-200">Daftar Pertanyaan Fatigue</h3>
                        <p class="text-xs text-slate-400">Atur kuesioner kebugaran (fatigue check) yang harus diisi oleh petugas.</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <button 
                            @click="isCreateModalOpen = true"
                            class="flex items-center gap-2 px-4 py-2 bg-amber-500 hover:bg-amber-400 text-slate-950 font-extrabold text-sm rounded-lg transition-colors duration-200 shadow-lg shadow-amber-500/20"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                            Tambah Pertanyaan
                        </button>
                    </div>
                </div>

                <div class="flex flex-col md:flex-row md:items-center gap-4 bg-slate-800/40 p-4 rounded-xl border border-slate-800">
                    <div class="flex-1 relative min-w-[200px]">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-slate-400">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                            </svg>
                        </div>
                        <input v-model="search" type="text" placeholder="Cari pertanyaan..." class="w-full bg-slate-900 border border-slate-700 rounded-lg py-2 pl-10 px-4 text-sm text-slate-200 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition-colors duration-200" />
                    </div>
                </div>

                <div class="overflow-x-auto rounded-lg border border-slate-800 relative transition-opacity duration-200" :class="{ 'opacity-50 pointer-events-none': isLoading }">
                    <div v-if="isLoading" class="absolute inset-0 z-10 flex flex-col items-center justify-center gap-3 bg-slate-900/60 rounded-lg backdrop-blur-sm">
                        <svg class="animate-spin w-7 h-7 text-amber-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        <span class="text-xs text-amber-400 font-medium">Memuat data...</span>
                    </div>
                    <table class="min-w-full text-slate-300">
                        <thead>
                            <tr class="bg-slate-950 border-b border-slate-800 text-left text-xs font-bold uppercase tracking-wider text-slate-400">
                                <th class="py-3 px-4 text-center w-12">No</th>
                                <th class="py-3 px-4">Isi Pertanyaan</th>
                                <th class="py-3 px-4 text-center w-32">Status</th>
                                <th class="py-3 px-4 text-right w-24">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800 text-sm">
                            <tr v-for="(q, index) in questions.data" :key="q.id" class="hover:bg-slate-850/50 transition-colors duration-150">
                                <td class="py-3 px-4 text-center font-medium text-slate-500">
                                    {{ questions.from + index }}
                                </td>
                                <td class="py-3 px-4">
                                    <div class="font-semibold text-slate-100">{{ q.question_text }}</div>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <span :class="[q.is_active ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/25' : 'bg-slate-800 text-slate-500 border border-slate-700/50', 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold']">
                                        {{ q.is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <div class="flex items-center justify-end gap-3">
                                        <button @click="openEditModal(q)" class="text-indigo-400 hover:text-indigo-300 transition-colors" title="Edit">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" />
                                            </svg>
                                        </button>
                                        <button @click="openDeleteModal(q)" class="text-rose-500 hover:text-rose-400 transition-colors" title="Hapus">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="questions.data.length === 0">
                                <td colspan="4" class="py-8 text-center text-slate-500">
                                    Tidak ada data pertanyaan yang ditemukan.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="flex items-center justify-between border-t border-slate-800 pt-4">
                    <div class="text-xs text-slate-400">
                        Menampilkan {{ questions.from || 0 }} sampai {{ questions.to || 0 }} dari {{ questions.total }} pertanyaan.
                    </div>
                    <Pagination :links="questions.links" />
                </div>
            </div>
        </div>

        <!-- Create Modal -->
        <div v-if="isCreateModalOpen" class="fixed z-50 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm transition-opacity" @click="!createForm.processing && (isCreateModalOpen = false)"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-bottom bg-slate-900 border border-slate-700 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <form @submit.prevent="submitCreate">
                        <div class="px-6 pt-6 pb-6">
                            <h2 class="text-lg font-bold text-slate-200 mb-4">Tambah Pertanyaan Fatigue</h2>
                            <div class="space-y-4">
                                <div>
                                    <InputLabel for="create_text" value="Isi Pertanyaan" class="text-slate-350" />
                                    <textarea id="create_text" rows="3" class="mt-1 block w-full bg-slate-950 border-slate-850 rounded-lg text-slate-250 focus:border-amber-500 focus:ring-amber-500" v-model="createForm.question_text" required placeholder="Contoh: Apakah Anda tidur minimal 6 jam semalam?"></textarea>
                                    <InputError :message="createForm.errors.question_text" class="mt-1" />
                                </div>
                                <div class="flex items-center gap-2">
                                    <input type="checkbox" id="create_active" class="rounded bg-slate-950 border-slate-850 text-amber-500 focus:ring-amber-500" v-model="createForm.is_active" />
                                    <label for="create_active" class="text-sm text-slate-300 font-medium">Aktif (Ditampilkan ke petugas)</label>
                                </div>
                            </div>
                        </div>
                        <div class="bg-slate-800/50 px-6 py-4 flex flex-row-reverse gap-3 border-t border-slate-800">
                            <button type="submit" :disabled="createForm.processing" class="inline-flex justify-center items-center gap-2 rounded-lg border border-transparent px-5 py-2.5 bg-amber-500 text-sm font-semibold text-slate-950 hover:bg-amber-400 focus:outline-none transition-colors disabled:opacity-50 shadow-lg shadow-amber-500/10">
                                {{ createForm.processing ? 'Menyimpan...' : 'Simpan Pertanyaan' }}
                            </button>
                            <button type="button" @click="isCreateModalOpen = false" :disabled="createForm.processing" class="inline-flex justify-center rounded-lg border border-slate-700 px-4 py-2 bg-slate-800 text-sm font-semibold text-slate-300 hover:bg-slate-700 focus:outline-none transition-colors disabled:opacity-50">
                                Batal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Edit Modal -->
        <div v-if="isEditModalOpen" class="fixed z-50 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm transition-opacity" @click="!editForm.processing && (isEditModalOpen = false)"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-bottom bg-slate-900 border border-slate-700 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <form @submit.prevent="submitEdit">
                        <div class="px-6 pt-6 pb-6">
                            <h2 class="text-lg font-bold text-slate-200 mb-4">Edit Pertanyaan</h2>
                            <div class="space-y-4">
                                <div>
                                    <InputLabel for="edit_text" value="Isi Pertanyaan" class="text-slate-350" />
                                    <textarea id="edit_text" rows="3" class="mt-1 block w-full bg-slate-950 border-slate-850 rounded-lg text-slate-250 focus:border-amber-500 focus:ring-amber-500" v-model="editForm.question_text" required></textarea>
                                    <InputError :message="editForm.errors.question_text" class="mt-1" />
                                </div>
                                <div class="flex items-center gap-2">
                                    <input type="checkbox" id="edit_active" class="rounded bg-slate-950 border-slate-850 text-amber-500 focus:ring-amber-500" v-model="editForm.is_active" />
                                    <label for="edit_active" class="text-sm text-slate-300 font-medium">Aktif (Ditampilkan ke petugas)</label>
                                </div>
                            </div>
                        </div>
                        <div class="bg-slate-800/50 px-6 py-4 flex flex-row-reverse gap-3 border-t border-slate-800">
                            <button type="submit" :disabled="editForm.processing" class="inline-flex justify-center items-center gap-2 rounded-lg border border-transparent px-5 py-2.5 bg-amber-550 text-sm font-semibold text-slate-950 hover:bg-amber-450 focus:outline-none transition-colors disabled:opacity-50 shadow-lg shadow-amber-500/10">
                                {{ editForm.processing ? 'Memperbarui...' : 'Perbarui Pertanyaan' }}
                            </button>
                            <button type="button" @click="isEditModalOpen = false" :disabled="editForm.processing" class="inline-flex justify-center rounded-lg border border-slate-700 px-4 py-2 bg-slate-800 text-sm font-semibold text-slate-300 hover:bg-slate-700 focus:outline-none transition-colors disabled:opacity-50">
                                Batal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Delete Modal -->
        <div v-if="isDeleteModalOpen" class="fixed z-50 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm transition-opacity" @click="isDeleteModalOpen = false"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-bottom bg-slate-900 border border-slate-700 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full">
                    <div class="px-6 pt-6 pb-6">
                        <div class="flex items-center justify-center w-12 h-12 rounded-full bg-rose-900/30 text-rose-500 mb-4 mx-auto">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <h2 class="text-lg font-bold text-slate-200 mb-2 text-center">Hapus Pertanyaan</h2>
                        <p class="text-sm text-slate-400 text-center">Apakah Anda yakin ingin menghapus pertanyaan ini? Tindakan ini tidak dapat dibatalkan.</p>
                        <div class="mt-4 p-3 bg-slate-950 rounded-lg border border-slate-850 text-xs text-slate-300 italic text-center">
                            "{{ selectedQuestion?.question_text }}"
                        </div>
                    </div>
                    <div class="bg-slate-800/50 px-6 py-4 flex flex-row-reverse gap-3 border-t border-slate-800">
                        <button @click="submitDelete" class="inline-flex justify-center items-center gap-2 rounded-lg border border-transparent px-5 py-2.5 bg-rose-600 text-sm font-semibold text-white hover:bg-rose-500 focus:outline-none transition-colors shadow-lg shadow-rose-900/20">
                            Ya, Hapus
                        </button>
                        <button @click="isDeleteModalOpen = false" class="inline-flex justify-center rounded-lg border border-slate-700 px-4 py-2 bg-slate-800 text-sm font-semibold text-slate-300 hover:bg-slate-700 focus:outline-none transition-colors">
                            Batal
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </AdminDashboardLayout>
</template>
