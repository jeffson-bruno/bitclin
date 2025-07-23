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
                </div>
              </div>

              <!-- Turno do exame -->
              <div v-if="form.procedimento === 'exame' && exameSelecionado">
                <label class="block font-medium">Turno</label>

                <!-- Exibir texto se turno for fixo -->
                <input
                  v-if="exameSelecionado.turno !== 'ambos'"
                  type="text"
                  class="mt-1 w-full rounded border-gray-300 bg-gray-100"
                  :value="exameSelecionado.turno === 'manha' ? 'Manhã' : 'Tarde'"
                  disabled
                />

                <!-- Exibir select se for ambos -->
                <select
                  v-else
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
import { router } from '@inertiajs/vue3'
import {
  mascaraCPF,
  mascaraTelefone,
  mascaraData
} from '@/utils/masks'

const props = defineProps({
  medicos: {
    type: Array,
    default: () => [],
  },
  exames: {
    type: Array,
    default: () => [],
  },
})

const agendaDisponivel = ref([])
const diasPermitidosExame = ref([])
const emit = defineEmits(['close'])
const isVisible = ref(true)
const msgSucesso = ref(false)

const form = ref({
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
})

watch(() => form.value.medico_id, (medicoId) => {
  if (form.value.procedimento === 'consulta' && medicoId) {
    axios.get(`/admin/agenda-medica/medico/${medicoId}/dias`).then(response => {
      agendaDisponivel.value = response.data
    })
    buscarPrecoConsulta(medicoId)
  }
})

watch(() => form.value.exame_id, (exameId) => {
  if (form.value.procedimento === 'exame' && exameId) {
    axios.get(`/admin/exames/${exameId}/info`).then(response => {
      form.value.preco = response.data.preco

      // Corrigir turno
      if (response.data.turno === 'manha' || response.data.turno === 'tarde') {
        form.value.turno_exame = response.data.turno
      } else {
        form.value.turno_exame = ''
      }

      // Corrigir dias da semana
      try {
        diasPermitidosExame.value = Array.isArray(response.data.dias_semana)
          ? response.data.dias_semana
          : JSON.parse(response.data.dias_semana || '[]')
      } catch (e) {
        diasPermitidosExame.value = []
      }
    })
  }
})

function buscarPrecoConsulta(medicoId) {
  axios.get(`/admin/agenda-medica/medico/${medicoId}/preco`)
    .then(response => {
      form.value.preco = response.data.preco_consulta
    })
    .catch(() => {
      form.value.preco = ''
    })
}

function buscarPrecoExame(exameId) {
  axios.get(`/admin/exames/${exameId}/preco`)
    .then(response => {
      form.value.preco = response.data.preco
    })
    .catch(() => {
      form.value.preco = ''
    })
}

function fecharModal() {
  isVisible.value = false
  setTimeout(() => {
    emit('close')
  }, 300)
}

function submit() {
  const dadosParaEnviar = { ...form.value }
  dadosParaEnviar.cpf = dadosParaEnviar.cpf.replace(/\D/g, '')
  dadosParaEnviar.telefone = dadosParaEnviar.telefone.replace(/\D/g, '')

  // Converte data_pagamento para yyyy-mm-dd
  if (dadosParaEnviar.data_pagamento && dadosParaEnviar.data_pagamento.includes('/')) {
  const dp = dadosParaEnviar.data_pagamento.split('/')
  if (dp.length === 3) {
    dadosParaEnviar.data_pagamento = `${dp[2]}-${dp[1]}-${dp[0]}`
  }
}

if (dadosParaEnviar.data_nascimento && dadosParaEnviar.data_nascimento.includes('/')) {
  const dn = dadosParaEnviar.data_nascimento.split('/')
  if (dn.length === 3) {
    dadosParaEnviar.data_nascimento = `${dn[2]}-${dn[1]}-${dn[0]}`
  }
}

console.log('Dados enviados:', dadosParaEnviar)

  router.post('/pacientes', dadosParaEnviar, {
    onSuccess: () => {
      window.$toast?.('Paciente salvo com sucesso!')
      setTimeout(() => {
        fecharModal()
        router.visit('/pacientes')
      }, 900)
    },
    onError: (errors) => {
      console.error('Erro ao salvar:', errors)
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
