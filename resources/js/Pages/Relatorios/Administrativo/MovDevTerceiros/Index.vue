<script setup>
import { reactive, ref, computed } from 'vue'
import ReportPageLayout from '@/Components/Reports/ReportPageLayout.vue'
import FilterPanel from '@/Components/Reports/FilterPanel.vue'
import { GenericDataGrid } from '@desenvolvimento/btz-components-vue'
import { ExclamationTriangleIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  title: String,
  section: String,
  filters: Array,
  empresas: { type: Array, default: () => [] },
  columns: { type: Array, required: true },
})

const form = reactive({})
props.filters.forEach(f => { form[f.name] = '' })

const lookups = computed(() => ({
  cod_empresa: props.empresas.map(e => ({
    value: e.ep,
    label: `${e.ep} - ${e.den_reduz}`,
  })),
}))

const gridData = ref([])
const searching = ref(false)
const searchError = ref('')
const hasSearched = ref(false)

// Campos agrupáveis (não numéricos)
const groupableColumns = computed(() =>
  props.columns.filter(c => !['number', 'currency'].includes(c.type))
)
const sumFields = computed(() =>
  props.columns.filter(c => c.type === 'currency').map(c => c.key)
)

// Grid mode
const gridMode = ref('normal')
const selectedGroupBy = ref([])
const showGroupPanel = ref(false)

const activeGroupBy = computed(() => gridMode.value === 'normal' ? [] : selectedGroupBy.value)
const activeGroupMode = computed(() => gridMode.value === 'quebra' ? 'quebra' : 'resumo')

function toggleGroupColumn(key) {
  const idx = selectedGroupBy.value.indexOf(key)
  if (idx >= 0) selectedGroupBy.value.splice(idx, 1)
  else selectedGroupBy.value.push(key)
}

function moveUp(idx) {
  if (idx <= 0) return
  const arr = selectedGroupBy.value
  ;[arr[idx - 1], arr[idx]] = [arr[idx], arr[idx - 1]]
}

function moveDown(idx) {
  const arr = selectedGroupBy.value
  if (idx >= arr.length - 1) return
  ;[arr[idx], arr[idx + 1]] = [arr[idx + 1], arr[idx]]
}

function setMode(mode) {
  if (mode === 'normal') {
    gridMode.value = 'normal'
    showGroupPanel.value = false
  } else {
    gridMode.value = mode
    if (!selectedGroupBy.value.length) {
      showGroupPanel.value = true
    }
  }
}

function clearGrouping() {
  selectedGroupBy.value = []
  gridMode.value = 'normal'
  showGroupPanel.value = false
}

async function handleSearch(values) {
  searching.value = true
  searchError.value = ''
  hasSearched.value = true
  gridData.value = []

  const params = new URLSearchParams()
  Object.entries(values).forEach(([k, v]) => {
    if (v !== '' && v !== null && v !== undefined) params.append(k, v)
  })

  try {
    const response = await fetch(
      `${route('relatorios.administrativo.mov_dev_terceiros.pesquisar')}?${params.toString()}`,
      { headers: { 'X-Requested-With': 'XMLHttpRequest' } }
    )

    if (!response.ok) {
      let msg = 'Erro ao pesquisar.'
      try {
        const json = await response.json()
        if (json.message) msg = json.message
      } catch {}
      searchError.value = msg
      return
    }

    const json = await response.json()
    gridData.value = json.data
  } catch {
    searchError.value = 'Erro de conexão. Verifique sua rede e tente novamente.'
  } finally {
    searching.value = false
  }
}
</script>

