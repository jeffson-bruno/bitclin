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
                <div class="bg-white overflow-x-auto shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">

                        <!-- Botão para abrir a modal -->
                        <button @click="mostrarModal = true" class="px-4 py-2 bg-blue-500 text-white rounded mb-4">
                            Cadastrar Novo Paciente
                        </button>

                        <!-- Verificando se a lista de pacientes está vazia -->
                        <div v-if="!pacientes.data || pacientes.data.length === 0">
                            <p class="text-red-500">Nenhum paciente encontrado.</p>
                        </div>
                        <!-- Tabela de pacientes -->
                        <table v-else class="min-w-full table-auto">
                            <thead>
                                <tr>
                                    <th class="px-4 py-2 w-1/4">Nome</th>
                                    <th class="px-4 py-2 w-1/6">CPF</th>
                                    <th class="px-4 py-2 w-1/6">Telefone</th>
                                    <th class="px-4 py-2 w-1/4">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="paciente in pacientes.data" :key="paciente.id">
                                    <td class="border px-4 py-2 w-1/4">{{ paciente.nome }}</td>
                                    <td class="border px-4 py-2 w-1/6">{{ mascaraCPF(paciente.cpf) }}</td>
                                    <td class="border px-4 py-2 w-1/6">{{ mascaraTelefone(paciente.telefone) }}</td>
                                    <td class="border px-4 py-2 w-1/4">
                                        <!--Ações-->
                                        <!--Imprimir Senha-->
                                        <div class="space-x-2">
                                        <button
                                            class="bg-green-500 text-white px-2 py-1 rounded text-sm"
                                            @click="abrirModalSenha(paciente)"
                                        >
                                            Gerar Senha
                                        </button>

                                           <!-- Novo: Imprimir Ficha -->
                                        <button @click="imprimirFicha(paciente.id)" title="Imprimir Ficha">
                                            📝
                                        </button>
                                    </div>

                                    </td>
                                </tr>
                            </tbody>
                        </table>
                     
                        <!-- Paginação -->
                         <div
                            v-if="pacientes.total > pacientes.per_page"
                            class="mt-4 flex justify-end items-center space-x-2"
                        >
                            <button
                                @click="goToPage(pacientes.current_page - 1)"
                                :disabled="pacientes.current_page === 1"
                                class="px-3 py-1 bg-gray-200 rounded disabled:opacity-50"
                            >
                                Anterior
                            </button>

                            <span class="text-sm text-gray-700">
                                Página {{ pacientes.current_page }} de {{ pacientes.last_page }}
                            </span>

                            <button
                                @click="goToPage(pacientes.current_page + 1)"
                                :disabled="pacientes.current_page === pacientes.last_page"
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

    <ModalSenha
    :mostrar="mostrarModalSenha"
    :tipo-inicial="tipoSenha"
    @confirmar="confirmarGeracaoSenha"
    @cancelar="mostrarModalSenha = false"
    />

</template>

<script setup>
import { Head } from '@inertiajs/vue3'
import { ref } from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import ModalCadastroPaciente from '@/Components/ModalCadastroPaciente.vue'
import ModalSenha from '@/Components/ModalSenha.vue' // nova importação
import { usePage, router } from '@inertiajs/vue3'
import axios from 'axios' // para requisição de geração de senha

// Pacientes vindos da página
const { pacientes } = usePage().props

console.log('Pacientes recebidos', pacientes)

// Modal de cadastro
const mostrarModal = ref(false)

// Modal de senha
const mostrarModalSenha = ref(false)
const pacienteSelecionado = ref(null)
const tipoSenha = ref('convencional')

// Função para ir para uma página específica (paginação)
function goToPage(page) {
  router.get('/pacientes', { page }, { replace: true })
}

// Máscara de CPF
const mascaraCPF = (value) => {
  if (!value) return ''
  value = value.replace(/\D/g, '')
  value = value.replace(/(\d{3})(\d)/, '$1.$2')
  value = value.replace(/(\d{3})(\d)/, '$1.$2')
  value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2')
  return value
}

// Máscara de telefone
const mascaraTelefone = (value) => {
  if (!value) return ''
  value = value.replace(/\D/g, '')
  if (value.length <= 10) {
    value = value.replace(/(\d{2})(\d)/, '($1) $2')
    value = value.replace(/(\d{4})(\d)/, '$1-$2')
  } else {
    value = value.replace(/(\d{2})(\d)/, '($1) $2')
    value = value.replace(/(\d{5})(\d)/, '$1-$2')
  }
  return value
}

//Função para abrir a modal da senha e definir paciente atual
function abrirModalSenha(paciente) {
  pacienteSelecionado.value = paciente
  tipoSenha.value = 'convencional'
  mostrarModalSenha.value = true
}

//Função chamada ao confirmar tipo da senha
async function confirmarGeracaoSenha(tipo) {
  tipoSenha.value = tipo

  try {
    const response = await axios.post('/senhas', {
      paciente_id: pacienteSelecionado.value.id,
      tipo: tipoSenha.value,
    })

    // Abrir nova aba com impressão da senha
    window.open(`/senhas/imprimir/${response.data.senha.id}`, '_blank')


    // Fecha modal
    mostrarModalSenha.value = false
  } catch (error) {
    console.error(error)
    alert('Erro ao gerar a senha.')
  }

}

function imprimirFicha(pacienteId) {
  window.open(`/pacientes/imprimir-ficha/${pacienteId}`, '_blank')

}
</script>
