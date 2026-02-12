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
          <label class="block text-sm font-medium text-gray-700 mb-1">View Mode</label>
          <select
            v-model="viewMode"
            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
          >
            <option value="calendar">Calendar View</option>
            <option value="table">Table View</option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Month</label>
          <div class="flex gap-2">
            <input
              type="month"
              v-model="selectedMonth"
              @change="fetchSchedules"
              class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
            />
          </div>
        </div>
      </div>
    </div>

    <!-- Calendar View -->
    <div v-if="viewMode === 'calendar'" class="bg-white shadow-md rounded-xl overflow-hidden">
      <!-- Calendar Header with Month and Year -->
      <div class="bg-gradient-to-r from-blue-900 to-blue-800 px-6 py-5">
        <div class="flex items-center justify-between">
          <div>
            <h2 class="text-3xl font-bold text-white mb-1">
              {{ currentMonthName }} {{ currentYear }}
            </h2>
            <p class="text-blue-100 text-sm">
              <i class="fa fa-calendar mr-2"></i>
              {{ getSchedulesForMonth().length }} schedules this month
            </p>
          </div>
          <div class="flex gap-2">
            <button 
              @click="previousMonth" 
              class="p-3 bg-blue-800 hover:bg-blue-700 text-white rounded-lg transition"
              title="Previous Month"
            >
              <i class="fa fa-chevron-left"></i>
            </button>
            <button 
              @click="goToToday" 
              class="px-5 py-3 bg-white text-blue-900 rounded-lg hover:bg-gray-100 transition font-medium"
            >
              <i class="fa fa-calendar-check-o mr-2"></i>Today
            </button>
            <button 
              @click="nextMonth" 
              class="p-3 bg-blue-800 hover:bg-blue-700 text-white rounded-lg transition"
              title="Next Month"
            >
              <i class="fa fa-chevron-right"></i>
            </button>
          </div>
        </div>
      </div>

      <!-- Month Stats -->
      <div class="grid grid-cols-7 gap-px bg-gray-200">
        <div 
          v-for="(stat, index) in monthStats" 
          :key="index"
          class="bg-white p-3 text-center"
        >
          <span class="text-xs text-gray-500 uppercase">{{ weekDays[index] }}</span>
          <p class="text-lg font-semibold text-gray-800">{{ stat }}</p>
        </div>
      </div>

      <!-- Calendar Grid -->
      <div class="p-6">
        <!-- Day Headers -->
        <div class="grid grid-cols-7 gap-2 mb-4">
          <div 
            v-for="day in weekDays" 
            :key="day" 
            class="text-center font-semibold text-gray-600 uppercase text-sm"
          >
            {{ day }}
          </div>
        </div>

        <!-- Calendar Days -->
        <div class="grid grid-cols-7 gap-2">
          <div
            v-for="(day, index) in calendarDays"
            :key="index"
            class="min-h-[140px] p-2 border rounded-lg transition-all relative group"
            :class="{
              'bg-gray-50': !day.isCurrentMonth,
              'border-blue-400 bg-blue-50 ring-2 ring-blue-200': day.isToday,
              'hover:shadow-md hover:border-blue-300 hover:bg-blue-50/50': true,
              'cursor-pointer': !isPastDate(day.date),
              'opacity-60 cursor-not-allowed': isPastDate(day.date) && !day.isToday
            }"
            @click="!isPastDate(day.date) ? openCreateModalWithDate(day.date) : showPastDateAlert()"
          >
            <div class="flex justify-between items-start mb-2">
              <span 
                class="text-sm font-medium w-7 h-7 flex items-center justify-center rounded-full"
                :class="{
                  'text-gray-400': !day.isCurrentMonth,
                  'bg-blue-600 text-white': day.isToday,
                  'group-hover:bg-blue-100': !day.isToday && !isPastDate(day.date),
                  'text-gray-400': isPastDate(day.date) && !day.isToday
                }"
              >
                {{ day.date.getDate() }}
              </span>
              <span 
                v-if="getSchedulesForDate(day.date).length > 0" 
                class="bg-blue-600 text-white text-xs px-2 py-1 rounded-full font-medium"
              >
                {{ getSchedulesForDate(day.date).length }}
              </span>
            </div>

            <!-- Quick add badge - only show for non-past dates -->
            <div v-if="!isPastDate(day.date)" class="absolute top-1 right-1 opacity-0 group-hover:opacity-100 transition">
              <span class="text-xs bg-green-500 text-white px-2 py-1 rounded-full shadow-lg">
                <i class="fa fa-plus"></i> Click to add
              </span>
            </div>

            <!-- Past date badge -->
            <div v-if="isPastDate(day.date) && !day.isToday" class="absolute top-1 right-1">
              <span class="text-xs bg-gray-400 text-white px-2 py-1 rounded-full shadow-lg">
                <i class="fa fa-ban"></i> Past
              </span>
            </div>

            <!-- Schedules for the day (clickable) -->
            <div class="space-y-1 max-h-[90px] overflow-y-auto">
              <div
                v-for="schedule in getSchedulesForDate(day.date).slice(0, 2)"
                :key="schedule.id"
                @click.stop="viewSchedule(schedule)"
                class="text-xs p-2 rounded-lg cursor-pointer truncate transition"
                :class="getStatusBgClass(schedule.status)"
              >
                <div class="flex items-center gap-1">
                  <i class="fa fa-clock-o text-xs"></i>
                  <span class="font-medium">{{ formatTime(schedule.hearing_date) }}</span>
                </div>
                <div class="font-semibold truncate">
                  {{ schedule.lawyer?.last_name }} - 
                  <span v-if="schedule.case_number" class="text-gray-600">[{{ schedule.case_number }}]</span>
                </div>
              </div>
              <div 
                v-if="getSchedulesForDate(day.date).length > 2"
                class="text-xs text-blue-600 hover:text-blue-800 cursor-pointer mt-1 font-medium flex items-center gap-1"
                @click.stop="viewDaySchedules(day.date)"
              >
                <i class="fa fa-plus-circle"></i>
                {{ getSchedulesForDate(day.date).length - 2 }} more
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Legend -->
      <div class="border-t p-4 bg-gray-50">
        <div class="flex flex-wrap items-center gap-6 text-sm">
          <span class="font-medium text-gray-700 mr-2">
            <i class="fa fa-info-circle mr-1"></i>Status:
          </span>
          <div class="flex items-center gap-2">
            <span class="w-3 h-3 bg-blue-100 rounded-full"></span>
            <span>Scheduled</span>
          </div>
          <div class="flex items-center gap-2">
            <span class="w-3 h-3 bg-green-100 rounded-full"></span>
            <span>Completed</span>
          </div>
          <div class="flex items-center gap-2">
            <span class="w-3 h-3 bg-red-100 rounded-full"></span>
            <span>Cancelled</span>
          </div>
          <div class="flex items-center gap-2">
            <span class="w-3 h-3 bg-yellow-100 rounded-full"></span>
            <span>Rescheduled</span>
          </div>
          <div class="flex items-center gap-2 ml-auto">
            <span class="w-3 h-3 bg-blue-600 rounded-full"></span>
            <span>Today</span>
          </div>
          <div class="flex items-center gap-2">
            <span class="w-3 h-3 bg-green-500 rounded-full"></span>
            <span>Click future date to add</span>
          </div>
          <div class="flex items-center gap-2">
            <span class="w-3 h-3 bg-gray-400 rounded-full"></span>
            <span>Past dates - view only</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Table View -->
    <div v-else class="bg-white shadow-md rounded-xl overflow-hidden">
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
              <th class="p-3 font-medium">Case #</th>
              <th class="p-3 font-medium">Title</th>
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
                  {{ schedule.lawyer?.first_name }} {{ schedule.lawyer?.last_name }}
                </div>
                <div class="text-sm text-gray-500">{{ schedule.lawyer?.email }}</div>
              </td>
              <td class="p-3">
                <span v-if="schedule.case_number" class="font-mono text-sm bg-gray-100 px-2 py-1 rounded">
                  {{ schedule.case_number }}
                </span>
                <span v-else class="text-gray-400 text-sm">N/A</span>
              </td>
              <td class="p-3 font-medium">{{ schedule.title }}</td>
              <td class="p-3 text-gray-500">{{ schedule.court_location || 'N/A' }}</td>
              <td class="p-3">
                <span :class="getStatusClass(schedule.status)" class="px-3 py-1 text-xs font-semibold rounded-full">
                  {{ schedule.status }}
                </span>
              </td>
              <td class="p-3 text-gray-500">
                {{ schedule.creator?.first_name }} {{ schedule.creator?.last_name }}
              </td>
              <td class="p-3">
                <div class="flex justify-end gap-2">
                  <button
                    @click="viewSchedule(schedule)"
                    class="flex items-center justify-center w-10 h-10 rounded-lg bg-blue-100 text-blue-700 hover:bg-blue-200 hover:text-blue-900 transition"
                    title="View Schedule"
                  >
                    <i class="fa fa-eye"></i>
                  </button>
                  <button
                    @click="editSchedule(schedule)"
                    class="flex items-center justify-center w-10 h-10 rounded-lg bg-green-100 text-green-700 hover:bg-green-200 hover:text-green-900 transition"
                    title="Edit Schedule"
                  >
                    <i class="fa fa-edit"></i>
                  </button>
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
              <!-- Selected Date Banner - Always show when creating from date click -->
              <div v-if="!isEdit && selectedDate" class="col-span-2 bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200 rounded-lg p-4 mb-6">
                <div class="flex items-center justify-between">
                  <div class="flex items-center gap-3">
                    <div class="bg-green-500 p-2 rounded-full">
                      <i class="fa fa-calendar-check text-white"></i>
                    </div>
                    <div>
                      <p class="text-xs text-green-700 font-medium uppercase">Creating schedule for</p>
                      <p class="text-lg font-bold text-gray-900">{{ formatDate(selectedDate) }}</p>
                      <p class="text-sm text-gray-600">{{ formatTime(form.hearing_date) }}</p>
                    </div>
                  </div>
                  <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-xs font-medium">
                    <i class="fa fa-clock-o mr-1"></i>You can change the time
                  </span>
                </div>
              </div>

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

                <!-- Case Number -->
                <div class="col-span-2 md:col-span-1">
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fa fa-file-text mr-1"></i>Case Number <span class="text-red-500">*</span>
                  </label>
                  <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                      <i class="fa fa-gavel text-gray-400"></i>
                    </div>
                    <input
                      type="text"
                      v-model="form.case_number"
                      required
                      placeholder="e.g. CRC-2024-12345"
                      class="block w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-mono"
                    />
                  </div>
                  <p class="text-xs text-gray-500 mt-1">Enter the official case/court reference number</p>
                </div>

                <!-- Title -->
                <div class="col-span-2 md:col-span-1">
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

                <!-- Court Location -->
                <div class="col-span-2 md:col-span-1">
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fa fa-map-marker mr-1"></i>Court Location
                  </label>
                  <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                      <i class="fa fa-building text-gray-400"></i>
                    </div>
                    <input
                      type="text"
                      v-model="form.court_location"
                      placeholder="e.g. Regional Trial Court, Branch 12"
                      class="block w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    />
                  </div>
                </div>

                <!-- Hearing Date -->
                <div class="col-span-2 md:col-span-1">
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fa fa-calendar-check mr-1"></i>Hearing Date & Time <span class="text-red-500">*</span>
                  </label>
                  <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                      <i class="fa fa-clock-o text-gray-400"></i>
                    </div>
                    <input
                      type="datetime-local"
                      v-model="form.hearing_date"
                      required
                      :min="minDateTime"
                      class="block w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    />
                  </div>
                  <p class="text-xs text-gray-500 mt-1">Must be a future date and time</p>
                </div>

                <!-- Status - Hidden when creating, shown when editing -->
                <div v-if="isEdit" class="col-span-2 md:col-span-1">
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fa fa-info-circle mr-1"></i>Status
                  </label>
                  <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                      <i class="fa fa-flag text-gray-400"></i>
                    </div>
                    <select
                      v-model="form.status"
                      class="block w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                      <option value="scheduled">Scheduled</option>
                      <option value="completed">Completed</option>
                      <option value="cancelled">Cancelled</option>
                      <option value="rescheduled">Rescheduled</option>
                    </select>
                  </div>
                </div>

                <!-- Hidden status field for create mode - always scheduled -->
                <input v-if="!isEdit" type="hidden" v-model="form.status" value="scheduled" />

                <!-- Description -->
                <div class="col-span-2">
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fa fa-align-left mr-1"></i>Description
                  </label>
                  <textarea
                    v-model="form.description"
                    rows="3"
                    placeholder="Enter hearing description, agenda, or notes about the case"
                    class="block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  ></textarea>
                </div>

                <!-- Notes -->
                <div class="col-span-2">
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fa fa-sticky-note mr-1"></i>Additional Notes
                  </label>
                  <textarea
                    v-model="form.notes"
                    rows="2"
                    placeholder="Any additional information, reminders, or special instructions"
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
                class="px-5 py-2 bg-blue-900 text-white rounded-lg shadow hover:bg-blue-800 transition disabled:bg-gray-400 disabled:cursor-not-allowed flex items-center gap-2"
              >
                <i v-if="!formLoading" class="fa" :class="isEdit ? 'fa-save' : 'fa-plus'"></i>
                <i v-if="formLoading" class="fa fa-spinner fa-spin"></i>
                {{ formLoading ? 'Saving...' : (isEdit ? 'Update Schedule' : 'Create Schedule') }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- VIEW MODAL with Status Actions -->
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
            <!-- Status Action Buttons -->
            <div class="mb-6 bg-gray-50 p-4 rounded-lg border">
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                  <span class="text-sm font-medium text-gray-700">Current Status:</span>
                  <span :class="getStatusClass(selectedSchedule?.status)" class="px-3 py-1 text-xs font-semibold rounded-full">
                    {{ selectedSchedule?.status }}
                  </span>
                </div>
                <div class="flex gap-2">
                  <button
                    v-if="selectedSchedule?.status !== 'rescheduled'"
                    @click="updateStatus('rescheduled')"
                    class="px-4 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 transition flex items-center gap-2 text-sm"
                    :disabled="statusLoading"
                  >
                    <i class="fa fa-calendar"></i>
                    Reschedule
                  </button>
                  <button
                    v-if="selectedSchedule?.status !== 'cancelled'"
                    @click="updateStatus('cancelled')"
                    class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition flex items-center gap-2 text-sm"
                    :disabled="statusLoading"
                  >
                    <i class="fa fa-ban"></i>
                    Cancel
                  </button>
                  <button
                    v-if="selectedSchedule?.status !== 'completed'"
                    @click="updateStatus('completed')"
                    class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition flex items-center gap-2 text-sm"
                    :disabled="statusLoading"
                  >
                    <i class="fa fa-check-circle"></i>
                    Complete
                  </button>
                </div>
              </div>
              <p v-if="statusLoading" class="text-xs text-gray-500 mt-2">
                <i class="fa fa-spinner fa-spin mr-1"></i>Updating status...
              </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- Case Number -->
              <div class="col-span-2 bg-gradient-to-r from-purple-50 to-indigo-50 p-5 rounded-lg border border-purple-200">
                <div class="flex items-center justify-between">
                  <div>
                    <label class="text-xs font-semibold text-gray-600 uppercase tracking-wide flex items-center mb-2">
                      <i class="fa fa-file-text mr-2 text-purple-600"></i>Case Number
                    </label>
                    <p v-if="selectedSchedule?.case_number" class="text-xl font-bold font-mono text-gray-900">
                      {{ selectedSchedule.case_number }}
                    </p>
                    <p v-else class="text-lg text-gray-400 italic">No case number provided</p>
                  </div>
                  <div class="bg-purple-200 p-3 rounded-full">
                    <i class="fa fa-gavel text-purple-700 text-2xl"></i>
                  </div>
                </div>
              </div>

              <!-- Lawyer Info -->
              <div class="bg-blue-50 p-4 rounded-lg">
                <label class="text-xs font-semibold text-gray-600 uppercase tracking-wide flex items-center mb-2">
                  <i class="fa fa-user-tie mr-2"></i>Lawyer
                </label>
                <p class="text-sm font-medium text-gray-900">
                  {{ selectedSchedule?.lawyer?.first_name }} {{ selectedSchedule?.lawyer?.last_name }}
                </p>
                <p class="text-xs text-gray-500">{{ selectedSchedule?.lawyer?.email }}</p>
              </div>

              <!-- Status (read-only display) -->
              <div class="bg-gray-50 p-4 rounded-lg">
                <label class="text-xs font-semibold text-gray-600 uppercase tracking-wide flex items-center mb-2">
                  <i class="fa fa-info-circle mr-2"></i>Status
                </label>
                <span :class="getStatusClass(selectedSchedule?.status)" class="inline-flex px-3 py-1 text-xs font-semibold rounded-full">
                  {{ selectedSchedule?.status }}
                </span>
              </div>

              <!-- Title -->
              <div class="col-span-2 bg-gray-50 p-4 rounded-lg">
                <label class="text-xs font-semibold text-gray-600 uppercase tracking-wide flex items-center mb-2">
                  <i class="fa fa-heading mr-2"></i>Title
                </label>
                <p class="text-sm text-gray-900 font-medium">{{ selectedSchedule?.title }}</p>
              </div>

              <!-- Court Location -->
              <div class="bg-gray-50 p-4 rounded-lg">
                <label class="text-xs font-semibold text-gray-600 uppercase tracking-wide flex items-center mb-2">
                  <i class="fa fa-map-marker mr-2"></i>Court Location
                </label>
                <p class="text-sm text-gray-900">{{ selectedSchedule?.court_location || 'N/A' }}</p>
              </div>

              <!-- Hearing Date -->
              <div class="bg-green-50 p-4 rounded-lg">
                <label class="text-xs font-semibold text-gray-600 uppercase tracking-wide flex items-center mb-2">
                  <i class="fa fa-calendar-check mr-2"></i>Hearing Date & Time
                </label>
                <p class="text-sm text-gray-900 font-medium">{{ formatDateTime(selectedSchedule?.hearing_date) }}</p>
              </div>

              <!-- Created By -->
              <div class="col-span-2 bg-yellow-50 p-4 rounded-lg">
                <label class="text-xs font-semibold text-gray-600 uppercase tracking-wide flex items-center mb-2">
                  <i class="fa fa-user mr-2"></i>Created By
                </label>
                <p class="text-sm text-gray-900 font-medium">
                  {{ selectedSchedule?.creator?.first_name }} {{ selectedSchedule?.creator?.last_name }}
                </p>
              </div>

              <!-- Description -->
              <div class="col-span-2 bg-gray-50 p-4 rounded-lg">
                <label class="text-xs font-semibold text-gray-600 uppercase tracking-wide flex items-center mb-2">
                  <i class="fa fa-align-left mr-2"></i>Description
                </label>
                <p class="text-sm text-gray-900">{{ selectedSchedule?.description || 'No description provided' }}</p>
              </div>

              <!-- Notes -->
              <div class="col-span-2 bg-gray-50 p-4 rounded-lg">
                <label class="text-xs font-semibold text-gray-600 uppercase tracking-wide flex items-center mb-2">
                  <i class="fa fa-sticky-note mr-2"></i>Notes
                </label>
                <p class="text-sm text-gray-900">{{ selectedSchedule?.notes || 'No notes' }}</p>
              </div>
            </div>
          </div>

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

    <!-- DAY SCHEDULES MODAL (Only for viewing multiple schedules) -->
    <div v-if="showDayModal" class="fixed inset-0 z-50 overflow-y-auto bg-gray-900 bg-opacity-50" @click.self="showDayModal = false">
      <div class="flex items-center justify-center min-h-screen px-4 py-8">
        <div class="bg-white rounded-xl shadow-md w-full max-w-2xl overflow-hidden">
          <div class="bg-blue-900 px-6 py-4 flex items-center justify-between">
            <h3 class="text-xl font-semibold text-white flex items-center gap-2">
              <i class="fa fa-calendar mr-2"></i>
              Schedules for {{ formatDate(selectedDate) }}
            </h3>
            <div class="flex items-center gap-2">
              <button 
                v-if="!isPastDate(selectedDate)"
                @click="openCreateModalWithDate(selectedDate)" 
                class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg text-sm transition flex items-center gap-2"
              >
                <i class="fa fa-plus"></i> Add New Schedule
              </button>
              <button @click="showDayModal = false" class="text-white hover:text-gray-200 transition">
                <i class="fa fa-times text-2xl"></i>
              </button>
            </div>
          </div>
          <div class="p-6">
            <div class="space-y-3">
              <div
                v-for="schedule in daySchedules"
                :key="schedule.id"
                @click="viewSchedule(schedule)"
                class="p-4 border rounded-lg hover:shadow-md transition cursor-pointer"
              >
                <div class="flex justify-between items-start">
                  <div>
                    <div class="flex items-center gap-2 mb-2">
                      <span :class="getStatusClass(schedule.status)" class="px-3 py-1 text-xs font-semibold rounded-full">
                        {{ schedule.status }}
                      </span>
                      <span class="text-sm font-semibold">{{ schedule.title }}</span>
                    </div>
                    <p class="text-sm text-gray-600">
                      <i class="fa fa-clock-o mr-1"></i>{{ formatTime(schedule.hearing_date) }}
                    </p>
                    <p class="text-sm text-gray-600">
                      <i class="fa fa-user-tie mr-1"></i>{{ schedule.lawyer?.first_name }} {{ schedule.lawyer?.last_name }}
                    </p>
                    <p v-if="schedule.case_number" class="text-sm text-gray-600">
                      <i class="fa fa-file-text mr-1"></i>Case #: {{ schedule.case_number }}
                    </p>
                  </div>
                  <div class="flex gap-2">
                    <button @click.stop="editSchedule(schedule)" class="text-green-600 hover:text-green-800">
                      <i class="fa fa-edit"></i>
                    </button>
                    <button @click.stop="handleDelete(schedule.id)" class="text-red-600 hover:text-red-800">
                      <i class="fa fa-trash"></i>
                    </button>
                  </div>
                </div>
              </div>
              <div v-if="daySchedules.length === 0" class="text-center py-8">
                <div class="text-gray-400 mb-4">
                  <i class="fa fa-calendar-times-o text-5xl"></i>
                </div>
                <p class="text-gray-500 mb-4">No schedules for this day.</p>
                <button 
                  v-if="!isPastDate(selectedDate)"
                  @click="openCreateModalWithDate(selectedDate)" 
                  class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition inline-flex items-center gap-2"
                >
                  <i class="fa fa-plus"></i> Create First Schedule
                </button>
                <p v-else class="text-gray-400 text-sm">
                  <i class="fa fa-ban mr-1"></i> Cannot add schedules to past dates
                </p>
              </div>
            </div>
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
    const statusLoading = ref(false);
    const showFormModal = ref(false);
    const showViewModal = ref(false);
    const showDayModal = ref(false);
    const selectedSchedule = ref(null);
    const isEdit = ref(false);
    const viewMode = ref('calendar');
    const selectedMonth = ref('');
    const currentDate = ref(new Date());
    const selectedDate = ref(null);
    const daySchedules = ref([]);

    const weekDays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

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
      status: "scheduled",
      notes: ""
    });

    // Computed property for min datetime (now)
    const minDateTime = computed(() => {
      const now = new Date();
      const year = now.getFullYear();
      const month = String(now.getMonth() + 1).padStart(2, '0');
      const day = String(now.getDate()).padStart(2, '0');
      const hours = String(now.getHours()).padStart(2, '0');
      const minutes = String(now.getMinutes()).padStart(2, '0');
      return `${year}-${month}-${day}T${hours}:${minutes}`;
    });

    const stats = computed(() => ({
      totalSchedules: schedules.value.length,
      scheduled: schedules.value.filter(s => s.status === "scheduled").length,
      completed: schedules.value.filter(s => s.status === "completed").length,
      cancelled: schedules.value.filter(s => s.status === "cancelled").length
    }));

    const currentMonthName = computed(() => {
      return currentDate.value.toLocaleString('default', { month: 'long' });
    });

    const currentYear = computed(() => {
      return currentDate.value.getFullYear();
    });

    const monthStats = computed(() => {
      const dayCounts = [0, 0, 0, 0, 0, 0, 0];
      
      schedules.value.forEach(schedule => {
        const scheduleDate = new Date(schedule.hearing_date);
        if (scheduleDate.getMonth() === currentDate.value.getMonth() && 
            scheduleDate.getFullYear() === currentDate.value.getFullYear()) {
          const dayOfWeek = scheduleDate.getDay();
          dayCounts[dayOfWeek]++;
        }
      });
      
      return dayCounts;
    });

    const calendarDays = computed(() => {
      const year = currentDate.value.getFullYear();
      const month = currentDate.value.getMonth();
      
      const firstDay = new Date(year, month, 1);
      const lastDay = new Date(year, month + 1, 0);
      
      const startDate = new Date(firstDay);
      startDate.setDate(firstDay.getDate() - firstDay.getDay());
      
      const endDate = new Date(lastDay);
      endDate.setDate(lastDay.getDate() + (6 - lastDay.getDay()));
      
      const days = [];
      const today = new Date();
      today.setHours(0, 0, 0, 0);
      
      for (let date = new Date(startDate); date <= endDate; date.setDate(date.getDate() + 1)) {
        days.push({
          date: new Date(date),
          isCurrentMonth: date.getMonth() === month,
          isToday: date.toDateString() === today.toDateString()
        });
      }
      
      return days;
    });

    const getSchedulesForMonth = () => {
      return schedules.value.filter(schedule => {
        const scheduleDate = new Date(schedule.hearing_date);
        return scheduleDate.getMonth() === currentDate.value.getMonth() && 
               scheduleDate.getFullYear() === currentDate.value.getFullYear();
      });
    };

    const isPastDate = (date) => {
      const today = new Date();
      today.setHours(0, 0, 0, 0);
      date.setHours(0, 0, 0, 0);
      return date < today;
    };

    const showPastDateAlert = () => {
      Swal.fire({
        icon: "error",
        title: "Cannot Add Schedule",
        text: "Cannot create a schedule on a past date. Please select a future date.",
        confirmButtonColor: "#3085d6",
        confirmButtonText: "OK"
      });
    };

    const fetchSchedules = async () => {
      loading.value = true;
      try {
        const params = { ...filters.value };
        
        if (selectedMonth.value) {
          const [year, month] = selectedMonth.value.split('-');
          params.start_date = `${year}-${month}-01`;
          const lastDay = new Date(year, month, 0).getDate();
          params.end_date = `${year}-${month}-${lastDay}`;
        }
        
        const res = await api.get("/schedules", { params });
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

    const getSchedulesForDate = (date) => {
      return schedules.value.filter(schedule => {
        const scheduleDate = new Date(schedule.hearing_date);
        return scheduleDate.toDateString() === date.toDateString();
      });
    };

    const viewDaySchedules = (date) => {
      selectedDate.value = date;
      daySchedules.value = getSchedulesForDate(date);
      showDayModal.value = true;
    };

    // Main function: Click date -> Validate if date is past, show validation, otherwise open create modal
    const openCreateModalWithDate = (date) => {
      // Check if date is in the past (excluding today)
      if (isPastDate(date)) {
        showPastDateAlert();
        return;
      }

      // Format the date to YYYY-MM-DDTHH:MM
      const year = date.getFullYear();
      const month = String(date.getMonth() + 1).padStart(2, '0');
      const day = String(date.getDate()).padStart(2, '0');
      
      let hours, minutes;
      
      // If date is today, use current time + 1 hour (minimum future time)
      if (date.toDateString() === new Date().toDateString()) {
        const now = new Date();
        now.setHours(now.getHours() + 1);
        hours = String(now.getHours()).padStart(2, '0');
        minutes = String(now.getMinutes()).padStart(2, '0');
      } else {
        // Future date - default to 9:00 AM
        hours = '09';
        minutes = '00';
      }
      
      const formattedDate = `${year}-${month}-${day}T${hours}:${minutes}`;
      
      // Reset form with date pre-filled and status set to scheduled
      form.value = {
        lawyer_id: "",
        title: "",
        description: "",
        case_number: "",
        court_location: "",
        hearing_date: formattedDate,
        status: "scheduled",
        notes: ""
      };
      
      selectedDate.value = date;
      selectedSchedule.value = null;
      isEdit.value = false;
      showFormModal.value = true;
      showDayModal.value = false;
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
      selectedMonth.value = '';
      currentDate.value = new Date();
      fetchSchedules();
    };

    const previousMonth = () => {
      currentDate.value = new Date(currentDate.value.getFullYear(), currentDate.value.getMonth() - 1, 1);
      selectedMonth.value = `${currentDate.value.getFullYear()}-${String(currentDate.value.getMonth() + 1).padStart(2, '0')}`;
      fetchSchedules();
    };

    const nextMonth = () => {
      currentDate.value = new Date(currentDate.value.getFullYear(), currentDate.value.getMonth() + 1, 1);
      selectedMonth.value = `${currentDate.value.getFullYear()}-${String(currentDate.value.getMonth() + 1).padStart(2, '0')}`;
      fetchSchedules();
    };

    const goToToday = () => {
      currentDate.value = new Date();
      selectedMonth.value = `${currentDate.value.getFullYear()}-${String(currentDate.value.getMonth() + 1).padStart(2, '0')}`;
      fetchSchedules();
    };

    const openCreateModal = () => {
      const now = new Date();
      now.setHours(now.getHours() + 1); // Add 1 hour to ensure future time
      
      const year = now.getFullYear();
      const month = String(now.getMonth() + 1).padStart(2, '0');
      const day = String(now.getDate()).padStart(2, '0');
      const hours = String(now.getHours()).padStart(2, '0');
      const minutes = String(now.getMinutes()).padStart(2, '0');
      const formattedDate = `${year}-${month}-${day}T${hours}:${minutes}`;
      
      form.value = {
        lawyer_id: "",
        title: "",
        description: "",
        case_number: "",
        court_location: "",
        hearing_date: formattedDate,
        status: "scheduled",
        notes: ""
      };
      selectedDate.value = null;
      selectedSchedule.value = null;
      isEdit.value = false;
      showFormModal.value = true;
    };

    const viewSchedule = (schedule) => {
      selectedSchedule.value = schedule;
      showViewModal.value = true;
      showDayModal.value = false;
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
        status: schedule.status,
        notes: schedule.notes || ""
      };
      isEdit.value = true;
      showFormModal.value = true;
      showDayModal.value = false;
      showViewModal.value = false;
    };

    const editFromView = () => {
      showViewModal.value = false;
      editSchedule(selectedSchedule.value);
    };

    const updateStatus = async (newStatus) => {
      if (!selectedSchedule.value) return;
      
      statusLoading.value = true;
      try {
        const res = await api.put(`/schedules/${selectedSchedule.value.id}/status`, {
          status: newStatus
        });

        if (res.data.success) {
          Swal.fire({
            icon: "success",
            title: "Status Updated!",
            text: `Schedule has been marked as ${newStatus}.`,
            timer: 2000,
            showConfirmButton: false
          });
          
          // Update local data
          selectedSchedule.value.status = newStatus;
          
          // Refresh schedules list
          fetchSchedules();
        }
      } catch (e) {
        console.error("Error updating status:", e);
        const message = e.response?.data?.message || "Failed to update status";
        Swal.fire("Error", message, "error");
      } finally {
        statusLoading.value = false;
      }
    };

    const formatDateForInput = (dateString) => {
      if (!dateString) return "";
      const date = new Date(dateString);
      const year = date.getFullYear();
      const month = String(date.getMonth() + 1).padStart(2, "0");
      const day = String(date.getDate()).padStart(2, "0");
      const hours = String(date.getHours()).padStart(2, "0");
      const minutes = String(date.getMinutes()).padStart(2, "0");
      return `${year}-${month}-${day}T${hours}:${minutes}`;
    };

// In your Schedules.vue - Update the handleSubmit function
const handleSubmit = async () => {
  formLoading.value = true;
  try {
    let res;
    if (isEdit.value) {
      res = await api.put(`/schedules/${selectedSchedule.value.id}`, form.value);
    } else {
      // Ensure status is always scheduled when creating
      form.value.status = "scheduled";
      res = await api.post("/schedules", form.value);
    }

    if (res.data.success) {
      let message = res.data.message;
      
      // Check if this was a reschedule operation
      if (res.data.is_rescheduled) {
        message = "Schedule has been rescheduled to a new date. The original schedule has been marked as rescheduled.";
      }
      
      Swal.fire({
        icon: "success",
        title: "Success!",
        text: message,
        timer: 3000,
        showConfirmButton: true
      });
      
      closeFormModal();
      fetchSchedules();
    }
  } catch (e) {
    console.error("Error saving schedule:", e);
    const message = e.response?.data?.message || "Failed to save schedule";
    const errors = e.response?.data?.errors;
    
    if (errors) {
      // Display validation errors
      let errorMessage = '';
      Object.keys(errors).forEach(key => {
        errorMessage += errors[key][0] + '\n';
      });
      Swal.fire("Error", errorMessage, "error");
    } else {
      Swal.fire("Error", message, "error");
    }
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
            showDayModal.value = false;
            showViewModal.value = false;
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
      selectedDate.value = null;
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

    const formatDate = (date) => {
      if (!date) return "";
      return date.toLocaleDateString("en-PH", {
        year: "numeric",
        month: "long",
        day: "numeric"
      });
    };

    const formatTime = (dateTime) => {
      if (!dateTime) return "";
      const d = new Date(dateTime);
      return d.toLocaleTimeString("en-PH", {
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

    const getStatusBgClass = (status) => {
      const classes = {
        scheduled: "bg-blue-50 hover:bg-blue-100 text-blue-800",
        completed: "bg-green-50 hover:bg-green-100 text-green-800",
        cancelled: "bg-red-50 hover:bg-red-100 text-red-800",
        rescheduled: "bg-yellow-50 hover:bg-yellow-100 text-yellow-800"
      };
      return classes[status] || "bg-gray-50 hover:bg-gray-100 text-gray-800";
    };

    onMounted(() => {
      const today = new Date();
      selectedMonth.value = `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}`;
      fetchSchedules();
      fetchLawyers();
    });

    return {
      schedules,
      lawyers,
      loading,
      formLoading,
      statusLoading,
      showFormModal,
      showViewModal,
      showDayModal,
      selectedSchedule,
      isEdit,
      filters,
      form,
      stats,
      viewMode,
      selectedMonth,
      currentDate,
      weekDays,
      calendarDays,
      selectedDate,
      daySchedules,
      monthStats,
      minDateTime,
      getSchedulesForMonth,
      isPastDate,
      showPastDateAlert,
      applyFilters,
      refreshData,
      openCreateModal,
      openCreateModalWithDate,
      viewSchedule,
      editSchedule,
      editFromView,
      updateStatus,
      handleDelete,
      closeFormModal,
      handleSubmit,
      formatDateTime,
      formatDate,
      formatTime,
      getStatusClass,
      getStatusBgClass,
      getSchedulesForDate,
      viewDaySchedules,
      previousMonth,
      nextMonth,
      goToToday
    };
  }
};
</script>

<style scoped>
.calendar-grid {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 0.5rem;
}

.calendar-day {
  min-height: 120px;
  padding: 0.5rem;
  border: 1px solid #e5e7eb;
  border-radius: 0.5rem;
}

.calendar-day:hover {
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

.calendar-day-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.5rem;
}

.calendar-day-number {
  font-size: 0.875rem;
  font-weight: 500;
}

.calendar-day-number.today {
  color: #2563eb;
  font-weight: 700;
}

.calendar-day-number.other-month {
  color: #9ca3af;
}
</style>