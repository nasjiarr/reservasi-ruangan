<script setup>
import { ref, computed, onMounted } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Breadcrumb from '@/Components/Breadcrumb.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NotificationDropdown from '@/Components/NotificationDropdown.vue';
import SidebarLink from '@/Components/SidebarLink.vue';
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
    ChevronDoubleLeftIcon,
    ChevronDoubleRightIcon,
    CheckCircleIcon,
    ExclamationCircleIcon,
    UserCircleIcon,
    ArrowRightOnRectangleIcon,
    ChevronRightIcon,
} from '@heroicons/vue/24/outline';

const page = usePage();
const currentUser = computed(() => page.props.auth.user);
const userRoles = computed(() => currentUser.value?.roles || []);

const canApprove = computed(() =>
    userRoles.value.some((role) => ['admin', 'manager'].includes(role))
);

const canViewReports = computed(() =>
    userRoles.value.some((role) => ['admin', 'manager'].includes(role))
);

const roleDisplay = computed(() => {
    if (userRoles.value.includes('admin')) return 'Admin';
    if (userRoles.value.includes('manager')) return 'Manager';
    return 'Staff';
});

// Sidebar State: Collapsed (Desktop) & Open (Mobile)
const isCollapsed = ref(false);
const isMobileOpen = ref(false);

onMounted(() => {
    if (typeof window !== 'undefined') {
        const saved = localStorage.getItem('sidebar_collapsed');
        if (saved !== null) {
            isCollapsed.value = saved === 'true';
        }
    }
});

const toggleCollapse = () => {
    isCollapsed.value = !isCollapsed.value;
    if (typeof window !== 'undefined') {
        localStorage.setItem('sidebar_collapsed', String(isCollapsed.value));
    }
};

const closeMobileSidebar = () => {
    isMobileOpen.value = false;
};
</script>

