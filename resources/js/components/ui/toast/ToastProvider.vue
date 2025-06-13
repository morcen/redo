<script setup lang="ts">
import { useToast } from '@/composables/useToast';
import { Teleport, Transition, TransitionGroup } from 'vue';
import Toast from './Toast.vue';

const { toasts, removeToast } = useToast();

const handleClose = (id: string) => {
    removeToast(id);
};
</script>

<template>
    <div>
        <!-- Slot for the main content -->
        <slot />

        <!-- Toast container -->
        <Teleport to="body">
            <div
                class="fixed top-4 right-4 z-50 flex flex-col gap-2 w-full max-w-sm pointer-events-none"
                aria-live="polite"
                aria-label="Notifications"
                role="region"
            >
                <TransitionGroup
                    name="toast"
                    tag="div"
                    class="flex flex-col gap-2"
                >
                    <div
                        v-for="toast in toasts"
                        :key="toast.id"
                        class="pointer-events-auto"
                    >
                        <Toast
                            :toast="toast"
                            @close="handleClose"
                        />
                    </div>
                </TransitionGroup>
            </div>
        </Teleport>
    </div>
</template>

<style scoped>
/* Toast transition animations */
.toast-enter-active {
    transition: all 0.3s ease-out;
}

.toast-leave-active {
    transition: all 0.3s ease-in;
}

.toast-enter-from {
    opacity: 0;
    transform: translateX(100%);
}

.toast-leave-to {
    opacity: 0;
    transform: translateX(100%);
}

.toast-move {
    transition: transform 0.3s ease;
}
</style>
