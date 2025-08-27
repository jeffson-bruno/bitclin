<template>
  <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center px-4">
    <transition name="modal-fade" appear>
      <div
        v-show="isVisible"
        class="relative bg-white w-full max-w-4xl rounded-lg shadow-xl p-6 overflow-y-auto max-h-[90vh]"
      >
        <!-- Botão Fechar -->
        <button
          class="absolute top-4 right-4 text-gray-500 hover:text-red-500 text-2xl font-bold transition-transform active:scale-90"
          @click="fecharModal"
          title="Fechar"
        >
          &times;
        </button>

        <!-- Título Centralizado -->
        <h2 class="text-2xl font-bold text-center text-gray-800 mb-6">
          Cadastro de Paciente
        </h2>

        <!-- Mensagem de Sucesso -->
        <div
          v-if="msgSucesso"
          class="mb-4 p-3 bg-green-100 text-green-800 rounded text-center font-semibold"
        >
          Paciente Salvo com Sucesso!
        </div>


        <!-- Formulário -->
        <form @submit.prevent="submit">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Coluna Esquerda -->
            <div class="space-y-4">
              <div>
                <label class="block font-medium">Nome</label>
                <input
                  v-model="form.nome"
                  type="text"
                  class="mt-1 w-full rounded border-gray-300"
                  required
                />
              </div>

              <div>
                <label class="block font-medium">CPF</label>
                <input
                  v-model="form.cpf"
                  type="text"
                  maxlength="14"
                  inputmode="numeric"
                  @input="form.cpf = mascaraCPF(form.cpf)"
                  class="mt-1 w-full rounded border-gray-300"
                />
              </div>

              <div>
                <label class="block font-medium">Telefone</label>
                <input
                  v-model="form.telefone"
                  type="text"
                  maxlength="15"
                  inputmode="numeric"
                  @input="form.telefone = mascaraTelefone(form.telefone)"
                  class="mt-1 w-full rounded border-gray-300"
                />
              </div>

              <div>
                <label class="block font-medium">Endereço</label>
                <input
                  v-model="form.endereco"
                  type="text"
                  class="mt-1 w-full rounded border-gray-300"
                />
              </div>

              <!-- Data de Nascimento (novo campo) -->
               <div>
                <label class="block font-medium">Data de Nascimento</label>
                <input
                    type="text"
                    v-model="form.data_nascimento"
                    @input="form.data_nascimento = mascaraData(form.data_nascimento)"
                    placeholder="dd/mm/aaaa"
                    class="mt-1 w-full rounded border-gray-300"
                    required
                />
                </div>

                <!-- Estado Civil -->

                <div>
                  <label class="block font-medium">Estado Civil</label>
                  <select
                      v-model="form.estado_civil"
                      class="mt-1 w-full rounded border-gray-300"
                      required
                  >
                      <option disabled value="">Selecione</option>
                      <option value="solteiro">Solteiro(a)</option>
                      <option value="casado">Casado(a)</option>
                      <option value="divorciado">Divorciado(a)</option>
                      <option value="viuvo">Viúvo(a)</option>
                      <option value="outro">Outro</option>
                  </select>
                </div>
            </div>

            <!-- Coluna Direita -->
            
            <div class="space-y-4">
            
              <!-- Profissão (novo campo) -->
              <div>
                <label class="block font-medium">Profissão</label>
                <input type="text"
                      v-model="paciente.profissao"
                      placeholder="Ex.: Professor(a), Pedreiro, Doméstica"
                      class="mt-1 w-full rounded border-gray-300" />
              </div>

            <!-- Procedimento -->
              <div>
                <label class="block font-medium">Procedimento</label>
                <select
                  v-model="form.procedimento"
                  class="mt-1 w-full rounded border-gray-300"
                  required
                >
                  <option disabled value="">Selecione</option>
                  <option value="consulta">Consulta</option>
                  <option value="exame">Exame</option>
                </select>
              </div>

              <!-- Se for consulta -->
              <div v-if="form.procedimento === 'consulta'" class="space-y-4">
                <div>
                  <label class="block font-medium">Médico</label>
                  <select v-model="form.medico_id" class="mt-1 w-full rounded border-gray-300">
                    <option disabled value="">Selecione um médico</option>
                    <option v-for="medico in props.medicos" :key="medico.id" :value="medico.id">
                      {{ medico.name }}
                    </option>
                  </select>
                </div>

                <div v-if="agendaDisponivel.length">
                  <label class="block font-medium">Data da Consulta</label>
                  <select v-model="form.data_consulta" class="mt-1 w-full rounded border-gray-300">
                    <option disabled value="">Selecione a data</option>
                    <option v-for="data in agendaDisponivel" :key="data">{{ data }}</option>
                  </select>
                </div>
              </div>

              <!-- Se for exame -->
              <div v-if="form.procedimento === 'exame'" class="space-y-4">
                <div>
                  <label class="block font-medium">Exame</label>
                  <select v-model="form.exame_id" class="mt-1 w-full rounded border-gray-300">
                    <option disabled value="">Selecione o exame</option>
                    <option v-for="exame in props.exames" :key="exame.id" :value="exame.id">
                      {{ exame.nome }}
                    </option>
                  </select>
                  <div v-if="exameSelecionado" class="text-sm mt-2">
                    Exame selecionado: {{ exameSelecionado.nome }}
                  </div>
                </div>
              </div>

              <!-- Turno do exame: só mostra o SELECT se o exame oferece ambos os turnos -->
              <div v-if="form.procedimento === 'exame' && exameInfo && exameInfo.turno === 'ambos'">
                <label class="block font-medium">Turno</label>
                <select
                  v-model="form.turno_exame"
                  class="mt-1 w-full rounded border-gray-300"
                  required
                >
                  <option disabled value="">Selecione o turno</option>
                  <option value="manha">Manhã</option>
                  <option value="tarde">Tarde</option>
                </select>
              </div>


              <!-- Dia da Semana para Exame -->
              <div v-if="form.procedimento === 'exame' && diasPermitidosExame.length">
                <label class="block font-medium">Dia da Semana para Exame</label>
                <select
                  v-model="form.dia_semana_exame"
                  class="mt-1 w-full rounded border-gray-300"
                  required
                >
                  <option disabled value="">Selecione o dia</option>
                  <option
                    v-for="dia in diasPermitidosExame"
                    :key="dia"
                    :value="dia"
                  >
                    {{ dia.charAt(0).toUpperCase() + dia.slice(1) }}
                  </option>
                </select>
              </div>


              

              <!-- Preço, Pagamento e Forma de Pagamento -->
              <div>
                <label class="block font-medium">Preço</label>
                <input
                  v-model="form.preco"
                  type="number"
                  step="0.01"
                  class="mt-1 w-full rounded border-gray-300"
                  required
                />
              </div>
              <div>
                <label class="block font-medium">Pagamento Realizado?</label>
                <select
                  v-model="form.pago"
                  class="mt-1 w-full rounded border-gray-300"
                  required
                >
                  <option :value="true">Sim</option>
                  <option :value="false">Não</option>
                </select>
              </div>

              <div v-if="form.pago === true">
                <label class="block font-medium">Forma de Pagamento</label>
                <select
                  v-model="form.forma_pagamento"
                  class="mt-1 w-full rounded border-gray-300"
                >
                  <option value="dinheiro">Dinheiro</option>
                  <option value="cartao">Cartão</option>
                  <option value="pix">Pix</option>
                </select>
              </div>

              <div v-if="form.pago === false">
                <label class="block font-medium">Data do Pagamento</label>
                <input
                  v-model="form.data_pagamento"
                  type="date"
                  class="mt-1 w-full rounded border-gray-300"
                />
              </div>
            </div>
          </div>

          <!-- Botão Salvar -->
          <div class="pt-6 flex justify-end">
            <button
              type="submit"
              class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition-transform active:scale-95"
            >
              Salvar
            </button>
          </div>
        </form>
      </div>
    </transition>
  </div>
