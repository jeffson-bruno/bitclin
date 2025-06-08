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

                        <!-- Botão para abrir a modal -->
                        <button @click="mostrarModal = true" class="px-4 py-2 bg-blue-500 text-white rounded mb-4">
                            Cadastrar Novo Paciente
                        </button>

                        <!-- Verificando se a lista de pacientes está vazia -->
                        <div v-if="!pacientes.data || pacientes.data.length === 0">
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
                        <!-- Paginação -->
                    <div
                        v-if="pacientes.meta && pacientes.meta.total > pacientes.meta.per_page"
                        class="mt-4 flex justify-end items-center space-x-2"
                        >
                        <button
                            @click="goToPage(pacientes.meta.current_page - 1)"
                            :disabled="pacientes.meta.current_page === 1"
                            class="px-3 py-1 bg-gray-200 rounded disabled:opacity-50"
                        >
                            Anterior
                        </button>

                        <span class="text-sm text-gray-700">
                            Página {{ pacientes.meta.current_page }} de {{ pacientes.meta.last_page }}
                        </span>

                        <button
                            @click="goToPage(pacientes.meta.current_page + 1)"
                            :disabled="pacientes.meta.current_page === pacientes.meta.last_page"
                            class="px-3 py-1 bg-gray-200 rounded disabled:opacity-50"
                        >
                            Próxima
                        </button>
                    </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>

    <!-- Modal visível com base na variável mostrarModal -->
    <ModalCadastroPaciente v-if="mostrarModal" @close="mostrarModal = false" />
</template>

<script setup>
import { Head } from '@inertiajs/vue3'
import { ref } from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import ModalCadastroPaciente from '@/Components/ModalCadastroPaciente.vue'
import { usePage, router } from '@inertiajs/vue3';

const { pacientes } = usePage().props;

const mostrarModal = ref(false)

console.log(pacientes);


function goToPage(page) {
  router.get('/pacientes', { page }, { preserveState: true });
}

</script>
