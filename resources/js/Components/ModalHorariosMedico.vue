<template>
  <div v-if="show" class="fixed inset-0 bg-black bg-opacity-40 z-50 flex items-center justify-center">
    <div class="bg-white p-6 rounded-xl w-full max-w-lg shadow-lg relative">
      <h2 class="text-xl font-bold mb-4 text-center">Horários de Atendimento</h2>
      <p class="text-center text-gray-600 mb-4">{{ dataSelecionada }}</p>

      <div v-if="horarios.length > 0">
        <ul class="space-y-3">
          <li v-for="(item, index) in horarios" :key="index" class="border p-3 rounded shadow-sm">
            <strong>Dr. {{ item.medico }}</strong><br />
            <span>Horários: {{ item.horarios.join(', ') }}</span>
          </li>
        </ul>
      </div>
      <p v-else class="text-center text-gray-500">Nenhum horário encontrado para este dia.</p>

      <button
        @click="$emit('close')"
        class="absolute top-2 right-4 text-gray-500 hover:text-red-600 text-2xl"
      >
        &times;
      </button>
    </div>
  </div>
</template>

<script setup>
defineProps({
  show: Boolean,
  horarios: Array,
  dataSelecionada: String
})
</script>
