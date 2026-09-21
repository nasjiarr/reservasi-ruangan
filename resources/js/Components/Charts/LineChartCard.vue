<script setup>
import { computed } from 'vue';
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    Title,
    Tooltip,
    Legend,
    Filler,
} from 'chart.js';
import { Line } from 'vue-chartjs';
import Card from '@/Components/Card.vue';

ChartJS.register(
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    Title,
    Tooltip,
    Legend,
    Filler
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
});

const chartOptions = computed(() => ({
    responsive: true,
    maintainAspectRatio: false,
    interaction: {
        mode: 'index',
        intersect: false,
    },
    plugins: {
        legend: {
            display: false,
        },
        tooltip: {
            backgroundColor: '#0F172A', // Deep Slate
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
            displayColors: false,
            callbacks: {
                label: (context) => ` ${context.parsed.y} Pemesanan`,
            },
        },
    },
    scales: {
        x: {
            grid: {
                display: false,
            },
            ticks: {
                color: '#64748B',
                font: {
                    family: 'Inter, sans-serif',
                    size: 11,
                },
                maxRotation: 0,
                autoSkip: true,
                maxTicksLimit: 10,
            },
        },
        y: {
            grid: {
                color: '#F1F5F9',
            },
            ticks: {
                color: '#64748B',
                font: {
                    family: 'Inter, sans-serif',
                    size: 11,
                },
                precision: 0,
                stepSize: 1,
            },
            beginAtZero: true,
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
            <div class="h-64 sm:h-72 w-full">
                <Line :data="data" :options="chartOptions" />
            </div>
        </div>
    </Card>
</template>

