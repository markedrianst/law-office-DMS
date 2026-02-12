<template>
  <div class="p-6 bg-gray-50 min-h-screen">
    <!-- Header -->
    <div class="mb-6 flex justify-between items-center">
      <div>
        <h1 class="text-3xl font-bold text-gray-900">Notifications</h1>
        <p class="text-sm text-gray-500">
          Latest hearing schedule updates
        </p>
      </div>

      <button
        @click="fetchNotifications"
        class="bg-blue-900 text-white px-4 py-2 rounded-lg shadow hover:bg-blue-800 transition flex items-center"
      >
        <i class="fa fa-refresh mr-2"></i>Refresh
      </button>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="text-center py-16">
      <div class="animate-spin h-12 w-12 border-b-2 border-blue-600 rounded-full mx-auto"></div>
      <p class="mt-4 text-gray-500">Loading notifications...</p>
    </div>

    <!-- Empty State -->
    <div v-else-if="notifications.length === 0" class="text-center py-16">
      <i class="fa fa-bell-slash text-5xl text-gray-300 mb-4"></i>
      <p class="text-gray-500">No notifications yet</p>
    </div>

    <!-- Notifications List -->
    <div v-else class="space-y-4">
      <div
        v-for="item in notifications"
        :key="item.id"
        class="bg-white rounded-xl shadow hover:shadow-md transition border-l-4 p-5 cursor-pointer"
        :class="statusBorder(item.status)"
        @click="viewSchedule(item)"
      >
        <div class="flex justify-between items-start">
          <div>
            <!-- Title -->
            <h2 class="font-semibold text-gray-900">
              {{ item.title }}
            </h2>

            <!-- Lawyer -->
            <p class="text-sm text-gray-500">
              Lawyer: {{ item.lawyer.first_name }} {{ item.lawyer.last_name }}
            </p>

            <!-- Date -->
            <p class="text-sm text-gray-500">
              {{ formatDateTime(item.hearing_date) }}
            </p>
          </div>

          <!-- Status Badge -->
          <span
            class="px-3 py-1 text-xs font-semibold rounded-full"
            :class="getStatusClass(item.status)"
          >
            {{ item.status }}
          </span>
        </div>

        <!-- Description -->
        <p class="mt-3 text-sm text-gray-600 line-clamp-2">
          {{ item.description || "No description" }}
        </p>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from "vue";
import api from "@/services/api";
import Swal from "sweetalert2";

export default {
  setup() {
    const notifications = ref([]);
    const loading = ref(false);

    const fetchNotifications = async () => {
      loading.value = true;
      try {
        const res = await api.get("/schedules");
        if (res.data.success) {
          // newest first
          notifications.value = res.data.data.reverse();
        }
      } catch (e) {
        Swal.fire("Error", "Failed to load notifications", "error");
      } finally {
        loading.value = false;
      }
    };

    const viewSchedule = (schedule) => {
      Swal.fire({
        title: schedule.title,
        html: `
          <b>Lawyer:</b> ${schedule.lawyer.first_name} ${schedule.lawyer.last_name}<br>
          <b>Date:</b> ${formatDateTime(schedule.hearing_date)}<br>
          <b>Status:</b> ${schedule.status}<br><br>
          ${schedule.description || ""}
        `,
        confirmButtonText: "Close"
      });
    };

    const formatDateTime = (date) => {
      const d = new Date(date);
      return d.toLocaleString("en-PH", {
        year: "numeric",
        month: "short",
        day: "numeric",
        hour: "2-digit",
        minute: "2-digit"
      });
    };

    const getStatusClass = (status) => {
      const map = {
        scheduled: "bg-blue-100 text-blue-800",
        completed: "bg-green-100 text-green-800",
        cancelled: "bg-red-100 text-red-800",
        rescheduled: "bg-yellow-100 text-yellow-800"
      };
      return map[status] || "bg-gray-100 text-gray-800";
    };

    const statusBorder = (status) => {
      const map = {
        scheduled: "border-blue-500",
        completed: "border-green-500",
        cancelled: "border-red-500",
        rescheduled: "border-yellow-500"
      };
      return map[status] || "border-gray-300";
    };

    onMounted(async () => {
      await fetchNotifications();
    });


    return {
      notifications,
      loading,
      fetchNotifications,
      viewSchedule,
      formatDateTime,
      getStatusClass,
      statusBorder
    };
  }
};
</script>
