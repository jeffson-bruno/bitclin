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

                                        <!--Reagendar-->
                                        <button 
                                          @click="abrirReagendamento(paciente)"
                                        >
                                          🔄
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
    <ModalCadastroPaciente
      v-if="mostrarModal"
      :medicos="page.props.medicos"
      :exames="page.props.exames"
      @close="mostrarModal = false"
    />

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

  <!-- Modal de Reagendamento -->
  <ModalReagendamento
  :show="mostrarModalReagendamento"
  :paciente="pacienteParaReagendar"
  @close="mostrarModalReagendamento = false"
  @reagendado="pacienteAtualizado"
/>


  <Toast ref="toastRef" :type="toastType" />



</template>

<script setup>
import { Head } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import { usePage, router } from '@inertiajs/vue3'
import axios from 'axios'

import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import ModalCadastroPaciente from '@/Components/ModalCadastroPaciente.vue'
import ModalSenha from '@/Components/ModalSenha.vue'
import ModalEditarPaciente from '@/Components/ModalEditarPaciente.vue'
import ConfirmDeleteModal from '@/Components/ConfirmDeleteModal.vue'
import SearchBar from '@/Components/SearchBar.vue'
import Toast from '@/Components/Toast.vue'
import ModalReagendamento from '@/Components/ModalReagendamento.vue'


/* -------- Toast -------- */
const toastRef  = ref(null)
const toastType = ref('success')
const showToast = (msg, type = 'success') => {
  toastType.value = type
  toastRef.value?.showToast(msg)
}

/* -------- Dados vindos do laravel -------- */
const page      = usePage()
const pacientes = ref(page.props.pacientes)

/* -------- Computeds -------- */
const listaPacientes = computed(() => pacientes.value?.data ?? [])

/* -------- Busca -------- */
const searchTerm = ref('')
const filtrarPacientes = (termo) => { searchTerm.value = termo }
const listaPacientesFiltrada = computed(() => {
  if (!searchTerm.value) return listaPacientes.value
  const termo = searchTerm.value.toLowerCase()
  return listaPacientes.value.filter(p =>
    p.nome.toLowerCase().includes(termo) || p.cpf.includes(termo)
  )
})

/* -------- Paginação -------- */
function goToPage(pageNum) {
  router.get('/pacientes', { page: pageNum }, { replace: true })
}

/* -------- Máscaras -------- */
const mascaraCPF = v => !v ? '' : v.replace(/\D/g,'')
  .replace(/(\d{3})(\d)/,'$1.$2')
  .replace(/(\d{3})(\d)/,'$1.$2')
  .replace(/(\d{3})(\d{1,2})$/,'$1-$2')

const mascaraTelefone = v => {
  if (!v) return ''
  v = v.replace(/\D/g,'')
  return v.length <= 10
    ? v.replace(/(\d{2})(\d)/,'($1) $2').replace(/(\d{4})(\d)/,'$1-$2')
    : v.replace(/(\d{2})(\d)/,'($1) $2').replace(/(\d{5})(\d)/,'$1-$2')
}

/* -------- Estados de modal -------- */
const mostrarModal       = ref(false)      // cadastro
const mostrarModalSenha  = ref(false)
const mostrarModalEditar = ref(false)
const modalAberta        = ref(false)
const mostrarModalReagendamento = ref(false)
const pacienteParaReagendar = ref(null)

/* Paciente selecionado (senha e delete) */
const pacienteSelecionado = ref(null)

/* Senha */
const tipoSenha = ref('convencional')
function abrirModalSenha(p) {
  pacienteSelecionado.value = p
  tipoSenha.value           = 'convencional'
  mostrarModalSenha.value   = true
}

/* Confirmar senha */
async function confirmarGeracaoSenha(tipo) {
  tipoSenha.value = tipo
  try {
    const { data } = await axios.post('/senhas', {
      paciente_id: pacienteSelecionado.value.id,
      tipo:        tipoSenha.value
    })
    window.open(`/senhas/imprimir/${data.senha.id}`, '_blank')
    mostrarModalSenha.value = false
  } catch (e) {
    showToast('Erro ao gerar senha.', 'error')
  }
}

/* Imprimir Ficha */
const imprimirFicha = id => window.open(`/pacientes/imprimir-ficha/${id}`, '_blank')

/* -------- Edição -------- */
const pacienteParaEditar = ref(null)
function editarPaciente(id) {
  const pac = listaPacientes.value.find(p => p.id === id)
  if (pac) {
    pacienteParaEditar.value = { ...pac }
    mostrarModalEditar.value = true
  }
}

/* Atualizar listagem */
async function buscarPacientes() {
  try {
    const { data } = await axios.get('/pacientes', { headers:{Accept:'application/json'} })
    pacientes.value = {
      data:         data.data,
      current_page: data.pagination.current_page,
      last_page:    data.pagination.last_page,
      per_page:     10,
      total:        data.pagination.total
    }
  } catch (e) {
    showToast('Erro ao buscar pacientes.', 'error')
  }
}

/* Recebe evento do modal de edição */
const pacienteAtualizado = () => {
  buscarPacientes()
  showToast('Paciente atualizado com sucesso!', 'info')
}

/* Recebe evento do modal de cadastro */
const pacienteCadastrado = () => {
  buscarPacientes()
  showToast('Paciente cadastrado com sucesso!', 'success')
}

/* -------- Reagendamento -------- */
function abrirReagendamento(paciente) {
  pacienteParaReagendar.value = paciente
  mostrarModalReagendamento.value = true
}

/* -------- Deleção -------- */
function abrirModal(p) {
  pacienteSelecionado.value = p
  modalAberta.value         = true
}

function deletarPaciente(pac) {
  router.delete(route('pacientes.destroy', pac.id), {
    onSuccess: () => {
      modalAberta.value = false
      buscarPacientes()
      showToast('Paciente excluído com sucesso!', 'error')
    },
    onError: () => showToast('Erro ao excluir paciente.', 'error')
  })
}
// Chama a função para buscar pacientes ao carregar o componente

//Abrir modal de confirmação de deleção
//const modalAberta = ref(false)



</script>
