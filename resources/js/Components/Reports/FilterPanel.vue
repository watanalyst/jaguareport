<script setup>
import { InputLabel, InputError, PrimaryButton, SuccessButton, SecondaryButton, StatusModal } from '@jagua/ui'
import RadioGroup from './RadioGroup.vue'
import { ArrowDownTrayIcon, TableCellsIcon, MagnifyingGlassIcon } from '@heroicons/vue/24/outline'
import { ref } from 'vue'

const props = defineProps({
  filters: { type: Array, required: true },
  form: { type: Object, required: true },
  gerarRoute: { type: String, default: '' },
  empresas: { type: Array, default: () => [] },
  lookups: { type: Object, default: () => ({}) },
  csv: { type: Boolean, default: false },
  mode: { type: String, default: 'download' }, // 'download' | 'search'
  title: { type: String, default: '' },
})

const emit = defineEmits(['search'])

const loading = ref(false)
const modalOpen = ref(false)
const modalStatus = ref('loading') // 'loading' | 'success' | 'error'
const modalTitle = ref('')
const modalMessage = ref('')

const inputClasses = 'w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 shadow-sm placeholder:text-gray-400 transition-all duration-200 focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary disabled:cursor-not-allowed disabled:bg-gray-50 disabled:text-gray-500'

const activeFormat = ref('pdf')

async function submit(format = 'pdf') {
  // Search mode: emit values and return
  if (props.mode === 'search') {
    const values = {}
    Object.entries(props.form).forEach(([key, val]) => {
      if (val !== '' && val !== null && val !== undefined) {
        values[key] = val
      }
    })
    emit('search', values)
    return
  }

  activeFormat.value = format
  loading.value = true
  modalOpen.value = true
  modalStatus.value = 'loading'
  modalTitle.value = props.title?.replace(/^Relatório\s+/i, '') || 'Gerando relatório'
  modalMessage.value = format === 'csv' ? 'Gerando CSV, aguarde...' : 'Gerando relatório, aguarde...'

  const params = new URLSearchParams()
  Object.entries(props.form).forEach(([key, val]) => {
    if (val !== '' && val !== null && val !== undefined) {
      params.append(key, val)
    }
  })
  if (format === 'csv') {
    params.append('format', 'csv')
  }

  try {
    const response = await fetch(`${props.gerarRoute}?${params.toString()}`, {
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
      },
    })

    if (!response.ok) {
      // Try to get error message from response
      let errorMsg = 'Erro ao gerar o relatório.'
      try {
        const text = await response.text()
        // Check if it's JSON with error message
        const json = JSON.parse(text)
        if (json.message) errorMsg = json.message
      } catch {
        // not JSON, use default
      }

      if (response.status === 422) {
        errorMsg = 'Verifique os filtros e tente novamente.'
      }

      modalStatus.value = 'error'
      modalTitle.value = 'Erro'
      modalMessage.value = errorMsg
      loading.value = false
      return
    }

    const contentType = response.headers.get('content-type') || ''

    // If response is a redirect back (Inertia error), parse it
    if (contentType.includes('text/html') || contentType.includes('application/json')) {
      const text = await response.text()
      try {
        const json = JSON.parse(text)
        modalStatus.value = 'error'
        modalTitle.value = 'Erro'
        modalMessage.value = json.props?.flash?.error || json.message || 'Nenhum dado encontrado para os filtros informados.'
      } catch {
        modalStatus.value = 'error'
        modalTitle.value = 'Erro'
        modalMessage.value = 'Nenhum dado encontrado para os filtros informados.'
      }
      loading.value = false
      return
    }

    // Success - download the PDF
    const blob = await response.blob()
    const url = window.URL.createObjectURL(blob)

    // Extract filename from Content-Disposition header
    const disposition = response.headers.get('content-disposition') || ''
    const filenameMatch = disposition.match(/filename="?([^";\n]+)"?/)
    const fallbackName = format === 'csv' ? 'relatorio.csv' : 'relatorio.pdf'
    const filename = filenameMatch ? filenameMatch[1] : fallbackName

    const a = document.createElement('a')
    a.href = url
    a.download = filename
    document.body.appendChild(a)
    a.click()
    document.body.removeChild(a)
    window.URL.revokeObjectURL(url)

    modalStatus.value = 'success'
    modalTitle.value = 'Concluído'
    modalMessage.value = 'Relatório gerado com sucesso!'
  } catch (err) {
    modalStatus.value = 'error'
    modalTitle.value = 'Erro'
    modalMessage.value = 'Erro de conexão. Verifique sua rede e tente novamente.'
  } finally {
    loading.value = false
  }
}

