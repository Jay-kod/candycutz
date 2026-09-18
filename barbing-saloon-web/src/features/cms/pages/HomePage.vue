<template>
  <div class="space-y-8 animate-fade-in pb-12">
    <!-- Header Banner -->
    <div class="relative overflow-hidden rounded-3xl border border-admin/20 bg-gradient-to-br from-obsidian via-charcoal to-[#2A1100] p-8 lg:p-12 shadow-2xl">
      <div class="absolute -right-16 -top-16 h-64 w-64 rounded-full bg-admin/10 blur-3xl"></div>
      <div class="absolute -left-16 -bottom-16 h-48 w-48 rounded-full bg-admin/5 blur-3xl"></div>
      
      <div class="relative z-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
        <div>
          <p class="text-xs uppercase tracking-[0.3em] text-admin/80 font-bold flex items-center gap-2">
            <Cog8ToothIcon class="w-4 h-4" /> Home Page Content
          </p>
          <h1 class="mt-2 font-display text-4xl lg:text-5xl text-theme-text drop-shadow-lg">
            <span class="text-transparent bg-clip-text bg-gradient-to-r from-admin to-admin-light">Hero Section</span>
          </h1>
          <p class="mt-3 text-sm lg:text-base text-ivory/60 max-w-xl">Configure the main text and background image for your public landing page.</p>
        </div>
        
        <div class="flex items-center gap-3">
           <button 
              @click="submit" 
              :disabled="saving" 
              class="flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-admin to-admin-light px-6 py-3 text-sm font-bold text-obsidian transition-all hover:shadow-[0_4px_25px_rgba(255,103,0,0.4)] active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:shadow-none"
            >
              <span v-if="saving" class="h-4 w-4 animate-spin rounded-full border-2 border-obsidian border-t-transparent"></span>
              <CheckIcon v-else class="h-5 w-5" />
              {{ saving ? 'Saving...' : 'Save Changes' }}
            </button>
        </div>
      </div>
    </div>

    <div v-if="loading" class="flex justify-center py-32">
      <div class="h-10 w-10 animate-spin rounded-full border-4 border-admin/30 border-t-admin"></div>
    </div>

    <!-- Content Area -->
    <div v-else class="space-y-6 animate-slide-up">
      <div class="rounded-2xl border border-admin/10 bg-black/30 backdrop-blur-md overflow-hidden shadow-xl">
        <div class="border-b border-white/5 bg-gradient-to-r from-admin/10 to-transparent px-8 py-6">
          <div class="flex items-center gap-4">
            <div class="h-12 w-12 rounded-2xl bg-admin/10 border border-admin/20 flex items-center justify-center shadow-[0_0_15px_rgba(255,103,0,0.1)]">
              <WindowIcon class="h-6 w-6 text-admin" />
            </div>
            <div>
              <h2 class="font-display text-2xl text-theme-text">Hero Section</h2>
              <p class="text-sm text-ivory/50 mt-1">The first thing customers see when they visit your website.</p>
            </div>
          </div>
        </div>
        
        <div class="p-8 space-y-8">
          <HeroSectionForm 
            :settings="settings"
            :previewUrl="previewUrl"
            :currentHeroImage="currentHeroImage"
            v-model:imageLoadError="imageLoadError"
            :getStorageUrl="getStorageUrl"
            @image-upload="(file, inputRef) => { handleImageUpload(file, 'hero'); fileInputRef = inputRef; }"
          />
        </div>

        <StatisticsForm :settings="settings" />

        <AboutTeaserForm 
          :settings="settings"
          :previewAboutUrl="previewAboutUrl"
          :currentAboutImage="currentAboutImage"
          v-model:aboutImageLoadError="aboutImageLoadError"
          :getStorageUrl="getStorageUrl"
          @image-upload="(file, inputRef) => { handleImageUpload(file, 'about'); aboutFileInputRef = inputRef; }"
        />

        <PortalTeaserForm :settings="settings" />
        
        <!-- Bottom Action Bar -->
        <div class="border-t border-white/5 bg-black/20 px-8 py-5 flex justify-end">
          <button 
            @click="submit" 
            :disabled="saving" 
            class="flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-admin to-admin-light px-8 py-3 text-sm font-bold text-obsidian transition-all hover:shadow-[0_4px_25px_rgba(255,103,0,0.4)] active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:shadow-none"
          >
            <span v-if="saving" class="h-4 w-4 animate-spin rounded-full border-2 border-obsidian border-t-transparent"></span>
            <CheckIcon v-else class="h-5 w-5" />
            {{ saving ? 'Saving...' : 'Save Changes' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { Cog8ToothIcon, CheckIcon, WindowIcon } from '@heroicons/vue/24/outline';
import { useCmsSettings } from '../composables/useCmsSettings';
import HeroSectionForm from '../components/HeroSectionForm.vue';
import StatisticsForm from '../components/StatisticsForm.vue';
import AboutTeaserForm from '../components/AboutTeaserForm.vue';
import PortalTeaserForm from '../components/PortalTeaserForm.vue';

const {
  settings,
  saving,
  loading,
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
  animation: fadeIn 0.5s ease-out forwards;
}
.animate-slide-up {
  animation: slideUp 0.6s ease-out forwards;
}
@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}
@keyframes slideUp {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>
