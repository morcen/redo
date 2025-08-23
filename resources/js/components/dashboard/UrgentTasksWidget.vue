<script setup lang="ts">
import Badge from '@/components/ui/badge/Badge.vue';
import Card from '@/components/ui/card/Card.vue';
import CardContent from '@/components/ui/card/CardContent.vue';
import CardHeader from '@/components/ui/card/CardHeader.vue';
import { Link } from '@inertiajs/vue3';
import { AlertTriangle, Calendar, Clock } from 'lucide-vue-next';

interface UrgentTask {
    id: number;
    title: string;
    description: string | null;
    priority: 'low' | 'medium' | 'high';
    due_date: string | null;
    is_overdue: boolean;
    list_name: string;
    list_id: number;
}

interface Props {
    tasks: UrgentTask[];
}

const props = defineProps<Props>();

const priorityColor = (priority: string) => {
    switch (priority) {
        case 'high':
            return 'destructive';
        case 'medium':
            return 'secondary';
        case 'low':
            return 'outline';
        default:
            return 'outline';
    }
};

const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
};

const getDaysInfo = (dateString: string, isOverdue: boolean) => {
    if (isOverdue) return 'Overdue';

    const today = new Date();
    const dueDate = new Date(dateString);
    const diffTime = dueDate.getTime() - today.getTime();
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

    if (diffDays === 0) return 'Due today';
    if (diffDays === 1) return 'Due tomorrow';
    return `Due in ${diffDays} days`;
};
</script>

<template>
    <Card class="h-full py-6">
        <CardHeader class="pb-3">
            <div class="flex items-center gap-2">
                <AlertTriangle class="h-5 w-5 text-red-500" />
                <h3 class="text-lg font-semibold">Urgent Tasks</h3>
            </div>
            <p class="text-muted-foreground text-sm">
                {{ tasks.length > 0 ? `${tasks.length} task${tasks.length > 1 ? 's' : ''} need attention` : 'No urgent tasks' }}
            </p>
        </CardHeader>
        <CardContent>
            <div v-if="tasks.length === 0" class="py-8 text-center">
                <div class="mb-2 text-green-500">
                    <Clock class="mx-auto h-8 w-8" />
                </div>
                <p class="text-muted-foreground text-sm">All caught up! 🎉</p>
                <p class="text-muted-foreground mt-1 text-xs">No urgent tasks at the moment</p>
            </div>

            <div v-else class="max-h-64 space-y-3 overflow-y-auto">
                <div
                    v-for="task in tasks"
                    :key="task.id"
                    class="hover:bg-muted/50 rounded-lg border p-3 transition-colors"
                    :class="{ 'border-red-200 bg-red-50 dark:bg-red-950/20': task.is_overdue }"
                >
                    <div class="mb-2 flex items-start justify-between gap-2">
                        <Link :href="`/todo-lists/${task.list_id}/todos`" class="line-clamp-2 flex-1 text-sm font-medium hover:underline">
                            {{ task.title }}
                        </Link>
                        <Badge :variant="priorityColor(task.priority)" class="text-xs">
                            {{ task.priority }}
                        </Badge>
                    </div>

                    <div class="text-muted-foreground flex items-center justify-between text-xs">
                        <span class="flex items-center gap-1">
                            <Calendar class="h-3 w-3" />
                            {{ task.list_name }}
                        </span>
                        <span v-if="task.due_date" class="flex items-center gap-1" :class="{ 'font-medium text-red-600': task.is_overdue }">
                            <Clock class="h-3 w-3" />
                            {{ getDaysInfo(task.due_date, task.is_overdue) }}
                        </span>
                    </div>

                    <div v-if="task.is_overdue" class="mt-2 flex items-center gap-1 text-xs font-medium text-red-600">
                        <AlertTriangle class="h-3 w-3" />
                        This task is overdue
                    </div>
                </div>
            </div>

            <div v-if="tasks.length > 0" class="mt-4 border-t pt-3">
                <Link href="/todos" class="text-primary text-xs hover:underline"> View all tasks → </Link>
            </div>
        </CardContent>
    </Card>
</template>
