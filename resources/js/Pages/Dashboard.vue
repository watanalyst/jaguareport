<script setup>
import PortalLayout from '@/Layouts/PortalLayout.vue'
import { ExclamationTriangleIcon } from '@heroicons/vue/24/outline'
import { Head, usePage } from '@inertiajs/vue3'

const page = usePage()
const user = page.props.auth?.user
const flash = page.props.flash || {}
const firstName = user?.name?.split(' ')[0] || ''
const hour = new Date().getHours()
const greeting = hour < 12 ? 'Bom dia' : hour < 18 ? 'Boa tarde' : 'Boa noite'
</script>

<template>
  <Head title="Painel" />

  <PortalLayout>
    <template #header>
      <h2 class="text-sm font-semibold text-gray-700">Painel</h2>
    </template>

    <!-- Flash error -->
    <div v-if="flash.error" class="mb-4 flex items-center gap-3 rounded-xl bg-red-50 px-4 py-3">
      <ExclamationTriangleIcon class="h-5 w-5 flex-shrink-0 text-red-500" />
      <p class="text-sm text-red-700">{{ flash.error }}</p>
    </div>

    <div class="flex flex-1 items-start justify-center pt-24">
      <!-- Card no padrão dos relatórios -->
      <div class="w-full max-w-lg overflow-hidden rounded-2xl bg-white shadow-[0_4px_16px_rgba(0,0,0,0.06),0_1px_3px_rgba(0,0,0,0.04)]">
        <div class="h-1.5" style="background: linear-gradient(to right, #0A1E44, #093F87, #1565C0)" />

        <div class="px-6 py-10 text-center">
          <p class="text-sm font-medium tracking-wide text-gray-400">
            {{ greeting }}
          </p>

          <h1 class="mt-3 text-2xl font-bold text-gray-800">
            <span v-if="firstName">{{ firstName }}, bem-vindo ao </span>
            <span v-else>Bem-vindo ao </span>
            <span style="color: #093F87">JaguáReport</span>
          </h1>

          <div class="mx-auto mt-5 h-px w-16" style="background: linear-gradient(to right, transparent, #093F87, transparent)" />

          <p class="mt-5 text-sm text-gray-500 leading-relaxed">
            Selecione um relatório no menu lateral para começar.
          </p>
        </div>
      </div>
    </div>
  </PortalLayout>
</template>
