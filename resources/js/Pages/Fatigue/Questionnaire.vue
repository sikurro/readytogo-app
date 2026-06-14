<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import MobileAppLayout from '@/Layouts/MobileAppLayout.vue';

const questions = ref([
    {
        id: 'q1',
        text: 'Apakah Anda tidur minimal 6 jam semalam?',
        answer: null
    },
    {
        id: 'q2',
        text: 'Apakah Anda sedang mengonsumsi obat yang menyebabkan kantuk?',
        answer: null
    },
    {
        id: 'q3',
        text: 'Apakah kondisi badan Anda merasa sehat/fit hari ini?',
        answer: null
    }
]);

const errorMessage = ref('');

const submitQuestionnaire = () => {
    // Validate that all questions are answered
    for (const q of questions.value) {
        if (q.answer === null) {
            errorMessage.value = 'Harap jawab semua pertanyaan.';
            return;
        }
    }
    
    errorMessage.value = '';

    // Calculate status: 
    // Q1 (Tidur cukup) must be true
    // Q2 (Obat ngantuk) must be false
    // Q3 (Merasa sehat) must be true
    const isFitQuestionnaire = (
        questions.value[0].answer === true &&
        questions.value[1].answer === false &&
        questions.value[2].answer === true
    );

    router.post(route('fatigue.questionnaire.process'), {
        status: isFitQuestionnaire,
        answers: questions.value.map(q => q.answer)
    });
};
</script>

<template>
    <Head title="Fatigue Check - Kuesioner" />

    <MobileAppLayout>
        <div class="space-y-6">
            <!-- Header section -->
            <div class="bg-gradient-to-br from-slate-900 to-slate-800 border border-slate-800 rounded-2xl p-5 shadow-xl">
                <div class="flex items-center gap-4">
                    <div class="h-12 w-12 rounded-xl bg-slate-800 flex items-center justify-center border border-slate-700 shadow-inner">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6 text-amber-500">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-slate-100 tracking-tight">Self-Assessment</h2>
                        <p class="text-[11px] text-slate-400 font-medium mt-0.5 leading-relaxed">Jawab dengan jujur untuk memastikan kondisi Anda Fit to Work hari ini.</p>
                    </div>
                </div>
            </div>

            <!-- Error Message -->
            <div v-if="errorMessage" class="bg-red-500/10 border border-red-500/30 rounded-xl p-4 flex gap-3 items-start animate-pulse">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-red-500 shrink-0 mt-0.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2.25m0 2.25h.01M12 18a9 9 0 100-18 9 9 0 000 18z" />
                </svg>
                <p class="text-xs font-semibold text-red-400 mt-0.5">{{ errorMessage }}</p>
            </div>

            <!-- Questionnaire Form -->
            <div class="space-y-4">
                <div v-for="(question, index) in questions" :key="question.id" 
                    class="bg-slate-800/50 backdrop-blur border border-slate-700/50 rounded-xl p-5 shadow-lg">
                    <p class="text-sm font-medium text-slate-200 mb-4 leading-relaxed"><span class="text-amber-500 font-bold mr-1">{{ index + 1 }}.</span> {{ question.text }}</p>
                    <div class="flex space-x-4">
                        <label class="flex-1 relative">
                            <input type="radio" :name="question.id" :value="true" v-model="question.answer" class="peer sr-only">
                            <div class="text-center py-3 px-4 rounded-lg border border-slate-700 bg-slate-800 text-slate-400 font-bold text-sm cursor-pointer transition-all peer-checked:bg-emerald-500/20 peer-checked:border-emerald-500/50 peer-checked:text-emerald-400 hover:bg-slate-700">
                                Ya
                            </div>
                        </label>
                        <label class="flex-1 relative">
                            <input type="radio" :name="question.id" :value="false" v-model="question.answer" class="peer sr-only">
                            <div class="text-center py-3 px-4 rounded-lg border border-slate-700 bg-slate-800 text-slate-400 font-bold text-sm cursor-pointer transition-all peer-checked:bg-red-500/20 peer-checked:border-red-500/50 peer-checked:text-red-400 hover:bg-slate-700">
                                Tidak
                            </div>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Submit Action -->
            <div class="pt-4 pb-8">
                <button @click="submitQuestionnaire" 
                    class="w-full bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-slate-950 font-extrabold py-4 px-5 rounded-xl shadow-lg shadow-amber-500/20 transition-all active:scale-95 flex items-center justify-center gap-2">
                    <span>Lanjutkan ke Tes Reaksi</span>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                    </svg>
                </button>
            </div>
        </div>
    </MobileAppLayout>
</template>