</template>

<script setup>
import axios from 'axios'
import { ref, watch, computed } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import { mascaraCPF, mascaraTelefone, mascaraData } from '@/utils/masks'

const exameInfo = ref(null) // { turno: 'manha'|'tarde'|'ambos', dias_semana: [...], preco: ... }
// Props recebidas
const props = defineProps({
  medicos: { type: Array, default: () => [] },
  exames:  { type: Array, default: () => [] },
})

// Usuário logado
const user = usePage().props.auth.user

const agendaDisponivel = ref([])
const diasPermitidosExame = ref([])
const emit = defineEmits(['close'])
const isVisible = ref(true)
const msgSucesso = ref(false)

// Estado base do formulário
const formDefaults = () => ({
  nome: '',
  cpf: '',
  telefone: '',
  estado_civil: '',
  endereco: '',
  data_nascimento: '',
  procedimento: '',
  preco: '',
  pago: false,
  forma_pagamento: '',
  data_pagamento: '',
  medico_id: null,
  data_consulta: '',
  exame_id: null,
  turno_exame: '',
  dia_semana_exame: '',
  data_exame: '',
  profissao: '',
})

const form = ref(formDefaults())

const exameSelecionado = computed(() => {
  const id = Number(form.value.exame_id)
  if (!id) return null
  return props.exames.find(e => Number(e.id) === id) || null
})


// 🔁 Alias de compatibilidade: permite usar `paciente.*` no template apontando para `form.*`
const paciente = computed({
  get: () => form.value,
  set: (v) => { form.value = { ...form.value, ...(v || {}) } },
})

