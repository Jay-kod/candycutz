<template>
  <AdminLayout>
    <div class="space-y-6 pb-12">
      <!-- 1. Header with Clock, Badges, Search & Actions -->
      <BlogHeader
        v-model="search"
        :total-items="stats.totalCount"
        :published-count="stats.publishedCount"
        :is-refreshing="isRefreshing"
        @refresh="fetchPosts(true)"
        @new-post="openEditor()"
      />

      <!-- 2. Loading Skeleton or Content -->
      <BlogSkeleton v-if="loading" />

      <template v-else>
        <!-- 3. KPI Performance Cards -->
        <BlogStats :stats="stats" />

        <!-- 4. Filter Toolbar -->
        <BlogFilters
          v-model:status-filter="statusFilter"
          v-model:author-filter="authorFilter"
          v-model:sort-order="sortOrder"
          :authors="authors"
          :stats="stats"
          :has-active-filters="hasActiveFilters"
          @reset="resetFilters"
        />

        <!-- 5. Articles Grid & Rich Empty States -->
        <BlogGrid
          :posts="filteredPosts"
          :search-query="search"
          :status-filter="statusFilter"
          @edit="openEditor"
          @toggle-publish="togglePublish"
          @delete="deletePost"
          @reset="resetFilters"
          @new-post="openEditor()"
        />
      </template>
    </div>
  </AdminLayout>
</template>

<script setup>
import { computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import AdminLayout from '@/portals/admin/layouts/AdminLayout.vue';
import { useAdminBlog } from '../composables/useAdminBlog';
import BlogHeader from '../components/blog/BlogHeader.vue';
import BlogStats from '../components/blog/BlogStats.vue';
import BlogFilters from '../components/blog/BlogFilters.vue';
import BlogGrid from '../components/blog/BlogGrid.vue';
import BlogSkeleton from '../components/blog/BlogSkeleton.vue';

const router = useRouter();

const {
  loading,
  isRefreshing,
  search,
  statusFilter,
  authorFilter,
  sortOrder,
  authors,
  stats,
  filteredPosts,
  fetchPosts,
  togglePublish,
  deletePost,
  resetFilters,
} = useAdminBlog();

const hasActiveFilters = computed(() => {
  return (
    search.value.trim().length > 0 ||
    statusFilter.value !== 'all' ||
    authorFilter.value !== 'all' ||
    sortOrder.value !== 'newest'
  );
});

function openEditor(post = null) {
  if (post && post.id) {
    router.push(`/admin/blog/${post.id}/edit`);
  } else {
    router.push('/admin/blog/new');
  }
}

onMounted(() => {
  fetchPosts();
});
</script>
