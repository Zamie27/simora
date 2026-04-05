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
    <div :class="['flex items-center', className]" v-bind="$attrs">
        <!-- Full Logo (md and up) -->
        <img
            src="/images/simora_logo.png"
            alt="Simora Logo"
            class="hidden h-full w-auto object-contain md:block"
        />
        <!-- Small Icon (below md) -->
        <img
            src="/images/simora_icon.png"
            alt="Simora Icon"
            class="block h-full w-auto object-contain md:hidden"
        />
    </div>
</template>
