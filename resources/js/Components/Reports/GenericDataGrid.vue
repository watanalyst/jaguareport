<script setup>
import { ref, computed, watch } from 'vue'
import { SecondaryButton, SuccessButton } from '@jagua/ui'
import {
  ArrowUpIcon,
  ArrowDownIcon,
  FunnelIcon,
  XMarkIcon,
  TableCellsIcon,
  ArrowsPointingOutIcon,
} from '@heroicons/vue/24/solid'
import * as XLSX from 'xlsx'

const props = defineProps({
  columns: { type: Array, required: true },
  data: { type: Array, required: true },
  pageSize: { type: Number, default: 25 },
  exportFilename: { type: String, default: 'relatorio' },
  selectable: { type: Boolean, default: false },
})

const emit = defineEmits(['update:selected'])

// Selection state
const selectedSet = ref(new Set())
const selectAllChecked = ref(false)

function toggleSelectAll() {
  if (selectAllChecked.value) {
    selectedSet.value = new Set(sortedData.value.map((_, i) => i))
  } else {
    selectedSet.value = new Set()
  }
  emitSelected()
}

function toggleRow(index) {
  // Convert paginated index to sortedData index
  const globalIndex = (currentPage.value - 1) * perPage.value + index
  const s = new Set(selectedSet.value)
  if (s.has(globalIndex)) {
    s.delete(globalIndex)
  } else {
    s.add(globalIndex)
  }
  selectedSet.value = s
  selectAllChecked.value = s.size === sortedData.value.length
  emitSelected()
}

function isRowSelected(index) {
  const globalIndex = (currentPage.value - 1) * perPage.value + index
  return selectedSet.value.has(globalIndex)
}

function emitSelected() {
  const rows = [...selectedSet.value].sort().map(i => sortedData.value[i]).filter(Boolean)
  emit('update:selected', rows)
}

// Clear selection when data changes
watch(() => props.data, () => {
  selectedSet.value = new Set()
  selectAllChecked.value = false
  emitSelected()
})

// Sort state
const sortKey = ref('')
const sortOrder = ref('asc')

// Column filters
const columnFilters = ref({})
const showFilters = ref(false)

// Pagination
const currentPage = ref(1)
const perPage = ref(props.pageSize)

// Initialize column filters
props.columns.forEach(col => {
  if (col.filterable !== false) {
    columnFilters.value[col.key] = ''
  }
})

// Reset page on data change
watch(() => props.data, () => { currentPage.value = 1 })
watch(columnFilters, () => { currentPage.value = 1 }, { deep: true })

// Filtered data
const filteredData = computed(() => {
  let result = props.data

  for (const [key, value] of Object.entries(columnFilters.value)) {
    if (!value) continue
    const term = value.toLowerCase()
    const col = props.columns.find(c => c.key === key)
    result = result.filter(row => {
      const cell = row[key]
      if (cell == null) return false
      const display = col?.filterMap?.[cell] ?? String(cell)
      return display.toLowerCase().includes(term)
    })
  }

  return result
})

// Sorted data
const sortedData = computed(() => {
  if (!sortKey.value) return filteredData.value

  const col = props.columns.find(c => c.key === sortKey.value)
  const type = col?.type || 'text'

  return [...filteredData.value].sort((a, b) => {
    let va = a[sortKey.value]
    let vb = b[sortKey.value]
    if (va == null) va = ''
    if (vb == null) vb = ''

    let cmp = 0
    if (type === 'number' || type === 'currency') {
      cmp = (parseFloat(va) || 0) - (parseFloat(vb) || 0)
    } else if (type === 'date') {
      cmp = new Date(va || 0) - new Date(vb || 0)
    } else {
      cmp = String(va).localeCompare(String(vb), 'pt-BR', { sensitivity: 'base' })
    }

    return sortOrder.value === 'desc' ? -cmp : cmp
  })
})

// Pagination
const totalPages = computed(() => Math.max(1, Math.ceil(sortedData.value.length / perPage.value)))

const paginatedData = computed(() => {
  const start = (currentPage.value - 1) * perPage.value
  return sortedData.value.slice(start, start + perPage.value)
})

