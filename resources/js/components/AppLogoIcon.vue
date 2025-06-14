<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { computed, onMounted, onUnmounted, ref } from 'vue';

defineOptions({
    inheritAttrs: false,
});

interface Props {
    className?: HTMLAttributes['class'];
}

defineProps<Props>();

const isDark = ref(false);

const updateTheme = () => {
    if (typeof window !== 'undefined') {
        isDark.value = document.documentElement.classList.contains('dark');
    }
};

const logoSrc = computed(() => {
    return isDark.value ? '/redo-dark.svg' : '/redo.svg';
});

onMounted(() => {
    updateTheme();

    // Watch for theme changes
    const observer = new MutationObserver(() => {
        updateTheme();
    });

    observer.observe(document.documentElement, {
        attributes: true,
        attributeFilter: ['class'],
    });

    // Store observer for cleanup
    (window as any).__themeObserver = observer;
});

onUnmounted(() => {
    if ((window as any).__themeObserver) {
        (window as any).__themeObserver.disconnect();
        delete (window as any).__themeObserver;
    }
});
</script>

<template>
    <img :src="logoSrc" alt="Re:do logo" :class="className" v-bind="$attrs" style="object-fit: contain" />
</template>
