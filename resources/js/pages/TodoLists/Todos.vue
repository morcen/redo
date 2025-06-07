<script setup lang="ts">
import ErrorBoundary from '@/components/ErrorBoundary.vue';
import TodoFilters from '@/components/TodoFilters.vue';
import TodoForm from '@/components/TodoForm.vue';
import TodoListComponent from '@/components/TodoList.vue';
import { Button } from '@/components/ui/button';
import { Progress } from '@/components/ui/progress';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type Todo, type TodoFilters as TodoFiltersType, type TodoList } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { ArrowLeft, RotateCcw } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface Props {
    list: TodoList;
    todos: Todo[];
    availableDates: string[];
    selectedDate: string;
    todoLists: TodoList[];
    filters: TodoFiltersType;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Lists',
        href: '/todo-lists',
    },
    {
        title: props.list.name,
        href: `/todo-lists/${props.list.id}/todos`,
    },
];

const showForm = ref(false);
const editingTodo = ref<Todo | null>(null);

// Completion percentage computed properties
const completionPercentage = computed(() => {
    // Use filtered values if available (for date-filtered views), otherwise use regular values
    return props.list.filtered_completion_percentage ?? props.list.completion_percentage ?? 0;
});

const totalTodos = computed(() => {
    // Use filtered values if available (for date-filtered views), otherwise use regular values
    return props.list.filtered_total_todos ?? props.list.total_todos ?? 0;
});

const completedTodos = computed(() => {
    // Use filtered values if available (for date-filtered views), otherwise use regular values
    return props.list.filtered_completed_todos ?? props.list.completed_todos ?? 0;
});

const handleEdit = (todo: Todo) => {
    editingTodo.value = todo;
    showForm.value = true;
};

const handleCloseForm = () => {
    showForm.value = false;
    editingTodo.value = null;
};

const goBackToLists = () => {
    router.get(route('todo-lists.index'));
};
</script>

<template>
    <Head :title="`${list.name} - Todos`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Button variant="ghost" size="sm" @click="goBackToLists" class="flex items-center gap-2">
                        <ArrowLeft class="h-4 w-4" />
                        Back to Lists
                    </Button>
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-2xl font-bold tracking-tight">{{ list.name }}</h1>
                            <div
                                v-if="list.refresh_daily"
                                class="flex items-center gap-1 rounded-full bg-blue-100 px-2 py-1 text-xs text-blue-700"
                                title="This list refreshes daily"
                            >
                                <RotateCcw class="h-3 w-3" />
                                <span>Daily</span>
                            </div>
                        </div>
                        <p class="text-muted-foreground">
                            {{ list.description || 'Manage todos in this list' }}
                        </p>

                        <!-- Completion Progress -->
                        <div v-if="totalTodos > 0" class="mt-4 space-y-2">
                            <div class="text-muted-foreground flex items-center justify-between text-sm">
                                <span>{{ completedTodos }} of {{ totalTodos }} completed</span>
                                <span class="font-medium">{{ Math.round(completionPercentage) }}%</span>
                            </div>
                            <Progress :value="completionPercentage" :max="100" size="md" :show-percentage="false" class="w-full max-w-md" />
                        </div>
                        <div v-else class="mt-3">
                            <span class="text-muted-foreground text-sm">No todos yet - create one to get started!</span>
                        </div>
                    </div>
                </div>
                <button
                    @click="showForm = true"
                    class="ring-offset-background focus-visible:ring-ring bg-primary text-primary-foreground hover:bg-primary/90 inline-flex h-10 items-center justify-center rounded-md px-4 py-2 text-sm font-medium whitespace-nowrap transition-colors focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:outline-none disabled:pointer-events-none disabled:opacity-50"
                >
                    Add Todo
                </button>
            </div>

            <!-- Filters -->
            <TodoFilters
                :filters="props.filters"
                :todo-lists="props.todoLists"
                :available-dates="props.availableDates"
                :selected-date="props.selectedDate"
            />

            <!-- Todo List -->
            <div class="flex-1">
                <TodoListComponent :todos="props.todos" @edit="handleEdit" />
            </div>

            <!-- Todo Form Modal -->
            <ErrorBoundary v-if="showForm" fallback="Error loading form. Please try again.">
                <TodoForm
                    :key="`todo-form-${editingTodo?.id || 'new'}-${Date.now()}`"
                    :todo="editingTodo"
                    :todo-lists="props.todoLists"
                    :default-list-id="props.list.id"
                    :open="showForm"
                    @close="handleCloseForm"
                />
            </ErrorBoundary>
        </div>
    </AppLayout>
</template>
