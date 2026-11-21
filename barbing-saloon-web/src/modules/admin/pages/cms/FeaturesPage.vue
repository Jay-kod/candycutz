<template>
  <div class="space-y-6 pb-12">
    <!-- Error Alert Banner -->
    <div
      v-if="error"
      class="flex items-center justify-between rounded-2xl border border-rose-500/20 bg-rose-500/10 p-4 text-xs text-rose-400"
    >
      <span>{{ error }}</span>
      <button
        type="button"
        @click="fetchContent"
        class="rounded-lg bg-rose-500/20 px-3 py-1 font-semibold text-rose-300 hover:bg-rose-500/30 transition-colors"
      >
        Retry
      </button>
    </div>

    <!-- Content-aware Zero-CLS Skeleton -->
    <FeaturesSkeleton v-if="loading" />

    <!-- Loaded Studio View -->
    <template v-else>
      <!-- Header with Dirty Tracking & Actions -->
      <FeaturesHeader
        :feature-count="items.length"
        :has-changes="hasChanges"
        :loading="loading"
        :saving="saving"
        @save="saveContent"
        @refresh="fetchContent"
        @reset-defaults="resetDefaults"
      />

      <!-- Live Storefront Simulation -->
      <FeaturesPreview
        :content="content"
        :items="items"
      />

      <!-- Heading & Narrative Lead Form -->
      <FeaturesHeadingForm
        :content="content"
      />

      <!-- Feature Columns & Icon Manager -->
      <FeaturesItemsManager
        :items="items"
        :available-icons="availableIcons"
        :resolve-icon-component="resolveIconComponent"
        @add-feature="addFeature"
        @remove-feature="removeFeature"
      />

      <!-- Sticky Mobile Save Bar -->
      <div v-if="hasChanges" class="flex justify-end pt-2 sm:hidden">
        <button
          type="button"
          @click="saveContent"
          :disabled="saving"
          class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-[#FF6700] to-amber-500 py-3 text-xs font-bold text-white shadow-lg shadow-[#FF6700]/25 transition-all"
        >
          <span v-if="saving" class="h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
          <span>{{ saving ? 'Saving Changes...' : 'Save Features Block' }}</span>
        </button>
      </div>
    </template>
  </div>
</template>

<script setup>
import FeaturesHeader from '../../components/cms-features/FeaturesHeader.vue';
import FeaturesPreview from '../../components/cms-features/FeaturesPreview.vue';
import FeaturesHeadingForm from '../../components/cms-features/FeaturesHeadingForm.vue';
import FeaturesItemsManager from '../../components/cms-features/FeaturesItemsManager.vue';
import FeaturesSkeleton from '../../components/cms-features/FeaturesSkeleton.vue';
import { useAdminFeaturesCms } from '../../composables/useAdminFeaturesCms';

const {
  loading,
  saving,
  error,
  hasChanges,
  content,
  items,
  availableIcons,
  resolveIconComponent,
  addFeature,
  removeFeature,
  resetDefaults,
  fetchContent,
  saveContent
} = useAdminFeaturesCms();
</script>
