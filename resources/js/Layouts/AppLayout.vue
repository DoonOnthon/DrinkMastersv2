<template>
  <div>
    <nav class="bg-white border-b border-gray-200 shadow-md px-4 py-3 z-50 relative">
      <div class="max-w-7xl mx-auto flex justify-between items-center">
        <Link href="/" class="text-xl font-extrabold text-indigo-600 tracking-tight">DrinkMasters</Link>
        <div class="space-x-4 text-sm font-medium">
          <Link href="/" class="text-gray-700 hover:text-indigo-600">Home</Link>
          <Link href="/games" class="text-gray-700 hover:text-indigo-600">Games</Link>
          <Link href="/dashboard" class="text-gray-700 hover:text-indigo-600">Profile</Link>
        </div>
      </div>
    </nav>

    <main class="min-h-screen bg-gray-50 pt-6">
      <slot />
    </main>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
</script>
