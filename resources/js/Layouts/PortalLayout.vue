<script setup>
import { Link, usePage, router } from '@inertiajs/vue3'
import { DocumentTextIcon, FolderIcon, HomeIcon } from '@heroicons/vue/24/outline'
import { Bars3Icon, ChevronDownIcon } from '@heroicons/vue/24/solid'
import { ArrowRightOnRectangleIcon } from '@heroicons/vue/24/outline'
import { ref, computed, reactive, onMounted, onBeforeUnmount, watch } from 'vue'

const page = usePage()
const nav = page.props.navigation || []
const user = computed(() => page.props.auth?.user)
const sidebarOpen = ref(localStorage.getItem('sidebarOpen') !== 'false')
const menuOpen = ref(false)

const userInitials = computed(() => {
  const name = user.value?.name || ''
  if (!name) return 'U'
  const parts = name.trim().split(' ')
  if (parts.length >= 2) return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase()
  return name.substring(0, 2).toUpperCase()
})

function isActive(routeName) {
  const path = route(routeName, undefined, false)
  return page.url === path || page.url.startsWith(path + '/')
}

// --- Section collapse state ---
const SECTIONS_KEY = 'sidebarSections'

function loadSectionState() {
  try { return JSON.parse(localStorage.getItem(SECTIONS_KEY)) || {} }
  catch { return {} }
}

const sectionExpanded = reactive((() => {
  const saved = loadSectionState()
  const state = {}
  for (const section of nav) {
    const key = section.key || section.title
    // Auto-expand if contains active route, otherwise use saved state (default: expanded)
    const hasActive = section.items.some(item => isActive(item.routeName))
    state[key] = hasActive ? true : (key in saved ? saved[key] : true)
  }
  return state
})())

function toggleSection(key) {
  sectionExpanded[key] = !sectionExpanded[key]
  localStorage.setItem(SECTIONS_KEY, JSON.stringify({ ...sectionExpanded }))
}

watch(() => page.url, () => {
  for (const section of nav) {
    const key = section.key || section.title
    if (section.items.some(item => isActive(item.routeName))) {
      sectionExpanded[key] = true
      localStorage.setItem(SECTIONS_KEY, JSON.stringify({ ...sectionExpanded }))
    }
  }
})

function toggleSidebar() {
  sidebarOpen.value = !sidebarOpen.value
  localStorage.setItem('sidebarOpen', sidebarOpen.value)
}

function toggleMenu(e) {
  e.stopPropagation()
  menuOpen.value = !menuOpen.value
}

function handleClickOutside(event) {
  const menu = document.getElementById('user-menu')
  if (menu && !menu.contains(event.target)) {
    menuOpen.value = false
  }
}

function handleKeydown(event) {
  if (event.key === 'Escape') menuOpen.value = false
}

function logout() {
  router.post(route('logout'), {}, {
    onSuccess: () => {
      window.close()
    },
  })
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
  document.addEventListener('keydown', handleKeydown)
})

onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutside)
  document.removeEventListener('keydown', handleKeydown)
})
</script>

