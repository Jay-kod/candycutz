<template>
  <AdminLayout>
    <section class="space-y-6 animate-fade-in pb-10">
      <!-- Premium Header Banner -->
      <div class="relative overflow-hidden rounded-[2rem] border border-white/[0.05] bg-[#111111]/90 p-8 lg:p-10 shadow-2xl flex flex-col md:flex-row md:items-end justify-between gap-6 backdrop-blur-3xl">
        <div class="absolute -right-20 -top-20 h-80 w-80 rounded-full bg-admin/10 blur-[100px]"></div>
        <div class="absolute -bottom-24 -left-24 h-56 w-56 rounded-full bg-blue-500/10 blur-[80px]"></div>
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(255,103,0,0.05),transparent_60%)]"></div>
        
        <div class="relative z-10">
          <div class="flex items-center gap-3 mb-2">
            <span class="flex h-6 w-6 items-center justify-center rounded-md bg-admin/20 text-admin border border-admin/30">
              <UserGroupIcon class="h-3.5 w-3.5" />
            </span>
            <p class="text-[10px] uppercase tracking-[0.3em] text-admin/80 font-bold">User Management</p>
          </div>
          <h1 class="font-display text-4xl lg:text-5xl text-white drop-shadow-md leading-tight">
            Customer <span class="text-transparent bg-clip-text bg-gradient-to-r from-admin to-amber-400">Directory</span>
          </h1>
          <p class="mt-3 text-sm text-white/40 max-w-lg leading-relaxed">
            Manage your client base, view booking histories, track lifetime value, and identify top spenders.
          </p>
        </div>
        
        <div class="relative z-10 flex flex-col sm:flex-row items-center gap-4 shrink-0 w-full md:w-auto">
          <div class="relative w-full sm:w-72">
            <MagnifyingGlassIcon class="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-white/30" />
            <input
              v-model="search"
              type="text"
              placeholder="Search by name, email, or phone..."
              class="w-full bg-white/[0.03] border border-white/[0.08] text-sm text-white rounded-2xl py-3.5 pl-11 pr-4 outline-none transition-all focus:border-admin/50 focus:bg-white/[0.05] focus:ring-4 focus:ring-admin/10 placeholder:text-white/20"
            />
          </div>
        </div>
      </div>

      <!-- Filters & Stats -->
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <!-- Filter Tabs -->
        <div class="flex items-center gap-2 overflow-x-auto custom-scrollbar pb-2 md:pb-0 hide-scrollbar">
          <button 
            v-for="tab in tabs" 
            :key="tab.value"
            @click="currentFilter = tab.value"
            class="relative px-5 py-2.5 rounded-xl text-xs font-bold uppercase tracking-wider transition-all whitespace-nowrap overflow-hidden"
            :class="currentFilter === tab.value ? 'text-white' : 'text-white/40 hover:text-white hover:bg-white/5'"
          >
            <div v-if="currentFilter === tab.value" class="absolute inset-0 bg-white/10 border border-white/20 rounded-xl"></div>
            <span class="relative z-10 flex items-center gap-2">
              <component :is="tab.icon" v-if="tab.icon" class="h-4 w-4" :class="currentFilter === tab.value ? 'text-admin' : ''" />
              {{ tab.label }}
              <span v-if="tab.count !== undefined" class="flex items-center justify-center h-5 px-1.5 rounded-md bg-black/40 text-[10px] tabular-nums" :class="currentFilter === tab.value ? 'text-white' : 'text-white/40'">
                {{ tab.count }}
              </span>
            </span>
          </button>
        </div>
        
        <div class="text-xs font-semibold text-white/30 tracking-wider uppercase">
          Total Base: <span class="text-white">{{ customers.length }}</span>
        </div>
      </div>

      <!-- Main Content Area -->
      <div class="rounded-[2rem] border border-white/[0.05] bg-[#1a1a1a]/80 backdrop-blur-2xl shadow-2xl overflow-hidden min-h-[400px]">
        
        <!-- Loading Skeleton -->
        <div v-if="loading" class="divide-y divide-white/[0.02]">
          <div v-for="i in 6" :key="i" class="p-6 grid grid-cols-1 md:grid-cols-[2fr_1.5fr_1fr_1fr_auto] gap-6 items-center animate-pulse">
            <div class="flex items-center gap-4">
              <div class="h-12 w-12 rounded-full bg-white/[0.03]"></div>
              <div class="space-y-2"><div class="h-4 w-32 bg-white/[0.03] rounded"></div><div class="h-3 w-20 bg-white/[0.02] rounded"></div></div>
            </div>
            <div class="space-y-2 hidden md:block"><div class="h-4 w-24 bg-white/[0.03] rounded"></div><div class="h-3 w-32 bg-white/[0.02] rounded"></div></div>
            <div class="h-6 w-16 bg-white/[0.03] rounded-md hidden md:block"></div>
            <div class="h-6 w-20 bg-white/[0.03] rounded-md hidden md:block"></div>
            <div class="h-8 w-24 bg-white/[0.03] rounded-xl justify-self-end"></div>
          </div>
        </div>

        <!-- Customers List -->
        <template v-else>
          <div v-if="filteredCustomers.length > 0" class="divide-y divide-white/[0.04]">
            <!-- Headers -->
            <div class="hidden md:grid grid-cols-[2fr_1.5fr_1fr_1fr_auto] gap-6 px-8 py-4 text-[10px] uppercase tracking-[0.2em] text-white/30 font-bold bg-black/40">
              <span>Customer</span>
              <span>Contact</span>
              <span>Activity</span>
              <span>Lifetime Value</span>
              <span class="text-right">Actions</span>
            </div>

            <div
              v-for="customer in filteredCustomers"
              :key="customer.id"
              class="group relative grid grid-cols-1 md:grid-cols-[2fr_1.5fr_1fr_1fr_auto] items-center gap-4 md:gap-6 px-6 md:px-8 py-5 transition-all hover:bg-white/[0.02]"
            >
              <!-- Hover indicator line -->
              <div class="absolute left-0 top-0 bottom-0 w-1 bg-gradient-to-b from-admin to-amber-500 opacity-0 group-hover:opacity-100 transition-opacity"></div>

              <!-- Customer Profile -->
              <div class="flex items-center gap-4">
                <div class="relative flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-white/10 to-white/5 border border-white/10 text-white font-bold text-sm shadow-inner overflow-hidden group-hover:scale-105 transition-transform">
                  <img v-if="customer.avatar" :src="customer.avatar" alt="Avatar" class="h-full w-full object-cover" />
                  <span v-else>{{ initials(customer.name) }}</span>
                  <div v-if="isNewClient(customer)" class="absolute -top-1 -right-1 h-3 w-3 rounded-full bg-admin border-2 border-[#1a1a1a]"></div>
                </div>
                <div>
                  <p class="text-sm font-bold text-white mb-0.5">{{ customer.name }}</p>
                  <p class="text-[11px] font-mono text-white/40 flex items-center gap-1.5">
                    <CalendarDaysIcon class="h-3 w-3" />
                    Joined {{ formatDate(customer.created_at) }}
                  </p>
                </div>
              </div>

              <!-- Contact Info -->
              <div class="hidden md:block">
                <p class="text-sm text-white/80 font-medium mb-1 truncate">{{ customer.email || '—' }}</p>
                <div class="flex items-center gap-2">
                  <span class="text-[11px] font-mono text-white/40 bg-white/5 px-2 py-0.5 rounded-md">
                    {{ customer.phone || 'No phone' }}
                  </span>
                </div>
              </div>

              <!-- Activity (Bookings & No-shows) -->
              <div class="hidden md:flex flex-col items-start gap-1">
                <span class="inline-flex items-center gap-1.5 rounded-md bg-white/5 px-2 py-1 text-xs font-bold text-white/70">
                  <ChartBarIcon class="h-3.5 w-3.5 text-blue-400" />
                  {{ customer.total_bookings || 0 }} Bookings
                </span>
                <span v-if="(customer.no_shows || 0) > 0" class="text-[10px] text-red-400/80 font-bold px-1 uppercase tracking-wider">
                  {{ customer.no_shows }} No-shows
                </span>
              </div>

              <!-- Lifetime Value (Spent) -->
              <div class="hidden md:flex flex-col items-start gap-1">
                <span class="inline-flex items-center gap-1.5 rounded-md bg-emerald-500/10 border border-emerald-500/20 px-2.5 py-1 text-sm font-bold text-emerald-400">
                  ₦{{ (customer.total_spent || 0).toLocaleString() }}
                </span>
                <span v-if="customer.last_visit" class="text-[10px] text-white/30 font-medium px-1 mt-0.5">
                  Last visit: {{ timeAgo(customer.last_visit) }}
                </span>
              </div>

              <!-- Mobile View Extra Info -->
              <div class="md:hidden mt-2 p-3 rounded-xl bg-white/[0.03] border border-white/[0.05] grid grid-cols-2 gap-2">
                <div>
                  <p class="text-[9px] uppercase text-white/30 tracking-wider mb-0.5">Bookings</p>
                  <p class="text-xs font-bold text-white/70">{{ customer.total_bookings || 0 }}</p>
                </div>
                <div>
                  <p class="text-[9px] uppercase text-white/30 tracking-wider mb-0.5">Spent</p>
                  <p class="text-xs font-bold text-emerald-400">₦{{ (customer.total_spent || 0).toLocaleString() }}</p>
                </div>
                <div class="col-span-2 mt-1">
                  <p class="text-[10px] text-white/40 break-all">{{ customer.email || customer.phone || 'No contact info' }}</p>
                </div>
              </div>

              <!-- Actions -->
              <div class="flex items-center justify-end gap-2 mt-3 md:mt-0">
                <button
                  class="group/btn flex items-center gap-1.5 rounded-xl bg-white/5 border border-white/10 px-4 py-2 text-xs font-bold text-white/60 hover:bg-white/10 hover:text-white transition-all shadow-lg hover:shadow-white/5"
                  title="Coming soon: View full customer profile and history"
                >
                  <DocumentTextIcon class="h-4 w-4" />
                  View Profile
                </button>
              </div>
            </div>
          </div>

          <!-- Empty State -->
          <div v-else class="flex flex-col items-center justify-center py-24 px-6 text-center">
            <div class="relative flex h-20 w-20 items-center justify-center rounded-full bg-white/[0.02] border border-white/[0.05] mb-6">
              <div class="absolute inset-0 rounded-full border border-dashed border-white/10 animate-[spin_10s_linear_infinite]"></div>
              <UserGroupIcon class="h-8 w-8 text-white/20" />
            </div>
            <h3 class="text-xl font-bold text-white mb-2">No Customers Found</h3>
            <p class="text-sm text-white/40 max-w-sm mx-auto">
              {{ search ? `No results match your search "${search}".` : `There are no customers matching the selected criteria.` }}
            </p>
            <button v-if="search || currentFilter !== 'all'" @click="resetFilters" class="mt-6 text-admin hover:text-admin-light text-sm font-semibold transition-colors">
              Clear filters
            </button>
          </div>
        </template>
      </div>
    </section>
  </AdminLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import AdminLayout from '../layouts/AdminLayout.vue';
