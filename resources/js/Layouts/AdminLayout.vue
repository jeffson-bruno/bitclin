<script setup>
import { ref, watch } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import Toast from '@/Components/Toast.vue'

const page = usePage()
const user = page.props.auth.user

// Estado persistido via localStorage
const openCadastro = ref(JSON.parse(localStorage.getItem('openCadastro') ?? 'false'))
const openFinanceiro = ref(JSON.parse(localStorage.getItem('openFinanceiro') ?? 'false'))

watch(openCadastro, (val) => localStorage.setItem('openCadastro', JSON.stringify(val)))
watch(openFinanceiro, (val) => localStorage.setItem('openFinanceiro', JSON.stringify(val)))
</script>

<template>
  <div class="flex min-h-screen bg-gray-100">
    <!-- ============ SIDEBAR ============ -->
    <aside class="w-64 shrink-0 bg-white border-r border-gray-200 flex flex-col">
      <!-- Logo -->
      <div class="h-20 px-4 flex items-center justify-center border-b border-gray-200">
        <Link :href="route('admin.dashboard')">
          <img src="/images/bitclin_logo.png" alt="Logo da BitClin" class="h-44" />
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

        <!-- MONITOR -->
        <a
          :href="route('admin.monitor')"
          target="_blank"
          class="block px-3 py-2 rounded font-medium flex items-center gap-2 text-gray-700 hover:bg-gray-100"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
            viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path d="M4 5h16v10H4z"></path>
            <path d="M12 15v4M8 19h8" stroke-linecap="round" stroke-linejoin="round" />
          </svg>
          Ligar Monitor
        </a>


        <!-- ====== CADASTRO ====== -->
        <button
          class="w-full flex items-center justify-between px-3 py-2 rounded text-gray-700 hover:bg-gray-100"
          @click="openCadastro = !openCadastro">
          <span class="font-medium flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
                viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"></path>
            </svg>
            Cadastro
          </span>
          <svg :class="{'rotate-90': openCadastro}" class="h-4 w-4 transition-transform" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M6 6l6 4-6 4V6z" clip-rule="evenodd" />
          </svg>
        </button>

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
        </div>

        <!-- ========== FINANCEIRO ========== -->
        <button
          class="w-full flex items-center justify-between px-3 py-2 rounded text-gray-700 hover:bg-gray-100"
          @click="openFinanceiro = !openFinanceiro">
          <span class="font-medium flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
                viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-4 0-8 2-8 4s4 4 8 4 8-2 8-4-4-4-8-4z" />
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v8" />
              <path stroke-linecap="round" stroke-linejoin="round" d="M16 12H8" />
            </svg>
            Painel Financeiro
          </span>
          <svg :class="{'rotate-90': openFinanceiro}" class="h-4 w-4 transition-transform" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M6 6l6 4-6 4V6z" clip-rule="evenodd" />
          </svg>
        </button>

        <div v-show="openFinanceiro" class="ml-4 space-y-1">
          <Link
            :href="route('admin.financeiro.index')"
            :class="['block px-3 py-2 rounded', route().current('admin.financeiro.index') ? 'bg-gray-200 text-gray-900' : 'text-gray-700 hover:bg-gray-100']">
            Financeiro
          </Link>

          <Link 
            :href="route('admin.despesas.index')" 
            :class="['block px-3 py-2 rounded', route().current('admin.despesas.index') ? 'bg-gray-200 text-gray-900' : 'text-gray-700 hover:bg-gray-100']">
            Despesas
          </Link>
        </div>
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
