<script setup>
import { ref, computed } from 'vue';
import { Head, router, usePage, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import Modal from '@/Components/Modal.vue';
import { format, parseISO } from 'date-fns';
import { id } from 'date-fns/locale';

const props = defineProps({
    reservations: {
        type: [Array, Object],
        default: () => [],
    },
    currentStatus: {
        type: String,
        default: 'pending',
    },
});

const page = usePage();
const currentUser = computed(() => page.props.auth.user);
const hasAccess = computed(() => {
    return currentUser.value?.roles?.some((role) => ['admin', 'manager'].includes(role));
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

const tabs = [
    { key: 'pending', label: 'Menunggu Persetujuan' },
    { key: 'approved', label: 'Disetujui' },
    { key: 'rejected', label: 'Ditolak' },
];

const switchTab = (status) => {
    router.get(route('approvals.index'), { status }, { preserveState: true, preserveScroll: true });
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

const formatDate = (dateStr) => {
    if (!dateStr) return '-';
    try {
        const date = typeof dateStr === 'string' ? parseISO(dateStr) : new Date(dateStr);
        return format(date, 'dd MMM yyyy, HH:mm', { locale: id });
    } catch {
        return dateStr;
    }
};

// Approval logic
const isProcessing = ref(false);

const approveReservation = (reservation) => {
    if (confirm(`Setujui permohonan reservasi "${reservation.title}" untuk ruangan ${reservation.room?.name}?`)) {
        isProcessing.value = true;
        router.post(
            route('approvals.approve', reservation.id),
            {},
            {
                onFinish: () => {
                    isProcessing.value = false;
                },
            }
        );
    }
};

// Rejection Modal logic
const isRejectModalOpen = ref(false);
const rejectingReservation = ref(null);

const rejectForm = useForm({
    note: '',
});

const openRejectModal = (reservation) => {
    rejectingReservation.value = reservation;
    rejectForm.reset();
    rejectForm.clearErrors();
    isRejectModalOpen.value = true;
};

const closeRejectModal = () => {
    isRejectModalOpen.value = false;
    rejectingReservation.value = null;
    rejectForm.reset();
};

const submitReject = () => {
    if (!rejectingReservation.value) return;

    rejectForm.post(route('approvals.reject', rejectingReservation.value.id), {
        onSuccess: () => {
            closeRejectModal();
        },
    });
};
</script>

<template>
    <Head title="Persetujuan Reservasi" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Persetujuan Reservasi Ruangan
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Tidak Memiliki Hak Akses -->
                <div v-if="!hasAccess" class="bg-red-50 border border-red-200 text-red-700 p-6 rounded-lg text-center">
                    <h3 class="text-lg font-semibold">Tidak Ada Akses</h3>
                    <p class="mt-2 text-sm">Halaman persetujuan reservasi ini hanya dapat diakses oleh Administrator atau Manager.</p>
                </div>

                <!-- Konten Utama jika Berhak Akses -->
                <div v-else class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <!-- Status Filter Tabs -->
                    <div class="border-b border-gray-200">
                        <nav class="-mb-px flex space-x-8 px-6 pt-4" aria-label="Tabs">
                            <button
                                v-for="tab in tabs"
                                :key="tab.key"
                                @click="switchTab(tab.key)"
                                :class="[
                                    currentStatus === tab.key
                                        ? 'border-indigo-500 text-indigo-600 font-semibold'
                                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 font-medium',
                                    'whitespace-nowrap py-3 px-1 border-b-2 text-sm transition'
                                ]"
                            >
                                {{ tab.label }}
                            </button>
                        </nav>
                    </div>

                    <div class="p-6 text-gray-900">
                        <div class="mb-4 flex items-center justify-between">
                            <h3 class="text-base font-semibold text-gray-700">
                                Daftar Reservasi ({{ reservationList.length }})
                            </h3>
                        </div>

                        <div v-if="reservationList.length === 0" class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">Tidak ada reservasi pada kategori ini</h3>
                            <p class="mt-1 text-sm text-gray-500">Belum ada pengajuan dengan status {{ currentStatus }}.</p>
                        </div>

                        <div v-else class="space-y-4">
                            <div
                                v-for="res in reservationList"
                                :key="res.id"
                                class="border border-gray-200 rounded-lg p-5 hover:border-indigo-300 transition"
                            >
                                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                                    <div class="space-y-1.5">
                                        <div class="flex items-center space-x-2">
                                            <span
                                                v-if="res.status === 'pending'"
                                                class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 border border-amber-300"
                                            >
                                                Pending
                                            </span>
                                            <span
                                                v-else-if="res.status === 'approved'"
                                                class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-300"
                                            >
                                                Disetujui
                                            </span>
                                            <span
                                                v-else-if="res.status === 'rejected'"
                                                class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-800 border border-red-300"
                                            >
                                                Ditolak
                                            </span>
                                            <span
                                                v-else
                                                class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-800 border border-gray-300"
                                            >
                                                {{ res.status }}
                                            </span>
                                            <h4 class="text-lg font-bold text-gray-900">
                                                {{ res.title }}
                                            </h4>
                                        </div>

                                        <div class="text-sm text-gray-600 flex flex-wrap items-center gap-x-4 gap-y-1 pt-1">
                                            <div>
                                                <span class="font-medium text-gray-700">Ruangan:</span>
                                                <span class="text-indigo-600 font-semibold ml-1">{{ res.room?.name || '-' }}</span>
                                                <span v-if="res.room?.location" class="text-xs text-gray-500"> ({{ res.room.location }})</span>
                                            </div>
                                            <div>
                                                <span class="font-medium text-gray-700">Pemesan:</span>
                                                <span class="ml-1">{{ res.user?.name || '-' }}</span>
                                                <span v-if="res.user?.email" class="text-xs text-gray-400"> ({{ res.user.email }})</span>
                                            </div>
                                        </div>

                                        <div class="text-sm text-gray-600 flex items-center gap-x-2">
                                            <span class="font-medium text-gray-700">Jadwal:</span>
                                            <span class="text-gray-800">{{ formatDate(res.start_time) }}</span>
                                            <span>&rarr;</span>
                                            <span class="text-gray-800">{{ formatDate(res.end_time) }}</span>
                                        </div>

                                        <p v-if="res.description" class="text-sm text-gray-600 bg-gray-50 p-2.5 rounded mt-2 border border-gray-100">
                                            <span class="font-medium text-gray-700 block text-xs uppercase mb-0.5">Keperluan:</span>
                                            {{ res.description }}
                                        </p>
                                    </div>

                                    <!-- Tombol Aksi Sesuai Status -->
                                    <div class="flex items-center space-x-3 shrink-0 lg:self-center">
                                        <!-- Jika Pending: Tombol Setujui & Tolak -->
                                        <template v-if="res.status === 'pending'">
                                            <button
                                                type="button"
                                                :disabled="isProcessing"
                                                @click="approveReservation(res)"
                                                class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-md font-semibold text-xs uppercase tracking-widest transition disabled:opacity-50"
                                            >
                                                Setujui
                                            </button>
                                            <DangerButton
                                                type="button"
                                                :disabled="isProcessing"
                                                @click="openRejectModal(res)"
                                            >
                                                Tolak
                                            </DangerButton>
                                        </template>

                                        <!-- Jika Approved: Tombol Lihat QR -->
                                        <template v-else-if="res.status === 'approved'">
                                            <button
                                                type="button"
                                                @click="openQrModal(res)"
                                                class="inline-flex items-center px-3.5 py-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-md font-semibold text-xs uppercase tracking-wider border border-indigo-200 transition"
                                            >
                                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                                                </svg>
                                                Lihat QR
                                            </button>
                                        </template>
                                    </div>
                                </div>
                            </div>
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

        <!-- Modal Alasan Penolakan -->
        <Modal :show="isRejectModalOpen" @close="closeRejectModal">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 border-b pb-3">
                    Tolak Permohonan Reservasi
                </h3>

                <p v-if="rejectingReservation" class="mt-3 text-sm text-gray-600">
                    Anda akan menolak pengajuan untuk kegiatan <strong>"{{ rejectingReservation.title }}"</strong> pada ruangan <strong>{{ rejectingReservation.room?.name }}</strong>.
                </p>

                <form @submit.prevent="submitReject" class="mt-4 space-y-4">
                    <div>
                        <InputLabel for="note" value="Alasan Penolakan *" />
                        <textarea
                            id="note"
                            v-model="rejectForm.note"
                            rows="4"
                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                            placeholder="Tuliskan alasan penolakan agar pemesan mengetahui kendalanya (misal: Ruangan akan direnovasi atau bentrok agenda direksi)..."
                            required
                        />
                        <InputError class="mt-2" :message="rejectForm.errors.note" />
                    </div>

                    <div class="flex items-center justify-end space-x-3 pt-4 border-t">
                        <SecondaryButton type="button" @click="closeRejectModal">
                            Batal
                        </SecondaryButton>
                        <DangerButton :disabled="rejectForm.processing">
                            <span v-if="rejectForm.processing">Memproses...</span>
                            <span v-else>Tolak Reservasi</span>
                        </DangerButton>
                    </div>
                </form>
            </div>
        </Modal>

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
                    <div class="text-xs text-gray-500 mt-1">
                        Pemesan: <span class="font-medium text-gray-700">{{ activeReservation.user?.name }}</span>
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
                        QR Code ini siap dipasang atau di-scan di pintu ruangan saat sesi kegiatan berlangsung.
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

