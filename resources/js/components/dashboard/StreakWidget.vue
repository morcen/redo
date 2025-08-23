<script setup lang="ts">
import Badge from '@/components/ui/badge/Badge.vue';
import Card from '@/components/ui/card/Card.vue';
import CardContent from '@/components/ui/card/CardContent.vue';
import CardHeader from '@/components/ui/card/CardHeader.vue';
import { Flame, Target, Trophy } from 'lucide-vue-next';
import { computed } from 'vue';

interface StreakInfo {
    current_streak: number;
    best_streak: number;
    streak_type: string;
}

interface RecentActivity {
    id: number;
    title: string;
    completed_at: string;
    list_name: string;
    priority: string;
}

interface Props {
    streakInfo: StreakInfo;
    recentActivity: RecentActivity[];
}

const props = defineProps<Props>();

const streakMessage = computed(() => {
    const current = props.streakInfo.current_streak;
    const best = props.streakInfo.best_streak;

    if (current === 0) {
        return '🌅 Ready to start a new streak!';
    } else if (current === 1) {
        return '🚀 Great start! Keep the momentum!';
    } else if (current === best) {
        return '🔥 New personal record!';
    } else if (current >= 7) {
        return '🌟 Amazing consistency!';
    } else if (current >= 3) {
        return '💪 Building a strong habit!';
    }
    return '👍 Keep it up!';
});

const streakEmoji = computed(() => {
    const current = props.streakInfo.current_streak;
    if (current >= 10) return '🔥';
    if (current >= 7) return '⭐';
    if (current >= 3) return '💪';
    if (current >= 1) return '🌱';
    return '🎯';
});

const formatTimeAgo = (dateString: string) => {
    const now = new Date();
    const date = new Date(dateString);
    const diffInHours = Math.floor((now.getTime() - date.getTime()) / (1000 * 60 * 60));

    if (diffInHours < 1) return 'Just now';
    if (diffInHours < 24) return `${diffInHours}h ago`;

    const diffInDays = Math.floor(diffInHours / 24);
    return `${diffInDays}d ago`;
};
</script>

<template>
    <Card class="h-full py-6">
        <CardHeader class="pb-3">
            <div class="flex items-center gap-2">
                <Flame class="h-5 w-5 text-orange-500" />
                <h3 class="text-lg font-semibold">Streak & Activity</h3>
            </div>
            <p class="text-muted-foreground text-sm">Your consistency journey</p>
        </CardHeader>
        <CardContent class="space-y-4">
            <!-- Streak Section -->
            <div class="rounded-lg bg-gradient-to-r from-orange-50 to-red-50 p-4 dark:from-orange-950/20 dark:to-red-950/20">
                <div class="mb-3 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="text-2xl">{{ streakEmoji }}</span>
                        <div>
                            <p class="text-sm font-medium">Current Streak</p>
                            <p class="text-muted-foreground text-xs">Daily habits completion</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-2xl font-bold text-orange-600">
                            {{ streakInfo.current_streak }}
                        </div>
                        <p class="text-muted-foreground text-xs">day{{ streakInfo.current_streak !== 1 ? 's' : '' }}</p>
                    </div>
                </div>

                <p class="mb-2 text-center text-sm font-medium">{{ streakMessage }}</p>

                <div class="text-muted-foreground flex items-center justify-between text-xs">
                    <div class="flex items-center gap-1">
                        <Trophy class="h-3 w-3" />
                        <span>Best: {{ streakInfo.best_streak }} days</span>
                    </div>
                    <Badge :variant="streakInfo.current_streak > 0 ? 'default' : 'secondary'" class="text-xs">
                        {{ streakInfo.current_streak === streakInfo.best_streak ? 'Record!' : 'Active' }}
                    </Badge>
                </div>
            </div>

            <!-- Recent Activity -->
            <div>
                <div class="mb-3 flex items-center gap-2">
                    <Target class="h-4 w-4 text-green-500" />
                    <h4 class="text-sm font-medium">Recent Completions</h4>
                </div>

                <div v-if="recentActivity.length === 0" class="py-4 text-center">
                    <p class="text-muted-foreground text-xs">No recent activity</p>
                    <p class="text-muted-foreground mt-1 text-xs">Complete some tasks to see them here!</p>
                </div>

                <div v-else class="max-h-40 space-y-2 overflow-y-auto">
                    <div
                        v-for="activity in recentActivity"
                        :key="activity.id"
                        class="bg-muted/30 flex items-start justify-between gap-2 rounded-md p-2"
                    >
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-xs font-medium">{{ activity.title }}</p>
                            <p class="text-muted-foreground text-xs">{{ activity.list_name }}</p>
                        </div>
                        <div class="flex-shrink-0 text-right">
                            <Badge
                                variant="outline"
                                class="mb-1 text-xs"
                                :class="{
                                    'border-red-200 text-red-700': activity.priority === 'high',
                                    'border-yellow-200 text-yellow-700': activity.priority === 'medium',
                                    'border-blue-200 text-blue-700': activity.priority === 'low',
                                }"
                            >
                                {{ activity.priority }}
                            </Badge>
                            <p class="text-muted-foreground text-xs">
                                {{ formatTimeAgo(activity.completed_at) }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Motivational Quote -->
            <div class="bg-muted/30 rounded-lg p-3 text-center">
                <p class="text-muted-foreground text-xs font-medium">
                    {{
                        streakInfo.current_streak >= 7
                            ? '🎉 "Success is the sum of small efforts repeated day in and day out."'
                            : streakInfo.current_streak >= 3
                              ? '💫 "The secret of getting ahead is getting started."'
                              : '✨ "A journey of a thousand miles begins with a single step."'
                    }}
                </p>
            </div>
        </CardContent>
    </Card>
</template>
