<template>
  <div class="space-y-6 animate-fade-in pb-16">
    <!-- Header: Ambient luxury hero, live clock & primary actions -->
    <ServicesHeader 
      :saving="saving" 
      :loading="loading" 
      :is-dirty="isDirty" 
      @save="saveContent" 
      @refresh="reloadAll" 
    />

    <!-- Skeleton Preloader (Zero Layout Shift) -->
    <ServicesSkeleton v-if="loading" />

    <!-- Loaded Content Workspace -->
    <div v-else class="space-y-6 animate-slide-up">
      <!-- High-Density KPI / Section Stat Cards -->
      <ServicesStats 
        :content="content"
        :services="catalogServices"
        :categories="categories"
      />

      <!-- Section Navigation Tabs & Interactive Preview Switcher -->
      <ServicesNavTabs v-model:active-tab="activeTab" />

      <!-- Interactive Public Simulation Mode -->
      <div v-if="activeTab === 'preview'" class="transition-all duration-300">
        <ServicesLivePreview 
          :content="content"
          @exit="activeTab = 'all'"
        />
      </div>

      <!-- Editor Forms Container -->
      <div v-else class="space-y-6">
        <!-- 1. Page Header Copy & Headline Form -->
        <div v-show="activeTab === 'all' || activeTab === 'copy'">
          <ServicesCopyForm :content="content" />
        </div>

        <!-- 2. Catalog Integration Bridge -->
        <div v-show="activeTab === 'all' || activeTab === 'catalog'">
          <ServicesCatalogInfo 
            :services="catalogServices"
            :categories="categories"
          />
        </div>

        <!-- Bottom Action Bar -->
        <div class="rounded-2xl border border-white/[0.08] bg-[#12161C] p-5 flex flex-col sm:flex-row items-center justify-between gap-4 shadow-xl">
          <div class="flex items-center gap-2.5 text-xs text-white/50">
            <span class="h-2 w-2 rounded-full" :class="isDirty ? 'bg-amber-400' : 'bg-emerald-400'"></span>
            <span>{{ isDirty ? 'Unsaved modifications pending' : 'All services content in sync with production' }}</span>
            <span v-if="lastSaved" class="text-white/30">• Last synced {{ lastSaved }}</span>
          </div>

          <button 
            @click="saveContent" 
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
    <ServicesStickyBar :is-dirty="isDirty" :saving="saving" @save="saveContent" />
  </div>
</template>

<script setup>
import { onMounted } from 'vue';
import { CheckIcon } from '@heroicons/vue/24/outline';
import { useServicesCms } from './composables/useServicesCms';
import ServicesHeader from './components/services/ServicesHeader.vue';
import ServicesStats from './components/services/ServicesStats.vue';
import ServicesSkeleton from './components/services/ServicesSkeleton.vue';
import ServicesNavTabs from './components/services/ServicesNavTabs.vue';
import ServicesCopyForm from './components/services/ServicesCopyForm.vue';
import ServicesCatalogInfo from './components/services/ServicesCatalogInfo.vue';
import ServicesLivePreview from './components/services/ServicesLivePreview.vue';
import ServicesStickyBar from './components/services/ServicesStickyBar.vue';

const {
  content,
  saving,
  loading,
  lastSaved,
  isDirty,
  activeTab,
  catalogServices,
  categories,
  fetchContent,
  fetchCatalog,
  saveContent
} = useServicesCms();

const reloadAll = () => {
  fetchContent();
  fetchCatalog();
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
