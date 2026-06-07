<script setup>
import AdminDashboardLayout from '@/Layouts/AdminDashboardLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    questions: Object,
    categories: Array,
    filters: Object,
});

const category_id = ref(props.filters?.category_id || '');
const risk_level = ref(props.filters?.risk_level || '');

const handleSearch = () => {
    router.get(route('admin.questions.index'), {
        category_id: category_id.value,
        risk_level: risk_level.value,
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true
    });
};

// Auto search on filter change
watch([category_id, risk_level], () => {
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

const deleteQuestion = (id) => {
    if(confirm('Apakah Anda yakin ingin menghapus soal ini dari Master Bank? Ini akan menghapusnya dari semua kuis yang terhubung.')) {
        useForm({}).delete(route('admin.questions.destroy', id));
    }
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
                    <div class="flex-1 relative">
                        <select v-model="category_id" class="w-full bg-slate-900 border border-slate-700 rounded-lg py-2 px-4 text-sm text-slate-200 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition-colors duration-200">
                            <option value="">Semua Kategori</option>
                            <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                        </select>
                    </div>
                    <div class="flex-1 relative">
                        <select v-model="risk_level" class="w-full bg-slate-900 border border-slate-700 rounded-lg py-2 px-4 text-sm text-slate-200 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition-colors duration-200">
                            <option value="">Semua Level Resiko</option>
                            <option value="Low">Low</option>
                            <option value="Medium">Medium</option>
                            <option value="High">High</option>
                        </select>
                    </div>
                </div>

                <!-- Data Table -->
                <div class="overflow-x-auto">
                    <table class="min-w-full text-slate-300">
                        <thead>
                            <tr class="border-b border-slate-800 text-left text-xs font-bold uppercase tracking-wider text-slate-400">
                                <th class="py-3 px-4">Pertanyaan</th>
                                <th class="py-3 px-4">Kategori</th>
                                <th class="py-3 px-4 text-center">Resiko</th>
                                <th class="py-3 px-4 text-center">Dipakai (Kuis)</th>
                                <th class="py-3 px-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800 text-sm">
                            <tr v-for="q in questions.data" :key="q.id" class="hover:bg-slate-800/30 transition-colors duration-150">
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
                                <td class="py-3 px-4 text-right space-x-3">
                                    <Link :href="route('admin.questions.edit', q.id)" class="text-indigo-400 hover:text-indigo-300 font-medium transition-colors">Edit</Link>
                                    <button @click="deleteQuestion(q.id)" class="text-rose-500 hover:text-rose-400 font-medium transition-colors">Hapus</button>
                                </td>
                            </tr>
                            <tr v-if="questions.data.length === 0">
                                <td colspan="5" class="py-8 text-center text-slate-500">Belum ada soal di Master Bank.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Upload Modal -->
        <div v-if="isUploadModalOpen" class="fixed z-50 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm transition-opacity" @click="isUploadModalOpen = false"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-bottom bg-slate-900 border border-slate-700 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <form @submit.prevent="submitUpload">
                        <div class="px-6 pt-6 pb-6">
                            <h3 class="text-xl font-bold text-slate-200" id="modal-title">Upload Bank Soal</h3>
                            <div class="mt-2">
                                <p class="text-sm text-slate-400">Unggah file template (Excel/CSV) untuk menambahkan banyak soal sekaligus ke Master Bank Soal.</p>
                                <div class="mt-6">
                                    <input type="file" @change="e => uploadForm.file = e.target.files[0]" accept=".csv,.xlsx,.xls" required class="block w-full text-sm text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-600 file:text-white hover:file:bg-blue-500 cursor-pointer transition-colors" />
                                </div>
                                <div v-if="uploadForm.errors.file" class="text-rose-500 text-sm mt-2">{{ uploadForm.errors.file }}</div>
                            </div>
                        </div>
                        <div class="bg-slate-800/50 px-6 py-4 flex flex-row-reverse gap-3 border-t border-slate-800">
                            <button type="submit" :disabled="uploadForm.processing" class="inline-flex justify-center rounded-lg border border-transparent px-4 py-2 bg-blue-600 text-sm font-semibold text-white hover:bg-blue-500 focus:outline-none transition-colors disabled:opacity-50">
                                Upload & Proses
                            </button>
                            <button type="button" @click="isUploadModalOpen = false" class="inline-flex justify-center rounded-lg border border-slate-700 px-4 py-2 bg-slate-800 text-sm font-semibold text-slate-300 hover:bg-slate-700 focus:outline-none transition-colors">
                                Batal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AdminDashboardLayout>
</template>
