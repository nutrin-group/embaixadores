<script setup>
import GummyCoin from '@/Components/Icons/GummyCoin.vue';
defineProps({
    balance: {
        type: Number,
        required: true
    }
});
</script>

<template>

    <div class="flex items-center bg-yellow-100 text-yellow-800 rounded-full px-3 py-1">
        <GummyCoin class="h-4 w-4 mr-2" />
        <span class="text-sm font-medium">{{ balance }}</span>
    </div>
</template>
