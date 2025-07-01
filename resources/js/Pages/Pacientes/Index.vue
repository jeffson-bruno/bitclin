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

                        <!-- Barra de busca -->
                        <div class="flex justify-end mb-4">
                          <SearchBar @search="filtrarPacientes" />
                        </div>

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
                                <tr v-for="paciente in listaPacientesFiltrada" :key="paciente.id">
                                    <td class="border px-4 py-2 w-1/4">{{ paciente.nome }}</td>
                                    <td class="border px-4 py-2 w-1/6">{{ mascaraCPF(paciente.cpf) }}</td>
                                    <td class="border px-4 py-2 w-1/6">{{ mascaraTelefone(paciente.telefone) }}</td>
                                    <td class="border px-4 py-2 w-1/4">
                                        <!--Ações-->
                                        <!--Imprimir Senha-->
                                    <div class="flex space-x-2 items-center">
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

                                        <!-- Botão: Editar Paciente -->
                                            <button @click="editarPaciente(paciente.id)" class="hover:text-blue-700" title="Editar Paciente">
                                                <!-- Ícone de lápis -->
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M11 5H6a2 2 0 00-2 2v12a2 2 0 002 2h12a2 2 0 002-2v-5m-5.586-9.414a2 2 0 112.828 2.828L11 15l-4 1 1-4 7.414-7.414z" />
                                                </svg>
                                            </button>
                                        <!-- Fim Botão: Editar Paciente -->
                                        <!--Botão Deletar-->
                                                <svg @click="abrirModal(paciente)" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-500 cursor-pointer hover:text-red-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3m-4 0h14" />
                                                </svg>
                                        <!--Fim Botão Deletar-->
                                    </div>
                                    <!--Fim  Ações-->
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

    <ModalEditarPaciente 
    v-if="mostrarModalEditar" 
    :paciente="pacienteParaEditar" 
    @close="mostrarModalEditar = false" 
    @atualizar-lista="buscarPacientes" />

    <!-- Modal de confirmação de Deleção-->
  <ConfirmDeleteModal
    :show="modalAberta"
    :item="pacienteSelecionado"
    @cancel="modalAberta = false"
    @confirm="deletarPaciente"
  />


</template>

<script setup>
import { Head } from '@inertiajs/vue3'
import { ref,computed } from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import ModalCadastroPaciente from '@/Components/ModalCadastroPaciente.vue'
import ModalSenha from '@/Components/ModalSenha.vue' // nova importação
import { usePage, router } from '@inertiajs/vue3'
import axios from 'axios' // para requisição de geração de senha
import ModalEditarPaciente from '@/Components/ModalEditarPaciente.vue'
import ConfirmDeleteModal from '@/Components/ConfirmDeleteModal.vue' // Importando o componente de modal de confirmação
import SearchBar from '@/Components/SearchBar.vue'  // Importando o componente de busca

const page = usePage()

// Pacientes vindos da página
const pacientes = ref(page.props.pacientes)

const listaPacientes = computed(() => pacientes.value?.data ?? [])

const searchTerm = ref('')
//console.log('Pacientes recebidos', pacientes)

// Modal de cadastro
const mostrarModal = ref(false)

// Modal de senha
const mostrarModalSenha = ref(false)
const pacienteSelecionado = ref(null)
const tipoSenha = ref('convencional')

//Abri e fechar modal (edição de paciente) e armazenar o paciente selecionado
const mostrarModalEditar = ref(false)
const pacienteParaEditar = ref(null)


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

// Função para abrir a modal de edição de paciente
function editarPaciente(id) {
  const paciente = listaPacientes.value.find(p => p.id === id)
  if (paciente) {
    pacienteParaEditar.value = { ...paciente }
    mostrarModalEditar.value = true
  }
}

// Função para atualizar a lista de pacientes após edição
async function buscarPacientes() {
  try {
    const response = await axios.get('/pacientes', { headers: { Accept: 'application/json' } })
    pacientes.value = {
      data: response.data.data,
      current_page: response.data.pagination.current_page,
      last_page: response.data.pagination.last_page,
      per_page: 10, // se for fixo, ou pode pegar do response
      total: response.data.pagination.total
    }
  } catch (error) {
    console.error('Erro ao buscar pacientes:', error)
  }
}
// Chama a função para buscar pacientes ao carregar o componente

//Abrir modal de confirmação de deleção
const modalAberta = ref(false)

function abrirModal(paciente) {
  pacienteSelecionado.value = paciente
  modalAberta.value = true
}

//Deletar paciente
function deletarPaciente(paciente) {
  router.delete(route('pacientes.destroy', paciente.id), {
    onSuccess: () => {
      modalAberta.value = false
      buscarPacientes()  // Atualiza a lista após deletar
    },
    onError: () => {
      alert('Erro ao deletar paciente.')
    }
  })
}

// Função para filtrar pacientes

// Função para capturar o evento de busca do componente de busca
const filtrarPacientes = (termo) => {
  searchTerm.value = termo
}
// Computed para filtrar pacientes com base no termo de busca
const listaPacientesFiltrada = computed(() => {
  if (!searchTerm.value) {
    return listaPacientes.value
  }

  const termo = searchTerm.value.toLowerCase()
  return listaPacientes.value.filter(paciente => 
    paciente.nome.toLowerCase().includes(termo) || paciente.cpf.includes(termo)
  )
})



</script>