<template>
    <div class="min-h-screen bg-slate-50 font-sans text-slate-900 antialiased selection:bg-brand-500 selection:text-white">
        <!-- =================================================================== -->
        <!-- 1. DESKTOP SIDEBAR (Fixed Left, Collapsible w-64 <-> w-20)          -->
        <!-- =================================================================== -->
        <aside
            class="hidden md:flex flex-col fixed top-0 left-0 bottom-0 z-30 bg-white border-r border-slate-200/80 transition-all duration-300 ease-in-out shadow-xs"
            :class="isCollapsed ? 'w-20' : 'w-64'"
        >
            <!-- Sidebar Header: Logo & Collapse Toggle -->
            <div class="h-16 flex items-center px-4 border-b border-slate-100 shrink-0" :class="isCollapsed ? 'justify-center' : 'justify-between'">
                <Link
                    :href="route('dashboard')"
                    class="flex items-center focus:outline-none focus:ring-2 focus:ring-brand-500 rounded-lg overflow-hidden"
                    :title="isCollapsed ? 'RoomSpace' : undefined"
                >
                    <ApplicationLogo :compact="isCollapsed" />
                </Link>

                <!-- Collapse Toggle Button (Desktop only) -->
                <button
                    v-if="!isCollapsed"
                    type="button"
                    @click="toggleCollapse"
                    class="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors"
                    title="Ciutkan Sidebar"
                >
                    <ChevronDoubleLeftIcon class="w-4 h-4" />
                </button>
            </div>

            <!-- If collapsed: Small expand toggle button right below header -->
            <div v-if="isCollapsed" class="py-2 flex justify-center border-b border-slate-100">
                <button
                    type="button"
                    @click="toggleCollapse"
                    class="p-1.5 rounded-lg text-slate-400 hover:text-brand-600 hover:bg-brand-50 transition-colors"
                    title="Bentangkan Sidebar"
                >
                    <ChevronDoubleRightIcon class="w-4 h-4" />
                </button>
            </div>

            <!-- Navigation Links Scroll Area -->
            <nav class="flex-1 overflow-y-auto overflow-x-hidden p-3 space-y-6 scrollbar-thin">
                <!-- Group 1: MENU UTAMA -->
                <div class="space-y-1">
                    <div
                        v-if="!isCollapsed"
                        class="text-[10px] font-bold text-slate-400 tracking-wider uppercase px-3 mb-2"
                    >
                        Menu Utama
                    </div>
                    <div v-else class="border-t border-slate-100 my-2"></div>

                    <SidebarLink
                        :href="route('dashboard')"
                        :icon="Squares2X2Icon"
                        label="Dashboard"
                        :collapsed="isCollapsed"
                    />
                    <SidebarLink
                        :href="route('reservations.index')"
                        :icon="ClipboardDocumentCheckIcon"
                        label="Reservasi"
                        :active="route().current('reservations.index') || route().current('reservations.create') || route().current('reservations.show')"
                        :collapsed="isCollapsed"
                    />
                    <SidebarLink
                        :href="route('reservations.calendar')"
                        :icon="CalendarDaysIcon"
                        label="Kalender"
                        :collapsed="isCollapsed"
                    />
                </div>

                <!-- Group 2: MANAJEMEN -->
                <div class="space-y-1">
                    <div
                        v-if="!isCollapsed"
                        class="text-[10px] font-bold text-slate-400 tracking-wider uppercase px-3 mb-2"
                    >
                        Manajemen
                    </div>
                    <div v-else class="border-t border-slate-100 my-2"></div>

                    <SidebarLink
                        :href="route('rooms.index')"
                        :icon="BuildingOffice2Icon"
                        label="Ruangan"
                        :active="route().current('rooms.*')"
                        :collapsed="isCollapsed"
                    />
                    <SidebarLink
                        v-if="canApprove"
                        :href="route('approvals.index')"
                        :icon="ShieldCheckIcon"
                        label="Persetujuan"
                        :active="route().current('approvals.*')"
                        :collapsed="isCollapsed"
                    />
                </div>

                <!-- Group 3: ANALITIK & LAPORAN -->
                <div v-if="canViewReports" class="space-y-1">
                    <div
                        v-if="!isCollapsed"
                        class="text-[10px] font-bold text-slate-400 tracking-wider uppercase px-3 mb-2"
                    >
                        Analitik & Laporan
                    </div>
                    <div v-else class="border-t border-slate-100 my-2"></div>

                    <SidebarLink
                        :href="route('analytics.index')"
                        :icon="PresentationChartLineIcon"
                        label="Analytics"
                        :active="route().current('analytics.*')"
                        :collapsed="isCollapsed"
                    />
                    <SidebarLink
                        :href="route('reports.index')"
                        :icon="ChartBarSquareIcon"
                        label="Laporan"
                        :active="route().current('reports.*')"
                        :collapsed="isCollapsed"
                    />
                </div>
            </nav>

            <!-- Sidebar Footer: Compact Profile & Quick Actions -->
            <div class="p-3 border-t border-slate-200/80 bg-slate-50/50 shrink-0">
                <!-- Expanded Footer -->
                <div v-if="!isCollapsed" class="flex items-center justify-between gap-2">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <div class="w-8 h-8 rounded-xl bg-brand-100 text-brand-700 flex items-center justify-center font-bold text-xs ring-2 ring-brand-50 shrink-0">
                            {{ currentUser?.name?.charAt(0).toUpperCase() }}
                        </div>
                        <div class="min-w-0 leading-tight">
                            <div class="font-bold text-xs text-slate-800 truncate">
                                {{ currentUser?.name }}
                            </div>
                            <div class="inline-flex items-center gap-1 text-[10px] font-semibold text-brand-700 bg-brand-50 px-1.5 py-0.2 rounded-md mt-0.5">
                                {{ roleDisplay }}
                            </div>
                        </div>
                    </div>

                    <!-- Quick Logout & Profile buttons -->
                    <div class="flex items-center gap-1 shrink-0">
                        <Link
                            :href="route('profile.edit')"
                            class="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition"
                            title="Profil Saya"
                        >
                            <UserCircleIcon class="w-4 h-4" />
                        </Link>
                        <Link
                            :href="route('logout')"
                            method="post"
                            as="button"
                            class="p-1.5 rounded-lg text-rose-500 hover:text-rose-700 hover:bg-rose-50 transition"
                            title="Keluar (Log Out)"
                        >
                            <ArrowRightOnRectangleIcon class="w-4 h-4" />
                        </Link>
                    </div>
                </div>

                <!-- Collapsed Footer: Avatar with Quick Dropdown / Tooltip -->
                <div v-else class="flex flex-col items-center gap-2">
                    <Link
                        :href="route('profile.edit')"
                        class="w-8 h-8 rounded-xl bg-brand-100 text-brand-700 flex items-center justify-center font-bold text-xs ring-2 ring-brand-50 shrink-0 hover:ring-brand-200 transition"
                        :title="currentUser?.name + ' (' + roleDisplay + ')'"
                    >
                        {{ currentUser?.name?.charAt(0).toUpperCase() }}
                    </Link>
                    <Link
                        :href="route('logout')"
                        method="post"
                        as="button"
                        class="p-1.5 rounded-lg text-rose-500 hover:text-rose-700 hover:bg-rose-50 transition"
                        title="Keluar (Log Out)"
                    >
                        <ArrowRightOnRectangleIcon class="w-4 h-4" />
                    </Link>
                </div>
            </div>
        </aside>

        <!-- =================================================================== -->
        <!-- 2. MOBILE OFF-CANVAS SIDEBAR DRAWER (< md)                          -->
        <!-- =================================================================== -->
        <!-- Backdrop -->
        <div
            v-if="isMobileOpen"
            @click="closeMobileSidebar"
            class="fixed inset-0 z-40 bg-slate-900/50 backdrop-blur-xs md:hidden transition-opacity"
        ></div>

        <!-- Mobile Drawer -->
        <aside
            class="fixed inset-y-0 left-0 z-50 w-72 bg-white flex flex-col border-r border-slate-200 shadow-2xl md:hidden transform transition-transform duration-300 ease-in-out"
            :class="isMobileOpen ? 'translate-x-0' : '-translate-x-full'"
        >
            <!-- Mobile Header -->
            <div class="h-16 flex items-center justify-between px-4 border-b border-slate-100 shrink-0">
                <Link :href="route('dashboard')" @click="closeMobileSidebar">
                    <ApplicationLogo />
                </Link>
                <button
                    type="button"
                    @click="closeMobileSidebar"
                    class="p-2 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100"
                >
                    <XMarkIcon class="w-5 h-5" />
                </button>
            </div>

            <!-- Mobile Navigation Links -->
            <nav class="flex-1 overflow-y-auto p-4 space-y-6">
                <!-- Group 1: MENU UTAMA -->
                <div class="space-y-1">
                    <div class="text-[10px] font-bold text-slate-400 tracking-wider uppercase px-3 mb-2">
                        Menu Utama
                    </div>
                    <SidebarLink
                        :href="route('dashboard')"
                        :icon="Squares2X2Icon"
                        label="Dashboard"
                        @click="closeMobileSidebar"
                    />
                    <SidebarLink
                        :href="route('reservations.index')"
                        :icon="ClipboardDocumentCheckIcon"
                        label="Reservasi"
                        :active="route().current('reservations.index') || route().current('reservations.create') || route().current('reservations.show')"
                        @click="closeMobileSidebar"
                    />
                    <SidebarLink
                        :href="route('reservations.calendar')"
                        :icon="CalendarDaysIcon"
                        label="Kalender"
                        @click="closeMobileSidebar"
                    />
                </div>

                <!-- Group 2: MANAJEMEN -->
                <div class="space-y-1">
                    <div class="text-[10px] font-bold text-slate-400 tracking-wider uppercase px-3 mb-2">
                        Manajemen
                    </div>
                    <SidebarLink
                        :href="route('rooms.index')"
                        :icon="BuildingOffice2Icon"
                        label="Ruangan"
                        :active="route().current('rooms.*')"
                        @click="closeMobileSidebar"
                    />
                    <SidebarLink
                        v-if="canApprove"
                        :href="route('approvals.index')"
                        :icon="ShieldCheckIcon"
                        label="Persetujuan"
                        :active="route().current('approvals.*')"
                        @click="closeMobileSidebar"
                    />
                </div>

                <!-- Group 3: ANALITIK & LAPORAN -->
                <div v-if="canViewReports" class="space-y-1">
                    <div class="text-[10px] font-bold text-slate-400 tracking-wider uppercase px-3 mb-2">
                        Analitik & Laporan
                    </div>
                    <SidebarLink
                        :href="route('analytics.index')"
                        :icon="PresentationChartLineIcon"
                        label="Analytics"
                        :active="route().current('analytics.*')"
                        @click="closeMobileSidebar"
                    />
                    <SidebarLink
                        :href="route('reports.index')"
                        :icon="ChartBarSquareIcon"
                        label="Laporan"
                        :active="route().current('reports.*')"
                        @click="closeMobileSidebar"
                    />
                </div>
            </nav>

            <!-- Mobile Footer: User Info & Logout -->
            <div class="p-4 border-t border-slate-200 bg-slate-50/70">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-brand-100 text-brand-700 flex items-center justify-center font-bold text-xs ring-2 ring-brand-50 shrink-0">
                        {{ currentUser?.name?.charAt(0).toUpperCase() }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="font-bold text-xs text-slate-800 truncate">
                            {{ currentUser?.name }}
                        </div>
                        <div class="text-[11px] text-slate-500 truncate">
                            {{ currentUser?.email }}
                        </div>
                    </div>
                </div>

                <div class="mt-3 pt-3 border-t border-slate-200/60 flex items-center justify-between">
                    <Link
                        :href="route('profile.edit')"
                        @click="closeMobileSidebar"
                        class="text-xs font-semibold text-slate-600 hover:text-slate-900 flex items-center gap-1.5"
                    >
                        <UserCircleIcon class="w-4 h-4 text-slate-400" />
                        <span>Profil</span>
                    </Link>
                    <Link
                        :href="route('logout')"
                        method="post"
                        as="button"
                        class="text-xs font-semibold text-rose-600 hover:text-rose-700 flex items-center gap-1.5"
                    >
                        <ArrowRightOnRectangleIcon class="w-4 h-4 text-rose-500" />
                        <span>Keluar</span>
                    </Link>
                </div>
            </div>
        </aside>

        <!-- =================================================================== -->
        <!-- 3. MAIN CONTENT CONTAINER (Adjusts left margin for sidebar)        -->
        <!-- =================================================================== -->
        <div
            class="transition-all duration-300 ease-in-out flex flex-col min-h-screen"
            :class="isCollapsed ? 'md:pl-20' : 'md:pl-64'"
        >
            <!-- TOPBAR HEADER (Sticky Top on Content Area) -->
            <header class="sticky top-0 z-20 h-16 bg-white/95 backdrop-blur border-b border-slate-200/80 px-4 sm:px-6 lg:px-8 flex items-center justify-between shadow-2xs">
                <!-- Left: Hamburger button (Mobile) + Breadcrumb Context -->
                <div class="flex items-center gap-3">
                    <!-- Hamburger button for mobile -->
                    <button
                        type="button"
                        @click="isMobileOpen = true"
                        class="p-2 rounded-lg text-slate-500 hover:text-slate-700 hover:bg-slate-100 md:hidden transition"
                        title="Buka Menu"
                    >
                        <Bars3Icon class="w-5 h-5" />
                    </button>

                    <!-- Breadcrumb Context -->
                    <Breadcrumb />
                </div>

                <!-- Right: Notification Dropdown & Profile Dropdown -->
                <div class="flex items-center gap-3">
                    <!-- Notification Bell -->
                    <NotificationDropdown />

                    <!-- User Profile Dropdown Menu -->
                    <Dropdown align="right" width="48">
                        <template #trigger>
                            <button
                                type="button"
                                class="inline-flex items-center gap-2.5 px-3 py-1.5 border border-slate-200 rounded-xl text-sm font-medium text-slate-700 bg-white hover:bg-slate-50 hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition shadow-2xs"
                            >
                                <div class="w-7 h-7 rounded-lg bg-brand-100 text-brand-700 flex items-center justify-center font-bold text-xs ring-2 ring-brand-50">
                                    {{ currentUser?.name?.charAt(0).toUpperCase() }}
                                </div>
                                <span class="hidden sm:inline-block max-w-[120px] truncate text-left font-semibold text-slate-800 text-xs">
                                    {{ currentUser?.name }}
                                </span>
                                <ChevronDownIcon class="h-4 w-4 text-slate-400" />
                            </button>
                        </template>

                        <template #content>
                            <div class="px-4 py-2 border-b border-slate-100">
                                <p class="text-[11px] text-slate-400 font-medium">Masuk sebagai</p>
                                <p class="text-xs font-bold text-slate-800 truncate">{{ currentUser?.email }}</p>
                                <span class="inline-block text-[10px] font-bold text-brand-700 bg-brand-50 px-2 py-0.5 rounded-full mt-1">
                                    Role: {{ roleDisplay }}
                                </span>
                            </div>
                            <DropdownLink :href="route('profile.edit')" class="flex items-center gap-2 text-xs">
                                <UserCircleIcon class="w-4 h-4 text-slate-400" />
                                <span>Profil Saya</span>
                            </DropdownLink>
                            <DropdownLink :href="route('logout')" method="post" as="button" class="flex items-center gap-2 text-xs text-rose-600 hover:text-rose-700 hover:bg-rose-50">
                                <ArrowRightOnRectangleIcon class="w-4 h-4 text-rose-500" />
                                <span>Keluar (Log Out)</span>
                            </DropdownLink>
                        </template>
                    </Dropdown>
                </div>
            </header>

            <!-- Optional Page Header Slot -->
            <div class="bg-white border-b border-slate-200/80" v-if="$slots.header">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </div>

            <!-- Flash Alert Messages -->
            <div v-if="$page.props.flash?.success" class="max-w-7xl mx-auto w-full mt-6 px-4 sm:px-6 lg:px-8">
                <div class="p-4 bg-emerald-50/90 border border-emerald-200 text-emerald-900 rounded-xl text-xs sm:text-sm flex items-center gap-3 shadow-xs">
                    <CheckCircleIcon class="w-5 h-5 text-emerald-600 shrink-0" />
                    <span class="font-medium">{{ $page.props.flash.success }}</span>
                </div>
            </div>
            <div v-if="$page.props.flash?.error" class="max-w-7xl mx-auto w-full mt-6 px-4 sm:px-6 lg:px-8">
                <div class="p-4 bg-rose-50/90 border border-rose-200 text-rose-900 rounded-xl text-xs sm:text-sm flex items-center gap-3 shadow-xs">
                    <ExclamationCircleIcon class="w-5 h-5 text-rose-600 shrink-0" />
                    <span class="font-medium">{{ $page.props.flash.error }}</span>
                </div>
            </div>

            <!-- Main Page Content Body -->
            <main class="flex-1 py-8">
                <slot />
            </main>
        </div>
    </div>
</template>
