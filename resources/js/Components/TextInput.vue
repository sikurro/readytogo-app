<script setup>
import { onMounted, ref } from 'vue';

const model = defineModel({
    type: String,
    required: true,
});

const input = ref(null);

onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <input
        class="border-slate-800 bg-slate-950 text-slate-100 focus:border-amber-500 focus:ring-amber-500 rounded-xl shadow-sm placeholder-slate-600 transition-colors"
        v-model="model"
        ref="input"
    />
</template>
