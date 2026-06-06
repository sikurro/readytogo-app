<script setup>
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import QuizTimerBar from '@/Components/QuizTimerBar.vue';

const props = defineProps({
    quiz: Object
});

const questions = props.quiz.questions;
const currentQuestionIndex = ref(0);
const score = ref(0);
const correctAnswersCount = ref(0);
const timeSpentMs = ref(0);
const startTime = ref(Date.now());
const isPlaying = ref(true);
const selectedAnswerId = ref(null);
const timerRef = ref(null);

const currentQuestion = computed(() => questions[currentQuestionIndex.value] || null);

const handleAnswer = (answer) => {
    if (!isPlaying.value) return;
    isPlaying.value = false;
    selectedAnswerId.value = answer.id;
    
    // Evaluate answer
    if (answer.is_correct) {
        correctAnswersCount.value++;
        const timeLeft = timerRef.value ? timerRef.value.getTimeLeft() : 0;
        score.value += 100 + (timeLeft * 10);
    }

    setTimeout(() => {
        nextQuestion();
    }, 1500);
};

const handleTimeUp = () => {
    if (!isPlaying.value) return;
    isPlaying.value = false;
    
    setTimeout(() => {
        nextQuestion();
    }, 1500);
};

const nextQuestion = () => {
    if (currentQuestionIndex.value < questions.length - 1) {
        currentQuestionIndex.value++;
        selectedAnswerId.value = null;
        isPlaying.value = true;
    } else {
        finishQuiz();
    }
};

const finishQuiz = () => {
    timeSpentMs.value = Date.now() - startTime.value;
    
    router.post(route('quiz.store'), {
        score: score.value,
        correct_answers: correctAnswersCount.value,
        time_ms: timeSpentMs.value
    });
};

const getAnswerClass = (answer) => {
    if (!selectedAnswerId.value) return 'bg-white hover:bg-gray-100 border-gray-300';
    
    if (answer.is_correct) return 'bg-green-500 text-white border-green-600';
    if (selectedAnswerId.value === answer.id && !answer.is_correct) return 'bg-red-500 text-white border-red-600';
    
    return 'bg-gray-100 border-gray-300 opacity-50 text-gray-500';
};
</script>

<template>
    <Head title="Bermain Kuis" />

    <div class="min-h-screen bg-gray-900 text-white flex flex-col p-4 md:p-8">
        <div v-if="currentQuestion" class="flex-grow flex flex-col max-w-4xl mx-auto w-full">
            
            <!-- Header -->
            <div class="flex justify-between items-center mb-4">
                <span class="text-xl font-bold">Soal {{ currentQuestionIndex + 1 }} / {{ questions.length }}</span>
                <span class="text-xl font-bold text-yellow-400">Skor: {{ score }}</span>
            </div>

            <!-- Timer -->
            <QuizTimerBar 
                :key="currentQuestionIndex"
                ref="timerRef"
                :duration="currentQuestion.timer_seconds" 
                :isPlaying="isPlaying"
                @timeUp="handleTimeUp" 
            />

            <!-- Question Area -->
            <div class="bg-gray-800 rounded-xl p-6 shadow-xl flex-grow flex flex-col justify-center items-center mb-6">
                <!-- Question Image if any -->
                <img v-if="currentQuestion.question_image" 
                     :src="currentQuestion.question_image" 
                     alt="Question Image"
                     class="max-h-64 rounded-lg mb-6 object-contain" />
                
                <h2 v-if="currentQuestion.question_text" class="text-2xl md:text-3xl font-bold text-center leading-tight">
                    {{ currentQuestion.question_text }}
                </h2>
            </div>

            <!-- Answers Area (2x2 Grid) -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <button 
                    v-for="answer in currentQuestion.answers" 
                    :key="answer.id"
                    @click="handleAnswer(answer)"
                    :disabled="!isPlaying"
                    class="border-2 rounded-xl p-4 md:p-6 text-lg md:text-xl font-bold transition-all duration-200 flex flex-col justify-center items-center text-gray-900 min-h-[100px]"
                    :class="getAnswerClass(answer)"
                >
                    <img v-if="answer.answer_image" 
                         :src="answer.answer_image" 
                         alt="Answer Image"
                         class="max-h-32 mb-2 rounded object-contain" />
                    
                    <span v-if="answer.answer_text" class="text-center">{{ answer.answer_text }}</span>
                </button>
            </div>
            
        </div>
    </div>
</template>
