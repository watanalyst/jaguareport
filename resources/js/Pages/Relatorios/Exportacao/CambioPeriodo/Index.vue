<script setup>
import { reactive, ref, computed } from 'vue'
import ReportPageLayout from '@/Components/Reports/ReportPageLayout.vue'
import FilterPanel from '@/Components/Reports/FilterPanel.vue'
import { GenericDataGrid } from 'btz-components-vue'
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
    value: e.cod_empresa,
    label: e.cod_empresa,
  })),
}))

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
      `${route('relatorios.exportacao.cambio_periodo.pesquisar')}?${params.toString()}`,
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
      <GenericDataGrid
        :columns="columns"
        :data="gridData"
        :page-size="25"
        export-filename="cambio-periodo"
      />
    </div>

    <div v-if="!searching && hasSearched && gridData.length === 0 && !searchError" class="mt-8 text-center text-gray-400 text-sm">
      Nenhum registro encontrado para os filtros informados.
    </div>
  </ReportPageLayout>
</template>
