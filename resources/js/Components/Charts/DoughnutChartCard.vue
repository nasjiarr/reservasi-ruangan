<script setup>
import { computed } from 'vue';
import {
    Chart as ChartJS,
    ArcElement,
    Tooltip,
    Legend,
} from 'chart.js';
import { Doughnut } from 'vue-chartjs';
import Card from '@/Components/Card.vue';

ChartJS.register(
    ArcElement,
    Tooltip,
    Legend
);

const props = defineProps({
    data: {
        type: Object,
        required: true,
    },
    title: {
        type: String,
        required: true,
    },
    subtitle: {
        type: String,
        default: '',
    },
    distribution: {
        type: Array,
        default: () => [],
    },
    total: {
        type: Number,
        default: 0,
    },
});

const chartOptions = computed(() => ({
    responsive: true,
    maintainAspectRatio: false,
    cutout: '72%',
    plugins: {
        legend: {
            display: false, // Kita gunakan legend custom di samping
        },
        tooltip: {
            backgroundColor: '#0F172A',
            titleFont: {
                family: "'Plus Jakarta Sans', sans-serif",
                size: 12,
                weight: '600',
            },
            bodyFont: {
                family: 'Inter, sans-serif',
                size: 12,
            },
            padding: 10,
            cornerRadius: 8,
            displayColors: true,
            boxWidth: 8,
            boxHeight: 8,
            usePointStyle: true,
            callbacks: {
                label: (context) => ` ${context.label}: ${context.parsed} (${props.total > 0 ? Math.round((context.parsed / props.total) * 100) : 0}%)`,
            },
        },
    },
}));
</script>

<template>
    <Card :no-padding="true">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="font-heading font-bold text-base text-slate-900">
                    {{ title }}
                </h3>
                <p v-if="subtitle" class="text-xs text-slate-400 mt-0.5">
                    {{ subtitle }}
                </p>
            </div>
            <slot name="badge" />
        </div>

        <div class="p-6">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-6 min-h-[16rem]">
                <!-- Doughnut Canvas with Center Counter -->
                <div class="relative w-48 h-48 sm:w-52 sm:h-52 shrink-0">
                    <Doughnut :data="data" :options="chartOptions" />
                    <!-- Center Overlay -->
                    <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none text-center">
                        <span class="text-xs text-slate-400 uppercase font-semibold tracking-wider">Total</span>
                        <span class="font-heading font-extrabold text-2xl text-slate-900">
                            {{ total }}
                        </span>
                    </div>
                </div>

                <!-- Custom Legend List -->
                <div class="w-full sm:flex-1 space-y-2.5">
                    <div
                        v-for="item in distribution"
                        :key="item.status"
                        class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50/80 border border-slate-100 hover:border-slate-200 transition-colors text-xs"
                    >
                        <div class="flex items-center gap-2.5">
                            <span
                                class="w-3 h-3 rounded-full shrink-0 shadow-xs"
                                :style="{ backgroundColor: item.color }"
                            />
                            <span class="font-medium text-slate-700">{{ item.label }}</span>
                        </div>
                        <div class="flex items-center gap-2 font-semibold">
                            <span class="text-slate-800">{{ item.count }}</span>
                            <span class="px-1.5 py-0.5 rounded-md bg-white border border-slate-200/80 text-[10px] text-slate-500 min-w-[3rem] text-center">
                                {{ item.percentage }}%
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </Card>
</template>

