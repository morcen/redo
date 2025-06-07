<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { DatePicker } from '@/components/ui/date-picker';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { type TodoFilters as TodoFiltersType, type TodoList } from '@/types';
import { router } from '@inertiajs/vue3';
import { Search, X } from 'lucide-vue-next';
import { ref, watch } from 'vue';

interface Props {
    filters: TodoFiltersType;
    todoLists?: TodoList[];
    availableDates?: string[];
    selectedDate?: string;
    showDateOnly?: boolean; // When true, only show date filter (for TodoLists index)
}

const props = defineProps<Props>();

const search = ref(props.filters?.search || '');
const completed = ref(props.filters?.completed?.toString() || 'all');
const priority = ref(props.filters?.priority || 'all');
const selectedDate = ref(props.filters?.date || props.selectedDate || new Date().toISOString().split('T')[0]);

const updateFilters = () => {
    const params: Record<string, any> = {};

    if (search.value) {
        params.search = search.value;
    }

    if (completed.value !== 'all') {
        params.completed = completed.value === 'true';
    }

    if (priority.value !== 'all') {
        params.priority = priority.value;
    }

    if (selectedDate.value) {
        params.date = selectedDate.value;
    }

    // Use current route to maintain context (todos.index, lists.todos, or todo-lists.index)
    const currentRoute = route().current();
    const routeParams = route().params;

    if (currentRoute === 'lists.todos') {
        router.get(route('lists.todos', routeParams.list), params, {
            preserveState: true,
            replace: true,
        });
    } else if (currentRoute === 'todo-lists.index') {
        router.get(route('todo-lists.index'), params, {
            preserveState: true,
            replace: true,
        });
    } else {
        router.get(route('todos.index'), params, {
            preserveState: true,
            replace: true,
        });
    }
};

const clearFilters = () => {
    search.value = '';
    completed.value = 'all';
    priority.value = 'all';
    selectedDate.value = new Date().toISOString().split('T')[0];

    // Use current route to maintain context
    const currentRoute = route().current();
    const routeParams = route().params;

    if (currentRoute === 'lists.todos') {
        router.get(
            route('lists.todos', routeParams.list),
            {},
            {
                preserveState: true,
                replace: true,
            },
        );
    } else if (currentRoute === 'todo-lists.index') {
        router.get(
            route('todo-lists.index'),
            {},
            {
                preserveState: true,
                replace: true,
            },
        );
    } else {
        router.get(
            route('todos.index'),
            {},
            {
                preserveState: true,
                replace: true,
            },
        );
    }
};

const hasActiveFilters = () => {
    const today = new Date().toISOString().split('T')[0];
    if (props.showDateOnly) {
        return selectedDate.value !== today;
    }
    return search.value || completed.value !== 'all' || priority.value !== 'all' || selectedDate.value !== today;
};

// Debounce search input
let searchTimeout: NodeJS.Timeout;
watch(search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(updateFilters, 300);
});

watch([completed, priority, selectedDate], updateFilters);
</script>

<template>
    <div class="bg-muted/50 flex flex-col gap-4 rounded-lg p-4 sm:flex-row">
        <!-- Search -->
        <div v-if="!showDateOnly" class="relative flex-1">
            <Search class="text-muted-foreground absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 transform" />
            <Input v-model="search" placeholder="Search todos..." class="pl-10" />
        </div>

        <!-- Status Filter -->
        <div v-if="!showDateOnly" class="w-full sm:w-40">
            <Select v-model="completed">
                <SelectTrigger>
                    <SelectValue placeholder="Status" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem value="all">All</SelectItem>
                    <SelectItem value="false">Pending</SelectItem>
                    <SelectItem value="true">Completed</SelectItem>
                </SelectContent>
            </Select>
        </div>

        <!-- Priority Filter -->
        <div v-if="!showDateOnly" class="w-full sm:w-40">
            <Select v-model="priority">
                <SelectTrigger>
                    <SelectValue placeholder="Priority" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem value="all">All Priorities</SelectItem>
                    <SelectItem value="high">High</SelectItem>
                    <SelectItem value="medium">Medium</SelectItem>
                    <SelectItem value="low">Low</SelectItem>
                </SelectContent>
            </Select>
        </div>

        <!-- Date Filter -->
        <div class="w-full sm:w-48">
            <DatePicker v-model="selectedDate" :available-dates="availableDates || []" placeholder="Select Date" class="w-full" />
        </div>

        <!-- Clear Filters -->
        <Button v-if="hasActiveFilters()" variant="outline" size="sm" @click="clearFilters" class="w-full sm:w-auto">
            <X class="mr-2 h-4 w-4" />
            Clear
        </Button>
    </div>
</template>
