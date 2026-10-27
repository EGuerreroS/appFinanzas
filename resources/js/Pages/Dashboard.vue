<script setup>
import { computed, ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { Doughnut, Bar } from 'vue-chartjs';
import AppLayout from '@/Layouts/AppLayout.vue';
import MonthPicker from '@/Components/MonthPicker.vue';
import { moneyTooltip } from '@/Components/charts';
import { money } from '@/utils/format';

const props = defineProps({
    summary: Object,
    byCategory: Array,
    byGroup: Array,
    budgets: Array,
    month: Number,
    year: Number,
    presets: Array,
    presetId: Number,
});

const changeMonth = ({ month, year }) => router.get('/', { month, year }, { preserveScroll: true });

const stats = computed(() => [
    { label: 'Ingresos del mes', value: props.summary.income_month, icon: 'trending_up', color: 'positive' },
    { label: 'Gastos del mes', value: props.summary.expense_month, icon: 'trending_down', color: 'negative' },
    { label: 'Balance del mes', value: props.summary.balance_month, icon: 'account_balance', color: props.summary.balance_month >= 0 ? 'teal-8' : 'negative' },
    { label: 'Balance general', value: props.summary.balance_total, icon: 'account_balance_wallet', color: props.summary.balance_total >= 0 ? 'primary' : 'negative' },
]);

// Gráfica de gastos por categoría
const doughnutData = computed(() => ({
    labels: props.byCategory.map((c) => c.name),
    datasets: [{
        data: props.byCategory.map((c) => c.total),
        backgroundColor: props.byCategory.map((c) => c.color),
    }],
}));
const doughnutOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { position: 'right' }, tooltip: moneyTooltip },
};

// Gráfica de presupuesto vs gastado por categoría
const barData = computed(() => ({
    labels: props.budgets.map((b) => b.category.name),
    datasets: [
        { label: 'Presupuesto', data: props.budgets.map((b) => b.amount), backgroundColor: '#B2DFDB' },
        { label: 'Gastado', data: props.budgets.map((b) => b.spent), backgroundColor: props.budgets.map((b) => (b.over ? '#E53935' : '#00897B')) },
    ],
}));
const barOptions = {
    indexAxis: 'y',
    responsive: true,
    maintainAspectRatio: false,
    plugins: { tooltip: moneyTooltip },
    scales: { x: { ticks: { callback: (v) => money(v) } } },
};

// Metodología de ahorro (50/30/20, etc.)
const selectedPresetId = ref(props.presetId);
const preset = computed(() => props.presets.find((p) => p.id === selectedPresetId.value));
const presetOptions = computed(() => props.presets.map((p) => ({ label: p.name, value: p.id })));

const selectPreset = (id) => router.put('/saving-presets/select', { saving_preset_id: id }, { preserveScroll: true });

const pctKey = { essential: 'essential_pct', non_essential: 'non_essential_pct', savings: 'savings_pct' };
const groupRows = computed(() => props.byGroup.map((g) => {
    const targetPct = preset.value ? preset.value[pctKey[g.group]] : 0;
    const target = (props.summary.income_month * targetPct) / 100;
    const actualPct = props.summary.income_month > 0 ? (g.total / props.summary.income_month) * 100 : 0;
    // En "ahorro" lo deseable es llegar a la meta; en los gastos lo deseable es no pasarse
    const ok = g.group === 'savings' ? g.total >= target : g.total <= target;
    return { ...g, targetPct, target, actualPct, ok, progress: target > 0 ? Math.min(g.total / target, 1) : 0 };
}));

const customDialog = ref(false);
const customForm = useForm({ essential_pct: 0, non_essential_pct: 0, savings_pct: 0 });
const openCustom = () => {
    customForm.essential_pct = preset.value.essential_pct;
    customForm.non_essential_pct = preset.value.non_essential_pct;
    customForm.savings_pct = preset.value.savings_pct;
    customForm.clearErrors();
    customDialog.value = true;
};
const customTotal = computed(() => Number(customForm.essential_pct) + Number(customForm.non_essential_pct) + Number(customForm.savings_pct));
const saveCustom = () => customForm.put(`/saving-presets/${preset.value.id}`, {
    preserveScroll: true,
    onSuccess: () => (customDialog.value = false),
});
</script>

