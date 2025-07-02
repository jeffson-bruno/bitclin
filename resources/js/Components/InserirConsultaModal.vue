<template>
  <div v-if="show" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl p-6">
      <div class="flex justify-between items-center mb-4">
        <h2 class="text-lg font-semibold text-gray-800">Inserir Consulta</h2>
        <button @click="close" class="text-gray-500 hover:text-gray-800 text-xl font-bold">&times;</button>
      </div>

      <form @submit.prevent="submitForm">
        <!-- Nome do Especialista -->
        <div class="mb-4">
          <label class="block text-gray-700 font-medium mb-1">Nome do Especialista</label>
          <input v-model="form.nome" type="text" class="w-full border rounded px-3 py-2" required />
        </div>

        <!-- Especialidade -->
        <div class="mb-4">
          <label class="block text-gray-700 font-medium mb-1">Especialidade</label>
          <input v-model="form.especialidade" type="text" class="w-full border rounded px-3 py-2" required />
        </div>

        <!-- CRM/CRP/CRF -->
        <div class="mb-4">
          <label class="block text-gray-700 font-medium mb-1">CRM/CRP/CRF</label>
          <input v-model="form.registro" type="text" class="w-full border rounded px-3 py-2" required />
        </div>

        <!-- Horários -->
        <div class="mb-4">
          <label class="block text-gray-700 font-medium mb-1">Horários de Atendimento</label>
          <input v-model="form.horarios" type="text" placeholder="Ex: 08:00, 09:00, 10:00" class="w-full border rounded px-3 py-2" required />
        </div>

        <!-- Data ou Calendário -->
        <div class="mb-4">
          <label class="block text-gray-700 font-medium mb-1">Data da Consulta</label>
          <input v-model="form.data" type="date" class="w-full border rounded px-3 py-2" required />
        </div>

        <!-- Botões -->
        <div class="flex justify-end">
          <button type="button" @click="close" class="mr-3 px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">
            Cancelar
          </button>
          <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
            Salvar
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'

const props = defineProps({
  show: Boolean,
})

const emit = defineEmits(['close', 'salvo'])

const close = () => {
  emit('close')
}

// Formulário
const form = useForm({
  nome: '',
  especialidade: '',
  registro: '',
  horarios: '',
  data: '',
})

const submitForm = () => {
  // Aqui você pode salvar via Inertia post (ajustar rota depois)
  // form.post(route('agenda.salvar'), {
  //   onSuccess: () => {
  //     emit('salvo')
  //     close()
  //   }
  // })

  console.log(form) // temporário
  emit('salvo') // emitir evento para atualizar dados externos (ex: calendário)
  close()
}
</script>
