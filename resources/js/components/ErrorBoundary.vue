<script setup lang="ts">
import { ref, onErrorCaptured, provide } from 'vue';

interface Props {
    fallback?: string;
    showError?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    fallback: 'Something went wrong. Please try again.',
    showError: false,
});

const hasError = ref(false);
const errorMessage = ref('');

// Capture errors from child components
onErrorCaptured((error, instance, info) => {
    console.error('Error captured by ErrorBoundary:', error);
    console.error('Component instance:', instance);
    console.error('Error info:', info);

    // Handle specific parentNode errors
    if (error instanceof TypeError && error.message.includes('parentNode')) {
        console.warn('Caught parentNode error in ErrorBoundary');
        // Don't show error UI for parentNode errors, just log them
        return false; // Prevent the error from propagating
    }

    // Handle calendar date copy errors
    if (error instanceof TypeError && error.message.includes('copy is not a function')) {
        console.warn('Caught calendar date copy error in ErrorBoundary');
        // Don't show error UI for copy errors, just log them
        return false; // Prevent the error from propagating
    }

    // For other errors, show the error UI
    hasError.value = true;
    errorMessage.value = error.message || 'An unexpected error occurred';

    // Return false to prevent the error from propagating further
    return false;
});

// Provide a method for child components to reset the error state
const resetError = () => {
    hasError.value = false;
    errorMessage.value = '';
};

provide('resetError', resetError);
</script>

<template>
    <div>
        <div v-if="hasError" class="p-4 border border-red-200 rounded-md bg-red-50">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800">
                        {{ props.fallback }}
                    </h3>
                    <div v-if="props.showError && errorMessage" class="mt-2 text-sm text-red-700">
                        {{ errorMessage }}
                    </div>
                    <div class="mt-4">
                        <button
                            @click="resetError"
                            class="bg-red-100 px-2 py-1 text-sm font-medium text-red-800 rounded-md hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                        >
                            Try again
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <slot v-else />
    </div>
</template>
