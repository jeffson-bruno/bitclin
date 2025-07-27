<template>
  <div class="relative w-full h-screen bg-white overflow-hidden flex flex-col justify-center items-center">

    <!-- Marca d'água -->
    <img
      src="/images/marca-dagua.png"
      alt="Logo da Clínica"
      class="absolute w-1/2 opacity-10 z-0"
      style="top: 50%; left: 50%; transform: translate(-50%, -50%);"
    />

    <!-- Área principal -->
    <div class="z-10 text-center">
      <h1 class="text-7xl font-bold text-red-600 mb-8">SENHA: C001</h1>
      <p class="text-5xl text-red-600 font-semibold">Nome do Paciente</p>
    </div>

    <!-- Rodapé com últimas senhas -->
    <div class="absolute bottom-6 w-full text-center z-10">
      <p class="text-xl text-gray-700 font-medium">
        Senhas Chamadas: SENHA: C001 | SENHA: C002 | SENHA: C003
      </p>
    </div>
  </div>
</template>

<script setup>
// Aqui ainda não há lógica. Será adicionada nos próximos passos.
</script>
