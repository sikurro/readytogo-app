<script setup>
import AdminDashboardLayout from '@/Layouts/AdminDashboardLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    quiz: Object,
    bankQuestions: Object,
    attachedQuestionIds: Array,
    categories: Array,
    filters: Object,
});

const searchForm = useForm({
    category_id: props.filters.category_id || '',
    risk_level: props.filters.risk_level || '',
});

const handleSearch = () => {
    searchForm.get(route('admin.quizzes.show', props.quiz.id), {
        preserveState: true,
        preserveScroll: true,
    });
};

watch(() => [searchForm.category_id, searchForm.risk_level], () => {
    handleSearch();
});

const toggleQuestion = (questionId, isAttached) => {
    if (isAttached) {
        // Detach
        useForm({ question_id: questionId }).post(route('admin.quizzes.detach_question', props.quiz.id), {
            preserveScroll: true
        });
    } else {
        // Attach
        useForm({ question_id: questionId }).post(route('admin.quizzes.attach_question', props.quiz.id), {
            preserveScroll: true
        });
    }
};
</script>

<template>
    <AdminDashboardLayout>
        <Head :title="`Kelola Soal - ${quiz.title}`" />

        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">Pilih Soal untuk Kuis Event</h2>
                    <p class="text-sm text-gray-500 mt-1">Kuis: {{ quiz.title }} ({{ attachedQuestionIds.length }} Soal Terpilih)</p>
                </div>
                <Link :href="route('admin.quizzes.index')" class="text-sm text-gray-600 hover:text-gray-900">
                    &larr; Kembali ke Daftar Kuis
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-blue-700">
                                Centang kotak di kolom aksi untuk menambahkan soal dari Master Bank Soal ke dalam Kuis Event ini.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="bg-white p-4 rounded-lg shadow-sm mb-6 flex gap-4">
                    <div class="flex-1">
                        <label class="block text-xs font-medium text-gray-700 mb-1">Filter Kategori</label>
                        <select v-model="searchForm.category_id" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                            <option value="">Semua Kategori</option>
                            <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                        </select>
                    </div>
                    <div class="flex-1">
                        <label class="block text-xs font-medium text-gray-700 mb-1">Level Resiko</label>
                        <select v-model="searchForm.risk_level" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                            <option value="">Semua Level</option>
                            <option value="Low">Low</option>
                            <option value="Medium">Medium</option>
                            <option value="High">High</option>
                        </select>
                    </div>
                </div>

                <!-- Data Table -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi (Pilih)</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pertanyaan</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kategori</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Resiko</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="q in bankQuestions.data" :key="q.id" :class="attachedQuestionIds.includes(q.id) ? 'bg-indigo-50' : ''">
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <input 
                                            type="checkbox" 
                                            :checked="attachedQuestionIds.includes(q.id)"
                                            @change="toggleQuestion(q.id, attachedQuestionIds.includes(q.id))"
                                            class="h-5 w-5 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded cursor-pointer"
                                        />
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900 line-clamp-2 max-w-md">{{ q.question_text }}</div>
                                        <span v-if="q.question_image" class="inline-flex mt-1 items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                            Ada Gambar
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        <div class="flex flex-wrap gap-1">
                                            <span v-for="cat in q.categories" :key="cat.id" class="px-2 py-1 bg-gray-100 border rounded text-xs">{{ cat.name }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full"
                                              :class="{
                                                  'bg-green-100 text-green-800': q.risk_level === 'Low',
                                                  'bg-yellow-100 text-yellow-800': q.risk_level === 'Medium',
                                                  'bg-red-100 text-red-800': q.risk_level === 'High'
                                              }">
                                            {{ q.risk_level }}
                                        </span>
                                    </td>
                                </tr>
                                <tr v-if="bankQuestions.data.length === 0">
                                    <td colspan="4" class="px-6 py-4 text-center text-gray-500">Belum ada soal di Master Bank yang sesuai filter.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AdminDashboardLayout>
</template>
