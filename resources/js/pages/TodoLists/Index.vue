<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import TodoListForm from '@/components/TodoListForm.vue';
import TodoListList from '@/components/TodoListList.vue';
import TodoFilters from '@/components/TodoFilters.vue';
import { type BreadcrumbItem, type TodoList, type TodoFilters as TodoFiltersType } from '@/types';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';

interface Props {
    lists: TodoList[];
    availableDates: string[];
    selectedDate: string;
    filters: TodoFiltersType;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Lists',
        href: '/todo-lists',
    },
];

const showForm = ref(false);
const editingList = ref<TodoList | null>(null);

const handleEdit = (list: TodoList) => {
    editingList.value = list;
    showForm.value = true;
};

const handleCloseForm = () => {
    showForm.value = false;
    editingList.value = null;
};
</script>

<template>
    <Head title="Todo Lists" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Todo Lists</h1>
                    <p class="text-muted-foreground">
                        Organize your todos with custom lists
                    </p>
                </div>
                <button
                    @click="showForm = true"
                    class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2"
                >
                    Add List
                </button>
            </div>

            <!-- Filters -->
            <TodoFilters
                :filters="props.filters"
                :available-dates="props.availableDates"
                :selected-date="props.selectedDate"
                :show-date-only="true"
            />

            <!-- List List -->
            <div class="flex-1">
                <TodoListList
                    :lists="props.lists"
                    @edit="handleEdit"
                />
            </div>

            <!-- List Form Modal -->
            <TodoListForm
                v-if="showForm"
                :list="editingList"
                :open="showForm"
                @close="handleCloseForm"
            />
        </div>
    </AppLayout>
</template>
