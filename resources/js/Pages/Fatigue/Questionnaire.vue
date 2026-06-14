<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

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

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Kuesioner Fatigue Check</h2>
        </template>

        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Self-Assessment Sebelum Bekerja</h3>
                            <p class="text-gray-600">Jawab pertanyaan berikut dengan jujur untuk memastikan kondisi Anda Fit to Work hari ini.</p>
                        </div>

                        <div v-if="errorMessage" class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                            <span class="block sm:inline">{{ errorMessage }}</span>
                        </div>

                        <div class="space-y-6">
                            <div v-for="(question, index) in questions" :key="question.id" class="p-4 border rounded-md bg-gray-50">
                                <p class="text-md font-medium text-gray-800 mb-3">{{ index + 1 }}. {{ question.text }}</p>
                                <div class="flex space-x-4">
                                    <label class="flex items-center cursor-pointer">
                                        <input type="radio" :name="question.id" :value="true" v-model="question.answer" class="form-radio h-5 w-5 text-emerald-600 focus:ring-emerald-500">
                                        <span class="ml-2 text-gray-700">Ya</span>
                                    </label>
                                    <label class="flex items-center cursor-pointer">
                                        <input type="radio" :name="question.id" :value="false" v-model="question.answer" class="form-radio h-5 w-5 text-red-600 focus:ring-red-500">
                                        <span class="ml-2 text-gray-700">Tidak</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="mt-8 flex justify-end">
                            <PrimaryButton @click="submitQuestionnaire" class="w-full sm:w-auto py-3">
                                Lanjutkan ke Tes Reaksi
                            </PrimaryButton>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
