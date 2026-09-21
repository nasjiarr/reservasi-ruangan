<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router, usePage, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import Card from '@/Components/Card.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import { format, parseISO, differenceInMinutes, isBefore, isAfter, isWithinInterval } from 'date-fns';
import { id } from 'date-fns/locale';
import {
    CalendarDaysIcon,
    ClockIcon,
    BuildingOffice2Icon,
    MapPinIcon,
    UserGroupIcon,
    UserCircleIcon,
    ArrowLeftIcon,
    ShieldCheckIcon,
    ShieldExclamationIcon,
    QrCodeIcon,
    CheckCircleIcon,
    XCircleIcon,
    ExclamationCircleIcon,
    CheckBadgeIcon,
    CheckIcon,
    XMarkIcon,
    TrashIcon,
    InformationCircleIcon,
    ArrowTopRightOnSquareIcon,
    SparklesIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
    reservation: {
        type: Object,
        required: true,
    },
});

const page = usePage();
const currentUser = computed(() => page.props.auth.user);

const canApprove = computed(() =>
    currentUser.value?.permissions?.includes('approve-reservation') ||
    currentUser.value?.roles?.some((r) => ['admin', 'manager'].includes(r))
);

const isOwner = computed(() => currentUser.value?.id === props.reservation.user_id);
const isAdmin = computed(() => currentUser.value?.roles?.includes('admin'));

const canCancel = computed(() => {
    if (!isOwner.value && !isAdmin.value) return false;
    if (props.reservation.status === 'pending') return true;
    if (props.reservation.status === 'approved') {
        const endTime = parseISO(props.reservation.end_time);
        return isAfter(endTime, new Date());
    }
    return false;
});

// Date formatters
const formatDate = (dateStr) => {
    if (!dateStr) return '-';
    try {
        return format(parseISO(dateStr), 'EEEE, dd MMMM yyyy', { locale: id });
    } catch {
        return dateStr;
    }
};

const formatShortDate = (dateStr) => {
    if (!dateStr) return '-';
    try {
        return format(parseISO(dateStr), 'dd MMM yyyy, HH:mm', { locale: id });
    } catch {
        return dateStr;
    }
};

const formatTime = (dateStr) => {
    if (!dateStr) return '-';
    try {
        return format(parseISO(dateStr), 'HH:mm');
    } catch {
        return dateStr;
    }
};

const durationText = computed(() => {
    if (!props.reservation.start_time || !props.reservation.end_time) return '-';
    try {
        const start = parseISO(props.reservation.start_time);
        const end = parseISO(props.reservation.end_time);
        const diff = differenceInMinutes(end, start);
        if (diff < 60) return `${diff} Menit`;
        const hours = Math.floor(diff / 60);
        const mins = diff % 60;
        return mins > 0 ? `${hours} Jam ${mins} Menit` : `${hours} Jam`;
    } catch {
        return '-';
    }
});

// Time Status
const timeStatus = computed(() => {
    if (!props.reservation.start_time || !props.reservation.end_time) return null;
    try {
        const now = new Date();
        const start = parseISO(props.reservation.start_time);
        const end = parseISO(props.reservation.end_time);

        if (isWithinInterval(now, { start, end })) {
            return { label: 'Sedang Berlangsung', class: 'bg-emerald-100 text-emerald-800 ring-1 ring-emerald-300' };
        }
        if (isBefore(now, start)) {
            return { label: 'Akan Datang', class: 'bg-brand-100 text-brand-800 ring-1 ring-brand-300' };
        }
        return { label: 'Telah Selesai', class: 'bg-slate-100 text-slate-700 ring-1 ring-slate-200' };
    } catch {
        return null;
    }
});

// Latest Approval Note / Rejection Reason
const latestApproval = computed(() => {
    if (!props.reservation.approvals || props.reservation.approvals.length === 0) return null;
    return props.reservation.approvals[props.reservation.approvals.length - 1];
});

// Approval Actions Modal States
const isApproveModalOpen = ref(false);
const isRejectModalOpen = ref(false);
const isCancelModalOpen = ref(false);

const approveForm = useForm({
    note: '',
});

const rejectForm = useForm({
    note: '',
});

const isProcessing = ref(false);

const submitApprove = () => {
    approveForm.post(route('approvals.approve', props.reservation.id), {
        onSuccess: () => {
            isApproveModalOpen.value = false;
            approveForm.reset();
        },
    });
};

