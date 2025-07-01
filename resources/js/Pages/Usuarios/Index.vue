<template>
    <AuthenticatedLayout>
        <Head title="Usuários" />

        <div class="py-6 px-4 mx-auto max-w-7xl">
            <!-- Cabeçalho com botão -->
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-center w-full">Usuários Cadastrados</h1>

                <button
                    @click="toggleForm"
                    class="flex items-center gap-2 px-5 py-2.5 bg-blue-600 text-white text-sm rounded hover:bg-blue-700 transition-all"
                >
                    
                    <span class="text-lg">+</span> Cadastrar Usuário
                </button>
            </div>

            <!-- Collapse do formulário -->
            <div v-if="showForm" class="mb-6 p-4 border rounded-lg bg-gray-50 shadow-sm">
                <form @submit.prevent="submit" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block mb-1 text-sm font-medium">Nome</label>
                        <input v-model="form.name" type="text" class="w-full border rounded p-2" required />
                    </div>

                    <div>
                        <label class="block mb-1 text-sm font-medium">Usuário</label>
                        <input v-model="form.usuario" type="text" class="w-full border rounded p-2" required />
                    </div>

                    <div>
                        <label class="block mb-1 text-sm font-medium">Senha</label>
                        <input v-model="form.password" type="password" class="w-full border rounded p-2" required />
                    </div>

                    <div>
                        <label class="block mb-1 text-sm font-medium">Confirmar Senha</label>
                        <input v-model="form.password_confirmation" type="password" class="w-full border rounded p-2" required />
                    </div>

                    <div class="md:col-span-2">
                        <label class="block mb-1 text-sm font-medium">Função</label>
                        <select v-model="form.role" class="w-full border rounded p-2" required>
                            <option value="" disabled>Selecione...</option>
                            <option value="admin">Administrador</option>
                            <option value="receptionist">Recepcionista</option>
                            <option value="doctor">Médico</option>
                        </select>
                    </div>

                    <div class="md:col-span-2 flex justify-end mt-2">
                        <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                            Salvar
                        </button>
                    </div>
                </form>
            </div>

            <!-- Tabela de usuários -->
            <table class="w-full border-collapse border border-gray-300 rounded overflow-hidden shadow-sm">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="p-2 border">Nome</th>
                        <th class="p-2 border">Usuário</th>
                        <th class="p-2 border">Função</th>
                        <th class="p-2 border">Opções</th>
                    </tr>
                </thead>
                <tbody>
                    <!--Botões-->
                    <tr v-for="user in usuarios" :key="user.id" class="text-center hover:bg-gray-50">
                        <td class="p-2 border">{{ user.name }}</td>
                        <td class="p-2 border">{{ user.usuario }}</td>
                        <td class="p-2 border capitalize">{{ user.role }}</td>
                        <td class="p-2 border">
                            <div class="flex justify-center items-center gap-3">
                                <!-- Ver Dados -->
                                <button class="text-gray-600 hover:text-yellow-600" title="Ver Dados">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </button>

                                <!-- Editar -->
                                <button @click="editarUsuario(user)" class="text-gray-600 hover:text-blue-600" title="Editar Usuário">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M11 5H6a2 2 0 00-2 2v12a2 2 0 002 2h12a2 2 0 002-2v-5m-5.586-9.414a2 2 0 112.828 2.828L11 15l-4 1 1-4 7.414-7.414z" />
                                    </svg>
                                </button>

                                <!-- Excluir -->
                                <!-- Excluir -->
                                <button @click="deleteUser(user.id)" class="text-gray-600 hover:text-red-600" title="Excluir Usuário">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3m-4 0h14" />
                                    </svg>
                                </button>
                            </div>
                        </td>                       
                    </tr>
                </tbody>
            </table>
        </div>
    </AuthenticatedLayout>
</template>


<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { ref } from 'vue'
import { useForm, router } from '@inertiajs/vue3'

// Props vindas do controller
defineProps({
    usuarios: Array,
})

// Controle do formulário (exibir ou esconder)
const showForm = ref(false)
const toggleForm = () => showForm.value = !showForm.value

// Formulário do novo usuário
const form = useForm({
    name: '',
    usuario: '',
    password: '',
    password_confirmation: '',
    role: '',
})

const editando = ref(false)
const usuarioEditandoId = ref(null)


// Envio do formulário
const submit = () => {
    if (editando.value && usuarioEditandoId.value) {
        form.put(route('usuarios.update', usuarioEditandoId.value), {
            preserveScroll: true,
            onSuccess: () => {
                showForm.value = false
                editando.value = false
                usuarioEditandoId.value = null
                form.reset()
            },
            onError: () => {
                console.error('Erro ao atualizar usuário.')
            }
        });
    } else {
        form.post(route('usuarios.store'), {
            preserveScroll: true,
            onSuccess: () => {
                showForm.value = false
                form.reset()
            },
            onError: () => {
                console.error('Erro ao salvar usuário.')
            }
        });
    }
}


//Deletar Usuário
const deleteUser = (id) => {
    if (confirm('Tem certeza que deseja excluir este usuário?')) {
        router.delete(route('usuarios.destroy', id), {
            preserveScroll: true,
            onSuccess: () => {
                console.log('Usuário excluído com sucesso')
            },
            onError: () => {
                console.error('Erro ao excluir usuário')
            }
        });
    }
}

// Editar Usuário
const editarUsuario = (usuario) => {
    showForm.value = true
    editando.value = true
    usuarioEditandoId.value = usuario.id

    form.name = usuario.name
    form.usuario = usuario.usuario
    form.role = usuario.role
}


</script>
