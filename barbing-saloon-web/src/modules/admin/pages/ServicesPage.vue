<template>
  <AdminLayout>
    <section class="space-y-6 animate-fade-in pb-10">
      <!-- Premium Header Banner -->
      <div class="relative overflow-hidden rounded-[2rem] border border-white/[0.05] bg-[#111111]/90 p-8 lg:p-10 shadow-2xl flex flex-col md:flex-row md:items-end justify-between gap-6 backdrop-blur-3xl">
        <div class="absolute -right-20 -top-20 h-80 w-80 rounded-full bg-admin/10 blur-[100px]"></div>
        <div class="absolute -bottom-24 -left-24 h-56 w-56 rounded-full bg-emerald-500/8 blur-[80px]"></div>
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(255,103,0,0.05),transparent_60%)]"></div>

        <div class="relative z-10">
          <div class="flex items-center gap-3 mb-2">
            <span class="flex h-6 w-6 items-center justify-center rounded-md bg-admin/20 text-admin border border-admin/30">
              <SparklesIcon class="h-3.5 w-3.5" />
            </span>
            <p class="text-[10px] uppercase tracking-[0.3em] text-admin/80 font-bold">Catalog</p>
          </div>
          <h1 class="font-display text-4xl lg:text-5xl text-white drop-shadow-md leading-tight">
            Service <span class="text-transparent bg-clip-text bg-gradient-to-r from-admin to-amber-400">Menu</span>
            <span v-if="!loading" class="ml-3 inline-flex items-center justify-center h-9 px-3.5 rounded-full bg-white/5 border border-white/10 text-lg text-white/60 font-normal align-middle">{{ services.length }}</span>
          </h1>
          <p class="mt-3 text-sm text-white/40 max-w-lg leading-relaxed">
            Manage your saloon's service offerings, pricing, and durations. These services are visible to your customers during booking.
          </p>
        </div>

        <div class="relative z-10 flex flex-col sm:flex-row items-center gap-4 shrink-0 w-full md:w-auto">
          <router-link to="/admin/services/new" class="w-full sm:w-auto flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-admin to-amber-500 px-6 py-3.5 text-sm font-bold text-obsidian transition-all hover:shadow-[0_8px_30px_rgba(255,103,0,0.3)] shrink-0 group">
            <PlusIcon class="h-5 w-5 group-hover:rotate-90 transition-transform duration-300" />
            Add Service
          </router-link>
        </div>
      </div>

      <!-- Filters Bar: Category & Approval Status -->
      <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <!-- Approval Filter -->
        <div class="flex items-center gap-2 bg-black/40 p-1.5 rounded-2xl border border-white/5">
          <button
            @click="approvalFilter = 'all'"
            class="rounded-xl px-3.5 py-2 text-xs font-bold transition-all"
            :class="approvalFilter === 'all'
              ? 'bg-admin text-obsidian shadow-lg'
              : 'text-white/50 hover:text-white'"
          >
            All ({{ services.length }})
          </button>
          <button
            @click="approvalFilter = 'pending'"
            class="rounded-xl px-3.5 py-2 text-xs font-bold transition-all flex items-center gap-1.5"
            :class="approvalFilter === 'pending'
              ? 'bg-amber-500 text-obsidian shadow-lg'
              : 'text-amber-400/80 hover:text-amber-400'"
          >
            <span v-if="pendingCount > 0" class="h-2 w-2 rounded-full bg-amber-400 animate-pulse"></span>
            Pending Approval ({{ pendingCount }})
          </button>
          <button
            @click="approvalFilter = 'approved'"
            class="rounded-xl px-3.5 py-2 text-xs font-bold transition-all"
            :class="approvalFilter === 'approved'
              ? 'bg-emerald-500 text-obsidian shadow-lg'
              : 'text-white/50 hover:text-white'"
          >
            Approved
          </button>
        </div>

        <!-- Category Filter Tabs -->
        <div class="flex items-center gap-2 overflow-x-auto pb-1 scrollbar-hide max-w-full">
          <button
            @click="activeCategory = 'all'"
            class="shrink-0 rounded-xl px-3 py-2 text-xs font-bold uppercase tracking-wider border transition-all"
            :class="activeCategory === 'all'
              ? 'bg-admin/20 text-admin border-admin/30'
              : 'bg-white/[0.02] text-white/40 border-white/[0.05] hover:bg-white/[0.05] hover:text-white/60'"
          >
            All Cats
          </button>
          <button
            v-for="cat in categories"
            :key="cat"
            @click="activeCategory = cat"
            class="shrink-0 rounded-xl px-3 py-2 text-xs font-bold uppercase tracking-wider border transition-all"
            :class="activeCategory === cat
              ? 'bg-admin/20 text-admin border-admin/30'
              : 'bg-white/[0.02] text-white/40 border-white/[0.05] hover:bg-white/[0.05] hover:text-white/60'"
          >
            {{ cat }}
          </button>
        </div>
      </div>

      <!-- Services Grid -->
      <div class="rounded-[2rem] border border-white/[0.05] bg-[#1a1a1a]/80 backdrop-blur-2xl shadow-2xl overflow-hidden min-h-[400px]">

        <!-- Loading skeleton -->
        <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 p-6">
          <div v-for="i in 6" :key="i" class="rounded-2xl border border-white/[0.02] bg-white/[0.01] p-6 animate-pulse">
            <div class="h-4 w-20 bg-white/[0.03] rounded mb-4"></div>
            <div class="h-6 w-3/4 bg-white/[0.03] rounded mb-3"></div>
            <div class="h-3 w-full bg-white/[0.02] rounded mb-2"></div>
            <div class="h-3 w-5/6 bg-white/[0.02] rounded mb-6"></div>
            <div class="flex justify-between items-center pt-4 border-t border-white/[0.03]">
              <div class="h-6 w-16 bg-white/[0.03] rounded"></div>
              <div class="h-6 w-20 bg-white/[0.02] rounded"></div>
            </div>
          </div>
        </div>

        <!-- Empty state -->
        <div v-else-if="filteredServices.length === 0" class="flex flex-col items-center justify-center py-24 px-6 text-center">
          <div class="relative flex h-20 w-20 items-center justify-center rounded-full bg-white/[0.02] border border-white/[0.05] mb-6">
            <div class="absolute inset-0 rounded-full border border-dashed border-white/10 animate-[spin_10s_linear_infinite]"></div>
            <SparklesIcon class="h-8 w-8 text-white/20" />
          </div>
          <h3 class="text-xl font-bold text-white mb-2">{{ approvalFilter === 'pending' ? 'No Pending Approvals' : 'No Services Found' }}</h3>
          <p class="text-sm text-white/40 max-w-sm mx-auto mb-6">
            {{ approvalFilter === 'pending' ? 'All barber-submitted services have been reviewed.' : 'Add your first service to start accepting bookings from customers.' }}
          </p>
          <router-link to="/admin/services/new" class="text-admin hover:text-admin-light text-sm font-semibold transition-colors flex items-center gap-2">
            <PlusIcon class="h-4 w-4" /> Add your first service
          </router-link>
        </div>

        <!-- Service Cards -->
        <div v-else class="p-6">
          <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
            <article
              v-for="service in filteredServices"
              :key="service.id"
              class="group relative overflow-hidden rounded-3xl border border-white/[0.05] bg-gradient-to-br from-white/[0.02] to-transparent transition-all duration-300 hover:border-admin/20 hover:from-white/[0.04] hover:-translate-y-1 hover:shadow-[0_20px_40px_rgba(0,0,0,0.3)] flex flex-col"
              :class="{'border-amber-500/30 bg-amber-500/[0.02]': service.approval_status === 'pending'}"
            >
              <!-- Glowing Accent Bar -->
              <div class="absolute top-0 left-0 right-0 h-1 transition-opacity z-20"
                :class="service.approval_status === 'pending' ? 'bg-amber-400 opacity-100' : 'bg-gradient-to-r from-admin to-amber-400 opacity-0 group-hover:opacity-100'"></div>

              <!-- Cover Image (if exists) -->
              <div v-if="service.image || service.image_url" class="relative w-full h-40 bg-black overflow-hidden shrink-0">
                <img :src="service.image_url || service.image" :alt="service.name" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700" />
                <div class="absolute inset-0 bg-gradient-to-t from-[#1a1a1a] via-[#1a1a1a]/50 to-transparent z-10"></div>
                
                <div class="absolute top-4 left-4 z-20 flex items-center gap-2">
                  <span class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1 text-[10px] font-bold uppercase tracking-widest border border-admin/20 bg-admin/10 text-admin backdrop-blur-md">
                    <TagIcon class="h-3 w-3" />
                    {{ service.category_name || service.category || 'General' }}
                  </span>
                </div>
              </div>

              <div class="relative z-20 flex flex-col flex-1 p-6" :class="{'pt-2': service.image || service.image_url}">
                <!-- Header row (no image state) -->
                <div v-if="!service.image && !service.image_url" class="flex items-start justify-between gap-4 mb-4">
                  <span class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1 text-[10px] font-bold uppercase tracking-widest border border-admin/20 bg-admin/10 text-admin">
                    <TagIcon class="h-3 w-3" />
                    {{ service.category_name || service.category || 'General' }}
                  </span>

                  <div class="flex items-center gap-1 bg-white/5 rounded-xl p-1 border border-white/5 opacity-0 group-hover:opacity-100 transition-opacity">
                    <router-link :to="`/admin/services/${service.id}/edit`" class="p-1.5 rounded-lg text-white/40 hover:text-white hover:bg-white/10 transition-colors" title="Edit Service">
                      <PencilSquareIcon class="h-4 w-4" />
                    </router-link>
                    <button @click="deleteService(service)" class="p-1.5 rounded-lg text-white/40 hover:text-red-400 hover:bg-red-500/20 transition-colors" title="Delete Service">
                      <TrashIcon class="h-4 w-4" />
                    </button>
                  </div>
                </div>

                <!-- Floating action buttons (with image state) -->
                <div v-if="service.image || service.image_url" class="absolute top-4 right-4 z-30 flex items-center gap-1 bg-black/40 backdrop-blur-md rounded-xl p-1 border border-white/10 opacity-0 group-hover:opacity-100 transition-opacity shadow-lg">
                  <router-link :to="`/admin/services/${service.id}/edit`" class="p-1.5 rounded-lg text-white/70 hover:text-white hover:bg-white/20 transition-colors" title="Edit Service">
                    <PencilSquareIcon class="h-4 w-4" />
                  </router-link>
                  <button @click="deleteService(service)" class="p-1.5 rounded-lg text-white/70 hover:text-red-400 hover:bg-red-500/30 transition-colors" title="Delete Service">
                    <TrashIcon class="h-4 w-4" />
                  </button>
                </div>

                <!-- Barber creator tag -->
                <div v-if="service.barber" class="mb-2 flex items-center gap-1.5 text-xs text-amber-400/90 font-medium">
                  <span class="text-white/40">Created by barber:</span>
                  <span class="font-bold underline">{{ service.barber.name }}</span>
                </div>

                <!-- Name & Description -->
                <h2 class="font-display text-2xl text-white mb-2 group-hover:text-admin-light transition-colors leading-tight drop-shadow-sm">{{ service.name }}</h2>
                <p v-if="service.description" class="text-sm text-white/50 line-clamp-2 mb-auto leading-relaxed">{{ service.description }}</p>
                <p v-else class="text-sm text-white/20 italic mb-auto">No description provided</p>

                <!-- Status Badges -->
                <div class="mt-4 mb-4 flex items-center gap-2 flex-wrap">
                  <span
                    class="rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider border flex items-center gap-1.5"
                    :class="{
                      'bg-amber-500/15 text-amber-400 border-amber-500/30': service.approval_status === 'pending',
                      'bg-emerald-500/15 text-emerald-400 border-emerald-500/30': service.approval_status === 'approved' || !service.approval_status,
                      'bg-rose-500/15 text-rose-400 border-rose-500/30': service.approval_status === 'rejected'
                    }"
                  >
                    <span class="h-1.5 w-1.5 rounded-full" :class="{
                      'bg-amber-400 animate-pulse': service.approval_status === 'pending',
                      'bg-emerald-400': service.approval_status === 'approved' || !service.approval_status,
                      'bg-rose-400': service.approval_status === 'rejected'
                    }"></span>
                    {{ service.approval_status === 'pending' ? 'Pending Approval' : (service.approval_status === 'rejected' ? 'Rejected' : 'Approved') }}
                  </span>

                  <span
                    class="rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider border"
                    :class="service.is_active
                      ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20'
                      : 'bg-white/5 text-white/40 border-white/10'"
                  >
                    {{ service.is_active ? 'Active' : 'Inactive' }}
                  </span>
                </div>

                <!-- Admin Quick Actions for Pending Services -->
                <div v-if="service.approval_status === 'pending'" class="mb-4 pt-3 border-t border-white/10 flex items-center gap-2">
                  <button
                    @click="approveService(service)"
                    :disabled="approvingId === service.id"
                    class="flex-1 py-2 px-3 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-obsidian text-xs font-bold transition-all flex items-center justify-center gap-1 shadow-[0_0_15px_rgba(16,185,129,0.3)] disabled:opacity-50"
                  >
                    <CheckIcon class="h-4 w-4" />
                    Approve
                  </button>
                  <button
                    @click="rejectService(service)"
                    :disabled="approvingId === service.id"
                    class="py-2 px-3 rounded-xl bg-red-500/20 hover:bg-red-500/30 text-red-400 border border-red-500/30 text-xs font-bold transition-all flex items-center justify-center gap-1 disabled:opacity-50"
                  >
                    <XMarkIcon class="h-4 w-4" />
                    Reject
                  </button>
                </div>

                <!-- Price & Duration -->
                <div class="pt-4 flex items-center justify-between border-t border-white/[0.05]">
                  <div class="flex items-baseline gap-1 text-emerald-400 font-display">
                    <span class="text-xs opacity-60">₦</span>
                    <span class="text-2xl font-bold">{{ Number(service.price).toLocaleString() }}</span>
                  </div>
                  <div class="flex items-center gap-1.5 text-xs font-bold text-white/50 bg-white/[0.03] px-3 py-1.5 rounded-lg border border-white/[0.05]">
                    <ClockIcon class="h-4 w-4 text-admin/60" />
                    {{ service.duration_minutes }} mins
                  </div>
                </div>
              </div>
            </article>
          </div>
        </div>
      </div>
    </section>
  </AdminLayout>