<template>
  <ReportPageLayout :title="title" :section="section">
    <FilterPanel
      :filters="filters"
      :form="form"
      mode="search"
      :lookups="lookups"
      @search="handleSearch"
    />

    <div v-if="searchError" class="mt-6 flex items-center gap-3 rounded-xl bg-red-50 px-12 py-3">
      <ExclamationTriangleIcon class="h-5 w-5 flex-shrink-0 text-red-500" />
      <p class="text-sm text-red-700">{{ searchError }}</p>
    </div>

    <div v-if="searching" class="mt-8 flex items-center justify-center gap-3 text-gray-500">
      <svg class="h-5 w-5 animate-spin text-primary" viewBox="0 0 24 24" fill="none">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" />
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
      </svg>
      <span class="text-sm">Pesquisando...</span>
    </div>

    <div v-if="!searching && gridData.length > 0" class="mt-6">
      <!-- Mode selector -->
      <div class="mb-3 flex items-center gap-3 flex-wrap">
        <div class="inline-flex rounded-lg border border-gray-200 bg-white p-0.5">
          <button
            v-for="mode in [{ key: 'normal', label: 'Detalhado' }, { key: 'resumo', label: 'Resumo' }, { key: 'quebra', label: 'Quebra' }]"
            :key="mode.key"
            type="button"
            @click="setMode(mode.key)"
            class="px-3 py-1.5 text-xs font-semibold rounded-md transition-all"
            :class="gridMode === mode.key ? 'bg-blue-600 text-white shadow-sm' : 'text-gray-600 hover:text-gray-800 hover:bg-gray-50'"
          >
            {{ mode.label }}
          </button>
        </div>

        <button v-if="selectedGroupBy.length || gridMode !== 'normal'" type="button" @click="clearGrouping"
          class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold rounded-lg border border-red-200 bg-white text-red-600 hover:bg-red-50 transition-colors">
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
          Limpar
        </button>
      </div>

      <!-- Group fields panel -->
      <div v-if="showGroupPanel && gridMode !== 'normal'" class="mb-4 rounded-lg border border-gray-200 bg-white p-3 shadow-sm">
        <p class="text-xs font-semibold text-gray-500 mb-2">Selecione os campos para agrupar (clique para adicionar/remover):</p>
        <div class="flex flex-wrap gap-2">
          <button
            v-for="col in groupableColumns"
            :key="col.key"
            type="button"
            @click="toggleGroupColumn(col.key)"
            class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium rounded-md border transition-colors"
            :class="selectedGroupBy.includes(col.key) ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-600 border-gray-300 hover:border-blue-400 hover:text-blue-600'"
          >
            {{ col.label }}
            <span v-if="selectedGroupBy.includes(col.key)" class="ml-0.5 text-blue-200 font-bold">{{ selectedGroupBy.indexOf(col.key) + 1 }}</span>
          </button>
        </div>
        <div v-if="selectedGroupBy.length > 1" class="mt-3 flex items-center gap-2">
          <span class="text-xs text-gray-400">Ordem:</span>
          <div v-for="(key, idx) in selectedGroupBy" :key="key" class="inline-flex items-center gap-0.5 text-xs">
            <span class="font-semibold text-blue-700">{{ groupableColumns.find(c => c.key === key)?.label }}</span>
            <button v-if="idx > 0" @click="moveUp(idx)" class="text-gray-400 hover:text-blue-600">↑</button>
            <button v-if="idx < selectedGroupBy.length - 1" @click="moveDown(idx)" class="text-gray-400 hover:text-blue-600">↓</button>
            <span v-if="idx < selectedGroupBy.length - 1" class="mx-1 text-gray-300">›</span>
          </div>
        </div>
      </div>

      <GenericDataGrid
        :columns="columns"
        :data="gridData"
        :page-size="100"
        export-filename="mov-dev-terceiros"
        :group-by="activeGroupBy"
        :sum-columns="sumFields"
        :group-mode="activeGroupMode"
        :show-group-toggle="false"
        max-height="70vh"
      />
    </div>

    <div v-if="!searching && hasSearched && gridData.length === 0 && !searchError" class="mt-8 text-center text-gray-400 text-sm">
      Nenhum registro encontrado para os filtros informados.
    </div>
  </ReportPageLayout>
</template>
