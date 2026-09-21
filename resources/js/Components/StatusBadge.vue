<script setup>
import { computed } from 'vue';
import {
    CheckCircleIcon,
    ClockIcon,
    XCircleIcon,
    MinusCircleIcon,
} from '@heroicons/vue/20/solid';

const props = defineProps({
    status: {
        type: String,
        required: true,
    },
    size: {
        type: String,
        default: 'md', // sm, md, lg
    },
});

const config = computed(() => {
    switch (props.status) {
        case 'approved':
            return {
                label: 'Disetujui',
                icon: CheckCircleIcon,
                classes: 'bg-emerald-50 text-emerald-700 border-emerald-200/80 shadow-sm',
                dotClass: 'bg-emerald-500',
            };
        case 'pending':
            return {
                label: 'Menunggu',
                icon: ClockIcon,
                classes: 'bg-amber-50 text-amber-700 border-amber-200/80 shadow-sm',
                dotClass: 'bg-amber-500 animate-pulse',
            };
        case 'rejected':
            return {
                label: 'Ditolak',
                icon: XCircleIcon,
                classes: 'bg-rose-50 text-rose-700 border-rose-200/80 shadow-sm',
                dotClass: 'bg-rose-500',
            };
        case 'cancelled':
            return {
                label: 'Dibatalkan',
                icon: MinusCircleIcon,
                classes: 'bg-slate-100 text-slate-600 border-slate-200',
                dotClass: 'bg-slate-400',
            };
        case 'active':
            return {
                label: 'Aktif',
                icon: CheckCircleIcon,
                classes: 'bg-emerald-50 text-emerald-700 border-emerald-200/80 shadow-sm',
                dotClass: 'bg-emerald-500',
            };
        case 'maintenance':
            return {
                label: 'Pemeliharaan',
                icon: ClockIcon,
                classes: 'bg-amber-50 text-amber-700 border-amber-200/80 shadow-sm',
                dotClass: 'bg-amber-500',
            };
        case 'inactive':
            return {
                label: 'Nonaktif',
                icon: XCircleIcon,
                classes: 'bg-rose-50 text-rose-700 border-rose-200/80 shadow-sm',
                dotClass: 'bg-rose-500',
            };
        default:
            return {
                label: props.status,
                icon: ClockIcon,
                classes: 'bg-slate-100 text-slate-700 border-slate-200',
                dotClass: 'bg-slate-400',
            };
    }
});

const sizeClasses = computed(() => {
    switch (props.size) {
        case 'sm':
            return 'px-2 py-0.5 text-xs gap-1';
        case 'lg':
            return 'px-3 py-1.5 text-sm gap-1.5';
        default:
            return 'px-2.5 py-1 text-xs gap-1.5';
    }
});
</script>

<template>
    <span
        :class="[config.classes, sizeClasses]"
        class="inline-flex items-center font-medium rounded-full border transition-colors"
    >
        <component :is="config.icon" class="w-3.5 h-3.5 shrink-0 opacity-80" aria-hidden="true" />
        <span>{{ config.label }}</span>
    </span>
</template>
