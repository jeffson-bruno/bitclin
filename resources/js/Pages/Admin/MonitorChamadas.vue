<template>
  <div class="relative w-full h-screen bg-white overflow-hidden flex flex-col justify-center items-center">

    <!-- Marca d'água -->
    <img
      src="/images/marca-dagua.png"
      alt="Logo da Clínica"
      class="absolute w-1/2 opacity-10 z-0"
      style="top: 50%; left: 50%; transform: translate(-50%, -50%);"
    />

    <!-- SENHA ATUAL -->
    <div class="z-10 text-center">
      <h1 class="text-7xl font-bold text-red-600 mb-8">SENHA: {{ senhaAtual.senha || '—' }}</h1>
      <p class="text-5xl text-red-600 font-semibold">{{ senhaAtual.nome || 'Aguardando chamada...' }}</p>
    </div>

    <!-- Rodapé -->
    <div class="absolute bottom-6 w-full text-center z-10">
      <p class="text-xl text-gray-700 font-medium">
        Senhas Chamadas:
        <span v-for="(s, index) in ultimasChamadas" :key="index">
          SENHA: {{ s.senha }}<span v-if="index < ultimasChamadas.length - 1"> | </span>
        </span>
      </p>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import axios from 'axios'

const senhaAtual = ref({})
const ultimasChamadas = ref([])

const buscarDadosChamadas = async () => {
  try {
    const response = await axios.get('/monitor/dados-chamadas')
    senhaAtual.value = response.data.atual || {}
    ultimasChamadas.value = response.data.ultimas || []
  } catch (err) {
    console.error('Erro ao buscar chamadas', err)
  }
}

onMounted(() => {
  buscarDadosChamadas()
  setInterval(buscarDadosChamadas, 5000) // Atualiza a cada 5 segundos
})
</script>
