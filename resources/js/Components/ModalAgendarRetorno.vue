<script setup>
import { ref, watch } from 'vue'
import { router } from '@inertiajs/vue3' 

const props = defineProps({
  open:     { type: Boolean, default: false },
  paciente: { type: Object,  default: null },
})
const emit = defineEmits(['close','saved'])

const datasDisponiveis = ref([]) // ← inicia vazio

const form = ref({
  paciente_id: null,
  medico_id: null,
  data: '',           // yyyy-mm-dd
  motivo: '',
  observacoes: '',
})

const medicoNome = ref('')
const aviso = ref('')
const loading = ref(false)
const bloqueado = ref(false)

/* Utils */
function toYmd(s='') {
  if (!s) return ''
  if (/^\d{4}-\d{2}-\d{2}$/.test(s)) return s
  const m = s.match(/^(\d{2})\/(\d{2})\/(\d{4})$/)
  if (m) return `${m[3]}-${m[2]}-${m[1]}`
  const d = new Date(s)
  if (!isNaN(d)) return d.toISOString().slice(0,10)
  return ''
}

function extractDates(payload) {
  let lista = []
  if (Array.isArray(payload)) {
    lista = payload
  } else if (payload && typeof payload === 'object') {
    lista = payload.datas || payload.dias || payload.available || payload.available_dates || payload.data || []
  }
  const ymds = (Array.isArray(lista) ? lista : [lista]).map(item => {
    if (typeof item === 'string') return toYmd(item)
    if (item && typeof item === 'object') {
      return toYmd(item.data || item.date || item.dia || item.dia_util)
    }
    return ''
  }).filter(Boolean)
  return Array.from(new Set(ymds)).sort()
}

function todayYmd(tz = 'America/Fortaleza') {
  const parts = new Intl.DateTimeFormat('en-CA', {
    timeZone: tz, year: 'numeric', month: '2-digit', day: '2-digit',
  }).formatToParts(new Date())
  const y = parts.find(p => p.type === 'year')?.value
  const m = parts.find(p => p.type === 'month')?.value
  const d = parts.find(p => p.type === 'day')?.value
  return `${y}-${m}-${d}`
}

/* Carrega dados quando abrir */
watch(() => props.open, async (val) => {
  if (!val) return

  aviso.value = ''
  bloqueado.value = false
  datasDisponiveis.value = []
  form.value = {
    paciente_id: props.paciente?.id ?? null,
    medico_id: null,
    data: '',
    motivo: '',
    observacoes: '',
  }

  try {
    // 1) médico do paciente
    const r1 = await fetch(`/retornos/medico-do-paciente/${props.paciente.id}`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
    const j1 = await r1.json()
    if (!j1.ok) {
      aviso.value = j1.mensagem || 'Sem médico associado.'
      bloqueado.value = true
      return
    }
    form.value.medico_id = j1.medico.id
    medicoNome.value = j1.medico.nome

    // 2) dias disponíveis desse médico
    const r2 = await fetch(`/cadastro/agenda-medica/medico/${form.value.medico_id}/dias`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
    const j2 = await r2.json()

    const datas = extractDates(j2)
    datasDisponiveis.value = datas // ← agora sim!

    const hoje = todayYmd('America/Fortaleza')
    let proxima = datas.find(d => d > hoje) // 1ª futura

    if (!proxima && datas.includes(hoje)) {
      proxima = hoje // fallback para hoje (se quiser permitir)
    }

    if (!proxima) {
      aviso.value = 'Só é permitido agendar retorno quando tiver próxima data de consulta.'
      bloqueado.value = true
      return
    }

    form.value.data = proxima
  } catch (e) {
    aviso.value = 'Falha ao carregar agenda do médico.'
    bloqueado.value = true
  }
}, { immediate: false })

/* Salvar */
async function salvar() {
  if (bloqueado.value) return
  if (!form.value.paciente_id || !form.value.medico_id || !form.value.data) {
    aviso.value = 'Não há próxima data disponível para este médico.'
    return
  }

  loading.value = true
  router.post(route('retornos.store'), form.value, {
    preserveScroll: true,
    onSuccess: () => {
      loading.value = false
      emit('saved')
      emit('close')
    },
    onError: (errors) => {
      loading.value = false
      aviso.value = errors?.data ?? 'Falha ao agendar retorno.'
    }
  })
}
</script>


<template>
  <div v-if="open" class="fixed inset-0 bg-black/40 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl p-4 w-full max-w-md shadow-lg">
      <div class="flex items-center justify-between mb-3">
        <h2 class="text-lg font-semibold">Agendar Retorno</h2>
        <button class="text-gray-500 hover:text-gray-700" @click="$emit('close')">✕</button>
      </div>

      <div class="space-y-3">
        <div class="text-sm">
          <strong>Paciente:</strong> {{ paciente?.nome }}
        </div>

        <div>
          <label class="block text-sm font-medium">Médico</label>
          <input type="text" :value="medicoNome" class="form-input w-full bg-gray-100" disabled>
        </div>

        
        <div>
        <label class="block text-sm font-medium">Próxima data de consulta</label>

        <template v-if="datasDisponiveis?.length > 1">
            <select v-model="form.data" class="form-select w-full">
            <option v-for="d in datasDisponiveis" :key="d" :value="d">
                {{ d.split('-').reverse().join('/') }}
            </option>
            </select>
        </template>

        <input v-else type="date" :value="form.data" class="form-input w-full bg-gray-100" disabled>
        </div>


        <div v-if="aviso" class="text-red-600 text-sm">{{ aviso }}</div>

        <div>
          <label class="block text-sm font-medium">Motivo (opcional)</label>
          <input type="text" v-model="form.motivo" class="form-input w-full" />
        </div>

        <div>
          <label class="block text-sm font-medium">Observações</label>
          <textarea v-model="form.observacoes" rows="3" class="form-textarea w-full"></textarea>
        </div>
      </div>

      <div class="mt-4 flex justify-end gap-2">
        <button class="btn" @click="$emit('close')">Cancelar</button>
        <button class="btn btn-primary" :disabled="loading || bloqueado" @click="salvar">
          {{ loading ? 'Salvando...' : 'Agendar' }}
        </button>
      </div>
    </div>
  </div>
</template>

