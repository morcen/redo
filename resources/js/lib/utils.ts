import { clsx, type ClassValue } from 'clsx';
import { twMerge } from 'tailwind-merge';

export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}

/**
 * Safely access DOM element properties with null checks
 */
export function safeParentNode(element: Element | null): Element | null {
    try {
        return element?.parentNode as Element | null;
    } catch (error) {
        console.warn('Error accessing parentNode:', error);
        return null;
    }
}

/**
 * Safely perform DOM operations with error handling
 */
export function safeDOMOperation<T>(operation: () => T, fallback?: T): T | undefined {
    try {
        return operation();
    } catch (error) {
        console.warn('DOM operation failed:', error);
        return fallback;
    }
}
