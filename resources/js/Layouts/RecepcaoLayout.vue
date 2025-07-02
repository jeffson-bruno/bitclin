<template>
  <div>
    <nav class="bg-white border-b p-4 flex justify-between items-center shadow">
      <div class="flex space-x-4">
        <Link href="/pacientes" class="text-blue-600 hover:underline">
          Cadastrar Pacientes
        </Link>
        <div class="relative group">
          <span class="text-blue-600 cursor-pointer">Agendamentos</span>
          <div class="absolute hidden group-hover:block bg-white border rounded shadow mt-2 p-2">
            <Link href="/agendamentos/consulta" class="block px-2 py-1 hover:bg-gray-100">
              Agendar Consulta
            </Link>
            <Link href="/agendamentos/exame" class="block px-2 py-1 hover:bg-gray-100">
              Agendar Exame
            </Link>
          </div>
        </div>
      </div>

      <div>
        <Link href="/logout" method="post" as="button" class="text-red-600 hover:underline">
          Sair
        </Link>
      </div>
    </nav>

    <main class="p-6">
      <slot />
    </main>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
</script>
