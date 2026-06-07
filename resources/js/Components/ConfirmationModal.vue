<script setup>
import { onMounted, onUnmounted, watch } from 'vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    title: {
        type: String,
        default: 'Konfirmasi Tindakan',
    },
    message: {
        type: String,
        default: 'Apakah Anda yakin ingin melanjutkan tindakan ini?',
    },
    confirmText: {
        type: String,
        default: 'Ya, Hapus',
    },
    cancelText: {
        type: String,
        default: 'Batal',
    },
    loading: {
        type: Boolean,
        default: false,
    }
});

const emit = defineEmits(['close', 'confirm']);

watch(
    () => props.show,
    (showVal) => {
        if (showVal) {
            document.body.style.overflow = 'hidden';
        } else {
            document.body.style.overflow = null;
        }
    }
);

const close = () => {
    if (!props.loading) {
        emit('close');
    }
};

const confirm = () => {
    emit('confirm');
};

const handleEscape = (e) => {
    if (e.key === 'Escape' && props.show) {
        close();
    }
};

onMounted(() => document.addEventListener('keydown', handleEscape));
onUnmounted(() => {
    document.removeEventListener('keydown', handleEscape);
    document.body.style.overflow = null;
});
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="ease-out duration-300"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="ease-in duration-200"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-show="show" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm">
                <!-- Modal Box -->
                <div 
                    v-show="show"
                    class="w-full max-w-md bg-slate-900 border border-slate-800 rounded-2xl shadow-2xl transform transition-all overflow-hidden"
                >
                    <!-- Content -->
                    <div class="p-6">
                        <div class="flex items-start gap-4">
                            <!-- Warning Icon -->
                            <div class="p-3 bg-rose-500/10 text-rose-500 rounded-full shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                                </svg>
                            </div>
                            
                            <div class="space-y-1">
                                <h3 class="text-lg font-bold text-slate-200">{{ title }}</h3>
                                <p class="text-sm text-slate-400 leading-relaxed">{{ message }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Footer Action Buttons -->
                    <div class="px-6 py-4 bg-slate-950/40 border-t border-slate-800/60 flex justify-end gap-3">
                        <button
                            type="button"
                            :disabled="loading"
                            @click="close"
                            class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 font-semibold text-sm rounded-lg transition-colors duration-200"
                        >
                            {{ cancelText }}
                        </button>
                        <button
                            type="button"
                            :disabled="loading"
                            @click="confirm"
                            class="flex items-center justify-center gap-2 px-4 py-2 bg-rose-600 hover:bg-rose-500 text-white font-semibold text-sm rounded-lg transition-colors duration-200 shadow-lg shadow-rose-950/20 disabled:opacity-50"
                        >
                            <svg v-if="loading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            {{ confirmText }}
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
