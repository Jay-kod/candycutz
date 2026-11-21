<template>
  <div class="space-y-6 animate-fade-in pb-16">
    <!-- Header: Ambient luxury hero, live clock & primary actions -->
    <GalleryHeader 
      :saving="saving" 
      :loading="loading" 
      :is-dirty="isDirty" 
      @save="submit" 
      @refresh="reloadAll" 
    />

    <!-- Skeleton Preloader (Zero Layout Shift) -->
    <GallerySkeleton v-if="loading" />

    <!-- Loaded Content Workspace -->
    <div v-else class="space-y-6 animate-slide-up">
      <!-- High-Density KPI / Section Stat Cards -->
      <GalleryStats 
        :content="content"
        :hero-image="heroImage"
        :hero-opacity="heroOpacity"
        :items="galleryItems"
        :categories="categories"
      />

      <!-- Section Navigation Tabs & Interactive Preview Switcher -->
      <GalleryNavTabs v-model:active-tab="activeTab" />

      <!-- Interactive Public Simulation Mode -->
      <div v-if="activeTab === 'preview'" class="transition-all duration-300">
        <GalleryLivePreview 
          :content="content"
          :preview-image="previewImage"
          :hero-opacity="heroOpacity"
          @exit="activeTab = 'all'"
        />
      </div>

      <!-- Editor Forms Container -->
      <div v-else class="space-y-6">
        <!-- 1. Headline & Hero Cover Form -->
        <div v-show="activeTab === 'all' || activeTab === 'hero'">
          <GalleryHeroForm 
            ref="heroFormRef"
            :content="content"
            v-model:hero-opacity="heroOpacity"
            :preview-image="previewImage"
            @image-selected="selectImage"
            @clear-image="handleClearImage"
          />
        </div>

        <!-- 2. Portfolio Media Integration Bridge -->
        <div v-show="activeTab === 'all' || activeTab === 'media'">
          <GalleryMediaInfo 
            :items="galleryItems"
            :categories="categories"
          />
        </div>

        <!-- Bottom Action Bar -->
        <div class="rounded-2xl border border-white/[0.08] bg-[#12161C] p-5 flex flex-col sm:flex-row items-center justify-between gap-4 shadow-xl">
          <div class="flex items-center gap-2.5 text-xs text-white/50">
            <span class="h-2 w-2 rounded-full" :class="isDirty ? 'bg-amber-400' : 'bg-emerald-400'"></span>
            <span>{{ isDirty ? 'Unsaved modifications pending' : 'All gallery content in sync with production' }}</span>
            <span v-if="lastSaved" class="text-white/30">• Last synced {{ lastSaved }}</span>
          </div>

          <button 
            @click="submit" 
            :disabled="saving" 
            class="flex items-center justify-center min-h-[42px] w-full sm:w-auto gap-2 rounded-xl bg-gradient-to-r from-admin to-admin-light px-6 py-2.5 text-xs font-bold text-obsidian shadow-[0_4px_15px_rgba(255,103,0,0.25)] hover:shadow-[0_6px_20px_rgba(255,103,0,0.4)] active:scale-[0.98] transition-all disabled:opacity-50"
          >
            <span v-if="saving" class="h-4 w-4 animate-spin rounded-full border-2 border-obsidian border-t-transparent"></span>
            <CheckIcon v-else class="h-4 w-4" />
            <span>{{ saving ? 'Saving Changes...' : 'Save All Changes' }}</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Floating Sticky Bar on Unsaved Changes -->
    <GalleryStickyBar :is-dirty="isDirty" :saving="saving" @save="submit" />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { CheckIcon } from '@heroicons/vue/24/outline';
import { useGalleryCms } from './composables/useGalleryCms';
import GalleryHeader from './components/gallery/GalleryHeader.vue';
import GalleryStats from './components/gallery/GalleryStats.vue';
import GallerySkeleton from './components/gallery/GallerySkeleton.vue';
import GalleryNavTabs from './components/gallery/GalleryNavTabs.vue';
import GalleryHeroForm from './components/gallery/GalleryHeroForm.vue';
import GalleryMediaInfo from './components/gallery/GalleryMediaInfo.vue';
import GalleryLivePreview from './components/gallery/GalleryLivePreview.vue';
import GalleryStickyBar from './components/gallery/GalleryStickyBar.vue';

const {
  content,
  saving,
  loading,
  lastSaved,
  isDirty,
  activeTab,
  heroImage,
  heroOpacity,
  previewImage,
  galleryItems,
  categories,
  selectImage,
  clearImage,
  fetchContent,
  fetchGallery,
  saveContent
} = useGalleryCms();

const heroFormRef = ref(null);

const handleClearImage = () => {
  clearImage(heroFormRef.value?.imageInput);
};

const submit = () => {
  saveContent(heroFormRef.value?.imageInput);
};

const reloadAll = () => {
  fetchContent();
  fetchGallery();
};

onMounted(() => {
  reloadAll();
});
</script>

<style scoped>
.animate-fade-in {
  animation: fadeIn 0.4s ease-out forwards;
}
.animate-slide-up {
  animation: slideUp 0.5s ease-out forwards;
}
@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}
@keyframes slideUp {
  from { opacity: 0; transform: translateY(15px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>
