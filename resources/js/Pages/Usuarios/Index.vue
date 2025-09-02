<template>
  <AuthenticatedLayout>
    <Head title="Usuários" />

    <div class="py-6 px-4 mx-auto max-w-7xl">
      <!-- Cabeçalho -->
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-center w-full">Usuários Cadastrados</h1>

        <button
          @click="toggleForm"
          class="flex items-center gap-2 px-5 py-2.5 bg-blue-600 text-white text-sm rounded hover:bg-blue-700 transition-all">
          <span class="text-lg">+</span> Cadastrar Usuário
        </button>
      </div>

      <!-- Formulário (collapse) -->
      <div v-if="showForm" class="relative mb-6 p-7 border rounded-lg bg-gray-50 shadow-sm">
        <!-- Fechar -->
        <button
          @click="fecharFormulario"
          class="absolute top-2 right-2 text-gray-500 hover:text-red-600 text-xl font-bold"
          title="Fechar formulário">&times;</button>

        <form @submit.prevent="submit" class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <!-- Nome -->
          <div>
            <label class="block mb-1 text-sm font-medium">Nome</label>
            <input v-model="form.name" type="text" class="w-full border rounded p-2" required />
            <p v-if="form.errors.name" class="text-red-600 text-sm mt-1">{{ form.errors.name }}</p>
          </div>

          <!-- Usuário -->
          <div>
            <label class="block mb-1 text-sm font-medium">Usuário</label>
            <input v-model="form.usuario" type="text" class="w-full border rounded p-2" required />
            <p v-if="form.errors.usuario" class="text-red-600 text-sm mt-1">{{ form.errors.usuario }}</p>
          </div>

          <!-- Senha -->
          <div class="relative">
            <label class="block mb-1 text-sm font-medium">Senha</label>
            <input
              :type="showPassword ? 'text' : 'password'"
              v-model="form.password"
              maxlength="6"
              class="w-full border rounded p-2 pr-10"
              :required="!editando" />
            <button type="button" @click="showPassword = !showPassword" class="absolute right-2 top-[35px] text-gray-500">
              <svg v-if="showPassword" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-5.523 0-10-4.477-10-10 0-1.34.26-2.62.725-3.825M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
              </svg>
              <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
              </svg>
            </button>
            <p v-if="form.errors.password" class="text-red-600 text-sm mt-1">{{ form.errors.password }}</p>
          </div>

          <!-- Confirmar senha -->
          <div class="relative">
            <label class="block mb-1 text-sm font-medium">Confirmar Senha</label>
            <input
              :type="showConfirm ? 'text' : 'password'"
              v-model="form.password_confirmation"
              maxlength="6"
              class="w-full border rounded p-2 pr-10"
              :required="!editando" />
            <button type="button" @click="showConfirm = !showConfirm" class="absolute right-2 top-[35px] text-gray-500">
              <svg v-if="showConfirm" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-5.523 0-10-4.477-10-10 0-1.34.26-2.62.725-3.825M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
              </svg>
              <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
              </svg>
            </button>
          </div>

          <!-- Função -->
          <div class="md:col-span-2">
            <label class="block mb-1 text-sm font-medium">Função</label>
            <select v-model="form.role" class="w-full border rounded p-2" required>
              <option disabled value="">Selecione...</option>
              <option value="admin">Administrador</option>
              <option value="receptionist">Recepcionista</option>
              <option value="doctor">Médico</option>
              <option value="enfermeiro">Enfermeiro(a)</option>
              <option value="psicologo">Psicólogo(a)</option>
            </select>
            <p v-if="form.errors.role" class="text-red-600 text-sm mt-1">{{ form.errors.role }}</p>
          </div>

          <!-- Especialidade (somente para médicos) -->
          <div v-if="isDoctor" class="md:col-span-2">
            <label class="block mb-1 text-sm font-medium mt-2">Especialidade</label>
            <select v-model="form.especialidade_id" class="w-full border rounded p-2" :required="isDoctor">
              <option disabled value="">Selecione...</option>
              <option v-for="esp in props.especialidades" :key="esp.id" :value="esp.id">{{ esp.nome }}</option>
            </select>
            <p v-if="form.errors.especialidade_id" class="text-red-600 text-sm mt-1">{{ form.errors.especialidade_id }}</p>
          </div>
          <!-- Registro profissional (somente para médicos) -->
          <template v-if="needsCouncil">
            <div>
              <label class="block mb-1 text-sm font-medium">Tipo de Registro</label>
              <!-- Mostra o tipo já definido pelo papel -->
              <input v-model="form.registro_tipo" class="w-full border rounded p-2 bg-gray-100" readonly />
              <p v-if="form.errors.registro_tipo" class="text-red-600 text-sm mt-1">{{ form.errors.registro_tipo }}</p>
            </div>

            <div>
              <label class="block mb-1 text-sm font-medium">Número</label>
              <input v-model="form.registro_numero" type="text" class="w-full border rounded p-2" :required="needsCouncil" />
              <p v-if="form.errors.registro_numero" class="text-red-600 text-sm mt-1">{{ form.errors.registro_numero }}</p>
            </div>

            <div>
              <label class="block mb-1 text-sm font-medium">UF</label>
              <select v-model="form.registro_uf" class="w-full border rounded p-2" :required="needsCouncil">
                <option disabled value="">UF</option>
                <option v-for="uf in UFs" :key="uf" :value="uf">{{ uf }}</option>
              </select>
              <p v-if="form.errors.registro_uf" class="text-red-600 text-sm mt-1">{{ form.errors.registro_uf }}</p>
            </div>
          </template>
          <!-- Botão Salvar -->
          <div class="md:col-span-2 flex justify-end mt-2">
            <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
              Salvar
            </button>
          </div>
        </form>
      </div>

      <!-- Modal: Ver dados -->
      <div v-if="selectedUser" class="fixed inset-0 z-50 bg-black bg-opacity-50 flex items-center justify-center">
        <div class="bg-white rounded-lg shadow-lg max-w-md w-full p-6">
          <h2 class="text-xl font-bold mb-4">Detalhes do Usuário</h2>

          <p><strong>Nome:</strong> {{ selectedUser.name }}</p>
          <p><strong>Usuário:</strong> {{ selectedUser.usuario }}</p>
          <p><strong>Função:</strong> {{ selectedUser.role }}</p>
          <p><strong>Cadastrado em:</strong> {{ new Date(selectedUser.created_at).toLocaleDateString() }}</p>

          <div class="mt-4 text-end">
            <button
              @click="selectedUser = null"
              class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">Fechar</button>
          </div>
        </div>
      </div>

      <!-- Tabela -->
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
          <tr v-for="user in usuarios" :key="user.id" class="text-center hover:bg-gray-50">
            <td class="p-2 border">{{ user.name }}</td>
            <td class="p-2 border">{{ user.usuario }}</td>
            <td class="p-2 border capitalize">{{ user.role }}</td>
            <td class="p-2 border">
              <div class="flex justify-center items-center gap-3">
                <!-- Ver -->
                <button @click="verUsuario(user)" class="text-gray-600 hover:text-yellow-600" title="Ver Dados">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                       viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                  </svg>
                </button>

                <!-- Editar -->
                <button @click="editarUsuario(user)" class="text-gray-600 hover:text-blue-600" title="Editar Usuário">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                       viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M11 5H6a2 2 0 00-2 2v12a2 2 0 002 2h12a2 2 0 002-2v-5m-5.586-9.414a2 2 0 112.828 2.828L11 15l-4 1 1-4 7.414-7.414z"/>
                  </svg>
                </button>

                <!-- Excluir -->
                <button @click="deleteUser(user.id)" class="text-gray-600 hover:text-red-600" title="Excluir Usuário">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                       viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3m-4 0h14"/>
                  </svg>
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </AuthenticatedLayout>

  <!-- Toast global -->
  <Toast ref="toastRef" :type="toastType" />
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { ref, watch, computed } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import Toast from '@/Components/Toast.vue'
import { Head } from '@inertiajs/vue3'

