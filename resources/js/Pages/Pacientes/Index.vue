<script setup>
import { defineProps } from 'vue';

const props = defineProps({
  pacientes: {
    type: Object,  // Mudamos para Object pois estamos lidando com um objeto com "data" e "links"
    default: () => ({ data: [], links: [] })  // Garantimos que o padrão seja um objeto com "data" e "links"
  },
});
</script>

<template>
    <Head title="Pacientes" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold text-gray-800">
                Cadastro de Pacientes
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <!-- Verificando se a lista de pacientes está vazia -->
                        <div v-if="pacientes.data.length === 0">
                            <p class="text-red-500">Nenhum paciente encontrado.</p>
                        </div>
                        <table v-else class="min-w-full table-auto">
                            <thead>
                                <tr>
                                    <th class="px-4 py-2">Nome</th>
                                    <th class="px-4 py-2">CPF</th>
                                    <th class="px-4 py-2">Telefone</th>
                                    <th class="px-4 py-2">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Loop para listar pacientes -->
                                <tr v-for="paciente in pacientes.data" :key="paciente.id">
                                    <td class="border px-4 py-2">{{ paciente.nome }}</td>
                                    <td class="border px-4 py-2">{{ paciente.cpf }}</td>
                                    <td class="border px-4 py-2">{{ paciente.telefone }}</td>
                                    <td class="border px-4 py-2">
                                        <a :href="`/pacientes/${paciente.id}`" class="text-blue-500">Ver</a>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <!-- Paginação (se necessário) -->
                        <div class="mt-4" v-if="pacientes.links && pacientes.links.length > 0">
                            <pagination :links="pacientes.links" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>



