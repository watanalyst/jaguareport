<script setup>
import PortalLayout from '@/Layouts/PortalLayout.vue'
import { Head, usePage } from '@inertiajs/vue3'

const user = usePage().props.auth?.user
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

    <div class="flex flex-1 items-center justify-center" style="min-height: calc(100vh - 140px)">
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
