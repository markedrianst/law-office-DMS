<template>
  <div class="p-6 bg-gray-50 min-h-screen">
    <!-- Breadcrumb -->
    <div class="mb-4 text-sm text-gray-500 flex gap-2 flex-wrap">
      <span class="cursor-pointer hover:underline" @click="reset">
        Documents
      </span>

      <template v-if="selectedCategory">
        <span>›</span>
        <span class="cursor-pointer hover:underline" @click="view='category-details'">
          {{ selectedCategoryLabel }}
        </span>
      </template>

      <template v-if="selectedPerson">
        <span>›</span>
        <span class="cursor-pointer hover:underline" @click="view='cases'">
          {{ selectedPerson.name }}
        </span>
      </template>

      <template v-if="selectedCriminalCase">
        <span>›</span>
        <span class="cursor-pointer hover:underline" @click="view='case-details'">
          {{ selectedCriminalCase.criminalCaseNo }}
        </span>
      </template>
    </div>

    <!-- HEADER -->
    <div class="flex justify-between items-center mb-6">
      <div>
        <h1 class="text-3xl font-bold text-gray-900">Documents</h1>
        <p class="text-gray-600">Organize cases by category</p>
      </div>
    </div>

    <!-- ==================== DOCUMENTS VIEW ==================== -->
    <transition name="fade" mode="out-in">
      <!-- LEVEL 1: Categories -->
      <div v-if="view === 'categories'" key="categories" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-6">
        <div
          v-for="category in categories"
          :key="category.id"
          class="bg-white p-8 rounded-xl shadow hover:shadow-md cursor-pointer text-center font-semibold text-lg transition flex flex-col items-center gap-3 hover:scale-[1.02]"
          @click="openCategory(category.type)"
        >
          <i :class="category.icon + ' text-4xl text-blue-600'"></i>
          {{ category.title }}
          <span class="text-sm font-normal text-gray-500">
            {{ getCategoryCount(category.type) }} case{{ getCategoryCount(category.type) !== 1 ? 's' : '' }}
          </span>
        </div>
      </div>

      <!-- LEVEL 2: Category Details (People/Clusters) -->
      <div v-else-if="view === 'category-details'" key="category-details">
        <div class="flex items-center justify-between mb-6">
          <button
            @click="goBack"
            class="px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded-lg text-sm font-medium transition flex items-center gap-2"
          >
            <i class="fa-solid fa-arrow-left"></i> Back
          </button>
          
          <!-- Only Add New Case button - removed Add Person/Cluster -->
          <div class="flex gap-3">
            <button @click="openAddCaseModal"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition flex items-center gap-2">
              <i class="fa-solid fa-file-circle-plus"></i> Add New Case
            </button>
          </div>
        </div>

        <h2 class="text-2xl font-bold mb-6">{{ selectedCategoryLabel }}</h2>

        <!-- Search Bar for People -->
        <div class="bg-white rounded-xl shadow p-4 mb-6">
          <div class="relative">
            <input type="text" v-model="peopleSearch" @input="searchPeople"
                   placeholder="Search people/clusters..." class="w-full px-3 py-2 border border-gray-300 rounded-lg pl-10">
            <i class="fa-solid fa-search absolute left-3 top-3 text-gray-400"></i>
          </div>
        </div>

        <!-- People/Clusters List - SHOWING CASE NO NOT CRIMINAL CASE NO -->
        <div class="space-y-3">
          <div
            v-for="person in filteredPeople"
            :key="person.id"
            class="item flex items-center justify-between p-4 hover:shadow-md"
            @click="openPerson(person)"
          >
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                <i class="fa-solid fa-user text-blue-600"></i>
              </div>
              <div>
                <!-- Display Case No. (from person.caseNo) before name, NOT criminal case no -->
                <div class="font-medium text-gray-900">
                  <template v-if="selectedCategory === 'criminal'">
                    <span v-if="person.caseNo" class="text-blue-700 font-mono mr-2">
                      [{{ person.caseNo }}]
                    </span>
                    <span v-else class="text-gray-400 font-mono mr-2">
                      [No Case No]
                    </span>
                  </template>
                  <template v-else>
                    <span v-if="person.cases && person.cases.length > 0" class="text-blue-700 font-mono mr-2">
                      [{{ person.cases[0].crimNo || 'No Case No' }}]
                    </span>
                  </template>
                  {{ person.name }}
                </div>
                <div class="text-sm text-gray-500">{{ person.email || person.contact || 'No email' }}</div>
                <div class="text-xs text-gray-400">{{ person.phone || person.contact || 'No phone' }}</div>
              </div>
            </div>
            <div class="text-right">
              <div class="text-sm font-medium">
                <template v-if="selectedCategory === 'criminal'">
                  {{ person.criminalCases?.length || 0 }} case{{ (person.criminalCases?.length || 0) !== 1 ? 's' : '' }}
                </template>
                <template v-else>
                  {{ person.cases?.length || 0 }} case{{ (person.cases?.length || 0) !== 1 ? 's' : '' }}
                </template>
              </div>
              <div class="text-xs text-gray-500">Click to view</div>
            </div>
          </div>
          
          <div v-if="filteredPeople.length === 0" class="text-center py-8 text-gray-500">
            <i class="fa-solid fa-users text-4xl mb-3"></i>
            <p>No people/clusters found</p>
            <button @click="openAddCaseModal" class="mt-4 text-blue-600 text-sm hover:underline">
              <i class="fa-solid fa-plus-circle"></i> Add a new case
            </button>
          </div>
        </div>
      </div>

      <!-- LEVEL 3: Criminal Cases for Selected Person -->
      <div v-else-if="view === 'cases'" key="cases">
        <div class="flex items-center justify-between mb-6">
          <button
            @click="goBack"
            class="px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded-lg text-sm font-medium transition flex items-center gap-2"
          >
            <i class="fa-solid fa-arrow-left"></i> Back
          </button>
          <div class="flex items-center gap-4">
            <div class="text-lg font-semibold text-gray-800">
              <template v-if="selectedCategory === 'criminal'">
                {{ selectedPerson.name }} - Case No. {{ selectedPerson.caseNo }}
              </template>
              <template v-else>
                {{ selectedPerson.name }}'s Cases
              </template>
            </div>
            <button @click="openAddCaseModal"
                    class="px-3 py-1 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700 transition flex items-center gap-2">
              <i class="fa-solid fa-plus"></i> 
              {{ selectedCategory === 'criminal' ? 'Add Criminal Case' : 'Add Case' }}
            </button>
          </div>
        </div>

        <!-- Search Bar for Cases -->
        <div class="bg-white rounded-xl shadow p-4 mb-6">
          <div class="relative">
            <input type="text" v-model="caseSearch" @input="searchCases"
                   :placeholder="selectedCategory === 'criminal' ? 'Search criminal cases...' : 'Search cases...'" 
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg pl-10">
            <i class="fa-solid fa-search absolute left-3 top-3 text-gray-400"></i>
          </div>
        </div>

        <!-- Criminal Cases Grid -->
        <div v-if="selectedCategory === 'criminal'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <div
            v-for="criminalCase in filteredCriminalCases"
            :key="criminalCase.id"
            class="bg-white rounded-xl shadow hover:shadow-md p-5 cursor-pointer transition hover:scale-[1.02]"
            @click="openCriminalCase(criminalCase)"
          >
            <div class="flex items-start justify-between mb-3">
              <div class="w-12 h-12 rounded-lg bg-red-100 flex items-center justify-center">
                <i class="fa-solid fa-handcuffs text-red-600 text-xl"></i>
              </div>
              <span :class="getStatusClass(criminalCase.status)" class="px-2 py-1 text-xs rounded-full">
                {{ getStatusLabel(criminalCase.status) }}
              </span>
            </div>
            <h3 class="font-bold text-lg mb-2">{{ criminalCase.criminalCaseNo }}</h3>
            <p class="text-gray-600 text-sm mb-3 line-clamp-2">{{ criminalCase.description }}</p>
            <div class="flex items-center justify-between text-sm text-gray-500">
              <span>
                <i class="fa-solid fa-calendar mr-1"></i>
                {{ formatDate(criminalCase.filingDate) || 'No filing date' }}
              </span>
              <span>
                <i class="fa-solid fa-list-check mr-1"></i>
                {{ criminalCase.checklist?.length || 0 }} item{{ criminalCase.checklist?.length !== 1 ? 's' : '' }}
              </span>
            </div>
            <div class="mt-2 text-xs">
              <span class="text-green-600">{{ getCompletedTasks(criminalCase) }} completed</span>
              <span class="text-gray-400 mx-1">•</span>
              <span class="text-yellow-600">{{ getPendingChecklists(criminalCase) }} pending</span>
            </div>
          </div>
          
          <div v-if="filteredCriminalCases.length === 0" class="col-span-full text-center py-8 text-gray-500">
            <i class="fa-solid fa-file-circle-question text-4xl mb-3"></i>
            <p>No criminal cases found</p>
            <button @click="openAddCaseModal" class="mt-4 text-blue-600 text-sm hover:underline">
              <i class="fa-solid fa-plus-circle"></i> Add a criminal case
            </button>
          </div>
        </div>

        <!-- Civil/Family/etc Cases Grid -->
        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <div
            v-for="caseItem in filteredPersonCases"
            :key="caseItem.id"
            class="bg-white rounded-xl shadow hover:shadow-md p-5 cursor-pointer transition hover:scale-[1.02]"
            @click="openCase(caseItem)"
          >
            <div class="flex items-start justify-between mb-3">
              <div class="w-12 h-12 rounded-lg flex items-center justify-center"
                :class="getStatusColor(caseItem.status)">
                <i :class="`${getCaseIcon(caseItem.type)} text-xl ${getStatusIconColor(caseItem.status)}`"></i>
              </div>
              <span :class="getStatusClass(caseItem.status)" class="px-2 py-1 text-xs rounded-full">
                {{ getStatusLabel(caseItem.status) }}
              </span>
            </div>
            <h3 class="font-bold text-lg mb-2">{{ caseItem.caseNo }}</h3>
            <p class="text-gray-600 text-sm mb-3">{{ caseItem.title }}</p>
            <div class="flex items-center justify-between text-sm text-gray-500">
              <span>
                <i class="fa-solid fa-calendar mr-1"></i>
                {{ formatDate(caseItem.createdAt) }}
              </span>
              <span>
                <i class="fa-solid fa-list-check mr-1"></i>
                {{ caseItem.checklist?.length || 0 }} item{{ caseItem.checklist?.length !== 1 ? 's' : '' }}
              </span>
            </div>
          </div>
          
          <div v-if="filteredPersonCases.length === 0" class="col-span-full text-center py-8 text-gray-500">
            <i class="fa-solid fa-file-circle-question text-4xl mb-3"></i>
            <p>No cases found</p>
            <button @click="openAddCaseModal" class="mt-4 text-blue-600 text-sm hover:underline">
              <i class="fa-solid fa-plus-circle"></i> Add a case
            </button>
          </div>
        </div>
      </div>

      <!-- LEVEL 4: Criminal Case Details with Checklist -->
      <div v-else-if="view === 'case-details'" key="case-details">
        <div class="flex items-center justify-between mb-6">
          <button
            @click="goBack"
            class="px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded-lg text-sm font-medium transition flex items-center gap-2"
          >
            <i class="fa-solid fa-arrow-left"></i> Back
          </button>
          <div class="flex gap-2">
            <div class="relative">
              <button @click="showStatusDropdown = !showStatusDropdown" 
                      class="px-3 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition flex items-center gap-2">
                <i class="fa-solid fa-tag"></i> Status: {{ getStatusLabel(selectedCriminalCase?.status) }}
                <i class="fa-solid fa-chevron-down text-xs"></i>
              </button>
              <div v-if="showStatusDropdown" class="absolute right-0 mt-1 w-48 bg-white rounded-lg shadow-lg z-10 border">
                <div class="py-1">
                  <div v-for="(label, value) in {
                    pending: 'Pending',
                    for_filing: 'For Filing',
                    incomplete: 'Incomplete',
                    filed: 'Filed Cases',
                    closed: 'Case Closed'
                  }" :key="value" 
                       @click="updateStatus(selectedCriminalCase, value); showStatusDropdown = false"
                       class="px-4 py-2 text-sm hover:bg-gray-100 cursor-pointer flex items-center gap-2">
                    <span :class="'w-2 h-2 rounded-full ' + {
                      pending: 'bg-yellow-500',
                      for_filing: 'bg-orange-500',
                      incomplete: 'bg-red-500',
                      filed: 'bg-blue-500',
                      closed: 'bg-green-500'
                    }[value]"></span>
                    {{ label }}
                  </div>
                </div>
              </div>
            </div>
            <button @click="editCriminalCase(selectedCriminalCase)" 
                    class="px-3 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition flex items-center gap-2">
              <i class="fa-solid fa-edit"></i> Edit
            </button>
            <button @click="openChecklistModal(selectedCriminalCase)" 
                    class="px-3 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition flex items-center gap-2">
              <i class="fa-solid fa-list-check"></i> Checklist
            </button>
          </div>
        </div>

        <!-- Criminal Case Details Card -->
        <div class="bg-white rounded-xl shadow p-6">
          <div class="flex justify-between items-start mb-6">
            <div>
              <h2 class="text-2xl font-bold">{{ selectedCriminalCase?.criminalCaseNo }}</h2>
              <p class="text-gray-600">{{ selectedCriminalCase?.description }}</p>
            </div>
            <span :class="getStatusClass(selectedCriminalCase?.status)" class="px-3 py-1 text-sm rounded-full">
              {{ getStatusLabel(selectedCriminalCase?.status) }}
            </span>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div class="bg-gray-50 p-4 rounded-lg">
              <div class="text-sm text-gray-600">Case No.</div>
              <div class="font-semibold">{{ selectedPerson?.caseNo }}</div>
            </div>
            <div class="bg-gray-50 p-4 rounded-lg">
              <div class="text-sm text-gray-600">Client Name</div>
              <div class="font-semibold">{{ selectedPerson?.name }}</div>
            </div>
            <div class="bg-gray-50 p-4 rounded-lg">
              <div class="text-sm text-gray-600">Filing Date</div>
              <div class="font-semibold">{{ formatDate(selectedCriminalCase?.filingDate) || 'Not filed' }}</div>
            </div>
          </div>

          <!-- Checklist Summary -->
          <div class="border-t pt-6">
            <div class="flex items-center justify-between mb-4">
              <h3 class="font-semibold text-gray-700">Checklist Items</h3>
              <span class="text-sm text-gray-500">
                {{ getCompletedTasks(selectedCriminalCase) }}/{{ selectedCriminalCase?.checklist?.length || 0 }} completed
              </span>
            </div>
            
            <div class="space-y-2 max-h-96 overflow-y-auto">
              <div v-for="(item, index) in selectedCriminalCase?.checklist" :key="index"
                   class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                <div @click="toggleChecklistItem(item)" class="cursor-pointer">
                  <i :class="item.completed ? 'fa-solid fa-circle-check text-green-500' : 'fa-regular fa-circle text-gray-400'"></i>
                </div>
                <div class="flex-1">
                  <div class="text-sm" :class="item.completed ? 'line-through text-gray-500' : 'font-medium'">
                    {{ item.name }}
                  </div>
                  <div class="text-xs text-gray-500">{{ formatDate(item.date) || 'No date' }}</div>
                  <div v-if="item.fileName" class="mt-1 flex items-center gap-2 text-xs">
                    <i :class="getFileIcon(item.fileType)" class="text-blue-600"></i>
                    <span>{{ item.fileName }}</span>
                    <span class="text-gray-400">({{ formatFileSize(item.fileSize) }})</span>
                  </div>
                </div>
              </div>
              
              <div v-if="!selectedCriminalCase?.checklist?.length" class="text-center py-4 text-gray-500">
                <i class="fa-solid fa-clipboard-list text-3xl mb-2"></i>
                <p class="text-sm">No checklist items</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </transition>

    <!-- ==================== MODALS ==================== -->

    <!-- ADD/EDIT CRIMINAL CASE MODAL (with Person fields) -->
    <div v-if="showCriminalCaseModal" class="fixed inset-0 z-50 overflow-y-auto bg-black bg-opacity-50" @click.self="closeCriminalCaseModal">
      <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-xl shadow-lg w-full max-w-2xl">
          <div class="p-6">
            <h3 class="text-xl font-bold mb-4">
              {{ editingCriminalCase ? 'Edit Criminal Case' : 'Add New Criminal Case' }}
            </h3>
            
            <form @submit.prevent="saveCriminalCase">
              <div class="space-y-4">
                <!-- Person/Client Information Section -->
                <div class="bg-gray-50 p-4 rounded-lg">
                  <h4 class="font-semibold text-gray-700 mb-3">Client Information</h4>
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-1">Case No. *</label>
                      <input type="text" v-model="criminalCaseForm.caseNo" required
                             class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                             placeholder="e.g., 245">
                    </div>
                    
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-1">Client Name *</label>
                      <input type="text" v-model="criminalCaseForm.clientName" required
                             class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                             placeholder="Full name">
                    </div>
                    
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-1">Contact Number</label>
                      <input type="text" v-model="criminalCaseForm.contact"
                             class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                             placeholder="Phone number">
                    </div>
                    
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                      <input type="text" v-model="criminalCaseForm.address"
                             class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                             placeholder="Address">
                    </div>
                  </div>
                </div>
                
                <!-- Criminal Case Information Section -->
                <div class="bg-gray-50 p-4 rounded-lg">
                  <h4 class="font-semibold text-gray-700 mb-3">Criminal Case Information</h4>
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                      <label class="block text-sm font-medium text-gray-700 mb-1">Criminal Case No. *</label>
                      <input type="text" v-model="criminalCaseForm.criminalCaseNo" required
                             class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                             placeholder="e.g., CRIM. CASE NO. R-ANG-24-00879-CR">
                    </div>
                    
                    <div class="md:col-span-2">
                      <label class="block text-sm font-medium text-gray-700 mb-1">Description / Violation *</label>
                      <textarea v-model="criminalCaseForm.description" required rows="3"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="e.g., VIOLATION OF SECTION 11, ART. II OF RA 9165"></textarea>
                    </div>
                    
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-1">Status *</label>
                      <select v-model="criminalCaseForm.status" required
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="pending">Pending</option>
                        <option value="for_filing">For Filing</option>
                        <option value="incomplete">Incomplete</option>
                        <option value="filed">Filed Cases</option>
                        <option value="closed">Case Closed</option>
                      </select>
                    </div>
                    
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-1">Filing Date</label>
                      <input type="date" v-model="criminalCaseForm.filingDate"
                             class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                  </div>
                </div>
              </div>
              
              <div class="flex justify-end gap-3 mt-6">
                <button type="button" @click="closeCriminalCaseModal"
                        class="px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg">
                  Cancel
                </button>
                <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white hover:bg-blue-700 rounded-lg">
                  {{ editingCriminalCase ? 'Update Case' : 'Create Case' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- CREATE/EDIT CIVIL CASE MODAL -->
    <div v-if="showCaseModal" class="fixed inset-0 z-50 overflow-y-auto bg-black bg-opacity-50" @click.self="closeCaseModal">
      <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-xl shadow-lg w-full max-w-4xl">
          <div class="p-6">
            <h3 class="text-xl font-bold mb-4">
              {{ editingCase ? 'Edit Case' : 'Create New Case' }}
            </h3>
            
            <form @submit.prevent="saveCase">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Left Column -->
                <div class="space-y-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Crim No.</label>
                    <input type="text" v-model="caseForm.crimNo"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                           placeholder="e.g., 22">
                  </div>
                  
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Case Number *</label>
                    <input type="text" v-model="caseForm.caseNumber" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                  </div>
                  
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Title *</label>
                    <input type="text" v-model="caseForm.title" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                  </div>
                  
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea v-model="caseForm.description" rows="3"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                  </div>
                </div>
                
                <!-- Right Column -->
                <div class="space-y-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Case Type *</label>
                    <select v-model="caseForm.type" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                      <option value="">Select Type</option>
                      <option value="civil">Civil</option>
                      <option value="criminal">Criminal</option>
                      <option value="family">Family</option>
                      <option value="corporate">Corporate</option>
                      <option value="labor">Labor</option>
                    </select>
                  </div>
                  
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status *</label>
                    <select v-model="caseForm.status" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                      <option value="pending">Pending</option>
                      <option value="for_filing">For Filing</option>
                      <option value="incomplete">Incomplete</option>
                      <option value="filed">Filed Cases</option>
                      <option value="closed">Case Closed</option>
                    </select>
                  </div>
                  
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Court</label>
                    <input type="text" v-model="caseForm.court"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                  </div>
                  
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Assigned Lawyer</label>
                    <select v-model="caseForm.lawyerId"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                      <option value="">Select Lawyer</option>
                      <option v-for="lawyer in lawyers" :key="lawyer.id" :value="lawyer.id">
                        Atty. {{ lawyer.name }}
                      </option>
                    </select>
                  </div>
                  
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Filing Date</label>
                    <input type="date" v-model="caseForm.filingDate"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                  </div>
                </div>
              </div>
              
              <div class="flex justify-end gap-3 mt-6">
                <button type="button" @click="closeCaseModal"
                        class="px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg">
                  Cancel
                </button>
                <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white hover:bg-blue-700 rounded-lg">
                  {{ editingCase ? 'Update Case' : 'Create Case' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- CHECKLIST MODAL WITH FILE UPLOAD/DOWNLOAD -->
    <div v-if="showChecklistModal" class="fixed inset-0 z-50 overflow-y-auto bg-black bg-opacity-50" @click.self="closeChecklistModal">
      <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-xl shadow-lg w-full max-w-4xl">
          <div class="p-6">
            <div class="flex items-center justify-between mb-6">
              <div>
                <h3 class="text-xl font-bold">Checklist Management</h3>
                <p class="text-gray-600">{{ selectedCriminalCase?.criminalCaseNo }} - {{ selectedCriminalCase?.description }}</p>
              </div>
              <div class="flex items-center gap-2">
                <span class="text-sm text-gray-500">
                  {{ getCompletedTasks(selectedCriminalCase) }}/{{ selectedCriminalCase?.checklist?.length || 0 }} completed
                </span>
                <button @click="addChecklistItem" 
                        class="px-3 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 flex items-center gap-2">
                  <i class="fa-solid fa-plus"></i> Add Task
                </button>
              </div>
            </div>
            
            <!-- Add New Task Input Form -->
            <div v-if="showAddTaskForm" class="mb-6 p-4 bg-blue-50 rounded-lg border border-blue-200">
              <h4 class="font-semibold text-gray-700 mb-3">Add New Checklist Item</h4>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                  <label class="block text-sm font-medium text-gray-700 mb-1">Task Name *</label>
                  <input type="text" v-model="newChecklistItem.name" 
                         class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                         placeholder="e.g., ORDER, TRANSCRIPT, DECISION">
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Date</label>
                  <input type="date" v-model="newChecklistItem.date" 
                         class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                  <select v-model="newChecklistItem.completed" 
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option :value="false">Pending</option>
                    <option :value="true">Completed</option>
                  </select>
                </div>
                <div class="md:col-span-2">
                  <label class="block text-sm font-medium text-gray-700 mb-1">Attach File (PDF, Image, DOC)</label>
                  <div class="flex items-center gap-2">
                    <label class="flex-1 flex items-center justify-center px-4 py-2 border-2 border-dashed border-gray-300 rounded-lg cursor-pointer hover:border-blue-500 hover:bg-blue-50 transition">
                      <i class="fa-solid fa-cloud-upload-alt text-gray-500 mr-2"></i>
                      <span class="text-sm text-gray-600">{{ newChecklistItem.fileName || 'Choose file or drag here' }}</span>
                      <input type="file" @change="handleFileUpload" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" class="hidden">
                    </label>
                    <button v-if="newChecklistItem.file" @click="clearNewFile" 
                            class="px-3 py-2 bg-red-100 text-red-600 rounded-lg hover:bg-red-200">
                      <i class="fa-solid fa-times"></i>
                    </button>
                  </div>
                  <p class="text-xs text-gray-500 mt-1">Supported formats: PDF, DOC, DOCX, JPG, PNG (Max 10MB)</p>
                </div>
              </div>
              <div class="flex justify-end gap-2 mt-4">
                <button @click="cancelAddChecklistItem" 
                        class="px-4 py-2 text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-lg">
                  Cancel
                </button>
                <button @click="saveNewChecklistItem" 
                        class="px-4 py-2 bg-blue-600 text-white hover:bg-blue-700 rounded-lg">
                  <i class="fa-solid fa-plus mr-1"></i> Add Task
                </button>
              </div>
            </div>
            
            <!-- Checklist Items -->
            <div class="space-y-3 max-h-96 overflow-y-auto mb-4">
              <div v-for="(item, index) in selectedCriminalCase?.checklist" :key="index"
                   class="flex items-start gap-4 p-4 border rounded-lg hover:bg-gray-50">
                <!-- Checkbox -->
                <button @click="toggleChecklistItem(item)"
                        class="w-6 h-6 rounded-full border-2 flex items-center justify-center transition shrink-0 mt-1"
                        :class="item.completed ? 'bg-green-500 border-green-500 text-white' : 'border-gray-300'">
                  <i v-if="item.completed" class="fa-solid fa-check text-xs"></i>
                </button>
                
                <!-- Task Details -->
                <div class="flex-1 min-w-0">
                  <div class="flex items-start justify-between">
                    <div>
                      <input type="text" v-model="item.name" 
                             class="font-medium bg-transparent border-b border-transparent hover:border-gray-300 focus:border-blue-500 focus:outline-none px-1 -ml-1 w-full"
                             :class="item.completed ? 'line-through text-gray-500' : 'text-gray-900'">
                    </div>
                    <span class="text-xs px-2 py-1 rounded-full" 
                          :class="item.completed ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'">
                      {{ item.completed ? 'Completed' : 'Pending' }}
                    </span>
                  </div>
                  
                  <div class="flex items-center gap-4 text-sm text-gray-500 mt-2">
                    <div class="flex items-center gap-1">
                      <i class="fa-solid fa-calendar"></i>
                      <input type="date" v-model="item.date" class="text-sm border-none bg-transparent p-0 focus:ring-0">
                    </div>
                  </div>
                  
                  <!-- File Attachment Section -->
                  <div class="mt-3 flex items-center gap-2 flex-wrap">
                    <!-- Display attached file if exists -->
                    <div v-if="item.file" class="flex items-center gap-2 bg-gray-100 px-3 py-2 rounded-lg">
                      <i :class="getFileIcon(item.fileType)" class="text-gray-600"></i>
                      <span class="text-sm font-medium">{{ item.fileName || 'Attachment' }}</span>
                      <span class="text-xs text-gray-500">({{ formatFileSize(item.fileSize) }})</span>
                      <div class="flex items-center gap-1 ml-2">
                        <button @click="downloadFile(item)" 
                                class="p-1 text-blue-600 hover:bg-blue-100 rounded"
                                title="Download">
                          <i class="fa-solid fa-download"></i>
                        </button>
                        <button @click="viewFile(item)" 
                                class="p-1 text-green-600 hover:bg-green-100 rounded"
                                title="View">
                          <i class="fa-solid fa-eye"></i>
                        </button>
                        <button @click="removeFile(item)" 
                                class="p-1 text-red-600 hover:bg-red-100 rounded"
                                title="Remove">
                          <i class="fa-solid fa-trash"></i>
                        </button>
                      </div>
                    </div>
                    
                    <!-- Upload button -->
                    <label class="flex items-center gap-2 px-3 py-2 bg-gray-50 hover:bg-gray-100 rounded-lg cursor-pointer border border-gray-200">
                      <i class="fa-solid fa-cloud-upload-alt text-gray-600"></i>
                      <span class="text-sm">Attach File</span>
                      <input type="file" @change="(e) => attachFileToItem(e, item)" 
                             accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" class="hidden">
                    </label>
                  </div>
                </div>
                
                <!-- Actions -->
                <button @click="deleteChecklistItem(index)"
                        class="w-8 h-8 rounded-full bg-red-100 text-red-600 hover:bg-red-200 flex items-center justify-center shrink-0">
                  <i class="fa-solid fa-trash text-sm"></i>
                </button>
              </div>
              
              <div v-if="!selectedCriminalCase?.checklist?.length" class="text-center py-12 text-gray-500">
                <i class="fa-solid fa-clipboard-list text-5xl mb-4 text-gray-300"></i>
                <p class="text-lg font-medium">No checklist items yet</p>
                <p class="text-sm mt-1">Click "Add Task" to create your first checklist item</p>
                <button @click="showAddTaskForm = true" class="mt-4 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                  <i class="fa-solid fa-plus mr-1"></i> Add Your First Task
                </button>
              </div>
            </div>
            
            <!-- Case No. Display -->
            <div class="bg-gray-50 p-4 rounded-lg mb-4">
              <div class="text-sm text-gray-600">Case No.</div>
              <div class="font-semibold text-lg">{{ selectedPerson?.caseNo || 'N/A' }}</div>
            </div>
            
            <div class="flex justify-end gap-3 mt-6">
              <button @click="closeChecklistModal"
                      class="px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg">
                Close
              </button>
              <button @click="saveChecklist"
                      class="px-4 py-2 bg-blue-600 text-white hover:bg-blue-700 rounded-lg flex items-center gap-2">
                <i class="fa-solid fa-save"></i> Save Checklist
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'DocumentsView',

  data() {
    return {
      // Navigation
      view: 'categories',
      selectedCategory: null,
      selectedPerson: null,
      selectedCase: null,
      selectedCriminalCase: null,
      
      // Search
      peopleSearch: '',
      caseSearch: '',
      
      // Modals
      showAddPersonModal: false,
      showCaseModal: false,
      showChecklistModal: false,
      showCriminalCaseModal: false,
      showStatusDropdown: false,
      
      // Checklist modal
      showAddTaskForm: false,
      newChecklistItem: {
        name: '',
        date: '',
        completed: false,
        file: null,
        fileName: '',
        fileType: '',
        fileSize: null,
        fileData: null
      },
      
      // Selected items
      editingCase: null,
      editingCriminalCase: null,
      
      // Forms
      personForm: {
        name: '',
        email: '',
        phone: '',
        address: ''
      },
      
      caseForm: {
        crimNo: '',
        caseNumber: '',
        title: '',
        description: '',
        type: '',
        status: 'pending',
        court: '',
        lawyerId: '',
        filingDate: ''
      },
      
      criminalCaseForm: {
        // Person/Client fields
        caseNo: '',
        clientName: '',
        contact: '',
        address: '',
        // Criminal case fields
        criminalCaseNo: '',
        description: '',
        status: 'pending',
        filingDate: ''
      },
      
      // Data structures
      categories: [
        { id: 1, title: 'Civil Cases', icon: 'fa-solid fa-gavel', type: 'civil' },
        { id: 2, title: 'Criminal Cases', icon: 'fa-solid fa-user-secret', type: 'criminal' },
        { id: 3, title: 'Family Cases', icon: 'fa-solid fa-heart', type: 'family' },
        { id: 4, title: 'Corporate Cases', icon: 'fa-solid fa-building', type: 'corporate' },
        { id: 5, title: 'Labor Cases', icon: 'fa-solid fa-briefcase', type: 'labor' }
      ],

      lawyers: [
        { id: 1, name: 'Juan Dela Cruz', email: 'juan@example.com' },
        { id: 2, name: 'Maria Santos', email: 'maria@example.com' },
        { id: 3, name: 'Pedro Reyes', email: 'pedro@example.com' }
      ],
      
      // Main data structure matching Excel format
      data: {
        civil: [],
        criminal: [
          {
            id: 1,
            caseNo: '245',
            name: 'JAY AR MERCADO Y ARTIAGA',
            address: 'Brgy. Mangahan, San Pedro, Laguna',
            contact: '',
            criminalCases: [
              {
                id: 1,
                criminalCaseNo: 'CRIM. CASE NO. R-ANG-24-00879-CR',
                description: 'VIOLATION OF SECTION 11, ART. II OF RA 9165',
                status: 'filed',
                filingDate: '2024-04-12',
                checklist: [
                  { id: 1, name: 'ORDER', date: '2025-08-25', completed: true, file: null, fileName: '', fileType: '', fileSize: null, fileData: null },
                  { id: 2, name: 'ORDER', date: '2025-05-22', completed: true, file: null, fileName: '', fileType: '', fileSize: null, fileData: null },
                  { id: 3, name: 'ORDER', date: '2025-03-13', completed: true, file: null, fileName: '', fileType: '', fileSize: null, fileData: null },
                  { id: 4, name: 'ORDER', date: '2025-03-13', completed: true, file: null, fileName: '', fileType: '', fileSize: null, fileData: null },
                  { id: 5, name: 'ORDER', date: '2025-02-06', completed: true, file: null, fileName: '', fileType: '', fileSize: null, fileData: null },
                  { id: 6, name: 'ORDER', date: '2025-01-23', completed: true, file: null, fileName: '', fileType: '', fileSize: null, fileData: null },
                  { id: 7, name: 'ORDER', date: '2024-08-21', completed: true, file: null, fileName: '', fileType: '', fileSize: null, fileData: null },
                  { id: 8, name: 'TRANSCRIPT', date: '2024-07-23', completed: true, file: null, fileName: '', fileType: '', fileSize: null, fileData: null },
                  { id: 9, name: 'ORDER', date: '2024-04-18', completed: true, file: null, fileName: '', fileType: '', fileSize: null, fileData: null },
                  { id: 10, name: 'ORDER', date: '2024-07-23', completed: true, file: null, fileName: '', fileType: '', fileSize: null, fileData: null },
                  { id: 11, name: 'ORDER', date: '2024-04-25', completed: true, file: null, fileName: '', fileType: '', fileSize: null, fileData: null },
                  { id: 12, name: 'PETITION FOR BAIL', date: '2024-04-18', completed: true, file: null, fileName: '', fileType: '', fileSize: null, fileData: null },
                  { id: 13, name: 'ORDER', date: '2024-04-12', completed: true, file: null, fileName: '', fileType: '', fileSize: null, fileData: null },
                  { id: 14, name: 'ORDER', date: '2024-11-19', completed: true, file: null, fileName: '', fileType: '', fileSize: null, fileData: null },
                  { id: 15, name: 'ORDER', date: '2024-08-20', completed: true, file: null, fileName: '', fileType: '', fileSize: null, fileData: null },
                  { id: 16, name: 'INFORMATION', date: '2024-04-12', completed: true, file: null, fileName: '', fileType: '', fileSize: null, fileData: null },
                  { id: 17, name: 'JUDICIAL AFFIDAVIT OF POSEUR-BUYER', date: '2024-04-04', completed: true, file: null, fileName: '', fileType: '', fileSize: null, fileData: null },
                  { id: 18, name: 'JUDICIAL AFFIDAVIT OF BACKUP', date: '2024-04-04', completed: true, file: null, fileName: '', fileType: '', fileSize: null, fileData: null },
                  { id: 19, name: 'AFFIDAVIT OF INVESTIGADOR', date: '2024-04-04', completed: true, file: null, fileName: '', fileType: '', fileSize: null, fileData: null },
                  { id: 20, name: 'AFFIDAVIT/EXPLANATION', date: '2024-04-04', completed: true, file: null, fileName: '', fileType: '', fileSize: null, fileData: null },
                  { id: 21, name: 'CUSTODIAL INVESTIGATION REPORT', date: '2024-04-04', completed: true, file: null, fileName: '', fileType: '', fileSize: null, fileData: null },
                  { id: 22, name: 'MEMORANDUM', date: '2024-04-04', completed: true, file: null, fileName: '', fileType: '', fileSize: null, fileData: null },
                  { id: 23, name: 'CERTIFICATE OF COORDINATION', date: '2024-04-04', completed: true, file: null, fileName: '', fileType: '', fileSize: null, fileData: null },
                  { id: 24, name: 'COORDINATION FORM', date: '2024-04-04', completed: true, file: null, fileName: '', fileType: '', fileSize: null, fileData: null },
                  { id: 25, name: 'PRE-OPERATION REPORT', date: '2024-04-04', completed: true, file: null, fileName: '', fileType: '', fileSize: null, fileData: null },
                  { id: 26, name: 'INVENTORY OF CONFISCATED/SEIZED ITEMS', date: '2024-04-04', completed: true, file: null, fileName: '', fileType: '', fileSize: null, fileData: null },
                  { id: 27, name: 'CHAIN OF CUSTODY', date: '2024-04-04', completed: true, file: null, fileName: '', fileType: '', fileSize: null, fileData: null },
                  { id: 28, name: 'PS3-INV MEMORANDUM - LABORATORY EXAMINATION', date: '2024-04-05', completed: true, file: null, fileName: '', fileType: '', fileSize: null, fileData: null },
                  { id: 29, name: 'PS3-INV MEMORANDUM - DRUG TEST', date: '2024-04-05', completed: true, file: null, fileName: '', fileType: '', fileSize: null, fileData: null },
                  { id: 30, name: 'CHEMISTRY REPORT', date: '2024-04-05', completed: true, file: null, fileName: '', fileType: '', fileSize: null, fileData: null },
                  { id: 31, name: 'MOTION FOR DESTRUCTION', date: '2024-04-04', completed: true, file: null, fileName: '', fileType: '', fileSize: null, fileData: null },
                  { id: 32, name: 'REFERRAL FORM', date: '2024-04-01', completed: true, file: null, fileName: '', fileType: '', fileSize: null, fileData: null },
                  { id: 33, name: 'MOTION FOR CONSOLIDATION', date: '2024-04-08', completed: true, file: null, fileName: '', fileType: '', fileSize: null, fileData: null },
                  { id: 34, name: 'ORDER', date: '2024-04-12', completed: true, file: null, fileName: '', fileType: '', fileSize: null, fileData: null },
                  { id: 35, name: 'SUBPOENA', date: '2024-04-15', completed: true, file: null, fileName: '', fileType: '', fileSize: null, fileData: null },
                  { id: 36, name: 'INFORMATION', date: '', completed: false, file: null, fileName: '', fileType: '', fileSize: null, fileData: null }
                ]
              },
              {
                id: 2,
                criminalCaseNo: 'CRIM. CASE NO. R-ANG-24-00880-CR',
                description: 'VIOLATION OF SECTION 5, ART. II OF RA 9165',
                status: 'filed',
                filingDate: '2024-04-12',
                checklist: []
              }
            ]
          },
          {
            id: 2,
            caseNo: '239',
            name: 'ANALIZA GUILAS',
            address: '',
            contact: '',
            criminalCases: [
              {
                id: 3,
                criminalCaseNo: '23-0525 & 23-0526',
                description: 'Violation of B.P Blg. 22',
                status: 'pending',
                filingDate: '',
                checklist: []
              }
            ]
          },
          {
            id: 3,
            caseNo: '219',
            name: 'NELSON PAMINTUAN, JANE KRISTINE DUNGCA LUMACAD, ANNA LIZA BUTIU GUILAS',
            address: '',
            contact: '',
            criminalCases: [
              {
                id: 4,
                criminalCaseNo: 'CRIM. CASE. 19-0022',
                description: 'ESTAFA THRU FALSIFICATION OF PUBLIC DOCUMENT',
                status: 'closed',
                filingDate: '2024-05-27',
                checklist: [
                  { id: 1, name: 'ORDER', date: '2024-06-06', completed: true, file: null, fileName: '', fileType: '', fileSize: null, fileData: null },
                  { id: 2, name: 'OMNIBUS ORDER', date: '2024-06-07', completed: true, file: null, fileName: '', fileType: '', fileSize: null, fileData: null },
                  { id: 3, name: 'AMENDED DECISION', date: '2024-05-24', completed: true, file: null, fileName: '', fileType: '', fileSize: null, fileData: null },
                  { id: 4, name: 'DECISION', date: '2024-05-27', completed: true, file: null, fileName: '', fileType: '', fileSize: null, fileData: null },
                  { id: 5, name: 'ORDER', date: '2024-09-27', completed: true, file: null, fileName: '', fileType: '', fileSize: null, fileData: null },
                  { id: 6, name: 'ORDER', date: '2024-09-04', completed: true, file: null, fileName: '', fileType: '', fileSize: null, fileData: null }
                ]
              }
            ]
          }
        ],
        family: [],
        corporate: [],
        labor: []
      }
    }
  },

  computed: {
    selectedCategoryLabel() {
      const labels = {
        civil: 'Civil Cases',
        criminal: 'Criminal Cases',
        family: 'Family Cases',
        corporate: 'Corporate Cases',
        labor: 'Labor Cases'
      }
      return labels[this.selectedCategory] || ''
    },

    currentList() {
      return this.data[this.selectedCategory] || []
    },

    filteredPeople() {
      if (!this.peopleSearch) return this.currentList
      const search = this.peopleSearch.toLowerCase()
      return this.currentList.filter(person => {
        if (this.selectedCategory === 'criminal') {
          return person.name?.toLowerCase().includes(search) ||
                 (person.caseNo && person.caseNo.toLowerCase().includes(search)) ||
                 (person.address && person.address.toLowerCase().includes(search)) ||
                 (person.contact && person.contact.toLowerCase().includes(search))
        } else {
          return person.name?.toLowerCase().includes(search) ||
                 (person.email && person.email.toLowerCase().includes(search)) ||
                 (person.phone && person.phone.toLowerCase().includes(search))
        }
      })
    },

    filteredCriminalCases() {
      if (!this.selectedPerson) return []
      if (this.selectedCategory !== 'criminal') return []
      if (!this.caseSearch) return this.selectedPerson.criminalCases || []
      
      const search = this.caseSearch.toLowerCase()
      return (this.selectedPerson.criminalCases || []).filter(criminalCase => 
        criminalCase.criminalCaseNo?.toLowerCase().includes(search) ||
        criminalCase.description?.toLowerCase().includes(search)
      )
    },

    filteredPersonCases() {
      if (!this.selectedPerson) return []
      if (this.selectedCategory === 'criminal') return []
      if (!this.caseSearch) return this.selectedPerson.cases || []
      
      const search = this.caseSearch.toLowerCase()
      return (this.selectedPerson.cases || []).filter(caseItem => 
        caseItem.caseNo?.toLowerCase().includes(search) ||
        caseItem.title?.toLowerCase().includes(search) ||
        caseItem.description?.toLowerCase().includes(search)
      )
    }
  },

  created() {
    this.loadSampleData()
  },

  methods: {
    loadSampleData() {
      console.log('Sample data loaded')
    },
    
    // Navigation methods
    openCategory(cat) {
      this.selectedCategory = cat
      this.selectedPerson = null
      this.selectedCase = null
      this.selectedCriminalCase = null
      this.view = 'category-details'
      this.peopleSearch = ''
    },

    openPerson(person) {
      this.selectedPerson = person
      this.selectedCriminalCase = null
      this.view = 'cases'
      this.caseSearch = ''
    },

    openCase(caseItem) {
      this.selectedCase = caseItem
      this.view = 'case-details'
    },

    openCriminalCase(criminalCase) {
      this.selectedCriminalCase = criminalCase
      this.view = 'case-details'
    },

    goBack() {
      if (this.view === 'category-details') {
        this.view = 'categories'
      } else if (this.view === 'cases') {
        this.view = 'category-details'
      } else if (this.view === 'case-details') {
        this.view = 'cases'
      }
    },

    reset() {
      this.selectedCategory = null
      this.selectedPerson = null
      this.selectedCase = null
      this.selectedCriminalCase = null
      this.view = 'categories'
      this.peopleSearch = ''
      this.caseSearch = ''
    },

    // Search methods
    searchPeople() {},
    searchCases() {},

    // Person/Cluster Management
    openAddPersonModal() {
      this.personForm = { name: '', email: '', phone: '', address: '' }
      this.showAddPersonModal = true
    },

    closeAddPersonModal() {
      this.showAddPersonModal = false
    },

    savePerson() {
      const newPerson = {
        id: Date.now(),
        ...this.personForm,
        cases: []
      }
      if (this.selectedCategory === 'criminal') {
        newPerson.criminalCases = []
        newPerson.caseNo = ''
        newPerson.contact = this.personForm.phone
      }
      this.data[this.selectedCategory].push(newPerson)
      this.closeAddPersonModal()
    },

    // Case Management
    openAddCaseModal() {
      if (this.selectedCategory === 'criminal') {
        this.editingCriminalCase = null
        this.criminalCaseForm = {
          caseNo: '',
          clientName: '',
          contact: '',
          address: '',
          criminalCaseNo: '',
          description: '',
          status: 'pending',
          filingDate: ''
        }
        this.showCriminalCaseModal = true
      } else {
        this.editingCase = null
        this.caseForm = {
          crimNo: '',
          caseNumber: '',
          title: '',
          description: '',
          type: this.selectedCategory || '',
          status: 'pending',
          court: '',
          lawyerId: '',
          filingDate: ''
        }
        this.showCaseModal = true
      }
    },

    editCase(caseItem) {
      this.editingCase = caseItem
      this.caseForm = {
        crimNo: caseItem.crimNo || '',
        caseNumber: caseItem.caseNo || caseItem.caseNumber,
        title: caseItem.title,
        description: caseItem.description || '',
        type: caseItem.type,
        status: caseItem.status,
        court: caseItem.court || '',
        lawyerId: caseItem.lawyer?.id || '',
        filingDate: caseItem.filingDate || ''
      }
      this.showCaseModal = true
    },

    saveCase() {
      const caseData = {
        id: this.editingCase?.id || Date.now(),
        caseNo: this.caseForm.caseNumber,
        caseNumber: this.caseForm.caseNumber,
        crimNo: this.caseForm.crimNo,
        title: this.caseForm.title,
        description: this.caseForm.description,
        type: this.caseForm.type,
        status: this.caseForm.status,
        court: this.caseForm.court,
        lawyer: this.lawyers.find(l => l.id === this.caseForm.lawyerId) || null,
        client: { 
          name: this.selectedPerson?.name, 
          phone: this.selectedPerson?.phone, 
          email: this.selectedPerson?.email 
        },
        filingDate: this.caseForm.filingDate,
        createdAt: this.editingCase?.createdAt || new Date().toISOString().split('T')[0],
        checklist: this.editingCase?.checklist || [],
        notes: this.editingCase?.notes || []
      }
      
      if (this.editingCase) {
        const index = this.selectedPerson.cases.findIndex(c => c.id === this.editingCase.id)
        if (index !== -1) {
          this.selectedPerson.cases[index] = caseData
        }
        this.selectedCase = caseData
      } else {
        if (!this.selectedPerson.cases) {
          this.selectedPerson.cases = []
        }
        this.selectedPerson.cases.push(caseData)
      }
      this.closeCaseModal()
    },

    closeCaseModal() {
      this.showCaseModal = false
      this.editingCase = null
    },

    // Criminal Case Management
    editCriminalCase(criminalCase) {
      this.editingCriminalCase = criminalCase
      this.criminalCaseForm = {
        caseNo: this.selectedPerson?.caseNo || '',
        clientName: this.selectedPerson?.name || '',
        contact: this.selectedPerson?.contact || '',
        address: this.selectedPerson?.address || '',
        criminalCaseNo: criminalCase.criminalCaseNo,
        description: criminalCase.description,
        status: criminalCase.status,
        filingDate: criminalCase.filingDate || ''
      }
      this.showCriminalCaseModal = true
    },

    saveCriminalCase() {
      const criminalCaseData = {
        id: this.editingCriminalCase?.id || Date.now(),
        criminalCaseNo: this.criminalCaseForm.criminalCaseNo,
        description: this.criminalCaseForm.description,
        status: this.criminalCaseForm.status,
        filingDate: this.criminalCaseForm.filingDate,
        checklist: this.editingCriminalCase?.checklist || []
      }
      
      if (this.editingCriminalCase) {
        // Update existing criminal case
        const index = this.selectedPerson.criminalCases.findIndex(c => c.id === this.editingCriminalCase.id)
        if (index !== -1) {
          this.selectedPerson.criminalCases[index] = criminalCaseData
        }
        this.selectedCriminalCase = criminalCaseData
      } else {
        // Check if person already exists
        let person = this.data.criminal.find(p => p.caseNo === this.criminalCaseForm.caseNo)
        
        if (!person) {
          // Create new person
          person = {
            id: Date.now(),
            caseNo: this.criminalCaseForm.caseNo,
            name: this.criminalCaseForm.clientName,
            contact: this.criminalCaseForm.contact,
            address: this.criminalCaseForm.address,
            criminalCases: []
          }
          this.data.criminal.push(person)
        }
        
        // Add criminal case to person
        if (!person.criminalCases) {
          person.criminalCases = []
        }
        person.criminalCases.push(criminalCaseData)
        
        // If we're currently viewing this category, update selectedPerson
        if (this.selectedCategory === 'criminal' && this.selectedPerson?.caseNo === person.caseNo) {
          this.selectedPerson = person
        }
      }
      
      this.showCriminalCaseModal = false
      this.editingCriminalCase = null
      
      // Reset form
      this.criminalCaseForm = {
        caseNo: '',
        clientName: '',
        contact: '',
        address: '',
        criminalCaseNo: '',
        description: '',
        status: 'pending',
        filingDate: ''
      }
    },

    closeCriminalCaseModal() {
      this.showCriminalCaseModal = false
      this.editingCriminalCase = null
    },

    // Status Management
    updateStatus(criminalCase, newStatus) {
      if (criminalCase) {
        criminalCase.status = newStatus
      }
    },

    // Checklist Management Methods
    openChecklistModal(criminalCase) {
      this.selectedCriminalCase = JSON.parse(JSON.stringify(criminalCase))
      this.showChecklistModal = true
      this.showAddTaskForm = false
      this.resetNewChecklistItem()
    },

    addChecklistItem() {
      this.showAddTaskForm = true
      this.resetNewChecklistItem()
    },

    cancelAddChecklistItem() {
      this.showAddTaskForm = false
      this.resetNewChecklistItem()
    },

    resetNewChecklistItem() {
      this.newChecklistItem = {
        name: '',
        date: '',
        completed: false,
        file: null,
        fileName: '',
        fileType: '',
        fileSize: null,
        fileData: null
      }
    },

    handleFileUpload(event) {
      const file = event.target.files[0]
      if (file) {
        // Check file size (max 10MB)
        if (file.size > 10 * 1024 * 1024) {
          alert('File size must be less than 10MB')
          return
        }
        
        const reader = new FileReader()
        reader.onload = (e) => {
          this.newChecklistItem.fileData = e.target.result
          this.newChecklistItem.file = file
          this.newChecklistItem.fileName = file.name
          this.newChecklistItem.fileType = file.type
          this.newChecklistItem.fileSize = file.size
        }
        reader.readAsDataURL(file)
      }
    },

    attachFileToItem(event, item) {
      const file = event.target.files[0]
      if (file) {
        // Check file size (max 10MB)
        if (file.size > 10 * 1024 * 1024) {
          alert('File size must be less than 10MB')
          return
        }
        
        const reader = new FileReader()
        reader.onload = (e) => {
          item.fileData = e.target.result
          item.file = file
          item.fileName = file.name
          item.fileType = file.type
          item.fileSize = file.size
        }
        reader.readAsDataURL(file)
      }
    },

    removeFile(item) {
      item.file = null
      item.fileName = ''
      item.fileType = ''
      item.fileSize = null
      item.fileData = null
    },

    clearNewFile() {
      this.newChecklistItem.file = null
      this.newChecklistItem.fileName = ''
      this.newChecklistItem.fileType = ''
      this.newChecklistItem.fileSize = null
      this.newChecklistItem.fileData = null
    },

    downloadFile(item) {
      if (item.fileData) {
        const link = document.createElement('a')
        link.href = item.fileData
        link.download = item.fileName || 'download'
        document.body.appendChild(link)
        link.click()
        document.body.removeChild(link)
      }
    },

    viewFile(item) {
      if (item.fileData) {
        const fileType = item.fileType || ''
        if (fileType.includes('pdf') || fileType.includes('image')) {
          window.open(item.fileData, '_blank')
        } else {
          // For DOC files, download instead
          this.downloadFile(item)
        }
      }
    },

    getFileIcon(fileType) {
      if (!fileType) return 'fa-solid fa-file'
      if (fileType.includes('pdf')) return 'fa-solid fa-file-pdf text-red-500'
      if (fileType.includes('image')) return 'fa-solid fa-file-image text-blue-500'
      if (fileType.includes('word') || fileType.includes('doc')) return 'fa-solid fa-file-word text-blue-700'
      return 'fa-solid fa-file text-gray-500'
    },

    formatFileSize(bytes) {
      if (!bytes) return '0 B'
      const k = 1024
      const sizes = ['B', 'KB', 'MB', 'GB']
      const i = Math.floor(Math.log(bytes) / Math.log(k))
      return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
    },

    saveNewChecklistItem() {
      if (!this.newChecklistItem.name) {
        alert('Please enter a task name')
        return
      }
      
      if (!this.selectedCriminalCase.checklist) {
        this.selectedCriminalCase.checklist = []
      }
      
      const newItem = {
        id: Date.now(),
        name: this.newChecklistItem.name,
        date: this.newChecklistItem.date || '',
        completed: this.newChecklistItem.completed || false,
        file: this.newChecklistItem.file,
        fileName: this.newChecklistItem.fileName,
        fileType: this.newChecklistItem.fileType,
        fileSize: this.newChecklistItem.fileSize,
        fileData: this.newChecklistItem.fileData
      }
      
      this.selectedCriminalCase.checklist.push(newItem)
      this.showAddTaskForm = false
      this.resetNewChecklistItem()
    },

    toggleChecklistItem(item) {
      item.completed = !item.completed
    },

    deleteChecklistItem(index) {
      if (confirm('Are you sure you want to delete this checklist item?')) {
        this.selectedCriminalCase.checklist.splice(index, 1)
      }
    },

    saveChecklist() {
      if (this.selectedPerson) {
        const caseIndex = this.selectedPerson.criminalCases.findIndex(c => 
          c.id === this.selectedCriminalCase.id
        )
        if (caseIndex !== -1) {
          this.selectedPerson.criminalCases[caseIndex].checklist = this.selectedCriminalCase.checklist
        }
      }
      this.closeChecklistModal()
    },

    closeChecklistModal() {
      this.showChecklistModal = false
      this.showAddTaskForm = false
      this.selectedCriminalCase = null
      this.resetNewChecklistItem()
    },

    // Utility Methods
    getCategoryCount(type) {
      const persons = this.data[type] || []
      if (type === 'criminal') {
        return persons.reduce((total, person) => total + (person.criminalCases?.length || 0), 0)
      } else {
        return persons.reduce((total, person) => total + (person.cases?.length || 0), 0)
      }
    },

    getStatusLabel(status) {
      const labels = {
        pending: 'Pending',
        for_filing: 'For Filing',
        incomplete: 'Incomplete',
        filed: 'Filed Cases',
        closed: 'Case Closed'
      }
      return labels[status] || status
    },

    getStatusClass(status) {
      const classes = {
        pending: 'bg-yellow-100 text-yellow-800',
        for_filing: 'bg-orange-100 text-orange-800',
        incomplete: 'bg-red-100 text-red-800',
        filed: 'bg-blue-100 text-blue-800',
        closed: 'bg-green-100 text-green-800'
      }
      return classes[status] || 'bg-gray-100 text-gray-800'
    },

    getCaseTypeClass(type) {
      const classes = {
        civil: 'bg-blue-100 text-blue-800',
        criminal: 'bg-red-100 text-red-800',
        family: 'bg-pink-100 text-pink-800',
        corporate: 'bg-purple-100 text-purple-800',
        labor: 'bg-indigo-100 text-indigo-800'
      }
      return classes[type] || 'bg-gray-100 text-gray-800'
    },

    getStatusColor(status) {
      const colors = {
        pending: 'bg-yellow-100',
        for_filing: 'bg-orange-100',
        incomplete: 'bg-red-100',
        filed: 'bg-blue-100',
        closed: 'bg-green-100'
      }
      return colors[status] || 'bg-gray-100'
    },

    getStatusIconColor(status) {
      const colors = {
        pending: 'text-yellow-600',
        for_filing: 'text-orange-600',
        incomplete: 'text-red-600',
        filed: 'text-blue-600',
        closed: 'text-green-600'
      }
      return colors[status] || 'text-gray-600'
    },

    getCaseIcon(type) {
      const icons = {
        civil: 'fa-solid fa-landmark',
        criminal: 'fa-solid fa-handcuffs',
        family: 'fa-solid fa-heart',
        corporate: 'fa-solid fa-building',
        labor: 'fa-solid fa-briefcase'
      }
      return icons[type] || 'fa-solid fa-file'
    },

    formatDate(dateString) {
      if (!dateString) return 'N/A'
      const options = { year: 'numeric', month: 'short', day: 'numeric' }
      return new Date(dateString).toLocaleDateString('en-US', options)
    },

    getCompletedTasks(criminalCase) {
      if (!criminalCase?.checklist) return 0
      return criminalCase.checklist.filter(item => item.completed).length
    },

    getPendingChecklists(criminalCase) {
      if (!criminalCase?.checklist) return 0
      return criminalCase.checklist.filter(item => !item.completed).length
    }
  }
}
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.item {
  background: white;
  border-radius: 12px;
  cursor: pointer;
  box-shadow: 0 2px 6px rgba(0,0,0,0.05);
  transition: all 0.2s ease;
}

.item:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>