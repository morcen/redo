<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Progress } from '@/components/ui/progress';
import { type TodoList } from '@/types';
import { router, useForm } from '@inertiajs/vue3';
import { Copy, Edit, MoreHorizontal, RotateCcw, Trash2 } from 'lucide-vue-next';
import { computed } from 'vue';

interface Props {
    list: TodoList;
}

interface Emits {
    (e: 'edit', list: TodoList): void;
}

const props = defineProps<Props>();
const emit = defineEmits<Emits>();

const deleteForm = useForm({});
const duplicateForm = useForm({});

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

const handleEdit = () => {
    emit('edit', props.list);
};

const handleManageTodos = () => {
    router.get(route('todo-lists.todos', props.list.id));
};

const handleDuplicate = () => {
    if (confirm('Are you sure you want to duplicate this list?')) {
        duplicateForm.post(route('todo-lists.duplicate', props.list.id));
    }
};

const handleDelete = () => {
    if (confirm('Are you sure you want to delete this list?')) {
        deleteForm.delete(route('todo-lists.destroy', props.list.id));
    }
};
</script>

<template>
    <Card class="cursor-pointer transition-all hover:shadow-md" @click="handleManageTodos">
        <CardContent class="p-4">
            <div class="flex items-start justify-between gap-3">
                <!-- Content -->
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2">
                        <h3 class="text-sm font-medium">
                            {{ list.name }}
                        </h3>
                        <div
                            v-if="list.refresh_daily"
                            class="flex items-center gap-1 rounded-full bg-blue-100 px-2 py-1 text-xs text-blue-700"
                            title="This list refreshes daily"
                        >
                            <RotateCcw class="h-3 w-3" />
                            <span>Daily</span>
                        </div>
                    </div>
                    <p v-if="list.description" class="text-muted-foreground mt-1 text-sm">
                        {{ list.description }}
                    </p>

                    <!-- Completion Progress -->
                    <div v-if="totalTodos > 0" class="mt-3 space-y-2">
                        <div class="text-muted-foreground flex items-center justify-between text-xs">
                            <span>{{ completedTodos }} of {{ totalTodos }} completed</span>
                            <span>{{ Math.round(completionPercentage) }}%</span>
                        </div>
                        <Progress :value="completionPercentage" :max="100" size="sm" :show-percentage="false" class="w-full" />
                    </div>
                    <div v-else class="mt-2">
                        <span class="text-muted-foreground text-xs">No todos yet</span>
                    </div>
                </div>

                <!-- Actions -->
                <DropdownMenu>
                    <DropdownMenuTrigger as-child>
                        <Button variant="ghost" size="sm" class="h-8 w-8 p-0" @click.stop>
                            <MoreHorizontal class="h-4 w-4" />
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end">
                        <DropdownMenuItem @click="handleEdit">
                            <Edit class="mr-2 h-4 w-4" />
                            Edit
                        </DropdownMenuItem>
                        <DropdownMenuItem @click="handleDuplicate">
                            <Copy class="mr-2 h-4 w-4" />
                            Duplicate
                        </DropdownMenuItem>
                        <DropdownMenuItem @click="handleDelete" class="text-red-600">
                            <Trash2 class="mr-2 h-4 w-4" />
                            Delete
                        </DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenu>
            </div>
        </CardContent>
    </Card>
</template>
