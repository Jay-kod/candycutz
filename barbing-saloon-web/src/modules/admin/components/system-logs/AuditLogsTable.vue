<template>
  <div class="rounded-2xl border border-white/[0.08] bg-[#12161C]/90 backdrop-blur-xl shadow-xl overflow-hidden flex flex-col">
    <!-- Filter & Search Controls -->
    <div class="border-b border-white/[0.06] p-4 flex flex-col sm:flex-row gap-3.5 justify-between items-center bg-[#0B0F13]/60">
      <div class="flex items-center gap-2 rounded-xl bg-white/[0.03] border border-white/10 px-3 py-1.5 w-full sm:w-auto">
        <FunnelIcon class="h-3.5 w-3.5 text-[#FF6700] shrink-0" />
        <select
          :value="filterType"
          @change="$emit('update:filterType', $event.target.value)"
          class="bg-transparent text-white/80 text-xs outline-none cursor-pointer w-full sm:w-auto"
        >
          <option value="all" class="bg-[#12161C]">All Action Types</option>
          <option value="login" class="bg-[#12161C]">Logins</option>
          <option value="appointment" class="bg-[#12161C]">Appointments</option>
          <option value="payment" class="bg-[#12161C]">Payments</option>
          <option value="user" class="bg-[#12161C]">Users</option>
          <option value="barber" class="bg-[#12161C]">Barbers</option>
          <option value="service" class="bg-[#12161C]">Services</option>
          <option value="blog" class="bg-[#12161C]">Blog Posts</option>
          <option value="system" class="bg-[#12161C]">System Operations</option>
        </select>
      </div>

      <div class="flex items-center gap-2 rounded-xl bg-white/[0.03] border border-white/10 px-3 py-1.5 w-full sm:w-72">
        <MagnifyingGlassIcon class="h-3.5 w-3.5 text-white/40 shrink-0" />
        <input
          :value="search"
          @input="$emit('update:search', $event.target.value)"
          type="text"
          placeholder="Search user, action, entity, IP..."
          class="bg-transparent text-white text-xs outline-none w-full placeholder:text-white/30"
        />
        <button
          v-if="search"
          type="button"
          @click="$emit('update:search', '')"
          class="text-white/40 hover:text-white text-xs"
        >
          ✕
        </button>
      </div>
    </div>

    <!-- Audit Events Table -->
    <div class="overflow-x-auto">
      <table class="w-full text-left border-collapse">
        <thead>
          <tr class="border-b border-white/[0.06] bg-[#0B0F13]/40">
            <th class="p-4 text-[10px] font-bold uppercase tracking-widest text-white/40">Timestamp</th>
            <th class="p-4 text-[10px] font-bold uppercase tracking-widest text-white/40">Actor</th>
            <th class="p-4 text-[10px] font-bold uppercase tracking-widest text-white/40">Action</th>
            <th class="p-4 text-[10px] font-bold uppercase tracking-widest text-white/40">Entity Target</th>
            <th class="p-4 text-[10px] font-bold uppercase tracking-widest text-white/40">IP Address</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-white/[0.03]">
          <tr v-if="loading">
            <td colspan="5" class="p-12 text-center">
              <div class="inline-flex items-center gap-2.5 text-white/50">
                <ArrowPathIcon class="w-5 h-5 animate-spin text-[#FF6700]" />
                <span class="text-xs font-medium">Loading audit history...</span>
              </div>
            </td>
          </tr>
          <tr v-else-if="logs.length === 0">
            <td colspan="5" class="p-16 text-center">
              <ClipboardDocumentListIcon class="w-10 h-10 mx-auto mb-2 text-white/20" />
              <p class="text-white/60 font-semibold text-sm">No audit logs found</p>
              <p class="text-xs text-white/40 mt-0.5">Try clearing filters or search keywords.</p>
            </td>
          </tr>
          <tr v-for="log in logs" :key="log.id" class="hover:bg-white/[0.02] transition-colors">
            <!-- Timestamp -->
            <td class="p-4 text-xs font-mono text-white/50 whitespace-nowrap">
              {{ new Date(log.created_at).toLocaleString() }}
            </td>

            <!-- User -->
            <td class="p-4">
              <div class="flex items-center gap-2.5">
                <div class="h-8 w-8 rounded-lg bg-[#FF6700]/10 border border-[#FF6700]/20 flex items-center justify-center text-[#FF6700] text-xs font-bold shrink-0">
                  {{ (log.user_name || '?').charAt(0).toUpperCase() }}
                </div>
                <div class="flex flex-col min-w-0">
                  <span class="text-xs font-medium text-white truncate">{{ log.user_name || 'System Auto' }}</span>
                  <span class="text-[10px] text-white/40 uppercase tracking-wider">{{ log.user_role || 'System' }}</span>
                </div>
              </div>
            </td>

            <!-- Action -->
            <td class="p-4">
              <div class="flex items-center gap-2">
                <div class="p-1.5 rounded-lg shrink-0 border" :class="getIconColorForAction(log.action)">
                  <component :is="getIconForAction(log.action)" class="w-3.5 h-3.5" />
                </div>
                <span class="text-xs font-medium text-white capitalize">{{ (log.action || '').replace(/_/g, ' ') }}</span>
              </div>
            </td>

            <!-- Entity -->
            <td class="p-4">
              <span class="text-[11px] uppercase tracking-wider bg-white/[0.04] border border-white/10 px-2.5 py-1 rounded-lg text-white/60 font-mono">
                {{ log.entity_type }} <span v-if="log.entity_id" class="text-[#FF6700]">#{{ log.entity_id }}</span>
              </span>
            </td>

            <!-- IP Address -->
            <td class="p-4 text-xs font-mono text-white/40">
              {{ log.ip_address || '0.0.0.0' }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import {
  FunnelIcon,
  MagnifyingGlassIcon,
  ArrowPathIcon,
  ClipboardDocumentListIcon
} from '@heroicons/vue/24/outline';

defineProps({
  logs: {
    type: Array,
    required: true
  },
  loading: {
    type: Boolean,
    default: false
  },
  filterType: {
    type: String,
    default: 'all'
  },
  search: {
    type: String,
    default: ''
  },
  getIconForAction: {
    type: Function,
    required: true
  },
  getIconColorForAction: {
    type: Function,
    required: true
  }
});

defineEmits(['update:filterType', 'update:search']);
</script>

