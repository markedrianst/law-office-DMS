  <script setup>
  import { ref } from "vue";
  import api from "@/services/api";

  const first_name = ref("");
  const last_name = ref("");
  const middle_name = ref("");
  const email = ref("");
  const password = ref("");
  const role = ref("staff"); // default role
  const loading = ref(false);
  const message = ref("");
  const error = ref("");

  const register = async () => {
    loading.value = true;
    error.value = "";
    message.value = "";

    try {
      const res = await api.post("/register", {
        first_name: first_name.value,
        last_name: last_name.value,
        middle_name: middle_name.value,
        email: email.value,
        password: password.value,
        role: role.value, // send role to backend
      });

      message.value = "Registered successfully!";
      console.log(res.data);
    } catch (err) {
      error.value = err.response?.data?.message || "Registration failed";
    } finally {
      loading.value = false;
    }
  };
  </script>

  <template>
    <div class="min-h-screen flex items-center justify-center bg-gray-100">
      <div class="bg-white shadow-lg rounded-xl p-8 w-full max-w-md">
        <h2 class="text-2xl font-bold text-center mb-6">Create Account</h2>

        <form @submit.prevent="register" class="space-y-4">
          <!-- First Name -->
          <input v-model="first_name" type="text" placeholder="First Name"
            class="w-full border p-3 rounded-lg" />

          <input v-model="last_name" type="text" placeholder="Last Name"
            class="w-full border p-3 rounded-lg" />

          <input v-model="middle_name" type="text" placeholder="Middle Name (optional)"
            class="w-full border p-3 rounded-lg" />   

          <input v-model="email" type="email" placeholder="Email"
            class="w-full border p-3 rounded-lg" />

          <input v-model="password" type="password" placeholder="Password"
            class="w-full border p-3 rounded-lg" />

          <!-- Role selection -->
          <select v-model="role" class="w-full border p-3 rounded-lg">
            <option value="admin">Admin</option>
            <option value="lawyer">Lawyer</option>
            <option value="staff">Staff</option>
          </select>

          <button
            :disabled="loading"
            class="w-full bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700"
          >
            {{ loading ? "Registering..." : "Register" }}
          </button>
        </form>

        <p v-if="message" class="text-green-600 mt-4 text-center">
          {{ message }}
        </p>

        <p v-if="error" class="text-red-600 mt-4 text-center">
          {{ error }}
        </p>
      </div>
    </div>
  </template>
