<template>
  <div class="space-y-6 animate-fade-in pb-16">
    <!-- Header: Ambient luxury hero, live clock & primary actions -->
    <CmsHomeHeader 
      :saving="saving" 
      :loading="loading" 
      :is-dirty="isDirty" 
      @save="submit" 
      @refresh="fetchSettings" 
    />

    <!-- Skeleton Preloader (Zero Layout Shift) -->
    <CmsHomeSkeleton v-if="loading" />

    <!-- Loaded Content Workspace -->
    <div v-else class="space-y-6 animate-slide-up">
      <!-- High-Density KPI / Section Stat Cards -->
      <CmsHomeStats 
        :settings="settings"
        :current-hero-image="currentHeroImage"
        :current-about-image="currentAboutImage"
      />

      <!-- Section Navigation Tabs & Interactive Preview Switcher -->
      <CmsHomeNavTabs v-model:active-tab="activeTab" />

      <!-- Interactive Public Simulation Mode -->
      <div v-if="activeTab === 'preview'" class="transition-all duration-300">
        <CmsHomeLivePreview 
          :settings="settings"
          :preview-url="previewUrl"
          :current-hero-image="currentHeroImage"
          :preview-about-url="previewAboutUrl"
          :current-about-image="currentAboutImage"
          :get-storage-url="getStorageUrl"
          @exit="activeTab = 'all'"
        />
      </div>

      <!-- Editor Forms Container -->
      <div v-else class="space-y-6">
        <!-- 1. Hero Section Form -->
        <div v-show="activeTab === 'all' || activeTab === 'hero'">
          <HeroSectionForm 
            :settings="settings"
            :preview-url="previewUrl"
            :current-hero-image="currentHeroImage"
            v-model:image-load-error="imageLoadError"
            :get-storage-url="getStorageUrl"
            @image-upload="(file, inputRef) => { handleImageUpload(file, 'hero'); fileInputRef = inputRef; }"
          />
        </div>

        <!-- 2. Statistics Section Form -->
        <div v-show="activeTab === 'all' || activeTab === 'stats'">
          <StatisticsForm :settings="settings" />
        </div>

        <!-- 3. About Teaser Section Form -->
        <div v-show="activeTab === 'all' || activeTab === 'about'">
          <AboutTeaserForm 
            :settings="settings"
            :preview-about-url="previewAboutUrl"
            :current-about-image="currentAboutImage"
            v-model:about-image-load-error="aboutImageLoadError"
            :get-storage-url="getStorageUrl"
            @image-upload="(file, inputRef) => { handleImageUpload(file, 'about'); aboutFileInputRef = inputRef; }"
          />
        </div>

        <!-- 4. Portal Teaser Section Form -->
        <div v-show="activeTab === 'all' || activeTab === 'portal'">
          <PortalTeaserForm :settings="settings" />
        </div>

        <!-- Bottom Action Bar -->
        <div class="rounded-2xl border border-white/[0.08] bg-[#12161C] p-5 flex flex-col sm:flex-row items-center justify-between gap-4 shadow-xl">
          <div class="flex items-center gap-2.5 text-xs text-white/50">
            <span class="h-2 w-2 rounded-full" :class="isDirty ? 'bg-amber-400' : 'bg-emerald-400'"></span>
            <span>{{ isDirty ? 'Unsaved modifications pending' : 'All sections in sync with production' }}</span>
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
    <CmsHomeStickyBar :is-dirty="isDirty" :saving="saving" @save="submit" />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { CheckIcon } from '@heroicons/vue/24/outline';
import { useCmsSettings } from '../composables/useCmsSettings';
import CmsHomeHeader from '../components/CmsHomeHeader.vue';
import CmsHomeStats from '../components/CmsHomeStats.vue';
import CmsHomeSkeleton from '../components/CmsHomeSkeleton.vue';
import CmsHomeNavTabs from '../components/CmsHomeNavTabs.vue';
import CmsHomeLivePreview from '../components/CmsHomeLivePreview.vue';
import CmsHomeStickyBar from '../components/CmsHomeStickyBar.vue';
import HeroSectionForm from '../components/HeroSectionForm.vue';
import StatisticsForm from '../components/StatisticsForm.vue';
import AboutTeaserForm from '../components/AboutTeaserForm.vue';
import PortalTeaserForm from '../components/PortalTeaserForm.vue';

const {
  settings,
  saving,
  loading,
  lastSaved,
  isDirty,
  activeTab,
  currentHeroImage,
  imageLoadError,
  currentAboutImage,
  aboutImageLoadError,
  previewUrl,
  previewAboutUrl,
  getStorageUrl,
  fetchSettings,
  handleImageUpload,
  saveSettings
} = useCmsSettings();

const fileInputRef = ref(null);
const aboutFileInputRef = ref(null);

const submit = () => saveSettings(fileInputRef, aboutFileInputRef);

onMounted(() => {
  fetchSettings();
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
