<script setup>
import { XMarkIcon } from '@heroicons/vue/24/outline'

defineProps({
  show: { type: Boolean, required: true },
  docLabel: { type: String, default: '' },
})

const emit = defineEmits(['select', 'close'])
</script>

<template>
  <Teleport to="body">
    <Transition
      enter-active-class="transition duration-200 ease-out"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition duration-150 ease-in"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div v-if="show" class="fixed inset-0 z-[100] flex items-start justify-center pt-32 bg-gray-900/50 backdrop-blur-sm">
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
            <div class="h-1" style="background: linear-gradient(to right, #0A1E44, #093F87, #1565C0)" />

            <!-- Close button -->
            <button
              @click="emit('close')"
              class="absolute top-3 right-3 p-1 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors"
            >
              <XMarkIcon class="h-5 w-5" />
            </button>

            <div class="px-6 py-8 text-center space-y-5">
              <div>
                <h3 class="text-base font-semibold text-gray-800">{{ docLabel }}</h3>
                <p class="mt-1 text-sm text-gray-500">Selecione o tipo de impressão:</p>
              </div>

              <div class="flex items-center justify-center gap-3">
                <button
                  @click="emit('select', 'original')"
                  class="inline-flex items-center gap-2 rounded-lg px-5 py-2.5 text-sm font-semibold text-white shadow-md transition-all duration-200 hover:-translate-y-px hover:brightness-110"
                  style="background: linear-gradient(135deg, #093F87 0%, #0B56B3 100%)"
                >
                  Original
                </button>
                <button
                  @click="emit('select', 'copia')"
                  class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition-all duration-200 hover:-translate-y-px hover:bg-gray-50 hover:border-gray-400"
                >
                  Cópia
                </button>
              </div>
            </div>
          </div>
        </Transition>
      </div>
    </Transition>
  </Teleport>
</template>
