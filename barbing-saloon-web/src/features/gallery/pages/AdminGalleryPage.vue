<template>
  <AdminLayout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
      <!-- Loading Skeleton Preloader for Zero CLS -->
      <AdminGallerySkeleton v-if="loading" />

      <!-- Interactive Gallery Content -->
      <div v-else class="space-y-6 animate-fade-in pb-10">
        <!-- 1. Luxury Control Header with Real-Time Clock -->
        <AdminGalleryHeader
          v-model="search"
          :total-items="gallery.length"
          :is-refreshing="isRefreshing"
          @upload="openUploadForm"
          @refresh="fetchGallery(true)"
        />

        <!-- 2. High-Level KPI Metric Cards -->
        <AdminGalleryStats :stats="stats" />

        <!-- 3. Category Tabs, Stylist & Sorting Filters -->
        <AdminGalleryFilters
          v-model:activeCategory="activeCategory"
          v-model:barberFilter="barberFilter"
          v-model:sortOrder="sortOrder"
          :categories="categories"
          :barbers="barbers"
          :total-items="gallery.length"
        />

        <!-- 4. Gallery Grid with Empty States & Actions -->
        <AdminGalleryGrid
          :items="filteredGallery"
          :search="search"
          :active-category="activeCategory"
          :barber-filter="barberFilter"
          :get-image-url="getImageUrl"
          @edit="openEditForm"
          @delete="deleteImage"
          @upload="openUploadForm"
          @reset-filters="resetFilters"
        />

        <!-- 5. Upload & Edit Modal -->
        <GalleryUploadModal
          :show="showUploadForm"
          :saving="saving"
          :editing-id="editingId"
          :upload-form="uploadForm"
          :preview-image="previewImage"
          :selected-file-name="selectedFileName"
          :barbers="barbers"
          role="admin"
          color-theme="admin"
          @close="showUploadForm = false"
          @file-selected="onFileSelected"
          @save="onSaveImage"
        />
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { onMounted } from 'vue';
import AdminLayout from '@/portals/admin/layouts/AdminLayout.vue';
import AdminGalleryHeader from '../components/AdminGalleryHeader.vue';
import AdminGalleryStats from '../components/AdminGalleryStats.vue';
import AdminGalleryFilters from '../components/AdminGalleryFilters.vue';
import AdminGalleryGrid from '../components/AdminGalleryGrid.vue';
import AdminGallerySkeleton from '../components/AdminGallerySkeleton.vue';
import GalleryUploadModal from '../components/GalleryUploadModal.vue';
import { useAdminGallery } from '../composables/useAdminGallery';

const {
  loading,
  saving,
  isRefreshing,
  gallery,
  barbers,
  search,
  activeCategory,
  barberFilter,
  sortOrder,
  showUploadForm,
  editingId,
  previewImage,
  selectedFileName,
  uploadForm,
  categories,
  stats,
  filteredGallery,
  getImageUrl,
  fetchGallery,
  fetchBarbers,
  onFileSelected,
  openUploadForm,
  openEditForm,
  onSaveImage,
  deleteImage,
  resetFilters
} = useAdminGallery();

onMounted(() => {
  fetchGallery();
  fetchBarbers();
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
