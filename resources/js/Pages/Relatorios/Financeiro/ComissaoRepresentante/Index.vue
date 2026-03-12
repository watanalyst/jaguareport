<script setup>
import { reactive, ref, computed } from 'vue'
import ReportPageLayout from '@/Components/Reports/ReportPageLayout.vue'
import FilterPanel from '@/Components/Reports/FilterPanel.vue'
import GenericDataGrid from '@/Components/Reports/GenericDataGrid.vue'
import { PrimaryButton, SecondaryButton, StatusModal, ConfirmModal } from '@jagua/ui'
import { ExclamationTriangleIcon, CheckCircleIcon, XCircleIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  title: String,
  section: String,
  filters: Array,
  empresas: { type: Array, default: () => [] },
  columns: { type: Array, required: true },
})

const form = reactive({})
props.filters.forEach(f => { form[f.name] = '' })

const empresaLookup = computed(() =>
  props.empresas.map(e => ({
    value: e.ep,
    label: `${e.ep} - ${e.den_reduz}`,
  }))
)

const lookups = computed(() => ({
  emp: empresaLookup.value,
}))

// Grid state
const gridData = ref([])
const searching = ref(false)
const searchError = ref('')
const hasSearched = ref(false)

// Selection from GenericDataGrid
const selectedRows = ref([])

function onSelectionChange(rows) {
  selectedRows.value = rows
}

// Approval state
const approving = ref(false)
const modalVisible = ref(false)
const modalType = ref('success')
const modalMessage = ref('')

// Confirm modal state
const confirmVisible = ref(false)
const confirmTitle = ref('')
const confirmMessage = ref('')
const confirmVariant = ref('success')
const confirmAction = ref(null)

function formatDateISO(val) {
  if (!val) return null
  const d = new Date(val)
  if (isNaN(d)) return val
  return d.toISOString().slice(0, 10)
}

const pendingSelected = computed(() =>
  selectedRows.value.filter(r => r.status_aprov !== 'S').length
)
const approvedSelected = computed(() =>
  selectedRows.value.filter(r => r.status_aprov === 'S').length
)

function buildRegistros(filterFn) {
  return selectedRows.value.filter(filterFn).map(row => ({
    emp: row.emp,
    cod_repres: row.cod_repres,
    nome_repres: row.nome_repres,
    mes_comissao: formatDateISO(row.mes_comissao),
    val_comissao: row.val_comissao,
  }))
}

function askAprovar() {
  const count = pendingSelected.value
  if (!count) {
    modalType.value = 'warning'
    modalMessage.value = 'Todos os registros selecionados já estão aprovados.'
    modalVisible.value = true
    return
  }
  confirmTitle.value = 'Aprovar comissões'
  confirmMessage.value = `Deseja aprovar ${count} registro(s) pendente(s)?`
  confirmVariant.value = 'success'
  confirmAction.value = executeAprovar
  confirmVisible.value = true
}

function askDesaprovar() {
  const count = approvedSelected.value
  if (!count) {
    modalType.value = 'warning'
    modalMessage.value = 'Nenhum registro selecionado está aprovado.'
    modalVisible.value = true
    return
  }
  confirmTitle.value = 'Desaprovar comissões'
  confirmMessage.value = `Deseja desaprovar ${count} registro(s)?`
  confirmVariant.value = 'danger'
  confirmAction.value = executeDesaprovar
  confirmVisible.value = true
}

function onConfirm() {
  confirmVisible.value = false
  if (confirmAction.value) confirmAction.value()
}

async function executeAprovar() {
  const regs = buildRegistros(r => r.status_aprov !== 'S')
  approving.value = true
  try {
    const response = await fetch(route('relatorios.financeiro.comissao_representante.aprovar'), {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
      },
      body: JSON.stringify({ registros: regs }),
    })

    const json = await response.json()
    if (!response.ok) throw new Error(json.message || 'Erro ao aprovar.')

    modalType.value = 'success'
    modalMessage.value = json.message
    modalVisible.value = true
    await handleSearch(form)
  } catch (e) {
    modalType.value = 'error'
    modalMessage.value = e.message
    modalVisible.value = true
  } finally {
    approving.value = false
  }
}

