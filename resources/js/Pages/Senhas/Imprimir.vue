<template>
  <div class="tela-impressao">
    <div class="conteudo">
      <h2 class="cabecalho">Senha de Atendimento</h2>
      <div class="linha-divisoria"></div>

      <h1 class="senha">{{ senha.codigo }}</h1>
      <p class="tipo">Tipo: {{ tipoFormatado }}</p>
      <p class="paciente">Paciente: {{ senha.paciente.nome }}</p>
      <p class="data">Data: {{ formatarData(senha.data_emissao) }}</p>

      <div class="linha-divisoria pequena"></div>
      <p class="rodape">{{ nomeClinica }}</p>
    </div>
  </div>
</template>

<script setup>
import { onMounted, computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

const { senha } = usePage().props

const nomeClinica = 'Clínica Exemplo' // <- Substituível por dado dinâmico no futuro

const formatarData = (data) => {
  const d = new Date(data)
  return d.toLocaleDateString('pt-BR')
}

const tipoFormatado = computed(() => {
  return senha.tipo === 'prioridade' ? 'PRIORITÁRIA' : 'CONVENCIONAL'
})

onMounted(() => {
  window.print()
  setTimeout(() => window.close(), 1000)
})
</script>

<style scoped>
@media print {
  @page {
    size: 80mm auto;
    margin: 5mm;
  }

  body {
    margin: 0;
    padding: 0;
    background: white;
  }

  .conteudo {
    text-align: center;
    font-family: monospace;
  }

  .cabecalho {
    font-size: 18px;
    font-weight: bold;
    margin-bottom: 4px;
  }

  .linha-divisoria {
    border-top: 1px dashed #000;
    margin: 8px 0 12px;
  }

  .linha-divisoria.pequena {
    margin-top: 12px;
    margin-bottom: 8px;
  }

  .senha {
    font-size: 36px;
    font-weight: bold;
    margin: 10px 0;
  }

  .tipo {
    font-size: 18px;
    margin: 5px 0;
  }

  .paciente,
  .data {
    font-size: 14px;
    margin: 2px 0;
  }

  .rodape {
    font-size: 12px;
    margin-top: 10px;
  }
}
</style>
