<script setup>
import { ref } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import Toast from '@/Components/Toast.vue'

const page = usePage()
const user = page.props.auth.user

/* controla submenu "Cadastro" */
const openCadastro = ref(true)   // já vem aberto
</script>

<template>
  <div class="flex min-h-screen bg-gray-100">
    <!-- ============ SIDEBAR ============ -->
    <aside class="w-64 shrink-0 bg-white border-r border-gray-200 flex flex-col">
      <!-- Logo -->
      <div class="h-16 px-4 flex items-center border-b border-gray-200">
        <Link :href="route('admin.dashboard')" class="text-xl font-bold text-gray-800">
          BitClin
        </Link>
      </div>

      <!-- Menus -->
      <nav class="flex-1 overflow-y-auto p-4 space-y-2 text-sm">
        <!-- DASHBOARD -->
        <Link
          :href="route('admin.dashboard')"
          :class="['block px-3 py-2 rounded font-medium', route().current('admin.dashboard') ? 'bg-gray-200 text-gray-900' : 'text-gray-700 hover:bg-gray-100']">
          Dashboard
        </Link>

        <!-- ====== CADASTRO ====== -->
        <button
          class="w-full flex items-center justify-between px-3 py-2 rounded text-gray-700 hover:bg-gray-100"
          @click="openCadastro = !openCadastro">
          <span class="font-medium">Cadastro</span>
          <svg :class="{'rotate-90': openCadastro}" class="h-4 w-4 transition-transform" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M6 6l6 4-6 4V6z" clip-rule="evenodd" />
          </svg>
        </button>
<!-------------------------LINKS--------------------------------------------------------------->
        <div v-show="openCadastro" class="ml-4 space-y-1">
          <Link
            :href="route('pacientes.index')"
            :class="['block px-3 py-2 rounded', route().current('pacientes.*') ? 'bg-gray-200 text-gray-900' : 'text-gray-700 hover:bg-gray-100']">
            Pacientes
          </Link>

          <Link
            :href="route('usuarios.index')"
            :class="['block px-3 py-2 rounded', route().current('usuarios.*') ? 'bg-gray-200 text-gray-900' : 'text-gray-700 hover:bg-gray-100']">
            Usuários
          </Link>
        
          <Link
            :href="route('admin.especialidades.index')"
            :class="['block px-3 py-2 rounded', route().current('admin.especialidades.index') ? 'bg-gray-200 text-gray-900' : 'text-gray-700 hover:bg-gray-100']">
            Especialidades
         </Link>

         <Link 
         :href="route('admin.exames.index')"
         :class="['block px-3 py-2 rounded', route().current('admin.exames.index') ? 'bg-gray-200 text-gray-900' : 'text-gray-700 hover:bg-gray-100']">
            Exames
         </Link>

         <Link
          :href="route('admin.agenda-medica.index')"
          :class="['block px-3 py-2 rounded', route().current('admin.agenda-medica.index') ? 'bg-gray-200 text-gray-900' : 'text-gray-700 hover:bg-gray-100']">
          Agenda Médica
        </Link>
<!---------------------------------------------------------------------------------------->
        </div>
<!------======================= FINANCEIRO =============================------------------>
        <Link
          :href="route('admin.financeiro.index')"
          :class="['block px-3 py-2 rounded', route().current('admin.financeiro.index') ? 'bg-gray-200 text-gray-900' : 'text-gray-700 hover:bg-gray-100']">
          Financeiro
        </Link>
<!---------------------------------------------------------------------------------------->
      </nav>

      <!-- footer / usuário -->
      <div class="p-4 border-t border-gray-200 text-sm">
        <div class="font-medium text-gray-800">{{ user.name }}</div>
        <div class="text-gray-500">{{ user.email }}</div>
        <Link
          :href="route('logout')"
          method="post"
          as="button"
          class="mt-2 inline-block text-red-600 hover:underline">
          Sair
        </Link>
      </div>
    </aside>

    <!-- ============ CONTEÚDO ============ -->
    <main class="flex-1 p-6">
      <header v-if="$slots.header" class="mb-4">
        <h1 class="text-2xl font-semibold text-gray-800">
          <slot name="header" />
        </h1>
      </header>

      <slot />
    </main>

    <Toast />
  </div>
</template>
