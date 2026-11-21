<template>
  <div class="space-y-6 animate-fade-in pb-16">
    <!-- Header: Ambient luxury hero, live clock & primary actions -->
    <ContactHeader 
      :saving="saving" 
      :loading="loading" 
      :is-dirty="isDirty" 
      @save="submit" 
      @refresh="fetchContent" 
    />

    <!-- Skeleton Preloader (Zero Layout Shift) -->
    <ContactSkeleton v-if="loading" />

    <!-- Loaded Content Workspace -->
    <div v-else class="space-y-6 animate-slide-up">
      <!-- High-Density KPI / Section Stat Cards -->
      <ContactStats 
        :content="content"
        :active-social-count="activeSocialCount"
        :extracted-map-src="extractedMapSrc"
      />

      <!-- Section Navigation Tabs & Interactive Preview Switcher -->
      <ContactNavTabs 
        v-model:active-tab="activeTab" 
        :active-social-count="activeSocialCount"
      />

      <!-- Interactive Public Simulation Mode -->
      <div v-if="activeTab === 'preview'" class="transition-all duration-300">
        <ContactLivePreview 
          :content="content"
          :preview-image="previewImage"
          :hero-opacity="heroOpacity"
          :extracted-map-src="extractedMapSrc"
          :active-contact-preview-list="activeContactPreviewList"
          :active-contact-preview-count="activeContactPreviewCount"
          @exit="activeTab = 'all'"
        />
      </div>

      <!-- Editor Forms Container -->
      <div v-else class="space-y-6">
        <!-- 1. Headline & Hero Cover Form -->
        <div v-show="activeTab === 'all' || activeTab === 'hero'">
          <ContactHeroForm 
            ref="heroFormRef"
            :content="content"
            v-model:hero-opacity="heroOpacity"
            :preview-image="previewImage"
            @image-selected="selectImage"
            @clear-image="handleClearImage"
          />
        </div>

        <!-- 2. Business Details & Form Inquiries -->
        <div v-show="activeTab === 'all' || activeTab === 'details'">
          <ContactDetailsForm :content="content" />
        </div>

        <!-- 3. Social Media Constellation Matrix -->
        <div v-show="activeTab === 'all' || activeTab === 'socials'">
          <ContactSocialsForm 
            :content="content"
            :platforms-list="platformsList"
            :active-social-count="activeSocialCount"
            :active-footer-preview-list="activeFooterPreviewList"
            :active-footer-preview-count="activeFooterPreviewCount"
            :active-contact-preview-list="activeContactPreviewList"
            :active-contact-preview-count="activeContactPreviewCount"
            :is-option-checked="isOptionChecked"
            :toggle-option="toggleOption"
            :get-platform-status-text="getPlatformStatusText"
            :get-platform-status-badge="getPlatformStatusBadge"
          />
        </div>

        <!-- 4. Google Maps Integration -->
        <div v-show="activeTab === 'all' || activeTab === 'maps'">
          <ContactMapsForm 
            :content="content"
            :extracted-map-src="extractedMapSrc"
          />
        </div>

        <!-- Bottom Action Bar -->
        <div class="rounded-2xl border border-white/[0.08] bg-[#12161C] p-5 flex flex-col sm:flex-row items-center justify-between gap-4 shadow-xl">
          <div class="flex items-center gap-2.5 text-xs text-white/50">
            <span class="h-2 w-2 rounded-full" :class="isDirty ? 'bg-amber-400' : 'bg-emerald-400'"></span>
            <span>{{ isDirty ? 'Unsaved modifications pending' : 'All contact settings in sync with production' }}</span>
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
    <ContactStickyBar :is-dirty="isDirty" :saving="saving" @save="submit" />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { CheckIcon } from '@heroicons/vue/24/outline';
import { useContactCms } from './composables/useContactCms';
import ContactHeader from './components/contact/ContactHeader.vue';
import ContactStats from './components/contact/ContactStats.vue';
import ContactSkeleton from './components/contact/ContactSkeleton.vue';
import ContactNavTabs from './components/contact/ContactNavTabs.vue';
import ContactHeroForm from './components/contact/ContactHeroForm.vue';
import ContactDetailsForm from './components/contact/ContactDetailsForm.vue';
import ContactSocialsForm from './components/contact/ContactSocialsForm.vue';
import ContactMapsForm from './components/contact/ContactMapsForm.vue';
import ContactLivePreview from './components/contact/ContactLivePreview.vue';
import ContactStickyBar from './components/contact/ContactStickyBar.vue';

const {
  content,
  saving,
  loading,
  lastSaved,
  isDirty,
  activeTab,
  heroOpacity,
  previewImage,
  platformsList,
  isOptionChecked,
  toggleOption,
  getPlatformStatusText,
  getPlatformStatusBadge,
  activeSocialCount,
  activeFooterPreviewList,
  activeFooterPreviewCount,
  activeContactPreviewList,
  activeContactPreviewCount,
  extractedMapSrc,
  selectImage,
  clearImage,
  fetchContent,
  saveContent
} = useContactCms();

const heroFormRef = ref(null);

const handleClearImage = () => {
  clearImage(heroFormRef.value?.imageInput);
};

const submit = () => {
  saveContent(heroFormRef.value?.imageInput);
};

onMounted(() => {
  fetchContent();
});
</script>

<style scoped>
.animate-fade-in {
  animation: fadeIn 0.4s cubic-bezier(0.16, 1, 0.3, 1);
}
.animate-slide-up {
  animation: slideUp 0.4s cubic-bezier(0.16, 1, 0.3, 1);
}
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(6px); }
  to { opacity: 1; transform: translateY(0); }
}
@keyframes slideUp {
  from { opacity: 0; transform: translateY(12px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>
