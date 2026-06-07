<script setup>
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import QuizTimerBar from '@/Components/QuizTimerBar.vue';

const props = defineProps({
    quiz: Object,
    isDemo: Boolean
});

const questions = props.quiz.questions || [];
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
    
    router.post(route('quiz.store', props.quiz.id), {
        score: score.value,
        correct_answers: correctAnswersCount.value,
        time_ms: timeSpentMs.value,
        is_demo: props.isDemo
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

    <div class="min-h-screen bg-gray-900 text-white flex flex-col p-4 md:p-8 justify-center">
        <!-- Empty Questions Fallback -->
        <div v-if="questions.length === 0" class="max-w-md w-full mx-auto bg-gray-800 rounded-xl p-8 shadow-xl text-center">
            <div class="mb-4 text-red-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <h2 class="text-2xl font-bold mb-2">Kuis Belum Siap</h2>
            <p class="text-gray-400 mb-6">Kuis ini belum memiliki daftar pertanyaan/soal. Silakan hubungi admin untuk menambahkan soal.</p>
            <a :href="route('quiz.index')" class="inline-block w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg transition duration-200">
                Kembali ke Beranda Kuis
            </a>
        </div>

        <div v-else-if="currentQuestion" class="flex-grow flex flex-col max-w-4xl mx-auto w-full">
            
            <!-- Header -->
            <div class="flex justify-between items-center mb-4">
                <span class="text-xl font-bold">Soal {{ currentQuestionIndex + 1 }} / {{ questions.length }} <span v-if="isDemo" class="ml-2 text-xs uppercase px-2 py-1 bg-yellow-600 rounded">Simulasi</span></span>
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
