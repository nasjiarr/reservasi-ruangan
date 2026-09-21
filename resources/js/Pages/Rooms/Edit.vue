<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    room: {
        type: Object,
        required: true,
    },
    facilities: {
        type: Array,
        default: () => [],
    },
});

const form = useForm({
    name: props.room.name || '',
    location: props.room.location || '',
    capacity: props.room.capacity || 1,
    description: props.room.description || '',
    status: props.room.status || 'active',
    facilities: props.room.facilities?.map((f) => f.id) || [],
});

const submit = () => {
    form.put(route('rooms.update', props.room.id));
};
</script>

<template>
    <Head :title="`Edit Ruangan: ${room.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Edit Ruangan: {{ room.name }}
                </h2>
                <Link :href="route('rooms.index')">
                    <SecondaryButton>Kembali</SecondaryButton>
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Nama Ruangan -->
                        <div>
                            <InputLabel for="name" value="Nama Ruangan *" />
                            <TextInput
                                id="name"
                                v-model="form.name"
                                type="text"
                                class="mt-1 block w-full"
                                required
                                autofocus
                            />
                            <InputError class="mt-2" :message="form.errors.name" />
                        </div>

                        <!-- Lokasi -->
                        <div>
                            <InputLabel for="location" value="Lokasi" />
                            <TextInput
                                id="location"
                                v-model="form.location"
                                type="text"
                                class="mt-1 block w-full"
                            />
                            <InputError class="mt-2" :message="form.errors.location" />
                        </div>

                        <!-- Kapasitas & Status (Grid) -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <InputLabel for="capacity" value="Kapasitas (Orang) *" />
                                <TextInput
                                    id="capacity"
                                    v-model.number="form.capacity"
                                    type="number"
                                    min="1"
                                    class="mt-1 block w-full"
                                    required
                                />
                                <InputError class="mt-2" :message="form.errors.capacity" />
                            </div>

                            <div>
                                <InputLabel for="status" value="Status *" />
                                <select
                                    id="status"
                                    v-model="form.status"
                                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    required
                                >
                                    <option value="active">Aktif</option>
                                    <option value="maintenance">Pemeliharaan</option>
                                    <option value="inactive">Nonaktif</option>
                                </select>
                                <InputError class="mt-2" :message="form.errors.status" />
                            </div>
                        </div>

                        <!-- Deskripsi -->
                        <div>
                            <InputLabel for="description" value="Deskripsi Ruangan" />
                            <textarea
                                id="description"
                                v-model="form.description"
                                rows="3"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                            />
                            <InputError class="mt-2" :message="form.errors.description" />
                        </div>

                        <!-- Fasilitas -->
                        <div>
                            <InputLabel value="Fasilitas Ruangan" />
                            <div v-if="facilities.length === 0" class="text-sm text-gray-500 mt-1">
                                Belum ada data fasilitas.
                            </div>
                            <div v-else class="mt-2 grid grid-cols-2 sm:grid-cols-3 gap-3">
                                <label
                                    v-for="facility in facilities"
                                    :key="facility.id"
                                    class="flex items-center space-x-2 p-2 rounded border border-gray-200 hover:bg-gray-50 cursor-pointer"
                                >
                                    <input
                                        type="checkbox"
                                        :value="facility.id"
                                        v-model="form.facilities"
                                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                    />
                                    <span class="text-sm text-gray-700">{{ facility.name }}</span>
                                </label>
                            </div>
                            <InputError class="mt-2" :message="form.errors.facilities" />
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center justify-end space-x-4 pt-4 border-t">
                            <Link :href="route('rooms.index')">
                                <SecondaryButton type="button">Batal</SecondaryButton>
                            </Link>
                            <PrimaryButton :disabled="form.processing">
                                <span v-if="form.processing">Menyimpan...</span>
                                <span v-else>Perbarui Ruangan</span>
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

