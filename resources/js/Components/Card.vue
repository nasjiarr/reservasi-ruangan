<script setup>
defineProps({
    hoverable: {
        type: Boolean,
        default: false,
    },
    noPadding: {
        type: Boolean,
        default: false,
    },
});
</script>

<template>
    <div
        :class="[
            'bg-white rounded-xl border border-slate-200/80 shadow-card overflow-hidden',
            hoverable ? 'hover:shadow-card-hover hover:-translate-y-0.5 hover:border-slate-300 transition-all duration-200' : 'transition-colors',
        ]"
    >
        <div v-if="$slots.header" class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <slot name="header" />
        </div>

        <div :class="{ 'p-6': !noPadding }">
            <slot />
        </div>

        <div v-if="$slots.footer" class="px-6 py-3 bg-slate-50/50 border-t border-slate-100">
            <slot name="footer" />
        </div>
    </div>
</template>

