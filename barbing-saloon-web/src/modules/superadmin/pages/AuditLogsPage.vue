<template>
  <SuperAdminLayout>
    <section class="space-y-8 animate-fade-in">
      <!-- Command Banner -->
      <div :class="['rounded-2xl border p-4 xs:p-6 sm:p-8', bannerClass]">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <div>
            <p :class="['text-sm uppercase tracking-[0.3em] font-semibold', mutedText]">System Compliance & Security</p>
            <h1 :class="['mt-2 font-display text-4xl font-bold', headingText]">Audit Activity Trail</h1>
            <p :class="['mt-1 text-sm', mutedText]">Immutable security logs across all platform services and operations</p>
          </div>
          <div class="flex items-center gap-3">
            <button
              @click="loadLogs"
              :disabled="loading"
              :class="['flex items-center gap-2 rounded-xl border px-4 py-2.5 text-sm font-semibold transition-all duration-200', refreshBtnClass]"
            >
              <ArrowPathIcon :class="['h-4 w-4', loading ? 'animate-spin' : '']" />
              {{ loading ? 'Updating…' : 'Refresh Logs' }}
            </button>
          </div>
        </div>

        <!-- Filter & Search Controls -->
        <div class="mt-6 flex flex-col md:flex-row items-center gap-4">
          <div class="relative flex-1 w-full">
            <MagnifyingGlassIcon :class="['absolute left-3.5 top-1/2 -translate-y-1/2 h-5 w-5', mutedText]" />
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Search by action, user, module, or IP address..."
              :class="['w-full rounded-xl border pl-11 pr-4 py-2.5 text-sm outline-none transition-all', inputClass]"
            />
          </div>

          <!-- Module Filter Dropdown/Tabs -->
          <div :class="['flex items-center rounded-xl p-1 border gap-1 self-stretch md:self-auto overflow-x-auto', tabContainerClass]">
            <button
              v-for="mod in availableModules"
              :key="mod"
              @click="selectedModule = mod"
              :class="[
                'px-3.5 py-1.5 rounded-lg text-xs font-semibold whitespace-nowrap transition-all capitalize',
                selectedModule === mod ? activeTabClass : inactiveTabClass
              ]"
            >
              {{ mod }}
            </button>
          </div>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading && !logList.length" class="space-y-3">
        <div v-for="i in 8" :key="i" :class="['h-16 rounded-xl animate-pulse border', skeletonClass]"></div>
      </div>

      <!-- Empty State -->
      <div v-else-if="!filteredLogs.length" :class="['rounded-2xl border p-6 sm:p-12 text-center', cardClass]">
        <ClipboardDocumentListIcon :class="['mx-auto h-12 w-12 opacity-30', mutedText]" />
        <h3 :class="['mt-4 font-display text-xl font-bold', headingText]">No Audit Logs Match</h3>
        <p :class="['mt-1 text-sm', mutedText]">Try clearing your search query or selecting "all" modules.</p>
      </div>

      <!-- Audit Logs Table -->
      <div v-else :class="['rounded-2xl border overflow-hidden', cardClass]">
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
              <tr :class="['border-b', tableHeaderClass]">
                <th class="text-left py-3.5 px-5 text-xs font-bold uppercase tracking-wider">User</th>
                <th class="text-left py-3.5 px-4 text-xs font-bold uppercase tracking-wider">Action</th>
                <th class="text-left py-3.5 px-4 text-xs font-bold uppercase tracking-wider hidden md:table-cell">Module</th>
                <th class="text-left py-3.5 px-4 text-xs font-bold uppercase tracking-wider hidden lg:table-cell">Target</th>
                <th class="text-left py-3.5 px-4 text-xs font-bold uppercase tracking-wider hidden lg:table-cell">IP Address</th>
                <th class="text-right py-3.5 px-5 text-xs font-bold uppercase tracking-wider">Timestamp</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="log in filteredLogs"
                :key="log.id"
                @click="openDetail(log)"
                :class="['border-b transition-colors cursor-pointer', tableRowClass]"
              >
                <!-- User Column -->
                <td class="py-3.5 px-5">
                  <div class="flex items-center gap-3">
                    <div :class="['flex h-8 w-8 items-center justify-center rounded-full text-xs font-bold shrink-0', avatarClass]">
                      {{ getInitials(log.user?.name) }}
                    </div>
                    <div class="min-w-0">
                      <p :class="['font-semibold text-xs truncate', valueText]">{{ log.user?.name || 'System' }}</p>
                      <p :class="['text-[11px] truncate', mutedText]">{{ log.user_role || log.user?.role || 'Service' }}</p>
                    </div>
                  </div>
                </td>

                <!-- Action Column -->
                <td class="py-3.5 px-4">
                  <span :class="['inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-semibold border', getActionPillClass(log.action)]">
                    {{ log.action }}
                  </span>
                </td>

                <!-- Module Column -->
                <td class="py-3.5 px-4 hidden md:table-cell">
                  <span :class="['inline-block rounded-lg px-2.5 py-1 text-xs font-mono border capitalize', moduleBadgeClass]">
                    {{ log.module || 'Core' }}
                  </span>
                </td>

                <!-- Target Column -->
                <td class="py-3.5 px-4 hidden lg:table-cell">
                  <span v-if="log.target_type" :class="['text-xs font-mono', mutedText]">
                    {{ log.target_type }}#{{ log.target_id }}
                  </span>
                  <span v-else :class="mutedText">—</span>
                </td>

                <!-- IP Address Column -->
                <td class="py-3.5 px-4 hidden lg:table-cell">
                  <span :class="['font-mono text-xs', mutedText]">{{ log.ip_address || '127.0.0.1' }}</span>
                </td>

                <!-- Timestamp Column -->
                <td class="py-3.5 px-5 text-right">
                  <p :class="['text-xs font-medium tabular-nums', valueText]">{{ formatDate(log.created_at) }}</p>
                  <p :class="['text-[11px] tabular-nums', mutedText]">{{ formatTime(log.created_at) }}</p>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination Bar -->
        <div :class="['px-5 py-3 border-t flex flex-col sm:flex-row items-center justify-between gap-3', tableHeaderClass]">
          <p :class="['text-xs font-medium', mutedText]">
            Showing <span :class="valueText">{{ pagination.from || 1 }}</span> to <span :class="valueText">{{ pagination.to || filteredLogs.length }}</span> of <span :class="valueText">{{ pagination.total || filteredLogs.length }}</span> events
          </p>
          <div class="flex items-center gap-2">
            <button
              @click="changePage(pagination.current_page - 1)"
              :disabled="pagination.current_page <= 1 || loading"
              :class="['px-3 py-1.5 rounded-lg border text-xs font-semibold transition-all disabled:opacity-40 disabled:cursor-not-allowed', refreshBtnClass]"
            >
              Previous
            </button>
            <span :class="['text-xs px-2', mutedText]">
              Page {{ pagination.current_page || 1 }} of {{ pagination.last_page || 1 }}
            </span>
            <button
              @click="changePage(pagination.current_page + 1)"
              :disabled="pagination.current_page >= pagination.last_page || loading"
              :class="['px-3 py-1.5 rounded-lg border text-xs font-semibold transition-all disabled:opacity-40 disabled:cursor-not-allowed', refreshBtnClass]"
            >
              Next
            </button>
          </div>
        </div>
      </div>

      <!-- Audit Event Detail Modal -->
      <div
        v-if="selectedLog"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm animate-fade-in"
        @click.self="selectedLog = null"
      >
        <div :class="['w-full max-w-2xl rounded-2xl border p-6 shadow-2xl space-y-6 max-h-[90vh] overflow-y-auto', cardClass]">
          <!-- Modal Header -->
          <div class="flex items-start justify-between gap-4 border-b pb-4" :class="isDark ? 'border-red-500/20' : 'border-emerald-500/20'">
            <div>
              <div class="flex items-center gap-2.5">
                <span :class="['inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-semibold border', getActionPillClass(selectedLog.action)]">
                  {{ selectedLog.action }}
                </span>
                <span :class="['text-xs font-mono px-2 py-0.5 rounded-md border capitalize', moduleBadgeClass]">
                  {{ selectedLog.module || 'Core' }}
                </span>
                <span :class="['text-xs font-mono', mutedText]">
                  ID #{{ selectedLog.id }}
                </span>
              </div>
              <h3 :class="['mt-2 text-lg font-bold', headingText]">Administrative Action Forensic Detail</h3>
            </div>
            <button
              @click="selectedLog = null"
              :class="['rounded-lg p-1.5 text-white/50 hover:text-white hover:bg-white/10 transition-colors']"
            >
              ✕
            </button>
          </div>

          <!-- Actor & Forensic Card -->
          <div :class="['grid grid-cols-1 sm:grid-cols-2 gap-4 p-4 rounded-xl border', isDark ? 'bg-black/40 border-red-500/10' : 'bg-slate-50 border-emerald-100']">
            <div>
              <p :class="['text-[11px] uppercase tracking-wider font-semibold', mutedText]">Actor Staff</p>
              <p :class="['text-sm font-bold mt-0.5', valueText]">{{ selectedLog.user?.name || 'System Service' }}</p>
              <p :class="['text-xs', mutedText]">{{ selectedLog.user?.email || 'automated-task@candycutz.internal' }}</p>
              <p :class="['text-[11px] font-mono mt-1 capitalize', isDark ? 'text-red-400' : 'text-emerald-700']">
                Role: {{ selectedLog.user_role || selectedLog.user?.role || 'system' }}
              </p>
            </div>
            <div>
              <p :class="['text-[11px] uppercase tracking-wider font-semibold', mutedText]">Network & Timestamp</p>
              <p :class="['text-xs font-mono mt-0.5', valueText]">IP: {{ selectedLog.ip_address || '127.0.0.1' }}</p>
              <p :class="['text-xs mt-0.5', mutedText]">{{ formatDate(selectedLog.created_at) }} {{ formatTime(selectedLog.created_at) }}</p>
              <p v-if="selectedLog.target_type" :class="['text-xs font-mono mt-1', valueText]">
                Target: {{ selectedLog.target_type }} <span :class="isDark ? 'text-red-400' : 'text-emerald-600'">#{{ selectedLog.target_id || selectedLog.entity_id || '—' }}</span>
              </p>
            </div>
          </div>

          <!-- User Agent -->
          <div v-if="selectedLog.user_agent" :class="['p-3 rounded-xl border', isDark ? 'bg-black/40 border-red-500/10' : 'bg-slate-50 border-emerald-100']">
            <p :class="['text-[11px] uppercase tracking-wider font-semibold', mutedText]">Client User-Agent</p>
            <p :class="['text-xs font-mono mt-0.5 break-all', mutedText]">{{ selectedLog.user_agent }}</p>
          </div>

          <!-- State Changes (Before & After) -->
          <div class="space-y-3">
            <p :class="['text-xs uppercase tracking-wider font-bold', headingText]">State Delta (Audit Payload)</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <!-- Old Value -->
              <div :class="['rounded-xl border p-3 flex flex-col', isDark ? 'bg-black/60 border-red-500/15' : 'bg-white border-slate-200']">
                <span :class="['text-[11px] uppercase font-semibold text-rose-500 mb-1.5']">Before (Old Value)</span>
                <pre :class="['text-[11px] font-mono overflow-x-auto p-2 rounded-lg bg-black/40 flex-1 whitespace-pre-wrap break-all', mutedText]">{{ formatJson(selectedLog.old_value) }}</pre>
              </div>
              <!-- New Value -->
              <div :class="['rounded-xl border p-3 flex flex-col', isDark ? 'bg-black/60 border-emerald-500/20' : 'bg-white border-emerald-200']">
                <span :class="['text-[11px] uppercase font-semibold text-emerald-500 mb-1.5']">After (New Value)</span>
                <pre :class="['text-[11px] font-mono overflow-x-auto p-2 rounded-lg bg-black/40 flex-1 whitespace-pre-wrap break-all', isDark ? 'text-emerald-300' : 'text-emerald-800']">{{ formatJson(selectedLog.new_value) }}</pre>
              </div>
            </div>
          </div>

          <!-- Modal Footer -->
          <div class="flex justify-end pt-2 border-t" :class="isDark ? 'border-red-500/20' : 'border-emerald-500/20'">
            <button
              @click="selectedLog = null"
              :class="['px-5 py-2 rounded-xl text-xs font-semibold border transition-all', refreshBtnClass]"
            >
              Close Inspector
            </button>
          </div>
        </div>
      </div>
    </section>
  </SuperAdminLayout>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import { useTheme } from '@/core/composables/useTheme';
