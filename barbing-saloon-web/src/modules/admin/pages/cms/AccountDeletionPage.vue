<template>
  <div class="space-y-6 animate-fade-in pb-16">
    <!-- Header: Ambient luxury hero, live clock & primary actions -->
    <AccountDeletionHeader 
      :saving="saving" 
      :loading="loading" 
      :is-dirty="isDirty" 
      @save="submit" 
      @refresh="fetchContent" 
    />

    <!-- Skeleton Preloader (Zero Layout Shift) -->
    <AccountDeletionSkeleton v-if="loading" />

    <!-- Loaded Content Workspace -->
    <div v-else class="space-y-6 animate-slide-up">
      <!-- High-Density KPI / Section Stat Cards -->
      <AccountDeletionStats 
        :content="content"
        :retention-notice-word-count="retentionNoticeWordCount"
        :is-compliant-with-app-store="isCompliantWithAppStore"
      />

      <!-- Section Navigation Tabs & Interactive Preview Switcher -->
      <AccountDeletionNavTabs v-model:active-tab="activeTab" />

      <!-- Interactive Public Simulation Mode -->
      <div v-if="activeTab === 'preview'" class="transition-all duration-300">
        <AccountDeletionLivePreview 
          :content="content"
          :preview-hero-image="previewHeroImage"
          :hero-opacity="heroOpacity"
          @exit="activeTab = 'all'"
        />
      </div>

      <!-- Editor Forms Container -->
      <div v-else class="space-y-6">
        <!-- 1. Headline & Hero Cover Form -->
        <div v-show="activeTab === 'all' || activeTab === 'hero'">
          <AccountDeletionHeroForm 
            ref="heroFormRef"
            :content="content"
            v-model:hero-opacity="heroOpacity"
            :preview-hero-image="previewHeroImage"
            @image-selected="selectHeroImage"
            @clear-image="handleClearImage"
          />
        </div>

        <!-- 2. Statutory Retention & Legal Notice Form -->
        <div v-show="activeTab === 'all' || activeTab === 'retention'">
          <AccountDeletionRetentionForm :content="content" />
        </div>

        <!-- 3. Form Field Placeholders Form -->
        <div v-show="activeTab === 'all' || activeTab === 'placeholders'">
          <AccountDeletionPlaceholdersForm :content="content" />
        </div>

        <!-- Bottom Action Bar -->
        <div class="rounded-2xl border border-white/[0.08] bg-[#12161C] p-5 flex flex-col sm:flex-row items-center justify-between gap-4 shadow-xl">
          <div class="flex items-center gap-2.5 text-xs text-white/50">
            <span class="h-2 w-2 rounded-full" :class="isDirty ? 'bg-amber-400' : 'bg-emerald-400'"></span>
            <span>{{ isDirty ? 'Unsaved modifications pending' : 'All account deletion settings in sync with production' }}</span>
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
    <AccountDeletionStickyBar :is-dirty="isDirty" :saving="saving" @save="submit" />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { CheckIcon } from '@heroicons/vue/24/outline';
import { useAccountDeletionCms } from './composables/useAccountDeletionCms';
import AccountDeletionHeader from './components/account-deletion/AccountDeletionHeader.vue';
import AccountDeletionStats from './components/account-deletion/AccountDeletionStats.vue';
import AccountDeletionSkeleton from './components/account-deletion/AccountDeletionSkeleton.vue';
import AccountDeletionNavTabs from './components/account-deletion/AccountDeletionNavTabs.vue';
import AccountDeletionHeroForm from './components/account-deletion/AccountDeletionHeroForm.vue';
import AccountDeletionRetentionForm from './components/account-deletion/AccountDeletionRetentionForm.vue';
import AccountDeletionPlaceholdersForm from './components/account-deletion/AccountDeletionPlaceholdersForm.vue';
import AccountDeletionLivePreview from './components/account-deletion/AccountDeletionLivePreview.vue';
import AccountDeletionStickyBar from './components/account-deletion/AccountDeletionStickyBar.vue';

const {
  content,
  saving,
  loading,
  lastSaved,
  isDirty,
  activeTab,
  heroOpacity,
  previewHeroImage,
  retentionNoticeWordCount,
  isCompliantWithAppStore,
  selectHeroImage,
  clearHeroImage,
  fetchContent,
  saveContent
} = useAccountDeletionCms();

const heroFormRef = ref(null);

const handleClearImage = () => {
  clearHeroImage(heroFormRef.value?.imageInput);
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
