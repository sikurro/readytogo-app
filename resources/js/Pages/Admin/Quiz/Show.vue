<script setup>
import AdminDashboardLayout from '@/Layouts/AdminDashboardLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    quiz: Object,
    bankQuestions: Object,
    attachedQuestionIds: Array,
    categories: Array,
    filters: Object,
});

const category_id = ref(props.filters.category_id || '');
const risk_level = ref(props.filters.risk_level || '');

const handleSearch = () => {
    // Gunakan router langsung untuk performa pencarian/filter
    import('@inertiajs/vue3').then(({ router }) => {
        router.get(route('admin.quizzes.show', props.quiz.id), {
            category_id: category_id.value,
            risk_level: risk_level.value,
        }, {
            preserveState: true,
            preserveScroll: true,
            replace: true
        });
    });
};

watch([category_id, risk_level], () => {
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
            <span>Pilih Soal untuk Kuis Event</span>
        </template>

        <div class="space-y-6">
            <!-- Header Panel -->
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h3 class="text-lg font-bold text-slate-200">Kuis: {{ quiz.title }}</h3>
                    <p class="text-sm text-slate-400 mt-1">Status Kuis Event ini memiliki <span class="font-bold text-indigo-400">{{ attachedQuestionIds.length }}</span> Soal Terpilih</p>
                </div>
                <Link 
                    :href="route('admin.quizzes.index')" 
                    class="flex items-center gap-2 px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-700 rounded-lg text-sm font-medium transition-colors"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                    </svg>
                    Kembali ke Daftar Kuis
                </Link>
            </div>

            <!-- Info Alert -->
            <div class="bg-blue-950/20 border border-blue-900/40 p-4 rounded-xl flex items-start gap-3">
                <div class="pt-0.5 text-blue-400">
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 111.085 1.086L12 13.5m0-6H12v.008h-.008V7.5zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0zM12 21a9 9 0 100-18 9 9 0 000 18z" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-slate-300">
                        Centang kotak di kolom paling kiri untuk menautkan/memasukkan soal dari Master Bank Soal ke dalam Kuis Event ini. Pengubahan akan disimpan secara otomatis saat dicentang.
                    </p>
                </div>
            </div>

            <!-- Filters -->
            <div class="flex flex-col md:flex-row md:items-center gap-4 bg-slate-800/40 p-4 rounded-xl border border-slate-800">
                <div class="flex-1 relative">
                    <select v-model="category_id" class="w-full bg-slate-900 border border-slate-700 rounded-lg py-2.5 px-4 text-sm text-slate-200 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition-colors duration-200">
                        <option value="">Semua Kategori</option>
                        <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                    </select>
                </div>
                <div class="flex-1 relative">
                    <select v-model="risk_level" class="w-full bg-slate-900 border border-slate-700 rounded-lg py-2.5 px-4 text-sm text-slate-200 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition-colors duration-200">
                        <option value="">Semua Level Resiko</option>
                        <option value="Low">Low</option>
                        <option value="Medium">Medium</option>
                        <option value="High">High</option>
                    </select>
                </div>
            </div>

            <!-- Data Table -->
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl space-y-4">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-slate-300">
                        <thead>
                            <tr class="border-b border-slate-800 text-left text-xs font-bold uppercase tracking-wider text-slate-400">
                                <th class="py-3 px-4 text-center w-24">Pilih</th>
                                <th class="py-3 px-4">Pertanyaan</th>
                                <th class="py-3 px-4">Kategori</th>
                                <th class="py-3 px-4 text-center w-28">Resiko</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800 text-sm">
                            <tr 
                                v-for="q in bankQuestions.data" 
                                :key="q.id" 
                                class="transition-colors duration-150"
                                :class="attachedQuestionIds.includes(q.id) 
                                    ? 'bg-indigo-950/20 hover:bg-indigo-950/35 border-l-2 border-indigo-500' 
                                    : 'hover:bg-slate-800/30'"
                            >
                                <td class="py-4 px-4 text-center whitespace-nowrap">
                                    <input 
                                        type="checkbox" 
                                        :checked="attachedQuestionIds.includes(q.id)"
                                        @change="toggleQuestion(q.id, attachedQuestionIds.includes(q.id))"
                                        class="h-5 w-5 text-indigo-600 focus:ring-indigo-500 bg-slate-950 border-slate-700 rounded cursor-pointer focus:ring-offset-slate-900"
                                    />
                                </td>
                                <td class="py-4 px-4">
                                    <div class="font-medium text-slate-200 line-clamp-2 max-w-xl">{{ q.question_text }}</div>
                                    <span v-if="q.question_image" class="inline-flex mt-1.5 items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-900/30 text-blue-400 border border-blue-800/50">
                                        Ada Gambar
                                    </span>
                                </td>
                                <td class="py-4 px-4">
                                    <div class="flex flex-wrap gap-1">
                                        <span v-for="cat in q.categories" :key="cat.id" class="px-2 py-1 bg-slate-800 border border-slate-700 rounded text-xs text-slate-300">{{ cat.name }}</span>
                                    </div>
                                </td>
                                <td class="py-4 px-4 text-center whitespace-nowrap">
                                    <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full border"
                                          :class="{
                                              'bg-emerald-900/30 text-emerald-400 border-emerald-800/50': q.risk_level === 'Low',
                                              'bg-amber-900/30 text-amber-400 border-amber-800/50': q.risk_level === 'Medium',
                                              'bg-rose-900/30 text-rose-400 border-rose-800/50': q.risk_level === 'High'
                                          }">
                                        {{ q.risk_level }}
                                    </span>
                                </td>
                            </tr>
                            <tr v-if="bankQuestions.data.length === 0">
                                <td colspan="4" class="py-8 text-center text-slate-500">Belum ada soal di Master Bank yang sesuai filter.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Section -->
                <div class="mt-6 flex flex-col sm:flex-row justify-between items-center gap-4 border-t border-slate-800 pt-6" v-if="bankQuestions.data.length > 0">
                    <div class="text-sm text-slate-400">
                        Menampilkan 
                        <span class="font-medium text-slate-300">{{ bankQuestions.from }}</span> 
                        sampai 
                        <span class="font-medium text-slate-300">{{ bankQuestions.to }}</span> 
                        dari 
                        <span class="font-medium text-slate-300">{{ bankQuestions.total }}</span> soal
                    </div>
                    <Pagination :links="bankQuestions.links" />
                </div>
            </div>
        </div>
    </AdminDashboardLayout>
</template>
