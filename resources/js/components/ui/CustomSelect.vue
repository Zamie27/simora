<script setup lang="ts">
import { ChevronDown } from 'lucide-vue-next';
import { computed, onMounted, onUnmounted, ref } from 'vue';

interface Option {
    value: string;
    label: string;
}

const props = defineProps<{
    options: Option[];
    placeholder?: string;
}>();

const model = defineModel<string>({ default: '' });

const isOpen = ref(false);
const dropdownRef = ref<HTMLElement>();

const selectedLabel = computed(() => {
    const found = props.options.find(o => o.value === model.value);
    return found ? found.label : '';
});

const select = (val: string) => {
    model.value = val;
    isOpen.value = false;
};

const handleClickOutside = (e: MouseEvent) => {
    if (dropdownRef.value && !dropdownRef.value.contains(e.target as Node)) {
        isOpen.value = false;
    }
};

onMounted(() => document.addEventListener('click', handleClickOutside));
onUnmounted(() => document.removeEventListener('click', handleClickOutside));
</script>

<template>
    <div ref="dropdownRef" class="relative">
        <button
            type="button"
            @click="isOpen = !isOpen"
            class="h-14 w-full flex items-center justify-between px-5 rounded-2xl text-sm font-bold transition-all duration-200 border focus:outline-none"
            :class="[
                isOpen
                    ? 'border-accent ring-2 ring-accent/20 bg-muted/40'
                    : 'border-border bg-muted/30 hover:border-accent/50',
                selectedLabel ? 'text-foreground' : 'text-muted-foreground'
            ]"
        >
            <span>{{ selectedLabel || placeholder || 'Pilih...' }}</span>
            <ChevronDown
                class="w-4 h-4 text-muted-foreground transition-transform duration-200 flex-shrink-0"
                :class="{ 'rotate-180': isOpen }"
            />
        </button>

        <Transition
            enter-active-class="transition duration-150 ease-out"
            enter-from-class="opacity-0 translate-y-1 scale-95"
            enter-to-class="opacity-100 translate-y-0 scale-100"
            leave-active-class="transition duration-100 ease-in"
            leave-from-class="opacity-100 translate-y-0 scale-100"
            leave-to-class="opacity-0 translate-y-1 scale-95"
        >
            <div
                v-if="isOpen"
                class="absolute z-50 top-full mt-2 w-full rounded-xl border border-border bg-card shadow-xl shadow-black/20 overflow-hidden"
            >
                <div class="py-1">
                    <button
                        v-for="option in options"
                        :key="option.value"
                        type="button"
                        @click="select(option.value)"
                        class="w-full text-left px-5 py-3.5 text-sm font-semibold transition-colors duration-150 flex items-center justify-between"
                        :class="
                            model === option.value
                                ? 'bg-accent text-white'
                                : 'text-foreground hover:bg-accent/10 hover:text-accent'
                        "
                    >
                        <span>{{ option.label }}</span>
                        <svg v-if="model === option.value" class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
            </div>
        </Transition>
    </div>
</template>
