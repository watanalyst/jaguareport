<script setup>
import { reactive, ref, computed } from 'vue'
import ReportPageLayout from '@/Components/Reports/ReportPageLayout.vue'
import { GenericDataGrid } from 'btz-components-vue'
import { PrimaryButton, SecondaryButton, TextInput, InputLabel, InputError, Modal, ConfirmModal, StatusModal } from 'btz-components-vue'
import { PlusIcon, PencilSquareIcon, TrashIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  empresas: { type: Array, default: () => [] },
  initialData: { type: Array, default: () => [] },
})

// ── Grid columns ──
const columns = [
  { key: 'cod_empresa', label: 'Empresa', sortable: true, filterable: true, align: 'center' },
  { key: 'num_nc', label: 'Num NC', sortable: true, filterable: true, align: 'center' },
  { key: 'ano_nc', label: 'Ano NC', sortable: true, filterable: true, align: 'center' },
  { key: 'account_name', label: 'Account Name', sortable: true, filterable: true },
  { key: 'bank_name', label: 'Bank Name', sortable: true, filterable: true },
  { key: 'account_type', label: 'Account Type', sortable: true, filterable: true },
  { key: 'account_number', label: 'Account Number', sortable: true, filterable: true },
  { key: 'iban', label: 'IBAN', sortable: true, filterable: true },
  { key: 'swift_code', label: 'Swift Code', sortable: true, filterable: true },
  { key: 'branch', label: 'Branch', sortable: true, filterable: true },
  { key: 'acoes', label: 'Ações', sortable: false, filterable: false, align: 'center', width: '90px' },
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
    const res = await fetch(route('relatorios.exportacao.banco_credit_note.pesquisar'), {
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
    })
    const json = await res.json()
    gridData.value = json.data
  } catch {}
}

// ══════════════════════════════════════
// CRUD
// ══════════════════════════════════════
const formModalOpen = ref(false)
const editingId = ref(null)
const form = reactive({
  cod_empresa: '',
  num_nc: '',
  ano_nc: '',
  account_name: '',
  bank_name: '',
  account_type: '',
  account_number: '',
  iban: '',
  swift_code: '',
  branch: '',
})
const formErrors = ref({})
const formGeneralError = ref('')
const saving = ref(false)

function openNew() {
  editingId.value = null
  Object.keys(form).forEach(k => form[k] = '')
  formErrors.value = {}
  formGeneralError.value = ''
  formModalOpen.value = true
}

async function openEdit(row) {
  editingId.value = row.id_registro
  formErrors.value = {}
  formGeneralError.value = ''

  try {
    const res = await fetch(route('relatorios.exportacao.banco_credit_note.show', row.id_registro), {
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
    })
    const data = await res.json()

    form.cod_empresa = data.cod_empresa || ''
    form.num_nc = data.num_nc ?? ''
    form.ano_nc = data.ano_nc ?? ''
    form.account_name = data.account_name || ''
    form.bank_name = data.bank_name || ''
    form.account_type = data.account_type || ''
    form.account_number = data.account_number || ''
    form.iban = data.iban || ''
    form.swift_code = data.swift_code || ''
    form.branch = data.branch || ''

    formModalOpen.value = true
  } catch {
    showModal('error', 'Erro ao carregar registro.')
  }
}

async function saveForm() {
  saving.value = true
  formErrors.value = {}
  formGeneralError.value = ''

  const isEdit = editingId.value !== null
  const url = isEdit
    ? route('relatorios.exportacao.banco_credit_note.update', editingId.value)
    : route('relatorios.exportacao.banco_credit_note.store')

  try {
    const res = await fetch(url, {
      method: isEdit ? 'PUT' : 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-XSRF-TOKEN': xsrfToken(),
      },
      body: JSON.stringify({ ...form }),
    })

    if (res.status === 422) {
      const json = await res.json()
      formErrors.value = json.errors || {}
      return
    }

    if (!res.ok) {
      const json = await res.json().catch(() => ({}))
      formGeneralError.value = json.message || 'Erro ao salvar.'
      return
    }

    formModalOpen.value = false
    await reloadData()
  } catch {
    formGeneralError.value = 'Erro de conexão.'
  } finally {
    saving.value = false
  }
}

// ── Delete ──
const confirmDeleteOpen = ref(false)
const deletingId = ref(null)

function openDelete(row) {
  deletingId.value = row.id_registro
  confirmDeleteOpen.value = true
}

async function handleDelete() {
  confirmDeleteOpen.value = false
  try {
    const res = await fetch(route('relatorios.exportacao.banco_credit_note.destroy', deletingId.value), {
      method: 'DELETE',
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'X-XSRF-TOKEN': xsrfToken(),
      },
    })

    if (!res.ok) {
      const json = await res.json().catch(() => ({}))
      showModal('error', json.message || 'Erro ao excluir.')
      return
    }

    await reloadData()
  } catch {
    showModal('error', 'Erro de conexão.')
  }
}
</script>

