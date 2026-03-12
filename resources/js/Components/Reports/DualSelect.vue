<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
  modelValue: { type: String, default: '' },
  options: { type: Array, default: () => [] },
})

const emit = defineEmits(['update:modelValue'])

const leftRef = ref(null)
const rightRef = ref(null)

const selected = computed(() => {
  if (!props.modelValue) return new Set()
  return new Set(props.modelValue.split(',').filter(Boolean))
})

const available = computed(() =>
  props.options.filter(o => !selected.value.has(o.value))
)
const chosen = computed(() =>
  props.options.filter(o => selected.value.has(o.value))
)

function emitUpdate(newSet) {
  emit('update:modelValue', [...newSet].join(','))
}

function getSelected(selectEl) {
  if (!selectEl) return []
  return Array.from(selectEl.selectedOptions).map(o => o.value)
}

function moveRight() {
  const vals = getSelected(leftRef.value)
  if (!vals.length) return
  const s = new Set(selected.value)
  vals.forEach(v => s.add(v))
  emitUpdate(s)
}

function moveAllRight() {
  emitUpdate(new Set(props.options.map(o => o.value)))
}

function moveLeft() {
  const vals = getSelected(rightRef.value)
  if (!vals.length) return
  const s = new Set(selected.value)
  vals.forEach(v => s.delete(v))
  emitUpdate(s)
}

function moveAllLeft() {
  emitUpdate(new Set())
}

function dblLeft(e) {
  const val = e.target.value
  if (!val) return
  const s = new Set(selected.value)
  s.add(val)
  emitUpdate(s)
}

function dblRight(e) {
  const val = e.target.value
  if (!val) return
  const s = new Set(selected.value)
  s.delete(val)
  emitUpdate(s)
}
</script>

<template>
  <div class="grid items-center gap-1.5" style="grid-template-columns: 1fr 36px 1fr;">
    <!-- Available -->
    <div>
      <div class="mb-1 text-[10px] font-semibold text-gray-500 uppercase tracking-wider">Disponíveis</div>
      <select
        ref="leftRef"
        multiple
        @dblclick="dblLeft"
        class="w-full h-28 rounded-lg border border-gray-300 bg-white px-2 py-2 text-sm text-gray-700 focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
      >
        <option v-for="opt in available" :key="opt.value" :value="opt.value">
          {{ opt.label }}
        </option>
      </select>
    </div>

    <!-- Buttons -->
    <div class="flex flex-col items-center gap-1 pt-3">
      <button type="button" @click="moveAllRight" :disabled="!available.length" title="Adicionar todas"
        class="flex items-center justify-center w-7 h-6 rounded border border-gray-300 bg-gray-50 text-gray-500 font-bold text-[10px] leading-none cursor-pointer transition-all hover:bg-primary hover:text-white hover:border-primary disabled:opacity-25 disabled:cursor-default disabled:hover:bg-gray-50 disabled:hover:text-gray-500 disabled:hover:border-gray-300"
      >▶▶</button>
      <button type="button" @click="moveRight" title="Adicionar"
        class="flex items-center justify-center w-7 h-6 rounded border border-gray-300 bg-gray-50 text-gray-500 font-bold text-[10px] leading-none cursor-pointer transition-all hover:bg-primary hover:text-white hover:border-primary"
      >▶</button>
      <button type="button" @click="moveLeft" title="Remover"
        class="flex items-center justify-center w-7 h-6 rounded border border-gray-300 bg-gray-50 text-gray-500 font-bold text-[10px] leading-none cursor-pointer transition-all hover:bg-primary hover:text-white hover:border-primary"
      >◀</button>
      <button type="button" @click="moveAllLeft" :disabled="!chosen.length" title="Remover todas"
        class="flex items-center justify-center w-7 h-6 rounded border border-gray-300 bg-gray-50 text-gray-500 font-bold text-[10px] leading-none cursor-pointer transition-all hover:bg-primary hover:text-white hover:border-primary disabled:opacity-25 disabled:cursor-default disabled:hover:bg-gray-50 disabled:hover:text-gray-500 disabled:hover:border-gray-300"
      >◀◀</button>
    </div>

    <!-- Selected -->
    <div>
      <div class="mb-1 text-[10px] font-semibold text-gray-500 uppercase tracking-wider">Selecionados</div>
      <select
        ref="rightRef"
        multiple
        @dblclick="dblRight"
        class="w-full h-28 rounded-lg border border-gray-300 bg-white px-2 py-1 text-xs text-gray-700 focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
      >
        <option v-for="opt in chosen" :key="opt.value" :value="opt.value">
          {{ opt.label }}
        </option>
      </select>
    </div>
  </div>
</template>
