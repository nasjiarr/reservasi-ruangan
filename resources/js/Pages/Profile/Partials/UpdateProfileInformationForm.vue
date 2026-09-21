<script setup>
import { computed } from 'vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import {
    UserCircleIcon,
    EnvelopeIcon,
    CheckCircleIcon,
    ShieldCheckIcon,
    ExclamationCircleIcon,
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
const user = computed(() => page.props.auth.user);
const userRoles = computed(() => user.value?.roles || []);
const roleDisplay = computed(() => {
    if (userRoles.value.includes('admin')) return 'Administrator';
    if (userRoles.value.includes('manager')) return 'Manager Fasilitas';
    return 'Staff Anggota';
});

const form = useForm({
    name: user.value.name,
    email: user.value.email,
});
</script>

<template>
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <!-- Card Header -->
        <div class="p-6 border-b border-slate-100 flex items-start gap-4">
            <div class="w-10 h-10 rounded-xl bg-teal-50 text-brand-600 flex items-center justify-center border border-teal-100 shrink-0">
                <UserCircleIcon class="w-6 h-6" />
            </div>
            <div>
                <h3 class="font-heading font-bold text-base text-slate-900">
                    Informasi Profil & Akun
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">
                    Perbarui nama lengkap dan alamat email yang terdaftar pada sistem RoomSpace.
                </p>
            </div>
        </div>

        <!-- Form Body -->
        <form @submit.prevent="form.patch(route('profile.update'))" class="p-6 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Name Field -->
                <div>
                    <InputLabel for="name" value="Nama Lengkap" class="font-bold text-slate-700 text-xs" />
                    <div class="relative mt-1.5">
                        <UserCircleIcon class="w-5 h-5 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" />
                        <TextInput
                            id="name"
                            type="text"
                            class="block w-full pl-10 text-xs sm:text-sm rounded-xl border-slate-200 focus:border-brand-500 focus:ring-brand-500/20"
                            v-model="form.name"
                            required
                            autofocus
                            autocomplete="name"
                            placeholder="Masukkan nama lengkap Anda"
                        />
                    </div>
                    <InputError class="mt-1.5" :message="form.errors.name" />
                </div>

                <!-- Email Field -->
                <div>
                    <InputLabel for="email" value="Alamat Email" class="font-bold text-slate-700 text-xs" />
                    <div class="relative mt-1.5">
                        <EnvelopeIcon class="w-5 h-5 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" />
                        <TextInput
                            id="email"
                            type="email"
                            class="block w-full pl-10 text-xs sm:text-sm rounded-xl border-slate-200 focus:border-brand-500 focus:ring-brand-500/20"
                            v-model="form.email"
                            required
                            autocomplete="username"
                            placeholder="nama@perusahaan.com"
                        />
                    </div>
                    <InputError class="mt-1.5" :message="form.errors.email" />
                </div>
            </div>

            <!-- Role & Permissions Preview Box -->
            <div class="bg-slate-50/70 p-4 rounded-xl border border-slate-200/70 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
                <div class="flex items-center gap-2.5">
                    <ShieldCheckIcon class="w-5 h-5 text-brand-600 shrink-0" />
                    <div>
                        <span class="font-bold text-slate-800">Peran & Hak Akses Akun:</span>
                        <span class="ml-1 text-slate-600">Ditetapkan sebagai <strong>{{ roleDisplay }}</strong></span>
                    </div>
                </div>
                <div class="inline-flex items-center px-2.5 py-1 rounded-full bg-brand-50 text-brand-700 border border-brand-200 font-bold text-[11px] self-start sm:self-auto">
                    {{ roleDisplay }}
                </div>
            </div>

            <!-- Email Verification Notice -->
            <div v-if="mustVerifyEmail && user.email_verified_at === null" class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-xs space-y-2">
                <div class="flex items-center gap-2 font-bold">
                    <ExclamationCircleIcon class="w-4 h-4 text-amber-600 shrink-0" />
                    <span>Alamat email Anda belum terverifikasi.</span>
                </div>
                <p class="text-amber-800">
                    Silakan klik tautan di bawah ini untuk mengirimkan ulang email verifikasi akun.
                </p>
                <Link
                    :href="route('verification.send')"
                    method="post"
                    as="button"
                    class="font-bold text-brand-700 hover:text-brand-800 underline"
                >
                    Kirim Ulang Email Verifikasi &rarr;
                </Link>

                <div
                    v-show="status === 'verification-link-sent'"
                    class="mt-2 font-bold text-emerald-700 flex items-center gap-1.5"
                >
                    <CheckCircleIcon class="w-4 h-4" />
                    <span>Tautan verifikasi baru telah dikirim ke alamat email Anda.</span>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="pt-4 border-t border-slate-100 flex items-center gap-4">
                <PrimaryButton :disabled="form.processing" class="gap-2">
                    <span>Simpan Perubahan</span>
                </PrimaryButton>

                <Transition
                    enter-active-class="transition ease-in-out duration-300"
                    enter-from-class="opacity-0 translate-x-2"
                    leave-active-class="transition ease-in-out duration-300"
                    leave-to-class="opacity-0"
                >
                    <div v-if="form.recentlySuccessful" class="flex items-center gap-1.5 text-xs font-bold text-emerald-600 bg-emerald-50 px-3 py-1.5 rounded-lg border border-emerald-200">
                        <CheckCircleIcon class="w-4 h-4" />
                        <span>Perubahan berhasil disimpan.</span>
                    </div>
                </Transition>
            </div>
        </form>
    </div>
</template>
