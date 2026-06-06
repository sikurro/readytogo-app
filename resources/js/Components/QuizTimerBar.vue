<script setup>
import { ref, onMounted, onUnmounted, watch } from 'vue';

const props = defineProps({
    duration: {
        type: Number,
        required: true,
    },
    isPlaying: {
        type: Boolean,
        default: true
    }
});

const emit = defineEmits(['timeUp']);

const timeLeft = ref(props.duration);
const progress = ref(100);
let timerInterval = null;

const startTimer = () => {
    timeLeft.value = props.duration;
    progress.value = 100;
    
    timerInterval = setInterval(() => {
        if (!props.isPlaying) return;
        
        timeLeft.value -= 0.1; // 100ms
        progress.value = (timeLeft.value / props.duration) * 100;

        if (timeLeft.value <= 0) {
            clearInterval(timerInterval);
            timeLeft.value = 0;
            progress.value = 0;
            emit('timeUp');
        }
    }, 100);
};

onMounted(() => {
    startTimer();
});

onUnmounted(() => {
    if (timerInterval) clearInterval(timerInterval);
});

// Resets timer when duration prop changes (e.g. next question)
watch(() => props.duration, () => {
    if (timerInterval) clearInterval(timerInterval);
    startTimer();
});

defineExpose({
    getTimeLeft: () => Math.max(0, Math.ceil(timeLeft.value))
});
</script>

<template>
    <div class="w-full bg-gray-200 rounded-full h-4 mb-4 dark:bg-gray-700 overflow-hidden">
        <div 
            class="h-4 rounded-full transition-all duration-100 ease-linear"
            :class="{
                'bg-green-500': progress > 50,
                'bg-yellow-400': progress > 20 && progress <= 50,
                'bg-red-600': progress <= 20
            }"
            :style="{ width: `${progress}%` }"
        ></div>
    </div>
</template>