// Máscaras nos inputs (use @input no template)
function onCPFInput(e)      { form.value.cpf = mascaraCPF(e.target.value || '') }
function onTelefoneInput(e) { form.value.telefone = mascaraTelefone(e.target.value || '') }
function onDataNascInput(e) { form.value.data_nascimento = mascaraData(e.target.value || '') }

// 🔄 Reset quando troca o procedimento
watch(() => form.value.procedimento, (p) => {
  if (p === 'consulta') {
    form.value.exame_id = null
    form.value.turno_exame = ''
    form.value.dia_semana_exame = ''
    form.value.data_exame = ''
    diasPermitidosExame.value = []
  } else if (p === 'exame') {
    form.value.medico_id = null
    form.value.data_consulta = ''
    agendaDisponivel.value = []
  }
})

// 🔄 Watch para seleção de médico em consultas
watch(() => form.value.medico_id, async (medicoId) => {
  if (form.value.procedimento !== 'consulta') return
  if (!medicoId) { agendaDisponivel.value = []; form.value.preco = ''; return }

  try {
    const [dias, preco] = await Promise.all([
      axios.get(`/cadastro/agenda-medica/medico/${medicoId}/dias`),
      axios.get(`/cadastro/agenda-medica/medico/${medicoId}/preco`)
    ])
    agendaDisponivel.value = dias.data || []
    form.value.preco = preco.data?.preco ?? ''
  } catch {
    agendaDisponivel.value = []
    form.value.preco = ''
  }
})

// 🔄 Watch para seleção de exame
watch(() => form.value.exame_id, async (exameId) => {
  if (form.value.procedimento !== 'exame') return

  if (!exameId) {
    form.value.preco = ''
    form.value.turno_exame = ''
    diasPermitidosExame.value = []
    exameInfo.value = null
    return
  }

  try {
    const { data } = await axios.get(`/cadastro/exames/${exameId}/info`)

    // Preço
    form.value.preco = data?.preco ?? ''

    // Turno vindo do cadastro do exame
    exameInfo.value = {
      turno: data?.turno || '', // 'manha' | 'tarde' | 'ambos'
      dias_semana: (() => {
        try {
          return Array.isArray(data?.dias_semana)
            ? data.dias_semana
            : JSON.parse(data?.dias_semana || '[]')
        } catch { return [] }
      })()
    }

    // Se o exame tem turno fixo, já define no form; se for "ambos", exige escolha do usuário
    if (exameInfo.value.turno === 'manha' || exameInfo.value.turno === 'tarde') {
      form.value.turno_exame = exameInfo.value.turno
    } else if (exameInfo.value.turno === 'ambos') {
      form.value.turno_exame = '' // força selecionar no select
    } else {
      form.value.turno_exame = ''
    }

    diasPermitidosExame.value = exameInfo.value.dias_semana
  } catch {
    form.value.preco = ''
    form.value.turno_exame = ''
    diasPermitidosExame.value = []
    exameInfo.value = null
  }
})


// Fecha a modal
function fecharModal() {
  isVisible.value = false
  setTimeout(() => emit('close'), 300)
}

// Envio do formulário
function submit() {
  const dadosParaEnviar = { ...form.value }

  // Remove máscaras
  dadosParaEnviar.cpf = (dadosParaEnviar.cpf || '').replace(/\D/g, '')
  dadosParaEnviar.telefone = (dadosParaEnviar.telefone || '').replace(/\D/g, '')

  // Converte dd/mm/aaaa -> aaaa-mm-dd (se vier mascarado)
  const toISO = (br) => {
    if (!br || typeof br !== 'string' || !br.includes('/')) return br
    const [d, m, a] = br.split('/')
    return (d && m && a) ? `${a}-${m}-${d}` : br
  }
  dadosParaEnviar.data_pagamento   = toISO(dadosParaEnviar.data_pagamento)
  dadosParaEnviar.data_nascimento  = toISO(dadosParaEnviar.data_nascimento)

  router.post('/pacientes', dadosParaEnviar, {
    onSuccess: () => {
      window.$toast?.('Paciente salvo com sucesso!')
      setTimeout(() => { fecharModal(); router.visit('/pacientes') }, 900)
    },
    onError: (errors) => {
      console.error('Erro ao salvar:', errors)
      window.$toast?.('Corrija os campos destacados.')
    }
  })
}

</script>


<style>
.modal-fade-enter-active,
.modal-fade-leave-active {
  transition: all 0.3s ease;
}
.modal-fade-enter-from,
.modal-fade-leave-to {
  opacity: 0;
  transform: scale(0.95);
}
.modal-fade-enter-to,
.modal-fade-leave-from {
  opacity: 1;
  transform: scale(1);
}
</style>
