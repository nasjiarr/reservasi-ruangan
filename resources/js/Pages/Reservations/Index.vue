<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import PageHeader from '@/Components/PageHeader.vue';
import Card from '@/Components/Card.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import EmptyState from '@/Components/EmptyState.vue';
import Modal from '@/Components/Modal.vue';
import { format, parseISO } from 'date-fns';
import { id } from 'date-fns/locale';
import {
    CalendarDaysIcon,
    PlusIcon,
    QrCodeIcon,
    XMarkIcon,
    BuildingOffice2Icon,
    ClockIcon,
    CheckCircleIcon,
    ClipboardDocumentListIcon,
    XCircleIcon,
} from '@heroicons/vue/24/outline';

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
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <!-- Header Section -->
            <PageHeader
                title="Daftar Reservasi Ruangan"
                description="Kelola dan pantau seluruh pengajuan peminjaman ruangan dan riwayat jadwal acara."
                :badge="`${reservationList.length} Reservasi`"
            >
                <template #actions>
                    <Link :href="route('reservations.calendar')">
                        <SecondaryButton class="gap-2 shadow-xs">
                            <CalendarDaysIcon class="w-4 h-4 text-slate-500" />
                            <span>Lihat Kalender</span>
                        </SecondaryButton>
                    </Link>
                    <Link :href="route('reservations.create')">
                        <PrimaryButton class="gap-2 shadow-sm">
                            <PlusIcon class="w-4 h-4" />
                            <span>Booking Ruangan</span>
                        </PrimaryButton>
                    </Link>
                </template>
            </PageHeader>

            <!-- Empty State -->
            <EmptyState
                v-if="reservationList.length === 0"
                title="Belum Ada Reservasi"
                description="Mulai buat pengajuan peminjaman ruangan untuk kegiatan, rapat, atau acara Anda sekarang."
            >
                <template #icon>
                    <ClipboardDocumentListIcon class="w-7 h-7 text-slate-400" />
                </template>
                <template #action>
                    <Link :href="route('reservations.create')">
                        <PrimaryButton class="gap-2">
                            <PlusIcon class="w-4 h-4" />
                            <span>Booking Sekarang</span>
                        </PrimaryButton>
                    </Link>
                </template>
            </EmptyState>

            <!-- Reservation Table Card -->
            <Card v-else :no-padding="true">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200/80">
                        <thead class="bg-slate-50/80">
                            <tr>
                                <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                    Kegiatan / Judul
                                </th>
                                <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                    Ruangan
                                </th>
                                <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                    Pemesan
                                </th>
                                <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                    Jadwal Waktu
                                </th>
                                <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                    Status
                                </th>
                                <th scope="col" class="px-6 py-3.5 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                    Aksi
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-slate-100">
                            <tr
                                v-for="res in reservationList"
                                :key="res.id"
                                class="hover:bg-slate-50/70 transition-colors"
                            >
                                <!-- Title & Description -->
                                <td class="px-6 py-4">
                                    <div class="font-heading font-bold text-sm text-slate-900 line-clamp-1">
                                        {{ res.title }}
                                    </div>
                                    <div v-if="res.description" class="text-xs text-slate-400 truncate max-w-xs mt-0.5">
                                        {{ res.description }}
                                    </div>
                                </td>

                                <!-- Room Info -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <div class="w-7 h-7 rounded-lg bg-teal-50 border border-teal-200/60 flex items-center justify-center text-brand-600 shrink-0">
                                            <BuildingOffice2Icon class="w-3.5 h-3.5" />
                                        </div>
                                        <span class="font-medium text-slate-800 text-sm">
                                            {{ res.room?.name || '-' }}
                                        </span>
                                    </div>
                                </td>

                                <!-- Requester -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-full bg-slate-100 text-slate-600 font-bold text-[10px] flex items-center justify-center shrink-0">
                                            {{ res.user?.name?.charAt(0).toUpperCase() || '?' }}
                                        </div>
                                        <span class="text-sm text-slate-600">
                                            {{ res.user?.name || '-' }}
                                        </span>
                                    </div>
                                </td>

                                <!-- Schedule -->
                                <td class="px-6 py-4 whitespace-nowrap text-xs text-slate-600">
                                    <div class="flex items-center gap-1.5 text-slate-700 font-medium">
                                        <ClockIcon class="w-3.5 h-3.5 text-slate-400 shrink-0" />
                                        <span>{{ formatDate(res.start_time) }}</span>
                                    </div>
                                    <div class="text-[11px] text-slate-400 pl-5 mt-0.5">
                                        s/d {{ formatDate(res.end_time) }}
                                    </div>
                                </td>

                                <!-- Status Badge -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <StatusBadge :status="res.status" size="sm" />
                                </td>

                                <!-- Actions -->
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <!-- QR Code Button -->
                                        <button
                                            v-if="res.status === 'approved'"
                                            type="button"
                                            @click="openQrModal(res)"
                                            class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-teal-50 hover:bg-teal-100 text-brand-700 border border-teal-200/80 rounded-lg text-xs font-semibold transition shadow-xs"
                                            title="Tampilkan QR Code Check-in"
                                        >
                                            <QrCodeIcon class="w-3.5 h-3.5" />
                                            <span>QR Code</span>
                                        </button>

                                        <!-- Cancel Button -->
                                        <button
                                            v-if="canCancel(res)"
                                            type="button"
                                            @click="cancelReservation(res)"
                                            class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-white hover:bg-rose-50 text-rose-600 hover:text-rose-700 border border-slate-200 hover:border-rose-200 rounded-lg text-xs font-semibold transition shadow-xs"
                                            title="Batalkan Reservasi"
                                        >
                                            <XCircleIcon class="w-3.5 h-3.5" />
                                            <span>Batalkan</span>
                                        </button>

                                        <span v-else-if="res.status !== 'approved'" class="text-xs text-slate-400 px-2">-</span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Footer -->
                <div v-if="paginationLinks.length > 3" class="px-6 py-4 border-t border-slate-100 flex justify-center bg-slate-50/50">
                    <nav class="inline-flex rounded-xl shadow-xs border border-slate-200 bg-white p-1 gap-1">
                        <template v-for="(link, key) in paginationLinks" :key="key">
                            <div
                                v-if="link.url === null"
                                class="px-3 py-1.5 text-xs font-medium text-slate-400 rounded-lg cursor-not-allowed"
                                v-html="link.label"
                            />
                            <Link
                                v-else
                                class="px-3 py-1.5 text-xs font-medium rounded-lg transition-colors"
                                :class="link.active ? 'bg-brand-600 text-white font-semibold shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100'"
                                :href="link.url"
                                v-html="link.label"
                            />
                        </template>
                    </nav>
                </div>
            </Card>
        </div>

        <!-- Modal QR Code Check-In -->
        <Modal :show="isQrModalOpen" @close="closeQrModal" max-width="md">
            <div class="p-6" v-if="activeReservation">
                <!-- Modal Header -->
                <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-teal-50 border border-teal-200/80 flex items-center justify-center text-brand-600">
                            <QrCodeIcon class="w-4 h-4" />
                        </div>
                        <h3 class="font-heading font-bold text-base text-slate-900">
                            QR Code Check-In Ruangan
                        </h3>
                    </div>
                    <button
                        @click="closeQrModal"
                        class="p-1 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition"
                    >
                        <XMarkIcon class="w-5 h-5" />
                    </button>
                </div>

                <!-- Event Details Card -->
                <div class="mt-4 bg-slate-50/80 p-4 rounded-xl border border-slate-200/70 text-left space-y-1.5">
                    <div class="text-xs font-bold uppercase tracking-wider text-brand-700 flex items-center gap-1.5">
                        <BuildingOffice2Icon class="w-3.5 h-3.5" />
                        <span>{{ activeReservation.room?.name }}</span>
                    </div>
                    <div class="font-heading font-bold text-slate-900 text-sm">
                        {{ activeReservation.title }}
                    </div>
                    <div class="text-xs text-slate-600 flex items-center gap-1.5 pt-1">
                        <ClockIcon class="w-3.5 h-3.5 text-slate-400" />
                        <span>{{ formatDate(activeReservation.start_time) }} &rarr; {{ formatDate(activeReservation.end_time) }}</span>
                    </div>
                </div>

                <!-- QR Code Box -->
                <div class="flex justify-center my-6">
                    <div class="p-4 bg-white border-2 border-brand-200/80 rounded-2xl shadow-card inline-block">
                        <img
                            :src="route('reservations.qr-code', activeReservation.id)"
                            alt="QR Code Check-in"
                            class="w-52 h-52 mx-auto object-contain"
                        />
                    </div>
                </div>

                <!-- Info Check-in Status -->
                <div class="text-center text-xs">
                    <div
                        v-if="activeReservation.check_in?.checked_in_at"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-emerald-50 text-emerald-800 border border-emerald-200 font-semibold"
                    >
                        <span>&check; Sudah Check-In ({{ formatDate(activeReservation.check_in.checked_in_at) }})</span>
                    </div>
                    <div v-else class="text-slate-500 bg-slate-50 rounded-lg p-3 border border-slate-100 text-xs">
                        Scan QR ini menggunakan kamera ponsel di depan pintu ruangan untuk konfirmasi kehadiran (mulai 15 menit sebelum kegiatan).
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="mt-6 flex justify-end">
                    <SecondaryButton @click="closeQrModal">
                        Tutup
                    </SecondaryButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
