<template>
  <header class="bg-white border-b shadow-sm">
    <div class="max-w-7xl mx-auto px-6 py-3">
      <nav class="flex items-center justify-between gap-6">
        <!-- Nav Item -->
        <RouterLink
          v-for="item in navItems"
          :key="item.name"
          :to="item.to"
          class="flex flex-col items-center text-slate-600 hover:text-blue-600 transition text-xs"
          active-class="text-blue-700 font-semibold"
        >
          <component :is="item.icon" class="w-7 h-7 mb-1" />
          {{ item.name }}
        </RouterLink>
      </nav>
    </div>
  </header>
</template>

<script setup>
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

const navItems = [
  { name: "Dashboard", to: "/dashboard", icon: Squares2X2Icon },
  { name: "Users", to: "/users", icon: UsersIcon },
  { name: "Reports", to: "/reports", icon: DocumentTextIcon },
  { name: "Clients", to: "/clients", icon: FolderIcon },
  { name: "Tasks", to: "/tasks", icon: ClipboardDocumentListIcon },
  { name: "Appointment", to: "/appointments", icon: ClockIcon },
  { name: "Notification", to: "/notifications", icon: BellIcon },
  { name: "Account", to: "/account", icon: Cog6ToothIcon },
  { name: "Logout", to: "/logout", icon: ArrowRightOnRectangleIcon },
];
</script>
