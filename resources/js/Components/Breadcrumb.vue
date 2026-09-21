<script setup>
import { computed } from 'vue';
import { usePage, Link } from '@inertiajs/vue3';
import { ChevronRightIcon } from '@heroicons/vue/20/solid';

const page = usePage();

const breadcrumbItems = computed(() => {
    const raw = page.props.breadcrumbs || [];
    const override = page.props.breadcrumbOverride;

    // Fallback if breadcrumbs array is empty
    if (!raw || raw.length === 0) {
        return [{ label: 'Dashboard', href: null }];
    }

    // If no dynamic override, return raw route breadcrumbs
    if (!override) {
        return raw;
    }

    // Avoid duplicate override if already present at the end
    if (raw.length > 0 && raw[raw.length - 1].label === override) {
        return raw;
    }

    // Append dynamic data override as the active leaf
    return [
        ...raw,
        { label: String(override), href: null },
    ];
});

const isSingleLevel = computed(() => breadcrumbItems.value.length <= 1);
</script>

<template>
    <!-- Single-level page (e.g. Dashboard or Index without sub-path) -->
    <div v-if="isSingleLevel" class="flex items-center">
        <h1 class="font-heading font-bold text-slate-900 text-sm sm:text-base tracking-tight m-0 p-0">
            {{ breadcrumbItems[0]?.label || 'Dashboard' }}
        </h1>
    </div>

    <!-- Multi-level hierarchical breadcrumb -->
    <nav v-else aria-label="Breadcrumb" class="flex items-center space-x-1.5 sm:space-x-2 text-xs sm:text-sm font-medium min-w-0">
        <template v-for="(item, index) in breadcrumbItems" :key="index">
            <!-- Chevron separator between items -->
            <ChevronRightIcon
                v-if="index > 0"
                class="w-3.5 h-3.5 text-slate-400 shrink-0 select-none"
                aria-hidden="true"
            />

            <!-- Navigable ancestor item with link -->
            <Link
                v-if="item.href && index < breadcrumbItems.length - 1"
                :href="item.href"
                class="text-slate-500 hover:text-brand-600 hover:underline transition-colors max-w-[120px] sm:max-w-[200px] truncate"
                :title="item.label"
            >
                {{ item.label }}
            </Link>

            <!-- Active / current leaf item (non-clickable) -->
            <span
                v-else-if="index === breadcrumbItems.length - 1"
                class="font-heading font-bold text-slate-900 truncate max-w-[150px] sm:max-w-[280px]"
                :title="item.label"
                aria-current="page"
            >
                {{ item.label }}
            </span>

            <!-- Intermediate item without link (e.g. static action category) -->
            <span
                v-else
                class="text-slate-500 font-medium truncate max-w-[120px] sm:max-w-[180px]"
                :title="item.label"
            >
                {{ item.label }}
            </span>
        </template>
    </nav>
</template>

