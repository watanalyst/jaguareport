<script setup>
import { reactive, computed } from 'vue'
import ReportPageLayout from '@/Components/Reports/ReportPageLayout.vue'
import FilterPanel from '@/Components/Reports/FilterPanel.vue'

const props = defineProps({
  title: String,
  filters: Array,
  empresas: { type: Array, default: () => [] },
})

const form = reactive({})
props.filters.forEach(f => { form[f.name] = '' })

const gerarRoute = route('relatorios.exportacao.debit_note.gerar')

const lookups = computed(() => ({
  cod_empresa: props.empresas.map(e => ({
    value: e.ep,
    label: `${e.ep} - ${e.den_reduz}`,
  })),
}))
</script>

<template>
  <ReportPageLayout :title="title">
    <FilterPanel
      :filters="filters"
      :form="form"
      :gerar-route="gerarRoute"
      :lookups="lookups"
      :title="title"
    />
  </ReportPageLayout>
</template>
