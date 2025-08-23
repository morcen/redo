<script setup lang="ts">
import { computed } from 'vue';
import Card from '@/components/ui/card/Card.vue';
import CardContent from '@/components/ui/card/CardContent.vue';
import CardHeader from '@/components/ui/card/CardHeader.vue';
import Badge from '@/components/ui/badge/Badge.vue';
import { Flame, Trophy, Target } from 'lucide-vue-next';

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
        return "🌅 Ready to start a new streak!";
    } else if (current === 1) {
        return "🚀 Great start! Keep the momentum!";
    } else if (current === best) {
        return "🔥 New personal record!";
    } else if (current >= 7) {
        return "🌟 Amazing consistency!";
    } else if (current >= 3) {
        return "💪 Building a strong habit!";
    }
    return "👍 Keep it up!";
});

const streakEmoji = computed(() => {
    const current = props.streakInfo.current_streak;
    if (current >= 10) return "🔥";
    if (current >= 7) return "⭐";
    if (current >= 3) return "💪";
    if (current >= 1) return "🌱";
    return "🎯";
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
            <p class="text-sm text-muted-foreground">Your consistency journey</p>
        </CardHeader>
        <CardContent class="space-y-4">
            <!-- Streak Section -->
            <div class="bg-gradient-to-r from-orange-50 to-red-50 dark:from-orange-950/20 dark:to-red-950/20 rounded-lg p-4">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <span class="text-2xl">{{ streakEmoji }}</span>
                        <div>
                            <p class="text-sm font-medium">Current Streak</p>
                            <p class="text-xs text-muted-foreground">Daily habits completion</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-2xl font-bold text-orange-600">
                            {{ streakInfo.current_streak }}
                        </div>
                        <p class="text-xs text-muted-foreground">day{{ streakInfo.current_streak !== 1 ? 's' : '' }}</p>
                    </div>
                </div>
                
                <p class="text-sm font-medium text-center mb-2">{{ streakMessage }}</p>
                
                <div class="flex items-center justify-between text-xs text-muted-foreground">
                    <div class="flex items-center gap-1">
                        <Trophy class="h-3 w-3" />
                        <span>Best: {{ streakInfo.best_streak }} days</span>
                    </div>
                    <Badge 
                        :variant="streakInfo.current_streak > 0 ? 'default' : 'secondary'" 
                        class="text-xs"
                    >
                        {{ streakInfo.current_streak === streakInfo.best_streak ? 'Record!' : 'Active' }}
                    </Badge>
                </div>
            </div>

            <!-- Recent Activity -->
            <div>
                <div class="flex items-center gap-2 mb-3">
                    <Target class="h-4 w-4 text-green-500" />
                    <h4 class="text-sm font-medium">Recent Completions</h4>
                </div>
                
                <div v-if="recentActivity.length === 0" class="text-center py-4">
                    <p class="text-xs text-muted-foreground">No recent activity</p>
                    <p class="text-xs text-muted-foreground mt-1">Complete some tasks to see them here!</p>
                </div>
                
                <div v-else class="space-y-2 max-h-40 overflow-y-auto">
                    <div 
                        v-for="activity in recentActivity" 
                        :key="activity.id"
                        class="flex items-start justify-between gap-2 p-2 bg-muted/30 rounded-md"
                    >
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-medium truncate">{{ activity.title }}</p>
                            <p class="text-xs text-muted-foreground">{{ activity.list_name }}</p>
                        </div>
                        <div class="text-right flex-shrink-0">
                            <Badge 
                                variant="outline" 
                                class="text-xs mb-1"
                                :class="{
                                    'border-red-200 text-red-700': activity.priority === 'high',
                                    'border-yellow-200 text-yellow-700': activity.priority === 'medium',
                                    'border-blue-200 text-blue-700': activity.priority === 'low'
                                }"
                            >
                                {{ activity.priority }}
                            </Badge>
                            <p class="text-xs text-muted-foreground">
                                {{ formatTimeAgo(activity.completed_at) }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Motivational Quote -->
            <div class="bg-muted/30 rounded-lg p-3 text-center">
                <p class="text-xs font-medium text-muted-foreground">
                    {{ streakInfo.current_streak >= 7 
                        ? "🎉 \"Success is the sum of small efforts repeated day in and day out.\"" 
                        : streakInfo.current_streak >= 3 
                        ? "💫 \"The secret of getting ahead is getting started.\"" 
                        : "✨ \"A journey of a thousand miles begins with a single step.\"" 
                    }}
                </p>
            </div>
        </CardContent>
    </Card>
</template>
