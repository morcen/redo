<script setup lang="ts">
import { computed } from 'vue'
import { cn } from '@/lib/utils'

interface Props {
  checked?: boolean
  disabled?: boolean
  id?: string
  name?: string
  class?: string
}

interface Emits {
  (e: 'update:checked', value: boolean): void
}

const props = withDefaults(defineProps<Props>(), {
  checked: false,
  disabled: false,
})

const emit = defineEmits<Emits>()

const handleChange = (event: Event) => {
  const target = event.target as HTMLInputElement
  emit('update:checked', target.checked)
}

const switchClasses = computed(() => 
  cn(
    'peer inline-flex h-6 w-11 shrink-0 cursor-pointer items-center rounded-full border-2 border-transparent transition-colors',
    'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:ring-offset-background',
    'disabled:cursor-not-allowed disabled:opacity-50',
    props.checked 
      ? 'bg-primary' 
      : 'bg-input',
    props.class
  )
)

const thumbClasses = computed(() =>
  cn(
    'pointer-events-none block h-5 w-5 rounded-full bg-background shadow-lg ring-0 transition-transform',
    props.checked ? 'translate-x-5' : 'translate-x-0'
  )
)
</script>

<template>
  <button
    type="button"
    role="switch"
    :aria-checked="checked"
    :disabled="disabled"
    :class="switchClasses"
    @click="handleChange({ target: { checked: !checked } } as Event)"
  >
    <span :class="thumbClasses" />
  </button>
  
  <!-- Hidden input for form submission -->
  <input
    v-if="name"
    type="hidden"
    :name="name"
    :value="checked"
  />
</template>
