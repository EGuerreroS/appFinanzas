import {
    Chart as ChartJS,
    ArcElement,
    BarElement,
    CategoryScale,
    LinearScale,
    Legend,
    Tooltip,
} from 'chart.js';
import { money } from '@/utils/format';

ChartJS.register(ArcElement, BarElement, CategoryScale, LinearScale, Legend, Tooltip);

// Tooltips con formato de moneda MXN
export const moneyTooltip = {
    callbacks: {
        label: (ctx) => {
            const value = typeof ctx.parsed === 'number'
                ? ctx.parsed
                : ctx.chart.options.indexAxis === 'y' ? ctx.parsed.x : ctx.parsed.y;
            return `${ctx.dataset.label ? ctx.dataset.label + ': ' : ''}${money(value)}`;
        },
    },
};
