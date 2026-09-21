<script setup>
import { computed } from 'vue';
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    BarElement,
    Title,
    Tooltip,
    Legend,
} from 'chart.js';
import { Bar } from 'vue-chartjs';
import Card from '@/Components/Card.vue';

ChartJS.register(
    CategoryScale,
    LinearScale,
    BarElement,
    Title,
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
    orientation: {
        type: String,
        default: 'vertical', // 'vertical' | 'horizontal'
    },
});

const isHorizontal = computed(() => props.orientation === 'horizontal');

const chartOptions = computed(() => ({
    responsive: true,
    maintainAspectRatio: false,
    indexAxis: isHorizontal.value ? 'y' : 'x',
    plugins: {
        legend: {
            display: false,
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
            displayColors: false,
            callbacks: {
                label: (context) => {
                    const val = isHorizontal.value ? context.parsed.x : context.parsed.y;
                    return ` ${val} Pemesanan`;
                },
            },
        },
    },
    scales: isHorizontal.value
        ? {
              x: {
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
                  },
                  beginAtZero: true,
              },
              y: {
                  grid: {
                      display: false,
                  },
                  ticks: {
                      color: '#475569',
                      font: {
                          family: 'Inter, sans-serif',
                          size: 11,
                          weight: '500',
                      },
                  },
              },
          }
        : {
              x: {
                  grid: {
                      display: false,
                  },
                  ticks: {
                      color: '#475569',
                      font: {
                          family: 'Inter, sans-serif',
                          size: 11,
                          weight: '500',
                      },
                      maxRotation: 25,
                      minRotation: 0,
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
                <Bar :data="data" :options="chartOptions" />
            </div>
        </div>
    </Card>
</template>

