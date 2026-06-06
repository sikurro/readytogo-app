<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    links: Array,
});
</script>

<template>
    <div v-if="links.length > 3">
        <div class="flex flex-wrap -mb-1">
            <template v-for="(link, key) in links" :key="key">
                <div v-if="link.url === null" class="mr-1 mb-1 px-4 py-3 text-sm leading-4 text-slate-500 border border-slate-800 rounded bg-slate-900/50" v-html="link.label" />
                <Link v-else class="mr-1 mb-1 px-4 py-3 text-sm leading-4 border rounded transition-colors duration-150" :class="{ 'bg-amber-500 text-slate-950 font-bold border-amber-500 hover:bg-amber-400': link.active, 'text-slate-300 border-slate-850 hover:bg-slate-800 bg-slate-900': !link.active }" :href="link.url" v-html="link.label" />
            </template>
        </div>
    </div>
</template>
