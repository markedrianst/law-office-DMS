<script setup>
import { computed } from "vue";
import { useRoute } from "vue-router";
import MainLayout from "@/layouts/Layout.vue";

const route = useRoute();

// Use layout for all pages except Login
const useLayout = computed(() => route.path !== "/");
</script>

<template>
  <MainLayout v-if="useLayout" />
  <router-view v-else />
</template>

