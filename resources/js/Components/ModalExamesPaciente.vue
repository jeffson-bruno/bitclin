
<script setup>
import { ref, watch, computed } from 'vue'
import axios from 'axios'

const props = defineProps({
  modelValue: { type: Boolean, default: false },
  paciente: { type: Object, default: null },
  canUpload: { type: Boolean, default: false },
  canDelete: { type: Boolean, default: false },
})
const emit = defineEmits(['update:modelValue', 'updated'])

const pacienteId = computed(() => props.paciente?.id ?? null)

const arquivos = ref([])
const arquivosNovos = ref(null)
const carregando = ref(false)
const salvando = ref(false)

watch(() => props.modelValue, (aberta) => {
  if (aberta) carregarArquivos()
  else { arquivos.value = []; arquivosNovos.value = null }
})

const close = () => emit('update:modelValue', false)

async function carregarArquivos () {
  if (!pacienteId.value) return
  carregando.value = true
  try {
    const res = await axios.get(`/arquivos-exames/paciente/${pacienteId.value}`, {
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      validateStatus: s => s >= 200 && s < 500
    })
    if (res.status === 401) { alert('Sessão expirada. Faça login novamente.'); return }
    if (res.status === 403) { alert('Sem permissão para ver os exames.'); return }
    if (res.status === 404) { arquivos.value = []; return }

    arquivos.value = res.data?.ok ? (res.data.arquivos || []) : []
  } finally {
    carregando.value = false
  }
}

function onArquivosSelecionados (e) {
  arquivosNovos.value = e.target.files
}

async function enviarArquivos () {
  if (!props.canUpload) return
  if (!pacienteId.value) return
  if (!arquivosNovos.value || arquivosNovos.value.length === 0) {
    alert('Selecione ao menos um arquivo.')
    return
  }
  salvando.value = true
  try {
    const fd = new FormData()
    fd.append('paciente_id', pacienteId.value)
    Array.from(arquivosNovos.value).forEach(f => fd.append('arquivos[]', f))

    const res = await axios.post('/arquivos-exames', fd, {
      headers: {
        'Content-Type': 'multipart/form-data',
        'X-Requested-With': 'XMLHttpRequest',
      },
      validateStatus: s => s >= 200 && s < 500
    })

    if (res.status === 401) { alert('Sessão expirada. Faça login novamente.'); return }
    if (res.status === 403) { alert('Sem permissão para enviar exames.'); return }

    const data = res.data
    if (data?.ok) {
      arquivosNovos.value = null
      await carregarArquivos()
      emit('updated')
      alert('Arquivos enviados com sucesso.')
    } else {
      alert('Falha ao enviar.')
    }
  } catch (e) {
    console.error(e)
    alert('Erro no upload.')
  } finally {
    salvando.value = false
  }
}

async function removerArquivo (arq) {
  if (!props.canDelete) return
  if (!confirm(`Remover "${arq.nome}"?`)) return
  try {
    const res = await axios.delete(`/arquivos-exames/${arq.id}`, {
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      validateStatus: s => s >= 200 && s < 500
    })
    if (res.status === 401) { alert('Sessão expirada. Faça login novamente.'); return }
    if (res.status === 403) { alert('Sem permissão para excluir.'); return }

    const data = res.data
    if (data?.ok) {
      arquivos.value = arquivos.value.filter(x => x.id !== arq.id)
      emit('updated')
      alert('Arquivo removido.')
    } else {
      alert(data?.message || 'Falha ao remover.')
    }
  } catch (e) {
    console.error(e)
    alert('Erro ao remover o arquivo.')
  }
}

function formatBytes (bytes) {
  if (!bytes && bytes !== 0) return '—'
  const sizes = ['Bytes','KB','MB','GB','TB']
  if (bytes === 0) return '0 Byte'
  const i = Math.floor(Math.log(bytes) / Math.log(1024))
  return `${(bytes / Math.pow(1024, i)).toFixed(1)} ${sizes[i]}`
}

