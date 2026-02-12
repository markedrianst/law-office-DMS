<!-- src/views/Schedules.vue -->
<template>
  <div class="p-6 bg-gray-50 min-h-screen font-sans">
    <!-- Dashboard Header -->
    <div class="mb-6">
      <h1 class="text-3xl font-bold text-gray-900 mb-1">Lawyer Hearing Schedules</h1>
      <p class="text-sm text-gray-500">Manage and track all lawyer hearing schedules</p>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
      <StatCard title="Total Schedules" :count="stats.totalSchedules" icon="calendar" />
      <StatCard title="Scheduled" :count="stats.scheduled" icon="clock" />
      <StatCard title="Completed" :count="stats.completed" icon="check-circle" />
      <StatCard title="Cancelled" :count="stats.cancelled" icon="x-circle" />
    </div>

    <!-- Quick Actions -->
    <div class="mb-6 flex gap-3">
      <button @click="openCreateModal" class="bg-blue-900 text-white px-5 py-2 rounded-lg shadow hover:bg-blue-800 transition">
        <i class="fa fa-plus mr-2"></i>Create Schedule
      </button>
      <button @click="refreshData" class="bg-green-600 text-white px-5 py-2 rounded-lg shadow hover:bg-green-700 transition">
        <i class="fa fa-refresh mr-2"></i>Refresh
      </button>
    </div>

    <!-- Filters -->
    <div class="bg-white shadow-md rounded-xl overflow-hidden mb-6 p-6">
      <h2 class="text-xl font-semibold mb-4">Filters</h2>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Lawyer</label>
          <select
            v-model="filters.lawyer_id"
            @change="applyFilters"
            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
          >
            <option value="">All Lawyers</option>
            <option v-for="lawyer in lawyers" :key="lawyer.id" :value="lawyer.id">
              {{ lawyer.first_name }} {{ lawyer.last_name }}
            </option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
          <select
            v-model="filters.status"
            @change="applyFilters"
            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
          >
            <option value="">All Status</option>
            <option value="scheduled">Scheduled</option>
            <option value="completed">Completed</option>
            <option value="cancelled">Cancelled</option>
            <option value="rescheduled">Rescheduled</option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
          <input
            type="date"
            v-model="filters.start_date"
            @change="applyFilters"
            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
          <input
            type="date"
            v-model="filters.end_date"
            @change="applyFilters"
            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
          />
        </div>
      </div>
    </div>

    <!-- Schedules Table -->
    <div class="bg-white shadow-md rounded-xl overflow-hidden">
      <h2 class="text-xl font-semibold p-4 border-b">All Schedules</h2>
      
      <!-- Loading State -->
      <div v-if="loading" class="p-12 text-center">
        <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
        <p class="mt-4 text-gray-500">Loading schedules...</p>
      </div>

      <!-- Table -->
      <div v-else>
        <table class="w-full text-left">
          <thead class="bg-gray-100">
            <tr>
              <th class="p-3 font-medium">Date & Time</th>
              <th class="p-3 font-medium">Lawyer</th>
              <th class="p-3 font-medium">Title</th>
              <th class="p-3 font-medium">Case Number</th>
              <th class="p-3 font-medium">Location</th>
              <th class="p-3 font-medium">Status</th>
              <th class="p-3 font-medium">Created By</th>
              <th class="p-3 font-medium text-right">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="schedule in schedules" :key="schedule.id" class="border-t hover:bg-gray-50">
              <td class="p-3">{{ formatDateTime(schedule.hearing_date) }}</td>
              <td class="p-3">
                <div class="text-sm font-medium text-gray-900">
                  {{ schedule.lawyer.first_name }} {{ schedule.lawyer.last_name }}
                </div>
                <div class="text-sm text-gray-500">{{ schedule.lawyer.email }}</div>
              </td>
              <td class="p-3">{{ schedule.title }}</td>
              <td class="p-3 text-gray-500">{{ schedule.case_number || 'N/A' }}</td>
              <td class="p-3 text-gray-500">{{ schedule.court_location || 'N/A' }}</td>
              <td class="p-3">
                <span :class="getStatusClass(schedule.status)" class="px-3 py-1 text-xs font-semibold rounded-full">
                  {{ schedule.status }}
                </span>
              </td>
              <td class="p-3 text-gray-500">
                {{ schedule.creator.first_name }} {{ schedule.creator.last_name }}
              </td>
              <td class="p-3">
                <div class="flex justify-end gap-2">
                  <!-- View Button -->
                  <button
                    @click="viewSchedule(schedule)"
                    class="flex items-center justify-center w-10 h-10 rounded-lg bg-blue-100 text-blue-700 hover:bg-blue-200 hover:text-blue-900 transition"
                    title="View Schedule"
                  >
                    <i class="fa fa-eye"></i>
                  </button>

                  <!-- Edit Button -->
                  <button
                    @click="editSchedule(schedule)"
                    class="flex items-center justify-center w-10 h-10 rounded-lg bg-green-100 text-green-700 hover:bg-green-200 hover:text-green-900 transition"
                    title="Edit Schedule"
                  >
                    <i class="fa fa-edit"></i>
                  </button>

                  <!-- Delete Button -->
                  <button
                    @click="handleDelete(schedule.id)"
                    class="flex items-center justify-center w-10 h-10 rounded-lg bg-red-100 text-red-700 hover:bg-red-200 hover:text-red-900 transition"
                    title="Delete Schedule"
                  >
                    <i class="fa fa-trash"></i>
                  </button>
                </div>
              </td>

            </tr>
            <tr v-if="schedules.length === 0">
              <td colspan="8" class="p-3 text-center text-gray-400">No schedules found.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- CREATE/EDIT MODAL -->
    <div v-if="showFormModal" class="fixed inset-0 z-50 overflow-y-auto bg-gray-900 bg-opacity-50" @click.self="closeFormModal">
      <div class="flex items-center justify-center min-h-screen px-4 py-8">
        <div class="bg-white rounded-xl shadow-md w-full max-w-3xl overflow-hidden">
          <form @submit.prevent="handleSubmit">
            <!-- Header -->
            <div class="bg-blue-900 px-6 py-4 flex items-center justify-between">
              <h3 class="text-xl font-semibold text-white">
                <i class="fa fa-calendar mr-2"></i>
                {{ isEdit ? 'Edit Schedule' : 'Create New Schedule' }}
              </h3>
              <button type="button" @click="closeFormModal" class="text-white hover:text-gray-200 transition">
                <i class="fa fa-times text-2xl"></i>
              </button>
            </div>

            <!-- Body -->
            <div class="px-6 py-6 max-h-[70vh] overflow-y-auto">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Lawyer -->
                <div class="col-span-2">
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fa fa-user-tie mr-1"></i>Lawyer <span class="text-red-500">*</span>
                  </label>
                  <select
                    v-model="form.lawyer_id"
                    required
                    class="block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  >
                    <option value="">Select Lawyer</option>
                    <option v-for="lawyer in lawyers" :key="lawyer.id" :value="lawyer.id">
                      {{ lawyer.first_name }} {{ lawyer.last_name }}
                    </option>
                  </select>
                </div>

                <!-- Title -->
                <div class="col-span-2">
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fa fa-heading mr-1"></i>Title <span class="text-red-500">*</span>
                  </label>
                  <input
                    type="text"
                    v-model="form.title"
                    required
                    placeholder="Enter hearing title"
                    class="block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  />
                </div>

                <!-- Case Number -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fa fa-file-text mr-1"></i>Case Number
                  </label>
                  <input
                    type="text"
                    v-model="form.case_number"
                    placeholder="Enter case number"
                    class="block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  />
                </div>

                <!-- Court Location -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fa fa-map-marker mr-1"></i>Court Location
                  </label>
                  <input
                    type="text"
                    v-model="form.court_location"
                    placeholder="Enter court location"
                    class="block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  />
                </div>

                <!-- Hearing Date -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fa fa-calendar-check mr-1"></i>Hearing Date & Time <span class="text-red-500">*</span>
                  </label>
                  <input
                    type="datetime-local"
                    v-model="form.hearing_date"
                    required
                    class="block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  />
                </div>

                <!-- Hearing End Date -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fa fa-calendar-times mr-1"></i>Hearing End Date & Time
                  </label>
                  <input
                    type="datetime-local"
                    v-model="form.hearing_end_date"
                    class="block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  />
                </div>

                <!-- Status -->
                <div class="col-span-2">
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fa fa-info-circle mr-1"></i>Status
                  </label>
                  <select
                    v-model="form.status"
                    class="block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  >
                    <option value="scheduled">Scheduled</option>
                    <option value="completed">Completed</option>
                    <option value="cancelled">Cancelled</option>
                    <option value="rescheduled">Rescheduled</option>
                  </select>
                </div>

                <!-- Description -->
                <div class="col-span-2">
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fa fa-align-left mr-1"></i>Description
                  </label>
                  <textarea
                    v-model="form.description"
                    rows="3"
                    placeholder="Enter hearing description"
                    class="block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  ></textarea>
                </div>

                <!-- Notes -->
                <div class="col-span-2">
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fa fa-sticky-note mr-1"></i>Notes
                  </label>
                  <textarea
                    v-model="form.notes"
                    rows="3"
                    placeholder="Additional notes"
                    class="block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  ></textarea>
                </div>
              </div>
            </div>

            <!-- Footer -->
            <div class="bg-gray-50 px-6 py-4 flex justify-end gap-3 border-t">
              <button
                type="button"
                @click="closeFormModal"
                class="px-5 py-2 bg-gray-500 text-white rounded-lg shadow hover:bg-gray-600 transition"
              >
                <i class="fa fa-times mr-2"></i>Cancel
              </button>
              <button
                type="submit"
                :disabled="formLoading"
                class="px-5 py-2 bg-blue-900 text-white rounded-lg shadow hover:bg-blue-800 transition disabled:bg-gray-400 disabled:cursor-not-allowed"
              >
                <i v-if="!formLoading" class="fa mr-2" :class="isEdit ? 'fa-save' : 'fa-plus'"></i>
                <i v-if="formLoading" class="fa fa-spinner fa-spin mr-2"></i>
                {{ formLoading ? 'Saving...' : (isEdit ? 'Update Schedule' : 'Create Schedule') }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- VIEW MODAL -->
    <div v-if="showViewModal" class="fixed inset-0 z-50 overflow-y-auto bg-gray-900 bg-opacity-50" @click.self="showViewModal = false">
      <div class="flex items-center justify-center min-h-screen px-4 py-8">
        <div class="bg-white rounded-xl shadow-md w-full max-w-3xl overflow-hidden">
          <!-- Header -->
          <div class="bg-gradient-to-r from-blue-900 to-blue-800 px-6 py-4 flex items-center justify-between">
            <h3 class="text-xl font-semibold text-white">
              <i class="fa fa-eye mr-2"></i>Schedule Details
            </h3>
            <button @click="showViewModal = false" class="text-white hover:text-gray-200 transition">
              <i class="fa fa-times text-2xl"></i>
            </button>
          </div>

          <!-- Body -->
          <div class="px-6 py-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- Lawyer Info -->
              <div class="bg-blue-50 p-4 rounded-lg">
                <label class="text-xs font-semibold text-gray-600 uppercase tracking-wide flex items-center mb-2">
                  <i class="fa fa-user-tie mr-2"></i>Lawyer
                </label>
                <p class="text-sm font-medium text-gray-900">
                  {{ selectedSchedule.lawyer.first_name }} {{ selectedSchedule.lawyer.last_name }}
                </p>
                <p class="text-xs text-gray-500">{{ selectedSchedule.lawyer.email }}</p>
              </div>

              <!-- Status -->
              <div class="bg-gray-50 p-4 rounded-lg">
                <label class="text-xs font-semibold text-gray-600 uppercase tracking-wide flex items-center mb-2">
                  <i class="fa fa-info-circle mr-2"></i>Status
                </label>
                <span :class="getStatusClass(selectedSchedule.status)" class="inline-flex px-3 py-1 text-xs font-semibold rounded-full">
                  {{ selectedSchedule.status }}
                </span>
              </div>

              <!-- Title -->
              <div class="col-span-2 bg-gray-50 p-4 rounded-lg">
                <label class="text-xs font-semibold text-gray-600 uppercase tracking-wide flex items-center mb-2">
                  <i class="fa fa-heading mr-2"></i>Title
                </label>
                <p class="text-sm text-gray-900 font-medium">{{ selectedSchedule.title }}</p>
              </div>

              <!-- Case Number -->
              <div class="bg-gray-50 p-4 rounded-lg">
                <label class="text-xs font-semibold text-gray-600 uppercase tracking-wide flex items-center mb-2">
                  <i class="fa fa-file-text mr-2"></i>Case Number
                </label>
                <p class="text-sm text-gray-900">{{ selectedSchedule.case_number || 'N/A' }}</p>
              </div>

              <!-- Court Location -->
              <div class="bg-gray-50 p-4 rounded-lg">
                <label class="text-xs font-semibold text-gray-600 uppercase tracking-wide flex items-center mb-2">
                  <i class="fa fa-map-marker mr-2"></i>Court Location
                </label>
                <p class="text-sm text-gray-900">{{ selectedSchedule.court_location || 'N/A' }}</p>
              </div>

              <!-- Hearing Date -->
              <div class="bg-green-50 p-4 rounded-lg">
                <label class="text-xs font-semibold text-gray-600 uppercase tracking-wide flex items-center mb-2">
                  <i class="fa fa-calendar-check mr-2"></i>Hearing Date
                </label>
                <p class="text-sm text-gray-900 font-medium">{{ formatDateTime(selectedSchedule.hearing_date) }}</p>
              </div>

              <!-- Hearing End Date -->
              <div class="bg-red-50 p-4 rounded-lg">
                <label class="text-xs font-semibold text-gray-600 uppercase tracking-wide flex items-center mb-2">
                  <i class="fa fa-calendar-times mr-2"></i>Hearing End Date
                </label>
                <p class="text-sm text-gray-900">
                  {{ selectedSchedule.hearing_end_date ? formatDateTime(selectedSchedule.hearing_end_date) : 'N/A' }}
                </p>
              </div>

              <!-- Created By -->
              <div class="col-span-2 bg-yellow-50 p-4 rounded-lg">
                <label class="text-xs font-semibold text-gray-600 uppercase tracking-wide flex items-center mb-2">
                  <i class="fa fa-user mr-2"></i>Created By
                </label>
                <p class="text-sm text-gray-900 font-medium">
                  {{ selectedSchedule.creator.first_name }} {{ selectedSchedule.creator.last_name }}
                </p>
              </div>

              <!-- Description -->
              <div class="col-span-2 bg-gray-50 p-4 rounded-lg">
                <label class="text-xs font-semibold text-gray-600 uppercase tracking-wide flex items-center mb-2">
                  <i class="fa fa-align-left mr-2"></i>Description
                </label>
                <p class="text-sm text-gray-900">{{ selectedSchedule.description || 'No description provided' }}</p>
              </div>

              <!-- Notes -->
              <div class="col-span-2 bg-gray-50 p-4 rounded-lg">
                <label class="text-xs font-semibold text-gray-600 uppercase tracking-wide flex items-center mb-2">
                  <i class="fa fa-sticky-note mr-2"></i>Notes
                </label>
                <p class="text-sm text-gray-900">{{ selectedSchedule.notes || 'No notes' }}</p>
              </div>
            </div>
          </div>

          <!-- Footer -->
          <div class="bg-gray-50 px-6 py-4 flex justify-end gap-3 border-t">
            <button
              @click="editFromView"
              class="px-5 py-2 bg-green-600 text-white rounded-lg shadow hover:bg-green-700 transition"
            >
              <i class="fa fa-edit mr-2"></i>Edit Schedule
            </button>
            <button
              @click="showViewModal = false"
              class="px-5 py-2 bg-gray-500 text-white rounded-lg shadow hover:bg-gray-600 transition"
            >
              <i class="fa fa-times mr-2"></i>Close
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, computed } from "vue";
import api from "@/services/api";
import Swal from "sweetalert2";