function closeModal() {
  modalOpen.value = false
}

function clearFilters() {
  Object.keys(props.form).forEach(key => {
    props.form[key] = ''
  })
}
</script>

<template>
  <form @submit.prevent="submit(activeFormat)">
    <div class="grid grid-cols-1 gap-x-5 gap-y-4 sm:grid-cols-2 lg:grid-cols-3">
      <div v-for="filter in filters" :key="filter.name">
        <InputLabel :for="filter.name" :value="filter.label" class="mb-1.5" />

        <!-- Date -->
        <input
          v-if="filter.type === 'date'"
          :id="filter.name"
          type="date"
          v-model="form[filter.name]"
          class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary"
          :class="form[filter.name] ? 'text-gray-900' : 'text-gray-400'"
          :required="filter.required"
        />

        <!-- Select (genérico: lookups → filter.options → empresas fallback) -->
        <select
          v-else-if="filter.type === 'select'"
          :id="filter.name"
          v-model="form[filter.name]"
          class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary"
          :class="form[filter.name] === '' || form[filter.name] === null ? 'text-gray-400' : 'text-gray-900'"
          :required="filter.required"
        >
          <!-- Lookups dinâmicos (do controller) -->
          <template v-if="lookups[filter.name]">
            <option value="" class="text-gray-400">{{ filter.placeholder || 'Selecione...' }}</option>
            <option
              v-for="opt in lookups[filter.name]"
              :key="opt.value"
              :value="opt.value"
              class="text-gray-900"
            >
              {{ opt.label }}
            </option>
          </template>

          <!-- Options estáticos (do config) -->
          <template v-else-if="filter.options">
            <option
              v-for="(label, value) in filter.options"
              :key="value"
              :value="value"
              :class="value === '' ? 'text-gray-400' : 'text-gray-900'"
            >
              {{ label }}
            </option>
          </template>

          <!-- Fallback: empresas (retrocompatível) -->
          <template v-else>
            <option value="" class="text-gray-400">Todas</option>
            <option
              v-for="emp in empresas"
              :key="emp.ep"
              :value="emp.ep"
              class="text-gray-900"
            >
              {{ emp.ep }} - {{ emp.empresa }}
            </option>
          </template>
        </select>

        <!-- Radio (segmented control) -->
        <RadioGroup
          v-else-if="filter.type === 'radio'"
          :name="filter.name"
          :options="filter.options"
          v-model="form[filter.name]"
          class="mt-1.5"
        />

        <!-- Text (default) -->
        <input
          v-else
          :id="filter.name"
          type="text"
          v-model="form[filter.name]"
          :class="inputClasses"
          :required="filter.required"
        />

        <InputError :message="form.errors?.[filter.name]" class="mt-1" />
      </div>
    </div>

    <div class="flex items-center gap-3 border-t border-gray-100 pt-4 mt-4">
      <!-- Search mode -->
      <template v-if="mode === 'search'">
        <PrimaryButton type="submit">
          <MagnifyingGlassIcon class="h-4 w-4" />
          <span>Pesquisar</span>
        </PrimaryButton>
      </template>

      <!-- Download mode (default) -->
      <template v-else>
        <PrimaryButton
          type="submit"
          @click="activeFormat = 'pdf'"
          :disabled="loading"
        >
          <svg v-if="loading && activeFormat === 'pdf'" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
          </svg>
          <ArrowDownTrayIcon v-else class="h-4 w-4" />
          <span>{{ loading && activeFormat === 'pdf' ? 'Gerando...' : 'Gerar PDF' }}</span>
        </PrimaryButton>

        <SuccessButton
          v-if="csv"
          type="submit"
          @click="activeFormat = 'csv'"
          :disabled="loading"
        >
          <svg v-if="loading && activeFormat === 'csv'" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
          </svg>
          <TableCellsIcon v-else class="h-4 w-4" />
          <span>{{ loading && activeFormat === 'csv' ? 'Gerando...' : 'Gerar CSV' }}</span>
        </SuccessButton>
      </template>

      <SecondaryButton
        type="button"
        @click="clearFilters"
        :disabled="loading"
      >
        Limpar filtros
      </SecondaryButton>
    </div>
  </form>

  <!-- Status modal -->
  <StatusModal
    :show="modalOpen"
    :status="modalStatus"
    :title="modalTitle"
    :message="modalMessage"
    @close="closeModal"
  />
</template>
