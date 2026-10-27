<template>
  <AdminLayout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
      <!-- Loading Skeleton Preloader for Zero CLS -->
      <TestimonialsSkeleton v-if="isLoading" />

      <!-- Interactive Testimonials Moderation Content -->
      <div v-else class="space-y-6 animate-fade-in pb-10">
        <!-- 1. Luxury Control Header with Real-Time Clock -->
        <TestimonialsHeader
          v-model="search"
          :total-items="testimonials.length"
          :is-refreshing="isRefreshing"
          @refresh="loadTestimonials(true)"
        />

        <!-- 2. High-Level KPI Metric Cards -->
        <TestimonialsStats :stats="stats" />

        <!-- 3. Testimonial Flow Motion Controls (Speed & Direction) -->
        <TestimonialsFlowSettingsCard />

        <!-- 4. Status Tabs, Services & Rating Filters -->
        <TestimonialsFilters
          v-model:activeStatusFilter="activeStatusFilter"
          v-model:activeServiceFilter="activeServiceFilter"
          v-model:activeRatingFilter="activeRatingFilter"
          v-model:sortOrder="sortOrder"
          :services="services"
          :total-items="testimonials.length"
          :pending-count="pendingCount"
          :approved-count="approvedCount"
          :featured-count="featuredCount"
        />

        <!-- 5. Reviews Moderation List with Empty States -->
        <TestimonialsList
          :items="filteredTestimonials"
          :moderating-ids="moderatingIds"
          :has-active-filters="hasActiveFilters"
          :search="search"
          :active-status-filter="activeStatusFilter"
          @toggle-approval="toggleApproval"
          @toggle-visibility="toggleWebsiteVisibility"
          @delete="deleteReview"
          @reset-filters="resetFilters"
        />
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { computed, onMounted } from 'vue';
import AdminLayout from '@/portals/admin/layouts/AdminLayout.vue';
import TestimonialsHeader from '../components/testimonials/TestimonialsHeader.vue';
import TestimonialsStats from '../components/testimonials/TestimonialsStats.vue';
import TestimonialsFilters from '../components/testimonials/TestimonialsFilters.vue';
import TestimonialsList from '../components/testimonials/TestimonialsList.vue';
import TestimonialsSkeleton from '../components/testimonials/TestimonialsSkeleton.vue';
import TestimonialsFlowSettingsCard from '../components/TestimonialsFlowSettingsCard.vue';
import { useAdminTestimonials } from '../composables/useAdminTestimonials';

const {
  testimonials,
  services,
  isLoading,
  isRefreshing,
  moderatingIds,
  search,
  activeStatusFilter,
  activeServiceFilter,
  activeRatingFilter,
  sortOrder,
  pendingCount,
  approvedCount,
  featuredCount,
  stats,
  filteredTestimonials,
  loadServices,
  loadTestimonials,
  toggleApproval,
  toggleWebsiteVisibility,
  deleteReview,
  resetFilters
} = useAdminTestimonials();

const hasActiveFilters = computed(() => {
  return search.value.trim() !== '' || 
    activeStatusFilter.value !== 'all' || 
    activeServiceFilter.value !== 'all' || 
    activeRatingFilter.value !== 'all';
});

onMounted(() => {
  loadServices();
  loadTestimonials();
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
