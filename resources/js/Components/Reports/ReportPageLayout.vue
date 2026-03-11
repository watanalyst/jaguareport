<script setup>
import { Head, usePage } from '@inertiajs/vue3'
import PortalLayout from '@/Layouts/PortalLayout.vue'
import { ChevronRightIcon, ExclamationTriangleIcon } from '@heroicons/vue/24/outline'

defineProps({
  title: String,
  section: { type: String, default: 'Financeiro' },
})

const flash = usePage().props.flash || {}
</script>

<template>
  <Head :title="title" />

  <PortalLayout>
    <template #header>
      <div class="flex items-center gap-1.5 text-sm text-gray-400">
        <span>Relatórios</span>
        <ChevronRightIcon class="h-3.5 w-3.5" />
        <span>{{ section }}</span>
        <ChevronRightIcon class="h-3.5 w-3.5" />
        <span class="font-semibold text-gray-700">{{ title }}</span>
      </div>
    </template>

    <!-- Flash error -->
    <div v-if="flash.error" class="mb-3 flex items-center gap-3 rounded-xl bg-red-50 px-4 py-3">
      <ExclamationTriangleIcon class="h-5 w-5 flex-shrink-0 text-red-500" />
      <p class="text-sm text-red-700">{{ flash.error }}</p>
    </div>

    <!-- Card principal -->
    <div class="overflow-hidden rounded-2xl bg-white shadow-[0_4px_16px_rgba(0,0,0,0.06),0_1px_3px_rgba(0,0,0,0.04)]">
      <!-- Accent bar -->
      <div class="h-1" style="background: linear-gradient(to right, #0A1E44, #093F87, #1565C0)" />

      <!-- Content -->
      <div class="px-5 py-5">
        <slot />
      </div>
    </div>
  </PortalLayout>
</template>
