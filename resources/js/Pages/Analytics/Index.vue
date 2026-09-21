<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import Card from '@/Components/Card.vue';
import LoadingSkeleton from '@/Components/LoadingSkeleton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import LineChartCard from '@/Components/Charts/LineChartCard.vue';
import BarChartCard from '@/Components/Charts/BarChartCard.vue';
import DoughnutChartCard from '@/Components/Charts/DoughnutChartCard.vue';
import UsageHeatmap from '@/Components/Charts/UsageHeatmap.vue';
import {
    CalendarDaysIcon,
    BuildingOffice2Icon,
    ClockIcon,
    CheckBadgeIcon,
    ArrowTrendingUpIcon,
    ArrowTrendingDownIcon,
    DocumentChartBarIcon,
    FunnelIcon,
    ArrowPathIcon,
    SparklesIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
    filters: {
        type: Object,
        required: true,
    },
    summary: {
        type: Object,
        required: true,
    },
    lineChart: {
        type: Object,
        required: true,
    },
    barChartTopRooms: {
        type: Object,
        required: true,
    },
    doughnutStatus: {
        type: Object,
        required: true,
    },
    horizontalBarHours: {
        type: Object,
        required: true,
    },
    heatmap: {
        type: Object,
        required: true,
    },
});

const startDate = ref(props.filters.start_date);
const endDate = ref(props.filters.end_date);
const isLoading = ref(false);

const applyFilter = () => {
    isLoading.value = true;
    router.get(
        route('analytics.index'),
        {
            start_date: startDate.value,
            end_date: endDate.value,
        },
        {
            preserveState: true,
            preserveScroll: true,
            only: [
                'filters',
                'summary',
                'lineChart',
                'barChartTopRooms',
                'doughnutStatus',
                'horizontalBarHours',
                'heatmap',
            ],
            onFinish: () => {
                isLoading.value = false;
            },
        }
    );
};

const resetFilter = () => {
    startDate.value = '';
    endDate.value = '';
    applyFilter();
};
</script>

