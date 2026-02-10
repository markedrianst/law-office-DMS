<template>
  <div class="p-6 bg-gray-50 min-h-screen font-sans">
    <!-- Header -->
    <div class="mb-6">
      <h1 class="text-3xl font-bold text-gray-900 mb-1">User Management</h1>
      <p class="text-sm text-gray-500">Admin Panel &gt; Users</p>
    </div>

    <!-- Add User Button -->
    <div class="flex justify-end mb-4">
      <button @click="openModal()" class="flex items-center gap-2 bg-blue-900 text-white px-5 py-2 rounded-lg shadow hover:bg-blue-800 transition">
        Add User
      </button>
    </div>

    <!-- Users Table -->
    <div class="bg-white shadow-md rounded-xl overflow-hidden">
      <table class="w-full table-auto">
        <thead class="bg-blue-50">
          <tr>
            <th class="p-3 text-left text-gray-700 font-medium">Name</th>
            <th class="p-3 text-left text-gray-700 font-medium">Email</th>
            <th class="p-3 text-left text-gray-700 font-medium">Role</th>
            <th class="p-3 text-left text-gray-700 font-medium">Status</th>
            <th class="p-3 text-left text-gray-700 font-medium">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="user in users" :key="user.id" class="border-b hover:bg-blue-50 transition">
            <td class="p-3 font-medium">{{ user.first_name }} {{ user.middle_name }} {{ user.last_name }}</td>
            <td class="p-3">{{ user.email }}</td>
            <td class="p-3">
              <span class="px-3 py-1 rounded-full text-white text-sm font-semibold shadow-sm"
                    :class="{
                      'bg-blue-700': user.role?.role === 'admin',
                      'bg-green-600': user.role?.role === 'lawyer',
                      'bg-gray-500': user.role?.role === 'staff'
                    }">
                {{ user.role?.role }}
              </span>
            </td>
            <td class="p-3">
              <span :class="{
                'text-green-600 font-semibold': user.status === 'active',
                'text-red-600 font-semibold': user.status === 'inactive'
              }">
                {{ user.status }}
              </span>
            </td>
            <td class="p-3 flex gap-2">
              <button @click="openModal(user)" class="flex items-center gap-1 bg-yellow-500 text-white px-3 py-1 rounded-lg hover:bg-yellow-600 transition">Edit</button>
              <button
                @click="confirmDelete(user)"
                class="flex items-center gap-1 bg-red-600 text-white px-3 py-1 rounded-lg hover:bg-red-700 transition"
                :disabled="user.role?.role === 'admin'"
                :class="{ 'opacity-50 cursor-not-allowed': user.role?.role === 'admin' }"
              >
                Delete
              </button>
            </td>
          </tr>
          <tr v-if="users.length === 0">
            <td colspan="5" class="p-3 text-center text-gray-400">No users found.</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Add/Edit User Modal -->
    <div v-if="showModal" @keydown.enter="saveUser" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-xl shadow-xl w-full max-w-md p-6">
        <h2 class="text-2xl font-bold mb-4 border-b pb-2">{{ isEditing ? "Edit User" : "Add New User" }}</h2>

        <form class="space-y-3">
          <div>
            <input v-model="form.first_name" placeholder="First Name" class="border p-2 w-full rounded focus:ring-2 focus:ring-blue-600" />
            <p v-if="errors.first_name" class="text-red-500 text-sm mt-1">{{ errors.first_name }}</p>
          </div>
          <div>
            <input v-model="form.middle_name" placeholder="Middle Name" class="border p-2 w-full rounded focus:ring-2 focus:ring-blue-600" />
          </div>
          <div>
            <input v-model="form.last_name" placeholder="Last Name" class="border p-2 w-full rounded focus:ring-2 focus:ring-blue-600" />
            <p v-if="errors.last_name" class="text-red-500 text-sm mt-1">{{ errors.last_name }}</p>
          </div>
          <div>
            <input v-model="form.email" placeholder="Email" class="border p-2 w-full rounded focus:ring-2 focus:ring-blue-600" />
            <p v-if="errors.email" class="text-red-500 text-sm mt-1">{{ errors.email }}</p>
          </div>
          <div>
            <input v-model="form.password" type="password" placeholder="Password (leave blank to keep)" class="border p-2 w-full rounded focus:ring-2 focus:ring-blue-600" />
            <p v-if="errors.password" class="text-red-500 text-sm mt-1">{{ errors.password }}</p>
          </div>
          <div>
            <select v-model="form.role" class="border p-2 w-full rounded focus:ring-2 focus:ring-blue-600">
              <option disabled value="">Select role</option>
              <option>admin</option>
              <option>lawyer</option>
              <option>staff</option>
            </select>
            <p v-if="errors.role" class="text-red-500 text-sm mt-1">{{ errors.role }}</p>
          </div>
          <div>
            <select v-model="form.status" class="border p-2 w-full rounded focus:ring-2 focus:ring-blue-600">
              <option>active</option>
              <option>inactive</option>
            </select>
          </div>
        </form>

        <div class="flex justify-end gap-3 mt-6">
          <button @click="closeModal" type="button" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400 transition">Cancel</button>
          <button @click="saveUser" type="button" class="px-4 py-2 bg-blue-900 text-white rounded hover:bg-blue-800 transition">Save</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import api from "@/services/api";
