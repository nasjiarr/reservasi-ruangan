<script setup>
import { computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import Card from '@/Components/Card.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import {
    BuildingOffice2Icon,
    MapPinIcon,
    UserGroupIcon,
    PencilSquareIcon,
    ArrowLeftIcon,
    CheckBadgeIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
    room: {
        type: Object,
        required: true,
    },
});

const page = usePage();
const isAdmin = computed(() => page.props.auth.user?.roles?.includes('admin'));
</script>

<template>
    <Head :title="`Detail Ruangan - ${room.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <PageHeader
                :title="room.name"
                :description="`Detail informasi fasilitas dan kapasitas ruangan di ${room.location || 'Gedung Kantor'}`"
            >
                <template #actions>
                    <div class="flex items-center gap-3">
                        <Link :href="route('rooms.index')">
                            <SecondaryButton class="flex items-center gap-2">
                                <ArrowLeftIcon class="w-4 h-4" />
                                <span>Kembali</span>
                            </SecondaryButton>
                        </Link>
                        <Link v-if="isAdmin" :href="route('rooms.edit', room.id)">
                            <PrimaryButton class="flex items-center gap-2">
                                <PencilSquareIcon class="w-4 h-4" />
                                <span>Edit Ruangan</span>
                            </PrimaryButton>
                        </Link>
                    </div>
                </template>
            </PageHeader>
        </template>

        <div class="space-y-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Room Info -->
                <div class="lg:col-span-2 space-y-6">
                    <Card class="p-6">
                        <div class="flex items-start justify-between">
                            <div class="flex items-center gap-4">
                                <div class="w-14 h-14 rounded-2xl bg-brand-50 text-brand-700 flex items-center justify-center">
                                    <BuildingOffice2Icon class="w-8 h-8" />
                                </div>
                                <div>
                                    <h3 class="text-xl font-heading font-bold text-slate-900">{{ room.name }}</h3>
                                    <div class="flex items-center gap-2 mt-1 text-sm text-slate-500">
                                        <MapPinIcon class="w-4 h-4 text-slate-400" />
                                        <span>{{ room.location || 'Lokasi belum diatur' }}</span>
                                    </div>
                                </div>
                            </div>
                            <StatusBadge :status="room.status" />
                        </div>

                        <div class="mt-6 pt-6 border-t border-slate-100">
                            <h4 class="text-sm font-bold text-slate-700 uppercase tracking-wider mb-2">Deskripsi</h4>
                            <p class="text-sm text-slate-600 leading-relaxed">
                                {{ room.description || 'Tidak ada deskripsi tambahan untuk ruangan ini.' }}
                            </p>
                        </div>
                    </Card>

                    <!-- Facilities Card -->
                    <Card class="p-6">
                        <h4 class="text-base font-bold text-slate-900 mb-4">Fasilitas Tersedia</h4>
                        <div v-if="room.facilities && room.facilities.length > 0" class="flex flex-wrap gap-2">
                            <span
                                v-for="facility in room.facilities"
                                :key="facility.id"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-brand-50/80 text-brand-800 border border-brand-200/50"
                            >
                                <CheckBadgeIcon class="w-4 h-4 text-brand-600" />
                                {{ facility.name }}
                            </span>
                        </div>
                        <p v-else class="text-sm text-slate-400 italic">
                            Belum ada fasilitas terdaftar pada ruangan ini.
                        </p>
                    </Card>
                </div>

                <!-- Sidebar Metadata -->
                <div class="space-y-6">
                    <Card class="p-6">
                        <h4 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4">Kapasitas & Info</h4>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-100">
                                <div class="flex items-center gap-2.5">
                                    <UserGroupIcon class="w-5 h-5 text-slate-500" />
                                    <span class="text-xs font-medium text-slate-600">Kapasitas Maksimal</span>
                                </div>
                                <span class="font-bold text-sm text-slate-900">{{ room.capacity }} Orang</span>
                            </div>
                            <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-100">
                                <span class="text-xs font-medium text-slate-600">Status Operasional</span>
                                <span class="capitalize text-xs font-bold text-brand-700">{{ room.status }}</span>
                            </div>
                        </div>
                    </Card>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