<template>
    <Head title="Analytics Dashboard" />

    <AuthenticatedLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <!-- Header Section -->
            <PageHeader
                title="Analytics & Utilisasi Ruangan"
                description="Visualisasi performa pemakaian ruangan rapat, tren booking harian, dan metrik rapat secara real-time."
                :badge="`${filters.start_date} s/d ${filters.end_date}`"
            >
                <template #actions>
                    <!-- Link ke Halaman Laporan (Export Excel/PDF) -->
                    <Link :href="route('reports.index')">
                        <SecondaryButton class="gap-2 shadow-xs" title="Buka Halaman Laporan dan Ekspor Data">
                            <DocumentChartBarIcon class="w-4 h-4 text-slate-500" />
                            <span>Lihat sebagai Laporan</span>
                        </SecondaryButton>
                    </Link>
                </template>
            </PageHeader>

            <!-- Filter Bar Card -->
            <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-card flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-2 text-xs font-semibold text-slate-600">
                    <FunnelIcon class="w-4 h-4 text-brand-600" />
                    <span>Rentang Periode Analisis:</span>
                </div>

                <form @submit.prevent="applyFilter" class="flex flex-wrap items-center gap-2.5">
                    <div class="flex items-center gap-2">
                        <input
                            type="date"
                            v-model="startDate"
                            class="px-3 py-1.5 text-xs rounded-xl border border-slate-300 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 text-slate-700 shadow-xs"
                            required
                        />
                        <span class="text-xs text-slate-400">s/d</span>
                        <input
                            type="date"
                            v-model="endDate"
                            class="px-3 py-1.5 text-xs rounded-xl border border-slate-300 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 text-slate-700 shadow-xs"
                            required
                        />
                    </div>

                    <PrimaryButton
                        type="submit"
                        :disabled="isLoading"
                        class="!px-3.5 !py-1.5 !text-xs gap-1.5 shadow-xs"
                    >
                        <ArrowPathIcon v-if="isLoading" class="w-3.5 h-3.5 animate-spin" />
                        <span>{{ isLoading ? 'Memuat...' : 'Terapkan Filter' }}</span>
                    </PrimaryButton>
                </form>
            </div>

            <!-- Loading State Skeletons -->
            <div v-if="isLoading" class="space-y-6">
                <LoadingSkeleton type="cards" :count="4" />
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <LoadingSkeleton type="table" />
                    <LoadingSkeleton type="table" />
                </div>
            </div>

            <!-- Analytics Dashboard Content -->
            <div v-else class="space-y-6">
                <!-- 1. Top Row: 4 KPI Summary Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                    <!-- KPI 1: Total Reservasi -->
                    <Card class="hoverable">
                        <div class="flex items-start justify-between">
                            <div class="space-y-1">
                                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">
                                    Total Reservasi
                                </span>
                                <div class="font-heading font-extrabold text-2xl sm:text-3xl text-slate-900">
                                    {{ summary.total_reservations.value }}
                                </div>
                            </div>
                            <div class="w-10 h-10 rounded-xl bg-teal-50 border border-teal-200/80 flex items-center justify-center text-brand-600 shrink-0">
                                <CalendarDaysIcon class="w-5 h-5" />
                            </div>
                        </div>
                        <div class="mt-3 pt-3 border-t border-slate-100 flex items-center text-xs">
                            <span
                                :class="summary.total_reservations.change >= 0 ? 'text-emerald-600 bg-emerald-50 border-emerald-200' : 'text-rose-600 bg-rose-50 border-rose-200'"
                                class="inline-flex items-center gap-0.5 px-2 py-0.5 rounded-full border font-bold mr-2 text-[11px]"
                            >
                                <ArrowTrendingUpIcon v-if="summary.total_reservations.change >= 0" class="w-3 h-3 stroke-2" />
                                <ArrowTrendingDownIcon v-else class="w-3 h-3 stroke-2" />
                                {{ summary.total_reservations.change > 0 ? '+' : '' }}{{ summary.total_reservations.change }}%
                            </span>
                            <span class="text-slate-400">vs periode lalu</span>
                        </div>
                    </Card>

                    <!-- KPI 2: Tingkat Persetujuan (Approval Rate) -->
                    <Card class="hoverable">
                        <div class="flex items-start justify-between">
                            <div class="space-y-1">
                                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">
                                    Tingkat Approval
                                </span>
                                <div class="font-heading font-extrabold text-2xl sm:text-3xl text-slate-900">
                                    {{ summary.approval_rate.value }}<span class="text-base font-semibold text-slate-500">%</span>
                                </div>
                            </div>
                            <div class="w-10 h-10 rounded-xl bg-emerald-50 border border-emerald-200/80 flex items-center justify-center text-emerald-600 shrink-0">
                                <CheckBadgeIcon class="w-5 h-5" />
                            </div>
                        </div>
                        <div class="mt-3 pt-3 border-t border-slate-100 flex items-center text-xs">
                            <span
                                :class="summary.approval_rate.change >= 0 ? 'text-emerald-600 bg-emerald-50 border-emerald-200' : 'text-rose-600 bg-rose-50 border-rose-200'"
                                class="inline-flex items-center gap-0.5 px-2 py-0.5 rounded-full border font-bold mr-2 text-[11px]"
                            >
                                <ArrowTrendingUpIcon v-if="summary.approval_rate.change >= 0" class="w-3 h-3 stroke-2" />
                                <ArrowTrendingDownIcon v-else class="w-3 h-3 stroke-2" />
                                {{ summary.approval_rate.change > 0 ? '+' : '' }}{{ summary.approval_rate.change }}%
                            </span>
                            <span class="text-slate-400">vs periode lalu</span>
                        </div>
                    </Card>

                    <!-- KPI 3: Ruangan Paling Sering Dipakai -->
                    <Card class="hoverable">
                        <div class="flex items-start justify-between">
                            <div class="space-y-1 overflow-hidden pr-2">
                                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">
                                    Ruangan Terfavorit
                                </span>
                                <div class="font-heading font-bold text-lg text-slate-900 truncate" :title="summary.most_used_room.name">
                                    {{ summary.most_used_room.name }}
                                </div>
                            </div>
                            <div class="w-10 h-10 rounded-xl bg-amber-50 border border-amber-200/80 flex items-center justify-center text-amber-600 shrink-0">
                                <BuildingOffice2Icon class="w-5 h-5" />
                            </div>
                        </div>
                        <div class="mt-3 pt-3 border-t border-slate-100 flex items-center text-xs text-slate-600">
                            <span class="font-bold text-brand-700 bg-brand-50 px-2 py-0.5 rounded-md border border-brand-200 mr-2 text-[11px]">
                                {{ summary.most_used_room.count }} booking
                            </span>
                            <span class="text-slate-400">paling diminati</span>
                        </div>
                    </Card>

                    <!-- KPI 4: Rata-rata Durasi Rapat -->
                    <Card class="hoverable">
                        <div class="flex items-start justify-between">
                            <div class="space-y-1">
                                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">
                                    Rata-Rata Durasi
                                </span>
                                <div class="font-heading font-extrabold text-2xl sm:text-3xl text-slate-900">
                                    {{ summary.avg_duration.value }} <span class="text-base font-normal text-slate-500">menit</span>
                                </div>
                            </div>
                            <div class="w-10 h-10 rounded-xl bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-700 shrink-0">
                                <ClockIcon class="w-5 h-5" />
                            </div>
                        </div>
                        <div class="mt-3 pt-3 border-t border-slate-100 flex items-center text-xs">
                            <span
                                :class="summary.avg_duration.change >= 0 ? 'text-slate-700 bg-slate-50 border-slate-200' : 'text-slate-600 bg-slate-50 border-slate-200'"
                                class="inline-flex items-center gap-0.5 px-2 py-0.5 rounded-full border font-semibold mr-2 text-[11px]"
                            >
                                {{ summary.avg_duration.change > 0 ? '+' : '' }}{{ summary.avg_duration.change }}%
                            </span>
                            <span class="text-slate-400">vs periode lalu</span>
                        </div>
                    </Card>
                </div>

                <!-- 2. Middle Row 1: Line Chart & Top 5 Rooms Bar Chart -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Line Chart: Tren Harian -->
                    <LineChartCard
                        :data="lineChart"
                        title="Tren Reservasi Harian"
                        subtitle="Jumlah pengajuan peminjaman ruangan yang dibuat per hari."
                    >
                        <template #badge>
                            <span class="inline-flex items-center gap-1 text-xs font-semibold px-2.5 py-1 rounded-full bg-teal-50 text-teal-800 border border-teal-200/60">
                                <SparklesIcon class="w-3 h-3 text-teal-600" />
                                <span>Real-time Insight</span>
                            </span>
                        </template>
                    </LineChartCard>

                    <!-- Bar Chart: Top 5 Ruangan -->
                    <BarChartCard
                        :data="barChartTopRooms"
                        title="Top 5 Ruangan Terpopuler"
                        subtitle="Peringkat ruangan berdasarkan total frekuensi penggunaan."
                        orientation="vertical"
                    />
                </div>

                <!-- 3. Middle Row 2: Status Doughnut & Busy Hours Horizontal Bar -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Doughnut Chart: Distribusi Status -->
                    <DoughnutChartCard
                        :data="doughnutStatus"
                        title="Distribusi Status Reservasi"
                        subtitle="Proporsi permohonan disetujui, menunggu, ditolak, dan dibatalkan."
                        :distribution="doughnutStatus.distribution"
                        :total="doughnutStatus.total"
                    />

                    <!-- Horizontal Bar Chart: Jam Paling Sibuk -->
                    <BarChartCard
                        :data="horizontalBarHours"
                        title="Jam Paling Sibuk (08:00 - 18:00)"
                        subtitle="Frekuensi rapat dimulai pada rentang jam kerja operasional."
                        orientation="horizontal"
                    />
                </div>

                <!-- 4. Bottom Row: Usage Heatmap Matrix -->
                <UsageHeatmap
                    :matrix="heatmap.matrix"
                    :max-count="heatmap.max_count"
                    title="Peta Intensitas Pemakaian Ruangan (Heatmap Mingguan)"
                    subtitle="Analisis waktu terpadat berdasarkan hari dalam seminggu (Senin–Minggu) vs jam rapat."
                />
            </div>
        </div>
    </AuthenticatedLayout>
</template>

