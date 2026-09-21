<script setup>
import { ref, computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/Card.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import Modal from '@/Components/Modal.vue';
import {
    CalendarDaysIcon,
    BuildingOffice2Icon,
    ClockIcon,
    ShieldCheckIcon,
    CheckBadgeIcon,
    QrCodeIcon,
    ArrowRightIcon,
    SparklesIcon,
    MapPinIcon,
    PlusIcon,
    XMarkIcon,
    UserCircleIcon,
    PresentationChartLineIcon,
    ChevronRightIcon,
    CheckCircleIcon,
    ExclamationCircleIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
    greeting: {
        type: String,
        required: true,
    },
    todayDate: {
        type: String,
        required: true,
    },
    stats: {
        type: Object,
        required: true,
    },
    nextMeeting: {
        type: Object,
        default: null,
    },
    roomStatuses: {
        type: Array,
        default: () => [],
    },
    recentReservations: {
        type: Array,
        default: () => [],
    },
});

const page = usePage();
const currentUser = computed(() => page.props.auth.user);
const userRoles = computed(() => currentUser.value?.roles || []);
const roleDisplay = computed(() => {
    if (userRoles.value.includes('admin')) return 'Administrator';
    if (userRoles.value.includes('manager')) return 'Manager Fasilitas';
    return 'Staff Anggota';
});

// Modal QR Code Check-In
const isQrModalOpen = ref(false);
const activeQrData = ref(null);

const openQrModal = (meetingData) => {
    activeQrData.value = meetingData;
    isQrModalOpen.value = true;
};

const closeQrModal = () => {
    isQrModalOpen.value = false;
    activeQrData.value = null;
};
</script>

