<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import Icon from '@/components/Icon.vue';
import { Badge } from '@/components/ui/badge';
import { Card, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Welcome to Re:do">
        <meta
            name="description"
            content="Re:do - A smart todo list application with daily refresh functionality, hierarchical organization, and timezone-aware task management."
        />
    </Head>

    <div class="from-background via-background to-muted/20 min-h-screen bg-gradient-to-br">
        <!-- Navigation Header -->
        <header class="border-border/40 bg-background/80 relative z-10 w-full border-b backdrop-blur-sm">
            <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
                <div class="flex items-center gap-2">
                    <AppLogoIcon class="text-foreground h-8 w-8 fill-current" />
                    <span class="text-foreground text-xl font-semibold">Re:do</span>
                </div>

                <nav class="flex items-center gap-4">
                    <Link
                        v-if="$page.props.auth.user"
                        :href="route('dashboard')"
                        class="bg-primary text-primary-foreground hover:bg-primary/90 focus-visible:ring-ring inline-flex items-center justify-center rounded-md px-4 py-2 text-sm font-medium shadow transition-colors focus-visible:ring-1 focus-visible:outline-none"
                    >
                        Go to Dashboard
                    </Link>
                    <template v-else>
                        <Link
                            :href="route('login')"
                            class="text-foreground hover:bg-accent hover:text-accent-foreground focus-visible:ring-ring inline-flex items-center justify-center rounded-md px-4 py-2 text-sm font-medium transition-colors focus-visible:ring-1 focus-visible:outline-none"
                        >
                            Log in
                        </Link>
                        <Link
                            :href="route('register')"
                            class="bg-primary text-primary-foreground hover:bg-primary/90 focus-visible:ring-ring inline-flex items-center justify-center rounded-md px-4 py-2 text-sm font-medium shadow transition-colors focus-visible:ring-1 focus-visible:outline-none"
                        >
                            Get Started
                        </Link>
                    </template>
                </nav>
            </div>
        </header>

        <!-- Hero Section -->
        <main class="relative">
            <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 sm:py-24 lg:px-8 lg:py-32">
                <div class="text-center">
                    <div class="mb-8 flex justify-center">
                        <Badge variant="secondary" class="px-3 py-1 text-sm">
                            <Icon name="sparkles" class="mr-2 h-4 w-4" />
                            Smart Todo Management
                        </Badge>
                    </div>

                    <h1 class="text-foreground mx-auto max-w-4xl text-4xl font-bold tracking-tight sm:text-5xl lg:text-6xl">
                        Organize your tasks with
                        <span class="from-primary to-primary/80 bg-gradient-to-r bg-clip-text text-transparent"> Re:do </span>
                    </h1>

                    <p class="text-muted-foreground mx-auto mt-6 max-w-2xl text-lg leading-8">
                        A smart todo list application with daily refresh functionality, hierarchical organization, and timezone-aware task management.
                        Stay organized and productive with intelligent task recreation.
                    </p>

                    <div class="mt-10 flex items-center justify-center gap-4">
                        <Link
                            v-if="!$page.props.auth.user"
                            :href="route('register')"
                            class="bg-primary text-primary-foreground hover:bg-primary/90 focus-visible:ring-ring inline-flex items-center justify-center rounded-md px-6 py-3 text-base font-medium shadow-sm transition-colors focus-visible:ring-1 focus-visible:outline-none"
                        >
                            <Icon name="arrow-right" class="mr-2 h-4 w-4" />
                            Get Started Free
                        </Link>
                        <Link
                            v-else
                            :href="route('dashboard')"
                            class="bg-primary text-primary-foreground hover:bg-primary/90 focus-visible:ring-ring inline-flex items-center justify-center rounded-md px-6 py-3 text-base font-medium shadow-sm transition-colors focus-visible:ring-1 focus-visible:outline-none"
                        >
                            <Icon name="arrow-right" class="mr-2 h-4 w-4" />
                            Go to Dashboard
                        </Link>
                        <Link
                            v-if="!$page.props.auth.user"
                            :href="route('login')"
                            class="border-input bg-background text-foreground hover:bg-accent hover:text-accent-foreground focus-visible:ring-ring inline-flex items-center justify-center rounded-md border px-6 py-3 text-base font-medium shadow-sm transition-colors focus-visible:ring-1 focus-visible:outline-none"
                        >
                            Sign In
                        </Link>
                    </div>
                </div>
            </div>
        </main>

        <!-- Features Section -->
        <section class="relative py-16 sm:py-24">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <h2 class="text-foreground text-3xl font-bold tracking-tight sm:text-4xl">Everything you need to stay organized</h2>
                    <p class="text-muted-foreground mx-auto mt-4 max-w-2xl text-lg">
                        Re:do combines powerful features with intelligent automation to help you manage your tasks effortlessly.
                    </p>
                </div>

                <div class="mt-16 grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                    <!-- Hierarchical Organization -->
                    <Card class="relative overflow-hidden">
                        <CardHeader>
                            <div class="bg-primary/10 flex h-12 w-12 items-center justify-center rounded-lg">
                                <Icon name="folder-tree" class="text-primary h-6 w-6" />
                            </div>
                            <CardTitle class="text-xl">Hierarchical Lists</CardTitle>
                            <CardDescription> Organize your todos in nested lists with clear ownership and inheritance patterns. </CardDescription>
                        </CardHeader>
                    </Card>

                    <!-- Daily Refresh -->
                    <Card class="relative overflow-hidden">
                        <CardHeader>
                            <div class="bg-primary/10 flex h-12 w-12 items-center justify-center rounded-lg">
                                <Icon name="refresh-cw" class="text-primary h-6 w-6" />
                            </div>
                            <CardTitle class="text-xl">Daily Refresh</CardTitle>
                            <CardDescription>
                                Automatically recreate your daily tasks based on your timezone settings for consistent routines.
                            </CardDescription>
                        </CardHeader>
                    </Card>

                    <!-- Date Filtering -->
                    <Card class="relative overflow-hidden">
                        <CardHeader>
                            <div class="bg-primary/10 flex h-12 w-12 items-center justify-center rounded-lg">
                                <Icon name="calendar" class="text-primary h-6 w-6" />
                            </div>
                            <CardTitle class="text-xl">Smart Filtering</CardTitle>
                            <CardDescription>
                                Filter tasks by date with intuitive calendar components and quick shortcuts like 'Today' and 'Yesterday'.
                            </CardDescription>
                        </CardHeader>
                    </Card>

                    <!-- Completion Tracking -->
                    <Card class="relative overflow-hidden">
                        <CardHeader>
                            <div class="bg-primary/10 flex h-12 w-12 items-center justify-center rounded-lg">
                                <Icon name="check-circle" class="text-primary h-6 w-6" />
                            </div>
                            <CardTitle class="text-xl">Completion Tracking</CardTitle>
                            <CardDescription>
                                Track completion with timestamps, not just checkboxes. See exactly when tasks were completed.
                            </CardDescription>
                        </CardHeader>
                    </Card>

                    <!-- Timezone Aware -->
                    <Card class="relative overflow-hidden">
                        <CardHeader>
                            <div class="bg-primary/10 flex h-12 w-12 items-center justify-center rounded-lg">
                                <Icon name="globe" class="text-primary h-6 w-6" />
                            </div>
                            <CardTitle class="text-xl">Timezone Aware</CardTitle>
                            <CardDescription>
                                Intelligent task management that respects your timezone for accurate daily task recreation.
                            </CardDescription>
                        </CardHeader>
                    </Card>

                    <!-- Progress Insights -->
                    <Card class="relative overflow-hidden">
                        <CardHeader>
                            <div class="bg-primary/10 flex h-12 w-12 items-center justify-center rounded-lg">
                                <Icon name="bar-chart-3" class="text-primary h-6 w-6" />
                            </div>
                            <CardTitle class="text-xl">Progress Insights</CardTitle>
                            <CardDescription>
                                View completion percentages and progress metrics calculated based on your filtered tasks.
                            </CardDescription>
                        </CardHeader>
                    </Card>
                </div>
            </div>
        </section>

        <!-- Call to Action Section -->
        <section class="relative py-16 sm:py-24">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="bg-primary relative overflow-hidden rounded-2xl px-6 py-16 text-center shadow-2xl sm:px-16">
                    <div class="from-primary to-primary/80 absolute inset-0 bg-gradient-to-br" />
                    <div class="relative">
                        <h2 class="text-primary-foreground text-3xl font-bold tracking-tight sm:text-4xl">Ready to get organized?</h2>
                        <p class="text-primary-foreground/90 mx-auto mt-4 max-w-2xl text-lg">
                            Join thousands of users who have transformed their productivity with Re:do's intelligent task management.
                        </p>
                        <div class="mt-8 flex flex-col items-center justify-center gap-4 sm:flex-row">
                            <Link
                                v-if="!$page.props.auth.user"
                                :href="route('register')"
                                class="bg-background text-foreground hover:bg-background/90 focus-visible:ring-ring inline-flex items-center justify-center rounded-md px-6 py-3 text-base font-medium shadow-sm transition-colors focus-visible:ring-1 focus-visible:outline-none"
                            >
                                <Icon name="arrow-right" class="mr-2 h-4 w-4" />
                                Start Free Today
                            </Link>
                            <Link
                                v-else
                                :href="route('dashboard')"
                                class="bg-background text-foreground hover:bg-background/90 focus-visible:ring-ring inline-flex items-center justify-center rounded-md px-6 py-3 text-base font-medium shadow-sm transition-colors focus-visible:ring-1 focus-visible:outline-none"
                            >
                                <Icon name="arrow-right" class="mr-2 h-4 w-4" />
                                Go to Dashboard
                            </Link>
                            <span class="text-primary-foreground/80 text-sm"> No credit card required </span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="border-border/40 bg-background/50 border-t backdrop-blur-sm">
            <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                <div class="flex flex-col items-center justify-between gap-4 sm:flex-row">
                    <div class="flex items-center gap-2">
                        <AppLogoIcon class="text-foreground h-6 w-6 fill-current" />
                        <span class="text-foreground text-lg font-semibold">Re:do</span>
                    </div>
                    <p class="text-muted-foreground text-sm">© {{ new Date().getFullYear() }} Re:do. Built with care for productivity.</p>
                </div>
            </div>
        </footer>
    </div>
</template>