const visiblePages = computed(() => {
  const pages = []
  const total = totalPages.value
  const cur = currentPage.value

  if (total <= 7) {
    for (let i = 1; i <= total; i++) pages.push(i)
  } else {
    pages.push(1)
    if (cur > 3) pages.push('...')
    for (let i = Math.max(2, cur - 1); i <= Math.min(total - 1, cur + 1); i++) pages.push(i)
    if (cur < total - 2) pages.push('...')
    pages.push(total)
  }

  return pages
})

// Sort
function toggleSort(key) {
  const col = props.columns.find(c => c.key === key)
  if (!col?.sortable) return

  if (sortKey.value === key) {
    if (sortOrder.value === 'asc') {
      sortOrder.value = 'desc'
    } else {
      sortKey.value = ''
      sortOrder.value = 'asc'
    }
  } else {
    sortKey.value = key
    sortOrder.value = 'asc'
  }
}

// Format
function formatCell(value, col) {
  if (value == null || value === '') return '—'

  switch (col.type) {
    case 'date':
      try {
        const d = new Date(value)
        if (isNaN(d)) return value
        return d.toLocaleDateString('pt-BR')
      } catch {
        return value
      }
    case 'number':
      return Number(value).toLocaleString('pt-BR')
    case 'currency':
      return Number(value).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
    default:
      return String(value).trim()
  }
}

function cellAlign(col) {
  if (col.align) return `text-${col.align}`
  if (col.type === 'number' || col.type === 'currency') return 'text-right'
  return 'text-left'
}

