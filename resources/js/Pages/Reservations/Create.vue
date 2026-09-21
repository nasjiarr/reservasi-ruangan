<script setup>
import { ref, onMounted } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    rooms: {
        type: Array,
        default: () => [],
    },
});

const clientError = ref('');

const form = useForm({
    room_id: props.rooms.length > 0 ? props.rooms[0].id : '',
    title: '',
    description: '',
    start_time: '',
    end_time: '',
});

onMounted(() => {
    // Cek apakah ada query param date (misal saat klik dari halaman kalender)
    const urlParams = new URLSearchParams(window.location.search);
    const dateParam = urlParams.get('date');

    if (dateParam) {
        // Format datetime-local: YYYY-MM-DDTHH:mm
        const baseDate = dateParam.includes('T') ? dateParam.split('T')[0] : dateParam;
        form.start_time = `${baseDate}T09:00`;
        form.end_time = `${baseDate}T10:00`;
    }
});

const submit = () => {
    clientError.value = '';

    // Validasi client-side sederhana
    if (!form.start_time || !form.end_time) {
        clientError.value = 'Waktu mulai dan waktu selesai wajib diisi.';
        return;
    }

    const startDate = new Date(form.start_time);
    const endDate = new Date(form.end_time);

    if (endDate <= startDate) {
        clientError.value = 'Waktu selesai harus setelah waktu mulai.';
        return;
    }

    form.post(route('reservations.store'), {
        onError: () => {
            // Error dari server (seperti RoomAvailableRule) akan otomatis terisi di form.errors
        },
    });
};
</script>

<template>
    <Head title="Booking Ruangan" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Form Booking Ruangan
                </h2>
                <Link :href="route('reservations.index')">
                    <SecondaryButton>Kembali</SecondaryButton>
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Pilihan Ruangan -->
                        <div>
                            <InputLabel for="room_id" value="Pilih Ruangan *" />
                            <select
                                id="room_id"
                                v-model="form.room_id"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                required
                            >
                                <option value="" disabled>-- Pilih Ruangan --</option>
                                <option
                                    v-for="room in rooms"
                                    :key="room.id"
                                    :value="room.id"
                                >
                                    {{ room.name }} (Kapasitas: {{ room.capacity }} orang)
                                    <template v-if="room.location"> - {{ room.location }}</template>
                                </option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.room_id" />
                        </div>

                        <!-- Judul / Kegiatan -->
                        <div>
                            <InputLabel for="title" value="Judul / Nama Kegiatan *" />
                            <TextInput
                                id="title"
                                v-model="form.title"
                                type="text"
                                class="mt-1 block w-full"
                                required
                                autofocus
                                placeholder="Contoh: Rapat Evaluasi Bulanan Divisi IT"
                            />
                            <InputError class="mt-2" :message="form.errors.title" />
                        </div>

                        <!-- Deskripsi -->
                        <div>
                            <InputLabel for="description" value="Deskripsi / Keperluan (Opsional)" />
                            <textarea
                                id="description"
                                v-model="form.description"
                                rows="3"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                placeholder="Jelaskan agenda pertemuan, peralatan tambahan yang diperlukan, dll."
                            />
                            <InputError class="mt-2" :message="form.errors.description" />
                        </div>

                        <!-- Waktu Mulai & Waktu Selesai (Grid) -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <InputLabel for="start_time" value="Waktu Mulai *" />
                                <TextInput
                                    id="start_time"
                                    v-model="form.start_time"
                                    type="datetime-local"
                                    class="mt-1 block w-full"
                                    required
                                />
                                <InputError class="mt-2" :message="form.errors.start_time" />
                            </div>

                            <div>
                                <InputLabel for="end_time" value="Waktu Selesai *" />
                                <TextInput
                                    id="end_time"
                                    v-model="form.end_time"
                                    type="datetime-local"
                                    class="mt-1 block w-full"
                                    required
                                />
                                <!-- Client-side error -->
                                <div v-if="clientError" class="mt-2 text-sm text-red-600 font-medium">
                                    {{ clientError }}
                                </div>
                                <!-- Server-side error (termasuk bentrok jadwal dari RoomAvailableRule) -->
                                <InputError class="mt-2 font-medium" :message="form.errors.end_time" />
                            </div>
                        </div>

                        <!-- Warning / Helper Box -->
                        <div class="rounded-md bg-blue-50 p-4 border border-blue-200">
                            <div class="flex">
                                <div class="shrink-0">
                                    <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ms-3 text-sm text-blue-700">
                                    <p>Sistem akan otomatis memeriksa ketersediaan ruangan. Pengajuan reservasi Anda akan berstatus <strong>Pending</strong> sampai disetujui oleh Manager atau Administrator.</p>
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center justify-end space-x-4 pt-4 border-t">
                            <Link :href="route('reservations.index')">
                                <SecondaryButton type="button">Batal</SecondaryButton>
                            </Link>
                            <PrimaryButton :disabled="form.processing">
                                <span v-if="form.processing">Memproses Reservasi...</span>
                                <span v-else>Ajukan Reservasi</span>
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

