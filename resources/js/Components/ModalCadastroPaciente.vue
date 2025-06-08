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
            </div>

            <!-- Coluna Direita -->
            <div class="space-y-4">
              <div>
                <label class="block font-medium">Procedimento</label>
                <select
                  v-model="form.procedimento"
                  @change="definirPreco"
                  class="mt-1 w-full rounded border-gray-300"
                >
                  <option disabled value="">Selecione</option>
                  <option value="consulta">Consulta</option>
                  <option value="exame">Exame</option>
                </select>
              </div>

              <div>
                <label class="block font-medium">Preço</label>
                <input
                  v-model="form.preco"
                  type="number"
                  step="0.01"
                  class="mt-1 w-full rounded border-gray-300"
                />
              </div>

              <div>
                <label class="block font-medium">Pagamento Realizado?</label>
                <select
                  v-model="form.pago"
                  class="mt-1 w-full rounded border-gray-300"
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
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'

const emit = defineEmits(['close'])
const isVisible = ref(true)
const msgSucesso = ref(false)

const form = ref({
  nome: '',
  cpf: '',
  telefone: '',
  endereco: '',
  procedimento: '',
  preco: '',
  pago: false,
  forma_pagamento: '',
  data_pagamento: '',
})

function definirPreco() {
  if (form.value.procedimento === 'consulta') {
    form.value.preco = 150.0
  } else if (form.value.procedimento === 'exame') {
    form.value.preco = 250.0
  } else {
    form.value.preco = ''
  }
}

// Máscara para CPF
function mascaraCPF(value) {
  value = value.replace(/\D/g, '')
  value = value.replace(/(\d{3})(\d)/, '$1.$2')
  value = value.replace(/(\d{3})(\d)/, '$1.$2')
  value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2')
  return value
}

// Máscara para Telefone
function mascaraTelefone(value) {
  value = value.replace(/\D/g, '')
  if (value.length <= 10) {
    value = value.replace(/(\d{2})(\d)/, '($1) $2')
    value = value.replace(/(\d{4})(\d)/, '$1-$2')
  } else {
    value = value.replace(/(\d{2})(\d)/, '($1) $2')
    value = value.replace(/(\d{5})(\d)/, '$1-$2')
  }
  return value
}

function fecharModal() {
  isVisible.value = false
  setTimeout(() => {
    emit('close')
  }, 300) // tempo da animação
}

function submit() {
  const dadosParaEnviar = { ...form.value }
  dadosParaEnviar.cpf = dadosParaEnviar.cpf.replace(/\D/g, '')
  dadosParaEnviar.telefone = dadosParaEnviar.telefone.replace(/\D/g, '')

  router.post('/pacientes', dadosParaEnviar, {
    onSuccess: () => {
      // Mostra o toast primeiro
      window.$toast?.('Paciente salvo com sucesso!')

      // Aguarda 100ms para garantir que o toast apareça
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
