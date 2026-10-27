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

    <!-- Preloader Skeleton -->
    <CookieEditorSkeleton v-if="loading" />

    <!-- Loaded Studio View -->
    <template v-else>
      <!-- Luxury Cookie Header -->
      <CookieEditorHeader
        :active-view="activeView"
        :has-changes="hasChanges"
        :loading="loading"
        :saving="saving"
        @save="saveContent"
        @refresh="fetchContent"
        @reset-defaults="resetToDefaults"
        @update:active-view="activeView = $event"
      />

      <!-- Studio Editor View -->
      <div v-show="activeView === 'editor'" class="space-y-6">
        <!-- Introduction Copy Card -->
        <CookieIntroCard :content="content" />

        <!-- 4 Consent Categories List -->
        <CookieCategoriesList
          :categories="categories"
          :content="content"
        />
      </div>

      <!-- Live Consent Modal Simulation -->
      <div v-show="activeView === 'preview'">
        <CookieModalPreview
          :categories="categories"
          :content="content"
        />
      </div>

      <!-- Sticky Mobile Save Bar -->
      <div v-if="hasChanges" class="flex justify-end pt-2 sm:hidden">
        <button
          type="button"
          @click="saveContent"
          :disabled="saving"
          class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-[#FF6700] to-amber-500 py-3 text-xs font-bold text-white shadow-lg shadow-[#FF6700]/25 transition-all"
        >
          <span v-if="saving" class="h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
          <span>{{ saving ? 'Saving Changes...' : 'Save Preferences' }}</span>
        </button>
      </div>
    </template>
  </div>
</template>

<script setup>
import CookieEditorHeader from '../../components/cms-cookies/CookieEditorHeader.vue';
import CookieIntroCard from '../../components/cms-cookies/CookieIntroCard.vue';
import CookieCategoriesList from '../../components/cms-cookies/CookieCategoriesList.vue';
import CookieModalPreview from '../../components/cms-cookies/CookieModalPreview.vue';
import CookieEditorSkeleton from '../../components/cms-cookies/CookieEditorSkeleton.vue';
import { useAdminCookieCms } from '../../composables/useAdminCookieCms';

const {
  categories,
  loading,
  saving,
  error,
  activeView,
  content,
  hasChanges,
  resetToDefaults,
  fetchContent,
  saveContent
} = useAdminCookieCms();
</script>
