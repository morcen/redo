<script setup lang="ts">
import { computed } from 'vue';
import Card from '@/components/ui/card/Card.vue';
import CardContent from '@/components/ui/card/CardContent.vue';
import CardHeader from '@/components/ui/card/CardHeader.vue';
import Badge from '@/components/ui/badge/Badge.vue';
import { BarChart3, CheckSquare, Calendar, Repeat } from 'lucide-vue-next';

interface QuickStats {
    total_todos: number;
    completed_todos: number;
    total_lists: number;
    daily_lists: number;
    overall_completion_rate: number;
    this_week_completed: number;
    this_week_total: number;
}

interface Props {
    stats: QuickStats;
}

const props = defineProps<Props>();

const statsItems = computed(() => [
    {
        icon: CheckSquare,
        label: 'Total Tasks',
        value: props.stats.total_todos,
        subtext: `${props.stats.completed_todos} completed`,
        color: 'text-blue-600',
        bgColor: 'bg-blue-100 dark:bg-blue-950/20'
    },
    {
        icon: Calendar,
        label: 'Todo Lists',
        value: props.stats.total_lists,
        subtext: `${props.stats.daily_lists} daily habits`,
        color: 'text-green-600',
        bgColor: 'bg-green-100 dark:bg-green-950/20'
    },
    {
        icon: BarChart3,
        label: 'Completion Rate',
        value: `${props.stats.overall_completion_rate}%`,
        subtext: 'Overall progress',
        color: 'text-purple-600',
        bgColor: 'bg-purple-100 dark:bg-purple-950/20'
    },
    {
        icon: Repeat,
        label: 'This Week',
        value: props.stats.this_week_completed,
        subtext: `of ${props.stats.this_week_total} tasks`,
        color: 'text-orange-600',
        bgColor: 'bg-orange-100 dark:bg-orange-950/20'
    }
]);

const weeklyCompletionRate = computed(() => {
    return props.stats.this_week_total > 0 
        ? Math.round((props.stats.this_week_completed / props.stats.this_week_total) * 100)
        : 0;
});
</script>

<template>
    <Card class="h-full py-6">
        <CardHeader class="pb-3">
            <div class="flex items-center gap-2">
                <BarChart3 class="h-5 w-5 text-purple-500" />
                <h3 class="text-lg font-semibold">Quick Stats</h3>
            </div>
            <p class="text-sm text-muted-foreground">Your productivity at a glance</p>
        </CardHeader>
        <CardContent>
            <div class="grid grid-cols-2 gap-3">
                <div 
                    v-for="item in statsItems" 
                    :key="item.label"
                    class="rounded-lg p-3 transition-colors hover:bg-muted/50"
                    :class="item.bgColor"
                >
                    <div class="flex items-start justify-between mb-2">
                        <component 
                            :is="item.icon"
                            class="h-5 w-5 flex-shrink-0"
                            :class="item.color"
                        />
                        <div class="text-right">
                            <div class="text-lg font-bold" :class="item.color">
                                {{ item.value }}
                            </div>
                        </div>
                    </div>
                    
                    <div class="space-y-1">
                        <p class="text-xs font-medium text-muted-foreground">
                            {{ item.label }}
                        </p>
                        <p class="text-xs text-muted-foreground">
                            {{ item.subtext }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Weekly Progress Bar -->
            <div class="mt-4 p-3 bg-muted/30 rounded-lg">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-xs font-medium">This Week's Progress</span>
                    <Badge variant="secondary" class="text-xs">
                        {{ weeklyCompletionRate }}%
                    </Badge>
                </div>
                <div class="h-2 bg-muted rounded-full overflow-hidden">
                    <div 
                        class="h-full bg-gradient-to-r from-blue-500 to-purple-500 transition-all duration-500"
                        :style="{ width: `${weeklyCompletionRate}%` }"
                    />
                </div>
                <p class="text-xs text-muted-foreground mt-1">
                    {{ stats.this_week_completed }} of {{ stats.this_week_total }} tasks completed
                </p>
            </div>

            <!-- Motivational Message -->
            <div class="mt-3 text-center">
                <p v-if="stats.overall_completion_rate >= 80" class="text-xs text-green-600 font-medium">
                    🌟 Excellent work! Keep up the momentum!
                </p>
                <p v-else-if="stats.overall_completion_rate >= 60" class="text-xs text-blue-600 font-medium">
                    💪 Good progress! You're doing great!
                </p>
                <p v-else-if="stats.overall_completion_rate >= 40" class="text-xs text-yellow-600 font-medium">
                    🚀 Building momentum! Keep going!
                </p>
                <p v-else-if="stats.total_todos > 0" class="text-xs text-orange-600 font-medium">
                    🌅 Every journey starts with a single step!
                </p>
                <p v-else class="text-xs text-muted-foreground">
                    📝 Ready to start your productivity journey?
                </p>
            </div>
        </CardContent>
    </Card>
</template>
