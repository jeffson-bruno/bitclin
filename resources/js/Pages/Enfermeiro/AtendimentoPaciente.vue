<template>
  <div class="p-6 bg-gray-100 min-h-screen">
    <header class="flex items-center justify-between mb-4">
      <h1 class="text-2xl font-bold">Atendimento de Enfermagem</h1>
      <div class="text-sm text-gray-600">
        Paciente: <span class="font-semibold">{{ paciente.nome }}</span>
      </div>
    </header>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
      <!-- Sidebar -->
      <aside class="bg-white rounded shadow p-4 space-y-2">
        <button class="w-full bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700" @click="abrirTriagem">
          Triagem (Anamnese)
        </button>
        <button class="w-full bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700" @click="abrirReceita">
          Emitir Receita
        </button>
        <button class="w-full bg-gray-700 text-white px-4 py-2 rounded hover:bg-gray-800" @click="baixarProntuario()">
          Ver Prontuário
        </button>
        <button class="w-full bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700" @click="abrirExames">
          Ver Exames
        </button>
      </aside>

      <!-- Conteúdo -->
      <main class="md:col-span-3">
        <div class="bg-white rounded shadow p-5">
          <h2 class="text-lg font-semibold mb-2">Dados do Paciente</h2>
          <p><strong>Nome:</strong> {{ paciente.nome }}</p>
          <p v-if="paciente.cpf"><strong>CPF:</strong> {{ paciente.cpf }}</p>
          <p v-if="paciente.data_nascimento"><strong>Nasc.:</strong> {{ paciente.data_nascimento }}</p>
        </div>
      </main>
    </div>

    <!-- Modal Triagem -->
    <div v-if="modalTriagem" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
      <div class="bg-white rounded shadow max-w-lg w-full p-6">
        <h3 class="text-xl font-semibold mb-3">Triagem (Anamnese)</h3>
        <form @submit.prevent="salvarTriagem" class="space-y-3">
          <div>
            <label class="block text-sm font-medium">Pressão arterial</label>
            <input v-model="triagem.pressao_arterial" placeholder="ex: 120/80 mmHg" class="w-full border rounded p-2" />
          </div>
          <div>
            <label class="block text-sm font-medium">Queixa / Observações</label>
            <textarea v-model="triagem.conteudo" rows="5" class="w-full border rounded p-2" required></textarea>
          </div>

          <div class="text-right space-x-2">
            <button type="button" class="px-3 py-2 rounded bg-gray-200" @click="modalTriagem=false">Cancelar</button>
            <button type="submit" class="px-3 py-2 rounded bg-blue-600 text-white">Salvar</button>
          </div>
        </form>
      </div>
    </div>



    <!-- Modal Receita: você pode reutilizar sua ModalReceita existente via componente -->
     <!-- Modal Receita -->
<div v-if="modalReceita" class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center">
  <div class="bg-white w-full max-w-2xl rounded-lg p-6 shadow">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-lg font-semibold">Emitir Receita</h3>
      <button class="text-gray-500 hover:text-red-600" @click="modalReceita=false">&times;</button>
    </div>

    <div class="space-y-3 max-h-[50vh] overflow-y-auto pr-2">
      <div v-for="(m, i) in receita.medicamentos" :key="i" class="grid grid-cols-1 md:grid-cols-6 gap-2 items-end">
        <div class="md:col-span-3">
          <label class="text-sm">Medicamento*</label>
          <input v-model="m.nome" type="text" class="w-full border rounded p-2" placeholder="Ex.: Dipirona 500 mg" />
        </div>
        <div class="md:col-span-3">
          <label class="text-sm">Posologia (opcional)</label>
          <input v-model="m.posologia" type="text" class="w-full border rounded p-2" placeholder="Ex.: 1 cp 8/8h por 3 dias" />
        </div>
        <div class="md:col-span-6 text-right">
          <button v-if="receita.medicamentos.length > 1"
                  class="text-sm text-red-600 hover:underline"
                  @click="removeMedicamento(i)">
            Remover
          </button>
        </div>
      </div>

      <button class="text-sm text-blue-600 hover:underline" @click="addMedicamento">
        + Adicionar outro medicamento
      </button>
    </div>

    <div class="mt-6 flex justify-end gap-2">
      <button class="px-4 py-2 rounded bg-gray-200 hover:bg-gray-300" @click="modalReceita=false">Cancelar</button>
      <button class="px-4 py-2 rounded bg-green-600 text-white hover:bg-green-700" @click="emitirReceita">Gerar PDF</button>
    </div>
  </div>
</div>

</div>

<ModalExamesPaciente
  v-model="modalExamesAberta"
  :paciente="pacienteSelecionado"
  :can-upload="false"
  :can-delete="false"
/>


</template>

<script setup>
import { router } from '@inertiajs/vue3'
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'
import ModalExamesPaciente from '@/Components/ModalExamesPaciente.vue' 

const props = defineProps({
  paciente: { type: Object, required: true },
  especialidades: { type: Array, default: () => [] }
})

/* ===== Modais ===== */
const modalTriagem = ref(false)
const modalEncaminhar = ref(false)
const modalReceita = ref(false)
const modalExamesAberta   = ref(false)
const pacienteSelecionado = ref(props.paciente)

const abrirExames = () => {
  pacienteSelecionado.value = props.paciente
  modalExamesAberta.value   = true
}

/* ===== Paciente ===== */
const pacienteId = computed(() => props.paciente?.id ?? null)

