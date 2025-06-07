<script setup lang="ts">
import TodoFilters from '@/components/TodoFilters.vue';
import TodoListForm from '@/components/TodoListForm.vue';
import TodoListList from '@/components/TodoListList.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type TodoFilters as TodoFiltersType, type TodoList } from '@/types';
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
                    <p class="text-muted-foreground">Organize your todos with custom lists</p>
                </div>
                <button
                    @click="showForm = true"
                    class="ring-offset-background focus-visible:ring-ring bg-primary text-primary-foreground hover:bg-primary/90 inline-flex h-10 items-center justify-center rounded-md px-4 py-2 text-sm font-medium whitespace-nowrap transition-colors focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:outline-none disabled:pointer-events-none disabled:opacity-50"
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
                <TodoListList :lists="props.lists" @edit="handleEdit" />
            </div>

            <!-- List Form Modal -->
            <TodoListForm v-if="showForm" :list="editingList" :open="showForm" @close="handleCloseForm" />
        </div>
    </AppLayout>
</template>
