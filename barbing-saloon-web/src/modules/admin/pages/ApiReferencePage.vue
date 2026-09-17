<template>
  <AdminLayout>
    <section class="space-y-6 animate-fade-in">
      <div class="relative overflow-hidden rounded-3xl border border-admin/20 bg-gradient-to-br from-obsidian via-charcoal to-[#2A1100] p-8 shadow-2xl">
        <div class="absolute -right-16 -top-16 h-64 w-64 rounded-full bg-admin/5 blur-3xl"></div>
        <div class="absolute -left-16 -bottom-16 h-48 w-48 rounded-full bg-admin/5 blur-3xl"></div>
        <div class="relative z-10 flex flex-col gap-5 md:flex-row md:items-end md:justify-between">
          <div>
            <p class="text-xs uppercase tracking-[0.3em] text-admin/70 font-bold">Platform Reference</p>
            <h1 class="mt-2 font-display text-4xl text-theme-text">API <span class="text-admin-light">Directory</span></h1>
            <p class="mt-2 max-w-2xl text-sm text-ivory/60">One view of the endpoints used by the website, mobile app, and operations tools.</p>
          </div>
          <div class="flex items-center gap-3 rounded-2xl border border-white/10 bg-black/20 px-4 py-3">
            <span :class="['h-2.5 w-2.5 rounded-full', healthState === 'online' ? 'bg-emerald-400' : healthState === 'checking' ? 'bg-amber-400 animate-pulse' : 'bg-red-400']"></span>
            <div>
              <p class="text-[10px] uppercase tracking-widest text-ivory/40">API status</p>
              <p class="text-sm font-semibold text-theme-text">{{ healthLabel }}</p>
            </div>
            <button @click="checkHealth" class="ml-2 rounded-lg border border-admin/30 px-3 py-1.5 text-xs font-bold text-admin-light hover:bg-admin/10 transition-colors">
              Check
            </button>
          </div>
        </div>
      </div>

      <div class="grid gap-4 sm:grid-cols-3">
        <div v-for="stat in stats" :key="stat.label" class="rounded-2xl border border-white/10 bg-black/20 p-5">
          <p class="text-[10px] uppercase tracking-widest text-ivory/40">{{ stat.label }}</p>
          <p class="mt-2 font-display text-3xl text-theme-text">{{ stat.value }}</p>
        </div>
      </div>

      <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div class="flex flex-wrap gap-2">
          <button
            v-for="filter in filters"
            :key="filter"
            @click="activeFilter = filter"
            :class="['rounded-xl border px-3 py-2 text-xs font-bold transition-colors', activeFilter === filter ? 'border-admin/40 bg-admin/15 text-admin-light' : 'border-white/10 text-ivory/50 hover:bg-white/5 hover:text-ivory/80']"
          >
            {{ filter }}
          </button>
        </div>
        <input v-model="search" type="search" placeholder="Filter endpoints..." class="w-full rounded-xl border border-white/10 bg-black/20 px-4 py-2.5 text-sm text-theme-text outline-none focus:border-admin md:w-72" />
      </div>

      <div v-for="group in visibleGroups" :key="group.name" class="overflow-hidden rounded-2xl border border-white/10 bg-black/20">
        <div class="flex items-center justify-between border-b border-white/5 bg-white/[0.02] px-5 py-4">
          <div>
            <h2 class="font-display text-xl text-theme-text">{{ group.name }}</h2>
            <p class="mt-1 text-xs text-ivory/40">{{ group.description }}</p>
          </div>
          <span class="rounded-lg bg-white/5 px-2.5 py-1 text-xs font-bold text-ivory/50">{{ group.endpoints.length }}</span>
        </div>
        <div class="divide-y divide-white/[0.05]">
          <div v-for="endpoint in group.endpoints" :key="`${endpoint.method}-${endpoint.path}`" class="flex flex-col gap-3 px-5 py-4 md:flex-row md:items-center">
            <span :class="['w-16 shrink-0 rounded-md border px-2 py-1 text-center text-[10px] font-black tracking-wider', methodClass(endpoint.method)]">{{ endpoint.method }}</span>
            <code class="min-w-0 flex-1 break-all text-sm text-theme-text">{{ endpoint.path }}</code>
            <span class="text-xs text-ivory/40 md:w-64 md:text-right">{{ endpoint.description }}</span>
          </div>
        </div>
      </div>

      <div v-if="visibleGroups.length === 0" class="rounded-2xl border border-white/10 bg-black/20 p-12 text-center text-sm text-ivory/50">
        No endpoints match the current filter.
      </div>
    </section>
  </AdminLayout>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import AdminLayout from '../layouts/AdminLayout.vue';
import client from '../../../core/api/axios';

const search = ref('');
const activeFilter = ref('All');
const healthState = ref('checking');

