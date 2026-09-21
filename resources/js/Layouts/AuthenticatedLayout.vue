<script setup>
import { ref } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import NotificationDropdown from '@/Components/NotificationDropdown.vue';
import { Link } from '@inertiajs/vue3';
import {
    Squares2X2Icon,
    BuildingOffice2Icon,
    CalendarDaysIcon,
    ClipboardDocumentCheckIcon,
    ShieldCheckIcon,
    ChartBarSquareIcon,
    PresentationChartLineIcon,
    Bars3Icon,
    XMarkIcon,
    ChevronDownIcon,
    CheckCircleIcon,
    ExclamationCircleIcon,
    UserCircleIcon,
    ArrowRightOnRectangleIcon,
} from '@heroicons/vue/24/outline';

const showingNavigationDropdown = ref(false);
</script>

<template>
    <div class="min-h-screen bg-slate-50 font-sans text-slate-900 antialiased selection:bg-brand-500 selection:text-white">
        <!-- Top Navigation -->
        <nav class="sticky top-0 z-40 bg-white/95 backdrop-blur border-b border-slate-200/80 shadow-xs">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <!-- Logo -->
                        <div class="shrink-0 flex items-center">
                            <Link :href="route('dashboard')" class="focus:outline-none focus:ring-2 focus:ring-brand-500 rounded-lg">
                                <ApplicationLogo class="block h-9 w-auto" />
                            </Link>
                        </div>

                        <!-- Desktop Navigation Links -->
                        <div class="hidden sm:flex items-center space-x-1 sm:ms-8">
                            <NavLink :href="route('dashboard')" :active="route().current('dashboard')">
                                <Squares2X2Icon class="w-4 h-4 shrink-0" />
                                <span>Dashboard</span>
                            </NavLink>
                            <NavLink :href="route('rooms.index')" :active="route().current('rooms.*')">
                                <BuildingOffice2Icon class="w-4 h-4 shrink-0" />
                                <span>Ruangan</span>
                            </NavLink>
                            <NavLink :href="route('reservations.index')" :active="route().current('reservations.index') || route().current('reservations.create') || route().current('reservations.show')">
                                <ClipboardDocumentCheckIcon class="w-4 h-4 shrink-0" />
                                <span>Reservasi</span>
                            </NavLink>
                            <NavLink :href="route('reservations.calendar')" :active="route().current('reservations.calendar')">
                                <CalendarDaysIcon class="w-4 h-4 shrink-0" />
                                <span>Kalender</span>
                            </NavLink>
                            <NavLink
                                v-if="$page.props.auth.user?.roles?.some(role => ['admin', 'manager'].includes(role))"
                                :href="route('approvals.index')"
                                :active="route().current('approvals.*')"
                            >
                                <ShieldCheckIcon class="w-4 h-4 shrink-0" />
                                <span>Persetujuan</span>
                            </NavLink>
                            <NavLink
                                v-if="$page.props.auth.user?.roles?.some(role => ['admin', 'manager'].includes(role))"
                                :href="route('reports.index')"
                                :active="route().current('reports.*')"
                            >
                                <ChartBarSquareIcon class="w-4 h-4 shrink-0" />
                                <span>Laporan</span>
                            </NavLink>
                            <NavLink
                                v-if="$page.props.auth.user?.roles?.some(role => ['admin', 'manager'].includes(role))"
                                :href="route('analytics.index')"
                                :active="route().current('analytics.*')"
                            >
                                <PresentationChartLineIcon class="w-4 h-4 shrink-0" />
                                <span>Analytics</span>
                            </NavLink>
                        </div>
                    </div>

                    <!-- Right Controls: Notification + Profile -->
                    <div class="hidden sm:flex sm:items-center sm:ms-6 space-x-3">
                        <!-- Notification Dropdown -->
                        <NotificationDropdown />

                        <!-- Settings Dropdown -->
                        <div class="relative">
                            <Dropdown align="right" width="48">
                                <template #trigger>
                                    <button
                                        type="button"
                                        class="inline-flex items-center gap-2.5 px-3 py-1.5 border border-slate-200 rounded-lg text-sm font-medium text-slate-700 bg-white hover:bg-slate-50 hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition ease-in-out duration-150 shadow-xs"
                                    >
                                        <div class="w-7 h-7 rounded-full bg-brand-100 text-brand-700 flex items-center justify-center font-bold text-xs ring-2 ring-brand-50">
                                            {{ $page.props.auth.user.name?.charAt(0).toUpperCase() }}
                                        </div>
                                        <span class="max-w-[120px] truncate text-left font-semibold text-slate-800">{{ $page.props.auth.user.name }}</span>
                                        <ChevronDownIcon class="h-4 w-4 text-slate-400" />
                                    </button>
                                </template>

                                <template #content>
                                    <div class="px-4 py-2 border-b border-slate-100">
                                        <p class="text-xs text-slate-400 font-medium">Masuk sebagai</p>
                                        <p class="text-sm font-semibold text-slate-800 truncate">{{ $page.props.auth.user.email }}</p>
                                    </div>
                                    <DropdownLink :href="route('profile.edit')" class="flex items-center gap-2">
                                        <UserCircleIcon class="w-4 h-4 text-slate-400" />
                                        <span>Profil Saya</span>
                                    </DropdownLink>
                                    <DropdownLink :href="route('logout')" method="post" as="button" class="flex items-center gap-2 text-rose-600 hover:text-rose-700 hover:bg-rose-50">
                                        <ArrowRightOnRectangleIcon class="w-4 h-4 text-rose-500" />
                                        <span>Keluar (Log Out)</span>
                                    </DropdownLink>
                                </template>
                            </Dropdown>
                        </div>
                    </div>

                    <!-- Mobile Hamburger & Mobile Notification -->
                    <div class="-me-2 flex items-center sm:hidden space-x-1">
                        <NotificationDropdown />

                        <button
                            @click="showingNavigationDropdown = !showingNavigationDropdown"
                            class="inline-flex items-center justify-center p-2 rounded-lg text-slate-500 hover:text-slate-700 hover:bg-slate-100 focus:outline-none focus:bg-slate-100 transition duration-150 ease-in-out"
                        >
                            <Bars3Icon v-if="!showingNavigationDropdown" class="h-6 w-6" />
                            <XMarkIcon v-else class="h-6 w-6" />
                        </button>
                    </div>
                </div>
            </div>

            <!-- Responsive Navigation Menu -->
            <div
                v-show="showingNavigationDropdown"
                class="sm:hidden border-b border-slate-200 bg-white shadow-lg transition-all duration-200"
            >
                <div class="pt-2 pb-3 space-y-1 px-3">
                    <ResponsiveNavLink :href="route('dashboard')" :active="route().current('dashboard')">
                        <div class="flex items-center gap-3">
                            <Squares2X2Icon class="w-5 h-5" />
                            <span>Dashboard</span>
                        </div>
                    </ResponsiveNavLink>
                    <ResponsiveNavLink :href="route('rooms.index')" :active="route().current('rooms.*')">
                        <div class="flex items-center gap-3">
                            <BuildingOffice2Icon class="w-5 h-5" />
                            <span>Ruangan</span>
                        </div>
                    </ResponsiveNavLink>
                    <ResponsiveNavLink :href="route('reservations.index')" :active="route().current('reservations.index') || route().current('reservations.create') || route().current('reservations.show')">
                        <div class="flex items-center gap-3">
                            <ClipboardDocumentCheckIcon class="w-5 h-5" />
                            <span>Reservasi</span>
                        </div>
                    </ResponsiveNavLink>
                    <ResponsiveNavLink :href="route('reservations.calendar')" :active="route().current('reservations.calendar')">
                        <div class="flex items-center gap-3">
                            <CalendarDaysIcon class="w-5 h-5" />
                            <span>Kalender</span>
                        </div>
                    </ResponsiveNavLink>
                    <ResponsiveNavLink
                        v-if="$page.props.auth.user?.roles?.some(role => ['admin', 'manager'].includes(role))"
                        :href="route('approvals.index')"
                        :active="route().current('approvals.*')"
                    >
                        <div class="flex items-center gap-3">
                            <ShieldCheckIcon class="w-5 h-5" />
                            <span>Persetujuan</span>
                        </div>
                    </ResponsiveNavLink>
                    <ResponsiveNavLink
                        v-if="$page.props.auth.user?.roles?.some(role => ['admin', 'manager'].includes(role))"
                        :href="route('reports.index')"
                        :active="route().current('reports.*')"
                    >
                        <div class="flex items-center gap-3">
                            <ChartBarSquareIcon class="w-5 h-5" />
                            <span>Laporan</span>
                        </div>
                    </ResponsiveNavLink>
                    <ResponsiveNavLink
                        v-if="$page.props.auth.user?.roles?.some(role => ['admin', 'manager'].includes(role))"
                        :href="route('analytics.index')"
                        :active="route().current('analytics.*')"
                    >
                        <div class="flex items-center gap-3">
                            <PresentationChartLineIcon class="w-5 h-5" />
                            <span>Analytics</span>
                        </div>
                    </ResponsiveNavLink>
                </div>

                <!-- Responsive Settings Options -->
                <div class="pt-4 pb-3 border-t border-slate-200 bg-slate-50/50 px-4">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full bg-brand-600 text-white font-bold flex items-center justify-center text-sm shadow-xs">
                            {{ $page.props.auth.user.name?.charAt(0).toUpperCase() }}
                        </div>
                        <div>
                            <div class="font-semibold text-sm text-slate-800">
                                {{ $page.props.auth.user.name }}
                            </div>
                            <div class="text-xs text-slate-500">{{ $page.props.auth.user.email }}</div>
                        </div>
                    </div>

                    <div class="mt-3 space-y-1">
                        <ResponsiveNavLink :href="route('profile.edit')">
                            Profil Saya
                        </ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('logout')" method="post" as="button" class="text-rose-600 hover:text-rose-700">
                            Log Out
                        </ResponsiveNavLink>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Optional Page Header Slot -->
        <header class="bg-white border-b border-slate-200/80" v-if="$slots.header">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                <slot name="header" />
            </div>
        </header>

        <!-- Flash Messages -->
        <div v-if="$page.props.flash?.success" class="max-w-7xl mx-auto mt-6 px-4 sm:px-6 lg:px-8">
            <div class="p-4 bg-emerald-50/90 border border-emerald-200 text-emerald-900 rounded-xl text-sm flex items-center gap-3 shadow-xs">
                <CheckCircleIcon class="w-5 h-5 text-emerald-600 shrink-0" />
                <span class="font-medium">{{ $page.props.flash.success }}</span>
            </div>
        </div>
        <div v-if="$page.props.flash?.error" class="max-w-7xl mx-auto mt-6 px-4 sm:px-6 lg:px-8">
            <div class="p-4 bg-rose-50/90 border border-rose-200 text-rose-900 rounded-xl text-sm flex items-center gap-3 shadow-xs">
                <ExclamationCircleIcon class="w-5 h-5 text-rose-600 shrink-0" />
                <span class="font-medium">{{ $page.props.flash.error }}</span>
            </div>
        </div>

        <!-- Page Main Content -->
        <main class="py-8">
            <slot />
        </main>
    </div>
</template>
