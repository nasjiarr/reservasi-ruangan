<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import PageHeader from '@/Components/PageHeader.vue';
import Card from '@/Components/Card.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import Modal from '@/Components/Modal.vue';
import FullCalendar from '@fullcalendar/vue3';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';
import idLocale from '@fullcalendar/core/locales/id';
import {
    CalendarDaysIcon,
    TableCellsIcon,
    PlusIcon,
    XMarkIcon,
    BuildingOffice2Icon,
    ClockIcon,
    UserCircleIcon,
    FunnelIcon,
    MagnifyingGlassIcon,
    ArrowTopRightOnSquareIcon,
    InformationCircleIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
    events: {
        type: Array,
        default: () => [],
    },
    rooms: {
        type: Array,
        default: () => [],
    },
});

const fullCalendarRef = ref(null);

// Filters State
const selectedRoomId = ref('all');
const selectedStatus = ref('all');
const searchQuery = ref('');

// Filtered Events Computed
const filteredEvents = computed(() => {
    return props.events.filter((event) => {
        // Room Filter
        if (selectedRoomId.value !== 'all' && String(event.extendedProps?.room_id) !== String(selectedRoomId.value)) {
            return false;
        }

        // Status Filter
        if (selectedStatus.value !== 'all' && event.extendedProps?.status !== selectedStatus.value) {
            return false;
        }

        // Search Query (title, room_name, user_name)
        if (searchQuery.value.trim() !== '') {
            const q = searchQuery.value.toLowerCase().trim();
            const matchTitle = (event.title || '').toLowerCase().includes(q);
            const matchRoom = (event.extendedProps?.room_name || '').toLowerCase().includes(q);
            const matchUser = (event.extendedProps?.user_name || '').toLowerCase().includes(q);
            if (!matchTitle && !matchRoom && !matchUser) return false;
        }

        return true;
    });
});

// Modal Detail Event
const isModalOpen = ref(false);
const selectedEvent = ref(null);

// Real-time broadcast handler
const handleReservationBroadcast = (eventData) => {
    if (!fullCalendarRef.value) return;

    const calendarApi = fullCalendarRef.value.getApi();
    const existingEvent = calendarApi.getEventById(eventData.id);

    if (existingEvent) {
        if (eventData.status === 'cancelled' || eventData.status === 'rejected') {
            existingEvent.remove();
        } else {
            existingEvent.setProp('title', eventData.title);
            existingEvent.setProp('backgroundColor', eventData.backgroundColor);
            existingEvent.setProp('borderColor', eventData.borderColor);
            existingEvent.setDates(eventData.start, eventData.end);
            existingEvent.setExtendedProp('status', eventData.extendedProps?.status);
            existingEvent.setExtendedProp('description', eventData.extendedProps?.description);
            existingEvent.setExtendedProp('start_formatted', eventData.extendedProps?.start_formatted);
            existingEvent.setExtendedProp('end_formatted', eventData.extendedProps?.end_formatted);
        }
    } else {
        if (eventData.status === 'pending' || eventData.status === 'approved') {
            calendarApi.addEvent({
                id: eventData.id,
                title: eventData.title,
                start: eventData.start,
                end: eventData.end,
                backgroundColor: eventData.backgroundColor,
                borderColor: eventData.borderColor,
                extendedProps: eventData.extendedProps,
            });
        }
    }
};

onMounted(() => {
    if (window.Echo) {
        window.Echo.private('reservations')
            .listen('.ReservationStatusChanged', handleReservationBroadcast)
            .listen('ReservationStatusChanged', handleReservationBroadcast);
    }
});

onUnmounted(() => {
    if (window.Echo) {
        window.Echo.leave('reservations');
    }
});

const handleDateClick = (info) => {
    router.visit(route('reservations.create', { date: info.dateStr }));
};

const handleEventClick = (info) => {
    selectedEvent.value = {
        id: info.event.id,
        title: info.event.title,
        start: info.event.extendedProps?.start_formatted || info.event.startStr,
        end: info.event.extendedProps?.end_formatted || info.event.endStr,
        room_name: info.event.extendedProps?.room_name || '-',
        user_name: info.event.extendedProps?.user_name || '-',
        description: info.event.extendedProps?.description || '',
        status: info.event.extendedProps?.status || 'pending',
    };
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    selectedEvent.value = null;
};

