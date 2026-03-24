<script setup>
import { reactive, ref, computed, watch } from 'vue'
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

// Empresa dual-select options
const empresaLookup = computed(() =>
  props.empresas.map(e => ({
    value: e.ep,
    label: `${e.ep} - ${e.den_reduz}`,
  }))
)

// COD_ITEM autocomplete options (loaded on empresa change)
const itemOptions = ref([])
const loadingItems = ref(false)

async function fetchItems(empresasCsv) {
  if (!empresasCsv) {
    itemOptions.value = []
    return
  }

  loadingItems.value = true
  try {
    // Fetch items for each selected empresa and merge results
    const empresas = empresasCsv.split(',').filter(Boolean)
    const allItems = []
    const seen = new Set()

    for (const emp of empresas) {
      const response = await fetch(
        `${route('relatorios.exportacao.embarques_exportacao.items')}?empresa=${emp}`,
        { headers: { 'X-Requested-With': 'XMLHttpRequest' } }
      )
      if (response.ok) {
        const items = await response.json()
        for (const item of items) {
          if (!seen.has(item.value)) {
            seen.add(item.value)
            allItems.push(item)
          }
        }
      }
    }

    allItems.sort((a, b) => a.value.localeCompare(b.value))
    itemOptions.value = allItems
  } catch {
    itemOptions.value = []
  } finally {
    loadingItems.value = false
  }
}

// Watch empresa changes to reload items
watch(() => form.empresa, (newVal) => {
  form.cod_item = ''
  fetchItems(newVal)
})

const lookups = computed(() => ({
  empresa: empresaLookup.value,
  cod_item: itemOptions.value,
}))

// Grid state
const gridData = ref([])
const searching = ref(false)
const searchError = ref('')
const hasSearched = ref(false)

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
      `${route('relatorios.exportacao.embarques_exportacao.pesquisar')}?${params.toString()}`,
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
    <!-- Filters -->
    <FilterPanel
      :filters="filters"
      :form="form"
      mode="search"
      :lookups="lookups"
      @search="handleSearch"
    />

    <!-- Search error -->
    <div v-if="searchError" class="mt-6 flex items-center gap-3 rounded-xl bg-red-50 px-12 py-3">
      <ExclamationTriangleIcon class="h-5 w-5 flex-shrink-0 text-red-500" />
      <p class="text-sm text-red-700">{{ searchError }}</p>
    </div>

    <!-- Loading -->
    <div v-if="searching" class="mt-8 flex items-center justify-center gap-3 text-gray-500">
      <svg class="h-5 w-5 animate-spin text-primary" viewBox="0 0 24 24" fill="none">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" />
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
      </svg>
      <span class="text-sm">Pesquisando...</span>
    </div>

    <!-- Grid results -->
    <div v-if="!searching && gridData.length > 0" class="mt-6">
      <GenericDataGrid
        :columns="columns"
        :data="gridData"
        :page-size="25"
        export-filename="embarques-exportacao"
      />
    </div>

    <!-- No results -->
    <div v-if="!searching && hasSearched && gridData.length === 0 && !searchError" class="mt-8 text-center text-gray-400 text-sm">
      Nenhum embarque encontrado para os filtros informados.
    </div>
  </ReportPageLayout>
</template>
