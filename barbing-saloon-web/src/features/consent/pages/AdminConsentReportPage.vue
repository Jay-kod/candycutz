<template>
  <AdminLayout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
        <div>
          <h1 class="font-display text-2xl sm:text-3xl font-bold text-theme-text">Consent Log</h1>
          <p class="mt-1 text-sm text-theme-muted">
            Evidence of who accepted the Terms of Service, when, and from where.
          </p>
          <p v-if="termsVersion" class="mt-2 text-[11px] uppercase tracking-wider text-theme-muted">
            Current policy version <span class="font-semibold text-gold">{{ termsVersion }}</span>
          </p>
        </div>

        <button
          type="button"
          class="inline-flex items-center justify-center gap-2 rounded-lg bg-gold px-4 py-2.5 text-sm font-bold text-obsidian transition-colors hover:bg-gold-dark disabled:opacity-60 disabled:cursor-not-allowed"
          :disabled="exporting"
          @click="exportCsv"
        >
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
          </svg>
          {{ exporting ? 'Preparing...' : 'Export CSV' }}
        </button>
      </div>

      <!-- Stats -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="rounded-xl border border-graphite bg-theme-surface p-5">
          <p class="text-[10px] font-semibold uppercase tracking-wider text-theme-muted">Customers</p>
          <p class="mt-2 text-3xl font-display font-bold text-theme-text">{{ stats.total }}</p>
        </div>
        <div class="rounded-xl border border-emerald-500/30 bg-emerald-500/5 p-5">
          <p class="text-[10px] font-semibold uppercase tracking-wider text-emerald-400">Accepted</p>
          <p class="mt-2 text-3xl font-display font-bold text-emerald-400">{{ stats.accepted }}</p>
        </div>
        <div class="rounded-xl border border-amber-500/30 bg-amber-500/5 p-5">
          <p class="text-[10px] font-semibold uppercase tracking-wider text-amber-400">Pending</p>
          <p class="mt-2 text-3xl font-display font-bold text-amber-400">{{ stats.pending }}</p>
        </div>
      </div>

      <!-- Filters -->
      <div class="flex flex-col sm:flex-row gap-3 sm:items-center sm:justify-between">
        <div class="inline-flex rounded-lg border border-graphite bg-theme-surface p-1">
          <button
            v-for="tab in tabs"
            :key="tab.value"
            type="button"
            class="rounded-md px-3 py-1.5 text-xs font-semibold transition-colors"
            :class="status === tab.value ? 'bg-gold text-obsidian' : 'text-theme-muted hover:text-theme-text'"
            @click="status = tab.value"
          >
            {{ tab.label }}
          </button>
        </div>

        <div class="relative sm:w-72">
          <input
            v-model="search"
            type="search"
            placeholder="Search name, email or phone"
            class="w-full rounded-lg border border-graphite bg-theme-surface px-3 py-2.5 text-sm text-theme-text placeholder-theme-muted outline-none focus:border-gold focus:ring-1 focus:ring-gold"
          />
        </div>
      </div>

      <p v-if="error" class="rounded-lg border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-400">
        {{ error }}
      </p>

      <!-- Table -->
      <div class="overflow-hidden rounded-xl border border-graphite bg-theme-surface">
        <div v-if="loading" class="p-10 text-center text-sm text-theme-muted">Loading consent records...</div>

        <div v-else-if="rows.length === 0" class="p-10 text-center text-sm text-theme-muted">
          No customers match this filter.
        </div>

        <div v-else class="overflow-x-auto">
          <table class="min-w-full divide-y divide-graphite text-sm">
            <thead class="bg-theme-bg/60">
              <tr class="text-left text-[10px] uppercase tracking-wider text-theme-muted">
                <th class="px-4 py-3 font-semibold">Customer</th>
                <th class="px-4 py-3 font-semibold">Contact</th>
                <th class="px-4 py-3 font-semibold">Status</th>
                <th class="px-4 py-3 font-semibold">Accepted at</th>
                <th class="px-4 py-3 font-semibold">Source</th>
                <th class="px-4 py-3 font-semibold">IP address</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-graphite/70">
              <tr v-for="row in rows" :key="row.id" class="hover:bg-theme-bg/40">
                <td class="px-4 py-3">
                  <p class="font-semibold text-theme-text">{{ row.name }}</p>
                  <p class="text-xs text-theme-muted">Joined {{ formatDate(row.created_at) }}</p>
                </td>
                <td class="px-4 py-3 text-theme-muted">
                  <p>{{ row.email }}</p>
                  <p class="text-xs">{{ row.phone || '—' }}</p>
                </td>
                <td class="px-4 py-3">
                  <span
                    class="inline-flex items-center rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider"
                    :class="row.terms_status === 'accepted'
                      ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/30'
                      : 'bg-amber-500/10 text-amber-400 border border-amber-500/30'"
                  >
                    {{ row.terms_status }}
                  </span>
                  <p v-if="row.terms_version" class="mt-1 text-[10px] text-theme-muted">v{{ row.terms_version }}</p>
                </td>
                <td class="px-4 py-3 text-theme-muted">{{ row.accepted_at ? formatDateTime(row.accepted_at) : '—' }}</td>
                <td class="px-4 py-3 text-theme-muted">{{ row.source || '—' }}</td>
                <td class="px-4 py-3 text-theme-muted font-mono text-xs">{{ row.ip_address || '—' }}</td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="meta.last_page > 1" class="flex items-center justify-between border-t border-graphite px-4 py-3 text-xs text-theme-muted">
          <span>Page {{ meta.current_page }} of {{ meta.last_page }} · {{ meta.total }} customers</span>
          <div class="flex gap-2">
            <button
              type="button"
              class="rounded-md border border-graphite px-3 py-1.5 font-semibold disabled:opacity-40"
              :disabled="meta.current_page <= 1"
              @click="goToPage(meta.current_page - 1)"
            >
              Previous
            </button>
            <button
              type="button"
              class="rounded-md border border-graphite px-3 py-1.5 font-semibold disabled:opacity-40"
              :disabled="meta.current_page >= meta.last_page"
              @click="goToPage(meta.current_page + 1)"
            >
              Next
            </button>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { onMounted, reactive, ref, watch } from 'vue';
