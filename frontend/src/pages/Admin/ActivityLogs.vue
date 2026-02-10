<template>
  <div class="max-w-6xl mx-auto px-6 py-6">
    <h1 class="text-2xl font-bold mb-6">Activity Logs</h1>

    <!-- Search and Export section -->
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <!-- Search bar -->
      <div class="relative max-w-md w-full">
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
          <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
          </svg>
        </div>
        <input
          type="text"
          v-model="searchQuery"
          placeholder="Search activity..."
          class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-500 focus:border-transparent"
          @input="resetPagination"
        />
      </div>

      <!-- Export button -->
      <button
        @click="exportToExcel"
        :disabled="logs.length === 0"
        :class="['px-4 py-2 rounded-lg transition flex items-center gap-2 whitespace-nowrap', logs.length === 0 ? 'bg-gray-400 cursor-not-allowed' : 'bg-green-600 text-white hover:bg-green-700']"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
        </svg>
        Export to Excel
      </button>
    </div>

    <!-- Activity logs table -->
    <div class="overflow-x-auto bg-white shadow rounded-lg">
      <table class="w-full text-left">
        <thead class="bg-gray-100">
          <tr>
            <th class="p-3 font-medium">Date and time</th>
            <!-- <th class="p-3 font-medium">User ID</th> -->
            <th class="p-3 font-medium">User</th>
            <th class="p-3 font-medium">Action</th>
            <th class="p-3 font-medium">IP Address</th>
            <th class="p-3 font-medium">Browser</th>
            <th class="p-3 font-medium">Platform</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="log in paginatedLogs" :key="log.id" class="border-t hover:bg-gray-50">
            <td class="p-3">{{ log.created_at ? formatDateTime(log.created_at) : '' }}</td>
            <!-- <td class="p-3 text-gray-500">{{ log.user_id || '' }}</td> -->
            <td class="p-3">
              <div class="font-medium">{{ log.user?.first_name + ' ' + log.user?.last_name || 'N/A' }}</div>
              <div class="text-sm text-gray-500">{{ log.user?.email || '' }}</div>
            </td>
            <td class="p-3">
              <span class="text-blue-600">{{ log.action || '' }}</span>
            </td>
            <td class="p-3 text-gray-500">{{ log.ip_address || '' }}</td>
            <td class="p-3">{{ log.browser || '' }}</td>
            <td class="p-3">{{ log.platform || '' }}</td>
          </tr>
          <tr v-if="logs.length === 0">
            <td colspan="7" class="p-3 text-center text-gray-500">No activity logs found</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination - Only show if there are logs -->
    <div v-if="logs.length > 0" class="mt-6 flex flex-col sm:flex-row justify-between items-center gap-4">
      <div class="text-sm text-gray-600">
        Rows per page: 
        <select v-model="rowsPerPage" @change="resetPagination" class="ml-2 border border-gray-300 rounded px-2 py-1">
          <option v-for="option in rowsPerPageOptions" :key="option" :value="option">{{ option }}</option>
        </select>
      </div>
      
      <div class="text-sm text-gray-600">
        <span class="font-semibold">{{ startItem }} - {{ endItem }}</span> of {{ filteredLogs.length }}
      </div>
      
      <div class="flex items-center gap-2">
        <button
          @click="previousPage"
          :disabled="currentPage === 1"
          :class="['px-3 py-1 rounded border', currentPage === 1 ? 'bg-gray-100 text-gray-400 cursor-not-allowed' : 'bg-white text-gray-700 hover:bg-gray-50']"
        >
          Previous
        </button>
        
        <div class="flex gap-1">
          <button
            v-for="page in visiblePages"
            :key="page"
            @click="goToPage(page)"
            :class="['w-8 h-8 rounded border flex items-center justify-center', page === currentPage ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-700 hover:bg-gray-50']"
          >
            {{ page }}
          </button>
          <span v-if="showEllipsis" class="px-2 flex items-center">...</span>
        </div>
        
        <button
          @click="nextPage"
          :disabled="currentPage === totalPages"
          :class="['px-3 py-1 rounded border', currentPage === totalPages ? 'bg-gray-100 text-gray-400 cursor-not-allowed' : 'bg-white text-gray-700 hover:bg-gray-50']"
        >
          Next
        </button>
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
      logs: [],
      searchQuery: '',
      refreshInterval: null,
      
      // Pagination data
      currentPage: 1,
      rowsPerPage: 10,
      rowsPerPageOptions: [5, 10, 20, 50, 100]
    };
  },
  async mounted() {
    await this.fetchLogs();
    this.refreshInterval = setInterval(this.fetchLogs, 30000);
  },
  beforeUnmount() {
    clearInterval(this.refreshInterval);
  },
  computed: {
    filteredLogs() {
      if (!this.searchQuery) return this.logs;
      const query = this.searchQuery.toLowerCase();
      return this.logs.filter(log => {
        const userName = `${log.user?.first_name || ''} ${log.user?.last_name || ''}`.toLowerCase();
        const userEmail = (log.user?.email || '').toLowerCase();
        const action = (log.action || '').toLowerCase();
        const ipAddress = (log.ip_address || '').toLowerCase();
        const browser = (log.browser || '').toLowerCase();
        const platform = (log.platform || '').toLowerCase();
        return userName.includes(query) || 
               userEmail.includes(query) || 
               action.includes(query) ||
               ipAddress.includes(query) ||
               browser.includes(query) ||
               platform.includes(query);
      });
    },
    totalPages() {
      return Math.ceil(this.filteredLogs.length / this.rowsPerPage);
    },
    startItem() {
      return Math.min((this.currentPage - 1) * this.rowsPerPage + 1, this.filteredLogs.length);
    },
    endItem() {
      return Math.min(this.currentPage * this.rowsPerPage, this.filteredLogs.length);
    },
    paginatedLogs() {
      const start = (this.currentPage - 1) * this.rowsPerPage;
      const end = start + this.rowsPerPage;
      return this.filteredLogs.slice(start, end);
    },
    visiblePages() {
      const pages = [];
      const maxVisible = 5;
      if (this.totalPages <= maxVisible) {
        for (let i = 1; i <= this.totalPages; i++) pages.push(i);
      } else {
        if (this.currentPage <= 3) {
          for (let i = 1; i <= 4; i++) pages.push(i);
          pages.push(this.totalPages);
        } else if (this.currentPage >= this.totalPages - 2) {
          pages.push(1);
          for (let i = this.totalPages - 3; i <= this.totalPages; i++) pages.push(i);
        } else {
          pages.push(1);
          for (let i = this.currentPage - 1; i <= this.currentPage + 1; i++) pages.push(i);
          pages.push(this.totalPages);
        }
      }
      return pages;
    },
    showEllipsis() {
      return this.totalPages > 5 && this.currentPage < this.totalPages - 2;
    }
  },
  watch: {
    searchQuery() {
      this.currentPage = 1;
    },
    filteredLogs() {
      if (this.currentPage > this.totalPages && this.totalPages > 0) {
        this.currentPage = this.totalPages;
      } else if (this.totalPages === 0) {
        this.currentPage = 1;
      }
    }
  },
  methods: {
    async fetchLogs() {
      try {
        const res = await api.get("/activity-logs");
        this.logs = res.data.sort((a, b) => new Date(a.created_at) - new Date(b.created_at));
      } catch (e) {
        console.error('Error fetching logs:', e);
        this.logs = [];
        Swal.fire('Error', 'Failed to fetch activity logs.', 'error');
      }
    },
    
    formatDateTime(dateTime) {
      if (!dateTime) return '';
      const date = new Date(dateTime);
      return date.toLocaleString('en-US', {
        month: '2-digit',
        day: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        hour12: true
      });
    },

    safeString(value) {
      if (value === null || value === undefined) return '';
      const str = String(value);
      if (str.includes(',') || str.includes('"') || str.includes('\n') || str.includes('\r')) {
        return `"${str.replace(/"/g, '""')}"`;
      }
      return str;
    },

    async exportToExcel() {
      try {
        if (this.logs.length === 0) {
          Swal.fire('No data', 'No activity logs to export.', 'info');
          return;
        }

        const exportData = this.filteredLogs.map(log => ({
          'ID': log.id || '',
          'User ID': log.user_id || '',
          'Action': log.action || '',
          'IP Address': log.ip_address || '',
          'Browser': log.browser || '',
          'Platform': log.platform || '',
          'User Agent': log.user_agent || '',
          'Created At': log.created_at ? this.formatDateTime(log.created_at) : '',
          'Updated At': log.updated_at ? this.formatDateTime(log.updated_at) : '',
          'User First Name': log.user?.first_name || '',
          'User Last Name': log.user?.last_name || '',
          'User Email': log.user?.email || ''
        }));

        const headers = Object.keys(exportData[0]);
        const csvContent = [
          headers.join(','),
          ...exportData.map(row => headers.map(header => this.safeString(row[header])).join(','))
        ].join('\n');

        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        link.setAttribute('href', URL.createObjectURL(blob));
        link.setAttribute('download', `activity_logs_${new Date().toISOString().split('T')[0]}.csv`);
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);

        Swal.fire('Success', `Exported ${exportData.length} logs successfully.`, 'success');
      } catch (error) {
        console.error('Error exporting to Excel:', error);
        Swal.fire('Error', 'Failed to export logs.', 'error');
      }
    },

    // Pagination
    previousPage() { if (this.currentPage > 1) this.currentPage--; },
    nextPage() { if (this.currentPage < this.totalPages) this.currentPage++; },
    goToPage(page) { this.currentPage = page; },
    resetPagination() { this.currentPage = 1; }
  }
};
</script>

<style scoped>
table th, table td { 
  vertical-align: middle; 
  white-space: nowrap;
}
</style>