<script setup>
import { reactive, ref, computed } from 'vue'
import ReportPageLayout from '@/Components/Reports/ReportPageLayout.vue'
import FilterPanel from '@/Components/Reports/FilterPanel.vue'
import DataGrid from '@/Components/Reports/DataGrid.vue'
import DocumentModal from '@/Components/Reports/DocumentModal.vue'
import { StatusModal } from '@jagua/ui'
import { ExclamationTriangleIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  title: String,
  section: String,
  filters: Array,
  empresas: { type: Array, default: () => [] },
  documentos: { type: Object, required: true },
})

const form = reactive({})
props.filters.forEach(f => { form[f.name] = '' })

const lookups = computed(() => ({
  empresa: props.empresas.map(e => ({
    value: e.ep,
    label: `${e.ep} - ${e.den_reduz}`,
  })),
}))

// Grid state
const gridData = ref([])
const searching = ref(false)
const searchError = ref('')
const hasSearched = ref(false)

// Document modal state
const modalOpen = ref(false)
const pendingDoc = ref(null) // { docCode, rows }
const generating = ref(false)

// Status modal
const statusOpen = ref(false)
const statusType = ref('loading') // 'loading' | 'success' | 'error'
const statusMessage = ref('')
const statusTitle = ref('')

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
    const response = await fetch(`${route('relatorios.exportacao.processos_exportacao.pesquisar')}?${params.toString()}`, {
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
    })

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

function handleDocumentClick({ docCode, rows }) {
  const doc = props.documentos[docCode]
  if (!doc) return

  if (doc.copy_original) {
    pendingDoc.value = { docCode, rows }
    modalOpen.value = true
  } else {
    downloadDocument(docCode, rows, null)
  }
}

function handleCopySelect(copyType) {
  if (!pendingDoc.value) return
  const { docCode, rows } = pendingDoc.value
  modalOpen.value = false
  pendingDoc.value = null
  downloadDocument(docCode, rows, copyType)
}

async function downloadDocument(docCode, rows, copyType) {
  const doc = props.documentos[docCode]
  generating.value = true
  statusOpen.value = true
  statusType.value = 'loading'
  statusTitle.value = doc?.label || docCode
  statusMessage.value = 'Gerando documento, aguarde...'

  const params = new URLSearchParams()
  params.append('doc_type', docCode)
  if (copyType) params.append('copy_type', copyType)
  rows.forEach((r, i) => {
    params.append(`rows[${i}][empresa]`, r.empresa)
    params.append(`rows[${i}][processo]`, r.processo)
    params.append(`rows[${i}][embarque]`, r.embarque)
    params.append(`rows[${i}][ano]`, r.ano)
  })

  try {
    const response = await fetch(`${route('relatorios.exportacao.processos_exportacao.documento')}?${params.toString()}`, {
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
    })

    if (!response.ok) {
      let msg = 'Erro ao gerar documento.'
      try {
        const json = await response.json()
        if (json.message) msg = json.message
      } catch {}
      statusType.value = 'error'
      statusMessage.value = msg
      return
    }

    const blob = await response.blob()
    const url = window.URL.createObjectURL(blob)
    const disposition = response.headers.get('content-disposition') || ''
    const filenameMatch = disposition.match(/filename="?([^";\n]+)"?/)
    const filename = filenameMatch ? filenameMatch[1] : 'documento.pdf'

    const a = document.createElement('a')
    a.href = url
    a.download = filename
    document.body.appendChild(a)
    a.click()
    document.body.removeChild(a)
    window.URL.revokeObjectURL(url)

    statusType.value = 'success'
    statusMessage.value = 'Documento gerado com sucesso!'
  } catch {
    statusType.value = 'error'
    statusMessage.value = 'Erro de conexão. Verifique sua rede e tente novamente.'
  } finally {
    generating.value = false
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
    <div v-if="searchError" class="mt-6 flex items-center gap-3 rounded-xl bg-red-50 px-4 py-3">
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
      <div class="mb-3 flex items-center justify-between">
        <p class="text-sm text-gray-500">
          <span class="font-semibold text-gray-700">{{ gridData.length }}</span> processo(s) encontrado(s)
        </p>
      </div>
      <DataGrid
        :data="gridData"
        :documentos="documentos"
        @document-click="handleDocumentClick"
      />
    </div>

    <!-- No results -->
    <div v-if="!searching && hasSearched && gridData.length === 0 && !searchError" class="mt-8 text-center text-gray-400 text-sm">
      Nenhum processo encontrado para os filtros informados.
    </div>

    <!-- Document copy/original modal -->
    <DocumentModal
      :show="modalOpen"
      :doc-label="pendingDoc ? documentos[pendingDoc.docCode]?.label : ''"
      @select="handleCopySelect"
      @close="modalOpen = false; pendingDoc = null"
    />

    <!-- Status modal (download progress) -->
    <StatusModal
      :show="statusOpen"
      :status="statusType"
      :title="statusTitle"
      :message="statusMessage"
      @close="statusOpen = false"
    />
  </ReportPageLayout>
</template>
