<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const props = defineProps({
    href: {
        type: String,
        required: true,
    },
    icon: {
        type: [Object, Function],
        required: true,
    },
    label: {
        type: String,
        required: true,
    },
    active: {
        type: Boolean,
        default: null,
    },
    collapsed: {
        type: Boolean,
        default: false,
    },
    badge: {
        type: [String, Number],
        default: null,
    },
});

const emit = defineEmits(['click']);

const page = usePage();

const isActive = computed(() => {
    if (props.active !== null) {
        return props.active;
    }
    const currentUrl = page.url;
    try {
        const targetUrl = new URL(props.href, window.location.origin).pathname;
        if (targetUrl === '/dashboard') {
            return currentUrl === '/dashboard' || currentUrl === '/';
        }
        return currentUrl === targetUrl || (targetUrl !== '/' && currentUrl.startsWith(targetUrl));
    } catch {
        return false;
    }
});
</script>

<template>
    <Link
        :href="href"
        @click="emit('click')"
        :title="collapsed ? label : undefined"
        class="group relative flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm transition-all duration-150 select-none"
        :class="[
            isActive
                ? 'bg-brand-50/90 text-brand-800 font-bold border-l-4 border-brand-600 shadow-2xs'
                : 'text-slate-600 hover:bg-slate-100/90 hover:text-slate-900 font-medium border-l-4 border-transparent',
            collapsed ? 'justify-center px-2' : ''
        ]"
    >
        <!-- Icon -->
        <component
            :is="icon"
            class="w-5 h-5 shrink-0 transition-colors duration-150"
            :class="[
                isActive
                    ? 'text-brand-600'
                    : 'text-slate-400 group-hover:text-slate-700'
            ]"
        />

        <!-- Text Label (Hidden if collapsed) -->
        <span
            v-if="!collapsed"
            class="truncate tracking-tight"
        >
            {{ label }}
        </span>

        <!-- Optional Badge (Hidden if collapsed) -->
        <span
            v-if="!collapsed && badge"
            class="ml-auto text-[11px] font-bold px-2 py-0.5 rounded-full"
            :class="[
                isActive
                    ? 'bg-brand-200/80 text-brand-900'
                    : 'bg-slate-200/80 text-slate-700 group-hover:bg-slate-300'
            ]"
        >
            {{ badge }}
        </span>

        <!-- Floating Tooltip when Collapsed -->
        <div
            v-if="collapsed"
            class="pointer-events-none absolute left-full ml-3 hidden group-hover:flex items-center z-50 whitespace-nowrap rounded-lg bg-slate-900 px-2.5 py-1 text-xs font-semibold text-white shadow-xl opacity-0 group-hover:opacity-100 transition-opacity duration-150"
        >
            <span>{{ label }}</span>
            <span v-if="badge" class="ml-1.5 px-1.5 py-0.2 rounded-full bg-brand-500 text-[10px]">
                {{ badge }}
            </span>
            <!-- Little tooltip arrow -->
            <div class="absolute -left-1 top-1/2 -translate-y-1/2 border-y-4 border-y-transparent border-r-4 border-r-slate-900"></div>
        </div>
    </Link>
</template>

