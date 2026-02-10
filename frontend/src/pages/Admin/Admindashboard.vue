<template>
  <div class="p-6 bg-gray-50 min-h-screen font-sans">
    <!-- Dashboard Header -->
    <div class="mb-6">
      <h1 class="text-3xl font-bold text-gray-900 mb-1">Admin Dashboard</h1>
      <p class="text-sm text-gray-500">Overview of users, activity, and schedules</p>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 lg:grid-cols-5 gap-4 mb-6">
      <StatCard title="Total Users" :count="stats.totalUsers" icon="users" />
      <StatCard title="Active Users" :count="stats.activeUsers" icon="user-check" />
      <StatCard title="Inactive Users" :count="stats.inactiveUsers" icon="user-x" />
      <StatCard title="Activity Logs" :count="stats.totalLogs" icon="activity" />
      <StatCard title="New Users This Month" :count="stats.newUsers" icon="user-plus" />
    </div>

    <!-- Quick Actions -->
    <div class="mb-6 flex gap-3">
      <button @click="goToAddUser" class="bg-blue-900 text-white px-5 py-2 rounded-lg shadow hover:bg-blue-800 transition">Add User</button>
      <button @click="goToUsers" class="bg-green-600 text-white px-5 py-2 rounded-lg shadow hover:bg-green-700 transition">View Users</button>
      <button @click="goToLogs" class="bg-yellow-500 text-white px-5 py-2 rounded-lg shadow hover:bg-yellow-600 transition">View Activity Logs</button>
    </div>

    <!-- Main Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Recent Activity Logs -->
      <div class="bg-white shadow-md rounded-xl overflow-hidden">
        <h2 class="text-xl font-semibold p-4 border-b">Recent Activity Logs</h2>
        <table class="w-full text-left">
          <thead class="bg-gray-100">
            <tr>
              <th class="p-3 font-medium">Date</th>
              <th class="p-3 font-medium">User</th>
              <th class="p-3 font-medium">Action</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="log in recentLogs" :key="log.id" class="border-t hover:bg-gray-50">
              <td class="p-3">{{ formatDateTime(log.created_at) }}</td>
              <td class="p-3">{{ log.user?.first_name }} {{ log.user?.last_name }}</td>
              <td class="p-3">{{ log.action }}</td>
            </tr>
            <tr v-if="recentLogs.length === 0">
              <td colspan="3" class="p-3 text-center text-gray-400">No activity logs found.</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Schedules -->
      <div class="bg-white shadow-md rounded-xl overflow-hidden">
        <h2 class="text-xl font-semibold p-4 border-b">Upcoming Schedules</h2>
        <ul class="divide-y">
          <li v-for="schedule in schedules" :key="schedule.id" class="p-4 hover:bg-gray-50 flex justify-between">
            <div>   
              <p class="font-medium">{{ schedule.title }}</p>
              <p class="text-sm text-gray-500">{{ formatDateTime(schedule.start_time) }} - {{ formatDateTime(schedule.end_time) }}</p>
            </div>
            <span :class="schedule.status === 'completed' ? 'text-green-600 font-semibold' : 'text-red-600 font-semibold'">{{ schedule.status }}</span>
          </li>
          <li v-if="schedules.length === 0" class="p-4 text-center text-gray-400">No schedules found.</li>
        </ul>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from "vue";
import { useRouter } from "vue-router"; // << add this
import api from "@/services/api";
import Swal from "sweetalert2";

// Simple stat card component
const StatCard = {
  props: ["title", "count", "icon"],
  template: `
    <div class="bg-white shadow rounded-lg p-4 flex items-center gap-4">
      <div class="text-3xl text-blue-700"><i :class="'fa fa-' + icon"></i></div>
      <div>
        <p class="text-gray-500">{{ title }}</p>
        <p class="text-2xl font-bold">{{ count }}</p>
      </div>
    </div>
  `
};

export default {
  components: { StatCard },
  setup() {
    const router = useRouter(); // << initialize router

    const users = ref([]);
    const recentLogs = ref([]);
    const schedules = ref([]);
    const stats = ref({
      totalUsers: 0,
      activeUsers: 0,
      inactiveUsers: 0,
      totalLogs: 0,
      newUsers: 0
    });

    const fetchUsers = async () => {
      try {
        const res = await api.get("/users");
        users.value = res.data;
        stats.value.totalUsers = users.value.length;
        stats.value.activeUsers = users.value.filter(u => u.status === "active").length;
        stats.value.inactiveUsers = users.value.filter(u => u.status === "inactive").length;
        stats.value.newUsers = users.value.filter(u => {
          const d = new Date(u.created_at);
          const now = new Date();
          return d.getMonth() === now.getMonth() && d.getFullYear() === now.getFullYear();
        }).length;
      } catch (e) {
        Swal.fire("Error", e.response?.data?.message || "Failed to fetch users", "error");
      }
    };

    const fetchLogs = async () => {
      try {
        const res = await api.get("/activity-logs");
        recentLogs.value = res.data.sort((a,b)=>new Date(b.created_at)-new Date(a.created_at)).slice(0, 5);
        stats.value.totalLogs = res.data.length;
      } catch (e) {
        Swal.fire("Error", e.response?.data?.message || "Failed to fetch logs", "error");
      }
    };

    const fetchSchedules = async () => {
      try {
        const res = await api.get("/scheduled");
        if (res.data.success) {
          schedules.value = res.data.data;
        }
      } catch (e) {
        Swal.fire("Error", e.response?.data?.message || "Failed to fetch schedules", "error");
      }
    };

    onMounted(() => {
      fetchUsers();
      fetchLogs();
      fetchSchedules();
    });

    const formatDateTime = (dateTime) => {
      if (!dateTime) return '';
      const d = new Date(dateTime);
      return d.toLocaleString("en-US", { month:"2-digit", day:"2-digit", year:"numeric", hour:"2-digit", minute:"2-digit", hour12:true });
    };

    // Corrected navigation functions
    const goToAddUser = () => { router.push("/admin/manageusers"); };
    const goToUsers = () => { router.push("/admin/manageusers"); };
    const goToLogs = () => { router.push("/admin/activitylogs"); };

    return { users, recentLogs, schedules, stats, formatDateTime, goToAddUser, goToUsers, goToLogs };
  }
};

</script>

<style scoped>
/* optional styling */
</style>