// Custom Event HTML Rendering for Clean SaaS Appearance
const renderEventContent = (arg) => {
    const isWeekOrDay = arg.view.type.includes('timeGrid');
    const isApproved = arg.event.extendedProps?.status === 'approved';
    const roomName = arg.event.extendedProps?.room_name || '';
    const timeText = arg.timeText || '';
    const title = arg.event.title || '';

    if (!isWeekOrDay) {
        // Month View: Clean pill with dot, time, and title
        const dotColor = isApproved ? 'bg-emerald-500' : 'bg-amber-500';
        const textColor = isApproved ? 'text-emerald-950' : 'text-amber-950';
        return {
            html: `
                <div class="fc-custom-month-event flex items-center gap-1.5 px-2 py-0.5 w-full overflow-hidden text-[11px] leading-tight">
                    <span class="w-1.5 h-1.5 rounded-full shrink-0 ${dotColor}"></span>
                    <span class="font-bold shrink-0 opacity-75">${timeText}</span>
                    <span class="truncate font-semibold ${textColor}">${title}</span>
                </div>
            `,
        };
    }

    // Week / Day View: Structured event card
    const headerColor = isApproved ? 'text-emerald-700' : 'text-amber-700';
    const bodyColor = isApproved ? 'text-emerald-900' : 'text-amber-900';
    return {
        html: `
            <div class="fc-custom-timegrid-event p-1.5 h-full flex flex-col justify-between overflow-hidden">
                <div>
                    <div class="text-[10px] font-bold ${headerColor} uppercase tracking-wider truncate flex items-center gap-1">
                        <span>${roomName}</span>
                    </div>
                    <div class="font-bold text-xs ${bodyColor} leading-snug mt-0.5 line-clamp-2">
                        ${title}
                    </div>
                </div>
                <div class="text-[10px] font-medium opacity-75 mt-1">
                    ${timeText}
                </div>
            </div>
        `,
    };
};

const calendarOptions = computed(() => ({
    plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
    locales: [idLocale],
    locale: 'id',
    initialView: 'dayGridMonth',
    headerToolbar: {
        left: 'prev,next today',
        center: 'title',
        right: 'dayGridMonth,timeGridWeek,timeGridDay',
    },
    buttonIcons: {
        prev: 'chevron-left',
        next: 'chevron-right',
    },
    buttonText: {
        today: 'Hari Ini',
        month: 'Bulan',
        week: 'Minggu',
        day: 'Hari',
    },
    events: filteredEvents.value,
    dateClick: handleDateClick,
    eventClick: handleEventClick,
    eventContent: renderEventContent,
    editable: false,
    selectable: true,
    dayMaxEvents: 3,
    dayMaxEventRows: 3,
    moreLinkClick: 'popover',
    moreLinkText: (n) => `+${n} lainnya`,
    slotMinTime: '07:00:00',
    slotMaxTime: '21:00:00',
    slotDuration: '00:30:00',
    slotLabelInterval: '01:00:00',
    allDaySlot: false,
    nowIndicator: true,
    expandRows: true,
    height: 'auto',
    eventTimeFormat: {
        hour: '2-digit',
        minute: '2-digit',
        hour12: false,
    },
}));
</script>

