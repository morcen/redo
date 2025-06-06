<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';

import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Switch } from '@/components/ui/switch';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { type BreadcrumbItem, type Setting } from '@/types';
import { getUserTimezone } from '@/utils/timezone';

interface Props {
    settings: Setting;
    timezones: Record<string, string>;
    status?: string;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'General settings',
        href: '/settings/general',
    },
];

const form = useForm({
    timezone: props.settings.timezone,
    date_format: props.settings.date_format,
    time_format: props.settings.time_format,
    email_notifications: props.settings.email_notifications,
    browser_notifications: props.settings.browser_notifications,
});

const dateFormats = [
    { value: 'Y-m-d', label: 'YYYY-MM-DD (2024-01-15)' },
    { value: 'm/d/Y', label: 'MM/DD/YYYY (01/15/2024)' },
    { value: 'd/m/Y', label: 'DD/MM/YYYY (15/01/2024)' },
    { value: 'M j, Y', label: 'Month DD, YYYY (Jan 15, 2024)' },
    { value: 'F j, Y', label: 'Month DD, YYYY (January 15, 2024)' },
];

const timeFormats = [
    { value: 'H:i', label: '24-hour (14:30)' },
    { value: 'g:i A', label: '12-hour (2:30 PM)' },
    { value: 'g:i a', label: '12-hour (2:30 pm)' },
];

const commonTimezones = [
    { value: 'UTC', label: 'UTC (Coordinated Universal Time)' },
    { value: 'America/New_York', label: 'Eastern Time (US & Canada)' },
    { value: 'America/Chicago', label: 'Central Time (US & Canada)' },
    { value: 'America/Denver', label: 'Mountain Time (US & Canada)' },
    { value: 'America/Los_Angeles', label: 'Pacific Time (US & Canada)' },
    { value: 'Europe/London', label: 'London' },
    { value: 'Europe/Berlin', label: 'Berlin' },
    { value: 'Europe/Paris', label: 'Paris' },
    { value: 'Asia/Tokyo', label: 'Tokyo' },
    { value: 'Asia/Shanghai', label: 'Shanghai' },
    { value: 'Asia/Kolkata', label: 'Mumbai, Kolkata' },
    { value: 'Australia/Sydney', label: 'Sydney' },
];

const submit = () => {
    form.patch(route('settings.update'), {
        preserveScroll: true,
    });
};

const detectTimezone = () => {
    const detectedTimezone = getUserTimezone();
    if (detectedTimezone) {
        form.timezone = detectedTimezone;
    }
};


</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="General settings" />

        <SettingsLayout>
            <div class="flex flex-col space-y-6">
                <HeadingSmall 
                    title="General settings" 
                    description="Manage your timezone, date formats, and notification preferences" 
                />

                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Timezone Settings -->
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <Label for="timezone" class="text-base font-medium">Timezone</Label>
                            <Button 
                                type="button" 
                                variant="outline" 
                                size="sm" 
                                @click="detectTimezone"
                                class="text-xs"
                            >
                                Auto-detect
                            </Button>
                        </div>
                        <Select v-model="form.timezone">
                            <SelectTrigger>
                                <SelectValue placeholder="Select timezone" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem 
                                    v-for="timezone in commonTimezones" 
                                    :key="timezone.value" 
                                    :value="timezone.value"
                                >
                                    {{ timezone.label }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <InputError :message="form.errors.timezone" />
                    </div>

                    <!-- Date Format -->
                    <div class="space-y-2">
                        <Label for="date_format" class="text-base font-medium">Date Format</Label>
                        <Select v-model="form.date_format">
                            <SelectTrigger>
                                <SelectValue placeholder="Select date format" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem 
                                    v-for="format in dateFormats" 
                                    :key="format.value" 
                                    :value="format.value"
                                >
                                    {{ format.label }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <InputError :message="form.errors.date_format" />
                    </div>

                    <!-- Time Format -->
                    <div class="space-y-2">
                        <Label for="time_format" class="text-base font-medium">Time Format</Label>
                        <Select v-model="form.time_format">
                            <SelectTrigger>
                                <SelectValue placeholder="Select time format" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem 
                                    v-for="format in timeFormats" 
                                    :key="format.value" 
                                    :value="format.value"
                                >
                                    {{ format.label }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <InputError :message="form.errors.time_format" />
                    </div>

                    <!-- Notification Settings -->
                    <div class="space-y-4">
                        <h3 class="text-base font-medium">Notifications</h3>
                        
                        <div class="flex items-center justify-between">
                            <div class="space-y-0.5">
                                <Label for="email_notifications" class="text-sm font-medium">Email Notifications</Label>
                                <p class="text-sm text-muted-foreground">Receive email notifications for important updates</p>
                            </div>
                            <Switch 
                                id="email_notifications"
                                v-model:checked="form.email_notifications"
                            />
                        </div>

                        <div class="flex items-center justify-between">
                            <div class="space-y-0.5">
                                <Label for="browser_notifications" class="text-sm font-medium">Browser Notifications</Label>
                                <p class="text-sm text-muted-foreground">Receive browser notifications for reminders</p>
                            </div>
                            <Switch 
                                id="browser_notifications"
                                v-model:checked="form.browser_notifications"
                            />
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex items-center gap-4">
                        <Button type="submit" :disabled="form.processing">
                            {{ form.processing ? 'Saving...' : 'Save Settings' }}
                        </Button>
                        
                        <p v-if="form.recentlySuccessful" class="text-sm text-green-600">
                            Settings saved successfully.
                        </p>
                    </div>
                </form>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
