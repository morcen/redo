<script setup lang="ts">
import { useToast } from '@/composables/useToast';
import { cn } from '@/lib/utils';
import type { Toast } from '@/types/index.d';
import { CheckCircle, X, XCircle, AlertTriangle, Info } from 'lucide-vue-next';
import { computed, onMounted, onUnmounted, ref } from 'vue';

interface Props {
    toast: Toast;
}

const props = defineProps<Props>();
const emit = defineEmits<{
    close: [id: string];
}>();

const iconMap = {
    success: CheckCircle,
    error: XCircle,
    warning: AlertTriangle,
    info: Info,
};

const styleMap = {
    success: 'bg-green-50 border-green-200 text-green-800 dark:bg-green-900 dark:border-green-700 dark:text-green-100',
    error: 'bg-red-50 border-red-200 text-red-800 dark:bg-red-900 dark:border-red-700 dark:text-red-100',
    warning: 'bg-orange-50 border-orange-200 text-orange-800 dark:bg-orange-900 dark:border-orange-700 dark:text-orange-100',
    info: 'bg-blue-50 border-blue-200 text-blue-800 dark:bg-blue-900 dark:border-blue-700 dark:text-blue-100',
};

const iconStyleMap = {
    success: 'text-green-500 dark:text-green-400',
    error: 'text-red-500 dark:text-red-400',
    warning: 'text-orange-500 dark:text-orange-400',
    info: 'text-blue-500 dark:text-blue-400',
};

const Icon = computed(() => iconMap[props.toast.type]);
const toastClasses = computed(() => styleMap[props.toast.type]);
const iconClasses = computed(() => iconStyleMap[props.toast.type]);

const toastRef = ref<HTMLElement>();
const { pauseToast, resumeToast } = useToast();

const handleClose = () => {
    emit('close', props.toast.id);
};

const handleMouseEnter = () => {
    pauseToast(props.toast.id);
};

const handleMouseLeave = () => {
    if (props.toast.duration && props.toast.duration > 0) {
        resumeToast(props.toast.id, 1000); // Resume with 1 second remaining
    }
};

const handleKeydown = (event: KeyboardEvent) => {
    if (event.key === 'Escape') {
        handleClose();
    }
};

onMounted(() => {
    // Focus the toast for accessibility
    toastRef.value?.focus();
    document.addEventListener('keydown', handleKeydown);
});

onUnmounted(() => {
    document.removeEventListener('keydown', handleKeydown);
});
</script>

<template>
    <div
        ref="toastRef"
        :class="cn(
            'relative flex w-full items-start gap-3 rounded-lg border p-4 shadow-lg transition-all duration-300 ease-in-out',
            'animate-in slide-in-from-right-full data-[state=closed]:animate-out data-[state=closed]:slide-out-to-right-full',
            'focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500',
            'hover:shadow-xl cursor-pointer',
            toastClasses
        )"
        role="alert"
        tabindex="0"
        aria-live="polite"
        @mouseenter="handleMouseEnter"
        @mouseleave="handleMouseLeave"
    >
        <!-- Icon -->
        <div class="flex-shrink-0">
            <component :is="Icon" :class="cn('h-5 w-5', iconClasses)" />
        </div>

        <!-- Content -->
        <div class="flex-1 min-w-0">
            <div class="font-medium text-sm">
                {{ toast.title }}
            </div>
            <div v-if="toast.description" class="mt-1 text-sm opacity-90">
                {{ toast.description }}
            </div>
        </div>

        <!-- Close button -->
        <button
            @click="handleClose"
            class="flex-shrink-0 rounded-md p-1 transition-colors hover:bg-black/10 dark:hover:bg-white/10"
            aria-label="Close notification"
        >
            <X class="h-4 w-4" />
        </button>
    </div>
</template>
