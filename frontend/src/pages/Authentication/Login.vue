<template>
  <div class="h-screen flex items-center justify-center bg-gradient-to-br from-slate-900 to-slate-700">
    <div class="bg-white w-96 p-8 rounded-xl shadow-lg">

      <h2 class="text-2xl font-bold text-center mb-6">Law Office Login</h2>

      <!-- Form wrapper -->
      <form @submit.prevent="login">

        <!-- Email -->
        <input
          v-model="email"
          type="email"
          placeholder="Email"
          class="border p-3 w-full mb-3 rounded"
          required
        />

        <!-- Password -->
        <input
          v-model="password"
          type="password"
          placeholder="Password"
          class="border p-3 w-full mb-3 rounded"
          required
        />

        <!-- Login Button -->
        <button
          type="submit"
          :disabled="loading"
          class="bg-slate-900 hover:bg-slate-800 text-white w-full p-3 rounded font-semibold"
        >
          {{ loading ? "Logging in..." : "Login" }}
        </button>

      </form>
    </div>
  </div>
</template>

<script>
import api from "@/services/api";
import Swal from "sweetalert2";

export default {
  data() {
    return {
      email: "",
      password: "",
      loading: false,
    };
  },
  methods: {
    async login() {
      this.loading = true;

      try {
        const response = await api.post("/login", {
          email: this.email,
          password: this.password,
        });

        // Save token & user
        localStorage.setItem("token", response.data.token);
        localStorage.setItem("user", JSON.stringify(response.data.user));

        // Set Axios Authorization header
        api.defaults.headers.common["Authorization"] = `Bearer ${response.data.token}`;

        // Redirect based on role
        const role = response.data.user.role;
        if (role === "admin") this.$router.push("/admindashboard");
        if (role === "staff") this.$router.push("/staffdashboard");
        if (role === "lawyer") this.$router.push("/lawyerdashboard");

        // Optional success alert
        Swal.fire({
          icon: "success",
          title: "Login Successful",
          text: `Welcome back, ${response.data.user.name}!`,
          timer: 1500,
          showConfirmButton: false,
        });

      } catch (err) {
        console.error(err);
        // Show SweetAlert2 error
        Swal.fire({
          icon: "error",
          title: "Login Failed",
          text: err.response?.data?.message || "Check your credentials",
        });
      } finally {
        this.loading = false;
      }
    },
  },
};
</script>
