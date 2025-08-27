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

      <!-- Pré-visualização -->
      <div
        ref="previewRef"
        class="relative bg-white shadow-lg rounded-lg p-8 mt-6 text-gray-800"
        style="min-height: 600px; position: relative; overflow: hidden;"
      >
        <!-- Marca d'água -->
        <div class="absolute inset-0 flex items-center justify-center opacity-10 pointer-events-none select-none z-0">
          <img src="/images/logo.png" alt="Marca d'água" class="w-1/2" />
        </div>

        <!-- Conteúdo -->
        <div class="relative z-10">
          <!-- Paciente / Médico -->
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
              <div class="font-semibold">{{ medico.name || medico.nome }}</div>
            </div>

            <div>
              <label class="text-sm text-gray-600">Registro</label>
              <div class="font-semibold">
                {{ registroExibicao }}
              </div> 
            </div>
          </div>

          <!-- Prescrição -->
          <div class="mt-6 border-t pt-4">
            <h3 class="font-semibold text-lg mb-4">Prescrição</h3>

            <div v-for="(med, index) in medicamentos" :key="index" class="mb-6 border p-4 rounded-lg bg-gray-50">
              <!-- Linha 1: Nome e Tipo -->
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                <div>
                  <label class="text-sm text-gray-600">Nome do Remédio</label>
                  <input v-model="med.nome" type="text" class="input-form" />
                </div>
                <div>
                  <label class="text-sm text-gray-600">Forma Farmacêutica (Tipo)</label>
                  <select v-model="med.tipo" class="input-form">
                    <option value="" disabled>Selecione</option>
                    <option v-for="tipo in tipos" :key="tipo" :value="tipo">{{ tipo }}</option>
                  </select>
                </div>
              </div>

              <!-- Se NÃO for injetável -->
              <div v-if="med.tipo !== 'Injetável'">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
                  <div>
                    <label class="text-sm text-gray-600">Quantidade (Caixas)</label>
                    <input v-model="med.quantidade" type="number" class="input-form" />
                  </div>
                  <div>
                    <label class="text-sm text-gray-600">(mg / ml)</label>
                    <input v-model="med.miligramas" type="text" class="input-form" placeholder="Ex: 500mg" />
                  </div>
                  <div>
                    <label class="text-sm text-gray-600">Intervalo (horas)</label>
                    <input v-model="med.intervaloHoras" type="text" class="input-form" placeholder="Ex: 8 / 8 horas" />
                  </div>
                </div>

                <!-- Dosagem -->
                <div>
                  <label class="text-sm text-gray-600">Dosagem</label>
                  <div v-if="med.tipo === 'Gotas'">
                    <input v-model="med.detalhes.gotas" type="text" class="input-form" placeholder="Ex: 15 gotas" />
                  </div>
                  <div v-else-if="med.tipo === 'Líquido'">
                    <input v-model="med.detalhes.ml" type="text" class="input-form" placeholder="Ex: 10 ml" />
                  </div>
                  <div v-else-if="med.tipo === 'Comprimido'">
                    <input v-model="med.detalhes.comprimidos" type="text" class="input-form" placeholder="Ex: 1 comp" />
                  </div>
                </div>
              </div>

              <!-- Se for Injetável -->
              <div v-else>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                  <div>
                    <label class="text-sm text-gray-600">Quantidade (Ampolas ou Frascos)</label>
                    <input v-model="med.detalhes.ampolas" type="text" class="input-form" />
                  </div>
                  <div>
                    <label class="text-sm text-gray-600">Dosagem</label>
                    <input v-model="med.dosagem" type="text" class="input-form" placeholder="Ex: 1mg/ml" />
                  </div>
                </div>
                <div>
                  <label class="text-sm text-gray-600">Instruções de Aplicação</label>
                  <textarea v-model="med.instrucao" class="input-form" placeholder="Descreva como deve ser administrado..."></textarea>
                </div>
              </div>
            </div>

            <!-- Botão Adicionar Medicamento -->
            <div class="flex justify-center mt-4">
              <button @click="adicionarMedicamento" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                + Adicionar Medicamento
              </button>
            </div>
          </div>

          <!-- Botão Gerar PDF -->
          <div class="flex justify-end mt-6">
            <button
              @click.prevent="enviarReceita"
              class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded"
            >
              Gerar PDF e Salvar
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import axios from 'axios'

const emit = defineEmits(['close'])
const props = defineProps({
  paciente: { type: Object, required: true },
  medico:   { type: Object, required: true },
})

// NÃO usamos mais CRM digitado
// const crm = ref('')

const tipos = ['Gotas', 'Líquido', 'Comprimido', 'Injetável']

const medicamentos = ref([
  {
    nome: '',
    quantidade: '',
    tipo: '',
    intervaloHoras: '',
    miligramas: '',
    dosagem: '',
    instrucao: '',
    detalhes: { gotas: '', ml: '', comprimidos: '', ampolas: '' }
  }
])

const adicionarMedicamento = () => {
  medicamentos.value.push({
    nome: '',
    quantidade: '',
    tipo: '',
    intervaloHoras: '',
    miligramas: '',
    dosagem: '',
    instrucao: '',
    detalhes: { gotas: '', ml: '', comprimidos: '', ampolas: '' }
  })
}

// Monta o texto do registro (apenas para exibir)
const registroExibicao = computed(() => {
  const tipo = props.medico?.registro_tipo
  const num  = props.medico?.registro_numero
  const uf   = (props.medico?.registro_uf || '').toUpperCase()
  if (!tipo || !num) return '—'
  return uf ? `${tipo}-${uf} ${num}` : `${tipo} ${num}`
})

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

const prepararMedicamentos = () => {
  return medicamentos.value.map((med) => {
    let miligramaFormatada = med.miligramas

    if (med.tipo === 'Líquido' || med.tipo === 'Gotas') {
      if (miligramaFormatada && !miligramaFormatada.toLowerCase().includes('ml')) {
        miligramaFormatada += ' ml'
      }
    } else if (med.tipo === 'Comprimido') {
      if (miligramaFormatada && !miligramaFormatada.toLowerCase().includes('mg')) {
        miligramaFormatada += ' mg'
      }
    }

    return { ...med, mg: miligramaFormatada }
  })
}

const enviarReceita = async () => {
  try {
    const dadosMedicamentos = prepararMedicamentos()

    // Gera o PDF (agora SEM enviar crm — o backend monta a partir do usuário)
    const pdfResponse = await axios.post('/medico/gerar-receita', {
      paciente_id: props.paciente.id,
      medicamentos: dadosMedicamentos
    }, { responseType: 'blob' })

    // Abre o PDF em nova aba
    const file = new Blob([pdfResponse.data], { type: 'application/pdf' })
    const fileURL = URL.createObjectURL(file)
    window.open(fileURL)

    // Salvar no prontuário
    await axios.post('/medico/salvar-prontuario', {
      paciente_id: props.paciente.id,
      medico_id: props.medico.id,
      data_atendimento: new Date().toISOString().split('T')[0],
      receitas: dadosMedicamentos,
      exames: [],
      atestados: []
    })

    emit('close')
  } catch (err) {
    console.error(err)
    alert('Erro ao enviar a receita.')
  }
}
</script>

<style scoped>
.input-form {
  @apply w-full rounded border border-gray-300 px-3 py-2 text-sm;
}
</style>