</template>

<script setup>
import { onMounted, ref, computed } from 'vue';
import AdminLayout from '@/portals/admin/layouts/AdminLayout.vue';
import client from '@/shared/api/client';
import { adminApi } from '@/shared/api/old_adminApi';
import { useToast } from '../../../core/composables/useToast';
import { useConfirm } from '../../../core/composables/useConfirm';
import {
  SparklesIcon,
  TagIcon,
  ClockIcon,
  PencilSquareIcon,
  TrashIcon,
  PlusIcon,
  CheckIcon,
  XMarkIcon
} from '@heroicons/vue/24/outline';

const toast = useToast();
const { confirm } = useConfirm();

const services = ref([]);
const loading = ref(true);
const activeCategory = ref('all');
const approvalFilter = ref('all');
const approvingId = ref(null);

const pendingCount = computed(() => {
  return services.value.filter(s => s.approval_status === 'pending').length;
});

const categories = computed(() => {
  const cats = new Set(services.value.map(s => s.category_name || s.category || 'General'));
  return [...cats].sort();
});

const filteredServices = computed(() => {
  let list = services.value;

  if (approvalFilter.value === 'pending') {
    list = list.filter(s => s.approval_status === 'pending');
  } else if (approvalFilter.value === 'approved') {
    list = list.filter(s => s.approval_status === 'approved' || !s.approval_status);
  }

  if (activeCategory.value !== 'all') {
    list = list.filter(s => (s.category_name || s.category || 'General') === activeCategory.value);
  }

  return list;
});

