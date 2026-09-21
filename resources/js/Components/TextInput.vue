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
        class="border-slate-300 focus:border-brand-500 focus:ring-brand-500/20 focus:ring-2 rounded-xl text-slate-800 placeholder:text-slate-400 text-sm shadow-xs transition"
        v-model="model"
        ref="input"
    />
</template>
