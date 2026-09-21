<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import {
    EnvelopeIcon,
    LockClosedIcon,
    EyeIcon,
    EyeSlashIcon,
    CalendarDaysIcon,
    QrCodeIcon,
    ShieldCheckIcon,
    ArrowRightIcon,
    SparklesIcon,
    CheckCircleIcon,
} from '@heroicons/vue/24/outline';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const showPassword = ref(false);
const activeDemoRole = ref('');

const demoAccounts = [
    { role: 'Admin', email: 'admin@example.com', password: 'password', badge: 'Full Akses' },
    { role: 'Manager', email: 'manager@example.com', password: 'password', badge: 'Persetujuan' },
    { role: 'Staff', email: 'staff@example.com', password: 'password', badge: 'Pemesanan' },
];

const selectDemo = (account) => {
    form.email = account.email;
    form.password = account.password;
    activeDemoRole.value = account.role;
};

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Masuk ke Akun - RoomSpace" />

    <div class="min-h-screen flex bg-white font-sans text-slate-900 antialiased selection:bg-brand-500 selection:text-white">
        <!-- Left Side: Login Form -->
        <div class="w-full lg:w-1/2 flex flex-col justify-between p-6 sm:p-12 lg:p-16 min-h-screen bg-white">
            <!-- Brand Header -->
            <div class="flex items-center justify-between">
                <Link :href="route('login')" class="flex items-center gap-3 group focus:outline-none">
                    <ApplicationLogo class="h-10 w-auto transition-transform group-hover:scale-105" />
                </Link>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-brand-50 text-brand-700 border border-brand-200">
                    <span class="w-1.5 h-1.5 rounded-full bg-brand-500 animate-pulse"></span>
                    <span>SaaS Portal</span>
                </span>
            </div>

            <!-- Form Content Box -->
            <div class="my-auto py-8 max-w-md w-full mx-auto">
                <div class="space-y-2 mb-6">
                    <h1 class="font-heading font-extrabold text-2xl sm:text-3xl text-slate-900 tracking-tight">
                        Selamat Datang Kembali
                    </h1>
                    <p class="text-sm text-slate-500 leading-relaxed">
                        Silakan masukkan kredensial akun Anda untuk mengakses sistem manajemen dan peminjaman ruangan.
                    </p>
                </div>

                <!-- Quick Demo Accounts Switcher -->
                <div class="mb-6 p-3.5 bg-slate-50 rounded-2xl border border-slate-200/80 shadow-xs">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-600 flex items-center gap-1.5">
                            <SparklesIcon class="w-4 h-4 text-amber-500" />
                            <span>Pilih Akun Demo Uji Coba:</span>
                        </span>
                        <span class="text-[11px] text-slate-400">Auto-fill 1-Klik</span>
                    </div>
                    <div class="grid grid-cols-3 gap-2">
                        <button
                            v-for="acc in demoAccounts"
                            :key="acc.role"
                            type="button"
                            @click="selectDemo(acc)"
                            :class="[
                                activeDemoRole === acc.role
                                    ? 'bg-brand-600 text-white border-brand-600 shadow-xs font-bold'
                                    : 'bg-white hover:bg-slate-100/80 text-slate-700 border-slate-200 font-semibold',
                                'px-2.5 py-2 text-xs rounded-xl border text-center transition-all duration-150 flex flex-col items-center justify-center'
                            ]"
                        >
                            <span>{{ acc.role }}</span>
                            <span
                                :class="activeDemoRole === acc.role ? 'text-teal-200' : 'text-slate-400'"
                                class="text-[10px] font-normal"
                            >
                                {{ acc.badge }}
                            </span>
                        </button>
                    </div>
                </div>

                <!-- Session Status / Alert -->
                <div v-if="status" class="mb-4 p-4 rounded-xl text-sm bg-emerald-50 text-emerald-800 border border-emerald-200">
                    {{ status }}
                </div>

                <!-- Login Form -->
                <form @submit.prevent="submit" class="space-y-4">
                    <!-- Email Field -->
                    <div>
                        <InputLabel for="email" value="Alamat Email" class="text-xs font-bold uppercase text-slate-700 mb-1.5 tracking-wider" />
                        <div class="relative rounded-xl shadow-xs">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <EnvelopeIcon class="h-5 w-5" />
                            </div>
                            <input
                                id="email"
                                type="email"
                                v-model="form.email"
                                required
                                autofocus
                                autocomplete="username"
                                placeholder="nama@perusahaan.com"
                                class="block w-full pl-11 pr-4 py-2.5 bg-white border border-slate-300 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 rounded-xl text-sm text-slate-800 placeholder:text-slate-400 transition"
                            />
                        </div>
                        <InputError class="mt-1.5" :message="form.errors.email" />
                    </div>

                    <!-- Password Field -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <InputLabel for="password" value="Kata Sandi" class="text-xs font-bold uppercase text-slate-700 tracking-wider" />
                            <Link
                                v-if="canResetPassword"
                                :href="route('password.request')"
                                class="text-xs font-semibold text-brand-600 hover:text-brand-700 transition"
                            >
                                Lupa sandi?
                            </Link>
                        </div>
                        <div class="relative rounded-xl shadow-xs">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <LockClosedIcon class="h-5 w-5" />
                            </div>
                            <input
                                id="password"
                                :type="showPassword ? 'text' : 'password'"
                                v-model="form.password"
                                required
                                autocomplete="current-password"
                                placeholder="••••••••"
                                class="block w-full pl-11 pr-11 py-2.5 bg-white border border-slate-300 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 rounded-xl text-sm text-slate-800 placeholder:text-slate-400 transition"
                            />
                            <button
                                type="button"
                                @click="showPassword = !showPassword"
                                class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none transition"
                                title="Lihat/Sembunyikan Sandi"
                            >
                                <EyeSlashIcon v-if="showPassword" class="h-5 w-5" />
                                <EyeIcon v-else class="h-5 w-5" />
                            </button>
                        </div>
                        <InputError class="mt-1.5" :message="form.errors.password" />
                    </div>

                    <!-- Remember Me Checkbox -->
                    <div class="flex items-center justify-between pt-1">
                        <label class="flex items-center cursor-pointer select-none">
                            <Checkbox name="remember" v-model:checked="form.remember" />
                            <span class="ms-2.5 text-xs font-medium text-slate-600">Ingat sesi saya di peramban ini</span>
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-2">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="w-full py-3 px-4 bg-brand-600 hover:bg-brand-700 active:bg-brand-800 text-white font-bold text-sm rounded-xl shadow-md hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 transition-all duration-150 flex items-center justify-center gap-2 disabled:opacity-60 cursor-pointer"
                        >
                            <svg
                                v-if="form.processing"
                                class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                            >
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span>{{ form.processing ? 'Memverifikasi...' : 'Masuk ke Sistem' }}</span>
                            <ArrowRightIcon v-if="!form.processing" class="w-4 h-4 stroke-2" />
                        </button>
                    </div>
                </form>
            </div>

            <!-- Footer -->
            <div class="text-xs text-slate-400 text-center sm:text-left">
                &copy; {{ new Date().getFullYear() }} RoomSpace Enterprise. Sistem Reservasi Ruangan Terpadu.
            </div>
        </div>

        <!-- Right Side: Product Showcase Banner (Desktop) -->
        <div class="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-slate-900 via-slate-800 to-teal-950 p-12 lg:p-16 flex-col justify-between relative overflow-hidden text-white">
            <!-- Decorative Glow Elements -->
            <div class="absolute -top-24 -right-24 w-96 h-96 bg-brand-500/20 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-24 -left-24 w-96 h-96 bg-teal-500/10 rounded-full blur-3xl pointer-events-none"></div>

            <!-- Top Header Badge -->
            <div class="relative z-10 flex items-center justify-between">
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-white/10 backdrop-blur border border-white/10 text-xs font-semibold text-teal-300">
                    <SparklesIcon class="w-4 h-4 text-teal-300" />
                    <span>Room Management Platform v1.0</span>
                </div>
            </div>

            <!-- Middle Value Proposition Cards -->
            <div class="relative z-10 my-auto py-10 space-y-8 max-w-lg">
                <div class="space-y-3">
                    <h2 class="font-heading font-extrabold text-3xl xl:text-4xl text-white tracking-tight leading-tight">
                        Koordinasi Ruangan Rapat Lebih Efisien & Terstruktur.
                    </h2>
                    <p class="text-slate-300 text-sm xl:text-base leading-relaxed">
                        Hindari bentrok jadwal, pantau pemakaian secara transparan, dan lakukan verifikasi kehadiran mandiri dengan teknologi modern.
                    </p>
                </div>

                <!-- 3 Feature Highlight Cards -->
                <div class="space-y-3.5">
                    <div class="p-4 rounded-2xl bg-white/5 backdrop-blur border border-white/10 flex items-start gap-4 hover:bg-white/10 transition">
                        <div class="w-10 h-10 rounded-xl bg-teal-500/20 border border-teal-400/30 flex items-center justify-center text-teal-300 shrink-0">
                            <CalendarDaysIcon class="w-5 h-5" />
                        </div>
                        <div>
                            <h3 class="font-heading font-bold text-sm text-white">Kalender & Deteksi Bentrok Otomatis</h3>
                            <p class="text-xs text-slate-300 mt-0.5">Sistem memvalidasi jam penggunaan dan mencegah reservasi tumpang tindih secara instan.</p>
                        </div>
                    </div>

                    <div class="p-4 rounded-2xl bg-white/5 backdrop-blur border border-white/10 flex items-start gap-4 hover:bg-white/10 transition">
                        <div class="w-10 h-10 rounded-xl bg-amber-500/20 border border-amber-400/30 flex items-center justify-center text-amber-300 shrink-0">
                            <QrCodeIcon class="w-5 h-5" />
                        </div>
                        <div>
                            <h3 class="font-heading font-bold text-sm text-white">Check-in Mandiri Berbasis QR Code</h3>
                            <p class="text-xs text-slate-300 mt-0.5">Konfirmasi kehadiran di pintu ruangan menggunakan scan QR kamera smartphone 15 menit sebelum acara.</p>
                        </div>
                    </div>

                    <div class="p-4 rounded-2xl bg-white/5 backdrop-blur border border-white/10 flex items-start gap-4 hover:bg-white/10 transition">
                        <div class="w-10 h-10 rounded-xl bg-emerald-500/20 border border-emerald-400/30 flex items-center justify-center text-emerald-300 shrink-0">
                            <ShieldCheckIcon class="w-5 h-5" />
                        </div>
                        <div>
                            <h3 class="font-heading font-bold text-sm text-white">Persetujuan Multi-Level & Laporan</h3>
                            <p class="text-xs text-slate-300 mt-0.5">Alur persetujuan terstruktur untuk Manager & Admin dilengkapi ekspor laporan format Excel dan PDF.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom Showcase Stat -->
            <div class="relative z-10 pt-6 border-t border-white/10 flex items-center justify-between text-xs text-slate-400">
                <div class="flex items-center gap-2">
                    <CheckCircleIcon class="w-4 h-4 text-teal-400" />
                    <span>Role-Based Access Control (RBAC)</span>
                </div>
                <span>Laravel 12 • Inertia • Vue 3</span>
            </div>
        </div>
    </div>
</template>
