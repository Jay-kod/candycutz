<template>
  <CustomerLayout>
    <section class="animate-fade-in space-y-8 pb-12">
      <!-- 1. Page Header & Live Sync -->
      <BrowseServicesHeader
        :title="pageTitle"
        :subtitle="pageSubtitle"
        :total-services="services.length"
        :is-refreshing="isLoading"
        @refresh="loadData"
      />

      <!-- 2. Search, Sort & Dynamic Category Filters -->
      <BrowseServicesFilters
        v-model:searchQuery="searchQuery"
        v-model:sortOrder="sortOrder"
        :categories="availableCategories"
        :active-category="activeCategory"
        @category-change="changeCategory"
      />

      <!-- 3. Zero-CLS Skeleton Loader -->
      <BrowseServicesSkeleton v-if="isLoading" />

      <!-- 4. Interactive Live Services Grid -->
      <BrowseServicesGrid
        v-else
        :services="filteredServices"
        :wishlist="wishlist"
        :is-toggling-wishlist="isTogglingWishlist"
        @toggle-wishlist="toggleWishlist"
        @reset-filters="resetFilters"
      />
    </section>
  </CustomerLayout>
</template>

<script setup>
import { onMounted } from 'vue';
import CustomerLayout from '@/portals/customer/layouts/CustomerLayout.vue';
import BrowseServicesHeader from '../components/services/BrowseServicesHeader.vue';
import BrowseServicesFilters from '../components/services/BrowseServicesFilters.vue';
import BrowseServicesSkeleton from '../components/services/BrowseServicesSkeleton.vue';
import BrowseServicesGrid from '../components/services/BrowseServicesGrid.vue';
import { useCustomerBrowseServices } from '../composables/useCustomerBrowseServices';

const {
  services,
  wishlist,
  pageTitle,
  pageSubtitle,
  activeCategory,
  searchQuery,
  sortOrder,
  isLoading,
  isTogglingWishlist,
  availableCategories,
  filteredServices,
  loadData,
  changeCategory,
  resetFilters,
  toggleWishlist
} = useCustomerBrowseServices();

onMounted(() => {
  loadData();
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
