<script setup>
import { Link, usePage, router } from '@inertiajs/vue3'
import { DashboardLayout } from '@desenvolvimento/btz-components-vue'
import { computed } from 'vue'

const page = usePage()

const navigation = computed(() => page.props.navigation || [])

const dashUser = computed(() => {
  const u = page.props.auth?.user
  if (!u) return null
  return { name: u.name, subtitle: u.sc_user }
})

function handleLogout() {
  router.post(route('logout'), {}, {
    onSuccess: () => { window.close() },
  })
}
</script>

<template>
  <DashboardLayout
    app-name="Jaguá"
    app-name-highlight="Report"
    :navigation="navigation"
    :user="dashUser"
    :current-path="page.url"
    :home-href="route('dashboard', undefined, false)"
    :link-component="Link"
    version="JaguáReport v1.0"
    @logout="handleLogout"
  >
    <template #header>
      <slot name="header" />
    </template>

    <slot />
  </DashboardLayout>
</template>