// Fullscreen in new tab
function openFullscreen() {
  const cols = props.columns
  const rows = sortedData.value

  const dataJson = JSON.stringify(rows.map(row =>
    cols.reduce((obj, col) => { obj[col.key] = row[col.key]; return obj }, {})
  ))
  const colsJson = JSON.stringify(cols.map(col => ({
    key: col.key, label: col.label, type: col.type || 'text', align: col.align || '', sortable: !!col.sortable
  })))

  const html = `<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>${props.exportFilename} — Visualização expandida</title>
<style>
  *{margin:0;padding:0;box-sizing:border-box}
  body{font-family:Inter,system-ui,sans-serif;font-size:12px;color:#374151;padding:16px;background:#f3f4f6}
  .toolbar{display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;flex-wrap:wrap;gap:8px}
  .toolbar .left{font-size:13px;color:#6b7280}
  .toolbar .left b{color:#111827;font-weight:600}
  .toolbar .right{display:flex;align-items:center;gap:8px}
  .btn{padding:6px 14px;font-size:12px;font-weight:600;border:1px solid #d1d5db;border-radius:8px;background:#fff;color:#374151;cursor:pointer;transition:all .2s;box-shadow:0 2px 6px rgba(0,0,0,0.06)}
  .btn:hover{background:#f0f5ff;border-color:#93c5fd;color:#1d4ed8;box-shadow:0 3px 10px rgba(29,78,216,0.12)}
  .btn-excel{background:linear-gradient(135deg,#047857 0%,#059669 100%);color:#fff;border-color:#047857;font-weight:600;box-shadow:0 4px 14px rgba(4,120,87,0.35)}
  .btn-excel:hover{filter:brightness(1.1);box-shadow:0 4px 14px rgba(4,120,87,0.45)}
  .pg-select{padding:4px 24px 4px 10px;font-size:12px;font-weight:600;border:1px solid #e5e7eb;border-radius:8px;background:#fff;color:#374151;cursor:pointer}
  .pg-select:focus{outline:none;border-color:rgba(9,63,135,0.4);box-shadow:0 0 0 2px rgba(9,63,135,0.1)}
  .table-wrap{overflow:hidden;border-radius:12px;border:1px solid #e5e7eb;box-shadow:0 1px 3px rgba(0,0,0,0.06)}
  table{width:100%;border-collapse:collapse;background:#fff}
  thead tr{background:linear-gradient(180deg,#0B56B3 0%,#093F87 100%);color:#fff}
  thead th{padding:10px 12px;white-space:nowrap;font-weight:600;font-size:11px;text-transform:uppercase;letter-spacing:0.05em;user-select:none}
  thead th.sortable{cursor:pointer;transition:background .15s}
  thead th.sortable:hover{background:rgba(255,255,255,0.1)}
  .sort-arrow{display:inline-flex;margin-left:6px;opacity:0.25;vertical-align:middle}
  .sort-arrow.active{opacity:1;filter:drop-shadow(0 0 4px rgba(255,255,255,0.5))}
  .sort-arrow svg{width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:2.5;stroke-linecap:round;stroke-linejoin:round}
  tbody tr{transition:background .15s}
  tbody tr:hover{background:#bfdbfe !important}
  td{padding:8px 12px;white-space:nowrap;border-bottom:1px solid rgba(243,244,246,0.8)}
  .pagination{display:flex;align-items:center;justify-content:space-between;margin-top:16px;flex-wrap:wrap;gap:8px}
  .pagination .page-size{display:flex;align-items:center;gap:6px;font-size:12px;color:#6b7280}
  .pg-nav{display:inline-flex;align-items:center;gap:4px;border:1px solid #e5e7eb;border-radius:8px;background:#fff;padding:4px}
  .pg-btn{padding:4px 10px;font-size:12px;font-weight:600;border:none;border-radius:6px;background:transparent;color:#4b5563;cursor:pointer;min-width:32px;text-align:center;transition:all .15s}
  .pg-btn:hover:not(:disabled):not(.active){background:#f3f4f6;color:#1f2937}
  .pg-btn.active{background:linear-gradient(180deg,#0B56B3 0%,#093F87 100%);color:#fff;box-shadow:0 1px 3px rgba(9,63,135,0.3)}
  .pg-btn.nav-btn{font-weight:500;color:#4b5563}
  .pg-btn:disabled{opacity:.35;cursor:default}
  .pg-dots{padding:4px 2px;color:#9ca3af;font-size:12px}
  @media print{.toolbar,.pagination{display:none}body{padding:0;background:#fff}.table-wrap{border:none;box-shadow:none;border-radius:0}}
</style>
<script src="https://cdn.sheetjs.com/xlsx-0.20.3/package/dist/xlsx.full.min.js"><\/script>
</head>
<body>
<div class="toolbar">
  <span class="left"><b id="countDisplay">0</b> registro(s)</span>
  <div class="right">
    <button class="btn btn-excel" onclick="exportExcel()">Excel</button>
    <button class="btn" onclick="window.print()">Imprimir</button>
  </div>
</div>
<div class="table-wrap">
  <table>
    <thead><tr id="tableHead"></tr></thead>
    <tbody id="tableBody"></tbody>
  </table>
</div>
<div class="pagination" id="paginationBar">
  <div class="page-size">
    <span>Exibir</span>
    <select class="pg-select" id="pageSizeSelect" onchange="changePageSize()">
      <option value="25">25</option>
      <option value="50">50</option>
      <option value="100">100</option>
      <option value="0">Todos</option>
    </select>
    <span>por página</span>
  </div>
  <nav class="pg-nav" id="pageButtons"></nav>
</div>
<script>
var DATA=${dataJson};
var COLS=${colsJson};
var currentPage=1;
var pageSize=25;
var filename='${props.exportFilename}';
var sortKey='';
var sortOrder='asc';

function formatCell(v,col){
  if(v==null||v==='')return '—';
  if(col.type==='date'){try{var d=new Date(v);return isNaN(d)?v:d.toLocaleDateString('pt-BR')}catch(e){return v}}
  if(col.type==='number')return Number(v).toLocaleString('pt-BR');
  if(col.type==='currency')return Number(v).toLocaleString('pt-BR',{minimumFractionDigits:2,maximumFractionDigits:2});
  return String(v).trim();
}
function getAlign(col){
  if(col.align)return 'text-align:'+col.align;
  return(col.type==='number'||col.type==='currency')?'text-align:right':'text-align:left';
}
function toggleSort(key){
  var col=COLS.find(function(c){return c.key===key});
  if(!col||!col.sortable)return;
  if(sortKey===key){if(sortOrder==='asc')sortOrder='desc';else{sortKey='';sortOrder='asc'}}
  else{sortKey=key;sortOrder='asc'}
  sortData();currentPage=1;renderHead();render();
}
function sortData(){
  if(!sortKey){return}
  var col=COLS.find(function(c){return c.key===sortKey});
  var type=col?col.type:'text';
  DATA.sort(function(a,b){
    var va=a[sortKey],vb=b[sortKey];
    if(va==null)va='';if(vb==null)vb='';
    var cmp=0;
    if(type==='number'||type==='currency')cmp=(parseFloat(va)||0)-(parseFloat(vb)||0);
    else if(type==='date')cmp=new Date(va||0)-new Date(vb||0);
    else cmp=String(va).localeCompare(String(vb),'pt-BR',{sensitivity:'base'});
    return sortOrder==='desc'?-cmp:cmp;
  });
}
function renderHead(){
  document.getElementById('tableHead').innerHTML=COLS.map(function(c){
    var cls=c.sortable?' class="sortable" onclick="toggleSort(\\''+c.key+'\\')"':'';
    var arrow='';
    var svgUp='<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M12 19.5v-15m0 0l-6.75 6.75M12 4.5l6.75 6.75"/></svg>';
    var svgDown='<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M12 4.5v15m0 0l6.75-6.75M12 19.5l-6.75-6.75"/></svg>';
    if(c.sortable){
      if(sortKey===c.key){arrow='<span class="sort-arrow active">'+(sortOrder==='asc'?svgUp:svgDown)+'</span>'}
      else{arrow='<span class="sort-arrow">'+svgUp+'</span>'}
    }
    return '<th'+cls+' style="'+getAlign(c)+'">'+c.label+arrow+'</th>'
  }).join('');
}
function render(){
  var total=DATA.length;
  document.getElementById('countDisplay').textContent=total;
  var start=pageSize>0?(currentPage-1)*pageSize:0;
  var end=pageSize>0?Math.min(start+pageSize,total):total;
  var slice=DATA.slice(start,end);
  document.getElementById('tableBody').innerHTML=slice.map(function(row,i){
    var bg=i%2===0?'#fff':'#edf2f7';
    return '<tr style="background:'+bg+'">'+COLS.map(function(c){return '<td style="'+getAlign(c)+'">'+formatCell(row[c.key],c)+'</td>'}).join('')+'</tr>';
  }).join('');
  renderPagination(total,start,end);
}
function renderPagination(total,start,end){
  var bar=document.getElementById('paginationBar');
  if(pageSize===0||total<=pageSize){bar.style.display='none';return}
  bar.style.display='flex';
  var totalPages=Math.ceil(total/pageSize);
  var btns='';
  btns+='<button class="pg-btn nav-btn" onclick="goTo('+(currentPage-1)+')"'+(currentPage<=1?' disabled':'')+'>&laquo; Anterior</button>';
  var pages=[];
  if(totalPages<=7){for(var i=1;i<=totalPages;i++)pages.push(i)}
  else{pages.push(1);if(currentPage>3)pages.push('...');for(var i=Math.max(2,currentPage-1);i<=Math.min(totalPages-1,currentPage+1);i++)pages.push(i);if(currentPage<totalPages-2)pages.push('...');pages.push(totalPages)}
  for(var j=0;j<pages.length;j++){var p=pages[j];if(p==='...')btns+='<span class="pg-dots">...</span>';else btns+='<button class="pg-btn'+(p===currentPage?' active':'')+'" onclick="goTo('+p+')">'+p+'</button>'}
  btns+='<button class="pg-btn nav-btn" onclick="goTo('+(currentPage+1)+')"'+(currentPage>=totalPages?' disabled':'')+'> Próximo &raquo;</button>';
  document.getElementById('pageButtons').innerHTML=btns;
}
function goTo(p){var tp=pageSize>0?Math.ceil(DATA.length/pageSize):1;if(p>=1&&p<=tp){currentPage=p;render()}}
function changePageSize(){pageSize=parseInt(document.getElementById('pageSizeSelect').value);currentPage=1;render()}
function exportExcel(){
  var wb=XLSX.utils.table_to_book(document.querySelector('table'),{sheet:'Dados'});
  XLSX.writeFile(wb,filename+'_'+new Date().toISOString().slice(0,10)+'.xlsx');
}
renderHead();render();
<\/script>
</body>
</html>`

  const blob = new Blob([html], { type: 'text/html;charset=utf-8' })
  const url = URL.createObjectURL(blob)
  window.open(url, '_blank')
}

