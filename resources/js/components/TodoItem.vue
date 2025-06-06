<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Card, CardContent } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { type Todo } from '@/types';
import { useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Edit, MoreHorizontal, Trash2, Calendar } from 'lucide-vue-next';

interface Props {
    todo: Todo;
}

interface Emits {
    (e: 'edit', todo: Todo): void;
}

const props = defineProps<Props>();
const emit = defineEmits<Emits>();

const deleteForm = useForm({});

const priorityColors = {
    low: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
    medium: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
    high: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
};

const isOverdue = computed(() => {
    if (!props.todo.due_date) return false;
    const dueDate = new Date(props.todo.due_date);
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    return dueDate < today && !props.todo.completed;
});

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString();
};

const toggleCompleted = (checked: boolean | 'indeterminate') => {
    try {
        // Convert indeterminate to false, otherwise use the boolean value
        const completedValue = checked === 'indeterminate' ? false : checked;

        const form = useForm({
            title: props.todo.title,
            description: props.todo.description,
            completed: completedValue,
            priority: props.todo.priority,
            due_date: props.todo.due_date,
            todo_list_id: props.todo.todo_list_id,
        });

        form.put(route('todos.update', props.todo.id));
    } catch (error) {
        console.error('Error toggling todo completion:', error);
    }
};

const handleEdit = () => {
    emit('edit', props.todo);
};

const handleDelete = () => {
    if (confirm('Are you sure you want to delete this todo?')) {
        deleteForm.delete(route('todos.destroy', props.todo.id));
    }
};
</script>

<template>
    <Card class="transition-all hover:shadow-md" :class="{ 'opacity-60': todo.completed }">
        <CardContent class="p-4">
            <div class="flex items-start gap-3">
                <!-- Checkbox -->
                <Checkbox
                    :model-value="todo.completed"
                    @update:model-value="toggleCompleted"
                    class="mt-1"
                />

                <!-- Content -->
                <div class="flex-1 min-w-0">
                    <div class="flex items-start justify-between gap-2">
                        <div class="flex-1">
                            <h3 
                                class="font-medium text-sm"
                                :class="{ 'line-through text-muted-foreground': todo.completed }"
                            >
                                {{ todo.title }}
                            </h3>
                            <p 
                                v-if="todo.description" 
                                class="text-sm text-muted-foreground mt-1"
                                :class="{ 'line-through': todo.completed }"
                            >
                                {{ todo.description }}
                            </p>
                        </div>

                        <!-- Actions -->
                        <DropdownMenu :key="`todo-actions-${todo.id}`">
                            <DropdownMenuTrigger as-child>
                                <Button variant="ghost" size="sm" class="h-8 w-8 p-0">
                                    <MoreHorizontal class="h-4 w-4" />
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end">
                                <DropdownMenuItem @click="handleEdit">
                                    <Edit class="mr-2 h-4 w-4" />
                                    Edit
                                </DropdownMenuItem>
                                <DropdownMenuItem @click="handleDelete" class="text-red-600">
                                    <Trash2 class="mr-2 h-4 w-4" />
                                    Delete
                                </DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </div>

                    <!-- Meta information -->
                    <div class="flex items-center gap-2 mt-2">
                        <Badge :class="priorityColors[todo.priority]" variant="secondary">
                            {{ todo.priority }}
                        </Badge>

                        <div v-if="todo.due_date" class="flex items-center gap-1 text-xs text-muted-foreground">
                            <Calendar class="h-3 w-3" />
                            <span :class="{ 'text-red-600 font-medium': isOverdue }">
                                {{ formatDate(todo.due_date) }}
                            </span>
                            <span v-if="isOverdue" class="text-red-600 font-medium">
                                (Overdue)
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </CardContent>
    </Card>
</template>
