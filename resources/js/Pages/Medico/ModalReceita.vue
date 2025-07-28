<template>
  <div class="fixed inset-0 bg-black/40 z-50 flex items-center justify-center px-4">
    <div class="bg-white rounded-xl shadow-lg max-w-3xl w-full p-6 relative">
      <!-- Botão Fechar -->
      <button @click="$emit('close')" class="absolute top-3 right-3 text-gray-500 hover:text-red-500">
        ✕
      </button>

      <!-- Título -->
      <h2 class="text-2xl font-bold mb-4 text-center">Receita Médica</h2>

      <!-- Informações do Paciente e Médico -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
        <div>
          <label class="text-sm text-gray-600">Nome do Paciente</label>
          <div class="font-semibold">{{ paciente.nome }}</div>
        </div>
        <div>
          <label class="text-sm text-gray-600">Idade</label>
          <div class="font-semibold">{{ paciente.idade }} anos</div>
        </div>
        <div>
          <label class="text-sm text-gray-600">Nome do Médico</label>
          <div class="font-semibold">{{ medico.nome }}</div>
        </div>
        <div>
          <label class="text-sm text-gray-600">CRM</label>
          <input v-model="crm" type="text" class="input-form" placeholder="Digite o número do conselho">
        </div>
      </div>

      <!-- Seção de Medicamentos (estático por enquanto) -->
      <div class="mt-6 border-t pt-4">
        <h3 class="font-semibold text-lg mb-2">Prescrição</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="text-sm text-gray-600">Nome do Remédio</label>
            <input type="text" class="input-form" disabled placeholder="(campo será dinâmico)">
          </div>
          <div>
            <label class="text-sm text-gray-600">Quantidade (Caixas)</label>
            <input type="number" class="input-form" disabled>
          </div>
        </div>
      </div>

      <!-- Rodapé da Clínica -->
      <div class="mt-8 text-center text-sm text-gray-600 border-t pt-4">
        <div>Clínica Exemplo - Rua Projetada 01, Cocal-PI</div>
        <div>Telefone: (86) 9 9999-9999 | CEP: 64230-000</div>
        <div>Instagram: @clinicaexemplo | Facebook: /clinicaexemplo</div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const props = defineProps({
  paciente: Object,
  medico: Object,
})

const crm = ref('')
</script>

<style scoped>
.input-form {
  @apply w-full rounded border border-gray-300 px-3 py-2 text-sm;
}
</style>
