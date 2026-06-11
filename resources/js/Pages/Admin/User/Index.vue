<script setup>
import AdminDashboardLayout from '@/Layouts/AdminDashboardLayout.vue';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import Pagination from '@/Components/Pagination.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    users: Object,
    roles: Array,
    locations: Array,
    filters: Object,
});

// Filters
const search = ref(props.filters?.search || '');
const role_id = ref(props.filters?.role_id || '');
const location = ref(props.filters?.location || '');
const per_page = ref(props.filters?.per_page || '10');

const handleSearch = () => {
    router.get(route('admin.users.index'), {
        search: search.value,
        role_id: role_id.value,
        location: location.value,
        per_page: per_page.value,
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true
    });
};

watch([search, role_id, location, per_page], () => {
    handleSearch();
});

// Modals State
const isCreateModalOpen = ref(false);
const isEditModalOpen = ref(false);
const isResetPasswordModalOpen = ref(false);
const isImportModalOpen = ref(false);
const isDeleteModalOpen = ref(false);

const selectedUser = ref(null);

// Form Create
const createForm = useForm({
    name: '',
    nip: '',
    email: '',
    password: '',
    role_id: '',
    position: '',
    location: '',
    status_fit: true,
    avatar: null,
});

const submitCreate = () => {
    createForm.post(route('admin.users.store'), {
        onSuccess: () => {
            isCreateModalOpen.value = false;
            createForm.reset();
        }
    });
};

// Form Edit
const editForm = useForm({
    name: '',
    nip: '',
    email: '',
    role_id: '',
    position: '',
    location: '',
    status_fit: true,
    avatar: null,
    remove_avatar: false,
    _method: 'PUT' // For file uploads inside PUT/PATCH request simulated with POST
});

const openEditModal = (user) => {
    selectedUser.value = user;
    editForm.name = user.name;
    editForm.nip = user.nip;
    editForm.email = user.email;
    editForm.role_id = user.role_id;
    editForm.position = user.position || '';
    editForm.location = user.location || '';
    editForm.status_fit = !!user.status_fit;
    editForm.avatar = null;
    editForm.remove_avatar = false;
    isEditModalOpen.value = true;
};

const submitEdit = () => {
    // If uploading files (avatar), we must use POST with _method=PUT due to PHP limitations parsing multipart/form-data on PUT/PATCH
    editForm.post(route('admin.users.update', selectedUser.value.id), {
        onSuccess: () => {
            isEditModalOpen.value = false;
            editForm.reset();
        }
    });
};

// Reset Password
const resetForm = useForm({
    password: '',
    password_confirmation: '',
});

const openResetPasswordModal = (user) => {
    selectedUser.value = user;
    resetForm.reset();
    isResetPasswordModalOpen.value = true;
};

const submitResetPassword = () => {
    resetForm.post(route('admin.users.reset-password', selectedUser.value.id), {
        onSuccess: () => {
            isResetPasswordModalOpen.value = false;
            resetForm.reset();
        }
    });
};

// Import Excel
const importForm = useForm({
    file: null,
});

const submitImport = () => {
    importForm.post(route('admin.users.import'), {
        onSuccess: () => {
            isImportModalOpen.value = false;
            importForm.reset();
        }
    });
};

// Delete User
const openDeleteModal = (user) => {
    selectedUser.value = user;
    isDeleteModalOpen.value = true;
};

const submitDelete = () => {
    router.delete(route('admin.users.destroy', selectedUser.value.id), {
        onSuccess: () => {
            isDeleteModalOpen.value = false;
        }
    });
};
</script>

