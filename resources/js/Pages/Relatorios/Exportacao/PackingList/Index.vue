<script setup>
import { reactive, ref, computed } from 'vue'
import ReportPageLayout from '@/Components/Reports/ReportPageLayout.vue'
import { GenericDataGrid } from 'btz-components-vue'
import { PrimaryButton, SecondaryButton, TextInput, InputLabel, InputError, Modal, ConfirmModal, StatusModal } from 'btz-components-vue'
import { PlusIcon, PencilSquareIcon, TrashIcon, QueueListIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  empresas: { type: Array, default: () => [] },
  initialData: { type: Array, default: () => [] },
})

// ── Grid columns ──
const columns = [
  { key: 'cod_empresa', label: 'Empresa', sortable: true, align: 'center' },
  { key: 'processo', label: 'Processo', sortable: true, align: 'center' },
  { key: 'ano', label: 'Ano', sortable: true, align: 'center' },
  { key: 'embarque', label: 'Embarque', sortable: true, align: 'center' },
  { key: 'num_pedido', label: 'Num Pedido', sortable: true, align: 'center' },
  { key: 'cod_item', label: 'Cód Item', sortable: true, align: 'center' },
  { key: 'acoes', label: 'Ações', sortable: false, filterable: false, align: 'center', width: '110px' },
]

const gridData = ref(props.initialData || [])

// ── Status modal ──
const modalVisible = ref(false)
const modalType = ref('error')
const modalMessage = ref('')

function showModal(type, message) {
  modalType.value = type
  modalMessage.value = message
  modalVisible.value = true
}

// ── Empresas lookup ──
const empresaOptions = computed(() =>
  props.empresas.map(e => ({ value: e.ep, label: `${e.ep} - ${e.den_reduz}` }))
)

// ── XSRF helper ──
function xsrfToken() {
  return decodeURIComponent(document.cookie.match(/XSRF-TOKEN=([^;]+)/)?.[1] || '')
}

// ── Reload grid ──
async function reloadData() {
  try {
    const res = await fetch(route('relatorios.exportacao.packing_list.pesquisar'), {
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
    })
    const json = await res.json()
    gridData.value = json.data
  } catch {}
}

// ══════════════════════════════════════
// MASTER CRUD
// ══════════════════════════════════════
const masterModalOpen = ref(false)
const editingMasterId = ref(null)
const masterForm = reactive({
  cod_empresa: '',
  processo: '',
  ano: '',
  embarque: '',
  num_pedido: '',
  cod_item: '',
})
const masterErrors = ref({})
const masterGeneralError = ref('')
const savingMaster = ref(false)

function openNewMaster() {
  editingMasterId.value = null
  Object.keys(masterForm).forEach(k => masterForm[k] = '')
  masterErrors.value = {}
  masterGeneralError.value = ''
  pedidoOptions.value = []
  itemOptions.value = []
  lookupStatus.value = ''
  masterModalOpen.value = true
}

async function openEditMaster(row) {
  editingMasterId.value = row.id
  masterForm.cod_empresa = row.cod_empresa
  masterForm.processo = row.processo
  masterForm.ano = row.ano
  masterForm.embarque = row.embarque
  masterErrors.value = {}
  masterGeneralError.value = ''
  lookupStatus.value = ''
  pedidoOptions.value = []
  itemOptions.value = []
  masterForm.num_pedido = ''
  masterForm.cod_item = ''
  masterModalOpen.value = true

  // Carregar selects com os dados atuais
  try {
    const params = new URLSearchParams({
      cod_empresa: row.cod_empresa,
      processo: row.processo,
      ano: row.ano,
      embarque: row.embarque,
    })
    const res = await fetch(`${route('relatorios.exportacao.packing_list.lookup')}?${params}`, {
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
    })
    if (res.ok) {
      const data = await res.json()
      if (data.pedidos?.length > 0) {
        pedidoOptions.value = data.pedidos
        masterForm.num_pedido = row.num_pedido ? String(row.num_pedido) : ''

        // Carregar itens do pedido atual
        const resItem = await fetch(`${route('relatorios.exportacao.packing_list.lookup')}?num_pedido=${row.num_pedido}`, {
          headers: { 'X-Requested-With': 'XMLHttpRequest' },
        })
        if (resItem.ok) {
          const dataItem = await resItem.json()
          if (dataItem.itens?.length > 0) {
            itemOptions.value = dataItem.itens
            masterForm.cod_item = row.cod_item ? String(row.cod_item).trim() : ''
          }
        }
        lookupStatus.value = 'found'
      }
    }
  } catch {}
}

