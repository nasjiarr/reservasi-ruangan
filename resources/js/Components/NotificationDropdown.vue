<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import { formatDistanceToNow, parseISO } from 'date-fns';
import { id } from 'date-fns/locale';
import {
    BellIcon,
    CheckCircleIcon,
    XCircleIcon,
    CalendarDaysIcon,
} from '@heroicons/vue/24/outline';

const isOpen = ref(false);
const notifications = ref([]);
const unreadCount = ref(0);
const loading = ref(false);

const dropdownRef = ref(null);

const fetchNotifications = async () => {
    try {
        loading.value = true;
        const response = await axios.get(route('notifications.index'));
        notifications.value = response.data.notifications || [];
        unreadCount.value = response.data.unread_count || 0;
    } catch (error) {
        console.error('Failed to fetch notifications:', error);
    } finally {
        loading.value = false;
    }
};

const markAllAsRead = async () => {
    try {
        await axios.post(route('notifications.markAllAsRead'));
        unreadCount.value = 0;
        notifications.value = notifications.value.map((n) => ({
            ...n,
            read_at: n.read_at || new Date().toISOString(),
        }));
    } catch (error) {
        console.error('Failed to mark all as read:', error);
    }
};

const handleNotificationClick = async (notification) => {
    if (!notification.read_at) {
        try {
            await axios.post(route('notifications.markAsRead', notification.id));
            notification.read_at = new Date().toISOString();
            if (unreadCount.value > 0) {
                unreadCount.value--;
            }
        } catch (error) {
            console.error('Failed to mark as read:', error);
        }
    }

    isOpen.value = false;

    if (notification.data?.url) {
        router.visit(notification.data.url);
    }
};

const toggleDropdown = () => {
    isOpen.value = !isOpen.value;
    if (isOpen.value) {
        fetchNotifications();
    }
};

const handleClickOutside = (event) => {
    if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
        isOpen.value = false;
    }
};

const timeAgo = (dateStr) => {
    try {
        return formatDistanceToNow(parseISO(dateStr), { addSuffix: true, locale: id });
    } catch {
        return dateStr;
    }
};

onMounted(() => {
    fetchNotifications();
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});
</script>

<template>
    <div class="relative" ref="dropdownRef">
        <!-- Bell Icon Button with Badge -->
        <button
            type="button"
            @click="toggleDropdown"
            class="relative p-2 text-slate-500 hover:text-slate-900 hover:bg-slate-100 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500 transition duration-150"
            aria-label="Notifikasi"
            :title="unreadCount > 0 ? `${unreadCount} notifikasi belum dibaca` : 'Notifikasi'"
        >
            <BellIcon class="w-5 h-5" />

            <!-- Unread Badge with Nordic Teal Styling -->
            <span
                v-if="unreadCount > 0"
                class="absolute -top-0.5 -right-0.5 inline-flex items-center justify-center px-1.5 py-0.5 text-[10px] font-extrabold leading-none text-white transform bg-rose-600 rounded-full min-w-[1.2rem] h-[1.2rem] ring-2 ring-white shadow-xs animate-pulse"
            >
                {{ unreadCount > 99 ? '99+' : unreadCount }}
            </span>
        </button>

        <!-- Dropdown Menu -->
        <div
            v-if="isOpen"
            class="absolute right-0 mt-2 w-80 sm:w-96 bg-white rounded-2xl shadow-xl border border-slate-200/90 py-0 z-50 overflow-hidden ring-1 ring-black/5"
        >
            <!-- Header -->
            <div class="px-4 py-3 bg-slate-50/80 border-b border-slate-100 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <h4 class="text-xs font-heading font-extrabold uppercase tracking-wider text-slate-800">
                        Notifikasi
                    </h4>
                    <span
                        v-if="unreadCount > 0"
                        class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-brand-100 text-brand-800"
                    >
                        {{ unreadCount }} baru
                    </span>
                </div>
                <button
                    v-if="unreadCount > 0"
                    @click="markAllAsRead"
                    class="text-xs text-brand-600 hover:text-brand-800 font-semibold transition hover:underline"
                >
                    Tandai dibaca
                </button>
            </div>

            <!-- List Notifikasi -->
            <div class="max-h-84 overflow-y-auto divide-y divide-slate-100">
                <div v-if="loading && notifications.length === 0" class="p-6 text-center text-xs text-slate-400">
                    Memuat notifikasi...
                </div>

                <div v-else-if="notifications.length === 0" class="p-8 text-center">
                    <div class="w-10 h-10 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-2">
                        <BellIcon class="w-5 h-5" />
                    </div>
                    <div class="text-xs font-bold text-slate-700">Tidak ada notifikasi</div>
                    <div class="text-[11px] text-slate-400 mt-0.5">Semua permohonan dan konfirmasi terbaru akan muncul di sini.</div>
                </div>

                <template v-else>
                    <div
                        v-for="item in notifications"
                        :key="item.id"
                        @click="handleNotificationClick(item)"
                        class="p-3.5 hover:bg-slate-50 cursor-pointer transition flex items-start space-x-3 group relative"
                        :class="{ 'bg-brand-50/30': !item.read_at }"
                    >
                        <!-- Type-specific Semantic Icon -->
                        <div class="shrink-0 mt-0.5">
                            <div
                                v-if="item.data?.type === 'approved'"
                                class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 border border-emerald-200/60 flex items-center justify-center"
                            >
                                <CheckCircleIcon class="w-4 h-4 stroke-2" />
                            </div>
                            <div
                                v-else-if="item.data?.type === 'rejected'"
                                class="w-8 h-8 rounded-xl bg-rose-50 text-rose-600 border border-rose-200/60 flex items-center justify-center"
                            >
                                <XCircleIcon class="w-4 h-4 stroke-2" />
                            </div>
                            <div
                                v-else
                                class="w-8 h-8 rounded-xl bg-brand-50 text-brand-700 border border-brand-200/60 flex items-center justify-center"
                            >
                                <CalendarDaysIcon class="w-4 h-4" />
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between gap-2">
                                <p class="text-xs font-heading font-bold text-slate-900 truncate">
                                    {{ item.data?.title || 'Notifikasi' }}
                                </p>
                                <span
                                    v-if="!item.read_at"
                                    class="w-2 h-2 rounded-full bg-brand-600 shrink-0"
                                    title="Belum dibaca"
                                ></span>
                            </div>

                            <p class="text-xs text-slate-600 mt-0.5 line-clamp-2 leading-relaxed">
                                {{ item.data?.message || '-' }}
                            </p>

                            <div class="flex items-center justify-between text-[10px] text-slate-400 mt-1.5">
                                <span>{{ timeAgo(item.created_at) }}</span>
                                <span class="text-brand-600 font-semibold group-hover:underline opacity-0 group-hover:opacity-100 transition">
                                    Lihat Detail &rarr;
                                </span>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</template>
