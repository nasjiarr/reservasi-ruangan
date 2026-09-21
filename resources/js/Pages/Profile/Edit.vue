<script setup>
import { computed } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import {
    UserCircleIcon,
    EnvelopeIcon,
    ShieldCheckIcon,
    CheckBadgeIcon,
    SparklesIcon,
} from '@heroicons/vue/24/outline';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
        default: false,
    },
    status: {
        type: String,
        default: null,
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
</script>

<template>
    <Head title="Pengaturan Profil" />

    <AuthenticatedLayout>
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            <!-- 1. Header Section -->
            <PageHeader
                title="Pengaturan Profil & Akun"
                description="Kelola informasi identitas pribadi, kredensial keamanan kata sandi, dan preferensi akun RoomSpace Anda."
                :badge="roleDisplay"
            />

            <!-- 2. Identity Hero Card -->
            <div class="bg-gradient-to-br from-slate-900 via-slate-800 to-teal-950 rounded-2xl p-6 sm:p-7 text-white shadow-card relative overflow-hidden">
                <!-- Decorative Glow Spheres -->
                <div class="absolute -right-16 -top-16 h-48 w-48 rounded-full bg-brand-500/20 blur-3xl pointer-events-none"></div>
                <div class="absolute right-28 -bottom-16 h-40 w-40 rounded-full bg-emerald-500/10 blur-2xl pointer-events-none"></div>

                <div class="relative z-10 flex flex-col sm:flex-row sm:items-center justify-between gap-6">
                    <div class="flex items-center gap-5">
                        <!-- Large User Avatar -->
                        <div class="w-16 h-16 sm:w-18 sm:h-18 rounded-2xl bg-gradient-to-tr from-brand-600 to-brand-400 text-white font-heading font-extrabold text-2xl sm:text-3xl flex items-center justify-center shadow-md ring-4 ring-white/10 shrink-0">
                            {{ currentUser?.name?.charAt(0).toUpperCase() }}
                        </div>

                        <!-- User Info -->
                        <div class="space-y-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <h2 class="font-heading font-extrabold text-xl sm:text-2xl text-white tracking-tight">
                                    {{ currentUser?.name }}
                                </h2>
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-white/10 text-teal-200 border border-white/15">
                                    <SparklesIcon class="w-3.5 h-3.5 text-teal-300" />
                                    <span>{{ roleDisplay }}</span>
                                </span>
                            </div>

                            <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-slate-300 pt-0.5">
                                <div class="flex items-center gap-1.5 text-slate-300">
                                    <EnvelopeIcon class="w-4 h-4 text-slate-400" />
                                    <span>{{ currentUser?.email }}</span>
                                </div>
                                <div class="flex items-center gap-1.5 text-emerald-300">
                                    <CheckBadgeIcon class="w-4 h-4 text-emerald-400" />
                                    <span>Akun Terverifikasi</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Role Badge Pill -->
                    <div class="hidden md:flex flex-col items-end text-right">
                        <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Level Hak Akses</span>
                        <span class="font-heading font-bold text-sm text-teal-300 mt-0.5">{{ roleDisplay }}</span>
                    </div>
                </div>
            </div>

            <!-- 3. Form Cards Stack -->
            <div class="space-y-8">
                <!-- Personal Info Form -->
                <UpdateProfileInformationForm
                    :must-verify-email="mustVerifyEmail"
                    :status="status"
                />

                <!-- Password & Security Form -->
                <UpdatePasswordForm />

                <!-- Danger Zone / Delete Account -->
                <DeleteUserForm />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
