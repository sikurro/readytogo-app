<script setup>
import AdminDashboardLayout from '@/Layouts/AdminDashboardLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    quiz: Object,
    topics: Array,
});

const form = useForm({
    title: props.quiz.title,
    theme: props.quiz.theme,
    topic_id: props.quiz.topic_id || '',
    duration_minutes: props.quiz.duration_minutes,
    is_active: !!props.quiz.is_active,
    is_daily_quiz: !!props.quiz.is_daily_quiz,
    daily_question_limit: props.quiz.daily_question_limit || 10,
});

const submit = () => {
    form.put(route('admin.quizzes.update', props.quiz.id));
};
</script>

<template>
    <AdminDashboardLayout>
        <Head title="Edit Kuis" />

        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Kuis</h2>
                <Link :href="route('admin.quizzes.index')" class="text-sm text-gray-600 hover:text-gray-900">
                    &larr; Kembali
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <form @submit.prevent="submit" class="space-y-6">
                        
                        <div class="p-4 bg-purple-50 rounded-lg border border-purple-100 flex items-start gap-4">
                            <div class="pt-1">
                                <input v-model="form.is_daily_quiz" id="is_daily_quiz" type="checkbox" class="h-5 w-5 rounded border-gray-300 text-purple-600 focus:ring-purple-500" />
                            </div>
                            <div>
                                <label for="is_daily_quiz" class="block text-base font-bold text-purple-900">Jadikan sebagai Kuis Harian</label>
                                <p class="text-sm text-purple-700 mt-1">Jika dicentang, kuis ini akan menarik soal secara otomatis dan acak dari Master Bank Soal setiap kali dimainkan oleh Petugas. Anda tidak perlu memilih soal secara manual.</p>
                            </div>
                        </div>

                        <div v-if="form.is_daily_quiz" class="p-4 bg-white border border-purple-200 rounded-lg shadow-sm">
                            <label class="block text-sm font-medium text-gray-700">Batas Jumlah Soal Acak yang Dimunculkan</label>
                            <input v-model="form.daily_question_limit" type="number" min="1" class="mt-1 block w-48 rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500 sm:text-sm" required />
                            <p class="text-xs text-gray-500 mt-1">Contoh: 10. Maka sistem akan mengambil 10 soal acak dari Bank Soal.</p>
                            <div v-if="form.errors.daily_question_limit" class="text-red-500 text-sm mt-1">{{ form.errors.daily_question_limit }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Judul Kuis</label>
                            <input v-model="form.title" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" required />
                            <div v-if="form.errors.title" class="text-red-500 text-sm mt-1">{{ form.errors.title }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Tema</label>
                            <input v-model="form.theme" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" required />
                            <div v-if="form.errors.theme" class="text-red-500 text-sm mt-1">{{ form.errors.theme }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Topik (Opsional)</label>
                            <select v-model="form.topic_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                <option value="">Pilih Topik</option>
                                <option v-for="topic in topics" :key="topic.id" :value="topic.id">{{ topic.name }}</option>
                            </select>
                            <div v-if="form.errors.topic_id" class="text-red-500 text-sm mt-1">{{ form.errors.topic_id }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Durasi Pengerjaan (Menit)</label>
                            <input v-model="form.duration_minutes" type="number" min="1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" required />
                            <div v-if="form.errors.duration_minutes" class="text-red-500 text-sm mt-1">{{ form.errors.duration_minutes }}</div>
                        </div>

                        <div class="flex items-center">
                            <input v-model="form.is_active" id="is_active" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
                            <label for="is_active" class="ml-2 block text-sm text-gray-900">Aktif (Bisa dikerjakan petugas)</label>
                        </div>

                        <div class="flex justify-end gap-3 pt-4">
                            <Link :href="route('admin.quizzes.index')" class="px-4 py-2 bg-gray-100 text-gray-700 rounded hover:bg-gray-200">Batal</Link>
                            <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 disabled:opacity-50">
                                Simpan Perubahan
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </AdminDashboardLayout>
</template>
