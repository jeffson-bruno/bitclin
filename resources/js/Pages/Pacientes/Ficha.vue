<template>
  <div class="ficha-container">
    <!-- Cabeçalho com logo e título -->
    <div class="cabecalho">
      <img src="/logo.png" alt="Logo da Clínica" class="logo" />
      <h1 class="titulo">Ficha de Atendimento</h1>
    </div>

    <!-- Primeira via -->
    <section class="via">
      <h2 class="subtitulo">Via do Paciente</h2>
      <hr />
      <table class="tabela-dados">
        <tbody>
          <tr><th>Nome</th><td>{{ paciente.nome }}</td></tr>
          <tr><th>Estado Civil</th><td>{{ paciente.estado_civil }}</td></tr>
          <tr><th>CPF</th><td>{{ paciente.cpf }}</td></tr>
          <tr><th>Telefone</th><td>{{ paciente.telefone }}</td></tr>
          <tr><th>Endereço</th><td>{{ paciente.endereco }}</td></tr>
          <tr><th>Data de Nascimento</th><td>{{ formatarData(paciente.data_nascimento) }}</td></tr>
          <tr><th>Procedimento</th><td>{{ procedimento.procedimento }}</td></tr>
        </tbody>
      </table>
    </section>

    <div class="divisoria">---------------- Corte Aqui ----------------</div>

    <!-- Segunda via -->
    <section class="via">
      <h2 class="subtitulo">Via da Clínica</h2>
      <hr />
      <table class="tabela-dados">
        <tbody>
          <tr><th>Nome</th><td>{{ paciente.nome }}</td></tr>
          <tr><th>Estado Civil</th><td>{{ paciente.estado_civil }}</td></tr>
          <tr><th>CPF</th><td>{{ paciente.cpf }}</td></tr>
          <tr><th>Telefone</th><td>{{ paciente.telefone }}</td></tr>
          <tr><th>Endereço</th><td>{{ paciente.endereco }}</td></tr>
          <tr><th>Data de Nascimento</th><td>{{ formatarData(paciente.data_nascimento) }}</td></tr>
          <tr><th>Procedimento</th><td>{{ procedimento.procedimento }}</td></tr>
          <tr><th>Valor</th><td>R$ {{ procedimento.valor.toFixed(2) }}</td></tr>
          <tr><th>Status</th><td>{{ $procedimento['pago'] }}</td></tr>
        </tbody>
      </table>
    </section>
  </div>
</template>

<script setup>
import { onMounted } from 'vue'
import { usePage } from '@inertiajs/vue3'

const { paciente, procedimento } = usePage().props

const formatarData = (data) => {
  const d = new Date(data)
  return d.toLocaleDateString('pt-BR')
}

onMounted(() => {
  window.print()
  setTimeout(() => window.close(), 1000)
})
</script>

<style scoped>
.ficha-container {
  max-width: 800px;
  margin: auto;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  color: #333;
  padding: 20px;
}

/* Cabeçalho com borda */
.cabecalho {
  display: flex;
  align-items: center;
  border: 2px solid #000;
  padding: 10px;
  margin-bottom: 20px;
}

.logo {
  width: 80px;
  height: auto;
  margin-right: 20px;
}

.titulo {
  font-size: 1.8rem;
  font-weight: bold;
  text-align: center;
  flex: 1;
  color: #2c3e50;
}

/* Estilo da via */
.via {
  margin-bottom: 30px;
}

.subtitulo {
  text-align: center;
  font-size: 1.2rem;
  color: #34495e;
  margin-bottom: 10px;
}

/* Tabela de dados do paciente */
.tabela-dados {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.95rem;
  margin-top: 10px;
}

.tabela-dados th,
.tabela-dados td {
  border: 1px solid #888;
  padding: 6px 10px;
  text-align: left;
}

.tabela-dados th {
  background-color: #f0f0f0;
  width: 40%;
  font-weight: 600;
}

.divisoria {
  text-align: center;
  margin: 20px 0;
  font-size: 0.85rem;
  color: #999;
  border-top: 1px dashed #aaa;
  padding-top: 5px;
}

@media print {
  @page {
    size: A4 portrait;
    margin: 20mm;
  }

  body {
    print-color-adjust: exact !important;
    -webkit-print-color-adjust: exact !important;
  }

  .ficha-container {
    padding: 0;
    color: #000;
  }

  .cabecalho {
    border: 2px solid #000;
  }

  .divisoria {
    margin: 10mm 0;
  }
}
</style>

