/**
 * Timezone detection and management utilities
 */

/**
 * Get user's timezone using modern browser API
 */
export function getUserTimezone(): string {
    try {
        return Intl.DateTimeFormat().resolvedOptions().timeZone;
    } catch (error) {
        console.warn('Failed to detect timezone:', error);
        return 'UTC'; // fallback
    }
}

/**
 * Get timezone offset in minutes (for older browsers)
 */
export function getTimezoneOffset(): number {
    return new Date().getTimezoneOffset();
}

/**
 * Convert timezone offset to timezone name (approximate)
 */
export function offsetToTimezone(offset: number): string {
    const offsetHours = Math.abs(offset / 60);
    const sign = offset > 0 ? '-' : '+';
    
    // This is a simplified mapping - in production you'd want a more comprehensive one
    const timezoneMap: Record<string, string> = {
        '0': 'UTC',
        '+1': 'Europe/London',
        '+2': 'Europe/Berlin',
        '+5': 'America/New_York',
        '+6': 'America/Chicago',
        '+8': 'America/Los_Angeles',
        '-9': 'Asia/Tokyo',
        '-8': 'Asia/Shanghai',
    };
    
    return timezoneMap[`${sign}${offsetHours}`] || 'UTC';
}





/**
 * Format date according to user's timezone and preferences
 */
export function formatDateInTimezone(
    date: string | Date,
    timezone: string = 'UTC',
    options: Intl.DateTimeFormatOptions = {}
): string {
    try {
        const dateObj = typeof date === 'string' ? new Date(date) : date;
        
        return new Intl.DateTimeFormat('en-US', {
            timeZone: timezone,
            ...options,
        }).format(dateObj);
    } catch (error) {
        console.warn('Failed to format date in timezone:', error);
        return typeof date === 'string' ? date : date.toISOString();
    }
}

/**
 * Get list of common timezones for dropdown
 */
export function getCommonTimezones(): Array<{ value: string; label: string }> {
    return [
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
}
