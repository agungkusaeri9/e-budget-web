<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { computed } from 'vue';
import { cn } from '@/lib/utils';

const props = withDefaults(
    defineProps<{
        modelValue?: number | string | null;
        nullable?: boolean;
        showPrefix?: boolean;
        placeholder?: string;
        id?: string;
        disabled?: boolean;
        class?: HTMLAttributes['class'];
    }>(),
    {
        modelValue: null,
        nullable: false,
        showPrefix: true,
        placeholder: '0',
        disabled: false,
    },
);

const emit = defineEmits<{
    (e: 'update:modelValue', value: number | string | null): void;
}>();

const formatIndoNumber = (val: number | string | null | undefined): string => {
    if (val === null || val === undefined || val === '') {
        return '';
    }

    const digits = String(Math.floor(Number(val))).replace(/\D/g, '');
    if (!digits) {
        return '';
    }

    const numeric = Number(digits);
    if (Number.isNaN(numeric)) {
        return '';
    }

    if (numeric === 0 && props.nullable && val === '') {
        return '';
    }

    return new Intl.NumberFormat('id-ID', {
        maximumFractionDigits: 0,
    }).format(numeric);
};

const displayValue = computed(() => formatIndoNumber(props.modelValue));

const handleInput = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const rawDigits = target.value.replace(/\D/g, '');

    if (!rawDigits) {
        const emptyVal = props.nullable ? '' : 0;
        emit('update:modelValue', emptyVal);
        target.value = props.nullable ? '' : '0';
        return;
    }

    const numericValue = Number(rawDigits);
    emit('update:modelValue', numericValue);
    target.value = new Intl.NumberFormat('id-ID', {
        maximumFractionDigits: 0,
    }).format(numericValue);
};
</script>

<template>
    <div class="relative flex w-full items-center">
        <span
            v-if="showPrefix"
            class="pointer-events-none absolute left-3 text-xs font-medium text-muted-foreground select-none"
        >
            Rp
        </span>
        <input
            :id="id"
            type="text"
            inputmode="numeric"
            :value="displayValue"
            :placeholder="placeholder"
            :disabled="disabled"
            @input="handleInput"
            data-slot="input"
            :class="
                cn(
                    'file:text-foreground placeholder:text-muted-foreground selection:bg-primary selection:text-primary-foreground dark:bg-input/30 border-input flex h-9 w-full min-w-0 rounded-md border bg-transparent py-1 pr-3 text-base shadow-xs transition-[color,box-shadow] outline-none file:inline-flex file:h-7 file:border-0 file:bg-transparent file:text-sm file:font-medium disabled:pointer-events-none disabled:cursor-not-allowed disabled:opacity-50 md:text-sm',
                    'focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px]',
                    'aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive',
                    showPrefix ? 'pl-8' : 'pl-3',
                    props.class,
                )
            "
        />
    </div>
</template>

