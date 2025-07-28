<template>
  <div class="fixed inset-0 bg-black/40 z-50 flex items-center justify-center px-4">
    <div class="bg-white rounded-xl shadow-lg w-full max-w-3xl max-h-[90vh] overflow-y-auto relative p-6">
      <!-- Botão Fechar -->
      <button
        @click="$emit('close')"
        class="absolute top-3 right-3 text-gray-500 hover:text-red-500"
      >
        ✕
      </button>

      <!-- Título -->
      <h2 class="text-2xl font-bold mb-4 text-center">Receita Médica</h2>

      <!-- Pré-visualização da Receita com marca d'água -->
      <div
        ref="previewRef"
        class="relative bg-white shadow-lg rounded-lg p-8 mt-6 text-gray-800"
        style="min-height: 600px; position: relative; overflow: hidden;"
      >
        <!-- Marca d'água -->
        <div class="absolute inset-0 flex items-center justify-center opacity-10 pointer-events-none select-none z-0">
          <img src="/images/logo.png" alt="Marca d'água" class="w-1/2" />
        </div>

        <!-- Conteúdo da Receita -->
        <div class="relative z-10">
          <!-- Informações do Paciente e Médico -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
            <div>
              <label class="text-sm text-gray-600">Nome do Paciente</label>
              <div class="font-semibold">{{ paciente.nome }}</div>
            </div>
            <div>
              <label class="text-sm text-gray-600">Idade</label>
              <div class="font-semibold">{{ calcularIdade(paciente.data_nascimento) }} anos</div>
            </div>
            <div>
              <label class="text-sm text-gray-600">Nome do Médico</label>
              <div class="font-semibold">{{ medico.nome }}</div>
            </div>
            <div>
              <label class="text-sm text-gray-600">CRM</label>
              <input v-model="crm" type="text" class="input-form" placeholder="Digite o número do conselho" />
            </div>
          </div>

          <!-- Seção de Medicamentos -->
          <div class="mt-6 border-t pt-4">
            <h3 class="font-semibold text-lg mb-4">Prescrição</h3>

            <div v-for="(med, index) in medicamentos" :key="index" class="mb-6 border p-4 rounded-lg bg-gray-50">
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                <div>
                  <label class="text-sm text-gray-600">Nome do Remédio</label>
                  <input v-model="med.nome" type="text" class="input-form" />
                </div>
                <div>
                  <label class="text-sm text-gray-600">Quantidade (Caixas)</label>
                  <input v-model="med.quantidade" type="number" class="input-form" />
                </div>
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label class="text-sm text-gray-600">Tipo</label>
                  <select v-model="med.tipo" class="input-form">
                    <option value="" disabled>Selecione</option>
                    <option v-for="tipo in tipos" :key="tipo" :value="tipo">{{ tipo }}</option>
                  </select>
                </div>

                <div v-if="med.tipo !== 'Injetável'">
                  <label class="text-sm text-gray-600">Intervalo (horas)</label>
                  <input v-model="med.intervaloHoras" type="text" class="input-form" placeholder="Ex: a cada 8 horas" />
                </div>
              </div>

              <!-- Vezes ao dia -->
              <div class="mt-4">
                <label class="text-sm text-gray-600">Tomar quantas vezes ao dia?</label>
                <div class="flex gap-4 mt-1">
                  <label v-for="n in 4" :key="n" class="flex items-center gap-1 text-sm">
                    <input type="checkbox" :value="n" v-model="med.vezesPorDia" />
                    {{ n }}x
                  </label>
                </div>
              </div>

              <!-- Campos adicionais conforme o tipo -->
              <div class="mt-4">
                <template v-if="med.tipo === 'Gotas'">
                  <label class="text-sm text-gray-600">Quantas gotas?</label>
                  <input v-model="med.detalhes.gotas" type="number" class="input-form" />
                </template>
                <template v-else-if="med.tipo === 'Líquido'">
                  <label class="text-sm text-gray-600">Quantos ml?</label>
                  <input v-model="med.detalhes.ml" type="number" class="input-form" />
                </template>
                <template v-else-if="med.tipo === 'Comprimido'">
                  <label class="text-sm text-gray-600">Quantos comprimidos?</label>
                  <input v-model="med.detalhes.comprimidos" type="number" class="input-form" />
                </template>
                <template v-else-if="med.tipo === 'Injetável'">
                  <label class="text-sm text-gray-600">Quantas ampolas?</label>
                  <input v-model="med.detalhes.ampolas" type="number" class="input-form" />
                </template>
              </div>
            </div>

            <button @click="adicionarMedicamento" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
              + Adicionar Medicamento
            </button>
          </div>

          <button
            @click="enviarReceita"
            class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded"
          >
            Gerar PDF e Salvar
          </button>

        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const emit = defineEmits(['close'])
const props = defineProps({
  paciente: Object,
  medico: Object,
})

const crm = ref('')

const medicamentos = ref([
  {
    nome: '',
    quantidade: '',
    tipo: '',
    intervaloHoras: '',
    detalhes: {
      gotas: '',
      ml: '',
      comprimidos: '',
      ampolas: ''
    },
    vezesPorDia: []
  }
])

const adicionarMedicamento = () => {
  medicamentos.value.push({
    nome: '',
    quantidade: '',
    tipo: '',
    intervaloHoras: '',
    detalhes: {
      gotas: '',
      ml: '',
      comprimidos: '',
      ampolas: ''
    },
    vezesPorDia: []
  })
}

const tipos = ['Gotas', 'Líquido', 'Comprimido', 'Injetável']

const calcularIdade = (dataNasc) => {
  if (!dataNasc) return '—'
  const nasc = new Date(dataNasc)
  if (isNaN(nasc)) return '—'
  const hoje = new Date()
  let idade = hoje.getFullYear() - nasc.getFullYear()
  const m = hoje.getMonth() - nasc.getMonth()
  if (m < 0 || (m === 0 && hoje.getDate() < nasc.getDate())) idade--
  return idade
}

const enviarReceita = async () => {
  try {
    const response = await axios.post('/medico/gerar-receita', {
      paciente_id: props.paciente.id,
      crm: crm.value,
      medicamentos: medicamentos.value
    })

    if (response.data.success) {
      alert('Receita gerada e salva com sucesso!')
      window.open(response.data.url, '_blank');
      emit('close')
    } else {
      alert('Erro ao salvar a receita')
    }
  } catch (err) {
    console.error(err)
    alert('Erro ao enviar dados da receita.')
  }
}



</script>

<style scoped>
.input-form {
  @apply w-full rounded border border-gray-300 px-3 py-2 text-sm;
}
</style>