<template>
  <ReportPageLayout title="Banco Credit Note" section="Exportação">
    <GenericDataGrid
      :columns="columns"
      :data="gridData"
      :page-size="25"
      :show-excel="false"
      :show-expand="false"
      row-key="id_registro"
      export-filename="banco-credit-note"
    >
      <template #toolbar-right>
        <PrimaryButton @click="openNew">
          <PlusIcon class="h-4 w-4 mr-1.5" />
          Novo
        </PrimaryButton>
      </template>

      <template #cell-acoes="{ row }">
        <div class="flex items-center justify-center gap-1">
          <button
            @click.stop="openEdit(row)"
            class="p-1.5 rounded-lg text-gray-400 hover:text-primary-600 hover:bg-primary-50 transition-colors"
            title="Editar"
          >
            <PencilSquareIcon class="h-4 w-4" />
          </button>
          <button
            @click.stop="openDelete(row)"
            class="p-1.5 rounded-lg text-gray-400 hover:text-red-500 hover:bg-red-50 transition-colors"
            title="Excluir"
          >
            <TrashIcon class="h-4 w-4" />
          </button>
        </div>
      </template>
    </GenericDataGrid>

    <!-- Form Modal -->
    <Modal :show="formModalOpen" @close="formModalOpen = false" max-width="2xl">
      <div class="p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">
          {{ editingId ? 'Editar' : 'Novo' }} Banco Credit Note
        </h2>

        <div v-if="formGeneralError" class="mb-4 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700">
          {{ formGeneralError }}
        </div>

        <form @submit.prevent="saveForm" class="grid grid-cols-3 gap-4">
          <div>
            <InputLabel value="Empresa *" />
            <select
              v-model="form.cod_empresa"
              class="mt-1 block w-full rounded-lg border-gray-300 py-2.5 text-sm shadow-sm focus:border-primary focus:ring-primary"
            >
              <option value="">Selecione...</option>
              <option v-for="e in empresaOptions" :key="e.value" :value="e.value">{{ e.label }}</option>
            </select>
            <InputError :message="formErrors.cod_empresa?.[0]" class="mt-1" />
          </div>

          <div>
            <InputLabel value="Num NC *" />
            <TextInput v-model="form.num_nc" class="mt-1 w-full" />
            <InputError :message="formErrors.num_nc?.[0]" class="mt-1" />
          </div>

          <div>
            <InputLabel value="Ano NC *" />
            <TextInput v-model="form.ano_nc" class="mt-1 w-full" />
            <InputError :message="formErrors.ano_nc?.[0]" class="mt-1" />
          </div>

          <div class="col-span-2">
            <InputLabel value="Account Name" />
            <TextInput v-model="form.account_name" class="mt-1 w-full" />
            <InputError :message="formErrors.account_name?.[0]" class="mt-1" />
          </div>

          <div>
            <InputLabel value="Bank Name" />
            <TextInput v-model="form.bank_name" class="mt-1 w-full" />
            <InputError :message="formErrors.bank_name?.[0]" class="mt-1" />
          </div>

          <div>
            <InputLabel value="Account Type" />
            <TextInput v-model="form.account_type" class="mt-1 w-full" />
            <InputError :message="formErrors.account_type?.[0]" class="mt-1" />
          </div>

          <div>
            <InputLabel value="Account Number" />
            <TextInput v-model="form.account_number" class="mt-1 w-full" />
            <InputError :message="formErrors.account_number?.[0]" class="mt-1" />
          </div>

          <div>
            <InputLabel value="IBAN" />
            <TextInput v-model="form.iban" class="mt-1 w-full" />
            <InputError :message="formErrors.iban?.[0]" class="mt-1" />
          </div>

          <div>
            <InputLabel value="Swift Code" />
            <TextInput v-model="form.swift_code" class="mt-1 w-full" />
            <InputError :message="formErrors.swift_code?.[0]" class="mt-1" />
          </div>

          <div class="col-span-2">
            <InputLabel value="Branch" />
            <TextInput v-model="form.branch" class="mt-1 w-full" />
            <InputError :message="formErrors.branch?.[0]" class="mt-1" />
          </div>

          <div class="col-span-3 flex items-center gap-3 mt-4 pt-4 border-t border-gray-100">
            <PrimaryButton type="submit" :disabled="saving">
              {{ saving ? 'Salvando...' : 'Salvar' }}
            </PrimaryButton>
            <SecondaryButton @click="formModalOpen = false">Cancelar</SecondaryButton>
          </div>
        </form>
      </div>
    </Modal>

    <!-- Confirm Delete -->
    <ConfirmModal
      :show="confirmDeleteOpen"
      title="Excluir Registro"
      message="Tem certeza que deseja excluir este registro? Esta ação não pode ser desfeita."
      confirm-label="Excluir"
      variant="danger"
      @confirm="handleDelete"
      @cancel="confirmDeleteOpen = false"
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