<template>
    <Head title="Manajemen User - Admin" />

    <AdminDashboardLayout>
        <template #header>
            <span>Manajemen User / Petugas</span>
        </template>

        <div class="space-y-6">
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between border-b border-slate-800 pb-4 gap-4">
                    <div>
                        <h3 class="font-bold text-lg text-slate-200">Daftar Pengguna & Petugas Pelabuhan</h3>
                        <p class="text-xs text-slate-400">Kelola akun, role, unit kerja pelabuhan, dan status kebugaran petugas.</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <button 
                            @click="isCreateModalOpen = true"
                            class="flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white font-semibold text-sm rounded-lg transition-colors duration-200 shadow-lg shadow-blue-900/30"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                            Tambah User
                        </button>
                        <a 
                            :href="route('admin.users.template')" 
                            class="flex items-center gap-2 px-4 py-2 bg-slate-700 hover:bg-slate-650 text-slate-200 hover:text-white font-semibold text-sm rounded-lg border border-slate-600 transition-colors duration-200"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m6.75 12-3-3m0 0-3 3m3-3v6m-1.5-15H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                            </svg>
                            Template Import
                        </a>
                        <a 
                            :href="route('admin.users.export', { search, role_id, location })" 
                            class="flex items-center gap-2 px-4 py-2 bg-amber-600 hover:bg-amber-500 text-white font-semibold text-sm rounded-lg transition-colors duration-200 shadow-lg shadow-amber-900/30"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                            </svg>
                            Export Excel
                        </a>
                        <button 
                            @click="isImportModalOpen = true" 
                            class="flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white font-semibold text-sm rounded-lg transition-colors duration-200 shadow-lg shadow-emerald-900/30"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" />
                            </svg>
                            Import Excel
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
                        <input v-model="search" type="text" placeholder="Cari nama, email, NIP..." class="w-full bg-slate-900 border border-slate-700 rounded-lg py-2 pl-10 px-4 text-sm text-slate-200 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition-colors duration-200" />
                    </div>
                    <div class="w-full md:w-48 relative">
                        <select v-model="role_id" class="w-full bg-slate-900 border border-slate-700 rounded-lg py-2 px-4 text-sm text-slate-200 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition-colors duration-200">
                            <option value="">Semua Role</option>
                            <option v-for="role in roles" :key="role.id" :value="role.id">{{ role.name }}</option>
                        </select>
                    </div>
                    <div class="w-full md:w-48 relative">
                        <select v-model="location" class="w-full bg-slate-900 border border-slate-700 rounded-lg py-2 px-4 text-sm text-slate-200 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition-colors duration-200">
                            <option value="">Semua Unit/Lokasi Kerja</option>
                            <option v-for="loc in locations" :key="loc" :value="loc">{{ loc }}</option>
                        </select>
                    </div>
                    <div class="w-full md:w-32 relative">
                        <select v-model="per_page" class="w-full bg-slate-900 border border-slate-700 rounded-lg py-2 px-4 text-sm text-slate-200 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition-colors duration-200">
                            <option value="10">10 Baris</option>
                            <option value="15">15 Baris</option>
                            <option value="25">25 Baris</option>
                            <option value="50">50 Baris</option>
                            <option value="100">100 Baris</option>
                        </select>
                    </div>
                </div>

                <!-- Data Table -->
                <div class="overflow-x-auto rounded-lg border border-slate-800">
                    <table class="min-w-full text-slate-300">
                        <thead>
                            <tr class="bg-slate-950 border-b border-slate-800 text-left text-xs font-bold uppercase tracking-wider text-slate-400">
                                <th class="py-3 px-4 text-center w-12">No</th>
                                <th class="py-3 px-4">Pengguna</th>
                                <th class="py-3 px-4">NIP / Jabatan</th>
                                <th class="py-3 px-4">Unit / Lokasi Kerja</th>
                                <th class="py-3 px-4 text-center">Status Fit</th>
                                <th class="py-3 px-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800 text-sm">
                            <tr v-for="(u, index) in users.data" :key="u.id" class="hover:bg-slate-850/50 transition-colors duration-150">
                                <td class="py-3 px-4 text-center font-medium text-slate-500">
                                    {{ users.from + index }}
                                </td>
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-3">
                                        <!-- Avatar rendering with marine worker SVG placeholder -->
                                        <img v-if="u.avatar" :src="`/storage/` + u.avatar" class="w-10 h-10 rounded-full object-cover border border-slate-700" alt="Avatar" />
                                        <div v-else class="w-10 h-10 text-amber-500 bg-slate-800 rounded-full flex items-center justify-center border border-slate-700" title="Default Port Avatar">
                                            <!-- SVG Port Worker with hard hat -->
                                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3a6 6 0 016 6v1H6V9a6 6 0 016-6z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 10h16v1H4z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 14a4 4 0 01-8 0v-2h8v2z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5.5 21a6.5 6.5 0 0113 0" />
                                            </svg>
                                        </div>
                                        <div>
                                            <div class="font-bold text-slate-100">{{ u.name }}</div>
                                            <div class="text-xs text-slate-400">{{ u.email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="font-medium text-slate-200">{{ u.nip }}</div>
                                    <div class="text-xs text-slate-400">{{ u.position || '-' }}</div>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="font-medium text-slate-300">{{ u.location || '-' }}</div>
                                    <div class="inline-flex mt-1 items-center px-2 py-0.5 rounded text-[10px] font-semibold tracking-wider uppercase bg-slate-800 text-slate-300 border border-slate-700">
                                        {{ u.role ? u.role.name : '-' }}
                                    </div>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <span class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full border"
                                          :class="{
                                              'bg-emerald-950/30 text-emerald-400 border-emerald-800/50': u.status_fit,
                                              'bg-rose-950/30 text-rose-400 border-rose-800/50': !u.status_fit
                                          }">
                                        {{ u.status_fit ? 'Fit' : 'Unfit' }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <div class="flex items-center justify-end gap-3">
                                        <button @click="openEditModal(u)" class="text-indigo-400 hover:text-indigo-300 transition-colors" title="Edit">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" />
                                            </svg>
                                        </button>
                                        <button @click="openResetPasswordModal(u)" class="text-amber-500 hover:text-amber-400 transition-colors" title="Reset Password">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m-9 9l-1.5-1.5m0 0l-3-3m3 3l3.5-3.5M19.5 8.25a6.75 6.75 0 00-13.5 0c0 1.258.347 2.435.953 3.444L3 18.75V21h2.25l7.056-7.056A6.71 6.71 0 0019.5 8.25z" />
                                            </svg>
                                        </button>
                                        <button @click="openDeleteModal(u)" class="text-rose-500 hover:text-rose-400 transition-colors" title="Hapus">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="users.data.length === 0">
                                <td colspan="6" class="py-8 text-center text-slate-500">
                                    Tidak ada data user yang ditemukan.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="flex items-center justify-between border-t border-slate-800 pt-4">
                    <div class="text-xs text-slate-400">
                        Menampilkan {{ users.from || 0 }} sampai {{ users.to || 0 }} dari {{ users.total }} pengguna.
                    </div>
                    <Pagination :links="users.links" />
                </div>
            </div>
        </div>

        <!-- Create User Modal -->
        <div v-if="isCreateModalOpen" class="fixed z-50 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm transition-opacity" @click="!createForm.processing && (isCreateModalOpen = false)"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-bottom bg-slate-900 border border-slate-700 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                    <form @submit.prevent="submitCreate">
                        <div class="px-6 pt-6 pb-6">
                            <h2 class="text-lg font-bold text-slate-200 mb-4">Tambah Pengguna Baru</h2>
                            <div class="space-y-4">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <InputLabel for="create_name" value="Nama Lengkap" class="text-slate-350" />
                                        <TextInput id="create_name" type="text" class="mt-1 block w-full bg-slate-950 border-slate-800 text-slate-250 focus:border-blue-500" v-model="createForm.name" required />
                                        <InputError :message="createForm.errors.name" class="mt-1" />
                                    </div>
                                    <div>
                                        <InputLabel for="create_nip" value="NIP / Nomor Induk Petugas" class="text-slate-350" />
                                        <TextInput id="create_nip" type="text" class="mt-1 block w-full bg-slate-950 border-slate-800 text-slate-250 focus:border-blue-500" v-model="createForm.nip" required />
                                        <InputError :message="createForm.errors.nip" class="mt-1" />
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <InputLabel for="create_email" value="Email" class="text-slate-350" />
                                        <TextInput id="create_email" type="email" class="mt-1 block w-full bg-slate-950 border-slate-800 text-slate-250 focus:border-blue-500" v-model="createForm.email" required />
                                        <InputError :message="createForm.errors.email" class="mt-1" />
                                    </div>
                                    <div>
                                        <InputLabel for="create_password" value="Password" class="text-slate-350" />
                                        <TextInput id="create_password" type="password" class="mt-1 block w-full bg-slate-950 border-slate-800 text-slate-250 focus:border-blue-500" v-model="createForm.password" required />
                                        <InputError :message="createForm.errors.password" class="mt-1" />
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <InputLabel for="create_role" value="Role Pengguna" class="text-slate-350" />
                                        <select id="create_role" class="mt-1 block w-full bg-slate-950 border-slate-800 rounded-md shadow-sm text-slate-250 focus:border-blue-500" v-model="createForm.role_id" required>
                                            <option value="">Pilih Role</option>
                                            <option v-for="role in roles" :key="role.id" :value="role.id">{{ role.name }}</option>
                                        </select>
                                        <InputError :message="createForm.errors.role_id" class="mt-1" />
                                    </div>
                                    <div>
                                        <InputLabel for="create_position" value="Jabatan / Posisi" class="text-slate-350" />
                                        <TextInput id="create_position" type="text" class="mt-1 block w-full bg-slate-950 border-slate-800 text-slate-250 focus:border-blue-500" v-model="createForm.position" />
                                        <InputError :message="createForm.errors.position" class="mt-1" />
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <InputLabel for="create_location" value="Unit / Lokasi Kerja" class="text-slate-350" />
                                        <TextInput id="create_location" type="text" placeholder="Contoh: Kantor Pusat, Cabang Samarinda" class="mt-1 block w-full bg-slate-950 border-slate-800 text-slate-250 focus:border-blue-500" v-model="createForm.location" />
                                        <InputError :message="createForm.errors.location" class="mt-1" />
                                    </div>
                                    <div>
                                        <InputLabel for="create_status_fit" value="Status Kebugaran" class="text-slate-350" />
                                        <select id="create_status_fit" class="mt-1 block w-full bg-slate-950 border-slate-800 rounded-md shadow-sm text-slate-250 focus:border-blue-500" v-model="createForm.status_fit">
                                            <option :value="true">Fit (Siap Kerja)</option>
                                            <option :value="false">Unfit (Istirahat / Sakit)</option>
                                        </select>
                                        <InputError :message="createForm.errors.status_fit" class="mt-1" />
                                    </div>
                                </div>

                                <div>
                                    <InputLabel for="create_avatar" value="Avatar (Foto Profil)" class="text-slate-350" />
                                    <input id="create_avatar" type="file" @input="createForm.avatar = $event.target.files[0]" class="mt-1 block w-full text-slate-400 text-sm file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-slate-800 file:text-slate-300 hover:file:bg-slate-700" />
                                    <InputError :message="createForm.errors.avatar" class="mt-1" />
                                </div>
                            </div>
                        </div>
                        <div class="bg-slate-800/50 px-6 py-4 flex flex-row-reverse gap-3 border-t border-slate-800">
                            <button type="submit" :disabled="createForm.processing" class="inline-flex justify-center items-center gap-2 rounded-lg border border-transparent px-5 py-2.5 bg-blue-600 text-sm font-semibold text-white hover:bg-blue-500 focus:outline-none transition-colors disabled:opacity-50 shadow-lg shadow-blue-900/20">
                                <svg v-if="createForm.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                {{ createForm.processing ? 'Menyimpan...' : 'Simpan User' }}
                            </button>
                            <button type="button" @click="isCreateModalOpen = false" :disabled="createForm.processing" class="inline-flex justify-center rounded-lg border border-slate-700 px-4 py-2 bg-slate-800 text-sm font-semibold text-slate-300 hover:bg-slate-700 focus:outline-none transition-colors disabled:opacity-50">
                                Batal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Edit User Modal -->
        <div v-if="isEditModalOpen" class="fixed z-50 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm transition-opacity" @click="!editForm.processing && (isEditModalOpen = false)"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-bottom bg-slate-900 border border-slate-700 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                    <form @submit.prevent="submitEdit">
                        <div class="px-6 pt-6 pb-6">
                            <h2 class="text-lg font-bold text-slate-200 mb-4">Edit Detail Pengguna</h2>
                            <div class="space-y-4">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <InputLabel for="edit_name" value="Nama Lengkap" class="text-slate-350" />
                                        <TextInput id="edit_name" type="text" class="mt-1 block w-full bg-slate-950 border-slate-800 text-slate-250 focus:border-amber-500" v-model="editForm.name" required />
                                        <InputError :message="editForm.errors.name" class="mt-1" />
                                    </div>
                                    <div>
                                        <InputLabel for="edit_nip" value="NIP / Nomor Induk Petugas" class="text-slate-350" />
                                        <TextInput id="edit_nip" type="text" class="mt-1 block w-full bg-slate-950 border-slate-800 text-slate-250 focus:border-amber-500" v-model="editForm.nip" required />
                                        <InputError :message="editForm.errors.nip" class="mt-1" />
                                    </div>
                                </div>

                                <div>
                                    <InputLabel for="edit_email" value="Email" class="text-slate-350" />
                                    <TextInput id="edit_email" type="email" class="mt-1 block w-full bg-slate-950 border-slate-800 text-slate-250 focus:border-amber-500" v-model="editForm.email" required />
                                    <InputError :message="editForm.errors.email" class="mt-1" />
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <InputLabel for="edit_role" value="Role Pengguna" class="text-slate-350" />
                                        <select id="edit_role" class="mt-1 block w-full bg-slate-950 border-slate-800 rounded-md shadow-sm text-slate-250 focus:border-amber-500" v-model="editForm.role_id" required>
                                            <option value="">Pilih Role</option>
                                            <option v-for="role in roles" :key="role.id" :value="role.id">{{ role.name }}</option>
                                        </select>
                                        <InputError :message="editForm.errors.role_id" class="mt-1" />
                                    </div>
                                    <div>
                                        <InputLabel for="edit_position" value="Jabatan / Posisi" class="text-slate-350" />
                                        <TextInput id="edit_position" type="text" class="mt-1 block w-full bg-slate-950 border-slate-800 text-slate-250 focus:border-amber-500" v-model="editForm.position" />
                                        <InputError :message="editForm.errors.position" class="mt-1" />
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <InputLabel for="edit_location" value="Unit / Lokasi Kerja" class="text-slate-350" />
                                        <TextInput id="edit_location" type="text" class="mt-1 block w-full bg-slate-950 border-slate-800 text-slate-250 focus:border-amber-500" v-model="editForm.location" />
                                        <InputError :message="editForm.errors.location" class="mt-1" />
                                    </div>
                                    <div>
                                        <InputLabel for="edit_status_fit" value="Status Kebugaran" class="text-slate-350" />
                                        <select id="edit_status_fit" class="mt-1 block w-full bg-slate-950 border-slate-800 rounded-md shadow-sm text-slate-250 focus:border-amber-500" v-model="editForm.status_fit">
                                            <option :value="true">Fit (Siap Kerja)</option>
                                            <option :value="false">Unfit (Istirahat / Sakit)</option>
                                        </select>
                                        <InputError :message="editForm.errors.status_fit" class="mt-1" />
                                    </div>
                                </div>

                                <div>
                                    <InputLabel for="edit_avatar" value="Ubah Avatar" class="text-slate-350" />
                                    <div class="mt-2 flex items-center gap-4">
                                        <img v-if="selectedUser?.avatar && !editForm.remove_avatar" :src="`/storage/` + selectedUser.avatar" class="w-12 h-12 rounded-full object-cover" alt="Current Avatar" />
                                        <div v-else class="w-12 h-12 text-amber-500 bg-slate-800 rounded-full flex items-center justify-center border border-slate-700">
                                            <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3a6 6 0 016 6v1H6V9a6 6 0 016-6z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 10h16v1H4z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 14a4 4 0 01-8 0v-2h8v2z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5.5 21a6.5 6.5 0 0113 0" />
                                            </svg>
                                        </div>
                                        <input id="edit_avatar" type="file" @input="editForm.avatar = $event.target.files[0]" class="block w-full text-slate-400 text-sm file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-slate-800 file:text-slate-300 hover:file:bg-slate-700" />
                                    </div>
                                    <div v-if="selectedUser?.avatar" class="mt-2 flex items-center gap-2">
                                        <input type="checkbox" id="remove_avatar" v-model="editForm.remove_avatar" class="rounded border-slate-700 bg-slate-950 text-amber-500 focus:ring-amber-500" />
                                        <label for="remove_avatar" class="text-xs text-slate-400 select-none cursor-pointer">Hapus avatar saat ini (kembalikan ke default)</label>
                                    </div>
                                    <InputError :message="editForm.errors.avatar" class="mt-1" />
                                </div>
                            </div>
                        </div>
                        <div class="bg-slate-800/50 px-6 py-4 flex flex-row-reverse gap-3 border-t border-slate-800">
                            <button type="submit" :disabled="editForm.processing" class="inline-flex justify-center items-center gap-2 rounded-lg border border-transparent px-5 py-2.5 bg-amber-600 text-sm font-semibold text-white hover:bg-amber-500 focus:outline-none transition-colors disabled:opacity-50 shadow-lg shadow-amber-900/20">
                                <svg v-if="editForm.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                {{ editForm.processing ? 'Memperbarui...' : 'Perbarui User' }}
                            </button>
                            <button type="button" @click="isEditModalOpen = false" :disabled="editForm.processing" class="inline-flex justify-center rounded-lg border border-slate-700 px-4 py-2 bg-slate-800 text-sm font-semibold text-slate-300 hover:bg-slate-700 focus:outline-none transition-colors disabled:opacity-50">
                                Batal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Reset Password Modal -->
        <div v-if="isResetPasswordModalOpen" class="fixed z-50 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm transition-opacity" @click="!resetForm.processing && (isResetPasswordModalOpen = false)"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-bottom bg-slate-900 border border-slate-700 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <form @submit.prevent="submitResetPassword">
                        <div class="px-6 pt-6 pb-6">
                            <h2 class="text-lg font-bold text-slate-200 mb-1">Reset Password</h2>
                            <p class="text-xs text-slate-400 mb-4">Setel ulang sandi baru untuk akun <strong>{{ selectedUser?.name }}</strong>.</p>
                            <div class="space-y-4">
                                <div>
                                    <InputLabel for="reset_password" value="Password Baru" class="text-slate-350" />
                                    <TextInput id="reset_password" type="password" class="mt-1 block w-full bg-slate-950 border-slate-800 text-slate-250 focus:border-amber-500" v-model="resetForm.password" required />
                                    <InputError :message="resetForm.errors.password" class="mt-1" />
                                </div>

                                <div>
                                    <InputLabel for="reset_password_confirmation" value="Konfirmasi Password Baru" class="text-slate-350" />
                                    <TextInput id="reset_password_confirmation" type="password" class="mt-1 block w-full bg-slate-950 border-slate-800 text-slate-250 focus:border-amber-500" v-model="resetForm.password_confirmation" required />
                                    <InputError :message="resetForm.errors.password_confirmation" class="mt-1" />
                                </div>
                            </div>
                        </div>
                        <div class="bg-slate-800/50 px-6 py-4 flex flex-row-reverse gap-3 border-t border-slate-800">
                            <button type="submit" :disabled="resetForm.processing" class="inline-flex justify-center items-center gap-2 rounded-lg border border-transparent px-5 py-2.5 bg-amber-600 text-sm font-semibold text-white hover:bg-amber-500 focus:outline-none transition-colors disabled:opacity-50 shadow-lg shadow-amber-900/20">
                                <svg v-if="resetForm.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                {{ resetForm.processing ? 'Menyimpan...' : 'Simpan Password Baru' }}
                            </button>
                            <button type="button" @click="isResetPasswordModalOpen = false" :disabled="resetForm.processing" class="inline-flex justify-center rounded-lg border border-slate-700 px-4 py-2 bg-slate-800 text-sm font-semibold text-slate-300 hover:bg-slate-700 focus:outline-none transition-colors disabled:opacity-50">
                                Batal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Import Modal -->
        <div v-if="isImportModalOpen" class="fixed z-50 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm transition-opacity" @click="!importForm.processing && (isImportModalOpen = false)"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-bottom bg-slate-900 border border-slate-700 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <form @submit.prevent="submitImport">
                        <div class="px-6 pt-6 pb-6">
                            <h3 class="text-xl font-bold text-slate-200" id="modal-title">Import Data Pengguna (Excel)</h3>
                            <div class="mt-2">
                                <p class="text-sm text-slate-400">Unggah file Excel (.xlsx / .csv) sesuai template untuk mendaftarkan banyak petugas sekaligus.</p>
                                <div class="mt-6">
                                    <input type="file" @change="e => importForm.file = e.target.files[0]" accept=".csv,.xlsx,.xls" :disabled="importForm.processing" class="block w-full text-sm text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-600 file:text-white hover:file:bg-blue-500 cursor-pointer transition-colors disabled:opacity-50 disabled:cursor-not-allowed" required />
                                </div>
                                <div v-if="importForm.errors.file" class="text-rose-500 text-sm mt-2">{{ importForm.errors.file }}</div>

                                <!-- Progress & Processing States -->
                                <div v-if="importForm.progress" class="w-full bg-slate-800 rounded-full h-2.5 mt-4">
                                    <div class="bg-blue-600 h-2.5 rounded-full transition-all duration-150" :style="{ width: importForm.progress.percentage + '%' }"></div>
                                    <p class="text-xs text-slate-400 mt-1.5 text-right">Mengunggah: {{ importForm.progress.percentage }}%</p>
                                </div>
                                <div v-if="importForm.processing" class="flex items-center justify-center gap-3 mt-4 p-3 bg-slate-800/40 rounded-xl border border-slate-700/50 animate-pulse">
                                    <svg class="animate-spin h-5 w-5 text-emerald-500" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span class="text-sm text-slate-300 font-medium">Sedang memproses & mengimpor data user...</span>
                                </div>
                            </div>
                        </div>
                        <div class="bg-slate-800/50 px-6 py-4 flex flex-row-reverse gap-3 border-t border-slate-800">
                            <button type="submit" :disabled="importForm.processing" class="inline-flex justify-center rounded-lg border border-transparent px-4 py-2 bg-blue-600 text-sm font-semibold text-white hover:bg-blue-500 focus:outline-none transition-colors disabled:opacity-50">
                                {{ importForm.processing ? 'Memproses...' : 'Import Sekarang' }}
                            </button>
                            <button type="button" @click="isImportModalOpen = false" :disabled="importForm.processing" class="inline-flex justify-center rounded-lg border border-slate-700 px-4 py-2 bg-slate-800 text-sm font-semibold text-slate-300 hover:bg-slate-700 focus:outline-none transition-colors disabled:opacity-50">
                                Batal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Delete User Confirmation Modal -->
        <div v-if="isDeleteModalOpen" class="fixed z-50 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm transition-opacity" @click="isDeleteModalOpen = false"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-bottom bg-slate-900 border border-slate-700 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="px-6 pt-6 pb-6">
                        <h2 class="text-lg font-bold text-slate-200 mb-2">Hapus Akun Pengguna?</h2>
                        <p class="text-sm text-slate-400">
                            Apakah Anda yakin ingin menghapus akun <strong>{{ selectedUser?.name }}</strong>? Tindakan ini tidak dapat dibatalkan dan semua riwayat terkait user tersebut mungkin akan terpengaruh.
                        </p>
                    </div>
                    <div class="bg-slate-800/50 px-6 py-4 flex flex-row-reverse gap-3 border-t border-slate-800">
                        <DangerButton type="button" @click="submitDelete">
                            Hapus Akun
                        </DangerButton>
                        <button type="button" @click="isDeleteModalOpen = false" class="inline-flex justify-center rounded-lg border border-slate-700 px-4 py-2 bg-slate-800 text-sm font-semibold text-slate-300 hover:bg-slate-700 focus:outline-none transition-colors disabled:opacity-50">
                            Batal
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AdminDashboardLayout>
</template>