const endpointGroups = [
  {
    name: 'Public Catalog',
    description: 'Unauthenticated services, barbers, content, and availability.',
    endpoints: [
      { method: 'GET', path: '/api/v1/health', description: 'Service health probe' },
      { method: 'GET', path: '/api/v1/services', description: 'Service catalog' },
      { method: 'GET', path: '/api/v1/service-categories', description: 'Service categories' },
      { method: 'GET', path: '/api/v1/barbers', description: 'Available barbers' },
      { method: 'GET', path: '/api/v1/availability', description: 'Bookable time slots' },
      { method: 'GET', path: '/api/v1/gallery', description: 'Gallery items' },
      { method: 'GET', path: '/api/v1/testimonials', description: 'Approved testimonials' },
      { method: 'GET', path: '/api/v1/blog', description: 'Published blog posts' },
      { method: 'GET', path: '/api/public/settings', description: 'Website settings alias' },
      { method: 'GET', path: '/api/public/services', description: 'Website catalog alias' },
    ],
  },
  {
    name: 'Authentication',
    description: 'Sanctum token issuance and account lifecycle.',
    endpoints: [
      { method: 'POST', path: '/api/v1/auth/login', description: 'Email, username, or phone login' },
      { method: 'POST', path: '/api/v1/auth/register', description: 'Customer registration' },
      { method: 'POST', path: '/api/v1/auth/social-login', description: 'Verified social identity login' },
      { method: 'GET', path: '/api/v1/auth/me', description: 'Current authenticated user' },
      { method: 'POST', path: '/api/v1/auth/logout', description: 'Revoke current token' },
      { method: 'POST', path: '/api/v1/auth/forgot-password', description: 'Password reset request' },
    ],
  },
  {
    name: 'Bookings & Operations',
    description: 'Customer reservations, barber operations, and notifications.',
    endpoints: [
      { method: 'GET', path: '/api/v1/appointments', description: 'Role-scoped appointments' },
      { method: 'POST', path: '/api/v1/appointments', description: 'Create locked reservation' },
      { method: 'PATCH', path: '/api/v1/appointments/{id}/cancel', description: 'Cancel appointment' },
      { method: 'PATCH', path: '/api/v1/appointments/{id}/status', description: 'Update appointment status' },
      { method: 'POST', path: '/api/v1/appointments/walk-in', description: 'Create barber walk-in' },
      { method: 'GET', path: '/api/v1/barbers/schedule', description: 'Barber schedule' },
      { method: 'PUT', path: '/api/v1/barbers/schedule', description: 'Update barber schedule' },
      { method: 'PATCH', path: '/api/v1/barbers/chair-status', description: 'Update chair status' },
      { method: 'GET', path: '/api/v1/notifications', description: 'Authenticated notifications' },
    ],
  },
  {
    name: 'Payments & Administration',
    description: 'Payment callbacks and privileged operational resources.',
    endpoints: [
      { method: 'POST', path: '/api/v1/payments/webhook', description: 'Verified payment webhook' },
      { method: 'GET', path: '/api/v1/payments/appointments/{id}/receipt', description: 'Appointment receipt' },
      { method: 'GET', path: '/api/admin/dashboard', description: 'Admin operational dashboard' },
      { method: 'GET', path: '/api/admin/settings', description: 'Admin settings' },
      { method: 'GET', path: '/api/admin/logs', description: 'Audit log stream' },
    ],
  },
];

const filters = ['All', ...endpointGroups.map((group) => group.name)];
const visibleGroups = computed(() => {
  const needle = search.value.trim().toLowerCase();
  return endpointGroups
    .filter((group) => activeFilter.value === 'All' || group.name === activeFilter.value)
    .map((group) => ({
      ...group,
      endpoints: group.endpoints.filter((endpoint) => !needle || `${endpoint.method} ${endpoint.path} ${endpoint.description}`.toLowerCase().includes(needle)),
    }))
    .filter((group) => group.endpoints.length > 0);
});

const stats = computed(() => [
  { label: 'Endpoint groups', value: endpointGroups.length },
  { label: 'Documented routes', value: endpointGroups.reduce((total, group) => total + group.endpoints.length, 0) },
  { label: 'Auth protected', value: endpointGroups.flatMap((group) => group.endpoints).filter((endpoint) => !endpoint.path.includes('/public') && !endpoint.path.includes('/health') && !endpoint.path.includes('/services') && !endpoint.path.includes('/barbers')).length },
]);

const healthLabel = computed(() => ({ online: 'Online', checking: 'Checking...', offline: 'Unavailable' }[healthState.value]));

function methodClass(method) {
  return {
    GET: 'border-sky-400/30 bg-sky-400/10 text-sky-300',
    POST: 'border-emerald-400/30 bg-emerald-400/10 text-emerald-300',
    PUT: 'border-amber-400/30 bg-amber-400/10 text-amber-300',
    PATCH: 'border-orange-400/30 bg-orange-400/10 text-orange-300',
  }[method] || 'border-white/20 bg-white/5 text-ivory/60';
}

async function checkHealth() {
  healthState.value = 'checking';
  try {
    await client.get('/health', { timeout: 5000 });
    healthState.value = 'online';
  } catch {
    healthState.value = 'offline';
  }
}

onMounted(checkHealth);
</script>
