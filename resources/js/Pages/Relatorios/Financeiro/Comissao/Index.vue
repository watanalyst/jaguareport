<script setup>
import { reactive } from 'vue'
import ReportPageLayout from '@/Components/Reports/ReportPageLayout.vue'
import FilterPanel from '@/Components/Reports/FilterPanel.vue'

const props = defineProps({
  title: String,
  filters: Array,
  empresas: { type: Array, default: () => [] },
})

// Build reactive form from filter config
const form = reactive({})
props.filters.forEach(f => { form[f.name] = '' })

const gerarRoute = route('relatorios.financeiro.comissao.gerar')
</script>

<template>
  <ReportPageLayout :title="title">
    <FilterPanel
      :filters="filters"
      :form="form"
      :gerar-route="gerarRoute"
      :empresas="empresas"
    />
  </ReportPageLayout>
</template>
