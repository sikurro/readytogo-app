<script setup>
import { ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const show = ref(false);
const message = ref('');
const type = ref('success'); // 'success' or 'error'
let timer = null;

const showToast = (msg, toastType) => {
    if (timer) clearTimeout(timer);
    message.value = msg;
    type.value = toastType;
    show.value = true;
    
    timer = setTimeout(() => {
        show.value = false;
    }, 4000);
};

const close = () => {
    show.value = false;
};

// Watch for Inertia flash messages
watch(
    () => page.props.flash,
    (flash) => {
        if (flash?.success) {
            showToast(flash.success, 'success');
        } else if (flash?.error) {
            showToast(flash.error, 'error');
        }
    },
    { deep: true, immediate: true }
);
</script>

<template>
    <div class="fixed top-5 right-5 z-[9999] pointer-events-none max-w-sm w-full px-4 sm:px-0">
        <Transition
            enter-active-class="transform ease-out duration-300 transition"
            enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
            enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
            leave-active-class="transition ease-in duration-200"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div 
                v-if="show" 
                class="pointer-events-auto w-full max-w-sm overflow-hidden rounded-xl shadow-2xl border backdrop-blur-md transition-all duration-300"
                :class="{
                    'bg-slate-900/90 border-emerald-500/30 text-slate-100 shadow-emerald-950/20': type === 'success',
                    'bg-slate-900/90 border-rose-500/30 text-slate-100 shadow-rose-950/20': type === 'error'
                }"
            >
                <div class="p-4">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <!-- Success Icon -->
                            <svg v-if="type === 'success'" class="h-6 h-6 text-emerald-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <!-- Error Icon -->
                            <svg v-else class="h-6 h-6 text-rose-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                            </svg>
                        </div>
                        <div class="ml-3 w-0 flex-1 pt-0.5">
                            <p class="text-sm font-semibold text-slate-200">
                                {{ type === 'success' ? 'Sukses' : 'Gagal' }}
                            </p>
                            <p class="mt-1 text-xs text-slate-400 leading-relaxed">
                                {{ message }}
                            </p>
                        </div>
                        <div class="ml-4 flex flex-shrink-0">
                            <button 
                                @click="close" 
                                class="inline-flex rounded-md bg-transparent text-slate-500 hover:text-slate-300 focus:outline-none transition-colors"
                            >
                                <span class="sr-only">Close</span>
                                <svg class="h-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
                <!-- Progress bar -->
                <div class="h-1 bg-gradient-to-r" :class="type === 'success' ? 'from-emerald-500 to-teal-400' : 'from-rose-500 to-orange-400'"></div>
            </div>
        </Transition>
    </div>
</template>
