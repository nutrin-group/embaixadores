<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    href: {
        type: String,
        required: true,
    },
    active: {
        type: Boolean,
    },
    icon: {
        type: String,
        default: ''
    }
});

const classes = computed(() =>
    props.active
        ? 'flex items-center px-4 py-3 text-[#f45197] bg-pink-50 rounded-lg transition-colors duration-200'
        : 'flex items-center px-4 py-3 text-gray-600 hover:text-[#f45197] hover:bg-pink-50 rounded-lg transition-colors duration-200'
);
</script>

<template>
    <Link :href="href" :class="classes">
    <i v-if="icon" :class="['mr-3 text-lg', icon]"></i>
    <span class="font-medium">
        <slot />
    </span>
    </Link>
</template>
