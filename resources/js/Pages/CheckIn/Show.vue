<script setup>
import { Head, useForm, usePage } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';

const props = defineProps({
    isValid: Boolean,
    status: String,
    statusMessage: String,
    canCheckIn: Boolean,
    reservation: Object,
    checkIn: Object,
});

const page = usePage();

const form = useForm({});

const submitCheckIn = () => {
    if (!props.checkIn?.qr_token) return;
    form.post(route('checkin.confirm', props.checkIn.qr_token));
};
</script>

<template>
    <Head title="Konfirmasi Check-In Ruangan" />

    <div class="min-h-screen bg-gray-100 flex flex-col justify-center items-center px-4 py-8 sm:px-6 lg:px-8">
        <!-- Logo & Header -->
        <div class="sm:mx-auto sm:w-full sm:max-w-md text-center">
            <ApplicationLogo class="mx-auto h-12 w-auto fill-current text-indigo-600" />
            <h2 class="mt-4 text-2xl font-bold tracking-tight text-gray-900">
                Check-In Ruangan
            </h2>
            <p class="mt-1 text-sm text-gray-500">
                Sistem Reservasi & Penggunaan Fasilitas Ruangan
            </p>
        </div>

        <div class="mt-6 sm:mx-auto sm:w-full sm:max-w-lg">
            <div class="bg-white py-8 px-6 shadow-md sm:rounded-xl border border-gray-100 sm:px-10">
                <!-- Flash Success / Error Message -->
                <div
                    v-if="$page.props.flash?.success"
                    class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg text-sm flex items-center space-x-2"
                >
                    <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    <span>{{ $page.props.flash.success }}</span>
                </div>

                <div
                    v-if="$page.props.flash?.error"
                    class="mb-6 p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg text-sm flex items-center space-x-2"
                >
                    <svg class="w-5 h-5 text-red-500 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                    <span>{{ $page.props.flash.error }}</span>
                </div>

                <!-- Token Invalid State -->
                <div v-if="!isValid" class="text-center py-6">
                    <div class="mx-auto flex items-center justify-center h-14 w-14 rounded-full bg-red-100 text-red-600 mb-4">
                        <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">QR Code Tidak Valid</h3>
                    <p class="mt-2 text-sm text-gray-500">{{ statusMessage }}</p>
                </div>

                <!-- Reservation Detail -->
                <div v-else class="space-y-6">
                    <!-- Status Banner -->
                    <div
                        v-if="status === 'already_checked_in'"
                        class="p-4 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-start space-x-3"
                    >
                        <svg class="w-5 h-5 text-emerald-500 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <div>
                            <span class="font-bold block">Sudah Check-In</span>
                            <span>{{ statusMessage }}</span>
                        </div>
                    </div>

                    <div
                        v-else-if="status === 'too_early'"
                        class="p-4 rounded-lg bg-amber-50 border border-amber-200 text-amber-800 text-sm flex items-start space-x-3"
                    >
                        <svg class="w-5 h-5 text-amber-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div>
                            <span class="font-bold block">Belum Waktunya Check-In</span>
                            <span>{{ statusMessage }}</span>
                        </div>
                    </div>

                    <div
                        v-else-if="status === 'expired'"
                        class="p-4 rounded-lg bg-gray-100 border border-gray-200 text-gray-700 text-sm flex items-start space-x-3"
                    >
                        <svg class="w-5 h-5 text-gray-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <div>
                            <span class="font-bold block">Sesi Selesai</span>
                            <span>{{ statusMessage }}</span>
                        </div>
                    </div>

                    <div
                        v-else-if="canCheckIn"
                        class="p-4 rounded-lg bg-blue-50 border border-blue-200 text-blue-800 text-sm flex items-start space-x-3"
                    >
                        <svg class="w-5 h-5 text-blue-500 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                        <div>
                            <span class="font-bold block">Check-In Siap</span>
                            <span>{{ statusMessage }}</span>
                        </div>
                    </div>

                    <!-- Detail Box -->
                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-200 space-y-3">
                        <div>
                            <span class="text-xs uppercase text-gray-400 font-semibold block">Ruangan</span>
                            <span class="text-base font-bold text-gray-900">{{ reservation.room_name }}</span>
                            <span v-if="reservation.room_location" class="text-xs text-gray-500 block">
                                Lokasi: {{ reservation.room_location }}
                            </span>
                        </div>

                        <div>
                            <span class="text-xs uppercase text-gray-400 font-semibold block">Kegiatan</span>
                            <span class="text-sm font-medium text-gray-800">{{ reservation.title }}</span>
                        </div>

                        <div class="grid grid-cols-2 gap-2 pt-1 border-t border-gray-200">
                            <div>
                                <span class="text-xs uppercase text-gray-400 font-semibold block">Mulai</span>
                                <span class="text-xs font-semibold text-gray-700">{{ reservation.start_time }}</span>
                            </div>
                            <div>
                                <span class="text-xs uppercase text-gray-400 font-semibold block">Selesai</span>
                                <span class="text-xs font-semibold text-gray-700">{{ reservation.end_time }}</span>
                            </div>
                        </div>

                        <div class="pt-1 border-t border-gray-200">
                            <span class="text-xs uppercase text-gray-400 font-semibold block">Pemesan</span>
                            <span class="text-sm text-gray-700">{{ reservation.user_name }}</span>
                        </div>
                    </div>

                    <!-- Action Button -->
                    <div v-if="canCheckIn" class="pt-2">
                        <form @submit.prevent="submitCheckIn">
                            <PrimaryButton
                                class="w-full justify-center !py-3 !text-sm !font-bold"
                                :disabled="form.processing"
                            >
                                <span v-if="form.processing">Memproses Kehadiran...</span>
                                <span v-else>Konfirmasi Kehadiran</span>
                            </PrimaryButton>
                        </form>
                    </div>

                    <div v-else-if="status === 'already_checked_in'" class="text-center pt-2">
                        <span class="inline-flex items-center px-4 py-2 bg-emerald-100 text-emerald-800 rounded-full font-semibold text-sm">
                            Kehadiran Telah Dikonfirmasi
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

