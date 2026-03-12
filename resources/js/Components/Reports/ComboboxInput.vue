<script setup>
import { ref, computed, watch, nextTick } from 'vue'
import { ChevronUpDownIcon, XMarkIcon } from '@heroicons/vue/24/solid'

const props = defineProps({
  modelValue: { type: String, default: '' },
  options: { type: Array, default: () => [] }, // [{ value, label }]
  placeholder: { type: String, default: 'Pesquisar...' },
  inputClass: { type: String, default: '' },
})

const emit = defineEmits(['update:modelValue'])

const query = ref(props.modelValue || '')
const open = ref(false)
const inputRef = ref(null)
const listRef = ref(null)
const highlightIndex = ref(-1)

watch(() => props.modelValue, (v) => {
  if (v !== query.value) query.value = v || ''
})

const filtered = computed(() => {
  if (!query.value) return props.options
  const term = query.value.toLowerCase()
  return props.options.filter(o =>
    o.label.toLowerCase().includes(term) || o.value.toLowerCase().includes(term)
  )
})

function onInput() {
  query.value = query.value.toUpperCase()
  open.value = true
  highlightIndex.value = -1
  emit('update:modelValue', query.value)
}

function select(opt) {
  query.value = opt.value
  emit('update:modelValue', opt.value)
  open.value = false
}

function clear() {
  query.value = ''
  emit('update:modelValue', '')
  inputRef.value?.focus()
}

function onFocus() {
  open.value = true
}

function onBlur() {
  // Delay to allow click on option
  setTimeout(() => { open.value = false }, 150)
}

function onKeydown(e) {
  if (!open.value) return

  if (e.key === 'ArrowDown') {
    e.preventDefault()
    highlightIndex.value = Math.min(highlightIndex.value + 1, filtered.value.length - 1)
    scrollToHighlighted()
  } else if (e.key === 'ArrowUp') {
    e.preventDefault()
    highlightIndex.value = Math.max(highlightIndex.value - 1, 0)
    scrollToHighlighted()
  } else if (e.key === 'Enter' && highlightIndex.value >= 0) {
    e.preventDefault()
    select(filtered.value[highlightIndex.value])
  } else if (e.key === 'Escape') {
    open.value = false
  }
}

function scrollToHighlighted() {
  nextTick(() => {
    const el = listRef.value?.children[highlightIndex.value]
    el?.scrollIntoView({ block: 'nearest' })
  })
}
</script>

<template>
  <div class="relative">
    <div class="relative">
      <input
        ref="inputRef"
        type="text"
        v-model="query"
        @input="onInput"
        @focus="onFocus"
        @blur="onBlur"
        @keydown="onKeydown"
        :placeholder="placeholder"
        :class="inputClass"
        autocomplete="off"
      />
      <div class="absolute inset-y-0 right-0 flex items-center pr-1.5 gap-0.5">
        <button
          v-if="query"
          type="button"
          @mousedown.prevent="clear"
          class="p-0.5 text-gray-400 hover:text-gray-600 transition-colors"
        >
          <XMarkIcon class="h-3.5 w-3.5" />
        </button>
        <ChevronUpDownIcon class="h-4 w-4 text-gray-400 pointer-events-none" />
      </div>
    </div>

    <!-- Dropdown -->
    <Transition
      enter-active-class="transition duration-100 ease-out"
      enter-from-class="opacity-0 -translate-y-1"
      enter-to-class="opacity-100 translate-y-0"
      leave-active-class="transition duration-75 ease-in"
      leave-from-class="opacity-100 translate-y-0"
      leave-to-class="opacity-0 -translate-y-1"
    >
      <ul
        v-if="open && filtered.length > 0"
        ref="listRef"
        class="absolute z-30 mt-1 max-h-48 w-full overflow-auto rounded-lg border border-gray-200 bg-white py-1 shadow-lg text-sm"
      >
        <li
          v-for="(opt, i) in filtered"
          :key="opt.value"
          @mousedown.prevent="select(opt)"
          class="cursor-pointer px-3 py-1.5 transition-colors"
          :class="i === highlightIndex ? 'bg-primary-50 text-primary' : 'text-gray-700 hover:bg-gray-50'"
        >
          {{ opt.label }}
        </li>
      </ul>
    </Transition>

    <div v-if="open && filtered.length === 0 && query" class="absolute z-30 mt-1 w-full rounded-lg border border-gray-200 bg-white py-3 text-center text-xs text-gray-400 shadow-lg">
      Nenhum resultado
    </div>
  </div>
</template>