async function executeDesaprovar() {
  const regs = buildRegistros(r => r.status_aprov === 'S')
  approving.value = true
  try {
    const response = await fetch(route('relatorios.financeiro.comissao_representante.desaprovar'), {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
      },
      body: JSON.stringify({ registros: regs }),
    })

    const json = await response.json()
    if (!response.ok) throw new Error(json.message || 'Erro ao desaprovar.')

    modalType.value = 'success'
    modalMessage.value = json.message
    modalVisible.value = true
    await handleSearch(form)
  } catch (e) {
    modalType.value = 'error'
    modalMessage.value = e.message
    modalVisible.value = true
  } finally {
    approving.value = false
  }
}

async function handleSearch(values) {
  searching.value = true
  searchError.value = ''
  hasSearched.value = true
  gridData.value = []
  selectedRows.value = []

  const params = new URLSearchParams()
  Object.entries(values).forEach(([k, v]) => {
    if (v !== '' && v !== null && v !== undefined) params.append(k, v)
  })

  try {
    const response = await fetch(
      `${route('relatorios.financeiro.comissao_representante.pesquisar')}?${params.toString()}`,
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
    <div v-if="!searching && gridData.length > 0" class="mt-4">
      <!-- Approval action bar -->
      <div class="flex items-center justify-between rounded-t-xl border border-b-0 border-gray-200 bg-gray-50 px-4 py-2">
        <span class="text-xs text-gray-500">
          <template v-if="selectedRows.length > 0">
            <b class="text-gray-700">{{ selectedRows.length }}</b> selecionado(s)
            <template v-if="pendingSelected > 0">
              · <span class="text-yellow-600">{{ pendingSelected }} pendente(s)</span>
            </template>
            <template v-if="approvedSelected > 0">
              · <span class="text-green-600">{{ approvedSelected }} aprovado(s)</span>
            </template>
          </template>
          <template v-else>Selecione linhas para aprovar ou desaprovar</template>
        </span>
        <div class="flex items-center gap-2">
          <PrimaryButton
            type="button"
            @click="askAprovar"
            :disabled="!pendingSelected || approving"
          >
            <CheckCircleIcon class="h-4 w-4" />
            <span>Aprovar</span>
          </PrimaryButton>
          <SecondaryButton
            type="button"
            @click="askDesaprovar"
            :disabled="!approvedSelected || approving"
          >
            <XCircleIcon class="h-4 w-4" />
            <span>Desaprovar</span>
          </SecondaryButton>
        </div>
      </div>

      <!-- GenericDataGrid -->
      <GenericDataGrid
        :columns="columns"
        :data="gridData"
        :page-size="25"
        export-filename="comissao-representante"
        selectable
        @update:selected="onSelectionChange"
      >
        <template #cell-status_aprov="{ value }">
          <span
            :class="value === 'S'
              ? 'inline-flex items-center rounded-full bg-green-50 px-2 py-0.5 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20'
              : 'inline-flex items-center rounded-full bg-yellow-50 px-2 py-0.5 text-xs font-medium text-yellow-800 ring-1 ring-inset ring-yellow-600/20'"
          >
            {{ value === 'S' ? 'Aprovado' : 'Pendente' }}
          </span>
        </template>

        <template #cell-mes_comissao="{ value }">
          {{ value ? new Date(value).toLocaleDateString('pt-BR', { month: '2-digit', year: 'numeric' }) : '—' }}
        </template>
      </GenericDataGrid>
    </div>

    <!-- No results -->
    <div v-if="!searching && hasSearched && gridData.length === 0 && !searchError" class="mt-8 text-center text-gray-400 text-sm">
      Nenhum registro encontrado para os filtros informados.
    </div>

    <!-- Confirm modal -->
    <ConfirmModal
      :show="confirmVisible"
      :title="confirmTitle"
      :message="confirmMessage"
      :variant="confirmVariant"
      :confirm-label="confirmVariant === 'success' ? 'Aprovar' : 'Desaprovar'"
      @confirm="onConfirm"
      @cancel="confirmVisible = false"
    />

    <!-- Status modal -->
    <StatusModal
      v-if="modalVisible"
      :type="modalType"
      :message="modalMessage"
      @close="modalVisible = false"
    />
  </ReportPageLayout>
</template>
