<template>
    <div v-if="links.length > 3" class="flex flex-wrap -mb-1">
        <template v-for="(link, key) in links" :key="key">
            <div
                v-if="link.url === null"
                class="mr-1 mb-1 px-4 py-2 text-sm leading-4 text-gray-400 border rounded"
                v-html="link.label"
            />

            <Link
                v-else
                :href="link.url"
                class="mr-1 mb-1 px-4 py-2 text-sm leading-4 border rounded hover:bg-[#f45197] focus:border-[#f45197] focus:text-white"
                :class="{ 'bg-[#f45197] text-white': link.active }"
                v-html="link.label"
            />
        </template>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    links: {
        type: Array,
        required: true,
    },
});
</script>
