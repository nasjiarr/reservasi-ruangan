<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import { format, parseISO } from 'date-fns';
import { id } from 'date-fns/locale';

const props = defineProps({
    reservations: {
        type: [Array, Object],
        default: () => [],
    },
    rooms: {
        type: Array,
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({ start_date: '', end_date: '', room_id: '' }),
    },
    analytics: {
        type: Object,
        default: () => ({ total_this_month: 0, top_rooms: [] }),
    },
});

// Filter state
const formFilters = ref({
    start_date: props.filters.start_date || '',
    end_date: props.filters.end_date || '',
    room_id: props.filters.room_id || '',
});

const applyFilter = () => {
    router.get(route('reports.index'), formFilters.value, {
        preserveState: true,
        preserveScroll: true,
    });
};

const resetFilter = () => {
    formFilters.value = {
        start_date: '',
        end_date: '',
        room_id: '',
    };
    applyFilter();
};

// URL query string untuk download export native
const exportQueryString = computed(() => {
    const params = new URLSearchParams();
    if (formFilters.value.start_date) params.append('start_date', formFilters.value.start_date);
    if (formFilters.value.end_date) params.append('end_date', formFilters.value.end_date);
    if (formFilters.value.room_id) params.append('room_id', formFilters.value.room_id);
    return params.toString() ? `?${params.toString()}` : '';
});

const excelExportUrl = computed(() => {
    return route('reports.export-excel') + exportQueryString.value;
});

const pdfExportUrl = computed(() => {
    return route('reports.export-pdf') + exportQueryString.value;
});

const reservationList = computed(() => {
    if (Array.isArray(props.reservations)) {
        return props.reservations;
    }
    return props.reservations?.data || [];
});

const paginationLinks = computed(() => {
    return props.reservations?.links || [];
});

const formatDate = (dateStr) => {
    if (!dateStr) return '-';
    try {
        const date = typeof dateStr === 'string' ? parseISO(dateStr) : new Date(dateStr);
        return format(date, 'dd MMM yyyy, HH:mm', { locale: id });
    } catch {
        return dateStr;
    }
};

const getStatusBadgeClass = (status) => {
    switch (status) {
        case 'approved':
            return 'bg-emerald-100 text-emerald-800 border-emerald-300';
        case 'pending':
            return 'bg-amber-100 text-amber-800 border-amber-300';
        case 'rejected':
            return 'bg-red-100 text-red-800 border-red-300';
        case 'cancelled':
            return 'bg-gray-100 text-gray-800 border-gray-300';
        default:
            return 'bg-blue-100 text-blue-800 border-blue-300';
    }
};
</script>

