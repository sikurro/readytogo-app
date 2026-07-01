<script setup>
import AdminDashboardLayout from '@/Layouts/AdminDashboardLayout.vue';
import MobileAppLayout from '@/Layouts/MobileAppLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const page = usePage();
const user = computed(() => page.props.auth.user);
const layoutComponent = computed(() => {
    return user.value?.role_id === 1 ? AdminDashboardLayout : MobileAppLayout;
});
</script>

<template>
    <Head title="Profil Akun - Ready To GO" />

    <component :is="layoutComponent">
        <template #header>
            Profil Akun
        </template>

        <div class="space-y-6">
            <!-- Top Banner Card -->
            <div class="bg-gradient-to-br from-slate-900 to-slate-800 border border-slate-800 rounded-2xl p-6 shadow-xl flex flex-col sm:flex-row items-center gap-5">
                <div class="h-16 w-16 rounded-2xl bg-gradient-to-tr from-amber-500 to-orange-500 flex items-center justify-center font-black text-slate-950 text-2xl shadow-lg shadow-amber-500/20 flex-shrink-0 uppercase">
                    {{ user?.name?.charAt(0) || 'U' }}
                </div>
                <div class="text-center sm:text-left flex-1 min-w-0">
                    <div class="flex flex-col sm:flex-row sm:items-center gap-2">
                        <h3 class="text-lg font-bold text-slate-100 truncate">{{ user?.name }}</h3>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-500/10 text-amber-500 border border-amber-500/20 w-fit mx-auto sm:mx-0 uppercase tracking-wider">
                            {{ user?.role_id === 1 ? 'Administrator' : 'Petugas Lapangan' }}
                        </span>
                    </div>
                    <p class="text-xs text-slate-400 mt-1">NIP: <span class="font-semibold text-slate-300">{{ user?.nip || '-' }}</span> | Jabatan: <span class="font-semibold text-slate-300">{{ user?.position || '-' }}</span></p>
                </div>
            </div>

            <div class="p-6 bg-slate-900/90 border border-slate-800 rounded-2xl shadow-xl text-slate-100">
                <UpdateProfileInformationForm
                    :must-verify-email="mustVerifyEmail"
                    :status="status"
                    class="max-w-xl"
                />
            </div>

            <div class="p-6 bg-slate-900/90 border border-slate-800 rounded-2xl shadow-xl text-slate-100">
                <UpdatePasswordForm class="max-w-xl" />
            </div>

            <div class="p-6 bg-slate-900/90 border border-slate-800 rounded-2xl shadow-xl text-slate-100">
                <DeleteUserForm class="max-w-xl" />
            </div>
        </div>
    </component>
</template>
