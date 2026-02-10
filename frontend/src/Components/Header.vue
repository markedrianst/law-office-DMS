<template>
  <header class="bg-white border-b shadow-sm">
    <div class="max-w-7xl mx-auto px-6 py-3">
      <nav class="flex items-center justify-between gap-6">

        <!-- Loop through nav items -->
        <template v-for="item in navItems" :key="item.name">

          <!-- Regular links -->
          <RouterLink
            v-if="item.name !== 'Logout'"
            :to="item.to"
            class="flex flex-col items-center text-slate-500 text-xs transition"
            :class="{ 'text-blue-700 font-semibold': $route.path === item.to }"
          >
            <component
              :is="item.icon"
              class="w-7 h-7 mb-1 transition-colors"
              :class="{ 'text-blue-700': $route.path === item.to }"
            />
            {{ item.name }}
          </RouterLink>
          <button
            v-else
            @click="logout"
            class="flex flex-col items-center text-slate-500 text-xs transition hover:text-red-500"
          >
            <component :is="item.icon" class="w-7 h-7 mb-1 transition-colors" />
            Logout
          </button>

        </template>

      </nav>
    </div>
  </header>
</template>

<script setup>
import { ref, computed } from "vue";
import { useRouter } from "vue-router";
import Swal from "sweetalert2";
import api from "@/services/api"; // make sure you import it
import {
  Squares2X2Icon,
  UsersIcon,
  DocumentTextIcon,
  FolderIcon,
  ClipboardDocumentListIcon,
  ClockIcon,
  BellIcon,
  Cog6ToothIcon,
  ArrowRightOnRectangleIcon,
} from "@heroicons/vue/24/solid";

// Router
const router = useRouter();

// Reactive user from localStorage
const user = ref(JSON.parse(localStorage.getItem("user") || "{}"));

// Computed role
const role = computed(() => user.value.role || "guest");

// Reactive nav items based on role
const navItems = computed(() => {
  if (role.value === "admin") {
    return [
      { name: "Dashboard", to: "/admin/admindashboard", icon: Squares2X2Icon },
      { name: "Users", to: "/admin/manageusers", icon: UsersIcon },
      { name: "Activity Logs", to: "/admin/activitylogs", icon: ClipboardDocumentListIcon },
      { name: "Documents", to: "/admin/documents", icon: DocumentTextIcon },
      { name: "Schedules", to: "/admin/schedules", icon: ClockIcon },
      { name: "Notifications", to: "/admin/notifications", icon: BellIcon },
      { name: "Account", to: "/admin/account", icon: Cog6ToothIcon },
      { name: "Logout", icon: ArrowRightOnRectangleIcon },
    ];
  } else if (role.value === "staff") {
    return [
      { name: "Dashboard", to: "/staffdashboard", icon: Squares2X2Icon },
      { name: "Clients", to: "/clients", icon: UsersIcon },
      { name: "Documents", to: "/documents", icon: DocumentTextIcon },
      { name: "Notifications", to: "/notifications", icon: BellIcon },
      { name: "Settings", to: "/settings", icon: Cog6ToothIcon },
      { name: "Logout", icon: ArrowRightOnRectangleIcon },
    ];
  } else if (role.value === "lawyer") {
    return [
      { name: "Dashboard", to: "/lawyerdashboard", icon: Squares2X2Icon },
      { name: "Cases", to: "/cases", icon: UsersIcon },
      { name: "Documents", to: "/documents", icon: DocumentTextIcon },
      { name: "Schedules", to: "/schedules", icon: ClockIcon },
      { name: "Notifications", to: "/notifications", icon: BellIcon },
      { name: "Account", to: "/account", icon: Cog6ToothIcon },
      { name: "Logout", icon: ArrowRightOnRectangleIcon },
    ];
  } else {
    // guest or not logged in
    return [
      { name: "Dashboard", to: "/", icon: Squares2X2Icon },
      { name: "Logout", icon: ArrowRightOnRectangleIcon },
    ];
  }
});
async function logout() {
  const result = await Swal.fire({
    title: "Are you sure?",
    text: "You will be logged out from the system.",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#3085d6",
    cancelButtonColor: "#d33",
    confirmButtonText: "Yes",
    cancelButtonText: "Cancel",
  });

  if (!result.isConfirmed) return;

  try {
    await api.post("/logout");
  } catch (e) {
    console.error("Logout failed", e);
  }

  // Clear storage
  localStorage.removeItem("token");
  localStorage.removeItem("user");
  user.value = {};

  // Redirect
  router.push("/");
}



</script>
