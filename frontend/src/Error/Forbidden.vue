<template>
  <div class="flex items-center justify-center h-screen bg-gray-100">
    <div class="text-center p-6 bg-white rounded shadow">
      <h1 class="text-6xl font-bold text-red-500">403</h1>
      <p class="text-2xl mt-4">You are not authorized to access this page.</p>
      <button
        @click="$router.push('/')"
        class="mt-6 px-6 py-2 bg-blue-700 text-white rounded hover:bg-blue-800"
      >
        Go Home
      </button>
    </div>
  </div>
</template>
