<template>
  <div class="p-6 space-y-6">
    <!-- Cabeçalho -->
    <div class="flex items-center justify-between">
      <!-- Logo + título -->
      <div class="flex items-center gap-3">
        <!-- ajuste o caminho do logo se necessário -->
        <img src="/images/logo.png" alt="Logo da clínica" class="h-36 w-auto" />
        <span class="hidden sm:inline text-sm text-gray-600">
          Bem-vindo, {{ medicoUsuario }}
        </span>
      </div>

      <!-- sair -->
      <div class="flex items-center gap-4">
        <button
          @click="logout"
          class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 transition"
        >
          Sair
        </button>
      </div>
    </div>


    <!-- Cards lado a lado -->
    <div class="flex flex-col md:flex-row gap-6">
    <!-- Pacientes Agendados (consultas + retornos) -->
    <div class="bg-white p-6 rounded shadow flex-1">
      <h2 class="text-lg font-semibold mb-4">📋 Pacientes Agendados</h2>

      <div v-if="agendados.length" class="max-h-[400px] overflow-y-auto pr-2">
        <div
          v-for="item in agendados"
          :key="`${item.tipo}-${item.pacienteId}-${item.data}-${item.hora || ''}`"
          class="border rounded p-4 mb-4 shadow-sm"
        >
          <div class="flex justify-between items-center mb-2">
            <div>
              <div class="flex items-center gap-2">
                <p class="text-lg font-bold">{{ item.nome }}</p>
                <span
                  class="text-xs px-2 py-0.5 rounded"
                  :class="item.tipo === 'retorno'
                            ? 'bg-purple-100 text-purple-700'
                            : 'bg-blue-100 text-blue-700'"
                >
                  {{ item.tipo === 'retorno' ? 'Retorno' : 'Consulta' }}
                </span>
              </div>

              <p class="text-sm text-gray-600 mt-1">
                <template v-if="item.idade != null">Idade: {{ item.idade }} | </template>
                <template v-if="item.estado_civil">Estado Civil: {{ item.estado_civil }} | </template>
                Data: {{ formatarData(item.data) }}
                <template v-if="item.hora"> às {{ item.hora }}</template>
              </p>

              <p class="text-sm text-gray-500" v-if="item.senha">
                Senha: <span class="font-semibold">{{ item.senha }}</span>
              </p>
            </div>

            <div class="flex flex-col gap-2">
              <button
                class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded"
                @click="chamarSenha(item)"
              >
                Chamar Senha
              </button>
              <button
                class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-sm"
                @click="iniciarAtendimento(item)"
              >
                Iniciar Atendimento
              </button>
            </div>
          </div>
        </div>
      </div>
      <p v-else class="text-gray-500 text-sm">Nenhum paciente agendado para hoje.</p>
    </div>


      <!-- Pacientes Atendidos -->
      <div class="bg-white p-6 rounded shadow flex-1">
        <h2 class="text-lg font-semibold mb-4">✅ Pacientes Atendidos</h2>
        <div v-if="pacientesAtendidos.length" class="max-h-[400px] overflow-y-auto pr-2">
          <ul class="space-y-2">
            <li
              v-for="paciente in pacientesAtendidos"
              :key="paciente.id"
              class="text-green-700 font-medium cursor-pointer hover:underline"
              @click="abrirHistorico(paciente.id)"
            >
              {{ paciente.nome }}
            </li>
          </ul>
        </div>
        <p v-else class="text-gray-500 text-sm">Nenhum paciente atendido ainda.</p>
      </div>
    </div>

    <!-- TOAST VISUAL -->
    <Toast ref="globalToast" />
  </div>
</template>

