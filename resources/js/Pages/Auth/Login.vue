<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    usuario: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
  <Head title="Login" />

  <div class="min-h-screen flex flex-col items-center justify-center bg-gray-100 px-4">
    <!-- Logo -->
    <div class="mb-2">
      <img src="/images/logo.png" alt="Logo da Clínica" class="h-56" />
    </div>

    <!-- Formulário -->
    <div class="w-full max-w-md bg-white p-6 rounded shadow space-y-4 mt-2">
      <div v-if="status" class="text-sm font-medium text-green-600 text-center">
        {{ status }}
      </div>

      <form @submit.prevent="submit" class="space-y-4">
        <div>
          <InputLabel for="usuario" value="Usuário" />
          <TextInput
            id="usuario"
            type="text"
            v-model="form.usuario"
            required
            autofocus
            class="mt-1 block w-full"
          />
          <InputError :message="form.errors.usuario" class="mt-2" />
        </div>

        <div>
          <InputLabel for="password" value="Senha" />
          <TextInput
            id="password"
            type="password"
            v-model="form.password"
            required
            autocomplete="current-password"
            class="mt-1 block w-full"
          />
          <InputError class="mt-2" :message="form.errors.password" />
        </div>

        <div class="flex items-center justify-between">
          <label class="flex items-center text-sm">
            <Checkbox v-model:checked="form.remember" name="remember" />
            <span class="ml-2 text-gray-600">Lembrar-me</span>
          </label>
        </div>

        <div class="flex justify-end">
          <PrimaryButton
            class="w-full justify-center"
            :class="{ 'opacity-25': form.processing }"
            :disabled="form.processing"
          >
            ENTRAR
          </PrimaryButton>
        </div>
      </form>
    </div>
  </div>
</template>