const inlineHref   = (arq) => arq.inline_url   || `/arquivos-exames/${arq.id}/ver`
const downloadHref = (arq) => arq.download_url || `/arquivos-exames/${arq.id}/download`
</script>


<template>
  <div v-if="modelValue" class="fixed inset-0 z-50 bg-black/40 flex items-center justify-center">
    <div class="bg-white w-full max-w-3xl rounded-lg p-6 shadow relative">
      <button class="absolute top-2 right-3 text-gray-500 hover:text-red-600 text-2xl leading-none"
              @click="close" aria-label="Fechar">&times;</button>

      <h3 class="text-lg font-semibold mb-2">
        Exames de: <span class="text-gray-700">{{ paciente?.nome || '—' }}</span>
      </h3>

      <!-- Upload (somente se permitido) -->
      <div v-if="canUpload" class="border rounded p-4 mb-4">
        <label class="block text-sm font-medium mb-2">Adicionar arquivos (PDF/JPG/PNG)</label>
        <input type="file" multiple accept="application/pdf,image/png,image/jpeg" @change="onArquivosSelecionados" />
        <div v-if="arquivosNovos?.length" class="text-xs text-gray-600 mt-2">
          {{ arquivosNovos.length }} arquivo(s) selecionado(s)
        </div>

        <div class="mt-3 text-right">
          <button class="px-3 py-2 rounded bg-indigo-600 text-white hover:bg-indigo-700 disabled:opacity-50"
                  @click="enviarArquivos"
                  :disabled="salvando || !arquivosNovos?.length || !pacienteId">
            <span v-if="salvando">Enviando...</span>
            <span v-else>Enviar</span>
          </button>
        </div>
      </div>

      <!-- Lista -->
      <div>
        <h4 class="font-medium mb-2">Arquivos anexados</h4>
        <div v-if="carregando" class="text-sm text-gray-500">Carregando...</div>
        <div v-else-if="arquivos.length === 0" class="text-sm text-gray-500">Nenhum exame anexado para este paciente.</div>

        <div v-else class="space-y-2 max-h-[50vh] overflow-y-auto pr-2">
          <div v-for="arq in arquivos" :key="arq.id" class="flex items-center justify-between border rounded p-3">
            <div class="flex items-center gap-3 overflow-hidden">
              <template v-if="(arq.mime || '').startsWith('image/')">
                <img :src="inlineHref(arq)" class="h-12 w-12 object-cover rounded border" alt="">
              </template>
              <template v-else-if="arq.mime === 'application/pdf'">
                <span class="inline-grid place-items-center h-12 w-12 border rounded">PDF</span>
              </template>
              <template v-else>
                <span class="inline-grid place-items-center h-12 w-12 border rounded">ARQ</span>
              </template>

              <div class="truncate">
                <div class="font-medium truncate">{{ arq.nome }}</div>
                <div class="text-xs text-gray-500">
                  {{ arq.mime }} • {{ formatBytes(arq.tamanho) }}
                  <template v-if="arq.uploaded_by"> • {{ arq.uploaded_by }}</template>
                  <template v-if="arq.created_at"> • {{ arq.created_at }}</template>
                </div>
              </div>
            </div>

            <div class="flex items-center gap-2">
              <a :href="inlineHref(arq)" target="_blank"
                 class="px-3 py-1 rounded bg-blue-600 text-white hover:bg-blue-700">Ver</a>
              <a :href="downloadHref(arq)"
                 class="px-3 py-1 rounded bg-gray-700 text-white hover:bg-gray-800">Baixar</a>

              <button v-if="canDelete"
                      class="p-2 text-red-600 hover:bg-red-50 rounded"
                      @click="removerArquivo(arq)" title="Excluir">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                     fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round"
                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7h6m-1-3H10a1 1 0 00-1 1v2h8V5a1 1 0 00-1-1z"/>
                </svg>
              </button>
            </div>
          </div>
        </div>
      </div>

      <div class="mt-4 text-right">
        <button class="px-3 py-2 rounded bg-gray-200 hover:bg-gray-300" @click="close">Fechar</button>
      </div>
    </div>
  </div>
</template>