/* Props */
const props = defineProps({
  usuarios: Array,
  especialidades: Array
})

/* Listas auxiliares */
const UFs = [
  'AC','AL','AP','AM','BA','CE','DF','ES','GO','MA',
  'MT','MS','MG','PA','PB','PR','PE','PI','RJ','RN',
  'RS','RO','RR','SC','SP','SE','TO'
]
const tiposConselho = ['CRM','CRP','COREN','CREFITO','CRO','CRN','CRFa','CREFONO','CRBM']

/* Toast */
const toastRef = ref(null)
const toastType = ref('success')
const showToast = (msg, type = 'success') => {
  toastType.value = type
  toastRef.value?.showToast(msg)
}
const isHydrating = ref(false)


/* UI States */
const showForm = ref(false)
const editando = ref(false)
const usuarioEditandoId = ref(null)
const showPassword = ref(false)
const showConfirm = ref(false)

/* Collapse */
const toggleForm = () => (showForm.value = !showForm.value)
const fecharFormulario = () => {
  showForm.value = false
  editando.value = false
  usuarioEditandoId.value = null
  showPassword.value = false
  showConfirm.value = false
  form.reset()
}

/* Form */
const form = useForm({
  name: '',
  usuario: '',
  password: '',
  password_confirmation: '',
  role: '',
  especialidade_id: null,
  registro_tipo: '',
  registro_numero: '',
  registro_uf: '',
})