import { adminApi } from '../api/admin.api';
import { useToast } from '../../../core/composables/useToast';
import { 
  UserGroupIcon,
  MagnifyingGlassIcon,
  CalendarDaysIcon,
  ChartBarIcon,
  DocumentTextIcon,
  TrophyIcon,
  SparklesIcon
} from '@heroicons/vue/24/outline';

const toast = useToast();

const customers = ref([]);
const loading = ref(true);
const search = ref('');
const currentFilter = ref('all');

// Sort customers based on filter
const filteredCustomers = computed(() => {
  const list = customers.value || [];
  if (!Array.isArray(list)) return [];
  
  let result = [...list];
  
  // Apply Search
  if (search.value) {
    const q = search.value.toLowerCase();
    result = result.filter(c => {
      const name = c.name || '';
      const email = c.email || '';
      const phone = c.phone || '';
      return name.toLowerCase().includes(q) || 
             email.toLowerCase().includes(q) || 
             phone.includes(q);
    });
  }
  
  // Apply Sorting Filters
  if (currentFilter.value === 'top_spenders') {
    result.sort((a, b) => (b.total_spent || 0) - (a.total_spent || 0));
  } else if (currentFilter.value === 'most_active') {
    result.sort((a, b) => (b.total_bookings || 0) - (a.total_bookings || 0));
  } else if (currentFilter.value === 'newest') {
    result.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
  }
  
  return result;
});