<template>
  <div class="min-h-screen">
    <!-- Accent bar (topo global) -->
    <div class="fixed inset-x-0 top-0 z-50 h-[2px]" style="background: linear-gradient(to right, #0A1E44, #093F87, #1565C0)" />

    <!-- Sidebar -->
    <aside
      :class="[
        'fixed inset-y-0 left-0 z-40 flex flex-col pt-[2px] transition-all duration-300',
        sidebarOpen ? 'w-56 overflow-hidden' : 'w-14 overflow-visible',
      ]"
    >
      <div class="flex flex-1 flex-col" style="background: linear-gradient(180deg, #071631 0%, #0A1E44 40%, #082040 100%)">
        <!-- Glow overlay -->
        <div class="pointer-events-none absolute inset-0 opacity-30" style="background: radial-gradient(ellipse at 50% 0%, rgba(9,63,135,0.3) 0%, transparent 70%)" />

        <!-- Brand -->
        <Link
          :href="route('dashboard')"
          class="relative flex h-14 items-center justify-center cursor-pointer"
          style="background: linear-gradient(180deg, rgba(9,63,135,0.18) 0%, transparent 100%); box-shadow: inset 0 -1px 0 rgba(255,255,255,0.08), 0 2px 8px rgba(0,0,0,0.25)"
        >
          <span v-if="sidebarOpen" class="text-[22px] font-black text-white tracking-tight whitespace-nowrap drop-shadow-[0_0_10px_rgba(21,101,192,0.4)]">
            Jaguá<span class="text-primary-light">Report</span>
          </span>
          <span v-else class="text-lg font-black text-white drop-shadow-[0_0_8px_rgba(21,101,192,0.4)]">
            J<span class="text-primary-light">R</span>
          </span>
        </Link>

        <!-- Navigation -->
        <nav class="relative flex-1 py-4 space-y-0" :class="sidebarOpen ? 'px-3' : 'px-1'">
          <!-- Painel header -->
          <Link
            v-if="sidebarOpen"
            :href="route('dashboard')"
            class="flex w-full items-center gap-2 rounded-md px-3 py-1.5 mb-1
                   text-[11px] font-bold uppercase tracking-widest
                   hover:text-gray-200 hover:bg-white/5 transition-colors duration-150"
            :class="page.url === '/' ? 'text-white' : 'text-gray-300/80'"
          >
            <HomeIcon class="h-3.5 w-3.5" />
            Painel
          </Link>
          <Link
            v-else
            :href="route('dashboard')"
            class="group relative flex items-center justify-center rounded-lg py-2.5 mb-2 transition-all duration-200"
            :class="page.url === '/' ? 'bg-white/15 text-white' : 'text-gray-300 hover:bg-white/10 hover:text-white'"
          >
            <div
              v-if="page.url === '/'"
              class="absolute left-0 top-1/2 -translate-y-1/2 w-[3px] h-5 rounded-r-full bg-primary-light shadow-[0_0_8px_rgba(21,101,192,0.6)]"
            />
            <HomeIcon class="h-5 w-5 flex-shrink-0" />
            <span
              class="absolute left-full ml-3 whitespace-nowrap text-white text-xs py-1.5 px-3 rounded-lg shadow-lg opacity-0 group-hover:opacity-100 transition-all duration-200 z-50 pointer-events-none"
              style="background: #0A1E44"
            >
              Painel
            </span>
          </Link>

          <!-- Separador entre Painel e seções (sidebar colapsado) -->
          <div v-if="!sidebarOpen" class="mx-2 my-2 border-t border-white/10" />

          <div v-for="(section, index) in nav" :key="section.key || section.title">

            <!-- Section header (expanded sidebar) -->
            <button
              v-if="sidebarOpen"
              type="button"
              @click="toggleSection(section.key || section.title)"
              class="flex w-full items-center justify-between rounded-md px-3 py-1.5
                     text-[11px] font-bold uppercase tracking-widest text-gray-300/80
                     hover:text-gray-200 hover:bg-white/5 transition-colors duration-150
                     focus:outline-none focus-visible:ring-1 focus-visible:ring-white/20"
              :class="index > 0 ? 'mt-4 mb-1' : 'mb-1'"
            >
              <span class="flex items-center gap-2">
                <FolderIcon class="h-3.5 w-3.5" />
                {{ section.title }}
              </span>
              <ChevronDownIcon
                class="h-3 w-3 transition-transform duration-200"
                :class="sectionExpanded[section.key || section.title] ? '' : '-rotate-90'"
              />
            </button>

            <!-- Collapsed sidebar: separator between sections -->
            <div v-if="!sidebarOpen && index > 0" class="mx-2 my-2 border-t border-white/10" />

            <!-- Section items with CSS grid height transition -->
            <div
              class="grid transition-[grid-template-rows] duration-200 ease-out"
              :style="{ gridTemplateRows: (!sidebarOpen || sectionExpanded[section.key || section.title]) ? '1fr' : '0fr' }"
            >
              <div :class="sidebarOpen ? 'overflow-hidden' : ''">
                <div class="space-y-1">
                  <Link
                    v-for="item in section.items"
                    :key="item.routeName"
                    :href="route(item.routeName)"
                    prefetch
                    class="group relative flex items-center rounded-lg text-sm font-medium transition-all duration-200"
                    :class="[
                      sidebarOpen ? 'gap-3 py-2.5 px-4' : 'justify-center py-2.5',
                      isActive(item.routeName)
                        ? 'bg-white/15 text-white'
                        : 'text-gray-300 hover:bg-white/10 hover:text-white'
                    ]"
                  >
                    <!-- Active indicator -->
                    <div
                      v-if="isActive(item.routeName)"
                      class="absolute left-0 top-1/2 -translate-y-1/2 w-[3px] h-5 rounded-r-full bg-primary-light shadow-[0_0_8px_rgba(21,101,192,0.6)]"
                    />

                    <DocumentTextIcon class="h-5 w-5 flex-shrink-0" />

                    <!-- Label (expanded) -->
                    <span v-if="sidebarOpen" class="truncate">{{ item.label }}</span>

                    <!-- Tooltip (collapsed) -->
                    <span
                      v-else
                      class="absolute left-full ml-3 whitespace-nowrap text-white text-xs py-1.5 px-3 rounded-lg shadow-lg opacity-0 group-hover:opacity-100 transition-all duration-200 z-50 pointer-events-none"
                      style="background: #0A1E44"
                    >
                      {{ item.label }}
                    </span>
                  </Link>
                </div>
              </div>
            </div>
          </div>
        </nav>

        <!-- Footer -->
        <div class="relative border-t border-white/10 px-4 py-3 text-center">
          <span v-if="sidebarOpen" class="text-[10px] tracking-wider text-gray-500/50">Jaguareport v1.0</span>
          <span v-else class="text-[10px] tracking-wider text-gray-500/50">v1.0</span>
        </div>
      </div>
    </aside>

    <!-- Main content -->
    <div
      class="flex flex-col min-h-screen transition-all duration-300"
      :class="sidebarOpen ? 'ml-56' : 'ml-14'"
    >
      <!-- Navbar -->
      <header class="sticky top-[2px] z-20 flex h-14 items-center justify-between bg-white px-4 lg:px-6" style="box-shadow: 0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04)">
        <div class="flex items-center">
          <!-- Hamburger -->
          <button
            type="button"
            class="p-2 rounded-lg text-gray-400 hover:text-[#093F87] hover:bg-gray-100/80 transition-all duration-200"
            @click="toggleSidebar"
          >
            <Bars3Icon class="h-5 w-5" />
          </button>

          <!-- Divider -->
          <div class="ml-3 mr-3 h-6 w-px bg-gray-200" />

          <!-- Header slot -->
          <div>
            <slot name="header" />
          </div>
        </div>

        <!-- User menu (top right) -->
        <div v-if="user" id="user-menu" class="relative">
          <button
            @click="toggleMenu"
            class="flex items-center gap-3 pl-3 pr-2 py-1.5 rounded-xl border border-transparent hover:border-gray-200 hover:bg-gray-50/80 transition-all duration-200"
          >
            <!-- Avatar -->
            <div
              class="w-9 h-9 rounded-xl flex items-center justify-center text-white text-xs font-bold shadow-sm"
              style="background: linear-gradient(135deg, #0A1E44, #093F87)"
            >
              {{ userInitials }}
            </div>
            <div class="text-left hidden sm:block">
              <span class="font-semibold text-sm text-gray-800 block leading-tight">{{ user.name }}</span>
              <span class="text-[11px] text-gray-400 block leading-tight">{{ user.sc_user }}</span>
            </div>
            <ChevronDownIcon
              class="w-3.5 h-3.5 text-gray-400 transition-transform duration-200 ml-1"
              :class="{ 'rotate-180': menuOpen }"
            />
          </button>

          <!-- Dropdown -->
          <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0 -translate-y-1 scale-95"
            enter-to-class="opacity-100 translate-y-0 scale-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100 translate-y-0 scale-100"
            leave-to-class="opacity-0 -translate-y-1 scale-95"
          >
            <div
              v-if="menuOpen"
              class="absolute right-0 mt-2 w-52 bg-white rounded-xl z-50 overflow-hidden"
              style="border: 1px solid rgba(0,0,0,0.06); box-shadow: 0 12px 48px -8px rgba(0,0,0,0.12)"
            >
              <!-- Header -->
              <div class="flex items-center gap-3 px-4 py-3" style="background: #f8fafc; border-bottom: 1px solid rgba(0,0,0,0.04)">
                <div
                  class="w-10 h-10 rounded-xl flex items-center justify-center text-white text-sm font-bold flex-shrink-0"
                  style="background: linear-gradient(135deg, #0A1E44, #093F87)"
                >
                  {{ userInitials }}
                </div>
                <div class="min-w-0">
                  <p class="text-sm font-semibold text-gray-800 truncate">{{ user.name }}</p>
                  <p class="text-xs text-gray-400 mt-0.5">{{ user.sc_user }}</p>
                </div>
              </div>

              <div class="py-1">
                <div class="mx-3 border-t border-gray-100"></div>

                <button
                  @click="logout"
                  class="w-full text-left px-4 py-2.5 text-sm hover:bg-red-50 flex items-center gap-3 text-red-500 transition-colors"
                >
                  <ArrowRightOnRectangleIcon class="w-4 h-4" />
                  Sair
                </button>
              </div>
            </div>
          </Transition>
        </div>
      </header>

      <!-- Page content -->
      <main class="flex-1 px-6 pb-6 pt-3 lg:px-8 lg:pb-8 lg:pt-4">
        <slot />
      </main>
    </div>
  </div>
</template>
