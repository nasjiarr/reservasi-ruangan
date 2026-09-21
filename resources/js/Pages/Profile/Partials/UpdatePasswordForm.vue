<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import {
    KeyIcon,
    LockClosedIcon,
    EyeIcon,
    EyeSlashIcon,
    CheckCircleIcon,
    ShieldExclamationIcon,
} from '@heroicons/vue/24/outline';

const passwordInput = ref(null);
const currentPasswordInput = ref(null);

const showCurrentPassword = ref(false);
const showNewPassword = ref(false);
const showConfirmPassword = ref(false);

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updatePassword = () => {
    form.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
        onError: () => {
            if (form.errors.password) {
                form.reset('password', 'password_confirmation');
                passwordInput.value?.focus();
            }
            if (form.errors.current_password) {
                form.reset('current_password');
                currentPasswordInput.value?.focus();
            }
        },
    });
};
</script>

<template>
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <!-- Card Header -->
        <div class="p-6 border-b border-slate-100 flex items-start gap-4">
            <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center border border-amber-100 shrink-0">
                <KeyIcon class="w-6 h-6" />
            </div>
            <div>
                <h3 class="font-heading font-bold text-base text-slate-900">
                    Keamanan & Kata Sandi
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">
                    Pastikan akun Anda menggunakan kombinasi kata sandi yang kuat dan aman untuk melindungi data reservasi.
                </p>
            </div>
        </div>

        <!-- Form Body -->
        <form @submit.prevent="updatePassword" class="p-6 space-y-6">
            <!-- Security Hint Box -->
            <div class="bg-slate-50/70 p-4 rounded-xl border border-slate-200/70 flex items-start gap-3 text-xs text-slate-600">
                <ShieldExclamationIcon class="w-5 h-5 text-amber-500 shrink-0 mt-0.5" />
                <div class="space-y-1">
                    <div class="font-bold text-slate-800">Rekomendasi Kata Sandi Aman:</div>
                    <p class="text-slate-500 leading-relaxed">
                        Gunakan minimal 8 karakter dengan perpaduan huruf besar, huruf kecil, angka, dan karakter simbol khusus untuk keamanan optimal.
                    </p>
                </div>
            </div>

            <!-- Current Password -->
            <div>
                <InputLabel for="current_password" value="Kata Sandi Saat Ini" class="font-bold text-slate-700 text-xs" />
                <div class="relative mt-1.5 max-w-md">
                    <LockClosedIcon class="w-5 h-5 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" />
                    <TextInput
                        id="current_password"
                        ref="currentPasswordInput"
                        v-model="form.current_password"
                        :type="showCurrentPassword ? 'text' : 'password'"
                        class="block w-full pl-10 pr-10 text-xs sm:text-sm rounded-xl border-slate-200 focus:border-brand-500 focus:ring-brand-500/20"
                        autocomplete="current-password"
                        placeholder="••••••••"
                    />
                    <button
                        type="button"
                        @click="showCurrentPassword = !showCurrentPassword"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 focus:outline-none"
                    >
                        <EyeIcon v-if="!showCurrentPassword" class="w-4 h-4" />
                        <EyeSlashIcon v-else class="w-4 h-4 text-brand-600" />
                    </button>
                </div>
                <InputError :message="form.errors.current_password" class="mt-1.5" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- New Password -->
                <div>
                    <InputLabel for="password" value="Kata Sandi Baru" class="font-bold text-slate-700 text-xs" />
                    <div class="relative mt-1.5">
                        <LockClosedIcon class="w-5 h-5 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" />
                        <TextInput
                            id="password"
                            ref="passwordInput"
                            v-model="form.password"
                            :type="showNewPassword ? 'text' : 'password'"
                            class="block w-full pl-10 pr-10 text-xs sm:text-sm rounded-xl border-slate-200 focus:border-brand-500 focus:ring-brand-500/20"
                            autocomplete="new-password"
                            placeholder="Minimal 8 karakter"
                        />
                        <button
                            type="button"
                            @click="showNewPassword = !showNewPassword"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 focus:outline-none"
                        >
                            <EyeIcon v-if="!showNewPassword" class="w-4 h-4" />
                            <EyeSlashIcon v-else class="w-4 h-4 text-brand-600" />
                        </button>
                    </div>
                    <InputError :message="form.errors.password" class="mt-1.5" />
                </div>

                <!-- Confirm Password -->
                <div>
                    <InputLabel for="password_confirmation" value="Konfirmasi Kata Sandi Baru" class="font-bold text-slate-700 text-xs" />
                    <div class="relative mt-1.5">
                        <LockClosedIcon class="w-5 h-5 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" />
                        <TextInput
                            id="password_confirmation"
                            v-model="form.password_confirmation"
                            :type="showConfirmPassword ? 'text' : 'password'"
                            class="block w-full pl-10 pr-10 text-xs sm:text-sm rounded-xl border-slate-200 focus:border-brand-500 focus:ring-brand-500/20"
                            autocomplete="new-password"
                            placeholder="Ketik ulang kata sandi baru"
                        />
                        <button
                            type="button"
                            @click="showConfirmPassword = !showConfirmPassword"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 focus:outline-none"
                        >
                            <EyeIcon v-if="!showConfirmPassword" class="w-4 h-4" />
                            <EyeSlashIcon v-else class="w-4 h-4 text-brand-600" />
                        </button>
                    </div>
                    <InputError :message="form.errors.password_confirmation" class="mt-1.5" />
                </div>
            </div>

            <!-- Form Actions -->
            <div class="pt-4 border-t border-slate-100 flex items-center gap-4">
                <PrimaryButton :disabled="form.processing">
                    <span>Perbarui Kata Sandi</span>
                </PrimaryButton>

                <Transition
                    enter-active-class="transition ease-in-out duration-300"
                    enter-from-class="opacity-0 translate-x-2"
                    leave-active-class="transition ease-in-out duration-300"
                    leave-to-class="opacity-0"
                >
                    <div v-if="form.recentlySuccessful" class="flex items-center gap-1.5 text-xs font-bold text-emerald-600 bg-emerald-50 px-3 py-1.5 rounded-lg border border-emerald-200">
                        <CheckCircleIcon class="w-4 h-4" />
                        <span>Kata sandi berhasil diperbarui.</span>
                    </div>
                </Transition>
            </div>
        </form>
    </div>
</template>
