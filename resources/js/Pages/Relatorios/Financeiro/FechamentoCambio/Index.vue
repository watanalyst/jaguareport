<script setup>
import { reactive, computed } from 'vue'
import ReportPageLayout from '@/Components/Reports/ReportPageLayout.vue'
import FilterPanel from '@/Components/Reports/FilterPanel.vue'

const props = defineProps({
  title: String,
  filters: Array,
  bancos: { type: Array, default: () => [] },
})

const form = reactive({})
props.filters.forEach(f => { form[f.name] = '' })

const gerarRoute = route('relatorios.financeiro.fechamento_cambio.gerar')

const lookups = computed(() => ({
  cod_banco: props.bancos.map(b => ({
    value: b.cod_banco,
    label: b.banco,
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
      csv
    />
  </ReportPageLayout>
</template>
