<script setup lang="ts">
import TodoItem from '@/components/TodoItem.vue';
import { type Todo } from '@/types';
import { computed } from 'vue';

interface Props {
    todos?: Todo[];
    groupedTodos?: Record<string, Todo[]>;
}

const props = defineProps<Props>();
const emit = defineEmits(['edit']);

const handleEdit = (todo: Todo) => {
    emit('edit', todo);
};

const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    const today = new Date();
    today.setHours(0, 0, 0, 0);

    const tomorrow = new Date(today);
    tomorrow.setDate(tomorrow.getDate() + 1);

    const yesterday = new Date(today);
    yesterday.setDate(yesterday.getDate() - 1);

    if (date.getTime() === today.getTime()) return 'Today';
    if (date.getTime() === tomorrow.getTime()) return 'Tomorrow';
    if (date.getTime() === yesterday.getTime()) return 'Yesterday';

    return date.toLocaleDateString(undefined, { weekday: 'long', month: 'long', day: 'numeric' });
};

const sortedDates = computed(() => {
    if (!props.groupedTodos) return [];
    return Object.keys(props.groupedTodos).sort((a, b) => new Date(a).getTime() - new Date(b).getTime());
});
</script>

<template>
    <div>
        <div v-if="(!todos || todos.length === 0) && (!groupedTodos || Object.keys(groupedTodos).length === 0)" class="py-8 text-center">
            <p class="text-muted-foreground">No todos found. Create one to get started!</p>
        </div>

        <!-- Display ungrouped todos if provided -->
        <div v-else-if="todos" class="grid gap-4">
            <TodoItem v-for="todo in todos" :key="todo.id" :todo="todo" @edit="handleEdit" />
        </div>

        <!-- Display grouped todos -->
        <div v-else-if="groupedTodos" class="space-y-8">
            <div v-for="date in sortedDates" :key="date" class="space-y-4">
                <h3 class="bg-background sticky top-0 z-10 py-2 text-lg font-medium">{{ formatDate(date) }}</h3>
                <div class="grid gap-4">
                    <TodoItem v-for="todo in groupedTodos[date]" :key="todo.id" :todo="todo" @edit="handleEdit" />
                </div>
            </div>
        </div>
    </div>
</template>
