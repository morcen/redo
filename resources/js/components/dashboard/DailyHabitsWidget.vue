<script setup lang="ts">
import Badge from '@/components/ui/badge/Badge.vue';
import Card from '@/components/ui/card/Card.vue';
import CardContent from '@/components/ui/card/CardContent.vue';
import CardHeader from '@/components/ui/card/CardHeader.vue';
import { Link } from '@inertiajs/vue3';
import { CheckCircle, Repeat, Target } from 'lucide-vue-next';
import { computed } from 'vue';

interface DailyHabit {
    id: number;
    name: string;
    description: string | null;
    total_todos: number;
    completed_todos: number;
    completion_rate: number;
    is_complete: boolean;
}

interface Props {
    habits: DailyHabit[];
}

const props = defineProps<Props>();

const overallProgress = computed(() => {
    if (props.habits.length === 0) return 0;
    const totalRate = props.habits.reduce((sum, habit) => sum + habit.completion_rate, 0);
    return Math.round(totalRate / props.habits.length);
});

const completedHabits = computed(() => {
    return props.habits.filter((habit) => habit.is_complete).length;
});

const progressColor = (rate: number) => {
    if (rate === 100) return 'bg-green-500';
    if (rate >= 80) return 'bg-blue-500';
    if (rate >= 60) return 'bg-yellow-500';
    if (rate >= 40) return 'bg-orange-500';
    return 'bg-red-500';
};

const badgeVariant = (rate: number) => {
    if (rate === 100) return 'default';
    if (rate >= 80) return 'secondary';
    return 'outline';
};
</script>

<template>
    <Card class="h-full py-6">
        <CardHeader class="pb-3">
            <div class="flex items-center gap-2">
                <Repeat class="h-5 w-5 text-blue-500" />
                <h3 class="text-lg font-semibold">Daily Habits</h3>
            </div>
            <div class="flex items-center gap-2">
                <p class="text-muted-foreground text-sm">
                    {{ habits.length > 0 ? `${completedHabits}/${habits.length} complete` : 'No daily habits set up' }}
                </p>
                <Badge v-if="habits.length > 0" :variant="badgeVariant(overallProgress)" class="text-xs"> {{ overallProgress }}% </Badge>
            </div>
        </CardHeader>
        <CardContent>
            <div v-if="habits.length === 0" class="py-8 text-center">
                <div class="mb-2 text-blue-500">
                    <Target class="mx-auto h-8 w-8" />
                </div>
                <p class="text-muted-foreground mb-2 text-sm">No daily habits yet</p>
                <p class="text-muted-foreground text-xs">Create lists with "refresh daily" enabled to track habits</p>
                <Link href="/todo-lists" class="text-primary mt-2 inline-block text-xs hover:underline"> Create your first habit list → </Link>
            </div>

            <div v-else class="max-h-72 space-y-4 overflow-y-auto">
                <!-- Overall Progress -->
                <div class="bg-muted/50 rounded-lg p-3">
                    <div class="mb-2 flex items-center justify-between">
                        <span class="text-sm font-medium">Overall Progress</span>
                        <span class="text-muted-foreground text-sm">{{ overallProgress }}%</span>
                    </div>
                    <div class="bg-muted h-2 overflow-hidden rounded-full">
                        <div
                            :class="progressColor(overallProgress)"
                            class="h-full transition-all duration-500"
                            :style="{ width: `${overallProgress}%` }"
                        />
                    </div>
                </div>

                <!-- Individual Habits -->
                <div class="space-y-3">
                    <div
                        v-for="habit in habits"
                        :key="habit.id"
                        class="hover:bg-muted/50 rounded-lg border p-3 transition-colors"
                        :class="{ 'border-green-200 bg-green-50 dark:bg-green-950/20': habit.is_complete }"
                    >
                        <div class="mb-2 flex items-start justify-between gap-2">
                            <Link :href="`/todo-lists/${habit.id}/todos`" class="flex-1 text-sm font-medium hover:underline">
                                {{ habit.name }}
                            </Link>
                            <div class="flex items-center gap-1">
                                <CheckCircle v-if="habit.is_complete" class="h-4 w-4 text-green-500" />
                                <Badge :variant="badgeVariant(habit.completion_rate)" class="text-xs"> {{ habit.completion_rate }}% </Badge>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <div class="text-muted-foreground flex items-center justify-between text-xs">
                                <span>{{ habit.completed_todos }}/{{ habit.total_todos }} tasks</span>
                                <span v-if="habit.total_todos > 0"> {{ habit.total_todos - habit.completed_todos }} remaining </span>
                            </div>
                            <div class="bg-muted h-1.5 overflow-hidden rounded-full">
                                <div
                                    :class="progressColor(habit.completion_rate)"
                                    class="h-full transition-all duration-300"
                                    :style="{ width: `${habit.completion_rate}%` }"
                                />
                            </div>
                        </div>

                        <p v-if="habit.description" class="text-muted-foreground mt-2 text-xs">
                            {{ habit.description }}
                        </p>
                    </div>
                </div>
            </div>

            <div v-if="habits.length > 0" class="mt-4 border-t pt-3">
                <Link href="/todo-lists" class="text-primary text-xs hover:underline"> Manage habit lists → </Link>
            </div>
        </CardContent>
    </Card>
</template>
