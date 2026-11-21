<template>
  <BarberLayout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
      <!-- Loading Skeleton Preloader for Zero CLS -->
      <BarberReviewsSkeleton v-if="isLoading" />

      <!-- Interactive Barber Reviews & Testimonials Content -->
      <div v-else class="space-y-6 animate-fade-in pb-12">
        <!-- 1. Header with Search and Refresh -->
        <BarberReviewsHeader
          v-model="search"
          :total-items="totalCount"
          :is-refreshing="isRefreshing"
          @refresh="loadReviews(true)"
        />

        <!-- 2. KPI Metrics -->
        <BarberReviewsStats :stats="stats" />

        <!-- 3. Status Tabs, Services, Rating & Sort Filters -->
        <BarberReviewsFilters
          v-model:activeStatusFilter="activeStatusFilter"
          v-model:activeServiceFilter="activeServiceFilter"
          v-model:activeRatingFilter="activeRatingFilter"
          v-model:sortOrder="sortOrder"
          :services="services"
          :total-items="totalCount"
          :approved-count="approvedCount"
          :pending-count="pendingCount"
          :has-active-filters="hasActiveFilters"
          @reset-filters="resetFilters"
        />

        <!-- 4. Reviews List -->
        <BarberReviewsList
          :items="filteredTestimonials"
          :has-active-filters="hasActiveFilters"
          :search="search"
          :format-date="formatDate"
          @reset-filters="resetFilters"
        />
      </div>
    </div>
  </BarberLayout>
</template>

<script setup>
import { onMounted } from 'vue';
import BarberLayout from '@/portals/barber/layouts/BarberLayout.vue';
import BarberReviewsHeader from '../components/BarberReviewsHeader.vue';
import BarberReviewsStats from '../components/BarberReviewsStats.vue';
import BarberReviewsFilters from '../components/BarberReviewsFilters.vue';
import BarberReviewsList from '../components/BarberReviewsList.vue';
import BarberReviewsSkeleton from '../components/BarberReviewsSkeleton.vue';
import { useBarberReviews } from '../composables/useBarberReviews';

const {
  services,
  isLoading,
  isRefreshing,
  search,
  activeStatusFilter,
  activeServiceFilter,
  activeRatingFilter,
  sortOrder,
  totalCount,
  approvedCount,
  pendingCount,
  stats,
  filteredTestimonials,
  hasActiveFilters,
  loadReviews,
  loadServices,
  resetFilters,
  formatDate,
} = useBarberReviews();

onMounted(async () => {
  await Promise.all([loadServices(), loadReviews()]);
});
</script>

<style scoped>
.animate-fade-in {
  animation: fadeIn 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(8px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>

