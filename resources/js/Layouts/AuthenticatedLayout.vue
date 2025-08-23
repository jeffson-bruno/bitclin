<script setup>
import { ref, computed, onMounted } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'

/* ---------- componentes ---------- */
import ApplicationLogo   from '@/Components/ApplicationLogo.vue'
import Dropdown          from '@/Components/Dropdown.vue'
import DropdownLink      from '@/Components/DropdownLink.vue'
import NavLink           from '@/Components/NavLink.vue'
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue'
import Toast             from '@/Components/Toast.vue'

/* ---------- estado interno ---------- */
const showingNavigationDropdown = ref(false)
const toastRef = ref(null)

/* Exponha toast global p/ JS e outros componentes */
onMounted(() => {
  window.$toast = (msg, type = 'success') => {
    toastRef.value?.showToast(msg, type)
  }
})

/* ---------- dados do usuário ---------- */
const page = usePage()
const user  = page.props.auth.user ?? {}
const roles = user.roles ?? []          // array vindo do share()

/* ---------- helpers ---------- */
const isAdmin        = computed(() => roles.includes('admin'))
const isReceptionist = computed(() => roles.includes('receptionist'))
const isDoctor       = computed(() => roles.includes('doctor'))

</script>

<template>
  <div class="min-h-screen bg-gray-100">
    <!-- ===== NAVBAR ===== -->
    <nav class="bg-white border-b border-gray-100">
      <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 justify-between">

          <!-- LOGO -->
          <div class="flex items-center">
            <Link :href="route('dashboard')" class="flex items-center">
              <ApplicationLogo class="block h-24 w-auto fill-current text-gray-800" />
            </Link>
          </div>

          <!-- DESKTOP MENU -->
          <div class="hidden sm:flex sm:items-center sm:space-x-8">

            <!-- Dashboard (todos) -->
            <NavLink 
            :href="isReceptionist ? route('recepcao.dashboard') : isDoctor ? route('medico.dashboard') : route('dashboard')"
            :active="isReceptionist ? route().current('recepcao.dashboard') : isDoctor ? route().current('medico.dashboard') : route().current('dashboard')"
            >
              Dashboard
            </NavLink>
            <!-- NOVO LINK RECEPÇÃO -->
            <NavLink
              v-if="isReceptionist"
              :href="route('recepcao.consultas')"
              :active="route().current('recepcao.consultas')"
            >
              Datas de Consultas
            </NavLink>

            <!-- CADASTRO -->
            <Dropdown v-if="isAdmin || isReceptionist" align="left" width="48">
              <template #trigger>
                <button class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-gray-700">
                  Cadastro
                  <svg class="h-4 w-4 ml-1" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                  </svg>
                </button>
              </template>
              <template #content>
                <DropdownLink :href="route('pacientes.index')">Pacientes</DropdownLink>

                <!-- Usuários somente para admin -->
                <DropdownLink v-if="isAdmin" :href="route('usuarios.index')">
                  Usuários
                </DropdownLink>
              </template>
            </Dropdown>

            <!-- AGENDAMENTOS -->
            <!-- AGENDAMENTOS (apenas para Admin) -->
            <Dropdown v-if="isAdmin" align="left" width="56">
            <template #trigger>
                <button class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-gray-700">
                Agendamentos
                <svg class="h-4 w-4 ml-1" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                </svg>
                </button>
            </template>
            <template #content>
                <!---<DropdownLink :href="route('consultas.index')">Consultas</DropdownLink>-->
                <!---<DropdownLink :href="route('exames.index')">Exames</DropdownLink>-->
            </template>
            </Dropdown>


            <!-- MENU MÉDICO (exemplo) -->
            <NavLink
              v-if="isDoctor && !isAdmin && !isReceptionist"
              href="/minhas-consultas"
            >
              Minhas Consultas
            </NavLink>

            <!-- ===== USER DROPDOWN ===== -->
            <Dropdown align="right" width="48">
              <template #trigger>
                <button class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700">
                  {{ user.name }}
                  <svg class="h-4 w-4 ml-1" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                  </svg>
                </button>
              </template>
              <template #content>
               <!-- <DropdownLink :href="route('profile.edit')">Profile</DropdownLink> -->
                <DropdownLink :href="route('logout')" method="post" as="button">
                  Sair
                </DropdownLink>
              </template>
            </Dropdown>
          </div>

          <!-- MOBILE HAMBURGER -->
          <div class="-mr-2 flex items-center sm:hidden">
            <button
              @click="showingNavigationDropdown = !showingNavigationDropdown"
              class="p-2 rounded-md text-gray-400 hover:bg-gray-100 hover:text-gray-500 focus:outline-none"
            >
              <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path
                  v-if="!showingNavigationDropdown"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M4 6h16M4 12h16M4 18h16"
                />
                <path
                  v-else
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M6 18L18 6M6 6l12 12"
                />
              </svg>
            </button>
          </div>
        </div>
      </div>

      <!-- ===== MOBILE MENU ===== -->
      <div v-show="showingNavigationDropdown" class="sm:hidden">
        <div class="space-y-1 pt-2 pb-3">
          <ResponsiveNavLink :href="route('dashboard')" :active="route().current('dashboard')">
            Dashboard
          </ResponsiveNavLink>

          <ResponsiveNavLink
            v-if="isAdmin || isReceptionist"
            :href="route('pacientes.index')"
          >
            Pacientes
          </ResponsiveNavLink>

          <ResponsiveNavLink
            v-if="isReceptionist"
            :href="route('recepcao.consultas')"
            :active="route().current('recepcao.consultas')"
          >
            Datas de Consultas
          </ResponsiveNavLink>

          <ResponsiveNavLink
            v-if="isAdmin"
            :href="route('usuarios.index')"
          >
            Usuários
          </ResponsiveNavLink>
        </div>

        <!-- Mobile user info -->
        <div class="border-t border-gray-200 pt-4 pb-1">
          <div class="px-4 text-base font-medium text-gray-800">{{ user.name }}</div>
          <div class="px-4 text-sm font-medium text-gray-500">{{ user.email }}</div>

          <div class="mt-3 space-y-1">
            <ResponsiveNavLink :href="route('profile.edit')">Profile</ResponsiveNavLink>
            <ResponsiveNavLink :href="route('logout')" method="post" as="button">
              Sair
            </ResponsiveNavLink>
          </div>
        </div>
      </div>
    </nav>

    <!-- ==== PAGE HEADER SLOT ==== -->
    <header v-if="$slots.header" class="bg-white shadow">
      <div class="mx-auto max-w-7xl py-6 px-4 sm:px-6 lg:px-8">
        <slot name="header" />
      </div>
    </header>

    <!-- ==== MAIN CONTENT ==== -->
    <main>
      <slot />
    </main>
  </div>

  <!-- TOAST -->
  <Toast ref="toastRef" />
</template>
