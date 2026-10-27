<script setup>
import { computed, reactive, ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { useQuasar } from 'quasar';
import AppLayout from '@/Layouts/AppLayout.vue';
import { money, formatDate, toISODate, ranges } from '@/utils/format';

const props = defineProps({
    incomes: Array,
    filters: Object,
});

const $q = useQuasar();

const filter = reactive({ from: props.filters.from, to: props.filters.to });
const applyFilters = () => router.get('/incomes', { ...filter }, { preserveState: true, preserveScroll: true, replace: true });
const setRange = (range) => {
    Object.assign(filter, range);
    applyFilters();
};

const columns = [
    { name: 'date', label: 'Fecha', field: 'date', align: 'left', sortable: true, format: formatDate },
    { name: 'description', label: 'Descripción', field: 'description', align: 'left' },
    { name: 'amount', label: 'Monto', field: 'amount', align: 'right', sortable: true },
    { name: 'actions', label: '', field: 'id', align: 'right' },
];
const total = computed(() => props.incomes.reduce((sum, i) => sum + Number(i.amount), 0));

const dialog = ref(false);
const form = useForm({ id: null, amount: null, description: '', date: toISODate(new Date()) });

const openCreate = () => {
    form.reset();
    form.clearErrors();
    form.date = toISODate(new Date());
    dialog.value = true;
};
const openEdit = (row) => {
    form.clearErrors();
    Object.assign(form, { id: row.id, amount: row.amount, description: row.description ?? '', date: row.date });
    dialog.value = true;
};
const save = () => {
    const options = { preserveScroll: true, onSuccess: () => (dialog.value = false) };
    form.id ? form.put(`/incomes/${form.id}`, options) : form.post('/incomes', options);
};
const remove = (row) => {
    $q.dialog({
        title: 'Eliminar ingreso',
        message: `¿Eliminar "${row.description || 'ingreso'}" por ${money(row.amount)}?`,
        cancel: true,
        ok: { label: 'Eliminar', color: 'negative', unelevated: true },
    }).onOk(() => router.delete(`/incomes/${row.id}`, { preserveScroll: true }));
};
</script>

<template>
    <Head title="Ingresos" />
    <AppLayout>
        <div class="row items-center justify-between q-mb-md">
            <h1 class="page-title">Ingresos</h1>
            <q-btn unelevated color="teal-8" icon="add" label="Nuevo ingreso" @click="openCreate" />
        </div>

        <q-card flat bordered class="q-mb-md">
            <q-card-section class="row q-col-gutter-sm items-center">
                <div class="col-12 col-md-auto">
                    <q-btn-group outline>
                        <q-btn outline color="teal-8" label="Semana" @click="setRange(ranges.week())" />
                        <q-btn outline color="teal-8" label="Mes" @click="setRange(ranges.month())" />
                        <q-btn outline color="teal-8" label="Mes ant." @click="setRange(ranges.month(-1))" />
                        <q-btn outline color="teal-8" label="Año" @click="setRange(ranges.year())" />
                    </q-btn-group>
                </div>
                <div class="col-6 col-md-2">
                    <q-input v-model="filter.from" type="date" label="Desde" dense outlined stack-label @update:model-value="applyFilters" />
                </div>
                <div class="col-6 col-md-2">
                    <q-input v-model="filter.to" type="date" label="Hasta" dense outlined stack-label @update:model-value="applyFilters" />
                </div>
            </q-card-section>
        </q-card>

        <q-table
            flat
            bordered
            :rows="incomes"
            :columns="columns"
            row-key="id"
            :pagination="{ rowsPerPage: 15, sortBy: 'date', descending: true }"
            no-data-label="No hay ingresos en este periodo"
        >
            <template #top>
                <div class="text-subtitle1">
                    Total: <span class="text-weight-bold text-positive money">{{ money(total) }}</span>
                    <span class="text-caption text-grey-7 q-ml-sm">({{ incomes.length }} registros)</span>
                </div>
            </template>
            <template #body-cell-amount="p">
                <q-td :props="p" class="money text-weight-medium">{{ money(p.value) }}</q-td>
            </template>
            <template #body-cell-actions="p">
                <q-td :props="p">
                    <q-btn flat round dense size="sm" icon="edit" @click="openEdit(p.row)" />
                    <q-btn flat round dense size="sm" icon="delete" color="negative" @click="remove(p.row)" />
                </q-td>
            </template>
        </q-table>

        <q-dialog v-model="dialog">
            <q-card style="width: 420px; max-width: 95vw">
                <q-card-section class="text-h6">{{ form.id ? 'Editar ingreso' : 'Nuevo ingreso' }}</q-card-section>
                <q-form @submit.prevent="save">
                    <q-card-section class="q-gutter-sm">
                        <q-input
                            v-model.number="form.amount"
                            type="number"
                            step="0.01"
                            min="0"
                            label="Monto"
                            prefix="$"
                            outlined
                            autofocus
                            :error="!!form.errors.amount"
                            :error-message="form.errors.amount"
                        />
                        <q-input v-model="form.description" label="Descripción (ej. Nómina, Freelance)" outlined :error="!!form.errors.description" :error-message="form.errors.description" />
                        <q-input v-model="form.date" type="date" label="Fecha" stack-label outlined :error="!!form.errors.date" :error-message="form.errors.date" />
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
