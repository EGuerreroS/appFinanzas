<script setup>
import { computed, ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { useQuasar } from 'quasar';
import AppLayout from '@/Layouts/AppLayout.vue';
import MonthPicker from '@/Components/MonthPicker.vue';
import { money, groupLabels } from '@/utils/format';

const props = defineProps({
    budgets: Array,
    categories: Array,
    month: Number,
    year: Number,
});

const $q = useQuasar();

const changeMonth = ({ month, year }) => router.get('/budgets', { month, year }, { preserveScroll: true });
const copyPrevious = () => router.post(`/budgets/copy-previous?month=${props.month}&year=${props.year}`, {}, { preserveScroll: true });

const totals = computed(() => ({
    amount: props.budgets.reduce((s, b) => s + b.amount, 0),
    spent: props.budgets.reduce((s, b) => s + b.spent, 0),
}));

const columns = [
    { name: 'category', label: 'Categoría', field: (row) => row.category.name, align: 'left', sortable: true },
    { name: 'amount', label: 'Presupuesto', field: 'amount', align: 'right', sortable: true },
    { name: 'spent', label: 'Gastado', field: 'spent', align: 'right', sortable: true },
    { name: 'remaining', label: 'Restante', field: 'remaining', align: 'right', sortable: true },
    { name: 'progress', label: 'Avance', field: 'percent', align: 'left', style: 'min-width: 140px' },
    { name: 'actions', label: '', field: 'id', align: 'right' },
];

// Solo se ofrecen categorías que aún no tienen presupuesto este mes
const availableCategories = computed(() => {
    const used = new Set(props.budgets.map((b) => b.category_id));
    return props.categories
        .filter((c) => !used.has(c.id) || c.id === form.category_id)
        .map((c) => ({ label: c.name, value: c.id, color: c.color, group: c.group }));
});

const dialog = ref(false);
const form = useForm({ id: null, category_id: null, amount: null, month: props.month, year: props.year });

const openCreate = () => {
    form.reset();
    form.clearErrors();
    Object.assign(form, { month: props.month, year: props.year });
    dialog.value = true;
};
const openEdit = (row) => {
    form.clearErrors();
    Object.assign(form, { id: row.id, category_id: row.category_id, amount: row.amount, month: row.month, year: row.year });
    dialog.value = true;
};
const save = () => {
    const options = { preserveScroll: true, onSuccess: () => (dialog.value = false) };
    form.id ? form.put(`/budgets/${form.id}`, options) : form.post('/budgets', options);
};
const remove = (row) => {
    $q.dialog({
        title: 'Eliminar presupuesto',
        message: `¿Eliminar el presupuesto de "${row.category.name}" para este mes?`,
        cancel: true,
        ok: { label: 'Eliminar', color: 'negative', unelevated: true },
    }).onOk(() => router.delete(`/budgets/${row.id}`, { preserveScroll: true }));
};
</script>

<template>
    <Head title="Presupuestos" />
    <AppLayout>
        <div class="row items-center justify-between q-mb-md q-gutter-sm">
            <h1 class="page-title">Presupuestos</h1>
            <MonthPicker :month="month" :year="year" @change="changeMonth" />
            <div class="q-gutter-sm">
                <q-btn outline color="teal-8" icon="content_copy" label="Copiar mes anterior" @click="copyPrevious" />
                <q-btn unelevated color="teal-8" icon="add" label="Nuevo" :disable="availableCategories.length === 0" @click="openCreate" />
            </div>
        </div>

        <div class="row q-col-gutter-md q-mb-md">
            <div class="col-4">
                <q-card flat bordered><q-card-section>
                    <div class="text-caption text-grey-7">Presupuestado</div>
                    <div class="text-h6 money">{{ money(totals.amount) }}</div>
                </q-card-section></q-card>
            </div>
            <div class="col-4">
                <q-card flat bordered><q-card-section>
                    <div class="text-caption text-grey-7">Gastado</div>
                    <div class="text-h6 money text-negative">{{ money(totals.spent) }}</div>
                </q-card-section></q-card>
            </div>
            <div class="col-4">
                <q-card flat bordered><q-card-section>
                    <div class="text-caption text-grey-7">Restante</div>
                    <div class="text-h6 money" :class="totals.amount - totals.spent >= 0 ? 'text-teal-8' : 'text-negative'">
                        {{ money(totals.amount - totals.spent) }}
                    </div>
                </q-card-section></q-card>
            </div>
        </div>

        <q-table
            flat
            bordered
            :rows="budgets"
            :columns="columns"
            row-key="id"
            :pagination="{ rowsPerPage: 0 }"
            hide-pagination
            no-data-label="No hay presupuestos este mes. Crea uno o copia los del mes anterior."
        >
            <template #body-cell-category="p">
                <q-td :props="p">
                    <span class="color-dot" :style="{ background: p.row.category.color }" />{{ p.value }}
                    <div class="text-caption text-grey-6">{{ groupLabels[p.row.category.group] }}</div>
                </q-td>
            </template>
            <template #body-cell-amount="p"><q-td :props="p" class="money">{{ money(p.value) }}</q-td></template>
            <template #body-cell-spent="p"><q-td :props="p" class="money">{{ money(p.value) }}</q-td></template>
            <template #body-cell-remaining="p">
                <q-td :props="p" class="money text-weight-medium" :class="p.row.over ? 'text-negative' : 'text-teal-8'">
                    <q-icon v-if="p.row.over" name="warning" class="q-mr-xs" />{{ money(p.value) }}
                </q-td>
            </template>
            <template #body-cell-progress="p">
                <q-td :props="p">
                    <q-linear-progress
                        rounded
                        size="10px"
                        :value="p.row.amount > 0 ? Math.min(p.row.spent / p.row.amount, 1) : 1"
                        :color="p.row.over ? 'negative' : p.row.percent >= 80 ? 'warning' : 'teal-7'"
                    />
                    <div class="text-caption text-grey-7">{{ p.row.percent ?? '—' }}%</div>
                </q-td>
            </template>
            <template #body-cell-actions="p">
                <q-td :props="p">
                    <q-btn flat round dense size="sm" icon="edit" @click="openEdit(p.row)" />
                    <q-btn flat round dense size="sm" icon="delete" color="negative" @click="remove(p.row)" />
                </q-td>
            </template>
        </q-table>

        <q-dialog v-model="dialog">
            <q-card style="width: 400px; max-width: 95vw">
                <q-card-section class="text-h6">{{ form.id ? 'Editar presupuesto' : 'Nuevo presupuesto' }}</q-card-section>
                <q-form @submit.prevent="save">
                    <q-card-section class="q-gutter-sm">
                        <q-select
                            v-model="form.category_id"
                            :options="availableCategories"
                            label="Categoría"
                            emit-value
                            map-options
                            outlined
                            :disable="!!form.id"
                            :error="!!form.errors.category_id"
                            :error-message="form.errors.category_id"
                        />
                        <q-input
                            v-model.number="form.amount"
                            type="number"
                            step="0.01"
                            min="0"
                            label="Monto mensual"
                            prefix="$"
                            outlined
                            autofocus
                            :error="!!form.errors.amount"
                            :error-message="form.errors.amount"
                        />
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
