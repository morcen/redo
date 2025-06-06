<script setup lang="ts">
import { computed } from 'vue';
import { cn } from '@/lib/utils';

interface Props {
    value?: number;
    max?: number;
    class?: string;
    showPercentage?: boolean;
    size?: 'sm' | 'md' | 'lg';
}

const props = withDefaults(defineProps<Props>(), {
    value: 0,
    max: 100,
    showPercentage: true,
    size: 'md',
});

const percentage = computed(() => {
    if (props.max === 0) return 0;
    return Math.min(Math.max((props.value / props.max) * 100, 0), 100);
});

const sizeClasses = {
    sm: 'h-1',
    md: 'h-2',
    lg: 'h-3',
};

const getProgressColor = (percentage: number) => {
    if (percentage === 100) return 'bg-green-500';
    if (percentage >= 75) return 'bg-blue-500';
    if (percentage >= 50) return 'bg-yellow-500';
    if (percentage >= 25) return 'bg-orange-500';
    return 'bg-red-500';
};
</script>

<template>
    <div class="w-full">
        <div
            :class="cn(
                'relative w-full overflow-hidden rounded-full bg-muted',
                sizeClasses[size],
                props.class
            )"
        >
            <div
                :class="cn(
                    'h-full transition-all duration-300 ease-in-out',
                    getProgressColor(percentage)
                )"
                :style="{ width: `${percentage}%` }"
            />
        </div>
        <div v-if="showPercentage" class="mt-1 text-xs text-muted-foreground">
            {{ Math.round(percentage) }}% complete
        </div>
    </div>
</template>
