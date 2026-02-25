<script setup>
import PortalLayout from '@/Layouts/PortalLayout.vue'
import { Head, usePage } from '@inertiajs/vue3'

const user = usePage().props.auth?.user
</script>

<template>
  <Head title="Dashboard" />

  <PortalLayout>
    <template #header>
      <h2 class="text-sm font-semibold text-gray-700">Dashboard</h2>
    </template>

    <div class="flex flex-1 items-center justify-center" style="min-height: calc(100vh - 140px)">
      <div class="text-center">
        <div
          class="mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-2xl text-white text-2xl font-bold shadow-lg"
          style="background: linear-gradient(135deg, #0A1E44, #093F87)"
        >
          JR
        </div>

        <h1 class="text-2xl font-bold text-gray-800">
          Bem-vindo ao <span style="color: #093F87">JaguáReport</span><span v-if="user">, {{ user.name.split(' ')[0] }}</span>!
        </h1>

        <p class="mt-2 text-gray-500">
          Selecione um relatório no menu lateral para começar.
        </p>
      </div>
    </div>
  </PortalLayout>
</template>
