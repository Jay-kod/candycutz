<script setup>
import { ref, onMounted, computed } from 'vue'
import AdminLayout from '../layouts/AdminLayout.vue'
import { adminApi } from '../api/admin.api'
import { 
  ClipboardDocumentListIcon, 
  ArrowPathIcon,
  ShieldCheckIcon,
  ExclamationCircleIcon,
  UserIcon,
  CreditCardIcon,
  ArrowRightOnRectangleIcon,
  MagnifyingGlassIcon,
  FunnelIcon
} from '@heroicons/vue/24/outline'

const logs = ref([])
const loading = ref(true)
const search = ref('')
const filterType = ref('all')

const fetchLogs = async () => {
  loading.value = true
  try {
    const res = await adminApi.logs()
    logs.value = res.data.data || []
  } catch (err) {
    console.error('Failed to load logs:', err)
  } finally {
    loading.value = false
  }
}

const filteredLogs = computed(() => {
  let result = logs.value
  
  if (filterType.value !== 'all') {
    result = result.filter(log => log.entity_type === filterType.value || log.action === filterType.value)
  }
  
  if (search.value) {
    const q = search.value.toLowerCase()
    result = result.filter(log => 
      (log.user_name || '').toLowerCase().includes(q) ||
      (log.action || '').toLowerCase().includes(q) ||
      (log.entity_type || '').toLowerCase().includes(q) ||
      (log.ip_address || '').toLowerCase().includes(q)
    )
  }
  
  return result
})

const getIconForAction = (action) => {
  if (action === 'login') return ArrowRightOnRectangleIcon
  if (action === 'verify_payment' || action === 'reject_payment') return CreditCardIcon
  if (action.includes('error') || action.includes('failed')) return ExclamationCircleIcon
  if (action.includes('admin') || action.includes('system')) return ShieldCheckIcon
  return UserIcon
}

const getIconColorForAction = (action) => {
  if (action === 'login') return 'text-blue-400 bg-blue-500/10 border-blue-500/20'
  if (action === 'verify_payment') return 'text-emerald-400 bg-emerald-500/10 border-emerald-500/20'
  if (action === 'reject_payment') return 'text-red-400 bg-red-500/10 border-red-500/20'
  if (action.includes('approve')) return 'text-emerald-400 bg-emerald-500/10 border-emerald-500/20'
  if (action.includes('cancel') || action.includes('delete')) return 'text-red-400 bg-red-500/10 border-red-500/20'
  return 'text-admin bg-admin/10 border-admin/20'
}

onMounted(() => {
  fetchLogs()
})
</script>