<template>
    <Head title="Kalender Jadwal Reservasi" />

    <AuthenticatedLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <!-- Header Section -->
            <PageHeader
                title="Kalender Jadwal Ruangan"
                description="Visualisasi ketersediaan jadwal pemakaian seluruh ruangan secara real-time dan terstruktur."
                :badge="`${filteredEvents.length} Jadwal Tampil`"
            >
                <template #actions>
                    <Link :href="route('reservations.index')">
                        <SecondaryButton class="gap-2 shadow-xs">
                            <TableCellsIcon class="w-4 h-4 text-slate-500" />
                            <span>Daftar Tabel</span>
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

            <!-- Interactive Filter & Legend Toolbar -->
            <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-xs space-y-4">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                    <!-- Filters: Ruangan & Status -->
                    <div class="flex flex-wrap items-center gap-3">
                        <!-- Room Dropdown Filter -->
                        <div class="flex items-center gap-2">
                            <div class="relative min-w-[200px]">
                                <select
                                    v-model="selectedRoomId"
                                    class="w-full text-xs font-semibold text-slate-700 bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 pr-8 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition"
                                >
                                    <option value="all">Semua Ruangan</option>
                                    <option v-for="room in rooms" :key="room.id" :value="room.id">
                                        {{ room.name }}
                                    </option>
                                </select>
                            </div>
                        </div>

                        <!-- Status Pill Filters -->
                        <div class="inline-flex p-1 bg-slate-100 rounded-xl text-xs font-semibold text-slate-600">
                            <button
                                type="button"
                                @click="selectedStatus = 'all'"
                                class="px-3 py-1.5 rounded-lg transition"
                                :class="selectedStatus === 'all' ? 'bg-white text-slate-900 shadow-xs font-bold' : 'hover:text-slate-900'"
                            >
                                Semua Status
                            </button>
                            <button
                                type="button"
                                @click="selectedStatus = 'approved'"
                                class="px-3 py-1.5 rounded-lg transition flex items-center gap-1.5"
                                :class="selectedStatus === 'approved' ? 'bg-white text-emerald-700 shadow-xs font-bold' : 'hover:text-emerald-700'"
                            >
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                <span>Disetujui</span>
                            </button>
                            <button
                                type="button"
                                @click="selectedStatus = 'pending'"
                                class="px-3 py-1.5 rounded-lg transition flex items-center gap-1.5"
                                :class="selectedStatus === 'pending' ? 'bg-white text-amber-700 shadow-xs font-bold' : 'hover:text-amber-700'"
                            >
                                <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                                <span>Menunggu</span>
                            </button>
                        </div>
                    </div>

                    <!-- Search Input Filter -->
                    <div class="relative min-w-[240px]">
                        <MagnifyingGlassIcon class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
                        <input
                            type="text"
                            v-model="searchQuery"
                            placeholder="Cari kegiatan atau pemesan..."
                            class="w-full text-xs pl-9 pr-8 py-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 placeholder-slate-400 focus:bg-white focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition"
                        />
                        <button
                            v-if="searchQuery"
                            type="button"
                            @click="searchQuery = ''"
                            class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600"
                        >
                            <XMarkIcon class="w-3.5 h-3.5" />
                        </button>
                    </div>
                </div>

                <!-- Info Hint Bar -->
                <div class="pt-3 border-t border-slate-100 flex flex-wrap items-center justify-between gap-3 text-xs text-slate-500">
                    <div class="flex items-center gap-2">
                        <span class="font-medium">Menampilkan:</span>
                        <span class="font-bold text-slate-800">{{ filteredEvents.length }} kegiatan</span>
                        <span v-if="selectedRoomId !== 'all' || selectedStatus !== 'all' || searchQuery" class="text-teal-600 font-medium">
                            (Difilter)
                        </span>
                    </div>
                    <div class="flex items-center gap-1.5 text-slate-400">
                        <InformationCircleIcon class="w-4 h-4 text-slate-400 shrink-0" />
                        <span>Klik pada tanggal kosong untuk membuat pemesanan, atau klik pada event untuk melihat rincian kegiatan.</span>
                    </div>
                </div>
            </div>

            <!-- Calendar Container Card -->
            <Card :no-padding="true" class="shadow-xs overflow-hidden">
                <div class="p-4 sm:p-6 fullcalendar-container">
                    <FullCalendar ref="fullCalendarRef" :options="calendarOptions" />
                </div>
            </Card>
        </div>

        <!-- Detail Modal via Breeze Modal Component -->
        <Modal :show="isModalOpen" @close="closeModal" max-width="lg">
            <div v-if="selectedEvent" class="p-6">
                <!-- Modal Header -->
                <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-teal-50 border border-teal-200/80 flex items-center justify-center text-brand-600">
                            <CalendarDaysIcon class="w-4 h-4" />
                        </div>
                        <div>
                            <h3 class="font-heading font-bold text-base text-slate-900">
                                Detail Reservasi Ruangan
                            </h3>
                            <p class="text-xs text-slate-500">Informasi lengkap permohonan peminjaman ruangan</p>
                        </div>
                    </div>
                    <button
                        @click="closeModal"
                        class="p-1 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition"
                    >
                        <XMarkIcon class="w-5 h-5" />
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="mt-5 space-y-4 text-sm">
                    <!-- Title & Status -->
                    <div class="flex items-start justify-between gap-3 bg-slate-50/70 p-4 rounded-xl border border-slate-100">
                        <div>
                            <span class="text-[11px] uppercase font-bold text-slate-400 tracking-wider block">Agenda Kegiatan</span>
                            <h4 class="font-heading font-extrabold text-lg text-slate-900 mt-0.5">
                                {{ selectedEvent.title }}
                            </h4>
                        </div>
                        <StatusBadge :status="selectedEvent.status" size="sm" class="shrink-0" />
                    </div>

                    <!-- Room & Requester Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="bg-white p-3.5 rounded-xl border border-slate-200/80 shadow-2xs">
                            <span class="text-[11px] uppercase font-bold text-slate-400 block tracking-wider">Ruangan Terpilih</span>
                            <div class="flex items-center gap-2 mt-1 font-bold text-slate-800">
                                <BuildingOffice2Icon class="w-4 h-4 text-brand-600 shrink-0" />
                                <span>{{ selectedEvent.room_name }}</span>
                            </div>
                        </div>
                        <div class="bg-white p-3.5 rounded-xl border border-slate-200/80 shadow-2xs">
                            <span class="text-[11px] uppercase font-bold text-slate-400 block tracking-wider">Pemohon / Penyelenggara</span>
                            <div class="flex items-center gap-2 mt-1 font-bold text-slate-800">
                                <UserCircleIcon class="w-4 h-4 text-slate-400 shrink-0" />
                                <span class="truncate">{{ selectedEvent.user_name }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Schedule -->
                    <div class="bg-white p-3.5 rounded-xl border border-slate-200/80 shadow-2xs">
                        <span class="text-[11px] uppercase font-bold text-slate-400 block tracking-wider">Waktu Pelaksanaan</span>
                        <div class="flex items-center gap-2 mt-1 text-slate-800 font-semibold text-xs sm:text-sm">
                            <ClockIcon class="w-4 h-4 text-brand-600 shrink-0" />
                            <span>{{ selectedEvent.start }}</span>
                            <span class="text-slate-400">&rarr;</span>
                            <span>{{ selectedEvent.end }}</span>
                        </div>
                    </div>

                    <!-- Description -->
                    <div v-if="selectedEvent.description" class="space-y-1">
                        <span class="text-[11px] uppercase font-bold text-slate-400 tracking-wider block">Keterangan / Keperluan</span>
                        <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-100 text-xs text-slate-600 whitespace-pre-line leading-relaxed">
                            {{ selectedEvent.description }}
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="mt-6 flex items-center justify-between pt-4 border-t border-slate-100">
                    <Link
                        :href="route('reservations.show', selectedEvent.id)"
                        class="inline-flex items-center gap-1.5 text-xs font-bold text-brand-600 hover:text-brand-700 transition"
                    >
                        <span>Halaman Reservasi Lengkap</span>
                        <ArrowTopRightOnSquareIcon class="w-3.5 h-3.5" />
                    </Link>
                    <SecondaryButton @click="closeModal">
                        Tutup
                    </SecondaryButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>

<style>
/* -------------------------------------------------------------------------- */
/* FullCalendar Professional SaaS Styling (Nordic Teal & Clean Slate)         */
/* -------------------------------------------------------------------------- */

/* Header Toolbar Title */
.fullcalendar-container .fc .fc-toolbar-title {
    font-family: 'Plus Jakarta Sans', var(--font-heading, sans-serif) !important;
    font-size: 1.15rem !important;
    font-weight: 800 !important;
    color: #0f172a !important;
    letter-spacing: -0.02em !important;
}

/* Primary Toolbar Buttons */
.fullcalendar-container .fc .fc-button-primary {
    background-color: #ffffff !important;
    border: 1px solid #cbd5e1 !important;
    color: #334155 !important;
    font-size: 0.75rem !important;
    font-weight: 700 !important;
    border-radius: 0.625rem !important;
    padding: 0.4rem 0.8rem !important;
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.03) !important;
    transition: all 0.15s ease-in-out !important;
}

