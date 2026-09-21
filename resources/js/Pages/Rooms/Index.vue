<script setup>
import { computed } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';

const props = defineProps({
    rooms: {
        type: [Array, Object],
        default: () => [],
    },
});

const page = usePage();
const isAdmin = computed(() => page.props.auth.user?.roles?.includes('admin'));

const roomList = computed(() => {
    if (Array.isArray(props.rooms)) {
        return props.rooms;
    }
    return props.rooms?.data || [];
});

const paginationLinks = computed(() => {
    return props.rooms?.links || [];
});

const deleteRoom = (room) => {
    if (confirm(`Apakah Anda yakin ingin menghapus ruangan "${room.name}"?`)) {
        router.delete(route('rooms.destroy', room.id));
    }
};

const getStatusBadgeClass = (status) => {
    switch (status) {
        case 'active':
            return 'bg-emerald-100 text-emerald-800 border-emerald-300';
        case 'maintenance':
            return 'bg-amber-100 text-amber-800 border-amber-300';
        case 'inactive':
            return 'bg-red-100 text-red-800 border-red-300';
        default:
            return 'bg-gray-100 text-gray-800 border-gray-300';
    }
};

const formatStatus = (status) => {
    switch (status) {
        case 'active':
            return 'Aktif';
        case 'maintenance':
            return 'Pemeliharaan';
        case 'inactive':
            return 'Nonaktif';
        default:
            return status;
    }
};
</script>

<template>
    <Head title="Daftar Ruangan" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Daftar Ruangan
                </h2>
                <Link v-if="isAdmin" :href="route('rooms.create')">
                    <PrimaryButton>+ Tambah Ruangan</PrimaryButton>
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div v-if="roomList.length === 0" class="text-center py-10 text-gray-500">
                            Belum ada ruangan yang terdaftar.
                        </div>

                        <div v-else class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Nama Ruangan
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Lokasi
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Kapasitas
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Fasilitas
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Status
                                        </th>
                                        <th v-if="isAdmin" scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Aksi
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="room in roomList" :key="room.id" class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="font-semibold text-gray-900">{{ room.name }}</div>
                                            <div v-if="room.description" class="text-xs text-gray-500 truncate max-w-xs">
                                                {{ room.description }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                            {{ room.location || '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                            <span class="font-medium">{{ room.capacity }}</span> orang
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-600">
                                            <div class="flex flex-wrap gap-1">
                                                <span
                                                    v-for="facility in room.facilities"
                                                    :key="facility.id"
                                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-indigo-50 text-indigo-700 border border-indigo-200"
                                                >
                                                    {{ facility.name }}
                                                </span>
                                                <span v-if="!room.facilities || room.facilities.length === 0" class="text-xs text-gray-400 italic">
                                                    Tidak ada
                                                </span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span
                                                :class="getStatusBadgeClass(room.status)"
                                                class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full border"
                                            >
                                                {{ formatStatus(room.status) }}
                                            </span>
                                        </td>
                                        <td v-if="isAdmin" class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                            <Link
                                                :href="route('rooms.edit', room.id)"
                                                class="inline-flex items-center px-3 py-1 bg-amber-500 hover:bg-amber-600 text-white rounded text-xs font-semibold uppercase tracking-widest transition ease-in-out duration-150"
                                            >
                                                Edit
                                            </Link>
                                            <DangerButton
                                                class="!px-3 !py-1 !text-xs"
                                                @click="deleteRoom(room)"
                                            >
                                                Hapus
                                            </DangerButton>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
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
    </AuthenticatedLayout>
</template>