export const scheduleApi = {
  // Get all schedules with optional filters
  getSchedules(params = {}) {
    return api.get('/schedules', { params });
  },

  // Get single schedule
  getSchedule(id) {
    return api.get(`/schedules/${id}`);
  },

  // Create new schedule
  createSchedule(data) {
    return api.post('/schedules', data);
  },

  // Update schedule
  updateSchedule(id, data) {
    return api.put(`/schedules/${id}`, data);
  },

  // Delete schedule
  deleteSchedule(id) {
    return api.delete(`/schedules/${id}`);
  },

  // Get all lawyers
  getLawyers() {
    return api.get('/lawyers');
  }
};
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
    const schedules = ref([]);
    const lawyers = ref([]);
    const loading = ref(false);
    const formLoading = ref(false);
    const showFormModal = ref(false);
    const showViewModal = ref(false);
    const selectedSchedule = ref(null);
    const isEdit = ref(false);

    const filters = ref({
      lawyer_id: "",
      status: "",
      start_date: "",
      end_date: ""
    });

    const form = ref({
      lawyer_id: "",
      title: "",
      description: "",
      case_number: "",
      court_location: "",
      hearing_date: "",
      hearing_end_date: "",
      status: "scheduled",
      notes: ""
    });

    // Computed stats
    const stats = computed(() => ({
      totalSchedules: schedules.value.length,
      scheduled: schedules.value.filter(s => s.status === "scheduled").length,
      completed: schedules.value.filter(s => s.status === "completed").length,
      cancelled: schedules.value.filter(s => s.status === "cancelled").length
    }));

    const fetchSchedules = async () => {
      loading.value = true;
      try {
        const res = await api.get("/schedules", { params: filters.value });
        if (res.data.success) {
          schedules.value = res.data.data;
        }
      } catch (e) {
        Swal.fire("Error", e.response?.data?.message || "Failed to fetch schedules", "error");
      } finally {
        loading.value = false;
      }
    };

    const fetchLawyers = async () => {
      try {
        const res = await api.get("/lawyers");
        if (res.data.success) {
          lawyers.value = res.data.data;
        }
      } catch (e) {
        Swal.fire("Error", e.response?.data?.message || "Failed to fetch lawyers", "error");
      }
    };

    const applyFilters = () => {
      fetchSchedules();
    };

    const refreshData = () => {
      filters.value = {
        lawyer_id: "",
        status: "",
        start_date: "",
        end_date: ""
      };
      fetchSchedules();
    };

    const openCreateModal = () => {
      form.value = {
        lawyer_id: "",
        title: "",
        description: "",
        case_number: "",
        court_location: "",
        hearing_date: "",
        hearing_end_date: "",
        status: "scheduled",
        notes: ""
      };
      selectedSchedule.value = null;
      isEdit.value = false;
      showFormModal.value = true;
    };

    const viewSchedule = (schedule) => {
      selectedSchedule.value = schedule;
      showViewModal.value = true;
    };

    const editSchedule = (schedule) => {
      selectedSchedule.value = { ...schedule };
      form.value = {
        lawyer_id: schedule.lawyer_id,
        title: schedule.title,
        description: schedule.description || "",
        case_number: schedule.case_number || "",
        court_location: schedule.court_location || "",
        hearing_date: formatDateForInput(schedule.hearing_date),
        hearing_end_date: schedule.hearing_end_date ? formatDateForInput(schedule.hearing_end_date) : "",
        status: schedule.status,
        notes: schedule.notes || ""
      };
      isEdit.value = true;
      showFormModal.value = true;
    };

    const editFromView = () => {
      showViewModal.value = false;
      editSchedule(selectedSchedule.value);
    };

    const formatDateForInput = (dateString) => {
      const date = new Date(dateString);
      const year = date.getFullYear();
      const month = String(date.getMonth() + 1).padStart(2, "0");
      const day = String(date.getDate()).padStart(2, "0");
      const hours = String(date.getHours()).padStart(2, "0");
      const minutes = String(date.getMinutes()).padStart(2, "0");
      return `${year}-${month}-${day}T${hours}:${minutes}`;
    };

    const handleSubmit = async () => {
      formLoading.value = true;
      try {
        let res;
        if (isEdit.value) {
          res = await api.put(`/schedules/${selectedSchedule.value.id}`, form.value);
        } else {
          res = await api.post("/schedules", form.value);
        }

        if (res.data.success) {
          Swal.fire({
            icon: "success",
            title: "Success!",
            text: res.data.message,
            timer: 2000,
            showConfirmButton: false
          });
          closeFormModal();
          fetchSchedules();
        }
      } catch (e) {
        console.error("Error saving schedule:", e);
        const message = e.response?.data?.message || "Failed to save schedule";
        Swal.fire("Error", message, "error");
      } finally {
        formLoading.value = false;
      }
    };

    const handleDelete = async (id) => {
      const result = await Swal.fire({
        title: "Are you sure?",
        text: "You won't be able to revert this!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#3085d6",
        confirmButtonText: "Yes, delete it!"
      });

      if (result.isConfirmed) {
        try {
          const res = await api.delete(`/schedules/${id}`);
          if (res.data.success) {
            Swal.fire("Deleted!", "Schedule has been deleted.", "success");
            fetchSchedules();
          }
        } catch (e) {
          Swal.fire("Error", e.response?.data?.message || "Failed to delete schedule", "error");
        }
      }
    };

    const closeFormModal = () => {
      showFormModal.value = false;
      selectedSchedule.value = null;
      isEdit.value = false;
    };
const formatDateTime = (dateTime) => {
  if (!dateTime) return "";

  const d = new Date(dateTime);

  return d.toLocaleString("en-PH", {
    year: "numeric",
    month: "2-digit",
    day: "2-digit",
    hour: "2-digit",
    minute: "2-digit",
    hour12: true
  });
};


    const getStatusClass = (status) => {
      const classes = {
        scheduled: "bg-blue-100 text-blue-800",
        completed: "bg-green-100 text-green-800",
        cancelled: "bg-red-100 text-red-800",
        rescheduled: "bg-yellow-100 text-yellow-800"
      };
      return classes[status] || "bg-gray-100 text-gray-800";
    };

    onMounted(() => {
      fetchSchedules();
      fetchLawyers();
    });

    return {
      schedules,
      lawyers,
      loading,
      formLoading,
      showFormModal,
      showViewModal,
      selectedSchedule,
      isEdit,
      filters,
      form,
      stats,
      applyFilters,
      refreshData,
      openCreateModal,
      viewSchedule,
      editSchedule,
      editFromView,
      handleDelete,
      closeFormModal,
      handleSubmit,
      formatDateTime,
      getStatusClass
    };
  }
};
</script>