<script setup>
import { router } from '@inertiajs/vue3'
import { onMounted, onBeforeUnmount, ref } from 'vue'
import axios from 'axios'
import { useToast } from '@/Composables/useToast'
import Toast from '@/Components/Toast.vue'
import { toastRef } from '@/Composables/useGlobalToast'
import { usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

const { success, error } = useToast()
const globalToast = ref(null)

onMounted(() => {
  toastRef.value = globalToast.value
})

const page = usePage()
const medicoUsuario = computed(() =>
  page.props?.auth?.user?.usuario || page.props?.auth?.user?.name || 'Médico'
)

/* Props (fallbacks) */
const props = defineProps({
  pacientesAgendados: { type: Array, default: () => [] }, // fallback p/ agendados
  pacientesAtendidos: { type: Array, default: () => [] }  // fallback p/ atendidos
})

/* Estado local */
const agendados = ref([])                       // consultas + retornos
const atendidos = ref(props.pacientesAtendidos) // usa props; tenta API se existir

/* ===== Utils ===== */
function toYmd(str) {
  if (!str) return null
  if (/^\d{4}-\d{2}-\d{2}$/.test(str)) return str
  if (/^\d{2}\/\d{2}\/\d{4}$/.test(str)) {
    const [d,m,y] = str.split('/')
    return `${y}-${m}-${d}`
  }
  const d = new Date(str)
  if (!isNaN(d)) return d.toISOString().slice(0,10)
  return null
}

function todayYmd(tz = 'America/Sao_Paulo') {
  const parts = new Intl.DateTimeFormat('en-CA', {
    timeZone: tz, year: 'numeric', month: '2-digit', day: '2-digit'
  }).formatToParts(new Date())
  const y = parts.find(p => p.type === 'year')?.value
  const m = parts.find(p => p.type === 'month')?.value
  const d = parts.find(p => p.type === 'day')?.value
  return `${y}-${m}-${d}` // YYYY-MM-DD
}

function idadeFromYmd(ymd) {
  if (!ymd) return null
  const d = new Date(ymd)
  if (isNaN(d)) return null
  const h = new Date()
  let age = h.getFullYear() - d.getFullYear()
  const m = h.getMonth() - d.getMonth()
  if (m < 0 || (m === 0 && h.getDate() < d.getDate())) age--
  return age < 0 ? null : age
}

/* Normaliza um item (consulta/retorno) vindo do backend */
function normalizeItem(raw) {
  const data = toYmd(raw.data ?? raw.data_consulta ?? raw.data_retorno ?? null)
  const dn   = toYmd(raw.data_nascimento ?? null)

  return {
    pacienteId  : raw.paciente_id ?? raw.id,           // essencial p/ ações
    tipo        : raw.tipo ?? 'consulta',              // 'consulta' | 'retorno'
    nome        : raw.nome ?? raw.paciente ?? '—',
    data        : data || todayYmd(),                  // fallback: hoje
    hora        : raw.hora ?? null,
    estado_civil: raw.estado_civil ?? null,
    idade       : raw.idade ?? idadeFromYmd(dn),
    senha       : raw.senha ?? null,
  }
}

/* ===== Carregadores ===== */
async function carregarAgendados() {
  try {
    const { data } = await axios.get('/medico/agendados-hoje')
    agendados.value = (data?.itens ?? []).map(normalizeItem)
  } catch {
    // fallback: mantém comportamento antigo
    agendados.value = (props.pacientesAgendados ?? []).map(normalizeItem)
  }
}

async function carregarAtendidos() {
  try {
    // opcional: só se você criar a rota /medico/atendidos-hoje
    const { data } = await axios.get('/medico/atendidos-hoje')
    // espere data.itens com [{ id, nome, ... }]
    atendidos.value = data?.itens ?? props.pacientesAtendidos
  } catch {
    atendidos.value = props.pacientesAtendidos
  }
}

/* Primeira carga + recarga quando a aba voltar a ficar visível */
function onVisChange() {
  if (document.visibilityState === 'visible') {
    carregarAgendados()
    carregarAtendidos()
  }
}

onMounted(() => {
  carregarAgendados()
  carregarAtendidos()
  document.addEventListener('visibilitychange', onVisChange)

  // atualização instantânea (opcional):
  // na tela de atendimento, após finalizar:
  // window.localStorage.setItem('refresh-medico-dashboard', String(Date.now()))
  window.addEventListener('storage', (e) => {
    if (e.key === 'refresh-medico-dashboard') {
      carregarAgendados()
      carregarAtendidos()
    }
  })
})

onBeforeUnmount(() => {
  document.removeEventListener('visibilitychange', onVisChange)
})

/* ===== Ações ===== */
const chamarSenha = async (item) => {
  try {
    const { data } = await axios.post(`/medico/chamar-senha/${item.pacienteId}`)
    data?.success ? success(data.mensagem) : error(data?.mensagem || 'Falha ao chamar senha.')
  } catch (err) {
    error(err.response?.data?.erro || 'Erro ao chamar a senha.')
  }
}

const iniciarAtendimento = (item) => {
  router.get(`/medico/atendimento/${item.pacienteId}`)
}

function formatarData(data) {
  if (!data) return '—'
  const [y,m,d] = data.split('-')
  return `${d}/${m}/${y}`
}

const abrirHistorico = (id) => {
  router.get(`/medico/prontuario/${id}`)
}

function logout() {
  router.post('/logout')
}
</script>