// Export Excel
function exportExcel() {
  const headers = props.columns.map(c => c.label)
  const rows = sortedData.value.map(row =>
    props.columns.map(col => {
      const val = row[col.key]
      if (val == null) return ''
      if (col.type === 'number' || col.type === 'currency') return Number(val) || 0
      return String(val).trim()
    })
  )

  const ws = XLSX.utils.aoa_to_sheet([headers, ...rows])

  // Auto-size columns
  const colWidths = props.columns.map((col, i) => {
    const maxLen = Math.max(
      col.label.length,
      ...rows.map(r => String(r[i]).length)
    )
    return { wch: Math.min(maxLen + 2, 40) }
  })
  ws['!cols'] = colWidths

  const wb = XLSX.utils.book_new()
  XLSX.utils.book_append_sheet(wb, ws, 'Dados')
  XLSX.writeFile(wb, `${props.exportFilename}_${new Date().toISOString().slice(0, 10)}.xlsx`)
}

// Filters
const hasActiveFilters = computed(() =>
  Object.values(columnFilters.value).some(v => v !== '')
)

function clearFilters() {
  for (const key of Object.keys(columnFilters.value)) {
    columnFilters.value[key] = ''
  }
}

function goToPage(p) {
  if (typeof p === 'number' && p >= 1 && p <= totalPages.value) {
    currentPage.value = p
  }
}
</script>

