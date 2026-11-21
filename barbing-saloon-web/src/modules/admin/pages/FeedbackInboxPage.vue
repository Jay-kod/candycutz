<template>
  <AdminLayout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
      <!-- Loading Skeleton Preloader for Zero CLS -->
      <FeedbackSkeleton v-if="loading && !isRefreshing && submissions.length === 0" />

      <!-- Interactive Feedback Content -->
      <div v-else class="space-y-6 animate-fade-in pb-10">
        <!-- 1. Luxury Control Header with Real-Time Clock -->
        <FeedbackHeader
          v-model="search"
          :total-items="totalSubmissions"
          :is-refreshing="isRefreshing"
          @refresh="loadSubmissions(page, true)"
        />

        <!-- 2. High-Level KPI Metric Cards -->
        <FeedbackStats :stats="stats" />

        <!-- 3. Status Tabs, Topic & Sort Filters -->
        <FeedbackFilters
          v-model:activeStatus="activeStatus"
          v-model:activeCategory="activeCategory"
          v-model:sortOrder="sortOrder"
          :statuses="statuses"
          :categories="categories"
          :total-items="totalSubmissions"
          :new-count="stats.newCount"
          @update:active-status="loadSubmissions(1)"
          @update:active-category="loadSubmissions(1)"
        />

        <!-- Error Banner -->
        <div 
          v-if="errorMessage" 
          class="rounded-2xl border border-red-500/30 bg-red-500/10 p-4 text-xs font-semibold text-red-300" 
          role="alert"
        >
          {{ errorMessage }}
        </div>

        <!-- 4. Submissions List with Empty States & Pagination -->
        <FeedbackList
          :items="filteredSubmissions"
          :drafts="drafts"
          :saving-id="savingId"
          :statuses="statuses"
          :has-active-filters="hasActiveFilters"
          :search="search"
          :active-status="activeStatus"
          :active-category="activeCategory"
          :page="page"
          :last-page="lastPage"
          :loading="loading"
          @save="saveSubmission"
          @page-change="loadSubmissions($event)"
          @reset-filters="resetFilters"
        />
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { computed, onMounted } from 'vue';
import AdminLayout from '@/portals/admin/layouts/AdminLayout.vue';
import FeedbackHeader from '../components/feedback/FeedbackHeader.vue';
import FeedbackStats from '../components/feedback/FeedbackStats.vue';
import FeedbackFilters from '../components/feedback/FeedbackFilters.vue';
import FeedbackList from '../components/feedback/FeedbackList.vue';
import FeedbackSkeleton from '../components/feedback/FeedbackSkeleton.vue';
import { useAdminFeedback } from '../composables/useAdminFeedback';

const {
  submissions,
  loading,
  isRefreshing,
  savingId,
  errorMessage,
  page,
  lastPage,
  totalSubmissions,
  search,
  activeStatus,
  activeCategory,
  sortOrder,
  drafts,
  statuses,
  categories,
  stats,
  filteredSubmissions,
  loadSubmissions,
  saveSubmission,
  resetFilters
} = useAdminFeedback();

const hasActiveFilters = computed(() => {
  return search.value.trim() !== '' || 
    activeStatus.value !== 'all' || 
    activeCategory.value !== 'all';
});

onMounted(() => {
  loadSubmissions(1);
});
</script>

<style scoped>
.animate-fade-in {
  animation: fadeIn 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(8px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>
