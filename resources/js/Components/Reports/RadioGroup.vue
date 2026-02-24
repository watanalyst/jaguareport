<script setup>
const props = defineProps({
  modelValue: { type: String, default: '' },
  options: { type: Object, required: true },
  name: { type: String, required: true },
})

const emit = defineEmits(['update:modelValue'])
</script>

<template>
  <div class="inline-flex rounded-lg border border-gray-200 bg-gray-100 p-0.5">
    <button
      v-for="(label, value) in options"
      :key="value"
      type="button"
      :class="[
        'px-3 py-1.5 text-sm font-medium rounded-md transition-all duration-200',
        modelValue === value
          ? 'bg-white text-primary shadow-sm'
          : 'text-gray-500 hover:text-gray-700'
      ]"
      @click="emit('update:modelValue', value)"
    >
      {{ label }}
    </button>
  </div>
</template>