<template>
  <AdminLayout>
    <div class="space-y-6 animate-fade-in">
      <!-- Header Banner -->
      <div class="relative overflow-hidden rounded-3xl border border-admin/20 bg-gradient-to-br from-obsidian via-charcoal to-[#2A1100] p-8 shadow-2xl flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="absolute -right-16 -top-16 h-64 w-64 rounded-full bg-admin/5 blur-3xl pointer-events-none"></div>
        <div class="absolute -left-16 -bottom-16 h-48 w-48 rounded-full bg-admin/5 blur-3xl pointer-events-none"></div>
        
        <div class="relative z-10">
          <p class="text-xs uppercase tracking-[0.3em] text-admin/70 font-bold">Audit System</p>
          <h1 class="mt-2 font-display text-4xl text-theme-text drop-shadow-lg flex items-center gap-3">
            System <span class="text-transparent bg-clip-text bg-gradient-to-r from-admin to-admin-light">Logs</span>
            <span class="flex items-center justify-center h-8 px-3 rounded-full bg-admin/20 border border-admin/30 text-lg text-admin-light">{{ filteredLogs.length }}</span>
          </h1>
          <p class="mt-2 text-sm text-ivory/60">Track all admin actions, logins, and system events.</p>
        </div>
        
        <button @click="fetchLogs" class="relative z-10 flex items-center justify-center gap-2 rounded-xl bg-admin/10 border border-admin/20 text-admin-light px-5 py-3 text-sm font-bold hover:bg-admin/20 transition-all active:scale-[0.98] shrink-0">
          <ArrowPathIcon class="w-4 h-4" :class="{ 'animate-spin': loading }" />
          Refresh
        </button>
      </div>

      <!-- Table Container -->
      <div class="rounded-2xl border border-admin/10 bg-black/20 backdrop-blur-sm overflow-hidden flex flex-col">
        <!-- Filters Bar -->
        <div class="border-b border-white/5 p-5 flex flex-col sm:flex-row gap-4 justify-between items-center bg-white/[0.02]">
          <div class="flex items-center gap-3">
            <div class="flex items-center gap-2 rounded-xl bg-white/[0.03] border border-white/10 px-3 py-2">
              <FunnelIcon class="h-4 w-4 text-admin/60 shrink-0" />
              <select v-model="filterType" class="bg-transparent text-ivory/80 text-sm outline-none cursor-pointer">
                <option value="all" class="bg-obsidian">All Actions</option>
                <option value="login" class="bg-obsidian">Logins</option>
                <option value="payment" class="bg-obsidian">Payments</option>
                <option value="user" class="bg-obsidian">Users</option>
              </select>
            </div>
          </div>
          <div class="flex items-center gap-2 rounded-xl bg-white/[0.03] border border-white/10 px-3 py-2 w-full sm:w-72">
            <MagnifyingGlassIcon class="h-4 w-4 text-ivory/40 shrink-0" />
            <input 
              v-model="search" 
              type="text" 
              placeholder="Search logs..." 
              class="bg-transparent text-ivory/80 text-sm outline-none w-full placeholder-ivory/30"
            />
          </div>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="border-b border-white/5 bg-white/[0.02]">
                <th class="p-4 text-[10px] font-bold uppercase tracking-widest text-ivory/40">Time</th>
                <th class="p-4 text-[10px] font-bold uppercase tracking-widest text-ivory/40">User</th>
                <th class="p-4 text-[10px] font-bold uppercase tracking-widest text-ivory/40">Action</th>
                <th class="p-4 text-[10px] font-bold uppercase tracking-widest text-ivory/40">Entity</th>
                <th class="p-4 text-[10px] font-bold uppercase tracking-widest text-ivory/40">IP Address</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-white/[0.03]">
              <tr v-if="loading">
                <td colspan="5" class="p-12 text-center">
                  <div class="flex flex-col items-center gap-3">
                    <ArrowPathIcon class="w-6 h-6 animate-spin text-admin/50" />
                    <span class="text-sm text-ivory/50">Loading audit logs...</span>
                  </div>
                </td>
              </tr>
              <tr v-else-if="filteredLogs.length === 0">
                <td colspan="5" class="p-16 text-center">
                  <ClipboardDocumentListIcon class="w-10 h-10 mx-auto mb-3 text-ivory/20" />
                  <p class="text-ivory/60 font-medium">No logs found</p>
                  <p class="text-sm text-ivory/40 mt-1">Try adjusting your filters or search query.</p>
                </td>
              </tr>
              <tr v-for="log in filteredLogs" :key="log.id" class="hover:bg-white/[0.03] transition-colors group">
                <td class="p-4 text-sm text-ivory/50 whitespace-nowrap">
                  {{ new Date(log.created_at).toLocaleString() }}
                </td>
                <td class="p-4">
                  <div class="flex items-center gap-3">
                    <div class="h-8 w-8 rounded-lg bg-admin/10 border border-admin/20 flex items-center justify-center text-admin text-xs font-bold shrink-0">
                      {{ (log.user_name || '?').charAt(0).toUpperCase() }}
                    </div>
                    <div class="flex flex-col">
                      <span class="text-sm font-medium text-theme-text">{{ log.user_name || 'System' }}</span>
                      <span class="text-[10px] text-ivory/40 uppercase tracking-wider">{{ log.user_role || 'N/A' }}</span>
                    </div>
                  </div>
                </td>
                <td class="p-4">
                  <div class="flex items-center gap-2">
                    <div class="p-1.5 rounded-lg shrink-0 border" :class="getIconColorForAction(log.action)">
                      <component :is="getIconForAction(log.action)" class="w-3.5 h-3.5" />
                    </div>
                    <span class="text-sm font-medium text-theme-text capitalize">{{ log.action.replace(/_/g, ' ') }}</span>
                  </div>
                </td>
                <td class="p-4">
                  <span class="text-xs uppercase tracking-wider bg-white/[0.03] border border-white/10 px-2.5 py-1 rounded-lg text-ivory/50 font-medium">
                    {{ log.entity_type }} <span v-if="log.entity_id" class="text-admin/60">#{{ log.entity_id }}</span>
                  </span>
                </td>
                <td class="p-4 text-sm font-mono text-ivory/40">
                  {{ log.ip_address || 'N/A' }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
