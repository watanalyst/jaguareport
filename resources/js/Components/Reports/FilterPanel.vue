<script setup>
import { InputLabel, InputError } from '@jagua/ui'
import RadioGroup from './RadioGroup.vue'
import { ArrowDownTrayIcon, TableCellsIcon, CheckCircleIcon, ExclamationTriangleIcon, XMarkIcon } from '@heroicons/vue/24/outline'
import { ref } from 'vue'

const props = defineProps({
  filters: { type: Array, required: true },
  form: { type: Object, required: true },
  gerarRoute: { type: String, required: true },
  empresas: { type: Array, default: () => [] },
  lookups: { type: Object, default: () => ({}) },
  csv: { type: Boolean, default: false },
})

const loading = ref(false)
const modalOpen = ref(false)
const modalStatus = ref('loading') // 'loading' | 'success' | 'error'
const modalMessage = ref('')

const inputClasses = 'w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 shadow-sm placeholder:text-gray-400 transition-all duration-200 focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary disabled:cursor-not-allowed disabled:bg-gray-50 disabled:text-gray-500'

const activeFormat = ref('pdf')

async function submit(format = 'pdf') {
  activeFormat.value = format
  loading.value = true
  modalOpen.value = true
  modalStatus.value = 'loading'
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
        modalMessage.value = json.props?.flash?.error || json.message || 'Nenhum dado encontrado para os filtros informados.'
      } catch {
        modalStatus.value = 'error'
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
    modalMessage.value = 'Relatório gerado com sucesso!'

    setTimeout(() => {
      modalOpen.value = false
    }, 1500)
  } catch (err) {
    modalStatus.value = 'error'
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
  <form @submit.prevent="submit(activeFormat)" class="space-y-6">
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
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

    <div class="flex items-center gap-3 border-t border-gray-200 pt-6 mt-2">
      <button
        type="submit"
        @click="activeFormat = 'pdf'"
        :disabled="loading"
        class="inline-flex items-center justify-center gap-2 rounded-lg px-5 py-2.5 text-sm font-semibold text-white shadow-[0_4px_14px_0_rgba(9,63,135,0.35)] transition-all duration-200 hover:-translate-y-px hover:brightness-110 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:translate-y-0"
        :style="{ background: 'linear-gradient(135deg, #093F87 0%, #0B56B3 100%)' }"
      >
        <!-- Spinner -->
        <svg v-if="loading && activeFormat === 'pdf'" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
        </svg>
        <ArrowDownTrayIcon v-else class="h-4 w-4" />
        <span>{{ loading && activeFormat === 'pdf' ? 'Gerando...' : 'Gerar PDF' }}</span>
      </button>

      <button
        v-if="csv"
        type="submit"
        @click="activeFormat = 'csv'"
        :disabled="loading"
        class="inline-flex items-center justify-center gap-2 rounded-lg px-5 py-2.5 text-sm font-semibold text-white shadow-[0_4px_14px_0_rgba(21,101,55,0.35)] transition-all duration-200 hover:-translate-y-px hover:brightness-110 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:translate-y-0"
        :style="{ background: 'linear-gradient(135deg, #15803d 0%, #16a34a 100%)' }"
      >
        <svg v-if="loading && activeFormat === 'csv'" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
        </svg>
        <TableCellsIcon v-else class="h-4 w-4" />
        <span>{{ loading && activeFormat === 'csv' ? 'Gerando...' : 'Gerar CSV' }}</span>
      </button>

      <button
        type="button"
        @click="clearFilters"
        :disabled="loading"
        class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm font-medium text-gray-600 shadow-sm transition-all duration-200 hover:border-blue-300 hover:text-blue-600 hover:bg-blue-50 hover:-translate-y-px disabled:opacity-50 disabled:cursor-not-allowed"
      >
        Limpar filtros
      </button>
    </div>
  </form>

  <!-- Modal overlay -->
  <Teleport to="body">
    <Transition
      enter-active-class="transition duration-200 ease-out"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition duration-150 ease-in"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div v-if="modalOpen" class="fixed inset-0 z-[100] flex items-start justify-center pt-32 bg-gray-900/50 backdrop-blur-sm">
        <Transition
          enter-active-class="transition duration-200 ease-out"
          enter-from-class="opacity-0 scale-95 translate-y-2"
          enter-to-class="opacity-100 scale-100 translate-y-0"
          leave-active-class="transition duration-150 ease-in"
          leave-from-class="opacity-100 scale-100"
          leave-to-class="opacity-0 scale-95"
        >
          <div class="relative w-full max-w-sm mx-4 overflow-hidden rounded-2xl bg-white shadow-2xl">
            <!-- Accent bar -->
            <div
              class="h-1"
              :style="{
                background: modalStatus === 'error'
                  ? 'linear-gradient(to right, #dc2626, #ef4444, #f87171)'
                  : 'linear-gradient(to right, #0A1E44, #093F87, #1565C0)'
              }"
            />

            <!-- Close button (only on error) -->
            <button
              v-if="modalStatus === 'error'"
              @click="closeModal"
              class="absolute top-3 right-3 p-1 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors"
            >
              <XMarkIcon class="h-5 w-5" />
            </button>

            <div class="px-6 py-8 text-center">
              <!-- Loading -->
              <div v-if="modalStatus === 'loading'" class="space-y-4">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-primary/10">
                  <svg class="h-7 w-7 animate-spin text-primary" viewBox="0 0 24 24" fill="none">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" />
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                  </svg>
                </div>
                <div>
                  <h3 class="text-base font-semibold text-gray-800">Gerando relatório</h3>
                  <p class="mt-1 text-sm text-gray-500">{{ modalMessage }}</p>
                </div>
                <!-- Progress bar animation -->
                <div class="mx-auto w-48 h-1.5 rounded-full bg-gray-100 overflow-hidden">
                  <div class="h-full rounded-full animate-pulse" style="background: linear-gradient(90deg, #093F87, #1565C0); width: 60%; animation: progress 2s ease-in-out infinite" />
                </div>
              </div>

              <!-- Success -->
              <div v-else-if="modalStatus === 'success'" class="space-y-4">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-green-50">
                  <CheckCircleIcon class="h-7 w-7 text-green-500" />
                </div>
                <div>
                  <h3 class="text-base font-semibold text-gray-800">Concluído</h3>
                  <p class="mt-1 text-sm text-gray-500">{{ modalMessage }}</p>
                </div>
              </div>

              <!-- Error -->
              <div v-else-if="modalStatus === 'error'" class="space-y-4">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-red-50">
                  <ExclamationTriangleIcon class="h-7 w-7 text-red-500" />
                </div>
                <div>
                  <h3 class="text-base font-semibold text-gray-800">Erro</h3>
                  <p class="mt-1 text-sm text-gray-500">{{ modalMessage }}</p>
                </div>
                <button
                  @click="closeModal"
                  class="mt-2 inline-flex items-center rounded-lg px-4 py-2 text-sm font-medium text-gray-600 border border-gray-200 hover:bg-gray-50 transition-colors"
                >
                  Fechar
                </button>
              </div>
            </div>
          </div>
        </Transition>
      </div>
    </Transition>
  </Teleport>
</template>

<style scoped>
@keyframes progress {
  0% { transform: translateX(-100%); }
  50% { transform: translateX(80%); }
  100% { transform: translateX(-100%); }
}
</style>