<template>
    <Head title="Dashboard" />
    <AppLayout>
        <div class="row items-center justify-between q-mb-md">
            <h1 class="page-title">Dashboard</h1>
            <MonthPicker :month="month" :year="year" @change="changeMonth" />
        </div>

        <div class="row q-col-gutter-md q-mb-md">
            <div v-for="s in stats" :key="s.label" class="col-6 col-md-3">
                <q-card flat bordered>
                    <q-card-section class="row items-center no-wrap">
                        <q-avatar :icon="s.icon" :text-color="s.color" class="gt-xs" />
                        <div class="q-ml-sm">
                            <div class="text-caption text-grey-7">{{ s.label }}</div>
                            <div class="text-h6 money" :class="`text-${s.color}`">{{ money(s.value) }}</div>
                        </div>
                    </q-card-section>
                </q-card>
            </div>
        </div>

        <div class="row q-col-gutter-md q-mb-md">
            <div class="col-12 col-md-6">
                <q-card flat bordered class="full-height">
                    <q-card-section class="text-subtitle1 text-weight-medium">Gastos por categoría</q-card-section>
                    <q-card-section>
                        <div v-if="byCategory.length" style="height: 280px">
                            <Doughnut :data="doughnutData" :options="doughnutOptions" />
                        </div>
                        <div v-else class="text-grey-6 text-center q-pa-xl">Sin gastos este mes</div>
                    </q-card-section>
                </q-card>
            </div>
            <div class="col-12 col-md-6">
                <q-card flat bordered class="full-height">
                    <q-card-section class="text-subtitle1 text-weight-medium">Presupuesto vs gastado</q-card-section>
                    <q-card-section>
                        <div v-if="budgets.length" :style="{ height: Math.max(280, budgets.length * 40) + 'px' }">
                            <Bar :data="barData" :options="barOptions" />
                        </div>
                        <div v-else class="text-grey-6 text-center q-pa-xl">
                            Sin presupuestos este mes.
                            <q-btn flat dense color="teal-8" label="Crear presupuestos" @click="router.visit(`/budgets?month=${month}&year=${year}`)" />
                        </div>
                    </q-card-section>
                </q-card>
            </div>
        </div>

        <div class="row q-col-gutter-md">
            <div class="col-12 col-md-7">
                <q-card flat bordered>
                    <q-card-section class="text-subtitle1 text-weight-medium">Balance por presupuesto</q-card-section>
                    <q-list separator>
                        <q-item v-for="b in budgets" :key="b.id">
                            <q-item-section>
                                <q-item-label class="row items-center justify-between">
                                    <span><span class="color-dot" :style="{ background: b.category.color }" />{{ b.category.name }}</span>
                                    <span class="money text-caption">{{ money(b.spent) }} / {{ money(b.amount) }}</span>
                                </q-item-label>
                                <q-linear-progress
                                    class="q-my-xs"
                                    rounded
                                    size="10px"
                                    :value="b.amount > 0 ? Math.min(b.spent / b.amount, 1) : 1"
                                    :color="b.over ? 'negative' : b.percent >= 80 ? 'warning' : 'teal-7'"
                                />
                                <q-item-label caption :class="b.over ? 'text-negative text-weight-medium' : ''">
                                    <template v-if="b.over"><q-icon name="warning" /> Excedido por {{ money(-b.remaining) }}</template>
                                    <template v-else>Restante {{ money(b.remaining) }}</template>
                                </q-item-label>
                            </q-item-section>
                        </q-item>
                        <q-item v-if="!budgets.length">
                            <q-item-section class="text-grey-6">No hay presupuestos para este mes.</q-item-section>
                        </q-item>
                    </q-list>
                </q-card>
            </div>

            <div class="col-12 col-md-5">
                <q-card flat bordered>
                    <q-card-section class="row items-center justify-between q-gutter-sm">
                        <div class="text-subtitle1 text-weight-medium">Metodología de ahorro</div>
                        <div class="row items-center no-wrap">
                            <q-select
                                v-model="selectedPresetId"
                                :options="presetOptions"
                                emit-value
                                map-options
                                dense
                                outlined
                                style="min-width: 150px"
                                @update:model-value="selectPreset"
                            />
                            <q-btn v-if="preset?.is_custom" flat round dense icon="edit" @click="openCustom" />
                        </div>
                    </q-card-section>
                    <q-card-section v-if="summary.income_month <= 0" class="text-grey-7">
                        Registra ingresos este mes para comparar contra la metodología.
                    </q-card-section>
                    <q-list v-else separator>
                        <q-item v-for="g in groupRows" :key="g.group">
                            <q-item-section>
                                <q-item-label class="row justify-between">
                                    <span class="text-weight-medium">{{ g.label }}</span>
                                    <span class="text-caption">{{ g.actualPct.toFixed(1) }}% de {{ g.targetPct }}% meta</span>
                                </q-item-label>
                                <q-linear-progress class="q-my-xs" rounded size="8px" :value="g.progress" :color="g.ok ? 'teal-7' : 'negative'" />
                                <q-item-label caption class="money">{{ money(g.total) }} de {{ money(g.target) }}</q-item-label>
                            </q-item-section>
                        </q-item>
                    </q-list>
                </q-card>
            </div>
        </div>

        <q-dialog v-model="customDialog">
            <q-card style="width: 360px">
                <q-card-section class="text-h6">Preset personalizado</q-card-section>
                <q-card-section class="q-gutter-sm">
                    <q-input v-model.number="customForm.essential_pct" type="number" label="Esencial %" outlined dense :error="!!customForm.errors.essential_pct" :error-message="customForm.errors.essential_pct" />
                    <q-input v-model.number="customForm.non_essential_pct" type="number" label="No esencial %" outlined dense />
                    <q-input v-model.number="customForm.savings_pct" type="number" label="Ahorro %" outlined dense />
                    <div :class="customTotal === 100 ? 'text-positive' : 'text-negative'">Total: {{ customTotal }}%</div>
                </q-card-section>
                <q-card-actions align="right">
                    <q-btn flat label="Cancelar" v-close-popup />
                    <q-btn unelevated color="teal-8" label="Guardar" :disable="customTotal !== 100" :loading="customForm.processing" @click="saveCustom" />
                </q-card-actions>
            </q-card>
        </q-dialog>
    </AppLayout>
</template>
