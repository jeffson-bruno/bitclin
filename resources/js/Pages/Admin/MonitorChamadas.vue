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

    <!-- Rodapé -->
    <div class="absolute bottom-6 w-full text-center z-10">
        <p class="text-xl text-gray-700 font-bold uppercase mb-2">Senhas Chamadas:</p>
        <div class="w-full overflow-hidden">
            <div class="animate-marquee text-2xl text-gray-800 font-semibold">
            <span v-for="(s, index) in ultimasChamadas" :key="index" class="mx-4">
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
const corSenha = ref('text-blue-600') // azul por padrão
const audioBip = new Audio('/sounds/infobleep.mp3')

// Falar a senha com voz
const falarSenha = (senha, nome) => {
  const sintetizador = window.speechSynthesis
  if (sintetizador.speaking) sintetizador.cancel()

  const senhaSeparada = senha.split('').join(' ')
  const texto = `Senha ${senhaSeparada}, ${nome}, por favor dirigir-se ao atendimento.`

  const fala = new SpeechSynthesisUtterance(texto)
  fala.lang = 'pt-BR'
  sintetizador.speak(fala)
}

// Buscar chamadas e aplicar lógica de alteração de cor
const buscarDadosChamadas = async () => {
  try {
    const response = await axios.get('/monitor/dados-chamadas')
    const novaSenha = response.data.atual || {}

    if (novaSenha && novaSenha.senha !== ultimaSenhaChamada.value?.senha) {
      await audioBip.play().catch(() => console.warn('Audio não pôde ser reproduzido'))
      falarSenha(novaSenha.senha, novaSenha.nome)

      corSenha.value = 'text-red-600' // fica vermelho
      setTimeout(() => {
        corSenha.value = 'text-blue-600' // volta para azul após 3s
        senhaAtual.value = novaSenha // só atualiza depois da mudança de cor
      }, 3000)

      ultimaSenhaChamada.value = novaSenha
      ultimasChamadas.value = response.data.ultimas || []
    }
  } catch (err) {
    console.error('Erro ao buscar chamadas:', err)
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
  white-space: nowrap;
  animation: marquee 30s linear infinite;
}

@keyframes fade-in {
  0% { opacity: 0; transform: scale(0.95); }
  100% { opacity: 1; transform: scale(1); }
}

.animate-fade-in {
  animation: fade-in 0.8s ease-in-out;
}
</style>