.fullcalendar-container .fc .fc-button-primary:hover {
    background-color: #f8fafc !important;
    border-color: #94a3b8 !important;
    color: #0f172a !important;
}

.fullcalendar-container .fc .fc-button-primary:disabled {
    background-color: #f1f5f9 !important;
    border-color: #e2e8f0 !important;
    color: #94a3b8 !important;
    opacity: 0.7 !important;
}

.fullcalendar-container .fc .fc-button-primary:not(:disabled).fc-button-active,
.fullcalendar-container .fc .fc-button-primary:not(:disabled):active {
    background-color: #0d9488 !important;
    border-color: #0d9488 !important;
    color: #ffffff !important;
    box-shadow: 0 1px 3px 0 rgba(13, 148, 136, 0.25) !important;
}

/* Day Header Cell (Sen, Sel, Rab, dll.) */
.fullcalendar-container .fc-theme-standard th {
    background-color: #f8fafc !important;
    border-color: #e2e8f0 !important;
    padding: 0.65rem 0 !important;
    font-size: 0.75rem !important;
    font-weight: 700 !important;
    text-transform: uppercase !important;
    color: #64748b !important;
    letter-spacing: 0.05em !important;
}

.fullcalendar-container .fc-theme-standard td {
    border-color: #f1f5f9 !important;
}

