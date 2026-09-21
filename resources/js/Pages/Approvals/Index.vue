<script setup>
import { ref, computed } from 'vue';
import { Head, router, usePage, useForm, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import PageHeader from '@/Components/PageHeader.vue';
import Card from '@/Components/Card.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import EmptyState from '@/Components/EmptyState.vue';
import Modal from '@/Components/Modal.vue';
import { format, parseISO } from 'date-fns';
import { id } from 'date-fns/locale';
import {
    ShieldCheckIcon,
    ShieldExclamationIcon,
    ClockIcon,
    CheckCircleIcon,
    XCircleIcon,
    CheckIcon,
    XMarkIcon,
    BuildingOffice2Icon,
    UserCircleIcon,
    QrCodeIcon,
    ExclamationTriangleIcon,
} from '@heroicons/vue/24/outline';

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
    { key: 'pending', label: 'Menunggu Persetujuan', icon: ClockIcon },
    { key: 'approved', label: 'Disetujui', icon: CheckCircleIcon },
    { key: 'rejected', label: 'Ditolak', icon: XCircleIcon },
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
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <!-- Header Section -->
            <PageHeader
                title="Persetujuan Reservasi"
                description="Tinjau dan proses permohonan peminjaman ruangan dari pengguna secara terpusat."
                badge="Admin & Manager"
            />

            <!-- Akses Ditolak Card -->
            <div
                v-if="!hasAccess"
                class="bg-rose-50 border border-rose-200 rounded-2xl p-8 text-center max-w-lg mx-auto my-12"
            >
                <div class="w-12 h-12 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center mx-auto mb-3">
                    <ShieldExclamationIcon class="w-6 h-6" />
                </div>
                <h3 class="font-heading font-bold text-lg text-rose-900">Akses Dibatasi</h3>
                <p class="mt-2 text-sm text-rose-700 leading-relaxed">
                    Halaman persetujuan reservasi ini dikhususkan bagi akun dengan hak akses <strong>Administrator</strong> atau <strong>Manager</strong>.
                </p>
            </div>

            <!-- Main Content Container -->
            <Card v-else :no-padding="true">
                <!-- Status Filter Tabs -->
                <div class="border-b border-slate-200/80 bg-slate-50/50 px-6 pt-3">
                    <nav class="-mb-px flex space-x-6" aria-label="Tabs">
                        <button
                            v-for="tab in tabs"
                            :key="tab.key"
                            @click="switchTab(tab.key)"
                            :class="[
                                currentStatus === tab.key
                                    ? 'border-brand-600 text-brand-700 font-bold'
                                    : 'border-transparent text-slate-500 hover:text-slate-800 hover:border-slate-300 font-medium',
                                'inline-flex items-center gap-2 py-3.5 px-2 border-b-2 text-sm transition-colors'
                            ]"
                        >
                            <component :is="tab.icon" class="w-4 h-4 shrink-0" />
                            <span>{{ tab.label }}</span>
                        </button>
                    </nav>
                </div>

                <div class="p-6">
                    <!-- Section Meta -->
                    <div class="mb-5 flex items-center justify-between">
                        <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">
                            Menampilkan {{ reservationList.length }} pengajuan
                        </span>
                    </div>

                    <!-- Empty State -->
                    <EmptyState
                        v-if="reservationList.length === 0"
                        :title="`Tidak Ada Pengajuan ${currentStatus === 'pending' ? 'Menunggu' : currentStatus === 'approved' ? 'Disetujui' : 'Ditolak'}`"
                        :description="`Saat ini belum ada data permohonan reservasi dengan status ${currentStatus}.`"
                    >
                        <template #icon>
                            <ClockIcon v-if="currentStatus === 'pending'" class="w-7 h-7 text-slate-400" />
                            <CheckCircleIcon v-else-if="currentStatus === 'approved'" class="w-7 h-7 text-slate-400" />
                            <XCircleIcon v-else class="w-7 h-7 text-slate-400" />
                        </template>
                    </EmptyState>

                    <!-- List of Reservation Approvals -->
                    <div v-else class="space-y-4">
                        <div
                            v-for="res in reservationList"
                            :key="res.id"
                            class="rounded-xl border border-slate-200/90 bg-white p-5 hover:border-slate-300 hover:shadow-card transition duration-150"
                        >
                            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">
                                <div class="space-y-2.5 flex-1">
                                    <!-- Status Badge + Title -->
                                    <div class="flex flex-wrap items-center gap-2.5">
                                        <StatusBadge :status="res.status" size="sm" />
                                        <h4 class="font-heading font-bold text-base text-slate-900">
                                            {{ res.title }}
                                        </h4>
                                    </div>

                                    <!-- Room & Requester Info -->
                                    <div class="text-xs text-slate-600 flex flex-wrap items-center gap-x-5 gap-y-1 pt-0.5">
                                        <div class="flex items-center gap-1.5">
                                            <BuildingOffice2Icon class="w-4 h-4 text-brand-600 shrink-0" />
                                            <span class="font-medium text-slate-500">Ruangan:</span>
                                            <span class="font-bold text-slate-800">{{ res.room?.name || '-' }}</span>
                                            <span v-if="res.room?.location" class="text-slate-400">({{ res.room.location }})</span>
                                        </div>

                                        <div class="flex items-center gap-1.5">
                                            <UserCircleIcon class="w-4 h-4 text-slate-400 shrink-0" />
                                            <span class="font-medium text-slate-500">Pemesan:</span>
                                            <span class="font-semibold text-slate-800">{{ res.user?.name || '-' }}</span>
                                            <span v-if="res.user?.email" class="text-slate-400">({{ res.user.email }})</span>
                                        </div>
                                    </div>

                                    <!-- Time Schedule -->
                                    <div class="text-xs text-slate-600 flex items-center gap-2">
                                        <ClockIcon class="w-4 h-4 text-slate-400 shrink-0" />
                                        <span class="font-medium text-slate-500">Jadwal:</span>
                                        <span class="font-semibold text-slate-800">{{ formatDate(res.start_time) }}</span>
                                        <span class="text-slate-400">&rarr;</span>
                                        <span class="font-semibold text-slate-800">{{ formatDate(res.end_time) }}</span>
                                    </div>

                                    <!-- Description / Notes -->
                                    <div v-if="res.description" class="text-xs text-slate-600 bg-slate-50 p-3 rounded-lg border border-slate-100 mt-2">
                                        <span class="font-bold uppercase tracking-wider text-[10px] text-slate-400 block mb-0.5">Keperluan Acara:</span>
                                        {{ res.description }}
                                    </div>
                                </div>

                                <!-- Action Buttons Area -->
                                <div class="flex items-center gap-2 shrink-0 lg:self-center">
                                    <!-- Pending Actions -->
                                    <template v-if="res.status === 'pending'">
                                        <button
                                            type="button"
                                            :disabled="isProcessing"
                                            @click="approveReservation(res)"
                                            class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-semibold text-xs uppercase tracking-wider shadow-xs transition disabled:opacity-50"
                                        >
                                            <CheckIcon class="w-4 h-4 stroke-2" />
                                            <span>Setujui</span>
                                        </button>
                                        <button
                                            type="button"
                                            :disabled="isProcessing"
                                            @click="openRejectModal(res)"
                                            class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-white hover:bg-rose-50 text-rose-600 hover:text-rose-700 border border-slate-200 hover:border-rose-200 rounded-lg font-semibold text-xs uppercase tracking-wider shadow-xs transition disabled:opacity-50"
                                        >
                                            <XMarkIcon class="w-4 h-4 stroke-2" />
                                            <span>Tolak</span>
                                        </button>
                                    </template>

                                    <!-- Approved Actions -->
                                    <template v-else-if="res.status === 'approved'">
                                        <button
                                            type="button"
                                            @click="openQrModal(res)"
                                            class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-teal-50 hover:bg-teal-100 text-brand-700 border border-teal-200/80 rounded-lg font-semibold text-xs uppercase tracking-wider shadow-xs transition"
                                        >
                                            <QrCodeIcon class="w-4 h-4" />
                                            <span>Lihat QR</span>
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div v-if="paginationLinks.length > 3" class="mt-8 flex justify-center">
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
                </div>
            </Card>
        </div>

        <!-- Modal Alasan Penolakan -->
        <Modal :show="isRejectModalOpen" @close="closeRejectModal">
            <div class="p-6">
                <div class="flex items-center gap-3 border-b border-slate-100 pb-4">
                    <div class="w-10 h-10 rounded-xl bg-rose-50 border border-rose-200 flex items-center justify-center text-rose-600 shrink-0">
                        <ExclamationTriangleIcon class="w-5 h-5" />
                    </div>
                    <div>
                        <h3 class="font-heading font-bold text-base text-slate-900">
                            Tolak Permohonan Reservasi
                        </h3>
                        <p class="text-xs text-slate-500">Berikan keterangan alasan penolakan bagi pemesan ruangan.</p>
                    </div>
                </div>

                <div v-if="rejectingReservation" class="mt-4 p-3 bg-slate-50 rounded-xl border border-slate-100 text-xs text-slate-600 space-y-1">
                    <div class="font-bold text-slate-900 text-sm">{{ rejectingReservation.title }}</div>
                    <div>Ruangan: <span class="font-semibold text-slate-800">{{ rejectingReservation.room?.name }}</span></div>
                    <div>Pemesan: <span class="font-semibold text-slate-800">{{ rejectingReservation.user?.name }}</span></div>
                </div>

                <form @submit.prevent="submitReject" class="mt-4 space-y-4">
                    <div>
                        <InputLabel for="note" value="Alasan Penolakan *" class="font-semibold text-xs text-slate-700" />
                        <textarea
                            id="note"
                            v-model="rejectForm.note"
                            rows="4"
                            class="mt-1.5 block w-full border-slate-300 focus:border-brand-500 focus:ring-brand-500 rounded-xl shadow-xs text-sm"
                            placeholder="Tuliskan alasan penolakan secara jelas (misal: Ruangan terjadwal untuk perbaikan kelistrikan atau bentrok agenda direksi)..."
                            required
                        />
                        <InputError class="mt-2" :message="rejectForm.errors.note" />
                    </div>

                    <div class="flex items-center justify-end space-x-3 pt-4 border-t border-slate-100">
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
            <div class="p-6" v-if="activeReservation">
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
                    <div class="text-xs text-slate-500 pt-0.5">
                        Pemesan: <span class="font-semibold text-slate-800">{{ activeReservation.user?.name }}</span>
                    </div>
                </div>

                <!-- QR Code Container -->
                <div class="flex justify-center my-6">
                    <div class="p-4 bg-white border-2 border-brand-200/80 rounded-2xl shadow-card inline-block">
                        <img
                            :src="route('reservations.qr-code', activeReservation.id)"
                            alt="QR Code Check-in"
                            class="w-52 h-52 mx-auto object-contain"
                        />
                    </div>
                </div>

                <div class="text-center text-xs">
                    <div
                        v-if="activeReservation.check_in?.checked_in_at"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-emerald-50 text-emerald-800 border border-emerald-200 font-semibold"
                    >
                        <span>&check; Sudah Check-In ({{ formatDate(activeReservation.check_in.checked_in_at) }})</span>
                    </div>
                    <div v-else class="text-slate-500 bg-slate-50 rounded-lg p-3 border border-slate-100">
                        QR Code ini siap di-scan di pintu ruangan saat sesi kegiatan berlangsung.
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
