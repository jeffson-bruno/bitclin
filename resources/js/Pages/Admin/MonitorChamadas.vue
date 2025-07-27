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
    <div class="z-10 text-center animate-fade-in">
      <h1 class="text-7xl font-bold text-red-600 mb-8">SENHA: {{ senhaAtual.senha || '—' }}</h1>
      <p class="text-5xl text-red-600 font-semibold">{{ senhaAtual.nome || 'Aguardando chamada...' }}</p>
    </div>

    <!-- Rodapé com animação -->
    <div class="absolute bottom-6 w-full text-center z-10">
      <div class="overflow-hidden whitespace-nowrap animate-marquee">
        <span class="text-xl text-gray-700 font-medium">
          Senhas Chamadas:
          <span v-for="(s, index) in ultimasChamadas" :key="index">
            SENHA: {{ s.senha }}<span v-if="index < ultimasChamadas.length - 1"> | </span>
          </span>
        </span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import axios from 'axios'

const senhaAtual = ref({})
const ultimasChamadas = ref([])
const ultimaSenhaChamada = ref(null)
const audioBip = new Audio('/sounds/infobleep.mp3')

const falarSenha = (senha, nome) => {
  const utterance = new SpeechSynthesisUtterance(`Senha ${senha}, ${nome}, por favor, dirigir-se ao atendimento.`)
  utterance.lang = 'pt-BR'
  speechSynthesis.speak(utterance)
}

const buscarDadosChamadas = async () => {
  try {
    const response = await axios.get('/monitor/dados-chamadas')
    const novaSenha = response.data.atual || {}

    if (novaSenha && novaSenha.senha !== ultimaSenhaChamada.value?.senha) {
    audioBip.play()
    falarSenha(novaSenha)
    }

    senhaAtual.value = novaSenha
    ultimasChamadas.value = response.data.ultimas || []
  } catch (err) {
    console.error('Erro ao buscar chamadas', err)
  }
}

onMounted(() => {
  buscarDadosChamadas()
  setInterval(buscarDadosChamadas, 3000)
})
</script>

<style scoped>
@keyframes marquee {
  0% { transform: translateX(100%); }
  100% { transform: translateX(-100%); }
}

.animate-marquee {
  display: inline-block;
  animation: marquee 20s linear infinite;
}

@keyframes fade-in {
  0% { opacity: 0; transform: scale(0.95); }
  100% { opacity: 1; transform: scale(1); }
}

.animate-fade-in {
  animation: fade-in 0.8s ease-in-out;
}
</style>
