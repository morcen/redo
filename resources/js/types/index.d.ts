import type { PageProps } from '@inertiajs/core';
import type { LucideIcon } from 'lucide-vue-next';
import type { Config } from 'ziggy-js';

export interface Auth {
    user: User;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavItem {
    title: string;
    href: string;
    icon?: LucideIcon;
    isActive?: boolean;
}

export interface SharedData extends PageProps {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    ziggy: Config & { location: string };
    sidebarOpen: boolean;
}

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
}

export interface Setting {
    id: number;
    user_id: number;
    timezone: string;
    date_format: string;
    time_format: string;
    email_notifications: boolean;
    browser_notifications: boolean;
    created_at: string;
    updated_at: string;
}

export interface Todo {
    id: number;
    user_id: number;
    todo_list_id: number;
    title: string;
    description?: string;
    completed_at: string | null; // timestamp or null
    priority: 'low' | 'medium' | 'high';
    due_date?: string;
    created_at: string;
    updated_at: string;
    // Computed properties for backward compatibility
    is_completed?: boolean;
}

export interface TodoList {
    id: number;
    name: string;
    description?: string;
    refresh_daily: boolean;
    created_at: string;
    updated_at: string;
    completion_percentage?: number;
    total_todos?: number;
    completed_todos?: number;
    // Date-filtered completion stats
    filtered_completion_percentage?: number;
    filtered_total_todos?: number;
    filtered_completed_todos?: number;
}

export interface TodoFilters {
    completed?: boolean;
    priority?: 'low' | 'medium' | 'high';
    search?: string;
    date?: string;
}

export interface Toast {
    id: string;
    type: 'success' | 'error' | 'warning' | 'info';
    title: string;
    description?: string;
    duration?: number;
}

export interface ToastState {
    toasts: Toast[];
}

export type BreadcrumbItemType = BreadcrumbItem;
