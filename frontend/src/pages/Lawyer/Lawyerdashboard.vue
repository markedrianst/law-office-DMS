<template>
  <div class="p-6">

    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-3xl font-bold">Dashboard</h1>
    </div>

    <!-- Welcome message -->
    <p class="mb-6 text-gray-700">
      Welcome, <span class="font-semibold">{{ userName }}</span> ({{ userRole }})
    </p>

    <!-- Dashboard cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

      <div class="bg-white shadow rounded-xl p-6">
        <h2 class="font-semibold mb-2">Cases</h2>
        <p class="text-gray-600">Manage legal case records</p>
      </div>

      <div class="bg-white shadow rounded-xl p-6">
        <h2 class="font-semibold mb-2">Documents</h2>
        <p class="text-gray-600">Secure file storage</p>
      </div>

      <div class="bg-white shadow rounded-xl p-6">
        <h2 class="font-semibold mb-2">Schedules</h2>
        <p class="text-gray-600">Court & meeting calendar</p>
      </div>

    </div>

  </div>
</template>

<script>
import api from "../../services/api";

export default {
  data() {
    return {
      userName: "",
      userRole: "",
    };
  },
  mounted() {
    // Check if user is logged in
    const user = JSON.parse(localStorage.getItem("user"));

    if (!user) {
      // Not logged in, redirect to login
      this.$router.push("/");
    } else {
      this.userName = user.name;
      this.userRole = user.role;
    }
  },
  methods: {
    logout() {
      // Clear user info and token
      localStorage.removeItem("user");
      localStorage.removeItem("token");

      // Remove default Authorization header
      api.defaults.headers.common["Authorization"] = "";

      // Redirect to login page
      this.$router.push("/");
    },
  },
};
</script>