import AdminLayout from '@/portals/admin/layouts/AdminLayout.vue';
import { adminApi } from '@/shared/api/old_adminApi';

const tabs = [
  { label: 'All', value: 'all' },
  { label: 'Accepted', value: 'accepted' },
  { label: 'Pending', value: 'pending' },
];

const rows = ref([]);
const stats = reactive({ total: 0, accepted: 0, pending: 0 });
const meta = reactive({ current_page: 1, last_page: 1, per_page: 25, total: 0 });
const termsVersion = ref('');
const status = ref('all');
const search = ref('');
const loading = ref(true);
const exporting = ref(false);
const error = ref('');

let searchTimer = null;

function formatDate(value) {
  if (!value) return '—';
  const date = new Date(value);
  return Number.isNaN(date.getTime()) ? '—' : date.toLocaleDateString();
}

function formatDateTime(value) {
  if (!value) return '—';
  const date = new Date(value);
  return Number.isNaN(date.getTime()) ? '—' : date.toLocaleString();
}

async function load(page = 1) {
  loading.value = true;
  error.value = '';

  try {
    const response = await adminApi.consentReport({
      status: status.value,
      search: search.value || undefined,
      page,
      per_page: meta.per_page,
    });

    const data = response.data?.data || {};
    rows.value = data.users || [];
    termsVersion.value = data.terms_version || '';
    Object.assign(stats, data.stats || { total: 0, accepted: 0, pending: 0 });
    Object.assign(meta, data.meta || { current_page: 1, last_page: 1, per_page: 25, total: 0 });
  } catch (err) {
    error.value = err?.message || 'Unable to load the consent report.';
  } finally {
    loading.value = false;
  }
}

function goToPage(page) {
  if (page < 1 || page > meta.last_page) return;
  load(page);
}

async function exportCsv() {
  exporting.value = true;
  error.value = '';

  try {
    const response = await adminApi.exportConsentReport({
      status: status.value,
      search: search.value || undefined,
    });

    const blob = new Blob([response.data], { type: 'text/csv;charset=utf-8' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = `consent-report-${new Date().toISOString().slice(0, 10)}.csv`;
    document.body.appendChild(link);
    link.click();
    link.remove();
    URL.revokeObjectURL(url);
  } catch (err) {
    error.value = err?.message || 'Unable to export the consent report.';
  } finally {
    exporting.value = false;
  }
}

watch(status, () => load(1));

watch(search, () => {
  if (searchTimer) clearTimeout(searchTimer);
  searchTimer = setTimeout(() => load(1), 300);
});

onMounted(() => load(1));
</script>
