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
    <input class="rounded-md border-pink-300 shadow-sm focus:border-pink-500 focus:ring-pink-500" v-model="model"
        ref="input" />
</template>
