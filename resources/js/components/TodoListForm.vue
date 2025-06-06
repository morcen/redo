<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { Textarea } from '@/components/ui/textarea';
import { type TodoList } from '@/types';
import { useForm } from '@inertiajs/vue3';
import { computed, watch } from 'vue';

interface Props {
    list?: TodoList | null;
    open?: boolean;
}

interface Emits {
    (e: 'close'): void;
}

const props = withDefaults(defineProps<Props>(), {
    open: true,
});
const emit = defineEmits<Emits>();

const isEditing = computed(() => !!props.list);

const form = useForm({
    name: '',
    description: '',
    refresh_daily: false,
});

// Watch for list prop changes to populate form
watch(
    () => props.list,
    (list) => {
        if (list) {
            form.name = list.name;
            form.description = list.description || '';
            form.refresh_daily = list.refresh_daily || false;
        } else {
            form.reset();
        }
    },
    { immediate: true },
);

const handleSubmit = () => {
    if (isEditing.value && props.list) {
        form.put(route('todo-lists.update', props.list.id), {
            onSuccess: () => {
                emit('close');
            },
        });
    } else {
        form.post(route('todo-lists.store'), {
            onSuccess: () => {
                emit('close');
            },
        });
    }
};

const handleClose = () => {
    form.reset();
    emit('close');
};
</script>

<template>
    <Dialog :open="props.open" @update:open="handleClose">
        <DialogContent class="sm:max-w-[425px]">
            <DialogHeader>
                <DialogTitle>
                    {{ isEditing ? 'Edit List' : 'Create New List' }}
                </DialogTitle>
                <DialogDescription>
                    {{ isEditing ? 'Update your list details below.' : 'Add a new list to organize your todos.' }}
                </DialogDescription>
            </DialogHeader>

            <form @submit.prevent="handleSubmit" class="space-y-4">
                <div class="space-y-2">
                    <Label for="name">Name</Label>
                    <Input id="name" v-model="form.name" placeholder="Enter list name" :class="{ 'border-red-500': form.errors.name }" />
                    <p v-if="form.errors.name" class="text-sm text-red-500">
                        {{ form.errors.name }}
                    </p>
                </div>

                <div class="space-y-2">
                    <Label for="description">Description</Label>
                    <Textarea
                        id="description"
                        v-model="form.description"
                        placeholder="Enter list description (optional)"
                        rows="3"
                        :class="{ 'border-red-500': form.errors.description }"
                    />
                    <p v-if="form.errors.description" class="text-sm text-red-500">
                        {{ form.errors.description }}
                    </p>
                </div>

                <div class="space-y-2">
                    <div class="flex items-center space-x-2">
                        <Switch id="refresh_daily" v-model:checked="form.refresh_daily" />
                        <Label for="refresh_daily" class="text-sm leading-none font-medium peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                            Refresh daily
                        </Label>
                    </div>
                    <p class="text-muted-foreground text-xs">When enabled, todos from this list will be recreated each day</p>
                    <p v-if="form.errors.refresh_daily" class="text-sm text-red-500">
                        {{ form.errors.refresh_daily }}
                    </p>
                </div>

                <DialogFooter>
                    <Button type="button" variant="outline" @click="handleClose"> Cancel </Button>
                    <Button type="submit" :disabled="form.processing">
                        {{ form.processing ? 'Saving...' : isEditing ? 'Update List' : 'Create List' }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