<template>
    <Head title="Laporan Penggunaan Ruangan" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Laporan & Rekapitulasi Penggunaan Ruangan
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
                <!-- Analytics Summary Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Total Reservasi Bulan Ini -->
                    <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100 flex items-center space-x-4">
                        <div class="p-3 bg-indigo-50 text-indigo-600 rounded-full">
                            <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div>
                            <span class="text-xs uppercase tracking-wider text-gray-500 font-semibold block">Total Bulan Ini</span>
                            <span class="text-3xl font-extrabold text-gray-900">{{ analytics.total_this_month }}</span>
                            <span class="text-xs text-gray-400 block mt-0.5">Reservasi diajukan</span>
                        </div>
                    </div>

                    <!-- Top Ruangan Card (Span 2 cols on tablet/desktop) -->
                    <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100 md:col-span-2">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="text-xs uppercase tracking-wider text-gray-500 font-semibold">
                                Top 3 Ruangan Paling Sering Digunakan
                            </h3>
                            <span class="text-xs text-indigo-600 font-medium">Berdasarkan Total Reservasi</span>
                        </div>

                        <div v-if="!analytics.top_rooms || analytics.top_rooms.length === 0" class="text-xs text-gray-400 py-2">
                            Belum ada data riwayat pemesanan ruangan.
                        </div>

                        <div v-else class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div
                                v-for="(room, index) in analytics.top_rooms"
                                :key="room.id"
                                class="p-3 rounded-lg border bg-gray-50 flex items-start space-x-3"
                            >
                                <span class="flex items-center justify-center w-7 h-7 rounded-full bg-indigo-600 text-white font-bold text-xs shrink-0 mt-0.5">
                                    {{ index + 1 }}
                                </span>
                                <div class="min-w-0">
                                    <div class="font-bold text-sm text-gray-900 truncate">{{ room.name }}</div>
                                    <div class="text-xs text-gray-500 truncate">{{ room.location || 'Kapasitas: ' + room.capacity }}</div>
                                    <div class="text-xs text-indigo-600 font-semibold mt-1">
                                        {{ room.reservations_count }} kali digunakan
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filter & Export Toolbar -->
                <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100">
                    <form @submit.prevent="applyFilter" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                        <div>
                            <InputLabel for="start_date" value="Dari Tanggal" />
                            <TextInput
                                id="start_date"
                                v-model="formFilters.start_date"
                                type="date"
                                class="mt-1 block w-full"
                            />
                        </div>

                        <div>
                            <InputLabel for="end_date" value="Sampai Tanggal" />
                            <TextInput
                                id="end_date"
                                v-model="formFilters.end_date"
                                type="date"
                                class="mt-1 block w-full"
                            />
                        </div>

                        <div>
                            <InputLabel for="room_id" value="Pilih Ruangan" />
                            <select
                                id="room_id"
                                v-model="formFilters.room_id"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm"
                            >
                                <option value="">Semua Ruangan</option>
                                <option v-for="room in rooms" :key="room.id" :value="room.id">
                                    {{ room.name }}
                                </option>
                            </select>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex flex-wrap items-center gap-2">
                            <PrimaryButton type="submit" class="!py-2.5">
                                Filter
                            </PrimaryButton>
                            <SecondaryButton type="button" @click="resetFilter" class="!py-2.5">
                                Reset
                            </SecondaryButton>
                        </div>
                    </form>

                    <!-- Native Export Buttons -->
                    <div class="mt-6 pt-4 border-t border-gray-100 flex flex-wrap items-center justify-between gap-3">
                        <span class="text-xs text-gray-500">
                            Menampilkan preview data reservasi sesuai filter yang dipilih.
                        </span>
                        <div class="flex items-center space-x-3">
                            <!-- Tombol Download Excel -->
                            <a
                                :href="excelExportUrl"
                                class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-md text-xs font-bold uppercase tracking-wider transition shadow-sm"
                                download
                            >
                                <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                Export Excel (.xlsx)
                            </a>

                            <!-- Tombol Download PDF -->
                            <a
                                :href="pdfExportUrl"
                                class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-md text-xs font-bold uppercase tracking-wider transition shadow-sm"
                                download
                            >
                                <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                                Export PDF (.pdf)
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Preview Table -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-sm font-semibold text-gray-700 mb-4">
                            Preview Data Reservasi ({{ reservationList.length }} baris)
                        </h3>

                        <div v-if="reservationList.length === 0" class="text-center py-10 text-gray-500 text-sm">
                            Tidak ada data reservasi yang sesuai dengan kriteria filter.
                        </div>

                        <div v-else class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ruangan</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Judul Kegiatan</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pemesan</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Mulai</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Selesai</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Check-In</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    <tr v-for="res in reservationList" :key="res.id" class="hover:bg-gray-50">
                                        <td class="px-4 py-3 whitespace-nowrap font-medium text-gray-900">
                                            {{ res.room?.name || '-' }}
                                        </td>
                                        <td class="px-4 py-3 text-gray-800">
                                            {{ res.title }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-gray-600">
                                            {{ res.user?.name || '-' }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-gray-600">
                                            {{ formatDate(res.start_time) }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-gray-600">
                                            {{ formatDate(res.end_time) }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <span
                                                :class="getStatusBadgeClass(res.status)"
                                                class="px-2 py-0.5 inline-flex text-xs leading-4 font-semibold rounded-full border"
                                            >
                                                {{ res.status }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-xs text-gray-500">
                                            <span v-if="res.check_in?.checked_in_at" class="text-emerald-700 font-semibold">
                                                {{ formatDate(res.check_in.checked_in_at) }}
                                            </span>
                                            <span v-else class="text-gray-400 italic">Belum</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination Links -->
                        <div v-if="paginationLinks.length > 3" class="mt-6 flex justify-center">
                            <div class="flex flex-wrap -mb-1">
                                <template v-for="(link, key) in paginationLinks" :key="key">
                                    <div
                                        v-if="link.url === null"
                                        class="mr-1 mb-1 px-3 py-2 text-sm leading-4 text-gray-400 border rounded"
                                        v-html="link.label"
                                    />
                                    <Link
                                        v-else
                                        class="mr-1 mb-1 px-3 py-2 text-sm leading-4 border rounded hover:bg-gray-100 focus:border-indigo-500 focus:text-indigo-500"
                                        :class="{ 'bg-indigo-600 text-white hover:bg-indigo-700': link.active }"
                                        :href="link.url"
                                        v-html="link.label"
                                    />
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

