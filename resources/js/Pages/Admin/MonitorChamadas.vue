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
      <h1
        :key="senhaAtual.senha"
        class="text-7xl font-bold mb-8 fade-in"
        :class="senhaCor"
      >
        SENHA: {{ senhaAtual.senha || '—' }}
      </h1>
      <p
        :key="senhaAtual.nome"
        class="text-5xl font-semibold fade-in"
        :class="senhaCor"
      >
        {{ senhaAtual.nome || 'Aguardando chamada...' }}
      </p>
    </div>

    <!-- Rodapé -->
    <div class="absolute bottom-6 w-full z-10 px-8">
      <p class="text-xl text-gray-700 font-bold uppercase text-center mb-2">
        Últimas Chamadas:
      </p>
      <div class="relative w-full overflow-hidden border border-gray-300 rounded-md px-8 py-2">
        <div class="animate-marquee text-2xl text-gray-800 font-semibold whitespace-nowrap">
          <span v-for="(s, index) in ultimasChamadas" :key="index" class="mx-6">
            SENHA: {{ s.senha }}
          </span>
        </div>
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
const senhaCor = ref('text-blue-600')
const audioBip = new Audio('/sounds/infobleep.mp3')

// Fala a senha de forma pausada
const falarSenha = (senha, nome) => {
  const sintetizador = window.speechSynthesis
  if (sintetizador.speaking) sintetizador.cancel()

  const senhaSeparada = senha?.split('').join(' ') || ''
  const texto = `Senha ${senhaSeparada}, ${nome}, por favor, dirigir-se ao atendimento.`

  const fala = new SpeechSynthesisUtterance(texto)
  fala.lang = 'pt-BR'
  fala.rate = 0.9
  sintetizador.speak(fala)
}

// Busca dados da chamada
const buscarDadosChamadas = async () => {
  try {
    const response = await axios.get('/monitor/dados-chamadas')
    const novaSenha = response.data.atual || {}

    if (novaSenha && novaSenha.senha !== ultimaSenhaChamada.value?.senha) {
      senhaCor.value = 'text-red-600'
      audioBip.play()
      falarSenha(novaSenha.senha, novaSenha.nome)

      setTimeout(() => {
        senhaCor.value = 'text-blue-600'
      }, 5000)

      ultimaSenhaChamada.value = novaSenha
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

@keyframes fadeIn {
  from { opacity: 0; transform: scale(0.95); }
  to { opacity: 1; transform: scale(1); }
}

.fade-in {
  animation: fadeIn 0.6s ease-out;
}

@keyframes marquee {
  0% { transform: translateX(100%); }
  100% { transform: translateX(-100%); }
}

.animate-marquee {
  display: inline-block;
  animation: marquee 35s linear infinite;
}


</style>
