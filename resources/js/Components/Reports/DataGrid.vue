<script setup>
import { CheckIcon } from '@heroicons/vue/24/solid'
import { ref, computed } from 'vue'

const props = defineProps({
  data: { type: Array, required: true },
  documentos: { type: Object, required: true },
})

const emit = defineEmits(['document-click'])

const selectedRows = ref(new Set())

const docEntries = computed(() => Object.entries(props.documentos))

function isAvailable(row, docCode) {
  return row[`ies_${docCode.toLowerCase()}`] === 'S'
}

function toggleRow(index) {
  if (selectedRows.value.has(index)) {
    selectedRows.value.delete(index)
  } else {
    selectedRows.value.add(index)
  }
  // Force reactivity
  selectedRows.value = new Set(selectedRows.value)
}

function toggleAll() {
  if (selectedRows.value.size === props.data.length) {
    selectedRows.value = new Set()
  } else {
    selectedRows.value = new Set(props.data.map((_, i) => i))
  }
}

const allSelected = computed(() => props.data.length > 0 && selectedRows.value.size === props.data.length)

function handleDocClick(docCode) {
  const rows = []

  // If rows selected, use selected rows; otherwise use all
  const indices = selectedRows.value.size > 0
    ? [...selectedRows.value]
    : props.data.map((_, i) => i)

  for (const i of indices) {
    const row = props.data[i]
    if (isAvailable(row, docCode)) {
      rows.push({
        empresa: String(row.cod_empresa),
        processo: String(row.num_processo),
        embarque: String(row.num_embarque),
        ano: String(row.ano_processo),
      })
    }
  }

  if (rows.length === 0) return

  emit('document-click', { docCode, rows })
}

function getSelectedRowKeys() {
  return [...selectedRows.value].map(i => {
    const row = props.data[i]
    return {
      empresa: String(row.cod_empresa),
      processo: String(row.num_processo),
      embarque: String(row.num_embarque),
      ano: String(row.ano_processo),
    }
  })
}

defineExpose({ getSelectedRowKeys })

/*
 * Sticky column positions (px):
 * Checkbox:  w=36   left=0
 * EMP:       w=48   left=36
 * PROCESSO:  w=76   left=84
 * EMB:       w=44   left=160
 * ANO:       w=52   left=204
 * SITUAÇÃO:  w=72   left=256   (last sticky, has shadow)
 */
</script>

