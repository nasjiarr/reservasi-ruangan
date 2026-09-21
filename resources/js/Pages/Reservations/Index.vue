<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import Modal from '@/Components/Modal.vue';
import { format, parseISO } from 'date-fns';
import { id } from 'date-fns/locale';

const props = defineProps({
    reservations: {
        type: [Array, Object],
        default: () => [],
    },
});

const page = usePage();
const currentUser = computed(() => page.props.auth.user);
const isAdmin = computed(() => currentUser.value?.roles?.includes('admin'));

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

const cancelReservation = (reservation) => {
    if (confirm(`Apakah Anda yakin ingin membatalkan reservasi "${reservation.title}"?`)) {
        router.delete(route('reservations.destroy', reservation.id));
    }
};

const getStatusBadgeClass = (status) => {
    switch (status) {
        case 'pending':
            return 'bg-amber-100 text-amber-800 border-amber-300';
        case 'approved':
            return 'bg-emerald-100 text-emerald-800 border-emerald-300';
        case 'rejected':
            return 'bg-red-100 text-red-800 border-red-300';
        case 'cancelled':
            return 'bg-gray-100 text-gray-800 border-gray-300';
        default:
            return 'bg-blue-100 text-blue-800 border-blue-300';
    }
};

const formatStatus = (status) => {
    switch (status) {
        case 'pending':
            return 'Menunggu Persetujuan';
        case 'approved':
            return 'Disetujui';
        case 'rejected':
            return 'Ditolak';
        case 'cancelled':
            return 'Dibatalkan';
        default:
            return status;
    }
};

// Syarat bisa cancel: milik sendiri dan pending, atau admin
const canCancel = (reservation) => {
    if (reservation.status === 'cancelled') return false;
    if (isAdmin.value) return true;
    return reservation.user_id === currentUser.value?.id && reservation.status === 'pending';
};

// QR Code Modal
const isQrModalOpen = ref(false);
const activeReservation = ref(null);

const openQrModal = (reservation) => {
    activeReservation.value = reservation;
    isQrModalOpen.value = true;
};

const closeQrModal = () => {
    isQrModalOpen.value = false;
    activeReservation.value = null;
};
</script>

<template>
    <Head title="Daftar Reservasi" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Daftar Reservasi Ruangan
                </h2>
                <div class="flex items-center space-x-3">
                    <Link :href="route('reservations.calendar')">
                        <span class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Lihat Kalender
                        </span>
                    </Link>
                    <Link :href="route('reservations.create')">
                        <PrimaryButton>+ Booking Ruangan</PrimaryButton>
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div v-if="reservationList.length === 0" class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">Belum ada reservasi</h3>
                            <p class="mt-1 text-sm text-gray-500">Mulai buat pengajuan peminjaman ruangan sekarang.</p>
                            <div class="mt-6">
                                <Link :href="route('reservations.create')">
                                    <PrimaryButton>Booking Sekarang</PrimaryButton>
                                </Link>
                            </div>
                        </div>

                        <div v-else class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Kegiatan / Judul
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Ruangan
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Pemesan
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Waktu Mulai
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Waktu Selesai
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Status
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Aksi
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="res in reservationList" :key="res.id" class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="font-semibold text-gray-900">{{ res.title }}</div>
                                            <div v-if="res.description" class="text-xs text-gray-500 truncate max-w-xs">
                                                {{ res.description }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                            <span class="font-medium">{{ res.room?.name || '-' }}</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                            {{ res.user?.name || '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                            {{ formatDate(res.start_time) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                            {{ formatDate(res.end_time) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span
                                                :class="getStatusBadgeClass(res.status)"
                                                class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full border"
                                            >
                                                {{ formatStatus(res.status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                            <button
                                                v-if="res.status === 'approved'"
                                                type="button"
                                                @click="openQrModal(res)"
                                                class="inline-flex items-center px-2.5 py-1 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-semibold rounded border border-indigo-200 transition"
                                                title="Lihat QR Code Check-in"
                                            >
                                                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                                                </svg>
                                                QR Code
                                            </button>
                                            <DangerButton
                                                v-if="canCancel(res)"
                                                class="!px-3 !py-1 !text-xs"
                                                @click="cancelReservation(res)"
                                            >
                                                Batalkan
                                            </DangerButton>
                                            <span v-else-if="res.status !== 'approved'" class="text-xs text-gray-400">-</span>
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

        <!-- Modal QR Code Check-In -->
        <Modal :show="isQrModalOpen" @close="closeQrModal" max-width="md">
            <div class="p-6 text-center" v-if="activeReservation">
                <div class="flex items-center justify-between pb-3 border-b mb-4">
                    <h3 class="text-lg font-bold text-gray-900">
                        QR Code Check-In Ruangan
                    </h3>
                    <button @click="closeQrModal" class="text-gray-400 hover:text-gray-600 text-xl font-bold">
                        &times;
                    </button>
                </div>

                <div class="mb-4 text-left bg-gray-50 p-3 rounded-lg border border-gray-100">
                    <div class="text-xs font-semibold uppercase tracking-wider text-indigo-600 mb-1">
                        {{ activeReservation.room?.name }}
                    </div>
                    <div class="text-sm font-bold text-gray-900">{{ activeReservation.title }}</div>
                    <div class="text-xs text-gray-600 mt-1">
                        <span>{{ formatDate(activeReservation.start_time) }}</span>
                        <span class="mx-1">&rarr;</span>
                        <span>{{ formatDate(activeReservation.end_time) }}</span>
                    </div>
                </div>

                <!-- QR Code Container -->
                <div class="flex justify-center my-4">
                    <div class="p-3 bg-white border-2 border-indigo-200 rounded-xl shadow-sm inline-block">
                        <img
                            :src="route('reservations.qr-code', activeReservation.id)"
                            alt="QR Code Check-in"
                            class="w-52 h-52 mx-auto object-contain"
                        />
                    </div>
                </div>

                <!-- Info Check-in Status -->
                <div class="mb-4 text-xs">
                    <div v-if="activeReservation.check_in?.checked_in_at" class="inline-flex items-center px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 font-semibold">
                        &check; Sudah Check-In ({{ formatDate(activeReservation.check_in.checked_in_at) }})
                    </div>
                    <div v-else class="text-gray-500">
                        Scan QR ini di depan ruangan menggunakan kamera HP untuk konfirmasi kehadiran (mulai 15 menit sebelum acara).
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <SecondaryButton @click="closeQrModal">
                        Tutup
                    </SecondaryButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>