let lookupTimer = null
const lookupStatus = ref('') // '' | 'loading' | 'found' | 'not_found'
const pedidoOptions = ref([])
const itemOptions = ref([])

function onMasterFieldChange() {
  clearTimeout(lookupTimer)
  masterForm.num_pedido = ''
  masterForm.cod_item = ''
  pedidoOptions.value = []
  itemOptions.value = []
  lookupStatus.value = ''
  masterGeneralError.value = ''

  if (!masterForm.cod_empresa || !masterForm.processo || !masterForm.ano || !masterForm.embarque) return

  lookupStatus.value = 'loading'

  lookupTimer = setTimeout(async () => {
    try {
      const params = new URLSearchParams({
        cod_empresa: masterForm.cod_empresa,
        processo: masterForm.processo,
        ano: masterForm.ano,
        embarque: masterForm.embarque,
      })
      const res = await fetch(`${route('relatorios.exportacao.packing_list.lookup')}?${params}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
      })
      if (!res.ok) { lookupStatus.value = 'not_found'; return }
      const data = await res.json()
      if (data.pedidos && data.pedidos.length > 0) {
        pedidoOptions.value = data.pedidos
        lookupStatus.value = 'found'
        // Se só tem 1 pedido, seleciona automaticamente
        if (data.pedidos.length === 1) {
          masterForm.num_pedido = String(data.pedidos[0])
          onPedidoChange()
        }
      } else {
        lookupStatus.value = 'not_found'
      }
    } catch {
      lookupStatus.value = 'not_found'
    }
  }, 300)
}

async function onPedidoChange() {
  masterForm.cod_item = ''
  itemOptions.value = []
  masterGeneralError.value = ''

  if (!masterForm.num_pedido) return

  try {
    const params = new URLSearchParams({ num_pedido: masterForm.num_pedido })
    const res = await fetch(`${route('relatorios.exportacao.packing_list.lookup')}?${params}`, {
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
    })
    if (!res.ok) return
    const data = await res.json()
    if (data.itens && data.itens.length > 0) {
      itemOptions.value = data.itens
      // Se só tem 1 item, seleciona automaticamente
      if (data.itens.length === 1) {
        masterForm.cod_item = data.itens[0]
      }
    }
  } catch {}
}

const canSaveMaster = computed(() =>
  masterForm.cod_empresa && masterForm.processo && masterForm.ano && masterForm.embarque && masterForm.num_pedido && masterForm.cod_item
)

async function saveMaster() {
  savingMaster.value = true
  masterErrors.value = {}

  const isEdit = editingMasterId.value !== null
  const url = isEdit
    ? route('relatorios.exportacao.packing_list.update', editingMasterId.value)
    : route('relatorios.exportacao.packing_list.store')

  try {
    const res = await fetch(url, {
      method: isEdit ? 'PUT' : 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-XSRF-TOKEN': xsrfToken(),
      },
      body: JSON.stringify(masterForm),
    })

    if (!res.ok) {
      try {
        const json = await res.json()
        if (json.errors) {
          masterErrors.value = json.errors
        } else if (json.message) {
          masterGeneralError.value = json.message
        } else {
          masterGeneralError.value = 'Erro ao salvar.'
        }
      } catch {
        masterGeneralError.value = 'Erro ao salvar.'
      }
      return
    }

    const json = await res.json()
    masterModalOpen.value = false
    await reloadData()

    // Se criou novo, abre os filhos automaticamente
    if (!isEdit && json.id) {
      handleExpand(json.id)
    }
  } catch {
    showModal('error', 'Erro ao salvar.')
  } finally {
    savingMaster.value = false
  }
}

async function saveAsNew() {
  editingMasterId.value = null
  await saveMaster()
}

// Delete master
const confirmDeleteOpen = ref(false)
const deletingId = ref(null)

function confirmDelete(id) {
  deletingId.value = id
  confirmDeleteOpen.value = true
}

async function handleDeleteMaster() {
  try {
    await fetch(route('relatorios.exportacao.packing_list.destroy', deletingId.value), {
      method: 'DELETE',
      headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-XSRF-TOKEN': xsrfToken() },
    })
    confirmDeleteOpen.value = false
    if (expandedId.value === deletingId.value) expandedId.value = null
    await reloadData()
  } catch {
    showModal('error', 'Erro ao excluir.')
  }
}

// ══════════════════════════════════════
// DETAIL (FILHOS)
// ══════════════════════════════════════
const expandedId = ref(null)
const expandedMaster = ref(null)
const details = ref([])
const loadingDetails = ref(false)

async function handleExpand(id) {
  if (id === null) {
    expandedId.value = null
    expandedMaster.value = null
    details.value = []
    return
  }
  expandedId.value = id
  expandedMaster.value = gridData.value.find(m => m.id === id)
  loadingDetails.value = true
  details.value = []
  try {
    const res = await fetch(route('relatorios.exportacao.packing_list.show', id), {
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
    })
    const json = await res.json()
    details.value = json.details
  } catch {
    showModal('error', 'Erro ao carregar detalhes.')
  } finally {
    loadingDetails.value = false
  }
}

async function refreshDetails() {
  if (!expandedId.value) return
  loadingDetails.value = true
  try {
    const res = await fetch(route('relatorios.exportacao.packing_list.show', expandedId.value), {
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
    })
    const json = await res.json()
    details.value = json.details
  } catch {} finally {
    loadingDetails.value = false
  }
}

// Detail modal
const detailModalOpen = ref(false)
const editingDetailId = ref(null)
const detailForm = reactive({
  production_date: '',
  date_expiry: '',
  cartons: '',
  net_weight: '',
  gross_weight: '',
  lots: '',
  palete: '',
})
const detailErrors = ref({})
const savingDetail = ref(false)

function openNewDetail() {
  editingDetailId.value = null
  Object.keys(detailForm).forEach(k => detailForm[k] = '')
  detailErrors.value = {}
  detailModalOpen.value = true
}

function openEditDetail(detail) {
  editingDetailId.value = detail.id
  detailForm.production_date = detail.production_date || ''
  detailForm.date_expiry = detail.date_expiry || ''
  detailForm.cartons = detail.cartons
  detailForm.net_weight = detail.net_weight
  detailForm.gross_weight = detail.gross_weight
  detailForm.lots = detail.lots || ''
  detailForm.palete = detail.palete || ''
  detailErrors.value = {}
  detailModalOpen.value = true
}

async function saveDetail() {
  savingDetail.value = true
  detailErrors.value = {}

  const isEdit = editingDetailId.value !== null
  const url = isEdit
    ? route('relatorios.exportacao.packing_list.detalhes.update', [expandedId.value, editingDetailId.value])
    : route('relatorios.exportacao.packing_list.detalhes.store', expandedId.value)

  try {
    const res = await fetch(url, {
      method: isEdit ? 'PUT' : 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-XSRF-TOKEN': xsrfToken(),
      },
      body: JSON.stringify(detailForm),
    })

    if (res.status === 422) {
      const json = await res.json()
      detailErrors.value = json.errors || {}
      return
    }
    if (!res.ok) throw new Error()

    detailModalOpen.value = false
    await refreshDetails()
  } catch {
    showModal('error', 'Erro ao salvar item.')
  } finally {
    savingDetail.value = false
  }
}

const confirmDeleteDetailOpen = ref(false)
const deletingDetailId = ref(null)

function confirmDeleteDetail(detailId) {
  deletingDetailId.value = detailId
  confirmDeleteDetailOpen.value = true
}

async function handleDeleteDetail() {
  const detailId = deletingDetailId.value
  confirmDeleteDetailOpen.value = false
  try {
    await fetch(route('relatorios.exportacao.packing_list.detalhes.destroy', [expandedId.value, detailId]), {
      method: 'DELETE',
      headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-XSRF-TOKEN': xsrfToken() },
    })
    await refreshDetails()
  } catch {
    showModal('error', 'Erro ao excluir item.')
  }
}

function formatDate(val) {
  if (!val) return ''
  const d = val.substring(0, 10)
  const [y, m, day] = d.split('-')
  return `${day}/${m}/${y}`
}
</script>

<template>
  <ReportPageLayout title="Packing List" section="Exportação">
    <!-- Grid Master -->
    <GenericDataGrid
      :columns="columns"
      :data="gridData"
      :page-size="25"
      :show-excel="false"
      :show-expand="false"
      :expandable="true"
      :expanded-row-id="expandedId"
      row-key="id"
      export-filename="packing-list"
      @row-expand="handleExpand"
    >
      <!-- Botão Novo à direita, junto ao Filtros -->
      <template #toolbar-right>
        <PrimaryButton @click="openNewMaster">
          <PlusIcon class="h-4 w-4 mr-1.5" />
          Novo
        </PrimaryButton>
      </template>

      <!-- Coluna Ações: 3 ícones -->
      <template #cell-acoes="{ row, toggleExpand }">
        <div class="flex items-center justify-center gap-1">
          <button
            @click.stop="toggleExpand()"
            class="p-1.5 rounded-lg transition-colors"
            :class="expandedId === row.id
              ? 'text-primary-600 bg-primary-50'
              : 'text-gray-400 hover:text-primary-600 hover:bg-primary-50'"
            title="Ver itens"
          >
            <QueueListIcon class="h-4 w-4" />
          </button>
          <button
            @click.stop="openEditMaster(row)"
            class="p-1.5 rounded-lg text-gray-400 hover:text-primary-600 hover:bg-primary-50 transition-colors"
            title="Editar"
          >
            <PencilSquareIcon class="h-4 w-4" />
          </button>
          <button
            @click.stop="confirmDelete(row.id)"
            class="p-1.5 rounded-lg text-gray-400 hover:text-red-500 hover:bg-red-50 transition-colors"
            title="Excluir"
          >
            <TrashIcon class="h-4 w-4" />
          </button>
        </div>
      </template>

      <!-- Expanded row: detalhes inline -->
      <template #expanded-row="{ row, close }">
        <div class="flex items-center justify-between mb-3">
          <div>
            <h3 class="text-sm font-semibold text-gray-700">Itens do Packing List</h3>
            <p class="text-xs text-gray-400 mt-0.5">
              Pedido {{ row.num_pedido }} · {{ row.cod_item }}
            </p>
          </div>
          <div class="flex items-center gap-2">
            <PrimaryButton size="sm" @click="openNewDetail">
              <PlusIcon class="h-3.5 w-3.5 mr-1" />
              Novo Item
            </PrimaryButton>
            <SecondaryButton size="sm" @click="close">
              Fechar
            </SecondaryButton>
          </div>
        </div>

        <div v-if="loadingDetails" class="text-center py-4 text-sm text-gray-400">Carregando...</div>

        <div v-else-if="details.length === 0" class="text-center py-4 text-sm text-gray-400">
          Nenhum item cadastrado. Clique em "Novo Item" para adicionar.
        </div>

        <div v-else class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
          <table class="min-w-full text-xs">
            <thead>
              <tr style="background: linear-gradient(180deg, #0B56B3 0%, #093F87 100%);" class="text-white">
                <th class="px-3 py-2 text-center text-[11px] uppercase tracking-wider font-semibold">Data Produção</th>
                <th class="px-3 py-2 text-center text-[11px] uppercase tracking-wider font-semibold">Data Expiração</th>
                <th class="px-3 py-2 text-right text-[11px] uppercase tracking-wider font-semibold">Caixas</th>
                <th class="px-3 py-2 text-right text-[11px] uppercase tracking-wider font-semibold">Peso Líquido</th>
                <th class="px-3 py-2 text-right text-[11px] uppercase tracking-wider font-semibold">Peso Bruto</th>
                <th class="px-3 py-2 text-left text-[11px] uppercase tracking-wider font-semibold">Lotes</th>
                <th class="px-3 py-2 text-left text-[11px] uppercase tracking-wider font-semibold">Palete</th>
                <th class="px-3 py-2 text-center text-[11px] uppercase tracking-wider font-semibold">Ações</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100/80">
              <tr
                v-for="(d, i) in details"
                :key="d.id"
                class="transition-colors"
                :class="i % 2 === 0 ? 'bg-white hover:bg-blue-100/50' : 'bg-slate-50/70 hover:bg-blue-100/50'"
              >
                <td class="px-3 py-2 text-center">{{ formatDate(d.production_date) }}</td>
                <td class="px-3 py-2 text-center">{{ formatDate(d.date_expiry) }}</td>
                <td class="px-3 py-2 text-right">{{ Number(d.cartons).toLocaleString('pt-BR') }}</td>
                <td class="px-3 py-2 text-right">{{ Number(d.net_weight).toLocaleString('pt-BR', { minimumFractionDigits: 2 }) }}</td>
                <td class="px-3 py-2 text-right">{{ Number(d.gross_weight).toLocaleString('pt-BR', { minimumFractionDigits: 2 }) }}</td>
                <td class="px-3 py-2">{{ d.lots || '—' }}</td>
                <td class="px-3 py-2">{{ d.palete || '—' }}</td>
                <td class="px-3 py-2">
                  <div class="flex items-center justify-center gap-1">
                    <button @click="openEditDetail(d)" class="p-1 rounded text-gray-400 hover:text-primary-600 hover:bg-primary-50" title="Editar">
                      <PencilSquareIcon class="h-3.5 w-3.5" />
                    </button>
                    <button @click="confirmDeleteDetail(d.id)" class="p-1 rounded text-gray-400 hover:text-red-500 hover:bg-red-50" title="Excluir">
                      <TrashIcon class="h-3.5 w-3.5" />
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </template>
    </GenericDataGrid>

    <!-- Master Modal -->
    <Modal :show="masterModalOpen" @close="masterModalOpen = false" max-width="lg">
      <div class="p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">
          {{ editingMasterId ? 'Editar' : 'Novo' }} Packing List
        </h2>
        <form @submit.prevent="saveMaster" class="grid grid-cols-2 gap-4">
          <div>
            <InputLabel value="Empresa" />
            <select
              v-model="masterForm.cod_empresa"
              class="mt-1 block w-full rounded-lg border-gray-300 py-2.5 text-sm shadow-sm focus:border-primary focus:ring-primary"
              @change="onMasterFieldChange"
            >
              <option value="">Selecione...</option>
              <option v-for="e in empresaOptions" :key="e.value" :value="e.value">{{ e.label }}</option>
            </select>
            <InputError :message="masterErrors.cod_empresa?.[0]" class="mt-1" />
          </div>
          <div>
            <InputLabel value="Processo" />
            <TextInput v-model="masterForm.processo" class="mt-1 w-full" @input="onMasterFieldChange" />
            <InputError :message="masterErrors.processo?.[0]" class="mt-1" />
          </div>
          <div>
            <InputLabel value="Ano" />
            <TextInput v-model="masterForm.ano" class="mt-1 w-full" @input="onMasterFieldChange" />
            <InputError :message="masterErrors.ano?.[0]" class="mt-1" />
          </div>
          <div>
            <InputLabel value="Embarque" />
            <TextInput v-model="masterForm.embarque" class="mt-1 w-full" @input="onMasterFieldChange" />
            <InputError :message="masterErrors.embarque?.[0]" class="mt-1" />
          </div>
          <div>
            <InputLabel value="Num Pedido" />
            <select
              v-model="masterForm.num_pedido"
              class="mt-1 block w-full rounded-lg border-gray-300 py-2.5 text-sm shadow-sm focus:border-primary focus:ring-primary"
              :disabled="pedidoOptions.length === 0"
              @change="onPedidoChange"
            >
              <option value="">Selecione um pedido</option>
              <option v-for="p in pedidoOptions" :key="p" :value="String(p)">{{ p }}</option>
            </select>
          </div>
          <div>
            <InputLabel value="Cód Item" />
            <select
              v-model="masterForm.cod_item"
              class="mt-1 block w-full rounded-lg border-gray-300 py-2.5 text-sm shadow-sm focus:border-primary focus:ring-primary"
              :disabled="itemOptions.length === 0"
            >
              <option value="">Selecione um item</option>
              <option v-for="item in itemOptions" :key="item" :value="item">{{ item }}</option>
            </select>
          </div>

          <!-- Lookup status -->
          <div v-if="lookupStatus === 'loading'" class="col-span-2 text-sm text-gray-400">
            Buscando pedidos...
          </div>
          <div v-else-if="lookupStatus === 'not_found'" class="col-span-2 rounded-lg bg-red-50 px-3 py-2 text-sm text-red-600">
            Nenhum pedido encontrado para esta combinação de Empresa/Processo/Ano/Embarque.
          </div>

          <!-- General error (duplicidade, etc) -->
          <div v-if="masterGeneralError" class="col-span-2 rounded-lg bg-red-50 px-3 py-2 text-sm text-red-600">
            {{ masterGeneralError }}
          </div>

          <div class="col-span-2 flex items-center gap-3 mt-4 pt-4 border-t border-gray-100">
            <PrimaryButton type="submit" :disabled="savingMaster || !canSaveMaster">
              {{ savingMaster ? 'Salvando...' : 'Salvar' }}
            </PrimaryButton>
            <SecondaryButton v-if="editingMasterId" @click="saveAsNew" :disabled="savingMaster || !canSaveMaster">
              Salvar como Novo
            </SecondaryButton>
            <SecondaryButton @click="masterModalOpen = false">Cancelar</SecondaryButton>
          </div>
        </form>
      </div>
    </Modal>

    <!-- Detail Modal -->
    <Modal :show="detailModalOpen" @close="detailModalOpen = false" max-width="lg">
      <div class="p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">
          {{ editingDetailId ? 'Editar' : 'Novo' }} Item
        </h2>
        <form @submit.prevent="saveDetail" class="grid grid-cols-2 gap-4">
          <div>
            <InputLabel value="Data Produção" />
            <TextInput v-model="detailForm.production_date" class="mt-1 w-full" type="date" />
            <InputError :message="detailErrors.production_date?.[0]" class="mt-1" />
          </div>
          <div>
            <InputLabel value="Data Expiração" />
            <TextInput v-model="detailForm.date_expiry" class="mt-1 w-full" type="date" />
            <InputError :message="detailErrors.date_expiry?.[0]" class="mt-1" />
          </div>
          <div>
            <InputLabel value="Caixas" />
            <TextInput v-model="detailForm.cartons" class="mt-1 w-full" type="number" />
            <InputError :message="detailErrors.cartons?.[0]" class="mt-1" />
          </div>
          <div>
            <InputLabel value="Peso Líquido" />
            <TextInput v-model="detailForm.net_weight" class="mt-1 w-full" type="number" step="0.01" />
            <InputError :message="detailErrors.net_weight?.[0]" class="mt-1" />
          </div>
          <div>
            <InputLabel value="Peso Bruto" />
            <TextInput v-model="detailForm.gross_weight" class="mt-1 w-full" type="number" step="0.01" />
            <InputError :message="detailErrors.gross_weight?.[0]" class="mt-1" />
          </div>
          <div>
            <InputLabel value="Lotes" />
            <TextInput v-model="detailForm.lots" class="mt-1 w-full" />
            <InputError :message="detailErrors.lots?.[0]" class="mt-1" />
          </div>
          <div>
            <InputLabel value="Palete" />
            <TextInput v-model="detailForm.palete" class="mt-1 w-full" />
            <InputError :message="detailErrors.palete?.[0]" class="mt-1" />
          </div>

          <div class="col-span-2 flex items-center gap-3 mt-4 pt-4 border-t border-gray-100">
            <PrimaryButton type="submit" :disabled="savingDetail">
              {{ savingDetail ? 'Salvando...' : 'Salvar' }}
            </PrimaryButton>
            <SecondaryButton @click="detailModalOpen = false">Cancelar</SecondaryButton>
          </div>
        </form>
      </div>
    </Modal>

    <!-- Confirm Delete Master -->
    <ConfirmModal
      :show="confirmDeleteOpen"
      title="Excluir Packing List"
      message="Tem certeza que deseja excluir este Packing List? Todos os itens (filhos) cadastrados também serão excluídos permanentemente."
      confirm-label="Excluir"
      variant="danger"
      @confirm="handleDeleteMaster"
      @cancel="confirmDeleteOpen = false"
    />

    <!-- Confirm Delete Detail -->
    <ConfirmModal
      :show="confirmDeleteDetailOpen"
      title="Excluir Item"
      message="Tem certeza que deseja excluir este item do Packing List?"
      confirm-label="Excluir"
      variant="danger"
      @confirm="handleDeleteDetail"
      @cancel="confirmDeleteDetailOpen = false"
    />

    <!-- Status Modal -->
    <StatusModal
      :show="modalVisible"
      :status="modalType"
      :title="modalType === 'error' ? 'Erro' : 'Sucesso'"
      :message="modalMessage"
      @close="modalVisible = false"
    />
  </ReportPageLayout>
</template>
