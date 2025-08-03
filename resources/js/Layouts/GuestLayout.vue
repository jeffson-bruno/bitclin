<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
  <div class="min-h-screen flex flex-col justify-center items-center bg-gray-100 py-4 px-6">
    <div class="mb-4">
      <slot name="logo">
        <ApplicationLogo class="h-60 w-auto" />
      </slot>
    </div>

    <div class="w-full max-w-md">
      <slot />
    </div>
  </div>
</template>
