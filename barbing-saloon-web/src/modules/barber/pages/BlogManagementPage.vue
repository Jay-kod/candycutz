<template>
  <BarberLayout>
    <section class="max-w-7xl mx-auto pb-16 pt-2 px-4 sm:px-6 lg:px-8 space-y-8 animate-fade-in">
      <!-- Loading Skeleton State -->
      <BlogSkeleton v-if="loading" />

      <!-- Main Hub Content -->
      <template v-else>
        <!-- Page Header -->
        <BlogHeader
          :loading="loading"
          :total-count="stats.totalCount"
          @refresh="fetchPosts"
        />

        <!-- KPI Performance Metrics -->
        <BarberBlogStats :stats="stats" />

        <!-- Filter, Search & View Controls -->
        <BarberBlogFilters
          v-model:search-query="searchQuery"
          v-model:status-filter="statusFilter"
          v-model:sort-order="sortOrder"
          v-model:view-mode="viewMode"
          :stats="stats"
          :has-active-filters="hasActiveFilters"
          @reset="resetFilters"
        />

        <!-- Articles Grid / List -->
        <BlogList
          :posts="filteredPosts"
          :view-mode="viewMode"
          :get-image-url="getImageUrl"
          :format-date="formatDate"
          :has-active-filters="hasActiveFilters"
          @toggle-publish="togglePublish"
          @delete="deletePost"
          @reset-filters="resetFilters"
        />
      </template>
    </section>
  </BarberLayout>
</template>

<script setup>
import { onMounted } from 'vue';
import BarberLayout from '@/portals/barber/layouts/BarberLayout.vue';
import { useBarberBlog } from '../composables/useBarberBlog';
import BlogHeader from '../components/blog/BlogHeader.vue';
import BarberBlogStats from '../components/blog/BarberBlogStats.vue';
import BarberBlogFilters from '../components/blog/BarberBlogFilters.vue';
import BlogList from '../components/blog/BlogList.vue';
import BlogSkeleton from '../components/blog/BlogSkeleton.vue';

const {
  loading,
  filteredPosts,
  stats,
  searchQuery,
  statusFilter,
  sortOrder,
  viewMode,
  hasActiveFilters,
  resetFilters,
  getImageUrl,
  formatDate,
  fetchPosts,
  togglePublish,
  deletePost,
} = useBarberBlog();

onMounted(() => {
  fetchPosts();
});
</script>

<style scoped>
.animate-fade-in {
  animation: fadeIn 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
