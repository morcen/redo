<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { type Todo, type TodoList } from '@/types';
import { useForm } from '@inertiajs/vue3';
import { computed, watch, nextTick } from 'vue';

interface Props {
    todo?: Todo | null;
    todoLists: TodoList[];
    defaultListId?: number;
    open?: boolean;
}

interface Emits {
    (e: 'close'): void;
}

const props = withDefaults(defineProps<Props>(), {
    open: true,
});
const emit = defineEmits<Emits>();

const isEditing = computed(() => !!props.todo);

const selectedListName = computed(() => {
    const selectedList = props.todoLists.find(list => list.id.toString() === form.todo_list_id);
    return selectedList?.name || '';
});

const form = useForm({
    title: '',
    description: '',
    priority: 'medium' as 'low' | 'medium' | 'high',
    due_date: '',
    todo_list_id: '',
});

// Watch for todo prop changes to populate form
watch(() => props.todo, (todo) => {
    if (todo) {
        form.title = todo.title;
        form.description = todo.description || '';
        form.priority = todo.priority;
        form.due_date = todo.due_date || '';
        form.todo_list_id = todo.todo_list_id.toString();
    } else {
        form.reset();
        // Set default list ID if provided
        if (props.defaultListId) {
            form.todo_list_id = props.defaultListId.toString();
        }
    }
}, { immediate: true });

const handleSubmit = () => {
    if (isEditing.value && props.todo) {
        form.put(route('todos.update', props.todo.id), {
            onSuccess: () => {
                emit('close');
            },
        });
    } else {
        form.post(route('todos.store'), {
            onSuccess: () => {
                emit('close');
            },
        });
    }
};

const handleClose = () => {
    // Use nextTick to ensure DOM operations are safe
    nextTick(() => {
        try {
            form.reset();
            emit('close');
        } catch (error) {
            console.warn('Error during form close:', error);
            // Still emit close even if form reset fails
            emit('close');
        }
    });
};
</script>

<template>
    <Dialog
        :key="`todo-form-${props.todo?.id || 'new'}`"
        :open="props.open"
        @update:open="handleClose"
    >
        <DialogContent class="sm:max-w-[425px]">
            <DialogHeader>
                <DialogTitle>
                    {{ isEditing ? 'Edit Todo' : 'Create New Todo' }}
                </DialogTitle>
                <DialogDescription>
                    {{ isEditing ? 'Update your todo details below.' : 'Add a new todo to your list.' }}
                </DialogDescription>
            </DialogHeader>

            <form @submit.prevent="handleSubmit" class="space-y-4">
                <div class="space-y-2">
                    <Label for="title">Title</Label>
                    <Input
                        id="title"
                        v-model="form.title"
                        placeholder="Enter todo title"
                        :class="{ 'border-red-500': form.errors.title }"
                    />
                    <p v-if="form.errors.title" class="text-sm text-red-500">
                        {{ form.errors.title }}
                    </p>
                </div>

                <div class="space-y-2">
                    <Label for="description">Description</Label>
                    <Textarea
                        id="description"
                        v-model="form.description"
                        placeholder="Enter todo description (optional)"
                        rows="3"
                        :class="{ 'border-red-500': form.errors.description }"
                    />
                    <p v-if="form.errors.description" class="text-sm text-red-500">
                        {{ form.errors.description }}
                    </p>
                </div>

                <div class="space-y-2">
                    <Label for="todo_list_id">List</Label>
                    <div class="px-3 py-2 border border-gray-200 rounded-md bg-gray-50 text-gray-700">
                        {{ selectedListName || 'No list selected' }}
                    </div>
                    <p v-if="form.errors.todo_list_id" class="text-sm text-red-500">
                        {{ form.errors.todo_list_id }}
                    </p>
                </div>

                <div class="space-y-2">
                    <Label for="priority">Priority</Label>
                    <Select v-model="form.priority">
                        <SelectTrigger>
                            <SelectValue placeholder="Select priority" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="low">Low</SelectItem>
                            <SelectItem value="medium">Medium</SelectItem>
                            <SelectItem value="high">High</SelectItem>
                        </SelectContent>
                    </Select>
                    <p v-if="form.errors.priority" class="text-sm text-red-500">
                        {{ form.errors.priority }}
                    </p>
                </div>

                <div class="space-y-2">
                    <Label for="due_date">Due Date</Label>
                    <Input
                        id="due_date"
                        v-model="form.due_date"
                        type="date"
                        :class="{ 'border-red-500': form.errors.due_date }"
                    />
                    <p v-if="form.errors.due_date" class="text-sm text-red-500">
                        {{ form.errors.due_date }}
                    </p>
                </div>

                <DialogFooter>
                    <Button type="button" variant="outline" @click="handleClose">
                        Cancel
                    </Button>
                    <Button type="submit" :disabled="form.processing">
                        {{ form.processing ? 'Saving...' : (isEditing ? 'Update' : 'Create') }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