const tabs = computed(() => {
  return [
    { label: 'All Customers', value: 'all', count: customers.value.length },
    { label: 'Top Spenders', value: 'top_spenders', icon: TrophyIcon },
    { label: 'Most Active', value: 'most_active', icon: SparklesIcon },
    { label: 'Newest', value: 'newest' }
  ];
});

function initials(name) {
  return (name || 'U').split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
}

function formatDate(dateStr) {
  if (!dateStr) return '';
  return new Date(dateStr).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
}

function timeAgo(dateStr) {
  if (!dateStr) return 'Never';
  const now = new Date();
  const then = new Date(dateStr);
  const diff = Math.floor((now - then) / 1000);
  if (diff < 86400) return 'Today';
  if (diff < 172800) return 'Yesterday';
  if (diff < 604800) return `${Math.floor(diff / 86400)} days ago`;
  if (diff < 2592000) return `${Math.floor(diff / 604800)} weeks ago`;
  return formatDate(dateStr);
}

function isNewClient(customer) {
  if (!customer.created_at) return false;
  const created = new Date(customer.created_at);
  const now = new Date();
  return (now - created) / (1000 * 60 * 60 * 24) < 7;
}

function resetFilters() {
  search.value = '';
  currentFilter.value = 'all';
}

async function loadCustomers() {
  loading.value = true;
  try {
    const response = await adminApi.customers();
    customers.value = response.data.data || [];
  } catch (err) {
    console.error(err);
    toast.error('Failed to load customers');
  } finally {
    loading.value = false;
  }
}

onMounted(() => {
  loadCustomers();
});
</script>

<style scoped>
.animate-fade-in {
  animation: fadeIn 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(12px); }
  to { opacity: 1; transform: translateY(0); }
}

/* Custom hide scrollbar */
.hide-scrollbar::-webkit-scrollbar {
  display: none;
}
.hide-scrollbar {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
</style>
