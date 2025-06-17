<template>
  <div class="py-2">
    <input
      type="text"
      v-model="query"
      @input="onSearch"  
      placeholder="Buscar por nome ou CPF"
      class="px-4 py-2 border rounded"
    />
  </div>
</template>

<script setup>
import { ref, defineEmits } from 'vue'

// Utiliza-se um ref para armazenar o valor da pesquisa
const query = ref('')

// Definindo o evento de busca que será emitido para o componente pai
const emit = defineEmits(['search'])

// Emitindo o termo de busca para o componente pai sempre que o valor mudar
const onSearch = () => {
  emit('search', query.value)  // Aqui emite query.value ao invés de searchTerm
}
</script>
