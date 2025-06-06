<script setup lang="ts">
import { computed, ref } from 'vue'
import { Calendar } from '@/components/ui/calendar'
import { Button } from '@/components/ui/button'
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover'
import { CalendarIcon } from 'lucide-vue-next'
import { cn } from '@/lib/utils'
import { CalendarDate, today, getLocalTimeZone } from '@internationalized/date'
import type { DateValue } from '@internationalized/date'

interface Props {
  modelValue?: string | Date
  placeholder?: string
  disabled?: boolean
  class?: string
  availableDates?: string[]
}

interface Emits {
  (e: 'update:modelValue', value: string): void
}

const props = withDefaults(defineProps<Props>(), {
  placeholder: 'Pick a date',
  disabled: false,
})

const emit = defineEmits<Emits>()

const open = ref(false)

// Convert string/Date to CalendarDate for reka-ui
const stringToCalendarDate = (dateString: string): CalendarDate | undefined => {
  try {
    if (!dateString) return undefined
    // Parse YYYY-MM-DD format
    const match = dateString.match(/^(\d{4})-(\d{2})-(\d{2})/)
    if (match) {
      const [, year, month, day] = match
      return new CalendarDate(parseInt(year), parseInt(month), parseInt(day))
    }
    return undefined
  } catch (error) {
    console.warn('Error converting string to CalendarDate:', error)
    return undefined
  }
}

// Convert Date to CalendarDate for reka-ui
const dateToCalendarDate = (date: Date): CalendarDate | undefined => {
  try {
    if (!date || isNaN(date.getTime())) return undefined
    return new CalendarDate(date.getFullYear(), date.getMonth() + 1, date.getDate())
  } catch (error) {
    console.warn('Error converting Date to CalendarDate:', error)
    return undefined
  }
}

const selectedDate = computed((): DateValue | undefined => {
  if (!props.modelValue) return undefined
  try {
    if (typeof props.modelValue === 'string') {
      return stringToCalendarDate(props.modelValue)
    } else {
      return dateToCalendarDate(props.modelValue)
    }
  } catch (error) {
    console.warn('Invalid date value:', props.modelValue)
    return undefined
  }
})

// Convert CalendarDate back to string for emission
const calendarDateToString = (date: DateValue): string => {
  try {
    // DateValue objects have year, month, day properties
    const year = date.year
    const month = date.month.toString().padStart(2, '0')
    const day = date.day.toString().padStart(2, '0')
    return `${year}-${month}-${day}`
  } catch (error) {
    console.warn('Error converting CalendarDate to string:', error)
    return ''
  }
}

const handleDateSelect = (date: DateValue | undefined) => {
  if (date) {
    try {
      const dateString = calendarDateToString(date)
      if (dateString) {
        emit('update:modelValue', dateString)
        // Close the popover after a short delay to allow the update to process
        setTimeout(() => {
          open.value = false
        }, 100)
      }
    } catch (error) {
      console.warn('Error handling date selection:', error)
    }
  }
}

const formatDate = (dateValue: DateValue | undefined) => {
  if (!dateValue) return props.placeholder

  try {
    // Convert DateValue to Date for formatting
    const dateObj = new Date(dateValue.year, dateValue.month - 1, dateValue.day)

    if (isNaN(dateObj.getTime())) {
      return props.placeholder
    }

    const today = new Date()
    today.setHours(0, 0, 0, 0)

    const tomorrow = new Date(today)
    tomorrow.setDate(tomorrow.getDate() + 1)

    const yesterday = new Date(today)
    yesterday.setDate(yesterday.getDate() - 1)

    const dateToCheck = new Date(dateObj)
    dateToCheck.setHours(0, 0, 0, 0)

    if (dateToCheck.getTime() === today.getTime()) return 'Today'
    if (dateToCheck.getTime() === tomorrow.getTime()) return 'Tomorrow'
    if (dateToCheck.getTime() === yesterday.getTime()) return 'Yesterday'

    return dateObj.toLocaleDateString(undefined, {
      weekday: 'long',
      month: 'long',
      day: 'numeric'
    })
  } catch (error) {
    console.warn('Error formatting date:', error)
    return props.placeholder
  }
}

// Allow all dates to be clickable
const isDateDisabled = () => {
  // Always return false to make all dates clickable
  return false
}

// Helper functions for shortcuts
const getTodayDate = (): CalendarDate => {
  const todayDate = today(getLocalTimeZone())
  return todayDate
}

const getYesterdayDate = (): CalendarDate => {
  const todayDate = today(getLocalTimeZone())
  return todayDate.subtract({ days: 1 })
}

// Handle shortcut button clicks
const handleShortcutClick = (date: CalendarDate) => {
  try {
    handleDateSelect(date)
  } catch (error) {
    console.warn('Error handling shortcut click:', error)
  }
}
</script>

<template>
  <Popover v-model:open="open">
    <PopoverTrigger as-child>
      <Button
        variant="outline"
        :class="cn(
          'w-full justify-start text-left font-normal',
          !selectedDate && 'text-muted-foreground',
          props.class
        )"
        :disabled="disabled"
      >
        <CalendarIcon class="mr-2 h-4 w-4" />
        {{ formatDate(selectedDate) }}
      </Button>
    </PopoverTrigger>
    <PopoverContent class="w-auto p-0" align="start">
      <div class="p-0">
        <!-- Shortcut buttons -->
        <div class="flex gap-2 p-3 border-b">
          <Button
            variant="outline"
            size="sm"
            @click="handleShortcutClick(getTodayDate())"
            class="text-xs"
          >
            Today
          </Button>
          <Button
            variant="outline"
            size="sm"
            @click="handleShortcutClick(getYesterdayDate())"
            class="text-xs"
          >
            Yesterday
          </Button>
        </div>

        <Calendar
          v-if="open"
          :model-value="selectedDate"
          @update:model-value="handleDateSelect"
          :is-date-disabled="isDateDisabled"
          initial-focus
        />
      </div>
    </PopoverContent>
  </Popover>
</template>
