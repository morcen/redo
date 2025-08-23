<script setup lang="ts">
import DailyHabitsWidget from '@/components/dashboard/DailyHabitsWidget.vue';
import QuickStatsWidget from '@/components/dashboard/QuickStatsWidget.vue';
import StreakWidget from '@/components/dashboard/StreakWidget.vue';
import TodayTasksWidget from '@/components/dashboard/TodayTasksWidget.vue';
import UrgentTasksWidget from '@/components/dashboard/UrgentTasksWidget.vue';
import Badge from '@/components/ui/badge/Badge.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { Calendar } from 'lucide-vue-next';

interface DashboardData {
    todayStats: any;
    urgentTasks: any[];
    dailyHabitsProgress: any[];
    quickStats: any;
    recentActivity: any[];
    streakInfo: any;
    upcomingTasks: any[];
}

defineProps<DashboardData>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

function getTimeOfDayGreeting() {
    const hour = new Date().getHours();
    if (hour < 12) return 'morning';
    if (hour < 17) return 'afternoon';
    return 'evening';
}
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Welcome Message -->
            <div class="mb-2">
                <h1 class="text-2xl font-bold">Good {{ getTimeOfDayGreeting() }}! 👋</h1>
                <p class="text-muted-foreground">Here's your productivity overview for today</p>
            </div>

            <!-- Top Row - Key Metrics -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                <TodayTasksWidget :stats="todayStats" />
                <UrgentTasksWidget :tasks="urgentTasks" />
                <QuickStatsWidget :stats="quickStats" />
            </div>

            <!-- Second Row - Detailed Views -->
            <div class="grid gap-4 md:grid-cols-2">
                <DailyHabitsWidget :habits="dailyHabitsProgress" />
                <StreakWidget :streak-info="streakInfo" :recent-activity="recentActivity" />
            </div>

            <!-- Upcoming Tasks (if any) -->
            <div v-if="upcomingTasks && upcomingTasks.length > 0" class="mt-2">
                <div class="rounded-lg bg-blue-50 p-4 dark:bg-blue-950/20">
                    <h3 class="mb-3 flex items-center gap-2 text-lg font-semibold">
                        <Calendar class="h-5 w-5 text-blue-500" />
                        Upcoming Tasks
                    </h3>
                    <div class="grid gap-2 md:grid-cols-2 lg:grid-cols-3">
                        <div
                            v-for="task in upcomingTasks.slice(0, 6)"
                            :key="task.id"
                            class="rounded-lg border border-blue-200 bg-white p-3 dark:border-blue-800 dark:bg-gray-800"
                        >
                            <div class="mb-1 flex items-start justify-between gap-2">
                                <h4 class="line-clamp-1 text-sm font-medium">{{ task.title }}</h4>
                                <Badge variant="outline" class="text-xs">{{ task.priority }}</Badge>
                            </div>
                            <div class="text-muted-foreground flex items-center justify-between text-xs">
                                <span>{{ task.list_name }}</span>
                                <span>{{ task.days_until_due }} days</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
