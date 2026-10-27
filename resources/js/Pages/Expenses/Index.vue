<script setup>
import { computed, reactive, ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { useQuasar } from 'quasar';
import { Doughnut } from 'vue-chartjs';
import AppLayout from '@/Layouts/AppLayout.vue';
import { moneyTooltip } from '@/Components/charts';
import { money, formatDate, toISODate, ranges, groupLabels } from '@/utils/format';

const props = defineProps({
    expenses: Array,
    categories: Array,
    filters: Object,
});

const $q = useQuasar();

// ---- Filtros (se aplican en el servidor) ----
const filter = reactive({
    from: props.filters.from,
    to: props.filters.to,
    category_id: props.filters.category_id ? Number(props.filters.category_id) : null,
});
const search = ref('');

const applyFilters = () => router.get('/expenses', { ...filter }, { preserveState: true, preserveScroll: true, replace: true });
const setRange = (range) => {
    Object.assign(filter, range);
    applyFilters();
};

const categoryOptions = computed(() => props.categories.map((c) => ({ label: c.name, value: c.id, color: c.color, group: c.group })));

// ---- Tabla ----
const columns = [
    { name: 'date', label: 'Fecha', field: 'date', align: 'left', sortable: true, format: formatDate },
    { name: 'category', label: 'Categoría', field: (row) => row.category.name, align: 'left', sortable: true },
    { name: 'description', label: 'Descripción', field: 'description', align: 'left' },
    { name: 'amount', label: 'Monto', field: 'amount', align: 'right', sortable: true },
    { name: 'actions', label: '', field: 'id', align: 'right' },
];
const total = computed(() => props.expenses.reduce((sum, e) => sum + Number(e.amount), 0));

// ---- Gráfica por categoría del periodo filtrado ----
const byCategory = computed(() => {
    const map = new Map();
    for (const e of props.expenses) {
        const item = map.get(e.category_id) ?? { name: e.category.name, color: e.category.color, total: 0 };
        item.total += Number(e.amount);
        map.set(e.category_id, item);
    }
    return [...map.values()].sort((a, b) => b.total - a.total);
});
const chartData = computed(() => ({
    labels: byCategory.value.map((c) => c.name),
    datasets: [{ data: byCategory.value.map((c) => c.total), backgroundColor: byCategory.value.map((c) => c.color) }],
}));
const chartOptions = { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' }, tooltip: moneyTooltip } };

// ---- Alta / edición ----
const dialog = ref(false);
const form = useForm({ id: null, category_id: null, amount: null, description: '', date: toISODate(new Date()) });

const openCreate = () => {
    form.reset();
    form.clearErrors();
    form.date = toISODate(new Date());
    dialog.value = true;
};
const openEdit = (row) => {
    form.clearErrors();
    Object.assign(form, { id: row.id, category_id: row.category_id, amount: row.amount, description: row.description ?? '', date: row.date });
    dialog.value = true;
};
const save = () => {
    const options = { preserveScroll: true, onSuccess: () => (dialog.value = false) };
    form.id ? form.put(`/expenses/${form.id}`, options) : form.post('/expenses', options);
};
const remove = (row) => {
    $q.dialog({
        title: 'Eliminar gasto',
        message: `¿Eliminar "${row.description || row.category.name}" por ${money(row.amount)}?`,
        cancel: true,
        ok: { label: 'Eliminar', color: 'negative', unelevated: true },
    }).onOk(() => router.delete(`/expenses/${row.id}`, { preserveScroll: true }));
};
</script>

<template>
    <Head title="Gastos" />
    <AppLayout>
        <div class="row items-center justify-between q-mb-md">
            <h1 class="page-title">Gastos</h1>
            <q-btn unelevated color="teal-8" icon="add" label="Nuevo gasto" @click="openCreate" />
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
                <div class="col-12 col-md">
                    <q-select
                        v-model="filter.category_id"
                        :options="categoryOptions"
                        label="Categoría"
                        emit-value
                        map-options
                        clearable
                        dense
                        outlined
                        @update:model-value="applyFilters"
                    />
                </div>
            </q-card-section>
        </q-card>

        <div class="row q-col-gutter-md">
            <div class="col-12 col-md-8">
                <q-table
                    flat
                    bordered
                    :rows="expenses"
                    :columns="columns"
                    row-key="id"
                    :filter="search"
                    :pagination="{ rowsPerPage: 15, sortBy: 'date', descending: true }"
                    no-data-label="No hay gastos en este periodo"
                >
                    <template #top>
                        <div class="text-subtitle1">
                            Total: <span class="text-weight-bold text-negative money">{{ money(total) }}</span>
                            <span class="text-caption text-grey-7 q-ml-sm">({{ expenses.length }} registros)</span>
                        </div>
                        <q-space />
                        <q-input v-model="search" dense outlined placeholder="Buscar" debounce="200">
                            <template #append><q-icon name="search" /></template>
                        </q-input>
                    </template>
                    <template #body-cell-category="p">
                        <q-td :props="p">
                            <span class="color-dot" :style="{ background: p.row.category.color }" />{{ p.row.category.name }}
                        </q-td>
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
            </div>
            <div class="col-12 col-md-4">
                <q-card flat bordered>
                    <q-card-section class="text-subtitle1 text-weight-medium">Por categoría</q-card-section>
                    <q-card-section>
                        <div v-if="byCategory.length" style="height: 300px">
                            <Doughnut :data="chartData" :options="chartOptions" />
                        </div>
                        <div v-else class="text-grey-6 text-center q-pa-lg">Sin datos</div>
                    </q-card-section>
                </q-card>
            </div>
        </div>

        <q-dialog v-model="dialog">
            <q-card style="width: 420px; max-width: 95vw">
                <q-card-section class="text-h6">{{ form.id ? 'Editar gasto' : 'Nuevo gasto' }}</q-card-section>
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
                        <q-select
                            v-model="form.category_id"
                            :options="categoryOptions"
                            label="Categoría"
                            emit-value
                            map-options
                            outlined
                            :error="!!form.errors.category_id"
                            :error-message="form.errors.category_id"
                        >
                            <template #option="scope">
                                <q-item v-bind="scope.itemProps">
                                    <q-item-section avatar><span class="color-dot" :style="{ background: scope.opt.color }" /></q-item-section>
                                    <q-item-section>
                                        <q-item-label>{{ scope.opt.label }}</q-item-label>
                                        <q-item-label caption>{{ groupLabels[scope.opt.group] }}</q-item-label>
                                    </q-item-section>
                                </q-item>
                            </template>
                        </q-select>
                        <q-input v-model="form.description" label="Descripción" outlined :error="!!form.errors.description" :error-message="form.errors.description" />
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
