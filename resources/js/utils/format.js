const mxn = new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' });

export const money = (value) => mxn.format(Number(value || 0));

const pad = (n) => String(n).padStart(2, '0');

// Fecha local en formato YYYY-MM-DD (sin desfase por zona horaria)
export const toISODate = (date) => `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}`;

export const parseISODate = (iso) => {
    const [y, m, d] = iso.split('-').map(Number);
    return new Date(y, m - 1, d);
};

export const formatDate = (iso) =>
    parseISODate(iso).toLocaleDateString('es-MX', { day: '2-digit', month: 'short', year: 'numeric' });

export const monthLabel = (month, year) => {
    const label = new Date(year, month - 1, 1).toLocaleDateString('es-MX', { month: 'long', year: 'numeric' });
    return label.charAt(0).toUpperCase() + label.slice(1);
};

export const groupLabels = {
    essential: 'Esencial',
    non_essential: 'No esencial',
    savings: 'Ahorro',
};

export const groupColors = {
    essential: 'blue-8',
    non_essential: 'orange-8',
    savings: 'teal-7',
};

// Rangos rápidos para filtros de tablas
export const ranges = {
    week() {
        const now = new Date();
        const day = (now.getDay() + 6) % 7; // lunes = 0
        const from = new Date(now.getFullYear(), now.getMonth(), now.getDate() - day);
        const to = new Date(from.getFullYear(), from.getMonth(), from.getDate() + 6);
        return { from: toISODate(from), to: toISODate(to) };
    },
    month(offset = 0) {
        const now = new Date();
        const from = new Date(now.getFullYear(), now.getMonth() + offset, 1);
        const to = new Date(now.getFullYear(), now.getMonth() + offset + 1, 0);
        return { from: toISODate(from), to: toISODate(to) };
    },
    year() {
        const y = new Date().getFullYear();
        return { from: `${y}-01-01`, to: `${y}-12-31` };
    },
};
