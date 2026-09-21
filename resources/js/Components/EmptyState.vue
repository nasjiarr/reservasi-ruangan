<script setup>
import { FolderOpenIcon } from '@heroicons/vue/24/outline';

defineProps({
    title: {
        type: String,
        default: 'Tidak Ada Data',
    },
    description: {
        type: String,
        default: 'Belum ada data yang tersedia untuk ditampilkan.',
    },
});
</script>

<template>
    <div class="text-center py-12 px-6 border-2 border-dashed border-slate-200 rounded-2xl bg-slate-50/50 my-4">
        <div class="mx-auto w-14 h-14 rounded-2xl bg-white shadow-sm border border-slate-200 flex items-center justify-center text-slate-400 mb-4">
            <slot name="icon">
                <FolderOpenIcon class="w-7 h-7 stroke-[1.5]" />
            </slot>
        </div>
        <h3 class="font-heading font-bold text-base text-slate-800 tracking-tight">
            {{ title }}
        </h3>
        <p class="mt-1 text-sm text-slate-500 max-w-sm mx-auto">
            {{ description }}
        </p>
        <div v-if="$slots.action" class="mt-6 flex justify-center items-center gap-3">
            <slot name="action" />
        </div>
    </div>
</template>

