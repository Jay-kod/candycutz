<template>
  <AdminLayout>
    <div class="space-y-6 pb-12">
      <!-- 1. Header with Live Digital Clock, Badges, Search & Gateway Probe -->
      <ApiDirectoryHeader
        v-model="search"
        :total-endpoints="stats.totalEndpoints"
        :health-state="healthState"
        :latency-ms="latencyMs"
        @recheck="checkHealth"
      />

      <!-- 2. High-Density KPI Platform Metrics -->
      <ApiDirectoryStats :stats="stats" />

      <!-- 3. Resource Group, Method & Auth Scope Filters -->
      <ApiFilters
        v-model:active-group="activeGroup"
        v-model:active-method="activeMethod"
        v-model:active-auth="activeAuth"
        :groups="endpointGroups"
        :has-active-filters="hasActiveFilters"
        @reset="resetFilters"
      />

      <!-- 4. Endpoints Rendered by Resource Group -->
      <div v-if="visibleGroups.length > 0" class="space-y-6">
        <ApiGroupSection
          v-for="group in visibleGroups"
          :key="group.id"
          :group="group"
          :copied-path="copiedPath"
          @copy="copyPath"
        />
      </div>

      <!-- 5. Empty State -->
      <div 
        v-else 
        class="flex flex-col items-center justify-center py-20 px-4 text-center rounded-[2rem] border border-dashed border-white/10 bg-[#12161C]/50 backdrop-blur-sm"
      >
        <div class="h-16 w-16 rounded-2xl bg-white/[0.04] border border-white/[0.08] flex items-center justify-center text-white/40 mb-4">
          <CodeBracketIcon class="h-8 w-8" />
        </div>
        <h3 class="font-display text-xl text-white font-semibold mb-2">No Matching API Endpoints</h3>
        <p class="text-sm text-white/50 max-w-md mb-6">
          No routes match your current combination of search keyword, HTTP method, and authentication filters.
        </p>
        <button
          @click="resetFilters"
          class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl border border-white/10 bg-white/5 text-xs font-semibold text-white hover:bg-white/10 transition-colors"
        >
          <span>Reset All Filters</span>
        </button>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { computed, onMounted } from 'vue';
import AdminLayout from '@/portals/admin/layouts/AdminLayout.vue';
import { useAdminApiDirectory } from '../composables/useAdminApiDirectory';
import ApiDirectoryHeader from '../components/api-directory/ApiDirectoryHeader.vue';
import ApiDirectoryStats from '../components/api-directory/ApiDirectoryStats.vue';
import ApiFilters from '../components/api-directory/ApiFilters.vue';
import ApiGroupSection from '../components/api-directory/ApiGroupSection.vue';
import { CodeBracketIcon } from '@heroicons/vue/24/outline';

const {
  search,
  activeGroup,
  activeMethod,
  activeAuth,
  healthState,
  latencyMs,
  copiedPath,
  endpointGroups,
  stats,
  visibleGroups,
  checkHealth,
  copyPath,
  resetFilters,
} = useAdminApiDirectory();

const hasActiveFilters = computed(() => {
  return (
    search.value.trim().length > 0 ||
    activeGroup.value !== 'All' ||
    activeMethod.value !== 'All' ||
    activeAuth.value !== 'All'
  );
});

onMounted(() => {
  checkHealth();
});
</script>
