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

const hasAnswerImages = computed(() => {
    return currentQuestion.value?.answers?.some(a => a.answer_image) || false;
});

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
    if (!selectedAnswerId.value) {
        return 'bg-slate-900/60 hover:bg-slate-900 border-slate-800 hover:border-slate-700/80 text-slate-200';
    }
    
    if (answer.is_correct) {
        return 'bg-emerald-500/20 border-emerald-500 text-emerald-400 font-extrabold shadow-[0_0_10px_rgba(16,185,129,0.15)]';
    }
    if (selectedAnswerId.value === answer.id && !answer.is_correct) {
        return 'bg-rose-500/20 border-rose-500 text-rose-400 font-extrabold shadow-[0_0_10px_rgba(244,63,94,0.15)]';
    }
    
    return 'border-slate-900 bg-slate-900/20 opacity-30 text-slate-500 cursor-not-allowed';
};
</script>

<template>
    <Head title="Bermain Kuis" />

    <div class="h-[100dvh] max-h-[100dvh] bg-slate-950 text-slate-100 flex flex-col p-4 overflow-hidden select-none justify-center">
        <!-- Empty Questions Fallback -->
        <div v-if="questions.length === 0" class="max-w-md w-full mx-auto bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl text-center">
            <div class="mb-4 text-rose-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-14 w-14 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <h2 class="text-xl font-bold mb-2 text-slate-100">Kuis Belum Siap</h2>
            <p class="text-slate-400 text-sm mb-6">Kuis ini belum memiliki daftar pertanyaan/soal. Silakan hubungi admin untuk menambahkan soal.</p>
            <a :href="route('quiz.index')" class="inline-block w-full bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-slate-950 font-black py-3 px-6 rounded-xl text-center shadow-lg transition duration-200 text-sm">
                Kembali ke Beranda Kuis
            </a>
        </div>

        <div v-else-if="currentQuestion" class="flex-grow flex flex-col max-w-lg mx-auto w-full h-full justify-between py-2">
            
            <!-- Header -->
            <div class="flex justify-between items-center mb-2">
                <span class="text-xs text-slate-400 font-extrabold uppercase tracking-wider flex items-center gap-1.5">
                    Soal {{ currentQuestionIndex + 1 }} / {{ questions.length }}
                    <span v-if="isDemo" class="text-[9px] uppercase px-2 py-0.5 bg-amber-500/10 border border-amber-500/30 text-amber-500 rounded-full font-black">Simulasi</span>
                </span>
                <span class="text-sm font-black text-amber-500">Skor: {{ score }}</span>
            </div>

            <!-- Timer -->
            <div class="mb-3">
                <QuizTimerBar 
                    :key="currentQuestionIndex"
                    ref="timerRef"
                    :duration="currentQuestion.timer_seconds" 
                    :isPlaying="isPlaying"
                    @timeUp="handleTimeUp" 
                />
            </div>

            <!-- Question Area -->
            <div class="bg-slate-900 border border-slate-800/80 rounded-2xl p-4 flex-grow flex flex-col justify-center items-center mb-4 min-h-[150px] max-h-[420px] overflow-hidden">
                <!-- Question Image if any -->
                <img v-if="currentQuestion.question_image" 
                     :src="currentQuestion.question_image" 
                     alt="Question Image"
                     class="max-h-52 md:max-h-64 rounded-lg mb-3 object-contain shadow-inner" />
                
                <h2 v-if="currentQuestion.question_text" 
                    class="text-center text-slate-100"
                    :class="[!currentQuestion.question_image ? 'text-2xl md:text-4xl font-black px-6 tracking-tight leading-normal' : 'text-base md:text-xl font-bold leading-snug']"
                >
                    {{ currentQuestion.question_text }}
                </h2>
            </div>

            <!-- Answers Area (2x2 Grid) -->
            <div class="grid grid-cols-2 gap-3 mt-auto">
                <button 
                    v-for="answer in currentQuestion.answers" 
                    :key="answer.id"
                    @click="handleAnswer(answer)"
                    :disabled="!isPlaying"
                    class="border-2 rounded-2xl p-2.5 transition-all duration-200 flex flex-col justify-center items-center min-h-[145px] max-h-[180px] overflow-hidden"
                    :class="getAnswerClass(answer)"
                >
                    <img v-if="answer.answer_image" 
                         :src="answer.answer_image" 
                         alt="Answer Image"
                         class="h-28 md:h-32 w-auto mb-1 rounded-lg object-contain" />
                    
                    <span v-if="answer.answer_text" 
                          class="text-center leading-tight"
                          :class="[!answer.answer_image ? 'text-sm md:text-lg font-black px-1' : 'text-xs md:text-sm font-bold']"
                    >
                        {{ answer.answer_text }}
                    </span>
                </button>
            </div>
            
        </div>
    </div>
</template>