const submitReject = () => {
    rejectForm.post(route('approvals.reject', props.reservation.id), {
        onSuccess: () => {
            isRejectModalOpen.value = false;
            rejectForm.reset();
        },
    });
};

const cancelReservation = () => {
    isProcessing.value = true;
    router.delete(route('reservations.destroy', props.reservation.id), {
        onFinish: () => {
            isProcessing.value = false;
            isCancelModalOpen.value = false;
        },
    });
};
</script>

<template>
    <Head :title="`Detail Reservasi - ${reservation.title}`" />

    <AuthenticatedLayout>
        <template #header>
            <PageHeader
                :title="reservation.title"
                :description="`Reservasi di ${reservation.room?.name || 'Ruangan'} • ${formatDate(reservation.start_time)}`"
            >
                <template #actions>
                    <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                        <Link :href="route('reservations.index')">
                            <SecondaryButton class="flex items-center gap-1.5 text-xs">
                                <ArrowLeftIcon class="w-4 h-4" />
                                <span>Kembali</span>
                            </SecondaryButton>
                        </Link>

                        <!-- Approver Quick Action Buttons (If Pending & Can Approve) -->
                        <template v-if="canApprove && reservation.status === 'pending'">
                            <button
                                type="button"
                                @click="isApproveModalOpen = true"
                                class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-xs shadow-xs hover:shadow transition"
                            >
                                <CheckIcon class="w-4 h-4 stroke-2" />
                                <span>Setujui</span>
                            </button>
                            <button
                                type="button"
                                @click="isRejectModalOpen = true"
                                class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-white hover:bg-rose-50 text-rose-600 hover:text-rose-700 border border-slate-200 hover:border-rose-200 rounded-xl font-bold text-xs shadow-xs transition"
                            >
                                <XMarkIcon class="w-4 h-4 stroke-2" />
                                <span>Tolak</span>
                            </button>
                        </template>

                        <!-- Owner / Admin Cancellation Button -->
                        <button
                            v-if="canCancel"
                            type="button"
                            @click="isCancelModalOpen = true"
                            class="inline-flex items-center gap-1.5 px-3 py-2 text-rose-600 hover:text-rose-700 hover:bg-rose-50 border border-rose-200/80 rounded-xl font-semibold text-xs transition"
                            title="Batalkan reservasi ini"
                        >
                            <TrashIcon class="w-4 h-4" />
                            <span>Batalkan Reservasi</span>
                        </button>
                    </div>
                </template>
            </PageHeader>
        </template>

        <div class="space-y-6">
            <!-- =================================================================== -->
            <!-- 1. DYNAMIC STATUS HERO BANNER                                       -->
            <!-- =================================================================== -->
            <!-- Status: APPROVED -->
            <div
                v-if="reservation.status === 'approved'"
                class="rounded-2xl bg-gradient-to-r from-emerald-500/10 via-emerald-500/5 to-teal-500/10 border border-emerald-200 p-5 sm:p-6 shadow-xs relative overflow-hidden"
            >
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-emerald-600 text-white flex items-center justify-center shrink-0 shadow-md shadow-emerald-600/20">
                            <CheckCircleIcon class="w-7 h-7 stroke-2" />
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="font-heading font-extrabold text-slate-900 text-lg sm:text-xl">
                                    Reservasi Berhasil Disetujui
                                </h3>
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                                    Disetujui
                                </span>
                            </div>
                            <p class="text-sm text-slate-600 mt-1 max-w-2xl">
                                Ruangan siap digunakan sesuai jadwal yang diajukan. Silakan gunakan QR Code di bawah untuk konfirmasi kehadiran saat tiba di ruangan.
                            </p>
                            <div v-if="latestApproval?.note" class="mt-2 text-xs text-emerald-800 font-medium bg-emerald-50 px-3 py-1.5 rounded-lg inline-block border border-emerald-200/70">
                                💬 Catatan Approver: "{{ latestApproval.note }}"
                            </div>
                        </div>
                    </div>

                    <div v-if="reservation.check_in?.checked_in_at" class="sm:text-right shrink-0">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-100 text-emerald-800 text-xs font-bold border border-emerald-200">
                            <CheckBadgeIcon class="w-4 h-4 text-emerald-600" />
                            <span>Telah Check-In</span>
                        </span>
                        <div class="text-[11px] text-slate-500 mt-1">
                            {{ formatShortDate(reservation.check_in.checked_in_at) }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Status: PENDING -->
            <div
                v-else-if="reservation.status === 'pending'"
                class="rounded-2xl bg-gradient-to-r from-amber-500/10 via-amber-500/5 to-orange-500/10 border border-amber-200 p-5 sm:p-6 shadow-xs"
            >
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-amber-500 text-white flex items-center justify-center shrink-0 shadow-md shadow-amber-500/20">
                        <ClockIcon class="w-7 h-7 stroke-2 animate-pulse" />
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center gap-2">
                            <h3 class="font-heading font-extrabold text-slate-900 text-lg sm:text-xl">
                                Menunggu Persetujuan
                            </h3>
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800">
                                Pending Review
                            </span>
                        </div>
                        <p class="text-sm text-slate-600 mt-1 max-w-2xl">
                            Permohonan reservasi ini sedang dalam antrean peninjauan oleh manajemen. Notifikasi konfirmasi akan dikirimkan otomatis setelah permohonan ditinjau.
                        </p>
                        <div v-if="canApprove" class="mt-3 flex items-center gap-2">
                            <span class="text-xs font-semibold text-slate-700">Tindakan Anda sebagai Peninjau:</span>
                            <button
                                type="button"
                                @click="isApproveModalOpen = true"
                                class="text-xs font-bold text-emerald-700 hover:text-emerald-800 underline ml-1"
                            >
                                Setujui sekarang
                            </button>
                            <span class="text-slate-300">•</span>
                            <button
                                type="button"
                                @click="isRejectModalOpen = true"
                                class="text-xs font-bold text-rose-700 hover:text-rose-800 underline"
                            >
                                Tolak dengan catatan
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Status: REJECTED -->
            <div
                v-else-if="reservation.status === 'rejected'"
                class="rounded-2xl bg-gradient-to-r from-rose-500/10 via-rose-500/5 to-red-500/10 border border-rose-200 p-5 sm:p-6 shadow-xs"
            >
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-rose-600 text-white flex items-center justify-center shrink-0 shadow-md shadow-rose-600/20">
                        <XCircleIcon class="w-7 h-7 stroke-2" />
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center gap-2">
                            <h3 class="font-heading font-extrabold text-slate-900 text-lg sm:text-xl">
                                Permohonan Reservasi Ditolak
                            </h3>
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-100 text-rose-800">
                                Ditolak
                            </span>
                        </div>
                        <p class="text-sm text-slate-600 mt-1">
                            Mohon maaf, pengajuan reservasi ruangan ini tidak dapat disetujui oleh pihak manajemen.
                        </p>

                        <!-- Callout Alasan Penolakan -->
                        <div v-if="latestApproval?.note" class="mt-3.5 p-3.5 rounded-xl bg-white border border-rose-200 text-xs shadow-2xs">
                            <div class="font-bold text-rose-800 uppercase tracking-wider text-[11px] mb-1 flex items-center gap-1.5">
                                <InformationCircleIcon class="w-4 h-4 text-rose-500" />
                                <span>Alasan Penolakan dari {{ latestApproval.approver?.name || 'Approver' }}</span>
                            </div>
                            <p class="text-slate-700 italic leading-relaxed">
                                "{{ latestApproval.note }}"
                            </p>
                        </div>

                        <div class="mt-3.5 flex items-center gap-3">
                            <Link
                                :href="route('reservations.calendar')"
                                class="inline-flex items-center gap-1.5 text-xs font-bold text-brand-700 hover:text-brand-800 hover:underline"
                            >
                                <CalendarDaysIcon class="w-4 h-4" />
                                <span>Cari Jadwal Lain di Kalender</span>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Status: CANCELLED -->
            <div
                v-else-if="reservation.status === 'cancelled'"
                class="rounded-2xl bg-slate-100 border border-slate-200 p-5 sm:p-6 shadow-xs"
            >
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-slate-400 text-white flex items-center justify-center shrink-0">
                        <ExclamationCircleIcon class="w-7 h-7" />
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="font-heading font-extrabold text-slate-900 text-lg sm:text-xl">
                                Reservasi Telah Dibatalkan
                            </h3>
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-200 text-slate-700">
                                Dibatalkan
                            </span>
                        </div>
                        <p class="text-sm text-slate-600 mt-1">
                            Reservasi ini telah dibatalkan sehingga slot ruangan telah kembali tersedia untuk pemesan lain.
                        </p>
                    </div>
                </div>
            </div>

            <!-- =================================================================== -->
            <!-- 2. RESERVATION LIFECYCLE STEPPER TRACKER                            -->
            <!-- =================================================================== -->
            <Card class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h4 class="font-heading font-bold text-sm text-slate-900 uppercase tracking-wider">
                        Tahapan Reservasi
                    </h4>
                    <span v-if="timeStatus" :class="timeStatus.class" class="text-xs px-2.5 py-0.5 rounded-full font-bold">
                        {{ timeStatus.label }}
                    </span>
                </div>

                <div class="relative flex flex-col md:flex-row md:items-center justify-between gap-6 md:gap-0 pt-2">
                    <!-- Step 1: Diajukan -->
                    <div class="flex items-center gap-3.5 z-10">
                        <div class="w-9 h-9 rounded-full bg-emerald-600 text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-sm">
                            <CheckIcon class="w-5 h-5 stroke-2" />
                        </div>
                        <div>
                            <div class="text-xs font-bold text-slate-900">1. Pengajuan Diajukan</div>
                            <div class="text-[11px] text-slate-500">{{ formatShortDate(reservation.created_at) }}</div>
                        </div>
                    </div>

                    <!-- Line 1-2 -->
                    <div class="hidden md:block flex-1 h-0.5 mx-4 bg-slate-200">
                        <div class="h-full bg-emerald-500 w-full"></div>
                    </div>

                    <!-- Step 2: Review & Persetujuan -->
                    <div class="flex items-center gap-3.5 z-10">
                        <div
                            v-if="reservation.status === 'approved'"
                            class="w-9 h-9 rounded-full bg-emerald-600 text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-sm"
                        >
                            <CheckIcon class="w-5 h-5 stroke-2" />
                        </div>
                        <div
                            v-else-if="reservation.status === 'rejected'"
                            class="w-9 h-9 rounded-full bg-rose-600 text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-sm"
                        >
                            <XMarkIcon class="w-5 h-5 stroke-2" />
                        </div>
                        <div
                            v-else-if="reservation.status === 'cancelled'"
                            class="w-9 h-9 rounded-full bg-slate-300 text-slate-600 flex items-center justify-center font-bold text-xs shrink-0"
                        >
                            <XMarkIcon class="w-5 h-5" />
                        </div>
                        <div
                            v-else
                            class="w-9 h-9 rounded-full bg-amber-500 text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-sm animate-pulse"
                        >
                            <ClockIcon class="w-5 h-5" />
                        </div>
                        <div>
                            <div class="text-xs font-bold text-slate-900">2. Review & Persetujuan</div>
                            <div class="text-[11px] text-slate-500">
                                <span v-if="reservation.status === 'approved'">Disetujui oleh {{ latestApproval?.approver?.name || 'Manajemen' }}</span>
                                <span v-else-if="reservation.status === 'rejected'">Ditolak oleh {{ latestApproval?.approver?.name || 'Manajemen' }}</span>
                                <span v-else-if="reservation.status === 'cancelled'">Dibatalkan</span>
                                <span v-else>Menunggu konfirmasi admin</span>
                            </div>
                        </div>
                    </div>

                    <!-- Line 2-3 -->
                    <div class="hidden md:block flex-1 h-0.5 mx-4 bg-slate-200">
                        <div
                            class="h-full"
                            :class="reservation.status === 'approved' ? 'bg-emerald-500 w-full' : 'bg-slate-200 w-0'"
                        ></div>
                    </div>

                    <!-- Step 3: Check-in Kehadiran -->
                    <div class="flex items-center gap-3.5 z-10">
                        <div
                            v-if="reservation.check_in?.checked_in_at"
                            class="w-9 h-9 rounded-full bg-emerald-600 text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-sm"
                        >
                            <CheckIcon class="w-5 h-5 stroke-2" />
                        </div>
                        <div
                            v-else-if="reservation.status === 'approved'"
                            class="w-9 h-9 rounded-full bg-brand-50 text-brand-700 border-2 border-brand-500 flex items-center justify-center font-bold text-xs shrink-0"
                        >
                            <QrCodeIcon class="w-5 h-5" />
                        </div>
                        <div
                            v-else
                            class="w-9 h-9 rounded-full bg-slate-100 text-slate-400 border border-slate-200 flex items-center justify-center font-bold text-xs shrink-0"
                        >
                            <QrCodeIcon class="w-5 h-5" />
                        </div>
                        <div>
                            <div class="text-xs font-bold text-slate-900">3. Konfirmasi Kehadiran</div>
                            <div class="text-[11px] text-slate-500">
                                <span v-if="reservation.check_in?.checked_in_at">Hadir ({{ formatShortDate(reservation.check_in.checked_in_at) }})</span>
                                <span v-else-if="reservation.status === 'approved'">Siap Scan QR Code</span>
                                <span v-else>Setelah disetujui</span>
                            </div>
                        </div>
                    </div>
                </div>
            </Card>

            <!-- =================================================================== -->
            <!-- 3. MAIN DETAILS 2-COLUMN GRID                                       -->
            <!-- =================================================================== -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left Column (Span 2) -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Schedule & Agenda Card -->
                    <Card class="p-6">
                        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                            <h4 class="font-heading font-bold text-base text-slate-900 flex items-center gap-2">
                                <CalendarDaysIcon class="w-5 h-5 text-brand-600" />
                                <span>Waktu & Keperluan Acara</span>
                            </h4>
                            <StatusBadge :status="reservation.status" />
                        </div>

                        <!-- 3 Metric Blocks -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 my-5">
                            <div class="p-4 rounded-xl bg-slate-50 border border-slate-100">
                                <div class="text-xs font-medium text-slate-500 flex items-center gap-1.5 mb-1">
                                    <CalendarDaysIcon class="w-4 h-4 text-brand-600" />
                                    <span>Tanggal</span>
                                </div>
                                <div class="font-bold text-slate-800 text-sm">
                                    {{ formatDate(reservation.start_time) }}
                                </div>
                            </div>

                            <div class="p-4 rounded-xl bg-slate-50 border border-slate-100">
                                <div class="text-xs font-medium text-slate-500 flex items-center gap-1.5 mb-1">
                                    <ClockIcon class="w-4 h-4 text-brand-600" />
                                    <span>Waktu</span>
                                </div>
                                <div class="font-bold text-slate-800 text-sm">
                                    {{ formatTime(reservation.start_time) }} - {{ formatTime(reservation.end_time) }} WIB
                                </div>
                            </div>

                            <div class="p-4 rounded-xl bg-slate-50 border border-slate-100">
                                <div class="text-xs font-medium text-slate-500 flex items-center gap-1.5 mb-1">
                                    <SparklesIcon class="w-4 h-4 text-brand-600" />
                                    <span>Durasi</span>
                                </div>
                                <div class="font-bold text-slate-800 text-sm">
                                    {{ durationText }}
                                </div>
                            </div>
                        </div>

                        <!-- Agenda Description -->
                        <div class="mt-4 pt-4 border-t border-slate-100">
                            <h5 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">
                                Deskripsi Agenda Kegiatan
                            </h5>
                            <p class="text-sm text-slate-700 leading-relaxed whitespace-pre-line bg-slate-50/60 p-4 rounded-xl border border-slate-100">
                                {{ reservation.description || 'Tidak ada catatan deskripsi tambahan untuk reservasi ini.' }}
                            </p>
                        </div>
                    </Card>

                    <!-- Room Details Card -->
                    <Card class="p-6">
                        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                            <h4 class="font-heading font-bold text-base text-slate-900 flex items-center gap-2">
                                <BuildingOffice2Icon class="w-5 h-5 text-brand-600" />
                                <span>Informasi Ruangan</span>
                            </h4>
                            <Link
                                v-if="reservation.room?.id"
                                :href="route('rooms.show', reservation.room.id)"
                                class="text-xs font-bold text-brand-700 hover:text-brand-800 hover:underline flex items-center gap-1"
                            >
                                <span>Detail Ruangan</span>
                                <ArrowTopRightOnSquareIcon class="w-3.5 h-3.5" />
                            </Link>
                        </div>

                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mt-5">
                            <div class="flex items-center gap-4">
                                <div class="w-14 h-14 rounded-2xl bg-brand-50 text-brand-700 flex items-center justify-center shrink-0 ring-2 ring-brand-100/70">
                                    <BuildingOffice2Icon class="w-7 h-7" />
                                </div>
                                <div>
                                    <h3 class="text-lg font-heading font-bold text-slate-900">
                                        {{ reservation.room?.name || 'Nama Ruangan' }}
                                    </h3>
                                    <div class="flex items-center gap-2 text-xs text-slate-500 mt-0.5">
                                        <MapPinIcon class="w-3.5 h-3.5 text-slate-400" />
                                        <span>{{ reservation.room?.location || 'Gedung Kantor' }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center gap-3">
                                <div class="px-3 py-1.5 rounded-xl bg-slate-100 text-slate-700 text-xs font-bold flex items-center gap-1.5">
                                    <UserGroupIcon class="w-4 h-4 text-slate-500" />
                                    <span>Kapasitas {{ reservation.room?.capacity || '-' }} Orang</span>
                                </div>
                                <StatusBadge v-if="reservation.room?.status" :status="reservation.room.status" size="sm" />
                            </div>
                        </div>

                        <!-- Facilities List -->
                        <div class="mt-6 pt-5 border-t border-slate-100">
                            <h5 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-3">
                                Fasilitas Ruangan Tersedia
                            </h5>
                            <div v-if="reservation.room?.facilities && reservation.room.facilities.length > 0" class="flex flex-wrap gap-2">
                                <span
                                    v-for="facility in reservation.room.facilities"
                                    :key="facility.id"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-slate-50 text-slate-700 border border-slate-200/80"
                                >
                                    <CheckBadgeIcon class="w-3.5 h-3.5 text-brand-600" />
                                    <span>{{ facility.name }}</span>
                                </span>
                            </div>
                            <p v-else class="text-xs text-slate-400 italic">
                                Belum ada rincian fasilitas terdaftar pada ruangan ini.
                            </p>
                        </div>
                    </Card>

                    <!-- Audit Trail / Approval History Card -->
                    <Card class="p-6" v-if="reservation.approvals && reservation.approvals.length > 0">
                        <div class="pb-4 border-b border-slate-100 flex items-center justify-between">
                            <h4 class="font-heading font-bold text-base text-slate-900 flex items-center gap-2">
                                <ShieldCheckIcon class="w-5 h-5 text-brand-600" />
                                <span>Riwayat Audit & Persetujuan</span>
                            </h4>
                            <span class="text-xs text-slate-400 uppercase tracking-wider font-semibold">
                                {{ reservation.approvals.length }} Catatan
                            </span>
                        </div>

                        <div class="divide-y divide-slate-100 mt-4">
                            <div
                                v-for="appr in reservation.approvals"
                                :key="appr.id"
                                class="py-3.5 first:pt-0 last:pb-0 flex flex-col sm:flex-row sm:items-start justify-between gap-3"
                            >
                                <div class="flex items-start gap-3">
                                    <div class="w-8 h-8 rounded-full bg-brand-100 text-brand-700 flex items-center justify-center font-bold text-xs shrink-0 mt-0.5">
                                        {{ appr.approver?.name?.charAt(0).toUpperCase() || 'A' }}
                                    </div>
                                    <div>
                                        <div class="text-xs font-bold text-slate-900">
                                            {{ appr.approver?.name || 'Approver' }}
                                        </div>
                                        <div class="text-[11px] text-slate-500">
                                            {{ appr.approver?.email }}
                                        </div>
                                        <div v-if="appr.note" class="mt-2 text-xs text-slate-700 bg-slate-50 p-2.5 rounded-lg border border-slate-100 italic">
                                            "{{ appr.note }}"
                                        </div>
                                    </div>
                                </div>

                                <div class="flex sm:flex-col sm:items-end justify-between items-center shrink-0 gap-1">
                                    <StatusBadge :status="appr.status" size="sm" />
                                    <span class="text-[10px] text-slate-400">
                                        {{ formatShortDate(appr.created_at) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </Card>
                </div>

                <!-- Right Column (Sidebar: QR, User, Guidelines) -->
                <div class="space-y-6">
                    <!-- QR Code Check-In Card (Highlight for Approved) -->
                    <Card v-if="reservation.status === 'approved'" class="p-6 text-center border-2 border-brand-200/80 shadow-md">
                        <div class="flex items-center justify-center gap-1.5 text-xs font-bold text-brand-700 uppercase tracking-wider mb-2">
                            <QrCodeIcon class="w-4 h-4" />
                            <span>Tiket QR Code Check-In</span>
                        </div>
                        <h4 class="font-heading font-extrabold text-slate-900 text-base mb-1">
                            Pindai untuk Hadir
                        </h4>
                        <p class="text-xs text-slate-500 mb-4">
                            Tunjukkan kode ini ke kamera tablet resepsionis atau pemindai di depan ruangan.
                        </p>

                        <!-- Embedded QR Image -->
                        <div class="p-4 bg-white border border-slate-200 rounded-2xl shadow-card inline-block mb-4">
                            <img
                                :src="route('reservations.qr-code', reservation.id)"
                                alt="QR Code Check-In"
                                class="w-48 h-48 mx-auto object-contain"
                            />
                        </div>

                        <!-- Check-in status badge -->
                        <div v-if="reservation.check_in?.checked_in_at" class="p-2.5 rounded-xl bg-emerald-50 border border-emerald-200 text-xs text-emerald-800 font-bold mb-3">
                            ✅ Anda sudah check-in pada {{ formatShortDate(reservation.check_in.checked_in_at) }}
                        </div>
                        <div v-else class="p-2.5 rounded-xl bg-brand-50 border border-brand-100 text-xs text-brand-800 font-medium mb-3">
                            ⏳ Check-in dapat dilakukan mulai 15 menit sebelum kegiatan.
                        </div>

                        <!-- Action Link -->
                        <a
                            v-if="reservation.check_in?.qr_token"
                            :href="route('checkin.show', reservation.check_in.qr_token)"
                            target="_blank"
                            class="inline-flex items-center justify-center gap-1.5 w-full py-2.5 px-3 text-xs font-bold text-brand-700 bg-brand-50 hover:bg-brand-100 rounded-xl transition border border-brand-200"
                        >
                            <span>Buka Halaman Scan Publik</span>
                            <ArrowTopRightOnSquareIcon class="w-3.5 h-3.5" />
                        </a>
                    </Card>

                    <!-- Requester / Organizer Card -->
                    <Card class="p-6">
                        <h4 class="font-heading font-bold text-xs uppercase tracking-wider text-slate-500 mb-4">
                            Informasi Pemesan
                        </h4>
                        <div class="flex items-center gap-3.5">
                            <div class="w-12 h-12 rounded-2xl bg-brand-100 text-brand-800 font-bold flex items-center justify-center text-base shrink-0 ring-2 ring-brand-50">
                                {{ reservation.user?.name?.charAt(0).toUpperCase() || 'U' }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="font-heading font-bold text-sm text-slate-900 truncate">
                                    {{ reservation.user?.name || '-' }}
                                </div>
                                <div class="text-xs text-slate-500 truncate">
                                    {{ reservation.user?.email || '-' }}
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 pt-4 border-t border-slate-100 space-y-2 text-xs">
                            <div class="flex items-center justify-between text-slate-600">
                                <span class="text-slate-400">ID Reservasi:</span>
                                <span class="font-mono font-bold text-slate-800">#RES-{{ reservation.id }}</span>
                            </div>
                            <div class="flex items-center justify-between text-slate-600">
                                <span class="text-slate-400">Diajukan Pada:</span>
                                <span class="font-medium text-slate-800">{{ formatShortDate(reservation.created_at) }}</span>
                            </div>
                        </div>
                    </Card>

                    <!-- Room Policy & Etiquette Card -->
                    <Card class="p-6 bg-slate-50/50">
                        <h4 class="font-heading font-bold text-xs uppercase tracking-wider text-slate-500 mb-3 flex items-center gap-1.5">
                            <InformationCircleIcon class="w-4 h-4 text-brand-600" />
                            <span>Ketentuan Penggunaan</span>
                        </h4>
                        <ul class="space-y-2 text-xs text-slate-600 list-disc list-inside leading-relaxed">
                            <li>Hadir tepat waktu atau maksimal 15 menit setelah jadwal dimulai.</li>
                            <li>Pastikan peralatan proyektor & AC dimatikan seusai kegiatan.</li>
                            <li>Jaga kebersihan ruangan dan buang sampah pada tempatnya.</li>
                        </ul>
                    </Card>
                </div>
            </div>
        </div>

        <!-- =================================================================== -->
        <!-- MODALS: APPROVE, REJECT, CANCEL                                     -->
        <!-- =================================================================== -->
        <!-- Approve Modal -->
        <Modal :show="isApproveModalOpen" @close="isApproveModalOpen = false" max-width="md">
            <div class="p-6">
                <div class="flex items-center gap-3 text-emerald-700 mb-4">
                    <div class="w-10 h-10 rounded-xl bg-emerald-100 flex items-center justify-center">
                        <CheckCircleIcon class="w-6 h-6" />
                    </div>
                    <div>
                        <h3 class="font-heading font-bold text-base text-slate-900">
                            Setujui Reservasi
                        </h3>
                        <p class="text-xs text-slate-500">
                            Permohonan untuk "{{ reservation.title }}"
                        </p>
                    </div>
                </div>

                <form @submit.prevent="submitApprove" class="space-y-4">
                    <div>
                        <InputLabel for="approve-note" value="Catatan Persetujuan (Opsional)" />
                        <textarea
                            id="approve-note"
                            v-model="approveForm.note"
                            rows="3"
                            class="w-full mt-1 border-slate-300 rounded-xl text-xs focus:border-brand-500 focus:ring-brand-500"
                            placeholder="Contoh: Disetujui, harap menjaga kebersihan ruangan."
                        ></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-2">
                        <SecondaryButton type="button" @click="isApproveModalOpen = false">
                            Batal
                        </SecondaryButton>
                        <button
                            type="submit"
                            :disabled="approveForm.processing"
                            class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition disabled:opacity-50"
                        >
                            {{ approveForm.processing ? 'Memproses...' : 'Ya, Setujui Reservasi' }}
                        </button>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Reject Modal -->
        <Modal :show="isRejectModalOpen" @close="isRejectModalOpen = false" max-width="md">
            <div class="p-6">
                <div class="flex items-center gap-3 text-rose-700 mb-4">
                    <div class="w-10 h-10 rounded-xl bg-rose-100 flex items-center justify-center">
                        <XCircleIcon class="w-6 h-6" />
                    </div>
                    <div>
                        <h3 class="font-heading font-bold text-base text-slate-900">
                            Tolak Reservasi
                        </h3>
                        <p class="text-xs text-slate-500">
                            Permohonan untuk "{{ reservation.title }}"
                        </p>
                    </div>
                </div>

                <form @submit.prevent="submitReject" class="space-y-4">
                    <div>
                        <InputLabel for="reject-note" value="Alasan Penolakan (Wajib Diisi) *" />
                        <textarea
                            id="reject-note"
                            v-model="rejectForm.note"
                            rows="3"
                            required
                            class="w-full mt-1 border-slate-300 rounded-xl text-xs focus:border-rose-500 focus:ring-rose-500"
                            placeholder="Tuliskan alasan mengapa reservasi ini ditolak..."
                        ></textarea>
                        <InputError :message="rejectForm.errors.note" class="mt-1" />
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-2">
                        <SecondaryButton type="button" @click="isRejectModalOpen = false">
                            Batal
                        </SecondaryButton>
                        <button
                            type="submit"
                            :disabled="rejectForm.processing"
                            class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold transition disabled:opacity-50"
                        >
                            {{ rejectForm.processing ? 'Memproses...' : 'Tolak Reservasi' }}
                        </button>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Cancel Confirmation Modal -->
        <Modal :show="isCancelModalOpen" @close="isCancelModalOpen = false" max-width="sm">
            <div class="p-6 text-center">
                <div class="w-12 h-12 rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center mx-auto mb-4">
                    <TrashIcon class="w-6 h-6" />
                </div>
                <h3 class="font-heading font-bold text-base text-slate-900 mb-2">
                    Batalkan Reservasi Ini?
                </h3>
                <p class="text-xs text-slate-500 mb-6 leading-relaxed">
                    Tindakan ini akan membatalkan reservasi "{{ reservation.title }}" dan mengembalikan ketersediaan ruangan ke sistem. Tindakan ini tidak dapat diulang.
                </p>

                <div class="flex items-center justify-center gap-3">
                    <SecondaryButton type="button" @click="isCancelModalOpen = false">
                        Kembali
                    </SecondaryButton>
                    <DangerButton
                        type="button"
                        :disabled="isProcessing"
                        @click="cancelReservation"
                        class="text-xs"
                    >
                        {{ isProcessing ? 'Membatalkan...' : 'Ya, Batalkan' }}
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