import SuperAdminLayout from '@/portals/superadmin/layouts/SuperAdminLayout.vue';
import { superadminApi } from '@/shared/api/old_superadminApi';
import { useToast } from '@/core/composables/useToast';
import {
  ClipboardDocumentListIcon,
  ArrowPathIcon,
  MagnifyingGlassIcon,
} from '@heroicons/vue/24/outline';

const toast = useToast();

const { isDark } = useTheme();

const logs = ref([]);
const loading = ref(true);
const searchQuery = ref('');
const selectedModule = ref('all');
const selectedLog = ref(null);

const pagination = ref({
  current_page: 1,
  last_page: 1,
  total: 0,
  from: 0,
  to: 0,
});

const logList = computed(() => {
  const data = logs.value;
  return Array.isArray(data) ? data : (data?.data || []);
});

const availableModules = computed(() => {
  const set = new Set(['all']);
  for (const log of logList.value) {
    if (log.module) set.add(log.module);
  }
  return Array.from(set);
});

const filteredLogs = computed(() => {
  let list = logList.value;

  if (selectedModule.value !== 'all') {
    list = list.filter((l) => l.module === selectedModule.value);
  }

  if (searchQuery.value.trim()) {
    const q = searchQuery.value.toLowerCase().trim();
    list = list.filter((l) =>
      (l.action && l.action.toLowerCase().includes(q)) ||
      (l.module && l.module.toLowerCase().includes(q)) ||
      (l.user?.name && l.user.name.toLowerCase().includes(q)) ||
      (l.ip_address && l.ip_address.includes(q))
    );
  }

  return list;
});

