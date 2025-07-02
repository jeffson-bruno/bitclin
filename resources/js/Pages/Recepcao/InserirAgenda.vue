<template>
  <RecepcaoLayout>
    <template #header>
      <h2 class="text-xl font-semibold text-gray-800">Inserir Agenda</h2>
    </template>

    <div class="py-6 px-4 max-w-4xl mx-auto">
      <div class="grid gap-6 sm:grid-cols-2">
        <!-- Cartão Inserir Consulta -->
        <button
          @click="abrirConsulta"
          class="flex flex-col items-center justify-center p-6 bg-white rounded-lg shadow hover:shadow-md transition"
        >
          <svg
            xmlns="http://www.w3.org/2000/svg"
            class="h-12 w-12 text-blue-600 mb-3"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
            stroke-width="2"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              d="M12 4v16m8-8H4"
            />
          </svg>
          <span class="text-lg font-medium text-gray-700">Inserir Consulta</span>
        </button>

        <!-- Cartão Inserir Exame (placeholder) -->
        <button
          @click="abrirExame"
          class="flex flex-col items-center justify-center p-6 bg-white rounded-lg shadow hover:shadow-md transition"
        >
          <svg
            xmlns="http://www.w3.org/2000/svg"
            class="h-12 w-12 text-green-600 mb-3"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
            stroke-width="2"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              d="M12 4v16m8-8H4"
            />
          </svg>
          <span class="text-lg font-medium text-gray-700">Inserir Exame</span>
        </button>
      </div>
    </div>

    <!-- Modal de Consulta -->
    <InserirConsultaModal
      :show="showConsultaModal"
      @close="showConsultaModal = false"
      @salvo="aoSalvarConsulta"
    />

    <!-- (Opcional) Modal de Exame poderia ir aqui futuramente -->
  </RecepcaoLayout>
</template>

<script setup>
import { ref } from 'vue'
import RecepcaoLayout from '@/Layouts/RecepcaoLayout.vue'
import InserirConsultaModal from '@/Components/InserirConsultaModal.vue'

// Estado para exibir/fechar modal
const showConsultaModal = ref(false)

const abrirConsulta = () => {
  showConsultaModal.value = true
}

// Exame ainda não implementado – apenas placeholder
const abrirExame = () => {
  window.$toast?.('Funcionalidade de exame em breve!')
}

// Depois que consulta for salva, podemos recarregar calendário, etc.
const aoSalvarConsulta = () => {
  window.$toast?.('Consulta inserida com sucesso!')
  // Exemplo de navegação de volta ou refresh:
  // router.visit(route('recepcao.dashboard'))
}
</script>
