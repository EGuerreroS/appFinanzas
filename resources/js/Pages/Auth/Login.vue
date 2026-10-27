<script setup>
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
    email: '',
    password: '',
    remember: true,
});

const submit = () => form.post('/login', { onFinish: () => form.reset('password') });
</script>

<template>
    <Head title="Iniciar sesión" />
    <div class="fullscreen bg-teal-8 flex flex-center q-pa-md">
        <q-card style="width: 100%; max-width: 380px">
            <q-card-section class="text-center q-pt-lg">
                <q-icon name="account_balance_wallet" size="48px" color="teal-8" />
                <div class="text-h6 q-mt-sm">Finanzas</div>
                <div class="text-caption text-grey-7">Control de ingresos, gastos y presupuestos</div>
            </q-card-section>

            <q-card-section>
                <q-form class="q-gutter-md" @submit.prevent="submit">
                    <q-input
                        v-model="form.email"
                        type="email"
                        label="Correo"
                        outlined
                        autofocus
                        :error="!!form.errors.email"
                        :error-message="form.errors.email"
                    >
                        <template #prepend><q-icon name="mail" /></template>
                    </q-input>
                    <q-input
                        v-model="form.password"
                        type="password"
                        label="Contraseña"
                        outlined
                        :error="!!form.errors.password"
                        :error-message="form.errors.password"
                    >
                        <template #prepend><q-icon name="lock" /></template>
                    </q-input>
                    <q-checkbox v-model="form.remember" label="Mantener sesión iniciada" />
                    <q-btn type="submit" label="Entrar" color="teal-8" class="full-width" unelevated :loading="form.processing" />
                </q-form>
            </q-card-section>
        </q-card>
    </div>
</template>