const openDetail = (log) => {
  selectedLog.value = log;
};

const getInitials = (name) => {
  if (!name) return 'S';
  return name
    .split(' ')
    .map((n) => n[0])
    .join('')
    .toUpperCase()
    .slice(0, 2);
};

const formatDate = (dateStr) => {
  if (!dateStr) return '—';
  return new Date(dateStr).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
  });
};

const formatTime = (dateStr) => {
  if (!dateStr) return '';
  return new Date(dateStr).toLocaleTimeString('en-US', {
    hour: '2-digit',
    minute: '2-digit',
    hour12: true,
  });
};

const formatJson = (val) => {
  if (!val) return '—';
  if (typeof val === 'string') {
    try {
      const parsed = JSON.parse(val);
      return JSON.stringify(parsed, null, 2);
    } catch {
      return val;
    }
  }
  return JSON.stringify(val, null, 2);
};

async function loadLogs(page = 1) {
  loading.value = true;
  try {
    const params = { page };
    if (selectedModule.value !== 'all') {
      params.module = selectedModule.value;
    }
    if (searchQuery.value.trim()) {
      params.search = searchQuery.value.trim();
    }

    const response = await superadminApi.auditLogs(params);
    logs.value = response.data?.data || response.data || [];
    if (response.data?.meta) {
      pagination.value = {
        current_page: response.data.meta.current_page || page,
        last_page: response.data.meta.last_page || 1,
        total: response.data.meta.total || logs.value.length,
        from: response.data.meta.from || 1,
        to: response.data.meta.to || logs.value.length,
      };
    } else {
      pagination.value = {
        current_page: page,
        last_page: 1,
        total: logs.value.length,
        from: 1,
        to: logs.value.length,
      };
    }
  } catch (err) {
    console.error('Failed to load audit logs:', err);
    toast.error('Failed to load audit logs');
  } finally {
    loading.value = false;
  }
}

