<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="min-h-screen flex flex-col sm:justify-center items-center py-10 sm:py-16 bg-slate-50 relative overflow-hidden font-sans text-slate-900 antialiased">
        <!-- Background decorative ambient lights -->
        <div class="absolute -top-36 -left-36 w-96 h-96 bg-brand-200/40 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-36 -right-36 w-96 h-96 bg-teal-200/30 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col items-center mb-6">
            <Link :href="route('login')" class="focus:outline-none focus:ring-2 focus:ring-brand-500 rounded-xl">
                <ApplicationLogo class="h-11 w-auto shadow-xs" />
            </Link>
        </div>

        <div
            class="relative z-10 w-full sm:max-w-md px-6 py-8 bg-white border border-slate-200/80 shadow-card overflow-hidden sm:rounded-2xl"
        >
            <slot />
        </div>

        <div class="relative z-10 mt-8 text-center text-xs text-slate-400">
            &copy; {{ new Date().getFullYear() }} RoomSpace. All rights reserved.
        </div>
    </div>
</template>
