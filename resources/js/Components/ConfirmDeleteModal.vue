<template>
  <div v-if="show" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded p-6 w-full max-w-sm shadow-lg relative">
      <div class="flex items-center space-x-3 mb-4">
        <svg class="w-6 h-6 text-yellow-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m0-4h.01M12 2a10 10 0 100 20 10 10 0 000-20z" />
        </svg>
        <h2 class="text-lg font-semibold">Confirmar Exclusão</h2>
      </div>
      <p class="mb-6">Deseja realmente excluir <strong>{{ item?.nome }}</strong>?</p>
      <div class="flex justify-end space-x-4">
        <button @click="$emit('cancel')" class="text-gray-600 hover:underline">Cancelar</button>
        <button @click="$emit('confirm', item)" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">Excluir</button>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  show: Boolean,
  item: Object
})

defineEmits(['confirm', 'cancel'])
</script>