const changePage = (newPage) => {
  if (newPage < 1 || newPage > pagination.value.last_page) return;
  loadLogs(newPage);
};

let debounceTimer = null;
watch(searchQuery, () => {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(() => {
    loadLogs(1);
  }, 350);
});

watch(selectedModule, () => {
  loadLogs(1);
});

onMounted(() => loadLogs(1));

// ─── Theme-Aware Computed Classes (Red/Black Night vs Green/White Light) ───
const bannerClass = computed(() =>
  isDark.value
    ? 'bg-gradient-to-br from-red-950/60 via-black to-black border-red-500/20'
    : 'bg-gradient-to-br from-emerald-50 via-white to-white border-emerald-500/20'
);

const headingText = computed(() => (isDark.value ? 'text-red-400' : 'text-emerald-700'));
const valueText = computed(() => (isDark.value ? 'text-white' : 'text-slate-900'));
const mutedText = computed(() => (isDark.value ? 'text-white/50' : 'text-slate-500'));

const refreshBtnClass = computed(() =>
  isDark.value
    ? 'bg-red-500/10 border-red-500/30 text-red-400 hover:bg-red-500/20'
    : 'bg-emerald-500/10 border-emerald-500/30 text-emerald-700 hover:bg-emerald-500/20'
);

const inputClass = computed(() =>
  isDark.value
    ? 'bg-black/60 border-red-500/20 text-white placeholder-white/30 focus:border-red-500/60 focus:ring-1 focus:ring-red-500/40'
    : 'bg-white border-emerald-500/20 text-slate-900 placeholder-slate-400 focus:border-emerald-600 focus:ring-1 focus:ring-emerald-500/40'
);

