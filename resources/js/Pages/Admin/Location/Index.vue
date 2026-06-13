<script setup>
import AdminDashboardLayout from '@/Layouts/AdminDashboardLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import Pagination from '@/Components/Pagination.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    locations: Object,
    filters: Object,
});

const search = ref(props.filters?.search || '');

// State loading saat request filter sedang berjalan
const isLoading = ref(false);

const handleSearch = () => {
    isLoading.value = true;
    router.get(route('admin.locations.index'), {
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
const selectedLocation = ref(null);

const createForm = useForm({
    name: '',
});

const submitCreate = () => {
    createForm.post(route('admin.locations.store'), {
        onSuccess: () => {
            isCreateModalOpen.value = false;
            createForm.reset();
        }
    });
};

const editForm = useForm({
    name: '',
});

const openEditModal = (location) => {
    selectedLocation.value = location;
    editForm.name = location.name;
    isEditModalOpen.value = true;
};

const submitEdit = () => {
    editForm.put(route('admin.locations.update', selectedLocation.value.id), {
        onSuccess: () => {
            isEditModalOpen.value = false;
            editForm.reset();
        }
    });
};

const openDeleteModal = (location) => {
    selectedLocation.value = location;
    isDeleteModalOpen.value = true;
};

const submitDelete = () => {
    router.delete(route('admin.locations.destroy', selectedLocation.value.id), {
        onSuccess: () => {
            isDeleteModalOpen.value = false;
        }
    });
};
</script>

<template>
    <Head title="Master Lokasi / Unit Kerja - Admin" />

    <AdminDashboardLayout>
        <template #header>
            <span>Data Master / Lokasi & Unit Kerja</span>
        </template>

        <div class="space-y-6">
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between border-b border-slate-800 pb-4 gap-4">
                    <div>
                        <h3 class="font-bold text-lg text-slate-200">Master Lokasi / Unit Kerja</h3>
                        <p class="text-xs text-slate-400">Kelola data master lokasi dan unit kerja pelabuhan.</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <button 
                            @click="isCreateModalOpen = true"
                            class="flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white font-semibold text-sm rounded-lg transition-colors duration-200 shadow-lg shadow-blue-900/30"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                            Tambah Lokasi
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
                        <input v-model="search" type="text" placeholder="Cari nama lokasi..." class="w-full bg-slate-900 border border-slate-700 rounded-lg py-2 pl-10 px-4 text-sm text-slate-200 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition-colors duration-200" />
                    </div>
                </div>

                <div class="overflow-x-auto rounded-lg border border-slate-800 relative transition-opacity duration-200" :class="{ 'opacity-50 pointer-events-none': isLoading }">
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
                            <tr class="bg-slate-950 border-b border-slate-800 text-left text-xs font-bold uppercase tracking-wider text-slate-400">
                                <th class="py-3 px-4 text-center w-12">No</th>
                                <th class="py-3 px-4">Nama Lokasi / Unit Kerja</th>
                                <th class="py-3 px-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800 text-sm">
                            <tr v-for="(loc, index) in locations.data" :key="loc.id" class="hover:bg-slate-850/50 transition-colors duration-150">
                                <td class="py-3 px-4 text-center font-medium text-slate-500">
                                    {{ locations.from + index }}
                                </td>
                                <td class="py-3 px-4">
                                    <div class="font-bold text-slate-100">{{ loc.name }}</div>
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <div class="flex items-center justify-end gap-3">
                                        <button @click="openEditModal(loc)" class="text-indigo-400 hover:text-indigo-300 transition-colors" title="Edit">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" />
                                            </svg>
                                        </button>
                                        <button @click="openDeleteModal(loc)" class="text-rose-500 hover:text-rose-400 transition-colors" title="Hapus">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="locations.data.length === 0">
                                <td colspan="3" class="py-8 text-center text-slate-500">
                                    Tidak ada data lokasi yang ditemukan.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="flex items-center justify-between border-t border-slate-800 pt-4">
                    <div class="text-xs text-slate-400">
                        Menampilkan {{ locations.from || 0 }} sampai {{ locations.to || 0 }} dari {{ locations.total }} lokasi.
                    </div>
                    <Pagination :links="locations.links" />
                </div>
            </div>
        </div>

        <!-- Create Modal -->
        <div v-if="isCreateModalOpen" class="fixed z-50 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm transition-opacity" @click="!createForm.processing && (isCreateModalOpen = false)"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-bottom bg-slate-900 border border-slate-700 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full">
                    <form @submit.prevent="submitCreate">
                        <div class="px-6 pt-6 pb-6">
                            <h2 class="text-lg font-bold text-slate-200 mb-4">Tambah Lokasi Baru</h2>
                            <div class="space-y-4">
                                <div>
                                    <InputLabel for="create_name" value="Nama Lokasi / Unit Kerja" class="text-slate-350" />
                                    <TextInput id="create_name" type="text" class="mt-1 block w-full bg-slate-950 border-slate-800 text-slate-250 focus:border-blue-500" v-model="createForm.name" required />
                                    <InputError :message="createForm.errors.name" class="mt-1" />
                                </div>
                            </div>
                        </div>
                        <div class="bg-slate-800/50 px-6 py-4 flex flex-row-reverse gap-3 border-t border-slate-800">
                            <button type="submit" :disabled="createForm.processing" class="inline-flex justify-center items-center gap-2 rounded-lg border border-transparent px-5 py-2.5 bg-blue-600 text-sm font-semibold text-white hover:bg-blue-500 focus:outline-none transition-colors disabled:opacity-50 shadow-lg shadow-blue-900/20">
                                {{ createForm.processing ? 'Menyimpan...' : 'Simpan Lokasi' }}
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
                <div class="inline-block align-bottom bg-slate-900 border border-slate-700 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full">
                    <form @submit.prevent="submitEdit">
                        <div class="px-6 pt-6 pb-6">
                            <h2 class="text-lg font-bold text-slate-200 mb-4">Edit Lokasi</h2>
                            <div class="space-y-4">
                                <div>
                                    <InputLabel for="edit_name" value="Nama Lokasi / Unit Kerja" class="text-slate-350" />
                                    <TextInput id="edit_name" type="text" class="mt-1 block w-full bg-slate-950 border-slate-800 text-slate-250 focus:border-amber-500" v-model="editForm.name" required />
                                    <InputError :message="editForm.errors.name" class="mt-1" />
                                </div>
                            </div>
                        </div>
                        <div class="bg-slate-800/50 px-6 py-4 flex flex-row-reverse gap-3 border-t border-slate-800">
                            <button type="submit" :disabled="editForm.processing" class="inline-flex justify-center items-center gap-2 rounded-lg border border-transparent px-5 py-2.5 bg-amber-600 text-sm font-semibold text-white hover:bg-amber-500 focus:outline-none transition-colors disabled:opacity-50 shadow-lg shadow-amber-900/20">
                                {{ editForm.processing ? 'Memperbarui...' : 'Perbarui Lokasi' }}
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
                        <h2 class="text-lg font-bold text-slate-200 mb-2 text-center">Hapus Lokasi</h2>
                        <p class="text-sm text-slate-400 text-center">Apakah Anda yakin ingin menghapus lokasi <strong>{{ selectedLocation?.name }}</strong>? User yang memiliki lokasi ini akan diset ke kosong (null) dan tidak akan ikut terhapus.</p>
                    </div>
                    <div class="bg-slate-800/50 px-6 py-4 flex flex-row-reverse gap-3 border-t border-slate-800">
                        <button @click="submitDelete" class="inline-flex justify-center items-center gap-2 rounded-lg border border-transparent px-5 py-2.5 bg-rose-600 text-sm font-semibold text-white hover:bg-rose-500 focus:outline-none transition-colors shadow-lg shadow-rose-900/20">
                            Ya, Hapus Lokasi
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
