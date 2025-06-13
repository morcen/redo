import type { Toast, ToastState } from '@/types/index.d';
import { reactive } from 'vue';

// Global toast state
const state = reactive<ToastState>({
    toasts: [],
});

// Generate unique ID for toasts
const generateId = (): string => {
    return Math.random().toString(36).substring(2, 9);
};

// Store timeout IDs for each toast
const timeouts = new Map<string, NodeJS.Timeout>();

// Maximum number of toasts to show at once
const MAX_TOASTS = 3;
const DEFAULT_DURATION = 5000;

export const useToast = () => {
    const addToast = (toast: Omit<Toast, 'id'>): string => {
        const id = generateId();
        const newToast: Toast = {
            id,
            duration: 5000, // Default 5 seconds
            ...toast,
        };

        state.toasts.push(newToast);

        // Remove oldest toast if we exceed the maximum
        if (state.toasts.length > MAX_TOASTS) {
            const oldestToast = state.toasts[0];
            removeToast(oldestToast.id);
        }

        // Auto-remove toast after duration
        if (newToast.duration && newToast.duration > 0) {
            const timeoutId = setTimeout(() => {
                removeToast(id);
            }, newToast.duration);
            timeouts.set(id, timeoutId);
        }

        return id;
    };

    const removeToast = (id: string): void => {
        const index = state.toasts.findIndex((toast) => toast.id === id);
        if (index > -1) {
            state.toasts.splice(index, 1);
        }

        // Clear the timeout if it exists
        const timeoutId = timeouts.get(id);
        if (timeoutId) {
            clearTimeout(timeoutId);
            timeouts.delete(id);
        }
    };

    const pauseToast = (id: string): void => {
        const timeoutId = timeouts.get(id);
        if (timeoutId) {
            clearTimeout(timeoutId);
            timeouts.delete(id);
        }
    };

    const resumeToast = (id: string, duration: number = 5000): void => {
        const timeoutId = setTimeout(() => {
            removeToast(id);
        }, duration);
        timeouts.set(id, timeoutId);
    };

    const clearAllToasts = (): void => {
        // Clear all timeouts
        timeouts.forEach((timeoutId) => clearTimeout(timeoutId));
        timeouts.clear();

        state.toasts.splice(0);
    };

    // Convenience methods for different toast types
    const success = (title: string, description?: string, duration: number = DEFAULT_DURATION): string => {
        return addToast({ type: 'success', title, description, duration });
    };

    const error = (title: string, description?: string, duration: number = DEFAULT_DURATION): string => {
        return addToast({ type: 'error', title, description, duration });
    };

    const warning = (title: string, description?: string, duration: number = DEFAULT_DURATION): string => {
        return addToast({ type: 'warning', title, description, duration });
    };

    const info = (title: string, description?: string, duration: number = DEFAULT_DURATION): string => {
        return addToast({ type: 'info', title, description, duration });
    };

    // Utility functions for common todo operations
    const todoCompleted = (todoTitle: string): string => {
        return success('Todo completed!', `"${todoTitle}" has been marked as complete.`);
    };

    const todoIncomplete = (todoTitle: string): string => {
        return warning('Todo marked incomplete', `"${todoTitle}" has been marked as incomplete.`);
    };

    const todoError = (todoTitle: string, action: string = 'update'): string => {
        return error(`Failed to ${action} todo`, `Could not ${action} "${todoTitle}". Please try again.`);
    };

    const bulkTodosCompleted = (count: number): string => {
        return success(
            `${count} todo${count === 1 ? '' : 's'} completed!`,
            `Successfully marked ${count} todo${count === 1 ? '' : 's'} as complete.`,
        );
    };

    const bulkTodosIncomplete = (count: number): string => {
        return warning(
            `${count} todo${count === 1 ? '' : 's'} marked incomplete`,
            `Successfully marked ${count} todo${count === 1 ? '' : 's'} as incomplete.`,
        );
    };

    return {
        toasts: state.toasts,
        addToast,
        removeToast,
        pauseToast,
        resumeToast,
        clearAllToasts,
        success,
        error,
        warning,
        info,
        // Todo-specific utilities
        todoCompleted,
        todoIncomplete,
        todoError,
        bulkTodosCompleted,
        bulkTodosIncomplete,
    };
};