<template>
  <div>
    <!-- Toolbar -->
    <div class="flex items-center justify-between mb-3 gap-3">
      <div class="flex items-center gap-3 min-w-0">
        <p class="text-sm text-gray-500 whitespace-nowrap">
          <span class="font-semibold text-gray-700">{{ filteredData.length }}</span>
          <span v-if="filteredData.length !== data.length" class="text-gray-400"> de {{ data.length }}</span>
          registro(s)
        </p>
        <slot name="toolbar-left" />
      </div>

      <div class="flex items-center gap-2">
        <!-- Toggle filters -->
        <SecondaryButton type="button" @click="showFilters = !showFilters">
          <FunnelIcon class="h-4 w-4" />
          <span>Filtros</span>
          <span
            v-if="hasActiveFilters"
            class="ml-0.5 flex h-4 w-4 items-center justify-center rounded-full bg-primary text-[10px] font-bold text-white"
          >
            {{ Object.values(columnFilters).filter(v => v !== '').length }}
          </span>
        </SecondaryButton>

        <!-- Clear filters -->
        <SecondaryButton v-if="hasActiveFilters" type="button" @click="clearFilters">
          <XMarkIcon class="h-4 w-4" />
          <span>Limpar</span>
        </SecondaryButton>

        <!-- Fullscreen -->
        <SecondaryButton type="button" @click="openFullscreen" :disabled="!sortedData.length">
          <ArrowsPointingOutIcon class="h-4 w-4" />
          <span>Expandir</span>
        </SecondaryButton>

        <!-- Export Excel -->
        <SuccessButton type="button" @click="exportExcel">
          <TableCellsIcon class="h-4 w-4" />
          <span>Excel</span>
        </SuccessButton>
      </div>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto rounded-xl border border-gray-200 shadow-sm">
      <table class="min-w-full text-xs">
        <thead>
          <!-- Header row -->
          <tr style="background: linear-gradient(180deg, #0B56B3 0%, #093F87 100%);" class="text-white">
            <th v-if="selectable" class="px-3 py-3 w-10 text-center">
              <input
                type="checkbox"
                :checked="selectAllChecked"
                @change="selectAllChecked = $event.target.checked; toggleSelectAll()"
                class="h-3.5 w-3.5 rounded border-white/40 text-primary focus:ring-primary/50"
              />
            </th>
            <th
              v-for="col in columns"
              :key="col.key"
              class="px-3 py-3 font-semibold whitespace-nowrap select-none text-[11px] uppercase tracking-wider"
              :class="[
                cellAlign(col),
                col.sortable ? 'cursor-pointer hover:bg-white/10 transition-colors' : '',
              ]"
              @click="toggleSort(col.key)"
            >
              <span class="inline-flex items-center gap-1.5">
                {{ col.label }}
                <template v-if="sortKey === col.key">
                  <ArrowUpIcon v-if="sortOrder === 'asc'" class="h-3.5 w-3.5 text-white drop-shadow-[0_0_4px_rgba(255,255,255,0.5)]" />
                  <ArrowDownIcon v-else class="h-3.5 w-3.5 text-white drop-shadow-[0_0_4px_rgba(255,255,255,0.5)]" />
                </template>
                <ArrowUpIcon v-else-if="col.sortable" class="h-3 w-3 text-white/25" />
              </span>
            </th>
          </tr>

          <!-- Filter row -->
          <tr v-if="showFilters" class="bg-gray-50 border-b border-gray-200">
            <th v-if="selectable" class="px-1.5 py-1.5"></th>
            <th v-for="col in columns" :key="'f-' + col.key" class="px-1.5 py-1.5">
              <input
                v-if="col.filterable !== false"
                v-model="columnFilters[col.key]"
                type="text"
                :placeholder="col.label"
                class="w-full min-w-[60px] rounded border border-gray-200 bg-white px-2 py-1 text-xs text-gray-700 placeholder:text-gray-300 focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
              />
            </th>
          </tr>
        </thead>

        <tbody class="divide-y divide-gray-100/80">
          <tr
            v-for="(row, i) in paginatedData"
            :key="i"
            class="transition-colors duration-150"
            :class="[
              selectable && isRowSelected(i)
                ? 'bg-blue-50/80 hover:bg-blue-200/60 ring-inset ring-1 ring-blue-200/50'
                : i % 2 === 0
                  ? 'bg-white hover:bg-blue-200/70'
                  : 'bg-slate-100/70 hover:bg-blue-200/70',
              selectable ? 'cursor-pointer' : '',
            ]"
            @click="selectable && toggleRow(i)"
          >
            <td v-if="selectable" class="px-3 py-2.5 text-center" @click.stop>
              <input
                type="checkbox"
                :checked="isRowSelected(i)"
                @change="toggleRow(i)"
                class="h-3.5 w-3.5 rounded border-gray-300 text-primary focus:ring-primary/50"
              />
            </td>
            <td
              v-for="col in columns"
              :key="col.key"
              class="px-3 py-2.5 whitespace-nowrap text-gray-700"
              :class="cellAlign(col)"
            >
              <slot :name="'cell-' + col.key" :row="row" :value="row[col.key]" :col="col">
                {{ formatCell(row[col.key], col) }}
              </slot>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Empty state -->
      <div v-if="paginatedData.length === 0" class="py-12 text-center text-gray-400 text-sm">
        Nenhum registro encontrado.
      </div>
    </div>

    <!-- Pagination -->
    <div v-if="sortedData.length > 0" class="mt-5 flex items-center justify-between">
      <!-- Page size selector -->
      <div class="inline-flex items-center gap-1.5 text-xs text-gray-500">
        <span>Exibir</span>
        <select
          v-model.number="perPage"
          @change="currentPage = 1"
          class="rounded-lg border border-gray-200 bg-white pl-2.5 pr-7 py-1.5 text-xs font-semibold text-gray-700 focus:outline-none focus:ring-1 focus:ring-primary/30 focus:border-primary/40 cursor-pointer"
        >
          <option :value="10">10</option>
          <option :value="25">25</option>
          <option :value="50">50</option>
          <option :value="100">100</option>
        </select>
        <span>por página</span>
      </div>

      <!-- Page navigation -->
      <nav class="inline-flex items-center gap-1 rounded-lg border border-gray-200 bg-white p-1">
        <!-- Anterior -->
        <button
          type="button"
          :disabled="currentPage <= 1"
          @click="goToPage(currentPage - 1)"
          class="rounded-md px-2.5 py-1.5 text-xs font-medium transition-colors"
          :class="currentPage <= 1
            ? 'text-gray-300 cursor-not-allowed'
            : 'text-gray-600 hover:bg-gray-100 hover:text-gray-800'"
        >
          &laquo; Anterior
        </button>

        <!-- Page numbers -->
        <template v-for="p in visiblePages" :key="p">
          <span v-if="p === '...'" class="px-1.5 text-xs text-gray-300">...</span>
          <button
            v-else
            type="button"
            @click="goToPage(p)"
            class="min-w-[32px] rounded-md px-2 py-1.5 text-xs font-semibold transition-all"
            :class="p === currentPage
              ? 'text-white shadow-sm'
              : 'text-gray-600 hover:bg-gray-100 hover:text-gray-800'"
            :style="p === currentPage ? 'background: linear-gradient(180deg, #0B56B3 0%, #093F87 100%)' : ''"
          >
            {{ p }}
          </button>
        </template>

        <!-- Próximo -->
        <button
          type="button"
          :disabled="currentPage >= totalPages"
          @click="goToPage(currentPage + 1)"
          class="rounded-md px-2.5 py-1.5 text-xs font-medium transition-colors"
          :class="currentPage >= totalPages
            ? 'text-gray-300 cursor-not-allowed'
            : 'text-gray-600 hover:bg-gray-100 hover:text-gray-800'"
        >
          Próximo &raquo;
        </button>
      </nav>
    </div>
  </div>
</template>
