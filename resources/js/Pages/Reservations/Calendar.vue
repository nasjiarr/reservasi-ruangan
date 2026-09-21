<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import Modal from '@/Components/Modal.vue';
import FullCalendar from '@fullcalendar/vue3';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';

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
        // Jika statusnya dibatalkan atau ditolak, kita dapat menghapusnya atau update warna
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
        // Tambahkan event baru ke FullCalendar jika statusnya pending atau approved
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
    // Redirect ke halaman create dengan query param date
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

const getStatusBadge = (status) => {
    switch (status) {
        case 'approved':
            return { label: 'Disetujui', class: 'bg-emerald-100 text-emerald-800 border-emerald-300' };
        case 'pending':
            return { label: 'Menunggu Persetujuan', class: 'bg-amber-100 text-amber-800 border-amber-300' };
        case 'rejected':
            return { label: 'Ditolak', class: 'bg-red-100 text-red-800 border-red-300' };
        case 'cancelled':
            return { label: 'Dibatalkan', class: 'bg-gray-100 text-gray-800 border-gray-300' };
        default:
            return { label: status, class: 'bg-blue-100 text-blue-800 border-blue-300' };
    }
};
</script>

<template>
    <Head title="Kalender Jadwal Reservasi" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Kalender Jadwal Ruangan
                </h2>
                <div class="flex items-center space-x-3">
                    <Link :href="route('reservations.index')">
                        <SecondaryButton>Daftar Tabel</SecondaryButton>
                    </Link>
                    <Link :href="route('reservations.create')">
                        <PrimaryButton>+ Booking Ruangan</PrimaryButton>
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- Status Legend -->
                <div class="bg-white p-4 shadow-sm sm:rounded-lg flex flex-wrap items-center justify-between gap-4 text-sm">
                    <div class="text-gray-500 font-medium">Petunjuk Warna:</div>
                    <div class="flex flex-wrap items-center gap-4">
                        <div class="flex items-center space-x-1.5">
                            <span class="w-3.5 h-3.5 rounded-full bg-emerald-500 inline-block"></span>
                            <span class="text-gray-700">Disetujui</span>
                        </div>
                        <div class="flex items-center space-x-1.5">
                            <span class="w-3.5 h-3.5 rounded-full bg-amber-500 inline-block"></span>
                            <span class="text-gray-700">Menunggu (Pending)</span>
                        </div>
                    </div>
                    <div class="text-xs text-gray-400 italic">
                        * Klik pada tanggal kosong untuk membuat reservasi langsung.
                    </div>
                </div>

                <!-- Calendar Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <FullCalendar ref="fullCalendarRef" :options="calendarOptions" />
                </div>
            </div>
        </div>

        <!-- Detail Modal via Breeze Modal Component -->
        <Modal :show="isModalOpen" @close="closeModal">
            <div v-if="selectedEvent" class="p-6">
                <div class="flex items-center justify-between border-b pb-3">
                    <h3 class="text-lg font-semibold text-gray-900">
                        Detail Reservasi
                    </h3>
                    <button
                        @click="closeModal"
                        class="text-gray-400 hover:text-gray-600 focus:outline-none"
                    >
                        ✕
                    </button>
                </div>

                <div class="mt-4 space-y-4 text-sm">
                    <div>
                        <span class="text-gray-500 block text-xs uppercase font-medium">Kegiatan / Judul</span>
                        <p class="text-base font-semibold text-gray-800">{{ selectedEvent.title }}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <span class="text-gray-500 block text-xs uppercase font-medium">Ruangan</span>
                            <p class="font-medium text-gray-800">{{ selectedEvent.room_name }}</p>
                        </div>
                        <div>
                            <span class="text-gray-500 block text-xs uppercase font-medium">Status</span>
                            <span
                                :class="getStatusBadge(selectedEvent.status).class"
                                class="mt-1 px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full border"
                            >
                                {{ getStatusBadge(selectedEvent.status).label }}
                            </span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <span class="text-gray-500 block text-xs uppercase font-medium">Waktu Mulai</span>
                            <p class="text-gray-700">{{ selectedEvent.start }}</p>
                        </div>
                        <div>
                            <span class="text-gray-500 block text-xs uppercase font-medium">Waktu Selesai</span>
                            <p class="text-gray-700">{{ selectedEvent.end }}</p>
                        </div>
                    </div>

                    <div>
                        <span class="text-gray-500 block text-xs uppercase font-medium">Pemesan</span>
                        <p class="text-gray-700">{{ selectedEvent.user_name }}</p>
                    </div>

                    <div v-if="selectedEvent.description">
                        <span class="text-gray-500 block text-xs uppercase font-medium">Deskripsi / Keperluan</span>
                        <p class="text-gray-700 whitespace-pre-line mt-1 bg-gray-50 p-3 rounded border">
                            {{ selectedEvent.description }}
                        </p>
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <SecondaryButton @click="closeModal">Tutup</SecondaryButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>

<style>
/* Penyesuaian tampilan FullCalendar agar selaras dengan Tailwind */
.fc .fc-toolbar-title {
    font-size: 1.25rem !important;
    font-weight: 600 !important;
    color: #1f2937 !important;
}
.fc .fc-button-primary {
    background-color: #4f46e5 !important;
    border-color: #4338ca !important;
    font-size: 0.875rem !important;
    font-weight: 500 !important;
    text-transform: capitalize !important;
    border-radius: 0.375rem !important;
}
.fc .fc-button-primary:hover {
    background-color: #4338ca !important;
}
.fc .fc-button-primary:disabled {
    background-color: #a5b4fc !important;
    border-color: #a5b4fc !important;
}
.fc-event {
    cursor: pointer !important;
    border-radius: 4px !important;
    padding: 2px 4px !important;
    font-size: 0.8rem !important;
}
</style>

