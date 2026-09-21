<script setup>
import { nextTick, ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import DangerButton from '@/Components/DangerButton.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import {
    ExclamationTriangleIcon,
    XMarkIcon,
    LockClosedIcon,
} from '@heroicons/vue/24/outline';

const confirmingUserDeletion = ref(false);
const passwordInput = ref(null);

const form = useForm({
    password: '',
});

const confirmUserDeletion = () => {
    confirmingUserDeletion.value = true;
    nextTick(() => passwordInput.value?.focus());
};

const deleteUser = () => {
    form.delete(route('profile.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value?.focus(),
        onFinish: () => form.reset(),
    });
};

const closeModal = () => {
    confirmingUserDeletion.value = false;
    form.reset();
};
</script>

<template>
    <div class="bg-rose-50/20 rounded-2xl border border-rose-200/80 shadow-xs overflow-hidden">
        <!-- Card Header -->
        <div class="p-6 border-b border-rose-100/80 flex items-start gap-4">
            <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center border border-rose-200/80 shrink-0">
                <ExclamationTriangleIcon class="w-6 h-6" />
            </div>
            <div>
                <h3 class="font-heading font-bold text-base text-slate-900">
                    Zona Bahaya: Hapus Akun
                </h3>
                <p class="text-xs text-slate-500 mt-0.5 leading-relaxed">
                    Setelah akun Anda dihapus, seluruh data reservasi, hak akses, dan preferensi akun Anda akan dihapus secara permanen dari basis data.
                </p>
            </div>
        </div>

        <!-- Card Body -->
        <div class="p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="text-xs text-slate-600 max-w-lg leading-relaxed">
                Harap pastikan Anda telah menyelesaikan atau membatalkan seluruh agenda reservasi aktif sebelum melanjutkan proses penghapusan akun.
            </div>

            <DangerButton @click="confirmUserDeletion" class="shrink-0">
                Hapus Akun Saya
            </DangerButton>
        </div>

        <!-- Confirmation Modal -->
        <Modal :show="confirmingUserDeletion" @close="closeModal" max-width="md">
            <div class="p-6">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-rose-50 border border-rose-200 flex items-center justify-center text-rose-600">
                            <ExclamationTriangleIcon class="w-5 h-5" />
                        </div>
                        <h3 class="font-heading font-bold text-base text-slate-900">
                            Konfirmasi Hapus Akun
                        </h3>
                    </div>
                    <button
                        @click="closeModal"
                        class="p-1 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition"
                    >
                        <XMarkIcon class="w-5 h-5" />
                    </button>
                </div>

                <div class="mt-4 space-y-4">
                    <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                        Apakah Anda benar-benar yakin ingin menghapus akun Anda? Seluruh riwayat reservasi dan hak akses akan dihapus permanen. Masukkan kata sandi Anda untuk mengonfirmasi tindakan ini.
                    </p>

                    <div>
                        <InputLabel for="delete_password" value="Kata Sandi Anda" class="font-bold text-slate-700 text-xs" />
                        <div class="relative mt-1.5">
                            <LockClosedIcon class="w-5 h-5 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" />
                            <TextInput
                                id="delete_password"
                                ref="passwordInput"
                                v-model="form.password"
                                type="password"
                                class="block w-full pl-10 text-xs sm:text-sm rounded-xl border-slate-200 focus:border-rose-500 focus:ring-rose-500/20"
                                placeholder="Masukkan kata sandi akun"
                                @keyup.enter="deleteUser"
                            />
                        </div>
                        <InputError :message="form.errors.password" class="mt-1.5" />
                    </div>
                </div>

                <div class="mt-6 flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                    <SecondaryButton @click="closeModal">
                        Batal
                    </SecondaryButton>

                    <DangerButton
                        :class="{ 'opacity-50': form.processing }"
                        :disabled="form.processing"
                        @click="deleteUser"
                    >
                        Ya, Hapus Akun Secara Permanen
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </div>
</template>
