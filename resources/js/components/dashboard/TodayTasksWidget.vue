<script setup lang="ts">
import Badge from '@/components/ui/badge/Badge.vue';
import Card from '@/components/ui/card/Card.vue';
import CardContent from '@/components/ui/card/CardContent.vue';
import CardHeader from '@/components/ui/card/CardHeader.vue';
import { CalendarDays, CheckCircle, Clock } from 'lucide-vue-next';
import { computed } from 'vue';

interface TodayStats {
    total: number;
    completed: number;
    remaining: number;
    completionRate: number;
    date: string;
}

interface Props {
    stats: TodayStats;
}

const props = defineProps<Props>();

const progressColor = computed(() => {
    const rate = props.stats.completionRate;
    if (rate === 100) return 'bg-green-500';
    if (rate >= 80) return 'bg-blue-500';
    if (rate >= 60) return 'bg-yellow-500';
    if (rate >= 40) return 'bg-orange-500';
    return 'bg-red-500';
});

const motivationMessage = computed(() => {
    const rate = props.stats.completionRate;
    const remaining = props.stats.remaining;

    if (rate === 100) return '🎉 Perfect day! All tasks completed!';
    if (remaining === 1) return '💪 Just one more task to go!';
    if (remaining <= 3) return "🚀 You're almost there!";
    if (rate >= 50) return '👍 Great progress today!';
    if (props.stats.total === 0) return '📝 No tasks for today yet';
    return "🌅 Let's tackle those tasks!";
});
</script>

<template>
    <Card class="h-full py-6">
        <CardHeader class="pb-3">
            <div class="flex items-center gap-2">
                <CalendarDays class="text-muted-foreground h-5 w-5" />
                <h3 class="text-lg font-semibold">Today's Progress</h3>
            </div>
            <p class="text-muted-foreground text-sm">
                {{ new Date(stats.date).toLocaleDateString('en-US', { weekday: 'long', month: 'short', day: 'numeric' }) }}
            </p>
        </CardHeader>
        <CardContent class="space-y-4">
            <!-- Progress Bar -->
            <div class="space-y-2">
                <div class="flex justify-between text-sm">
                    <span>Progress</span>
                    <span class="font-medium">{{ stats.completionRate }}%</span>
                </div>
                <div class="bg-muted h-3 overflow-hidden rounded-full">
                    <div :class="progressColor" class="h-full transition-all duration-500 ease-out" :style="{ width: `${stats.completionRate}%` }" />
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-3 gap-2 text-center">
                <div class="space-y-1">
                    <div class="text-muted-foreground text-2xl font-bold">{{ stats.total }}</div>
                    <div class="text-muted-foreground text-xs">Total</div>
                </div>
                <div class="space-y-1">
                    <div class="text-2xl font-bold text-green-600">{{ stats.completed }}</div>
                    <div class="text-muted-foreground text-xs">Done</div>
                </div>
                <div class="space-y-1">
                    <div class="text-2xl font-bold text-orange-600">{{ stats.remaining }}</div>
                    <div class="text-muted-foreground text-xs">Left</div>
                </div>
            </div>

            <!-- Motivation Message -->
            <div class="bg-muted/50 rounded-lg p-3 text-center">
                <p class="text-sm font-medium">{{ motivationMessage }}</p>
            </div>

            <!-- Quick Actions -->
            <div class="flex gap-2 pt-2">
                <Badge variant="secondary" class="flex items-center gap-1 text-xs">
                    <CheckCircle class="h-3 w-3" />
                    {{ stats.completed }} completed
                </Badge>
                <Badge v-if="stats.remaining > 0" variant="outline" class="flex items-center gap-1 text-xs">
                    <Clock class="h-3 w-3" />
                    {{ stats.remaining }} remaining
                </Badge>
            </div>
        </CardContent>
    </Card>
</template>