import Swal from "sweetalert2";

export default {
  data() {
    return {
      users: [],
      showModal: false,
      isEditing: false,
      editId: null,
      form: {
        first_name: "",
        last_name: "",
        middle_name: "",
        email: "",
        password: "",
        role: "",
        status: "active",
      },
      errors: {},
    };
  },
  mounted() {
    this.fetchUsers();
  },
  methods: {
    async fetchUsers() {
      try {
        const res = await api.get("/users");
        this.users = res.data.sort((a, b) => (a.role?.role === "admin" && b.role?.role !== "admin" ? -1 : b.role?.role === "admin" && a.role?.role !== "admin" ? 1 : 0));
      } catch (e) {
        Swal.fire("Error", e.response?.data?.message || "Failed to fetch users.", "error");
      }
    },

    openModal(user = null) {
      this.isEditing = !!user;
      this.editId = user?.id || null;
      this.showModal = true;
      this.errors = {};

      this.form = {
        first_name: user?.first_name || "",
        middle_name: user?.middle_name || "",
        last_name: user?.last_name || "",
        email: user?.email || "",
        password: "",
        role: user?.role?.role || "",
        status: user?.status || "active",
      };
    },

    closeModal() {
      this.showModal = false;
      this.errors = {};
    },

    validateForm() {
      this.errors = {};
      if (!this.form.first_name) this.errors.first_name = "First name is required.";
      if (!this.form.last_name) this.errors.last_name = "Last name is required.";
      if (!this.form.email) this.errors.email = "Email is required.";
      else if (!/^\S+@\S+\.\S+$/.test(this.form.email)) this.errors.email = "Invalid email.";
      if (!this.isEditing && (!this.form.password || this.form.password.length < 6)) this.errors.password = "Password must be at least 6 characters.";
      if (!this.form.role) this.errors.role = "Role is required.";

      return Object.keys(this.errors).length === 0;
    },

    async saveUser() {
      if (!this.validateForm()) return;

      try {
        if (this.isEditing) await api.put(`/users/${this.editId}`, this.form);
        else await api.post("/addusers", this.form);
        Swal.fire("Success", "User saved successfully.", "success");
        this.closeModal();
        this.fetchUsers();
      } catch (e) {
        Swal.fire("Error", e.response?.data?.message || "Failed to save user.", "error");
      }
    },

    confirmDelete(user) {
      Swal.fire({
        title: "Confirm Delete",
        text: `Are you sure you want to delete ${user.first_name} ${user.last_name}?`,
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#3085d6",
        confirmButtonText: "Yes, delete it!",
      }).then(async (result) => {
        if (result.isConfirmed) {
          try {
            const res = await api.delete(`/users/${user.id}`);
            this.users = this.users.filter(u => u.id !== user.id);
            Swal.fire("Deleted!", res.data.message, "success");
          } catch (e) {
            Swal.fire("Error", e.response?.data?.message || "Failed to delete user.", "error");
          }
        }
      });
    },
  },
};
</script>
