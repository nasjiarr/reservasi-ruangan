<script setup>
import { computed } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import PageHeader from '@/Components/PageHeader.vue';
import Card from '@/Components/Card.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import EmptyState from '@/Components/EmptyState.vue';
import {
    BuildingOffice2Icon,
    MapPinIcon,
    UserGroupIcon,
    PlusIcon,
    PencilSquareIcon,
    TrashIcon,
    SparklesIcon,
} from '@heroicons/vue/24/outline';

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
</script>

<template>
    <Head title="Daftar Ruangan" />

    <AuthenticatedLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <!-- Header Section -->
            <PageHeader
                title="Daftar Ruangan"
                description="Kelola dan telusuri ruangan meeting serta fasilitas yang tersedia untuk kegiatan Anda."
                :badge="`${roomList.length} Ruangan`"
            >
                <template #actions>
                    <Link v-if="isAdmin" :href="route('rooms.create')">
                        <PrimaryButton class="gap-2 shadow-sm">
                            <PlusIcon class="w-4 h-4" />
                            <span>Tambah Ruangan</span>
                        </PrimaryButton>
                    </Link>
                </template>
            </PageHeader>

            <!-- Empty State -->
            <EmptyState
                v-if="roomList.length === 0"
                title="Belum Ada Ruangan"
                description="Belum ada data ruangan yang terdaftar di dalam sistem."
            >
                <template #icon>
                    <BuildingOffice2Icon class="w-7 h-7 text-slate-400" />
                </template>
                <template #action v-if="isAdmin">
                    <Link :href="route('rooms.create')">
                        <PrimaryButton class="gap-2">
                            <PlusIcon class="w-4 h-4" />
                            <span>Tambah Ruangan Sekarang</span>
                        </PrimaryButton>
                    </Link>
                </template>
            </EmptyState>

            <!-- Room Card Grid -->
            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div
                    v-for="room in roomList"
                    :key="room.id"
                    class="group bg-white rounded-2xl border border-slate-200/80 shadow-card hover:shadow-card-hover hover:border-slate-300 transition-all duration-200 flex flex-col justify-between overflow-hidden"
                >
                    <!-- Card Top / Decorative Banner -->
                    <div>
                        <div class="relative bg-gradient-to-br from-slate-900 via-slate-800 to-teal-950 p-5 text-white">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-11 h-11 rounded-xl bg-white/10 backdrop-blur border border-white/15 flex items-center justify-center text-teal-300 shadow-inner shrink-0">
                                        <BuildingOffice2Icon class="w-6 h-6" />
                                    </div>
                                    <div>
                                        <h3 class="font-heading font-bold text-lg text-white group-hover:text-teal-200 transition-colors line-clamp-1">
                                            {{ room.name }}
                                        </h3>
                                        <div class="flex items-center gap-1.5 text-xs text-slate-300 mt-0.5">
                                            <MapPinIcon class="w-3.5 h-3.5 text-teal-400 shrink-0" />
                                            <span class="truncate">{{ room.location || 'Lokasi belum diatur' }}</span>
                                        </div>
                                    </div>
                                </div>
                                <StatusBadge :status="room.status" size="sm" class="shrink-0" />
                            </div>
                        </div>

                        <!-- Card Content -->
                        <div class="p-5 space-y-4">
                            <!-- Capacity Badge -->
                            <div class="flex items-center justify-between py-2 px-3 bg-slate-50/80 rounded-xl border border-slate-100 text-sm">
                                <div class="flex items-center gap-2 text-slate-600">
                                    <UserGroupIcon class="w-4 h-4 text-brand-600" />
                                    <span class="text-xs font-medium text-slate-500">Kapasitas Maksimal</span>
                                </div>
                                <span class="font-heading font-bold text-slate-900 text-sm">
                                    {{ room.capacity }} <span class="font-normal text-xs text-slate-500">orang</span>
                                </span>
                            </div>

                            <!-- Description -->
                            <p class="text-xs text-slate-500 line-clamp-2 leading-relaxed min-h-[2rem]">
                                {{ room.description || 'Tidak ada deskripsi tambahan untuk ruangan ini.' }}
                            </p>

                            <!-- Facility Chips -->
                            <div class="space-y-1.5 pt-1">
                                <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider block">
                                    Fasilitas Ruangan
                                </span>
                                <div class="flex flex-wrap gap-1.5 min-h-[1.75rem]">
                                    <span
                                        v-for="facility in room.facilities"
                                        :key="facility.id"
                                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs font-medium bg-teal-50/80 text-teal-800 border border-teal-200/60"
                                    >
                                        <SparklesIcon class="w-3 h-3 text-teal-600" />
                                        <span>{{ facility.name }}</span>
                                    </span>
                                    <span
                                        v-if="!room.facilities || room.facilities.length === 0"
                                        class="text-xs text-slate-400 italic py-0.5"
                                    >
                                        Fasilitas belum ditambahkan
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card Actions Footer -->
                    <div class="p-4 bg-slate-50/70 border-t border-slate-100 flex items-center justify-between gap-2">
                        <!-- Quick Book Button (for all users when active) -->
                        <Link
                            v-if="room.status === 'active'"
                            :href="route('reservations.create', { room_id: room.id })"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-brand-700 bg-brand-50 hover:bg-brand-100 border border-brand-200 rounded-lg transition"
                        >
                            <span>Booking</span>
                            <span>&rarr;</span>
                        </Link>
                        <span v-else class="text-xs text-slate-400 italic px-1">
                            Tidak tersedia
                        </span>

                        <!-- Admin Actions -->
                        <div v-if="isAdmin" class="flex items-center gap-1.5">
                            <Link
                                :href="route('rooms.edit', room.id)"
                                class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-white hover:bg-slate-100 text-slate-700 border border-slate-200 rounded-lg text-xs font-semibold transition shadow-xs"
                                title="Edit Ruangan"
                            >
                                <PencilSquareIcon class="w-3.5 h-3.5 text-slate-500" />
                                <span>Edit</span>
                            </Link>
                            <button
                                type="button"
                                @click="deleteRoom(room)"
                                class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-white hover:bg-rose-50 text-rose-600 hover:text-rose-700 border border-slate-200 hover:border-rose-200 rounded-lg text-xs font-semibold transition shadow-xs"
                                title="Hapus Ruangan"
                            >
                                <TrashIcon class="w-3.5 h-3.5" />
                                <span>Hapus</span>
                            </button>
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
    </AuthenticatedLayout>
</template>
