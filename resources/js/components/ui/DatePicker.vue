<script setup lang="ts">
import { VueDatePicker } from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css';
import { onMounted, onUnmounted, ref, watch } from 'vue';

// model stores yyyy-MM-dd string (compatible with backend)
const model = defineModel<string>({ default: '' });

// Convert yyyy-MM-dd → Date (local timezone, no time offset)
const toDate = (val: string): Date | null => {
    if (!val) return null;
    const p = val.split('T')[0].split('-');
    if (p.length !== 3) return null;
    const d = new Date(Number(p[0]), Number(p[1]) - 1, Number(p[2]));
    return isNaN(d.getTime()) ? null : d;
};

// Convert Date → yyyy-MM-dd string
const fromDate = (d: Date): string => {
    const y = d.getFullYear();
    const m = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    return `${y}-${m}-${day}`;
};

// Internal Date value for VueDatePicker
const internalDate = ref<Date | null>(toDate(model.value));

// Sync internalDate → model (when user picks a date)
const onPick = (val: Date | null) => {
    internalDate.value = val;
    model.value = val ? fromDate(val) : '';
};

// Sync model → internalDate (when parent sets value programmatically)
watch(() => model.value, (newVal) => {
    const d = toDate(newVal);
    // Avoid infinite loop: only update if date actually changed
    if (!d && !internalDate.value) return;
    if (d && internalDate.value && fromDate(d) === fromDate(internalDate.value)) return;
    internalDate.value = d;
});

// Display format function: dd/MM/yyyy
const formatDisplay = (date: Date | Date[]): string => {
    const d = Array.isArray(date) ? date[0] : date;
    if (!d) return '';
    const day = String(d.getDate()).padStart(2, '0');
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const year = d.getFullYear();
    return `${day}/${month}/${year}`;
};

// Dark mode detection
const isDark = ref(false);
const updateDark = () => { isDark.value = document.documentElement.classList.contains('dark'); };
let observer: MutationObserver | null = null;
onMounted(() => {
    updateDark();
    observer = new MutationObserver(updateDark);
    observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
});
onUnmounted(() => observer?.disconnect());
</script>

<template>
    <VueDatePicker
        :model-value="internalDate"
        @update:model-value="onPick"
        :dark="isDark"
        :enableTimePicker="false"
        :format="formatDisplay"
        placeholder="dd/mm/yyyy"
        auto-apply
        :teleport="true"
        input-class-name="dp-custom-input"
    />
</template>

<style>
/* DARK theme */
.dp__theme_dark {
    --dp-background-color: #171c1c;
    --dp-text-color: #F4F7F7;
    --dp-hover-color: rgba(255, 97, 32, 0.15);
    --dp-hover-text-color: #F4F7F7;
    --dp-hover-icon-color: #FF6120;
    --dp-primary-color: #FF6120;
    --dp-primary-disabled-color: rgba(255, 97, 32, 0.4);
    --dp-primary-text-color: #ffffff;
    --dp-secondary-color: #262c2c;
    --dp-border-color: #262c2c;
    --dp-menu-border-color: #262c2c;
    --dp-border-color-hover: #FF6120;
    --dp-border-color-focus: #FF6120;
    --dp-disabled-color: #333;
    --dp-icon-color: #c8c8c8;
}

/* LIGHT theme */
.dp__theme_light {
    --dp-background-color: #ffffff;
    --dp-text-color: #1a1a1a;
    --dp-hover-color: rgba(255, 97, 32, 0.1);
    --dp-hover-text-color: #1a1a1a;
    --dp-hover-icon-color: #FF6120;
    --dp-primary-color: #FF6120;
    --dp-primary-disabled-color: rgba(255, 97, 32, 0.4);
    --dp-primary-text-color: #ffffff;
    --dp-secondary-color: #f5f5f5;
    --dp-border-color: #e2e8f0;
    --dp-menu-border-color: #e2e8f0;
    --dp-border-color-hover: #FF6120;
    --dp-border-color-focus: #FF6120;
    --dp-icon-color: #555;
}

.dp-custom-input {
    height: 2.5rem !important;
    border-radius: 0.375rem !important;
    border: 1px solid var(--dp-border-color) !important;
    background: transparent !important;
    color: inherit !important;
    font-family: inherit !important;
    font-size: 0.875rem !important;
    width: 100% !important;
    padding: 0 0.75rem !important;
}

.dp__main { font-family: 'Instrument Sans', sans-serif !important; }
.dp__menu { border-radius: 12px !important; overflow: hidden; }

/* Force hide ALL time-related UI (confirmed from v12 source) */
.dp--tp-wrap,
.dp--time-overlay-btn,
.dp__time_input,
.dp__time_picker_inline_container,
.dp__arrow_bottom_tp {
    display: none !important;
}
</style>
