<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import TodoForm from '@/components/TodoForm.vue';
import TodoListComponent from '@/components/TodoList.vue';
import TodoFilters from '@/components/TodoFilters.vue';
import ErrorBoundary from '@/components/ErrorBoundary.vue';
import { type BreadcrumbItem, type Todo, type TodoList, type TodoFilters as TodoFiltersType } from '@/types';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';

interface Props {
    todos: Todo[];
    availableDates: string[];
    selectedDate: string;
    todoLists: TodoList[];
    filters: TodoFiltersType;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Todos',
        href: '/todos',
    },
];

const showForm = ref(false);
const editingTodo = ref<Todo | null>(null);

const handleEdit = (todo: Todo) => {
    editingTodo.value = todo;
    showForm.value = true;
};

const handleCloseForm = () => {
    showForm.value = false;
    editingTodo.value = null;
};
</script>

<template>
    <Head title="Todos" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">My Todos</h1>
                    <p class="text-muted-foreground">
                        Manage your tasks and stay organized
                    </p>
                </div>
                <button
                    @click="showForm = true"
                    class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2"
                >
                    Add Todo
                </button>
            </div>

            <!-- Filters -->
            <TodoFilters
                :filters="props.filters"
                :available-dates="props.availableDates"
                :selected-date="props.selectedDate"
            />

            <!-- Todo List -->
            <div class="flex-1">
                <TodoListComponent
                    :todos="props.todos"
                    @edit="handleEdit"
                />
            </div>

            <!-- Todo Form Modal -->
            <ErrorBoundary v-if="showForm" fallback="Error loading form. Please try again.">
                <TodoForm
                    :key="`todo-form-${editingTodo?.id || 'new'}-${Date.now()}`"
                    :todo="editingTodo"
                    :todo-lists="props.todoLists"
                    :open="showForm"
                    @close="handleCloseForm"
                />
            </ErrorBoundary>
        </div>
    </AppLayout>
</template>