async function loadServices() {
  loading.value = true;
  try {
    const response = await client.get('/services');
    services.value = response.data?.data || response.data || [];
  } catch (err) {
    toast.error('Failed to fetch services');
  } finally {
    loading.value = false;
  }
}

async function approveService(service) {
  approvingId.value = service.id;
  try {
    const res = await client.patch(`/services/${service.id}/approve`);
    toast.success(res.data?.message || `"${service.name}" approved successfully!`);
    await loadServices();
  } catch (err) {
    toast.error(err.response?.data?.message || 'Failed to approve service');
  } finally {
    approvingId.value = null;
  }
}

async function rejectService(service) {
  const ok = await confirm('Reject Service', `Are you sure you want to reject "${service.name}"?`);
  if (!ok) return;

  approvingId.value = service.id;
  try {
    const res = await client.patch(`/services/${service.id}/reject`);
    toast.success(res.data?.message || `"${service.name}" rejected.`);
    await loadServices();
  } catch (err) {
    toast.error(err.response?.data?.message || 'Failed to reject service');
  } finally {
    approvingId.value = null;
  }
}

async function deleteService(service) {
  const ok = await confirm('Delete Service', `Are you sure you want to delete "${service.name}"? This action cannot be undone.`);
  if (!ok) return;

  try {
    await adminApi.deleteService(service.id);
    toast.success(`"${service.name}" has been deleted`);
    await loadServices();
  } catch (err) {
    toast.error('Failed to delete service');
  }
}

onMounted(() => {
  loadServices();
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
.scrollbar-hide::-webkit-scrollbar { display: none; }
.scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
</style>
