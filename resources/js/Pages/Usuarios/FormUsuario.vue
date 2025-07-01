<template>
  <div class="bg-white border border-gray-300 rounded-xl p-6 mb-6 shadow">
    <h2 class="text-lg font-semibold mb-4">Novo Usuário</h2>
    <form @submit.prevent="submit">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium">Nome</label>
          <input v-model="form.name" type="text" class="w-full border rounded p-2" required>
        </div>
        <div>
          <label class="block text-sm font-medium">Usuário</label>
          <input v-model="form.usuario" type="text" class="w-full border rounded p-2" required>
        </div>
        <div>
          <label class="block text-sm font-medium">Senha</label>
          <input v-model="form.password" type="password" class="w-full border rounded p-2" required>
        </div>
        <div>
          <label class="block text-sm font-medium">Confirme a Senha</label>
          <input v-model="form.password_confirmation" type="password" class="w-full border rounded p-2" required>
        </div>
        <div>
          <label class="block text-sm font-medium">Função</label>
          <select v-model="form.role" class="w-full border rounded p-2" required>
            <option disabled value="">Selecione</option>
            <option value="admin">Administrador</option>
            <option value="receptionist">Recepcionista</option>
            <option value="doctor">Médico</option>
          </select>
        </div>
      </div>
      <div class="mt-4 flex justify-between">
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Salvar</button>
        <button type="button" @click="$emit('fechar')" class="text-gray-600 hover:text-red-600">Cancelar</button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'

const form = useForm({
  name: '',
  usuario: '',
  password: '',
  password_confirmation: '',
  role: ''
})

const submit = () => {
  form.post('/usuarios', {
    preserveScroll: true,
    onSuccess: () => {
      form.reset()
      emit('fechar')
    }
  })
}

defineEmits(['fechar'])
</script>
