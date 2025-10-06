<template>
  <div class="p-6 space-y-6">
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-3">
        <img src="/images/logo.png" alt="Logo da clínica" class="h-36 w-auto" />
        <span class="hidden sm:inline text-sm text-gray-600">
          Bem-vindo(a), {{ usuarioNome }}
        </span>
      </div>
      <div>
        <button @click="logout" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">Sair</button>
      </div>
    </div>

    <div class="flex flex-col md:flex-row gap-6">
      <!-- Agendados -->
      <div class="bg-white p-6 rounded shadow flex-1">
        <h2 class="text-lg font-semibold mb-4">📋 Agendamentos de Hoje</h2>

        <div v-if="agendados.length" class="max-h-[400px] overflow-y-auto pr-2">
          <div v-for="item in agendados" :key="`${item.pacienteId}-${item.hora||''}`" class="border rounded p-4 mb-4 shadow-sm">
            <div class="flex justify-between items-center mb-2">
              <div>
                <div class="flex items-center gap-2">
                  <p class="text-lg font-bold">{{ item.nome }}</p>
                  <span class="text-xs px-2 py-0.5 rounded bg-blue-100 text-blue-700">{{ item.tipo || 'Consulta' }}</span>
                </div>
                <p class="text-sm text-gray-600 mt-1">
                  Data: {{ formatarData(item.data) }} <template v-if="item.hora">às {{ item.hora }}</template>
                </p>
                <p class="text-xs text-gray-500" v-if="item.medicoNome">
                    Médico: <span class="font-medium">{{ item.medicoNome }}</span>
                </p>
              </div>
              <div class="flex flex-col gap-2">
                <button class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded" @click="chamarSenha(item)">Chamar</button>
                <button class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-sm" @click="iniciarAtendimento(item)">Iniciar</button>
              </div>
            </div>
          </div>
        </div>
        <p v-else class="text-gray-500 text-sm">Nenhum agendamento hoje.</p>
      </div>

      <!-- Atendidos -->
      <div class="bg-white p-6 rounded shadow flex-1">
        <h2 class="text-lg font-semibold mb-4">✅ Atendidos</h2>
        <div v-if="atendidos.length" class="max-h-[400px] overflow-y-auto pr-2">
          <ul class="space-y-2">
            <li v-for="p in atendidos" :key="p.id" class="text-green-700 font-medium cursor-pointer hover:underline" @click="abrirHistorico(p.id)">
              {{ p.nome }}
            </li>
          </ul>
        </div>
        <p v-else class="text-gray-500 text-sm">Nenhum paciente atendido ainda.</p>
      </div>
    </div>

    <Toast ref="globalToast" />
  </div>
</template>

<script setup>
import { router, usePage } from '@inertiajs/vue3'
import { ref, onMounted, onBeforeUnmount, computed } from 'vue'
import axios from 'axios'
import Toast from '@/Components/Toast.vue'

const page = usePage()
const usuarioNome = computed(() => page.props?.auth?.user?.usuario || page.props?.auth?.user?.name || 'Enfermeiro(a)')

const agendados = ref([])
const atendidos = ref([])

function toYmd(str){ if(!str) return null; if(/^\d{4}-\d{2}-\d{2}$/.test(str)) return str; const d=new Date(str); return isNaN(d)?null:d.toISOString().slice(0,10) }
function formatarData(ymd){ if(!ymd) return '—'; const [y,m,d]=ymd.split('-'); return `${d}/${m}/${y}` }

async function carregarAgendados(){
  try {
    const { data } = await axios.get('/enfermeiro/agendados-hoje', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
    console.log('agendados-hoje.debug =>', data?.debug)
    const rows = data?.itens ?? []
    agendados.value = rows.map(n => ({
      pacienteId : n.paciente_id,
      nome       : n.paciente_nome ?? n.nome ?? '—',   // <<< usa alias novo
      medicoNome : n.medico_nome ?? null,              // <<< nome do médico
      data       : n.data ?? null,
      hora       : n.hora ?? null,
      tipo       : n.tipo ?? 'consulta',
      senha      : n.senha ?? null,
    }))
  } catch (e) {
    console.error('Falha ao carregar agendados:', e)
    agendados.value = []
  }
}


async function carregarAtendidos(){
  try {
    const { data } = await axios.get('/enfermeiro/atendidos-hoje', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
    console.log('atendidos-hoje.debug =>', data?.debug)
    atendidos.value = data?.itens ?? []
  } catch (e) {
    console.error('Falha ao carregar atendidos:', e)
    atendidos.value = []
  }
}



function onVisChange(){ if(document.visibilityState==='visible'){carregarAgendados();carregarAtendidos()} }
onMounted(()=>{carregarAgendados();carregarAtendidos();document.addEventListener('visibilitychange',onVisChange)})
onBeforeUnmount(()=>document.removeEventListener('visibilitychange',onVisChange))

const chamarSenha = async (item)=>{ await axios.post(`/enfermeiro/chamar-senha/${item.pacienteId}`) }
const iniciarAtendimento = (item)=> router.get(`/enfermeiro/atendimento/${item.pacienteId}`)
const abrirHistorico = (id)=> router.get(`/medico/prontuario/${id}`) // reaproveitando rota existente
const logout = ()=> router.post('/logout')
</script>