const tabContainerClass = computed(() =>
  isDark.value ? 'bg-black/60 border-red-500/20' : 'bg-slate-100 border-emerald-500/20'
);

const activeTabClass = computed(() =>
  isDark.value
    ? 'bg-red-600 text-white shadow-lg shadow-red-600/30'
    : 'bg-emerald-600 text-white shadow-lg shadow-emerald-600/30'
);

const inactiveTabClass = computed(() =>
  isDark.value ? 'text-white/60 hover:text-white' : 'text-slate-600 hover:text-slate-900'
);

const cardClass = computed(() =>
  isDark.value
    ? 'bg-[#0a0202]/80 border-red-500/15'
    : 'bg-white border-emerald-500/15 shadow-sm'
);

const skeletonClass = computed(() =>
  isDark.value ? 'bg-red-500/5 border-red-500/10' : 'bg-emerald-500/5 border-emerald-500/10'
);

const tableHeaderClass = computed(() =>
  isDark.value
    ? 'bg-red-950/20 border-red-500/15 text-red-400'
    : 'bg-emerald-50/60 border-emerald-100 text-emerald-800'
);

const tableRowClass = computed(() =>
  isDark.value
    ? 'border-white/[0.05] hover:bg-red-500/[0.04]'
    : 'border-slate-100 hover:bg-emerald-50/50'
);

const avatarClass = computed(() =>
  isDark.value
    ? 'bg-red-500/15 text-red-400'
    : 'bg-emerald-500/15 text-emerald-700'
);

const moduleBadgeClass = computed(() =>
  isDark.value
    ? 'bg-black/40 border-red-500/20 text-white/80'
    : 'bg-slate-50 border-emerald-200/60 text-slate-700'
);

const getActionPillClass = (action) => {
  if (!action) return isDark.value ? 'bg-white/5 text-white/60 border-white/10' : 'bg-slate-100 text-slate-600 border-slate-200';
  const a = action.toLowerCase();
  if (a.includes('create') || a.includes('add') || a.includes('register') || a.includes('store')) {
    return isDark.value
      ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20'
      : 'bg-emerald-50 text-emerald-700 border-emerald-200';
  }
  if (a.includes('delete') || a.includes('remove') || a.includes('destroy') || a.includes('deactivate')) {
    return isDark.value
      ? 'bg-red-500/10 text-red-400 border-red-500/20'
      : 'bg-red-50 text-red-700 border-red-200';
  }
  if (a.includes('update') || a.includes('edit') || a.includes('change') || a.includes('activate')) {
    return isDark.value
      ? 'bg-amber-500/10 text-amber-400 border-amber-500/20'
      : 'bg-amber-50 text-amber-700 border-amber-200';
  }
  if (a.includes('login') || a.includes('auth') || a.includes('verify')) {
    return isDark.value
      ? 'bg-blue-500/10 text-blue-400 border-blue-500/20'
      : 'bg-blue-50 text-blue-700 border-blue-200';
  }
  return isDark.value
    ? 'bg-white/5 text-white/60 border-white/10'
    : 'bg-slate-100 text-slate-600 border-slate-200';
};
</script>
