<template>
  <div v-if="mostrar" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow p-6 w-96">
      <h2 class="text-lg font-bold mb-4">Selecione o tipo de senha</h2>

      <div class="space-y-2 mb-4">
        <label class="flex items-center">
          <input type="radio" value="convencional" v-model="tipoLocal" class="mr-2" />
          Convencional
        </label>
        <label class="flex items-center">
          <input type="radio" value="prioridade" v-model="tipoLocal" class="mr-2" />
          Prioridade
        </label>
      </div>

      <div class="flex justify-end space-x-2">
        <button
          @click="$emit('cancelar')"
          class="bg-gray-300 text-gray-800 px-3 py-1 rounded"
        >
          Cancelar
        </button>
        <button
          @click="confirmar"
          :disabled="!tipoLocal"
          class="bg-blue-600 text-white px-3 py-1 rounded disabled:opacity-50"
        >
          Confirmar
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
  mostrar: Boolean,
  tipoInicial: {
    type: String,
    default: ''
  }
})

const emit = defineEmits(['confirmar', 'cancelar'])

const tipoLocal = ref(props.tipoInicial)

watch(() => props.tipoInicial, (val) => {
  tipoLocal.value = val
})

function confirmar() {
  if (!tipoLocal.value) return
  emit('confirmar', tipoLocal.value)
}
</script>