<template>
  <div class="overflow-x-auto rounded-xl border border-gray-200">
    <table class="min-w-full text-xs">
      <thead>
        <tr class="bg-primary text-white">
          <!-- Sticky columns -->
          <th class="sticky left-0 z-20 w-[36px] min-w-[36px] bg-primary px-2 py-2.5 text-center">
            <input
              type="checkbox"
              :checked="allSelected"
              @change="toggleAll"
              class="h-3.5 w-3.5 rounded border-gray-300 text-primary focus:ring-primary cursor-pointer"
            />
          </th>
          <th class="sticky left-[36px] z-20 w-[48px] min-w-[48px] bg-primary px-2 py-2.5 text-center font-semibold whitespace-nowrap">EMP</th>
          <th class="sticky left-[84px] z-20 w-[76px] min-w-[76px] bg-primary px-2 py-2.5 text-center font-semibold whitespace-nowrap">PROCESSO</th>
          <th class="sticky left-[160px] z-20 w-[44px] min-w-[44px] bg-primary px-2 py-2.5 text-center font-semibold whitespace-nowrap">EMB</th>
          <th class="sticky left-[204px] z-20 w-[52px] min-w-[52px] bg-primary px-2 py-2.5 text-center font-semibold whitespace-nowrap">ANO</th>
          <th class="sticky left-[256px] z-20 w-[72px] min-w-[72px] bg-primary px-2 py-2.5 text-center font-semibold whitespace-nowrap shadow-[2px_0_5px_-2px_rgba(0,0,0,0.15)]">SITUAÇÃO</th>
          <!-- Document columns (scrollable) -->
          <th
            v-for="[code, doc] in docEntries"
            :key="code"
            class="bg-primary px-2 py-2.5 text-center font-semibold whitespace-nowrap"
            :title="doc.label"
          >
            {{ doc.short_label || code }}
          </th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        <tr
          v-for="(row, i) in data"
          :key="i"
          class="bg-white transition-colors hover:bg-primary-50/40"
          :class="{ '!bg-primary-50/60': selectedRows.has(i) }"
        >
          <!-- Sticky data columns -->
          <td class="sticky left-0 z-10 bg-inherit w-[36px] min-w-[36px] px-2 py-2 text-center">
            <input
              type="checkbox"
              :checked="selectedRows.has(i)"
              @change="toggleRow(i)"
              class="h-3.5 w-3.5 rounded border-gray-300 text-primary focus:ring-primary cursor-pointer"
            />
          </td>
          <td class="sticky left-[36px] z-10 bg-inherit w-[48px] min-w-[48px] px-2 py-2 text-center font-medium text-gray-900 whitespace-nowrap">{{ row.cod_empresa }}</td>
          <td class="sticky left-[84px] z-10 bg-inherit w-[76px] min-w-[76px] px-2 py-2 text-center text-gray-700 whitespace-nowrap">{{ row.num_processo }}</td>
          <td class="sticky left-[160px] z-10 bg-inherit w-[44px] min-w-[44px] px-2 py-2 text-center text-gray-700 whitespace-nowrap">{{ row.num_embarque }}</td>
          <td class="sticky left-[204px] z-10 bg-inherit w-[52px] min-w-[52px] px-2 py-2 text-center text-gray-700 whitespace-nowrap">{{ row.ano_processo }}</td>
          <td class="sticky left-[256px] z-10 bg-inherit w-[72px] min-w-[72px] px-2 py-2 text-center text-gray-700 whitespace-nowrap shadow-[2px_0_5px_-2px_rgba(0,0,0,0.08)]">{{ row.cod_situacao }}</td>
          <!-- Document icons (scrollable) -->
          <td
            v-for="[code] in docEntries"
            :key="code"
            class="px-2 py-2 text-center"
          >
            <button
              v-if="isAvailable(row, code)"
              @click="emit('document-click', { docCode: code, rows: [{ empresa: String(row.cod_empresa), processo: String(row.num_processo), embarque: String(row.num_embarque), ano: String(row.ano_processo) }] })"
              class="inline-flex h-6 w-6 items-center justify-center rounded-md bg-green-50 text-green-600 transition-all hover:bg-green-200 hover:text-green-700 hover:scale-110"
              :title="documentos[code].label"
            >
              <CheckIcon class="h-3.5 w-3.5" />
            </button>
            <span v-else class="text-gray-300">—</span>
          </td>
        </tr>
      </tbody>
    </table>

    <!-- Empty state -->
    <div v-if="data.length === 0" class="py-12 text-center text-gray-400 text-sm">
      Nenhum processo encontrado.
    </div>
  </div>

  <!-- Selection info -->
  <div v-if="selectedRows.size > 0" class="mt-3 flex items-center gap-3 text-sm text-gray-600">
    <span class="font-medium">{{ selectedRows.size }} embarque(s) selecionado(s)</span>
    <span class="text-gray-400">|</span>
    <span class="text-gray-500">Clique em um documento no cabeçalho para gerar PDF dos selecionados</span>
  </div>

  <!-- Batch document buttons (when rows selected) -->
  <div v-if="selectedRows.size > 1" class="mt-3 flex flex-wrap gap-2">
    <button
      v-for="[code, doc] in docEntries"
      :key="code"
      @click="handleDocClick(code)"
      class="inline-flex items-center gap-1 rounded-lg border border-gray-200 px-2.5 py-1.5 text-xs font-medium text-gray-700 transition-colors hover:bg-primary-50 hover:border-primary-200 hover:text-primary"
      :title="doc.label"
    >
      <CheckIcon class="h-3 w-3 text-green-500" />
      {{ doc.short_label || code }}
    </button>
  </div>
</template>
