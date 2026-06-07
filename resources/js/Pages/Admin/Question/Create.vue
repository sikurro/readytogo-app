<script setup>
import AdminDashboardLayout from '@/Layouts/AdminDashboardLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    categories: Array,
});

// Create 5 default answer options
const defaultAnswers = Array(5).fill(null).map((_, index) => ({
    answer_text: '',
    answer_image: null,
    is_correct: index === 0, // Option A is correct by default
}));

// Setup form state
const form = useForm({
    question_text: '',
    question_image: null,
    risk_level: 'Low',
    reference: '',
    categories: [],
    answers: defaultAnswers,
});

// Local previews for newly uploaded files
const questionImagePreview = ref(null);
const answerImagePreviews = ref({});

const handleQuestionImageChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        form.question_image = file;
        questionImagePreview.value = URL.createObjectURL(file);
    }
};

const handleAnswerImageChange = (e, index) => {
    const file = e.target.files[0];
    if (file) {
        form.answers[index].answer_image = file;
        answerImagePreviews.value[index] = URL.createObjectURL(file);
    }
};

const setCorrectAnswer = (selectedIndex) => {
    form.answers.forEach((ans, idx) => {
        ans.is_correct = idx === selectedIndex;
    });
};

const scrollToError = () => {
    setTimeout(() => {
        const firstErrorEl = document.querySelector('.text-rose-500, .text-red-600, .border-rose-500');
        if (firstErrorEl) {
            firstErrorEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }, 100);
};

const submitForm = () => {
    form
        .transform((data) => {
            // Only send answers that have text or image
            const validAnswers = data.answers.filter(ans => ans.answer_text.trim() !== '' || ans.answer_image !== null);
            
            // Fallback: if user didn't fill anything, let validation handle it, but keep at least 2 to trigger min:2
            const answersToSubmit = validAnswers.length >= 2 ? validAnswers : data.answers.slice(0, 2);

            const cleanedAnswers = answersToSubmit.map(ans => {
                const copy = { ...ans };
                if (!copy.answer_image) {
                    delete copy.answer_image;
                }
                copy.is_correct = copy.is_correct ? 1 : 0;
                return copy;
            });

            const cleanedData = {
                ...data,
                answers: cleanedAnswers,
            };

            if (!cleanedData.question_image) {
                delete cleanedData.question_image;
            }

            return cleanedData;
        })
        .post(route('admin.questions.store'), {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => {
                form.reset();
                // Re-initialize default options to reset the state correctly for the next item
                form.answers = Array(5).fill(null).map((_, index) => ({
                    answer_text: '',
                    answer_image: null,
                    is_correct: index === 0,
                }));
                // Reset file inputs manually
                document.querySelectorAll('input[type=file]').forEach(el => el.value = '');
                questionImagePreview.value = null;
                answerImagePreviews.value = {};
            },
            onError: () => scrollToError(),
        });
};

const getAnswerLabel = (index) => {
    return String.fromCharCode(65 + index); // A, B, C, D...
};
</script>

<template>
    <Head title="Tambah Soal - Admin" />

    <AdminDashboardLayout>
        <template #header>
            <span>Tambah Soal Master Bank</span>
        </template>

        <div class="max-w-4xl mx-auto space-y-6">
            <div class="flex items-center justify-between">
                <Link 
                    :href="route('admin.questions.index')" 
                    class="flex items-center gap-2 text-slate-400 hover:text-slate-200 transition-colors duration-200 text-sm font-medium"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                    </svg>
                    Kembali ke Bank Soal
                </Link>
            </div>

            <form @submit.prevent="submitForm" class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl space-y-6">
                <!-- Form Header -->
                <div class="border-b border-slate-800 pb-4">
                    <h3 class="text-lg font-bold text-slate-200">Formulir Tambah Soal Manual</h3>
                    <p class="text-sm text-slate-400">Silakan isi detail pertanyaan, kategori, opsi jawaban, dan tentukan kunci jawaban di bawah.</p>
                </div>

                <!-- Errors list if any -->
                <div v-if="Object.keys(form.errors).length > 0" class="p-4 bg-rose-950/40 border border-rose-800/50 rounded-xl text-sm text-rose-400 space-y-1">
                    <p class="font-semibold">Mohon perbaiki kesalahan berikut:</p>
                    <ul class="list-disc pl-5 space-y-1">
                        <li v-for="(error, key) in form.errors" :key="key">{{ error }}</li>
                    </ul>
                </div>

                <!-- Question Text -->
                <div class="space-y-2">
                    <label class="block text-sm font-semibold text-slate-300">Teks Soal</label>
                    <textarea 
                        v-model="form.question_text" 
                        rows="4" 
                        required
                        class="w-full bg-slate-950 border border-slate-800 rounded-lg py-2.5 px-4 text-sm text-slate-200 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition-colors"
                        :class="form.errors.question_text ? 'border-rose-500/50 focus:border-rose-500 focus:ring-rose-500' : 'border-slate-800'"
                        placeholder="Ketik teks pertanyaan di sini..."
                    ></textarea>
                    <InputError :message="form.errors.question_text" class="mt-1" />
                </div>

                <!-- Question Image -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-4 bg-slate-950/40 rounded-xl border" :class="form.errors.question_image ? 'border-rose-500/50' : 'border-slate-800'">
                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-slate-300">Gambar Pendukung (Opsional)</label>
                        <p class="text-xs text-slate-400">Format: PNG, JPG, JPEG, GIF. Ukuran maks 2MB.</p>
                        <input 
                            type="file" 
                            @change="handleQuestionImageChange" 
                            accept="image/*"
                            class="block w-full text-sm text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-slate-800 file:text-slate-300 hover:file:bg-slate-700 cursor-pointer transition-colors"
                        />
                        <InputError :message="form.errors.question_image" class="mt-1" />
                    </div>
                    <div class="flex flex-col justify-center items-center bg-slate-950/80 rounded-lg p-2 min-h-[120px] border border-slate-800/50">
                        <span class="text-xs text-slate-500 mb-2 font-medium">Pratinjau Gambar</span>
                        <img v-if="questionImagePreview" :src="questionImagePreview" class="max-h-24 object-contain rounded" />
                        <span v-else class="text-xs text-slate-600">Tidak ada gambar</span>
                    </div>
                </div>

                <!-- Categories, Risk Level, Reference -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Categories -->
                    <div class="space-y-2 bg-slate-950/20 p-4 rounded-xl border" :class="form.errors.categories ? 'border-rose-500/50' : 'border-slate-800/80'">
                        <label class="block text-sm font-semibold text-slate-300">Kategori Soal (Bisa lebih dari satu)</label>
                        <div class="grid grid-cols-2 gap-2 max-h-40 overflow-y-auto pr-2">
                            <label v-for="cat in categories" :key="cat.id" class="flex items-center gap-2 p-2 rounded hover:bg-slate-800/30 cursor-pointer transition-colors">
                                <input 
                                    type="checkbox" 
                                    :value="cat.id" 
                                    v-model="form.categories"
                                    class="rounded bg-slate-950 border-slate-800 text-amber-500 focus:ring-amber-500"
                                />
                                <span class="text-xs text-slate-300 select-none">{{ cat.name }}</span>
                            </label>
                        </div>
                        <InputError :message="form.errors.categories" class="mt-2 text-rose-500" />
                    </div>

                    <!-- Risk Level & Reference -->
                    <div class="space-y-4">
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-slate-300">Level Resiko</label>
                            <select 
                                v-model="form.risk_level" 
                                required
                                class="w-full bg-slate-950 border rounded-lg py-2.5 px-4 text-sm text-slate-200 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition-colors"
                                :class="form.errors.risk_level ? 'border-rose-500/50 focus:border-rose-500 focus:ring-rose-500' : 'border-slate-800'"
                            >
                                <option value="Low">Low</option>
                                <option value="Medium">Medium</option>
                                <option value="High">High</option>
                            </select>
                            <InputError :message="form.errors.risk_level" class="mt-1" />
                        </div>

                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-slate-300">Referensi / Sumber</label>
                            <input 
                                type="text" 
                                v-model="form.reference"
                                class="w-full bg-slate-950 border rounded-lg py-2.5 px-4 text-sm text-slate-200 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition-colors"
                                :class="form.errors.reference ? 'border-rose-500/50 focus:border-rose-500 focus:ring-rose-500' : 'border-slate-800'"
                                placeholder="Contoh: Undang-Undang No. 1 Tahun 1970"
                            />
                            <InputError :message="form.errors.reference" class="mt-1" />
                        </div>
                    </div>
                </div>

                <!-- Answers section -->
                <div class="space-y-4 pt-4 border-t border-slate-800">
                    <div class="flex items-center justify-between">
                        <h4 class="text-md font-bold text-slate-200">Pilihan Jawaban</h4>
                        <span class="text-xs text-slate-400 italic">*Isi minimal 2 opsi jawaban. Kosongkan sisanya jika tidak dipakai. Pilih 1 kunci jawaban.</span>
                    </div>

                    <div class="space-y-4">
                        <div 
                            v-for="(ans, index) in form.answers" 
                            :key="index"
                            class="p-4 rounded-xl border transition-colors duration-200"
                            :class="ans.is_correct ? 'bg-emerald-950/20 border-emerald-800/80 shadow-md shadow-emerald-950/10' : 'bg-slate-950/20 border-slate-800'"
                        >
                            <div class="flex items-start gap-4">
                                <!-- Radio selection for correct answer -->
                                <div class="flex flex-col items-center pt-2">
                                    <input 
                                        type="radio" 
                                        name="correct_answer" 
                                        :checked="ans.is_correct"
                                        @change="setCorrectAnswer(index)"
                                        class="w-5 h-5 bg-slate-950 border-slate-800 text-emerald-500 focus:ring-emerald-500 cursor-pointer"
                                    />
                                    <span class="text-xs font-bold mt-1 text-slate-400">{{ getAnswerLabel(index) }}</span>
                                </div>

                                <!-- Text Input and Image Upload -->
                                <div class="flex-1 space-y-3">
                                    <div>
                                        <input 
                                            type="text" 
                                            v-model="ans.answer_text"
                                            :required="index < 2 && !ans.answer_image"
                                            class="w-full bg-slate-950 border border-slate-800 rounded-lg py-2 px-3 text-sm text-slate-200 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition-colors"
                                            placeholder="Teks opsi jawaban..."
                                        />
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-center bg-slate-950/40 p-3 rounded-lg border border-slate-800/50">
                                        <div class="space-y-1">
                                            <label class="block text-xs font-semibold text-slate-400">Gambar Opsi (Opsional)</label>
                                            <input 
                                                type="file" 
                                                @change="e => handleAnswerImageChange(e, index)"
                                                accept="image/*"
                                                class="block w-full text-xs text-slate-400 file:mr-2 file:py-1 file:px-3 file:rounded file:border-0 file:text-xs file:font-semibold file:bg-slate-800 file:text-slate-300 hover:file:bg-slate-700 cursor-pointer transition-colors"
                                            />
                                        </div>
                                        <div class="flex justify-center items-center bg-slate-950/80 rounded p-1 min-h-[50px] border border-slate-800/30">
                                            <img v-if="answerImagePreviews[index]" :src="answerImagePreviews[index]" class="max-h-12 object-contain rounded" />
                                            <span v-else class="text-[10px] text-slate-600">Tidak ada gambar</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit / Cancel Action buttons -->
                <div class="bg-slate-800/30 -mx-6 -mb-6 px-6 py-4 flex flex-row-reverse gap-3 border-t border-slate-800 rounded-b-2xl">
                    <button 
                        type="submit" 
                        :disabled="form.processing"
                        class="inline-flex justify-center rounded-lg border border-transparent px-5 py-2.5 bg-blue-600 text-sm font-semibold text-white hover:bg-blue-500 focus:outline-none transition-colors disabled:opacity-50 shadow-lg shadow-blue-900/20"
                    >
                        Simpan & Tambah Baru
                    </button>
                    <Link 
                        :href="route('admin.questions.index')"
                        class="inline-flex justify-center rounded-lg border border-slate-700 px-5 py-2.5 bg-slate-800 text-sm font-semibold text-slate-300 hover:bg-slate-700 focus:outline-none transition-colors"
                    >
                        Selesai / Kembali
                    </Link>
                </div>
            </form>
        </div>
    </AdminDashboardLayout>
</template>
