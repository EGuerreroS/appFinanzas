<script setup>
import { computed } from 'vue';
import { monthLabel } from '@/utils/format';

const props = defineProps({
    month: { type: Number, required: true },
    year: { type: Number, required: true },
});
const emit = defineEmits(['change']);

const label = computed(() => monthLabel(props.month, props.year));

const shift = (delta) => {
    const d = new Date(props.year, props.month - 1 + delta, 1);
    emit('change', { month: d.getMonth() + 1, year: d.getFullYear() });
};
</script>

<template>
    <div class="row items-center no-wrap">
        <q-btn flat round dense icon="chevron_left" @click="shift(-1)" />
        <div class="text-subtitle1 text-weight-medium q-px-sm" style="min-width: 150px; text-align: center">
            {{ label }}
        </div>
        <q-btn flat round dense icon="chevron_right" @click="shift(1)" />
    </div>
</template>