/* ===== Triagem (já existia) ===== */
const triagem = ref({
  pressao_arterial   : '',
  queixa_principal   : '',
  historia_doenca    : '',
  historico_medico   : '',
  historico_familiar : '',
  habitos_vida       : '',
  revisao_sistemas   : '',
  observacoes        : '',
  conteudo           : '',
  medico_id          : null
})

/* ===== Encaminhamento (já existia) ===== */
const encaminhar = ref({
  para_especialidade_id: null,
  observacoes: '',
})

/* ===== Receita (novo modal local) ===== */
const receita = ref({
  medicamentos: [
    { nome: '', posologia: '' } // 'nome' é obrigatório; posologia opcional
  ]
})

function addMedicamento () {
  receita.value.medicamentos.push({ nome: '', posologia: '' })
}
function removeMedicamento (idx) {
  receita.value.medicamentos.splice(idx, 1)
  if (receita.value.medicamentos.length === 0) {
    receita.value.medicamentos.push({ nome: '', posologia: '' })
  }
}

/* ===== Aberturas ===== */
const abrirTriagem    = () => { modalTriagem.value = true }
const abrirEncaminhar = () => { modalEncaminhar.value = true }
const abrirReceita    = () => { modalReceita.value = true }   // << não navega mais

/* Opcional: se alguém ainda te mandar ?tab=receita na URL, abre o modal automático */
onMounted(() => {
  const qs = new URLSearchParams(window.location.search)
  if (qs.get('tab') === 'receita') modalReceita.value = true
})

/* ===== Ações ===== */
const salvarTriagem = async () => {
  try {
    if (!pacienteId.value) throw new Error('Paciente não encontrado.')
    const observacoes = triagem.value.observacoes?.trim()
      || triagem.value.conteudo?.trim() || null

    const payload = {
      paciente_id       : pacienteId.value,
      pressao_arterial  : triagem.value.pressao_arterial || null,
      queixa_principal  : triagem.value.queixa_principal || null,
      historia_doenca   : triagem.value.historia_doenca  || null,
      historico_medico  : triagem.value.historico_medico || null,
      historico_familiar: triagem.value.historico_familiar || null,
      habitos_vida      : triagem.value.habitos_vida     || null,
      revisao_sistemas  : triagem.value.revisao_sistemas || null,
      observacoes,
      medico_id         : triagem.value.medico_id        || null,
    }

    const { data } = await axios.post('/enfermeiro/anamnese', payload, {
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })

    if (data?.ok) {
      modalTriagem.value = false
      window.localStorage.setItem('refresh-medico-dashboard', String(Date.now()))
      alert(data.message || 'Triagem registrada com sucesso.')
    } else {
      alert(data?.message || 'Erro ao salvar a triagem.')
    }
  } catch (err) {
    console.error(err); alert('Erro ao salvar a triagem.')
  }
}

/* ===== Emitir Receita (PDF em nova aba, sem sair da página) ===== */
const emitirReceita = async () => {
  try {
    if (!pacienteId.value) throw new Error('Paciente não encontrado.')

    // validação simples no front
    const itens = receita.value.medicamentos
      .map(m => ({ nome: (m.nome || '').trim(), posologia: (m.posologia || '').trim() }))
      .filter(m => m.nome.length > 0)

    if (itens.length === 0) {
      alert('Adicione ao menos um medicamento.')
      return
    }

    const payload = { paciente_id: pacienteId.value, medicamentos: itens }

    const res = await axios.post('/enfermeiro/gerar-receita', payload, {
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      responseType: 'blob'
    })

    const blob = new Blob([res.data], { type: 'application/pdf' })
    const url = URL.createObjectURL(blob)
    window.open(url, '_blank') // abre o PDF numa nova aba
    URL.revokeObjectURL(url)

    modalReceita.value = false
  } catch (err) {
    console.error(err)
    // 403 = permissão; 422 = validação; 500 = servidor
    alert(err?.response?.data?.message || 'Erro ao gerar a receita.')
  }
}

/* ===== Links existentes (histórico/atendimento médico) — se quiser manter ===== */
const baixarProntuario = async (pac = null) => {
  // aceita: nenhum param (usa pacienteId do props), um número (id) ou um objeto (paciente/item)
  const id = pac
    ? (typeof pac === 'object'
        ? (pac.id ?? pac.pacienteId ?? pac.paciente_id ?? null)
        : pac)
    : pacienteId.value

  if (!id) {
    alert('ID do paciente não encontrado.')
    return
  }

  try {
    const res = await axios.get(`/medico/prontuario/${id}/pdf`, {
      responseType: 'blob',
      validateStatus: s => (s >= 200 && s < 300) || s === 404 || s === 403
    })

    if (res.status === 404) {
      alert('Paciente sem prontuário no momento')
      return
    }
    if (res.status === 403) {
      alert('Sem permissão para baixar o prontuário.')
      return
    }

    const blob = new Blob([res.data], { type: 'application/pdf' })
    const url = window.URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = `historico_clinico_${id}.pdf`
    document.body.appendChild(a)
    a.click()
    a.remove()
    window.URL.revokeObjectURL(url)
  } catch (e) {
    console.error(e)
    alert('Falha ao baixar o prontuário.')
  }
}

// se quiser ir para a tela do médico, mantenha (não é mais necessário p/ receita)
const irAtendimentoMedico = () => { router.get(`/medico/atendimento/${pacienteId.value}`) }
</script>
