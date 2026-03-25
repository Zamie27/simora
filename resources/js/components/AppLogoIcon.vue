<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

type Props = {
    className?: HTMLAttributes['class'];
};

defineProps<Props>();
</script>

<template>
    <img
        src="/images/simora_logo.png"
        alt="Simora Logo"
        :class="['object-contain', className]"
        v-bind="$attrs"
    />
</template>
