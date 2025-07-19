<template>
  <div v-if="show" class="fixed inset-0 bg-black bg-opacity-40 z-50 flex items-center justify-center">
    <div class="bg-white rounded-xl p-6 w-full max-w-lg shadow relative">
      <h2 class="text-xl font-bold mb-4 text-center">Reagendar Paciente</h2>

      <div class="space-y-4">
        <div>
          <label class="font-medium">Nome do Paciente</label>
          <input type="text" :value="paciente.nome" disabled class="w-full border rounded p-2 bg-gray-100" />
        </div>

        <div>
          <label class="font-medium">Procedimento</label>
          <select v-model="procedimento" class="w-full border rounded p-2">
            <option value="consulta">Consulta</option>
            <option value="exame">Exame</option>
          </select>
        </div>

        <div v-if="procedimento === 'consulta'">
          <label class="font-medium">Médico</label>
          <select v-model="medicoId" @change="buscarDatasDisponiveis" class="w-full border rounded p-2">
            <option v-for="medico in medicos" :key="medico.id" :value="medico.id">
              {{ medico.nome }}
            </option>
          </select>

          <label class="font-medium mt-2 block">Data</label>
          <select v-model="novaData" class="w-full border rounded p-2">
            <option v-for="data in datasDisponiveis" :key="data" :value="data">
              {{ formatarData(data) }}
            </option>
          </select>
        </div>

        <div v-if="procedimento === 'exame'">
          <label class="font-medium">Exame</label>
          <select v-model="exameId" @change="atualizarValor" class="w-full border rounded p-2">
            <option v-for="exame in exames" :key="exame.id" :value="exame.id">
              {{ exame.nome }}
            </option>
          </select>
        </div>

        <div>
          <label class="font-medium">Novo Valor</label>
          <input type="number" v-model="valor" class="w-full border rounded p-2" />
        </div>
      </div>

      <div class="flex justify-end mt-6 space-x-2">
        <button @click="$emit('close')" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Cancelar</button>
        <button @click="confirmarReagendamento" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Confirmar</button>
      </div>
    </div>
  </div>
  
</template>

<script setup>
import { defineProps, defineEmits, ref, watch } from 'vue'
import axios from 'axios'

const props = defineProps({
  show: Boolean,
  paciente: Object
})

const emit = defineEmits(['close', 'reagendado'])

const procedimento = ref('consulta')
const medicos = ref([])
const exames = ref([])
const medicoId = ref(null)
const exameId = ref(null)
const datasDisponiveis = ref([])
const novaData = ref('')
const valor = ref('')

// Buscar médicos e exames ao abrir modal
watch(() => props.show, async (aberto) => {
  if (aberto) {
    procedimento.value = props.paciente.procedimento.toLowerCase()

    try {
      const [respMedicos, respExames] = await Promise.all([
        axios.get('/api/medicos'),
        axios.get('/api/exames')
      ])
      medicos.value = respMedicos.data
      exames.value = respExames.data
    } catch (err) {
      console.error('Erro ao carregar médicos ou exames', err)
    }
  }
})

const buscarDatasDisponiveis = async () => {
  if (!medicoId.value) return

  try {
    const { data } = await axios.get(`/api/agenda-medica/${medicoId.value}/datas`)
    datasDisponiveis.value = data
  } catch (err) {
    console.error('Erro ao buscar datas disponíveis', err)
  }
}

const atualizarValor = () => {
  const exameSelecionado = exames.value.find(e => e.id === exameId.value)
  if (exameSelecionado) {
    valor.value = exameSelecionado.valor
  }
}

const formatarData = (data) => {
  return new Date(data).toLocaleDateString('pt-BR')
}

const confirmarReagendamento = async () => {
  try {
    const payload = {
      procedimento: procedimento.value,
      medico_id: procedimento.value === 'consulta' ? medicoId.value : null,
      exame_id: procedimento.value === 'exame' ? exameId.value : null,
      data: novaData.value,
      valor: valor.value
    }

    await axios.post(`/api/pacientes/${props.paciente.id}/reagendar`, payload)

    emit('reagendado') // para recarregar a listagem
    emit('close')
  } catch (err) {
    console.error('Erro ao reagendar paciente', err)
  }
}
</script>
