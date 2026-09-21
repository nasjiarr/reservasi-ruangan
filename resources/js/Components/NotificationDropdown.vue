<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import { formatDistanceToNow, parseISO } from 'date-fns';
import { id } from 'date-fns/locale';

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
            class="relative p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-full focus:outline-none focus:ring-2 focus:ring-indigo-500 transition duration-150"
            aria-label="Notifikasi"
        >
            <svg
                class="w-6 h-6"
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"
                />
            </svg>

            <!-- Unread Badge -->
            <span
                v-if="unreadCount > 0"
                class="absolute top-0 right-0 inline-flex items-center justify-center px-1.5 py-0.5 text-xs font-bold leading-none text-white transform translate-x-1/4 -translate-y-1/4 bg-red-600 rounded-full min-w-[1.25rem]"
            >
                {{ unreadCount > 99 ? '99+' : unreadCount }}
            </span>
        </button>

        <!-- Dropdown Menu -->
        <div
            v-if="isOpen"
            class="absolute right-0 mt-2 w-80 sm:w-96 bg-white rounded-lg shadow-lg border border-gray-200 py-2 z-50 overflow-hidden"
        >
            <!-- Header -->
            <div class="px-4 py-2 border-b border-gray-100 flex items-center justify-between">
                <h4 class="text-sm font-semibold text-gray-800">
                    Notifikasi
                    <span v-if="unreadCount > 0" class="text-xs font-normal text-indigo-600 ml-1">
                        ({{ unreadCount }} belum dibaca)
                    </span>
                </h4>
                <button
                    v-if="unreadCount > 0"
                    @click="markAllAsRead"
                    class="text-xs text-indigo-600 hover:text-indigo-800 font-medium transition"
                >
                    Tandai semua dibaca
                </button>
            </div>

            <!-- List Notifikasi -->
            <div class="max-h-80 overflow-y-auto divide-y divide-gray-100">
                <div v-if="loading && notifications.length === 0" class="p-4 text-center text-xs text-gray-500">
                    Memuat notifikasi...
                </div>

                <div v-else-if="notifications.length === 0" class="p-6 text-center text-sm text-gray-400">
                    Tidak ada notifikasi.
                </div>

                <template v-else>
                    <div
                        v-for="item in notifications"
                        :key="item.id"
                        @click="handleNotificationClick(item)"
                        class="p-3.5 hover:bg-gray-50 cursor-pointer transition flex items-start space-x-3"
                        :class="{ 'bg-indigo-50/40': !item.read_at }"
                    >
                        <!-- Unread Dot -->
                        <div class="mt-1 shrink-0">
                            <span
                                v-if="!item.read_at"
                                class="w-2.5 h-2.5 bg-indigo-600 rounded-full inline-block"
                            ></span>
                            <span
                                v-else
                                class="w-2.5 h-2.5 bg-gray-300 rounded-full inline-block"
                            ></span>
                        </div>

                        <!-- Content -->
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-semibold text-gray-800 truncate">
                                {{ item.data?.title || 'Notifikasi' }}
                            </p>
                            <p class="text-xs text-gray-600 mt-0.5 line-clamp-2">
                                {{ item.data?.message || '-' }}
                            </p>
                            <span class="text-[10px] text-gray-400 mt-1 block">
                                {{ timeAgo(item.created_at) }}
                            </span>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</template>

