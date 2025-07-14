<template>
  <div class="p-8">
    <h1 class="text-2xl font-bold">Bem-vindo ao Painel do Médico</h1>

    <button
        @click="logout"
        class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 transition"
      >
        Sair
      </button>
  </div>
</template>

<script setup>
import { router } from '@inertiajs/vue3'

function logout() {
  router.post('/logout')
}
</script>
