<script setup>
import { computed } from 'vue';
import Card from '@/Components/Card.vue';

const props = defineProps({
    matrix: {
        type: Array,
        required: true,
    },
    maxCount: {
        type: Number,
        default: 1,
    },
    title: {
        type: String,
        default: 'Peta Intensitas Pemakaian Ruangan (Heatmap)',
    },
    subtitle: {
        type: String,
        default: 'Distribusi frekuensi rapat berdasarkan hari dalam seminggu dan jam operasional.',
    },
});

const getIntensityClass = (count) => {
    if (!count || count === 0) {
        return 'bg-slate-50 text-slate-300 border-slate-100 hover:border-slate-300';
    }
    const max = props.maxCount > 0 ? props.maxCount : 1;
    const ratio = count / max;

    if (ratio <= 0.25) {
        return 'bg-teal-50 text-teal-700 border-teal-100/80 hover:border-teal-300 font-medium';
    } else if (ratio <= 0.5) {
        return 'bg-teal-200/90 text-teal-900 border-teal-200 hover:border-teal-400 font-semibold';
    } else if (ratio <= 0.75) {
        return 'bg-teal-400 text-white border-teal-400 hover:border-teal-500 font-bold shadow-xs';
    } else {
        return 'bg-teal-600 text-white border-teal-600 hover:border-teal-700 font-bold shadow-xs';
    }
};

const hoursHeader = computed(() => {
    if (props.matrix.length > 0 && props.matrix[0].hours) {
        return props.matrix[0].hours.map((h) => h.hour);
    }
    return [];
});
</script>

<template>
    <Card :no-padding="true">
        <div class="px-6 py-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div>
                <h3 class="font-heading font-bold text-base text-slate-900">
                    {{ title }}
                </h3>
                <p v-if="subtitle" class="text-xs text-slate-400 mt-0.5">
                    {{ subtitle }}
                </p>
            </div>

            <!-- Heatmap Legend -->
            <div class="flex items-center gap-1.5 text-xs text-slate-500 shrink-0">
                <span class="text-[11px] text-slate-400 font-medium mr-1">Intensitas:</span>
                <span class="px-1.5 py-0.5 rounded text-[10px] bg-slate-50 border border-slate-200 text-slate-400">0</span>
                <span class="px-1.5 py-0.5 rounded text-[10px] bg-teal-50 border border-teal-100 text-teal-700">Rendah</span>
                <span class="px-1.5 py-0.5 rounded text-[10px] bg-teal-200 text-teal-900">Sedang</span>
                <span class="px-1.5 py-0.5 rounded text-[10px] bg-teal-400 text-white font-semibold">Tinggi</span>
                <span class="px-1.5 py-0.5 rounded text-[10px] bg-teal-600 text-white font-bold">Puncak</span>
            </div>
        </div>

        <div class="p-6 overflow-x-auto">
            <div class="min-w-[700px]">
                <table class="w-full border-collapse">
                    <thead>
                        <tr>
                            <th class="w-24 pb-3 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">
                                Hari \ Jam
                            </th>
                            <th
                                v-for="hr in hoursHeader"
                                :key="hr"
                                class="pb-3 text-center text-xs font-semibold text-slate-500"
                            >
                                {{ hr }}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="row in matrix" :key="row.day" class="group">
                            <td class="py-2 pr-3 text-xs font-semibold text-slate-700 whitespace-nowrap">
                                {{ row.day }}
                            </td>
                            <td
                                v-for="item in row.hours"
                                :key="item.hour"
                                class="p-1"
                            >
                                <div
                                    :class="getIntensityClass(item.count)"
                                    class="h-9 w-full rounded-lg border flex items-center justify-center text-xs transition-all duration-150 cursor-pointer relative group/cell"
                                    :title="`${row.day}, ${item.hour} : ${item.count} Pemesanan`"
                                >
                                    <span>{{ item.count > 0 ? item.count : '' }}</span>

                                    <!-- Floating Tooltip on Hover -->
                                    <div class="absolute bottom-full mb-1.5 hidden group-hover/cell:flex flex-col items-center z-30 pointer-events-none">
                                        <div class="bg-slate-900 text-white text-[11px] rounded-lg px-2 py-1 shadow-lg whitespace-nowrap">
                                            {{ row.day }}, {{ item.hour }} &bull; <strong class="text-teal-300">{{ item.count }} booking</strong>
                                        </div>
                                        <div class="w-2 h-2 bg-slate-900 rotate-45 -mt-1"></div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </Card>
</template>

