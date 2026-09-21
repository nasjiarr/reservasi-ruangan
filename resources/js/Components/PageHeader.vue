<script setup>
defineProps({
    title: {
        type: String,
        required: true,
    },
    description: {
        type: String,
        default: '',
    },
    badge: {
        type: String,
        default: '',
    },
});
</script>

<template>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2">
        <div class="space-y-1">
            <div class="flex items-center gap-2.5">
                <h1 class="font-heading font-extrabold text-2xl text-slate-900 tracking-tight">
                    {{ title }}
                </h1>
                <span
                    v-if="badge"
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-brand-50 text-brand-700 border border-brand-200"
                >
                    {{ badge }}
                </span>
            </div>
            <p v-if="description" class="text-sm text-slate-500 max-w-2xl">
                {{ description }}
            </p>
        </div>

        <div v-if="$slots.actions" class="flex items-center flex-wrap gap-2.5 shrink-0">
            <slot name="actions" />
        </div>
    </div>
</template>

