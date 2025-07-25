<template>
  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold text-gray-800">Área da Recepção</h2>
    </template>

    <div class="py-6 px-4">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <!-- Card Consultas de Hoje (modularizado) -->
        <CardConsultasHoje />

        <!-- Aqui virá depois o novo card de Exames de Hoje -->

      </div>
    </div>

    <!-- Modal de horários ainda existente -->
    <ModalHorariosMedico
      :show="showModalHorarios"
      :horarios="horariosDoDia"
      :dataSelecionada="dataSelecionada"
      @close="showModalHorarios = false"
    />
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import ModalHorariosMedico from '@/Components/ModalHorariosMedico.vue'
import CardConsultasHoje from '@/Components/CardConsultasHoje.vue' // novo componente

import { ref } from 'vue'

const showModalHorarios = ref(false)
const horariosDoDia = ref([])
const dataSelecionada = ref('')
</script>
