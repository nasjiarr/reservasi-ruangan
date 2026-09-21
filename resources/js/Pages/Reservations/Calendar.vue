<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
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
import {
    CalendarDaysIcon,
    TableCellsIcon,
    PlusIcon,
    XMarkIcon,
    BuildingOffice2Icon,
    ClockIcon,
    UserCircleIcon,
    InformationCircleIcon,
    DocumentTextIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
    events: {
        type: Array,
        default: () => [],
    },
});

const fullCalendarRef = ref(null);

// Modal detail event
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

const calendarOptions = {
    plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
    initialView: 'dayGridMonth',
    headerToolbar: {
        left: 'prev,next today',
        center: 'title',
        right: 'dayGridMonth,timeGridWeek,timeGridDay',
    },
    buttonText: {
        today: 'Hari Ini',
        month: 'Bulan',
        week: 'Minggu',
        day: 'Hari',
    },
    events: props.events,
    dateClick: handleDateClick,
    eventClick: handleEventClick,
    editable: false,
    selectable: true,
    dayMaxEvents: true,
    height: 'auto',
    eventTimeFormat: {
        hour: '2-digit',
        minute: '2-digit',
        hour12: false,
    },
};
</script>

<template>
    <Head title="Kalender Jadwal Reservasi" />

    <AuthenticatedLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <!-- Header Section -->
            <PageHeader
                title="Kalender Jadwal Ruangan"
                description="Visualisasi ketersediaan dan jadwal pemakaian seluruh ruangan secara real-time."
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

            <!-- Status Legend Card -->
            <div class="bg-white p-4 rounded-xl border border-slate-200/80 shadow-card flex flex-wrap items-center justify-between gap-4 text-sm">
                <div class="flex items-center gap-3">
                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Keterangan:</span>
                    <div class="flex flex-wrap items-center gap-4">
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-emerald-500 shadow-xs inline-block"></span>
                            <span class="text-xs font-semibold text-slate-700">Disetujui</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-amber-500 shadow-xs inline-block"></span>
                            <span class="text-xs font-semibold text-slate-700">Menunggu Persetujuan</span>
                        </div>
                    </div>
                </div>
                <div class="text-xs text-slate-400 flex items-center gap-1.5">
                    <InformationCircleIcon class="w-4 h-4 text-slate-400 shrink-0" />
                    <span>Klik pada tanggal kosong di kalender untuk membuat pemesanan langsung.</span>
                </div>
            </div>

            <!-- Calendar Container Card -->
            <Card :no-padding="true">
                <div class="p-6">
                    <FullCalendar ref="fullCalendarRef" :options="calendarOptions" />
                </div>
            </Card>
        </div>

        <!-- Detail Modal via Breeze Modal Component -->
        <Modal :show="isModalOpen" @close="closeModal">
            <div v-if="selectedEvent" class="p-6">
                <!-- Modal Header -->
                <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-teal-50 border border-teal-200/80 flex items-center justify-center text-brand-600">
                            <CalendarDaysIcon class="w-4 h-4" />
                        </div>
                        <h3 class="font-heading font-bold text-base text-slate-900">
                            Detail Reservasi Ruangan
                        </h3>
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
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <span class="text-[11px] uppercase font-bold text-slate-400 tracking-wider block">Kegiatan / Agenda</span>
                            <h4 class="font-heading font-bold text-lg text-slate-900 mt-0.5">
                                {{ selectedEvent.title }}
                            </h4>
                        </div>
                        <StatusBadge :status="selectedEvent.status" size="sm" class="shrink-0" />
                    </div>

                    <!-- Room & Requester Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
                        <div class="bg-slate-50 p-3 rounded-xl border border-slate-100">
                            <span class="text-[11px] uppercase font-semibold text-slate-400 block">Ruangan</span>
                            <div class="flex items-center gap-2 mt-1 font-semibold text-slate-800">
                                <BuildingOffice2Icon class="w-4 h-4 text-teal-600 shrink-0" />
                                <span>{{ selectedEvent.room_name }}</span>
                            </div>
                        </div>
                        <div class="bg-slate-50 p-3 rounded-xl border border-slate-100">
                            <span class="text-[11px] uppercase font-semibold text-slate-400 block">Pemesan</span>
                            <div class="flex items-center gap-2 mt-1 font-semibold text-slate-800">
                                <UserCircleIcon class="w-4 h-4 text-slate-500 shrink-0" />
                                <span class="truncate">{{ selectedEvent.user_name }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Schedule -->
                    <div class="bg-slate-50 p-3 rounded-xl border border-slate-100">
                        <span class="text-[11px] uppercase font-semibold text-slate-400 block">Waktu Pelaksanaan</span>
                        <div class="flex items-center gap-2 mt-1 text-slate-700 font-medium">
                            <ClockIcon class="w-4 h-4 text-slate-400 shrink-0" />
                            <span>{{ selectedEvent.start }}</span>
                            <span class="text-slate-400">&rarr;</span>
                            <span>{{ selectedEvent.end }}</span>
                        </div>
                    </div>

                    <!-- Description -->
                    <div v-if="selectedEvent.description" class="space-y-1 pt-1">
                        <span class="text-[11px] uppercase font-semibold text-slate-400 block">Keperluan / Keterangan</span>
                        <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-100 text-xs text-slate-600 whitespace-pre-line leading-relaxed">
                            {{ selectedEvent.description }}
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="mt-6 flex justify-end">
                    <SecondaryButton @click="closeModal">
                        Tutup
                    </SecondaryButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>

<style>
/* Styling FullCalendar selaras dengan Brand Nordic Teal & Deep Slate */
.fc .fc-toolbar-title {
    font-family: 'Plus Jakarta Sans', var(--font-heading, sans-serif) !important;
    font-size: 1.25rem !important;
    font-weight: 700 !important;
    color: #0f172a !important;
    letter-spacing: -0.02em !important;
}

.fc .fc-button-primary {
    background-color: #0d9488 !important;
    border-color: #0f766e !important;
    font-size: 0.8125rem !important;
    font-weight: 600 !important;
    border-radius: 0.5rem !important;
    padding: 0.45rem 0.85rem !important;
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05) !important;
    transition: all 0.15s ease-in-out !important;
}

.fc .fc-button-primary:hover {
    background-color: #0f766e !important;
    border-color: #115e59 !important;
}

.fc .fc-button-primary:disabled {
    background-color: #99f6e4 !important;
    border-color: #5eead4 !important;
    opacity: 0.6 !important;
}

.fc .fc-button-primary:not(:disabled).fc-button-active,
.fc .fc-button-primary:not(:disabled):active {
    background-color: #115e59 !important;
    border-color: #042f2e !important;
}

.fc-theme-standard th {
    background-color: #f8fafc !important;
    border-color: #e2e8f0 !important;
    padding: 0.6rem 0 !important;
    font-size: 0.75rem !important;
    font-weight: 600 !important;
    text-transform: uppercase !important;
    color: #64748b !important;
    letter-spacing: 0.05em !important;
}

.fc-theme-standard td {
    border-color: #f1f5f9 !important;
}

.fc .fc-day-today {
    background-color: rgba(13, 148, 136, 0.04) !important;
}

.fc-event {
    cursor: pointer !important;
    border-radius: 6px !important;
    padding: 2px 6px !important;
    font-size: 0.75rem !important;
    font-weight: 600 !important;
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.04) !important;
    transition: transform 0.15s ease !important;
}

.fc-event:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 4px 0 rgba(0, 0, 0, 0.08) !important;
}
</style>
