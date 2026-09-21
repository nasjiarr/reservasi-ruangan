<script setup>
defineProps({
    type: {
        type: String,
        default: 'table', // 'table', 'card', 'list'
    },
    rows: {
        type: Number,
        default: 4,
    },
});
</script>

<template>
    <div class="animate-pulse space-y-4">
        <!-- Table Skeleton -->
        <div v-if="type === 'table'" class="divide-y divide-slate-100">
            <div
                v-for="i in rows"
                :key="i"
                class="py-4 flex items-center justify-between gap-4"
            >
                <div class="flex items-center gap-3 w-1/3">
                    <div class="w-10 h-10 rounded-lg bg-slate-200 shrink-0" />
                    <div class="space-y-2 w-full">
                        <div class="h-4 bg-slate-200 rounded w-3/4" />
                        <div class="h-3 bg-slate-100 rounded w-1/2" />
                    </div>
                </div>
                <div class="h-4 bg-slate-200 rounded w-1/6" />
                <div class="h-4 bg-slate-200 rounded w-1/6" />
                <div class="h-6 bg-slate-200 rounded-full w-20" />
                <div class="h-8 bg-slate-200 rounded-lg w-16" />
            </div>
        </div>

        <!-- Card Grid Skeleton -->
        <div v-else-if="type === 'card'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div
                v-for="i in rows"
                :key="i"
                class="bg-white rounded-xl border border-slate-200 p-5 space-y-4"
            >
                <div class="h-40 bg-slate-200 rounded-lg w-full" />
                <div class="h-5 bg-slate-200 rounded w-2/3" />
                <div class="space-y-2">
                    <div class="h-3 bg-slate-100 rounded w-full" />
                    <div class="h-3 bg-slate-100 rounded w-4/5" />
                </div>
                <div class="pt-2 flex justify-between items-center border-t border-slate-100">
                    <div class="h-6 bg-slate-200 rounded-full w-20" />
                    <div class="h-8 bg-slate-200 rounded-lg w-20" />
                </div>
            </div>
        </div>

        <!-- List Skeleton -->
        <div v-else class="space-y-3">
            <div
                v-for="i in rows"
                :key="i"
                class="p-4 rounded-xl border border-slate-200 bg-white flex items-center justify-between"
            >
                <div class="space-y-2 w-1/2">
                    <div class="h-4 bg-slate-200 rounded w-3/4" />
                    <div class="h-3 bg-slate-100 rounded w-1/2" />
                </div>
                <div class="h-8 bg-slate-200 rounded-lg w-24" />
            </div>
        </div>
    </div>
</template>

