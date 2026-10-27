<script setup>
import { computed, ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { useQuasar } from 'quasar';
import AppLayout from '@/Layouts/AppLayout.vue';
import { groupColors } from '@/utils/format';

const props = defineProps({
    categories: Array,
    groups: Object,
});

const $q = useQuasar();

const groupOptions = computed(() => Object.entries(props.groups).map(([value, label]) => ({ value, label })));

const columns = [
    { name: 'name', label: 'Nombre', field: 'name', align: 'left', sortable: true },
    { name: 'group', label: 'Grupo', field: 'group', align: 'left', sortable: true },
    { name: 'expenses_count', label: 'Gastos', field: 'expenses_count', align: 'right', sortable: true },
    { name: 'actions', label: '', field: 'id', align: 'right' },
];

const dialog = ref(false);
const form = useForm({ id: null, name: '', group: 'essential', color: '#1976D2' });

const openCreate = () => {
    form.reset();
    form.clearErrors();
    dialog.value = true;
};
const openEdit = (row) => {
    form.clearErrors();
    Object.assign(form, { id: row.id, name: row.name, group: row.group, color: row.color });
    dialog.value = true;
};
const save = () => {
    const options = { preserveScroll: true, onSuccess: () => (dialog.value = false) };
    form.id ? form.put(`/categories/${form.id}`, options) : form.post('/categories', options);
};
const remove = (row) => {
    $q.dialog({
        title: 'Eliminar categoría',
        message: `¿Eliminar "${row.name}"? También se borrarán sus presupuestos.`,
        cancel: true,
        ok: { label: 'Eliminar', color: 'negative', unelevated: true },
    }).onOk(() => router.delete(`/categories/${row.id}`, { preserveScroll: true }));
};
</script>

<template>
    <Head title="Categorías" />
    <AppLayout>
        <div class="row items-center justify-between q-mb-md">
            <h1 class="page-title">Categorías</h1>
            <q-btn unelevated color="teal-8" icon="add" label="Nueva categoría" @click="openCreate" />
        </div>

        <q-table flat bordered :rows="categories" :columns="columns" row-key="id" :pagination="{ rowsPerPage: 0 }" hide-pagination>
            <template #body-cell-name="p">
                <q-td :props="p">
                    <span class="color-dot" :style="{ background: p.row.color }" />{{ p.row.name }}
                </q-td>
            </template>
            <template #body-cell-group="p">
                <q-td :props="p">
                    <q-badge :color="groupColors[p.value]" :label="groups[p.value]" />
                </q-td>
            </template>
            <template #body-cell-actions="p">
                <q-td :props="p">
                    <q-btn flat round dense size="sm" icon="edit" @click="openEdit(p.row)" />
                    <q-btn flat round dense size="sm" icon="delete" color="negative" :disable="p.row.expenses_count > 0" @click="remove(p.row)">
                        <q-tooltip v-if="p.row.expenses_count > 0">Tiene gastos registrados</q-tooltip>
                    </q-btn>
                </q-td>
            </template>
        </q-table>

        <q-dialog v-model="dialog">
            <q-card style="width: 400px; max-width: 95vw">
                <q-card-section class="text-h6">{{ form.id ? 'Editar categoría' : 'Nueva categoría' }}</q-card-section>
                <q-form @submit.prevent="save">
                    <q-card-section class="q-gutter-sm">
                        <q-input v-model="form.name" label="Nombre" outlined autofocus :error="!!form.errors.name" :error-message="form.errors.name" />
                        <q-select
                            v-model="form.group"
                            :options="groupOptions"
                            label="Grupo"
                            emit-value
                            map-options
                            outlined
                            :error="!!form.errors.group"
                            :error-message="form.errors.group"
                        />
                        <q-input v-model="form.color" label="Color" outlined :error="!!form.errors.color" :error-message="form.errors.color">
                            <template #append>
                                <q-icon name="colorize" class="cursor-pointer" :style="{ color: form.color }">
                                    <q-popup-proxy cover transition-show="scale" transition-hide="scale">
                                        <q-color v-model="form.color" no-header-tabs default-view="palette" />
                                    </q-popup-proxy>
                                </q-icon>
                            </template>
                        </q-input>
                    </q-card-section>
                    <q-card-actions align="right">
                        <q-btn flat label="Cancelar" v-close-popup />
                        <q-btn type="submit" unelevated color="teal-8" label="Guardar" :loading="form.processing" />
                    </q-card-actions>
                </q-form>
            </q-card>
        </q-dialog>
    </AppLayout>
</template>
