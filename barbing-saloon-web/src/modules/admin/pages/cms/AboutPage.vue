<template>
  <div class="space-y-6 animate-fade-in pb-16">
    <!-- Header: Ambient luxury hero, live clock & primary actions -->
    <AboutHeader 
      :saving="saving" 
      :loading="loading" 
      :is-dirty="isDirty" 
      @save="submit" 
      @refresh="fetchContent" 
    />

    <!-- Skeleton Preloader (Zero Layout Shift) -->
    <AboutSkeleton v-if="loading" />

    <!-- Loaded Content Workspace -->
    <div v-else class="space-y-6 animate-slide-up">
      <!-- High-Density KPI / Section Stat Cards -->
      <AboutStats 
        :content="content"
        :current-shop-image="currentShopImage"
      />

      <!-- Section Navigation Tabs & Interactive Preview Switcher -->
      <AboutNavTabs v-model:active-tab="activeTab" />

      <!-- Interactive Public Simulation Mode -->
      <div v-if="activeTab === 'preview'" class="transition-all duration-300">
        <AboutLivePreview 
          :content="content"
          :preview-shop-url="previewShopUrl"
          :current-shop-image="currentShopImage"
          :get-storage-url="getStorageUrl"
          @exit="activeTab = 'all'"
        />
      </div>

      <!-- Editor Forms Container -->
      <div v-else class="space-y-6">
        <!-- 1. Shop Information & Interior Photo -->
        <div v-show="activeTab === 'all' || activeTab === 'shop'">
          <AboutShopForm 
            :content="content"
            :preview-shop-url="previewShopUrl"
            :current-shop-image="currentShopImage"
            v-model:image-load-error="shopImageLoadError"
            :get-storage-url="getStorageUrl"
            @image-upload="(file, inputRef) => { handleShopImageUpload(file); shopFileInputRef = inputRef; }"
          />
        </div>

        <!-- 2. Meet the Team Teaser Section -->
        <div v-show="activeTab === 'all' || activeTab === 'team'">
          <AboutTeamForm :content="content" />
        </div>

        <!-- Bottom Action Bar -->
        <div class="rounded-2xl border border-white/[0.08] bg-[#12161C] p-5 flex flex-col sm:flex-row items-center justify-between gap-4 shadow-xl">
          <div class="flex items-center gap-2.5 text-xs text-white/50">
            <span class="h-2 w-2 rounded-full" :class="isDirty ? 'bg-amber-400' : 'bg-emerald-400'"></span>
            <span>{{ isDirty ? 'Unsaved modifications pending' : 'All story sections in sync with production' }}</span>
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
    <AboutStickyBar :is-dirty="isDirty" :saving="saving" @save="submit" />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { CheckIcon } from '@heroicons/vue/24/outline';
import { useAboutCms } from './composables/useAboutCms';
import AboutHeader from './components/AboutHeader.vue';
import AboutStats from './components/AboutStats.vue';
import AboutSkeleton from './components/AboutSkeleton.vue';
import AboutNavTabs from './components/AboutNavTabs.vue';
import AboutShopForm from './components/AboutShopForm.vue';
import AboutTeamForm from './components/AboutTeamForm.vue';
import AboutLivePreview from './components/AboutLivePreview.vue';
import AboutStickyBar from './components/AboutStickyBar.vue';

const {
  content,
  saving,
  loading,
  lastSaved,
  isDirty,
  activeTab,
  currentShopImage,
  shopImageLoadError,
  previewShopUrl,
  getStorageUrl,
  fetchContent,
  handleShopImageUpload,
  saveContent
} = useAboutCms();

const shopFileInputRef = ref(null);

const submit = () => saveContent(shopFileInputRef);

onMounted(() => {
  fetchContent();
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