/* Regras dinâmicas de exibição */
const isDoctor = computed(() => form.role === 'doctor')
const needsCouncil = computed(() => ['doctor','enfermeiro','psicologo'].includes(form.role))
const councilLabel = computed(() => ({
  doctor: 'CRM',
  enfermeiro: 'COREN',
  psicologo: 'CRP'
}[form.role] ?? 'Registro'))

/* Limpa campos de médico quando muda a função */
watch(() => form.role, (val) => {
  if (isHydrating.value) return  // <- impede limpar durante a hidratação

  // Especialidade só para médico
  if (val !== 'doctor') {
    form.especialidade_id = null
  }
  // Campos de conselho só para doctor/enfermeiro/psicologo
  if (!['doctor','enfermeiro','psicologo'].includes(val)) {
    form.registro_tipo = ''
    form.registro_numero = ''
    form.registro_uf = ''
  } else {
    // Preenche automaticamente CRM/COREN/CRP
    form.registro_tipo = ({ doctor: 'CRM', enfermeiro: 'COREN', psicologo: 'CRP' }[val])
  }
})


/* Visualizar usuário */
const selectedUser = ref(null)
const verUsuario = (user) => (selectedUser.value = user)

/* Submit */
const submit = () => {
  if (editando.value && usuarioEditandoId.value) {
    form.put(route('usuarios.update', usuarioEditandoId.value), {
      preserveScroll: true,
      onSuccess: () => {
        showToast('Usuário atualizado com sucesso!', 'info')
        fecharFormulario()
      },
      onError: () => showToast('Erro ao atualizar usuário.', 'error')
    })
  } else {
    form.post(route('usuarios.store'), {
      preserveScroll: true,
      onSuccess: () => {
        showToast('Usuário cadastrado com sucesso!', 'success')
        fecharFormulario()
      },
      onError: () => showToast('Erro ao salvar usuário.', 'error')
    })
  }
}

/* Delete */
const deleteUser = (id) => {
  if (confirm('Tem certeza que deseja excluir este usuário?')) {
    router.delete(route('usuarios.destroy', id), {
      preserveScroll: true,
      onSuccess: () => showToast('Usuário excluído com sucesso!', 'error'),
      onError: () => showToast('Erro ao excluir usuário.', 'error')
    })
  }
}

/* Edit */
const editarUsuario = (usuario) => {
  showForm.value = true
  editando.value = true
  usuarioEditandoId.value = usuario.id

  // silencia o watch durante a hidratação
  isHydrating.value = true

  form.name = usuario.name ?? ''
  form.usuario = usuario.usuario ?? ''
  form.password = ''
  form.password_confirmation = ''

  // papel primeiro (watch está silenciado)
  form.role = usuario.role ?? ''

  // dependentes
  form.especialidade_id = usuario.especialidade_id ?? null
  form.registro_tipo    = usuario.registro_tipo ?? ''
  form.registro_numero  = usuario.registro_numero ?? ''
  form.registro_uf      = usuario.registro_uf ?? ''

  // fim da hidratação
  isHydrating.value = false

  // se precisa de conselho e não veio tipo, define automaticamente
  if (['doctor','enfermeiro','psicologo'].includes(form.role) && !form.registro_tipo) {
    form.registro_tipo = ({ doctor: 'CRM', enfermeiro: 'COREN', psicologo: 'CRP' }[form.role])
  }
}

</script>
