<script setup lang="ts">
import TodoListItem from '@/components/TodoListItem.vue';
import { type TodoList } from '@/types';

interface Props {
    lists: TodoList[];
}

interface Emits {
    (e: 'edit', list: TodoList): void;
}

const props = defineProps<Props>();
const emit = defineEmits<Emits>();

const handleEdit = (list: TodoList) => {
    emit('edit', list);
};
</script>

<template>
    <div class="space-y-4">
        <div v-if="lists.length === 0" class="text-center py-12">
            <div class="mx-auto max-w-sm">
                <div class="mb-4">
                    <svg
                        class="mx-auto h-12 w-12 text-muted-foreground"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        aria-hidden="true"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"
                        />
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-foreground">No lists yet</h3>
                <p class="mt-1 text-sm text-muted-foreground">
                    Get started by creating your first list.
                </p>
            </div>
        </div>

        <div v-else class="grid gap-4">
            <TodoListItem
                v-for="list in lists"
                :key="list.id"
                :list="list"
                @edit="handleEdit"
            />
        </div>
    </div>
</template>