/* Today Cell Highlight */
.fullcalendar-container .fc .fc-day-today {
    background-color: rgba(13, 148, 136, 0.035) !important;
}

.fullcalendar-container .fc .fc-day-today .fc-daygrid-day-number {
    background-color: #0d9488 !important;
    color: #ffffff !important;
    border-radius: 9999px !important;
    width: 24px !important;
    height: 24px !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    margin: 4px !important;
    font-weight: 700 !important;
    font-size: 0.75rem !important;
}

/* Event Base Styles */
.fullcalendar-container .fc-event {
    cursor: pointer !important;
    border-radius: 6px !important;
    margin-bottom: 2px !important;
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.03) !important;
    transition: transform 0.15s ease, box-shadow 0.15s ease !important;
    border-width: 1px !important;
}

.fullcalendar-container .fc-event:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 5px 0 rgba(0, 0, 0, 0.08) !important;
}

/* Month Event (Pill Appearance with left accent border) */
.fullcalendar-container .fc-daygrid-event {
    border-left-width: 3px !important;
    padding: 1px 0 !important;
}

/* TimeGrid Events (Week & Day view) */
.fullcalendar-container .fc-timegrid-event {
    border-left-width: 4px !important;
    border-radius: 8px !important;
}

/* Now Indicator Line (Live Red/Teal time bar) */
.fullcalendar-container .fc .fc-timegrid-now-indicator-line {
    border-color: #0d9488 !important;
    border-width: 2px !important;
}

.fullcalendar-container .fc .fc-timegrid-now-indicator-arrow {
    border-color: #0d9488 !important;
}

/* Day More Link (+2 lainnya) */
.fullcalendar-container .fc-daygrid-more-link {
    font-size: 0.75rem !important;
    font-weight: 700 !important;
    color: #0d9488 !important;
    padding: 1px 6px !important;
    border-radius: 4px !important;
    transition: background-color 0.15s !important;
}

.fullcalendar-container .fc-daygrid-more-link:hover {
    background-color: #f0fdfa !important;
    text-decoration: none !important;
}

/* Popover for (+more) */
.fc-popover {
    border-radius: 1rem !important;
    border: 1px solid #e2e8f0 !important;
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1) !important;
    overflow: hidden !important;
}

.fc-popover-header {
    background-color: #f8fafc !important;
    padding: 0.5rem 0.75rem !important;
    font-weight: 700 !important;
    font-size: 0.8125rem !important;
    color: #0f172a !important;
    border-bottom: 1px solid #e2e8f0 !important;
}

.fc-popover-body {
    padding: 0.75rem !important;
}
</style>