<template>
    <Head title="Command Center" />

    <AuthenticatedLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            <!-- 1. Hero Welcome Banner -->
            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-slate-900 via-slate-800 to-teal-950 p-6 sm:p-8 text-white shadow-card">
                <!-- Subtle Decorative Background Glow -->
                <div class="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-brand-500/20 blur-3xl pointer-events-none"></div>
                <div class="absolute right-40 -bottom-20 h-48 w-48 rounded-full bg-emerald-500/10 blur-2xl pointer-events-none"></div>

                <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                    <div class="space-y-2.5 max-w-2xl">
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 backdrop-blur border border-white/15 text-xs font-semibold text-teal-200">
                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                            <span>{{ roleDisplay }}</span>
                            <span class="text-white/40">•</span>
                            <span>{{ todayDate }}</span>
                        </div>
                        <h1 class="font-heading font-extrabold text-2xl sm:text-3xl lg:text-4xl text-white tracking-tight">
                            {{ greeting }}, {{ currentUser?.name }} 👋
                        </h1>
                        <p class="text-sm sm:text-base text-slate-300 leading-relaxed">
                            Selamat datang di Command Center RoomSpace. Pantau ketersediaan ruangan real-time, jadwal pertemuan Anda, dan koordinasi fasilitas kerja hari ini.
                        </p>
                    </div>

                    <!-- Quick CTA Buttons -->
                    <div class="flex flex-wrap items-center gap-3 shrink-0">
                        <Link :href="route('reservations.calendar')">
                            <button
                                type="button"
                                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold bg-white/10 hover:bg-white/20 text-white border border-white/20 transition-all shadow-xs"
                            >
                                <CalendarDaysIcon class="w-4 h-4 text-teal-200" />
                                <span>Lihat Kalender</span>
                            </button>
                        </Link>
                        <Link :href="route('reservations.create')">
                            <button
                                type="button"
                                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold bg-brand-500 hover:bg-brand-600 text-white shadow-sm transition-all transform hover:-translate-y-0.5"
                            >
                                <PlusIcon class="w-4 h-4" />
                                <span>Booking Ruangan</span>
                            </button>
                        </Link>
                    </div>
                </div>
            </div>

            <!-- 2. Real-Time Operational Stat Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                <!-- Card 1: Agenda Hari Ini -->
                <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs hover:shadow-card transition-shadow duration-200">
                    <div class="flex items-center justify-between">
                        <div class="w-11 h-11 rounded-xl bg-teal-50 text-brand-600 flex items-center justify-center border border-teal-100/80">
                            <CalendarDaysIcon class="w-6 h-6" />
                        </div>
                        <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-slate-100 text-slate-600">
                            Hari Ini
                        </span>
                    </div>
                    <div class="mt-4">
                        <div class="font-heading font-extrabold text-2xl sm:text-3xl text-slate-900 tracking-tight">
                            {{ stats.today_reservations }}
                        </div>
                        <div class="text-xs font-bold text-slate-700 mt-0.5">Pertemuan Terjadwal</div>
                        <p class="text-xs text-slate-500 mt-1">Agenda aktif berlangsung hari ini</p>
                    </div>
                </div>

                <!-- Card 2: Ruangan Terpakai Saat Ini -->
                <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs hover:shadow-card transition-shadow duration-200">
                    <div class="flex items-center justify-between">
                        <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center border border-blue-100/80">
                            <BuildingOffice2Icon class="w-6 h-6" />
                        </div>
                        <span
                            :class="[
                                'text-xs font-semibold px-2.5 py-1 rounded-full flex items-center gap-1.5',
                                stats.rooms_occupied_now > 0 ? 'bg-amber-50 text-amber-700 border border-amber-200/60' : 'bg-emerald-50 text-emerald-700 border border-emerald-200/60'
                            ]"
                        >
                            <span class="w-1.5 h-1.5 rounded-full" :class="stats.rooms_occupied_now > 0 ? 'bg-amber-500' : 'bg-emerald-500'"></span>
                            <span>{{ stats.rooms_occupied_now > 0 ? 'In-Use' : 'Low Traffic' }}</span>
                        </span>
                    </div>
                    <div class="mt-4">
                        <div class="font-heading font-extrabold text-2xl sm:text-3xl text-slate-900 tracking-tight">
                            {{ stats.rooms_occupied_now }} <span class="text-lg font-medium text-slate-400">/ {{ stats.total_active_rooms }}</span>
                        </div>
                        <div class="text-xs font-bold text-slate-700 mt-0.5">Ruang Terpakai Detik Ini</div>
                        <p class="text-xs text-slate-500 mt-1">
                            {{ stats.total_active_rooms - stats.rooms_occupied_now }} ruangan tersedia saat ini
                        </p>
                    </div>
                </div>

                <!-- Card 3: Action Required (Role Aware) -->
                <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs hover:shadow-card transition-shadow duration-200">
                    <div class="flex items-center justify-between">
                        <div
                            :class="[
                                'w-11 h-11 rounded-xl flex items-center justify-center border',
                                stats.can_approve
                                    ? 'bg-amber-50 text-amber-600 border-amber-100/80'
                                    : 'bg-teal-50 text-brand-600 border-teal-100/80'
                            ]"
                        >
                            <ShieldCheckIcon v-if="stats.can_approve" class="w-6 h-6" />
                            <ClockIcon v-else class="w-6 h-6" />
                        </div>
                        <span
                            v-if="stats.can_approve && stats.pending_approvals > 0"
                            class="text-xs font-bold px-2.5 py-1 rounded-full bg-rose-50 text-rose-700 border border-rose-200/60 animate-pulse"
                        >
                            Butuh Aksi
                        </span>
                        <span
                            v-else
                            class="text-xs font-semibold px-2.5 py-1 rounded-full bg-slate-100 text-slate-600"
                        >
                            {{ stats.can_approve ? 'Persetujuan' : 'Agenda Saya' }}
                        </span>
                    </div>
                    <div class="mt-4">
                        <div class="font-heading font-extrabold text-2xl sm:text-3xl text-slate-900 tracking-tight">
                            {{ stats.can_approve ? stats.pending_approvals : stats.user_upcoming_reservations }}
                        </div>
                        <div class="text-xs font-bold text-slate-700 mt-0.5">
                            {{ stats.can_approve ? 'Menunggu Disetujui' : 'Reservasi Aktif Saya' }}
                        </div>
                        <p class="text-xs text-slate-500 mt-1">
                            {{ stats.can_approve ? 'Permohonan tertunda dalam antrean' : 'Jadwal yang akan datang milik Anda' }}
                        </p>
                    </div>
                </div>

                <!-- Card 4: Check-in Sukses Hari Ini -->
                <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs hover:shadow-card transition-shadow duration-200">
                    <div class="flex items-center justify-between">
                        <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center border border-emerald-100/80">
                            <CheckBadgeIcon class="w-6 h-6" />
                        </div>
                        <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200/60">
                            Verifikasi
                        </span>
                    </div>
                    <div class="mt-4">
                        <div class="font-heading font-extrabold text-2xl sm:text-3xl text-slate-900 tracking-tight">
                            {{ stats.checkins_today }}
                        </div>
                        <div class="text-xs font-bold text-slate-700 mt-0.5">Check-In Terkonfirmasi</div>
                        <p class="text-xs text-slate-500 mt-1">Scan QR kehadiran sukses hari ini</p>
                    </div>
                </div>
            </div>

            <!-- 3. Main Workspace Grid: (Left 8 cols, Right 4 cols) -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                <!-- Left Column (8 cols): Personal Next Meeting & Recent Reservations -->
                <div class="lg:col-span-8 space-y-8">
                    <!-- Widget A: Agenda Anda Berikutnya (Next Meeting Card) -->
                    <Card class="overflow-hidden border-teal-200/70 shadow-sm relative">
                        <div class="p-5 sm:p-6">
                            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-lg bg-teal-50 text-brand-600 flex items-center justify-center">
                                        <SparklesIcon class="w-4 h-4" />
                                    </div>
                                    <h3 class="font-heading font-bold text-base text-slate-900">
                                        Agenda Anda Berikutnya
                                    </h3>
                                </div>
                                <span v-if="nextMeeting" class="text-xs font-medium text-slate-500">
                                    Jadwal terdekat milik Anda
                                </span>
                            </div>

                            <!-- State 1: User Has Upcoming Meeting -->
                            <div v-if="nextMeeting" class="mt-5 space-y-4">
                                <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 bg-slate-50/70 p-4 sm:p-5 rounded-xl border border-slate-200/70">
                                    <div class="space-y-2">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span
                                                v-if="nextMeeting.is_happening_now"
                                                class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200"
                                            >
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-600 animate-ping"></span>
                                                <span>Sedang Berlangsung</span>
                                            </span>
                                            <span
                                                v-else
                                                class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-brand-50 text-brand-700 border border-brand-200"
                                            >
                                                <ClockIcon class="w-3.5 h-3.5" />
                                                <span>{{ nextMeeting.time_text }}</span>
                                            </span>
                                            <span class="text-xs text-slate-500 font-medium">
                                                {{ nextMeeting.date_formatted }}
                                            </span>
                                        </div>

                                        <h4 class="font-heading font-bold text-lg text-slate-900">
                                            {{ nextMeeting.title }}
                                        </h4>

                                        <div class="flex flex-wrap items-center gap-x-4 gap-y-1.5 text-xs text-slate-600 pt-1">
                                            <div class="flex items-center gap-1.5 font-medium text-slate-800">
                                                <BuildingOffice2Icon class="w-4 h-4 text-brand-600 shrink-0" />
                                                <span>{{ nextMeeting.room_name }}</span>
                                            </div>
                                            <div class="flex items-center gap-1.5 text-slate-500">
                                                <MapPinIcon class="w-4 h-4 text-slate-400 shrink-0" />
                                                <span>{{ nextMeeting.room_location }}</span>
                                            </div>
                                            <div class="flex items-center gap-1.5 text-slate-500">
                                                <ClockIcon class="w-4 h-4 text-slate-400 shrink-0" />
                                                <span>{{ nextMeeting.start_time }} - {{ nextMeeting.end_time }} WIB</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- QR Check-in Shortcut / Status -->
                                    <div class="flex sm:flex-col items-center sm:items-end justify-between gap-2 shrink-0 border-t sm:border-t-0 pt-3 sm:pt-0 border-slate-200">
                                        <div v-if="nextMeeting.is_checked_in" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs font-semibold">
                                            <CheckCircleIcon class="w-4 h-4 text-emerald-600" />
                                            <span>Sudah Check-In</span>
                                        </div>
                                        <button
                                            v-else
                                            type="button"
                                            @click="openQrModal(nextMeeting)"
                                            class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold bg-white hover:bg-slate-50 text-brand-700 border-2 border-brand-300 hover:border-brand-500 shadow-xs transition-all"
                                        >
                                            <QrCodeIcon class="w-4 h-4 text-brand-600" />
                                            <span>Tiket QR Check-In</span>
                                        </button>
                                        <Link
                                            :href="route('reservations.show', nextMeeting.id)"
                                            class="text-xs font-semibold text-slate-500 hover:text-slate-800 flex items-center gap-1 mt-1 transition"
                                        >
                                            <span>Rincian</span>
                                            <ChevronRightIcon class="w-3.5 h-3.5" />
                                        </Link>
                                    </div>
                                </div>
                            </div>

                            <!-- State 2: No Upcoming Meeting -->
                            <div v-else class="mt-5 text-center py-6 px-4 bg-slate-50/50 rounded-xl border border-dashed border-slate-200">
                                <div class="w-10 h-10 rounded-full bg-teal-50 text-brand-600 flex items-center justify-center mx-auto mb-3">
                                    <CalendarDaysIcon class="w-5 h-5" />
                                </div>
                                <h4 class="font-heading font-bold text-slate-800 text-sm">
                                    Tidak Ada Jadwal Pertemuan Aktif
                                </h4>
                                <p class="text-xs text-slate-500 max-w-md mx-auto mt-1 mb-4">
                                    Anda belum memiliki agenda reservasi dalam waktu dekat. Butuh ruang rapat untuk koordinasi tim atau presentasi klien?
                                </p>
                                <Link :href="route('reservations.create')">
                                    <PrimaryButton class="text-xs py-2 px-3.5 gap-1.5">
                                        <PlusIcon class="w-3.5 h-3.5" />
                                        <span>Pesan Ruangan Sekarang</span>
                                    </PrimaryButton>
                                </Link>
                            </div>
                        </div>
                    </Card>

                    <!-- Widget B: Antrean & Aktivitas Reservasi Terkini -->
                    <Card class="overflow-hidden shadow-xs">
                        <div class="p-5 sm:p-6">
                            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                                <div>
                                    <h3 class="font-heading font-bold text-base text-slate-900">
                                        {{ stats.can_approve ? 'Antrean Reservasi & Aktivitas Terkini' : 'Riwayat Reservasi Terakhir Anda' }}
                                    </h3>
                                    <p class="text-xs text-slate-500 mt-0.5">
                                        {{ stats.can_approve ? 'Daftar permohonan yang perlu diperhatikan atau baru diperbarui' : 'Status pengajuan peminjaman ruangan yang baru Anda buat' }}
                                    </p>
                                </div>
                                <Link
                                    :href="stats.can_approve ? route('approvals.index') : route('reservations.index')"
                                    class="text-xs font-bold text-brand-600 hover:text-brand-700 flex items-center gap-1 transition"
                                >
                                    <span>{{ stats.can_approve ? 'Lihat Antrean Approval' : 'Lihat Semua' }}</span>
                                    <ArrowRightIcon class="w-3.5 h-3.5" />
                                </Link>
                            </div>

                            <!-- Table Rows -->
                            <div class="mt-4 overflow-x-auto">
                                <table class="w-full text-left border-collapse text-xs">
                                    <thead>
                                        <tr class="border-b border-slate-100 text-slate-400 font-bold uppercase tracking-wider">
                                            <th class="py-3 px-3">Kegiatan / Pemohon</th>
                                            <th class="py-3 px-3">Ruangan</th>
                                            <th class="py-3 px-3">Jadwal Acara</th>
                                            <th class="py-3 px-3 text-center">Status</th>
                                            <th class="py-3 px-3 text-right">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        <tr
                                            v-for="res in recentReservations"
                                            :key="res.id"
                                            class="hover:bg-slate-50/80 transition-colors"
                                        >
                                            <td class="py-3.5 px-3">
                                                <div class="font-bold text-slate-800 text-sm">
                                                    {{ res.title }}
                                                </div>
                                                <div class="text-xs text-slate-500 flex items-center gap-1 mt-0.5">
                                                    <UserCircleIcon class="w-3.5 h-3.5 text-slate-400" />
                                                    <span>{{ res.user_name }}</span>
                                                </div>
                                            </td>
                                            <td class="py-3.5 px-3 font-medium text-slate-700">
                                                <div class="flex items-center gap-1.5">
                                                    <BuildingOffice2Icon class="w-3.5 h-3.5 text-slate-400 shrink-0" />
                                                    <span>{{ res.room_name }}</span>
                                                </div>
                                            </td>
                                            <td class="py-3.5 px-3 text-slate-600 whitespace-nowrap">
                                                <div class="font-semibold text-slate-800">{{ res.date }}</div>
                                                <div class="text-slate-500 text-[11px]">{{ res.start_time }} - {{ res.end_time }} WIB</div>
                                            </td>
                                            <td class="py-3.5 px-3 text-center whitespace-nowrap">
                                                <StatusBadge :status="res.status" />
                                            </td>
                                            <td class="py-3.5 px-3 text-right whitespace-nowrap">
                                                <div class="flex items-center justify-end gap-1.5">
                                                    <Link
                                                        v-if="stats.can_approve && res.status === 'pending'"
                                                        :href="route('approvals.index')"
                                                        class="px-2.5 py-1 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-bold border border-emerald-200 transition text-[11px]"
                                                    >
                                                        Review
                                                    </Link>
                                                    <Link
                                                        :href="route('reservations.show', res.id)"
                                                        class="p-1 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition"
                                                        title="Lihat Rincian"
                                                    >
                                                        <ChevronRightIcon class="w-4 h-4" />
                                                    </Link>
                                                </div>
                                            </td>
                                        </tr>

                                        <tr v-if="recentReservations.length === 0">
                                            <td colspan="5" class="text-center py-8 text-slate-400">
                                                Tidak ada data reservasi terkini.
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </Card>
                </div>

                <!-- Right Column (4 cols): Live Room Availability & Quick Resources -->
                <div class="lg:col-span-4 space-y-6">
                    <!-- Widget C: Live Ketersediaan Ruangan (Available Now) -->
                    <Card class="overflow-hidden shadow-xs">
                        <div class="p-5">
                            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                                <div class="flex items-center gap-2">
                                    <div class="relative flex h-2.5 w-2.5">
                                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                                    </div>
                                    <h3 class="font-heading font-bold text-sm text-slate-900">
                                        Status Ruangan Live
                                    </h3>
                                </div>
                                <Link
                                    :href="route('rooms.index')"
                                    class="text-xs font-semibold text-brand-600 hover:text-brand-700 transition"
                                >
                                    Semua Ruangan
                                </Link>
                            </div>

                            <!-- List of Rooms with Live In-Use / Available indicator -->
                            <div class="mt-4 space-y-3">
                                <div
                                    v-for="room in roomStatuses"
                                    :key="room.id"
                                    class="p-3 rounded-xl border transition-all text-xs"
                                    :class="[
                                        room.status === 'maintenance'
                                            ? 'bg-slate-50/70 border-slate-200 text-slate-500'
                                            : room.is_occupied
                                            ? 'bg-rose-50/40 border-rose-200/80'
                                            : 'bg-emerald-50/30 border-emerald-200/70 hover:border-emerald-300'
                                    ]"
                                >
                                    <div class="flex items-start justify-between gap-2">
                                        <div>
                                            <div class="font-bold text-slate-900 text-xs">
                                                {{ room.name }}
                                            </div>
                                            <div class="text-slate-500 text-[11px] mt-0.5">
                                                {{ room.location }} • Kapasitas {{ room.capacity }} org
                                            </div>
                                        </div>

                                        <!-- Badge Status -->
                                        <div>
                                            <span
                                                v-if="room.status === 'maintenance'"
                                                class="px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 font-semibold text-[10px]"
                                            >
                                                Perawatan
                                            </span>
                                            <span
                                                v-else-if="room.is_occupied"
                                                class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-rose-100 text-rose-800 font-semibold text-[10px]"
                                            >
                                                <span class="w-1.5 h-1.5 rounded-full bg-rose-600"></span>
                                                <span>Dipakai</span>
                                            </span>
                                            <span
                                                v-else
                                                class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 font-semibold text-[10px]"
                                            >
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                                <span>Tersedia</span>
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Occupied Info or Booking Button -->
                                    <div class="mt-2.5 pt-2 border-t border-slate-200/60 flex items-center justify-between">
                                        <div v-if="room.is_occupied" class="text-[11px] text-slate-600 truncate max-w-[200px]">
                                            <span class="font-semibold text-slate-800">s/d {{ room.current_booking.end_time }}:</span>
                                            <span class="ml-1 text-slate-500 italic">{{ room.current_booking.title }}</span>
                                        </div>
                                        <div v-else-if="room.status === 'active'" class="text-[11px] text-emerald-700 font-medium">
                                            Siap digunakan sekarang
                                        </div>
                                        <div v-else class="text-[11px] text-slate-400">
                                            Sementara tidak dapat dipesan
                                        </div>

                                        <Link
                                            v-if="!room.is_occupied && room.status === 'active'"
                                            :href="route('reservations.create', { room_id: room.id })"
                                            class="text-[11px] font-bold text-brand-600 hover:text-brand-800 underline ml-auto"
                                        >
                                            Pesan &rarr;
                                        </Link>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </Card>

                    <!-- Widget D: Pusat Pintasan & Bantuan (Quick Resources) -->
                    <Card class="overflow-hidden shadow-xs bg-gradient-to-br from-slate-50 to-white">
                        <div class="p-5 space-y-3">
                            <h3 class="font-heading font-bold text-sm text-slate-900 pb-2 border-b border-slate-100">
                                Pintasan Navigasi
                            </h3>

                            <div class="space-y-2">
                                <Link
                                    v-if="stats.can_approve"
                                    :href="route('analytics.index')"
                                    class="flex items-center justify-between p-2.5 rounded-xl bg-white border border-slate-200/80 hover:border-brand-300 hover:shadow-xs transition group"
                                >
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-7 h-7 rounded-lg bg-teal-50 text-brand-600 flex items-center justify-center group-hover:bg-brand-600 group-hover:text-white transition">
                                            <PresentationChartLineIcon class="w-4 h-4" />
                                        </div>
                                        <div>
                                            <div class="font-bold text-xs text-slate-800">Analytics & Metrik</div>
                                            <div class="text-[10px] text-slate-500">Visual grafik tren & jam sibuk</div>
                                        </div>
                                    </div>
                                    <ChevronRightIcon class="w-3.5 h-3.5 text-slate-400 group-hover:text-brand-600 transition" />
                                </Link>

                                <Link
                                    :href="route('reservations.calendar')"
                                    class="flex items-center justify-between p-2.5 rounded-xl bg-white border border-slate-200/80 hover:border-brand-300 hover:shadow-xs transition group"
                                >
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center group-hover:bg-blue-600 group-hover:text-white transition">
                                            <CalendarDaysIcon class="w-4 h-4" />
                                        </div>
                                        <div>
                                            <div class="font-bold text-xs text-slate-800">Kalender Interaktif</div>
                                            <div class="text-[10px] text-slate-500">Jadwal bulanan & slot kosong</div>
                                        </div>
                                    </div>
                                    <ChevronRightIcon class="w-3.5 h-3.5 text-slate-400 group-hover:text-brand-600 transition" />
                                </Link>

                                <Link
                                    :href="route('rooms.index')"
                                    class="flex items-center justify-between p-2.5 rounded-xl bg-white border border-slate-200/80 hover:border-brand-300 hover:shadow-xs transition group"
                                >
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-7 h-7 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center group-hover:bg-amber-600 group-hover:text-white transition">
                                            <BuildingOffice2Icon class="w-4 h-4" />
                                        </div>
                                        <div>
                                            <div class="font-bold text-xs text-slate-800">Katalog Ruangan</div>
                                            <div class="text-[10px] text-slate-500">Fasilitas, mikrofon, proyektor</div>
                                        </div>
                                    </div>
                                    <ChevronRightIcon class="w-3.5 h-3.5 text-slate-400 group-hover:text-brand-600 transition" />
                                </Link>
                            </div>

                            <!-- Helpful Tip Box -->
                            <div class="mt-4 p-3 rounded-xl bg-teal-50/70 border border-teal-200/80 text-[11px] text-teal-900 space-y-1">
                                <div class="font-bold flex items-center gap-1.5 text-brand-800">
                                    <SparklesIcon class="w-3.5 h-3.5 text-brand-600" />
                                    <span>Tips Check-In Mandiri</span>
                                </div>
                                <p class="text-slate-600 leading-normal">
                                    Gunakan tiket QR Code di depan ruangan rapat mulai 15 menit sebelum acara untuk mengonfirmasi kehadiran tim Anda.
                                </p>
                            </div>
                        </div>
                    </Card>
                </div>
            </div>
        </div>

        <!-- Modal QR Code Check-In -->
        <Modal :show="isQrModalOpen" @close="closeQrModal" max-width="md">
            <div class="p-6" v-if="activeQrData">
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
                        <span>{{ activeQrData.room_name }}</span>
                    </div>
                    <div class="font-heading font-bold text-slate-900 text-sm">
                        {{ activeQrData.title }}
                    </div>
                    <div class="text-xs text-slate-600 flex items-center gap-1.5 pt-1">
                        <ClockIcon class="w-3.5 h-3.5 text-slate-400" />
                        <span>{{ activeQrData.date_formatted }} • {{ activeQrData.start_time }} - {{ activeQrData.end_time }} WIB</span>
                    </div>
                </div>

                <!-- QR Code Box -->
                <div class="flex justify-center my-6">
                    <div class="p-4 bg-white border-2 border-brand-200/80 rounded-2xl shadow-card inline-block">
                        <img
                            :src="route('reservations.qr-code', activeQrData.id)"
                            alt="QR Code Check-in"
                            class="w-52 h-52 mx-auto object-contain"
                        />
                    </div>
                </div>

                <!-- Info Check-in Status -->
                <div class="text-center text-xs">
                    <div
                        v-if="activeQrData.is_checked_in"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-emerald-50 text-emerald-800 border border-emerald-200 font-semibold"
                    >
                        <span>&check; Sudah Terverifikasi Check-In</span>
                    </div>
                    <div v-else class="text-slate-500 bg-slate-50 rounded-lg p-3 border border-slate-100 text-xs">
                        Tunjukkan atau scan QR ini menggunakan kamera ponsel di depan pintu ruangan untuk konfirmasi kehadiran.
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
