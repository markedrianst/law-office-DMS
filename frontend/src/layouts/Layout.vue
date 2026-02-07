<script setup>
import Header from "@/Components/Header.vue";
import Footer from "@/Components/Footer.vue";
</script>

<template>
  <div class="min-h-screen flex flex-col">
    <Header />

    <main class="flex-1 p-6">
      <router-view />
    </main>

    <Footer />
  </div>
</template>
