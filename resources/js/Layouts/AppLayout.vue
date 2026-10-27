<script setup>
import { computed, ref, watch } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { useQuasar } from 'quasar';

const $q = useQuasar();
const page = usePage();
const drawer = ref(false);

const user = computed(() => page.props.auth.user);

const items = [
    { label: 'Dashboard', icon: 'dashboard', href: '/' },
    { label: 'Gastos', icon: 'trending_down', href: '/expenses' },
    { label: 'Ingresos', icon: 'trending_up', href: '/incomes' },
    { label: 'Presupuestos', icon: 'savings', href: '/budgets' },
    { label: 'Categorías', icon: 'label', href: '/categories' },
];

const isActive = (href) => {
    const path = page.url.split('?')[0];
    return href === '/' ? path === '/' : path.startsWith(href);
};

const go = (href) => {
    drawer.value = $q.screen.gt.sm ? drawer.value : false;
    router.visit(href);
};

const logout = () => router.post('/logout');

// Muestra los mensajes flash que manda Laravel después de cada acción
watch(
    () => page.props.flash,
    (flash) => {
        if (flash?.success) $q.notify({ type: 'positive', message: flash.success });
        if (flash?.warning) $q.notify({ type: 'warning', message: flash.warning, timeout: 6000, icon: 'warning' });
        if (flash?.error) $q.notify({ type: 'negative', message: flash.error });
    },
    { immediate: true },
);
</script>

<template>
    <q-layout view="hHh lpR fFf">
        <q-header elevated class="bg-teal-8 text-white">
            <q-toolbar>
                <q-btn flat dense round icon="menu" class="lt-md" @click="drawer = !drawer" />
                <q-toolbar-title class="text-weight-bold">
                    <q-icon name="account_balance_wallet" class="q-mr-sm" />Finanzas
                </q-toolbar-title>
                <div class="gt-xs q-mr-sm">{{ user?.nombres }}</div>
                <q-btn flat dense round icon="logout" @click="logout">
                    <q-tooltip>Cerrar sesión</q-tooltip>
                </q-btn>
            </q-toolbar>
        </q-header>

        <q-drawer v-model="drawer" show-if-above bordered :width="220" :breakpoint="1023">
            <q-list padding>
                <q-item
                    v-for="item in items"
                    :key="item.href"
                    clickable
                    v-ripple
                    :active="isActive(item.href)"
                    active-class="bg-teal-1 text-teal-9 text-weight-medium"
                    @click="go(item.href)"
                >
                    <q-item-section avatar><q-icon :name="item.icon" /></q-item-section>
                    <q-item-section>{{ item.label }}</q-item-section>
                </q-item>
            </q-list>
        </q-drawer>

        <q-page-container>
            <q-page class="q-pa-md">
                <div style="max-width: 1200px; margin: 0 auto">
                    <slot />
                </div>
            </q-page>
        </q-page-container>
    </q-layout>
</template>
