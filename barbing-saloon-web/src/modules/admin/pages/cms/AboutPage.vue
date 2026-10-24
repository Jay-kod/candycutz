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
        <section class="overflow-hidden rounded-2xl border border-admin/10 bg-black/30 shadow-xl">
          <div class="border-b border-white/5 bg-gradient-to-r from-admin/10 to-transparent px-6 py-5 sm:px-8">
            <h2 class="font-display text-xl text-theme-text">About Hero Cover</h2>
            <p class="mt-1 text-xs text-ivory/45">Upload a cover image and adjust the dark overlay behind the About page title.</p>
          </div>
          <div class="grid gap-8 p-6 sm:p-8 xl:grid-cols-2">
            <div class="space-y-6">
              <div>
                <label for="about-hero-image" class="mb-2 block text-xs font-bold uppercase tracking-widest text-ivory/50">Hero cover image</label>
                <input
                  id="about-hero-image"
                  ref="heroImageInput"
                  type="file"
                  accept="image/jpeg,image/png,image/webp"
                  class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-theme-text file:mr-4 file:rounded-lg file:border-0 file:bg-admin/15 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-admin-light"
                  @change="handleHeroImageUpload($event.target.files?.[0])"
                />
                <p class="mt-2 text-xs text-ivory/45">JPG, PNG, or WEBP, up to 5 MB. Save All Changes to publish the cover image.</p>
              </div>
              <div>
                <div class="mb-2 flex items-center justify-between gap-4">
                  <label for="about-hero-opacity" class="block text-xs font-bold uppercase tracking-widest text-ivory/50">Image overlay opacity</label>
                  <span class="text-sm font-semibold text-admin-light">{{ heroOpacity }}%</span>
                </div>
                <input
                  id="about-hero-opacity"
                  v-model.number="heroOpacity"
                  type="range"
                  min="0"
                  max="100"
                  class="w-full accent-admin"
                />
                <p class="mt-2 text-xs text-ivory/45">Higher opacity darkens the image to keep the hero text legible.</p>
              </div>
              <button
                v-if="currentHeroImage || previewHeroImage"
                type="button"
                class="min-h-10 rounded-xl border border-red-500/30 px-4 py-2 text-sm font-semibold text-red-300 transition hover:bg-red-500/10"
                @click="clearHeroImage"
              >
                Remove hero cover image
              </button>
            </div>
            <div class="min-w-0">
              <p class="mb-3 text-xs font-bold uppercase tracking-[0.16em] text-ivory/50">Live hero preview</p>
              <PageHero
                page-key="about"
                fallback-title="About CandyCutz"
                fallback-subtitle="Our story, our craft, and the standard behind every cut."
                :background-image="previewHeroImage"
                :overlay-opacity="heroOpacity"
                :load-settings="false"
              />
            </div>
          </div>
        </section>

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
import PageHero from '@/shared/ui/PageHero.vue';
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
  currentHeroImage,
  heroImageInput,
  heroOpacity,
  previewHeroImage,
  shopImageLoadError,
  previewShopUrl,
  getStorageUrl,
  fetchContent,
  handleShopImageUpload,
  handleHeroImageUpload,
  clearHeroImage,
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
