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
        @click="loadSettings"
        class="rounded-lg bg-rose-500/20 px-3 py-1 font-semibold text-rose-300 hover:bg-rose-500/30 transition-colors"
      >
        Retry
      </button>
    </div>

    <!-- Preloader Skeleton -->
    <PageHeroesSkeleton v-if="loading" />

    <!-- Loaded Studio Environment -->
    <template v-else>
      <!-- Header with Dirty Tracking & Actions -->
      <PageHeroesHeader
        :page-label="currentPage.label"
        :has-changes="hasChanges"
        :loading="loading"
        :saving="saving"
        @save="save"
        @refresh="loadSettings"
        @reset-text="resetText"
      />

      <!-- Page Target Selector Toolbar -->
      <PageHeroesSelector
        :pages="pages"
        :selected-page="selectedPage"
        @select-page="(key) => { selectedPage = key; loadSelectedPage(); }"
      />

      <!-- Two-Column Studio Layout -->
      <div class="grid gap-6 xl:grid-cols-2">
        <!-- Left: Form Parameters & Media -->
        <PageHeroesForm
          :form="form"
          :selected-file="selectedFile"
          :preview-image="previewImage"
          :page-label="currentPage.label"
          @select-image="selectImage"
          @clear-image="clearImage"
          @reset-text="resetText"
        />

        <!-- Right: Live 40vh Hero Viewport Preview -->
        <PageHeroesPreview
          :selected-page="selectedPage"
          :page-label="currentPage.label"
          :default-title="currentPage.defaultTitle"
          :default-subtitle="currentPage.defaultSubtitle"
          :form="form"
          :preview-image="previewImage"
        />
      </div>

      <!-- Sticky Mobile Save Bar -->
      <div v-if="hasChanges" class="flex justify-end pt-2 sm:hidden">
        <button
          type="button"
          @click="save"
          :disabled="saving"
          class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-[#FF6700] to-amber-500 py-3 text-xs font-bold text-white shadow-lg shadow-[#FF6700]/25 transition-all"
        >
          <span v-if="saving" class="h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
          <span>{{ saving ? 'Saving Changes...' : `Save ${currentPage.label} Hero` }}</span>
        </button>
      </div>
    </template>
  </div>
</template>

<script setup>
import PageHeroesHeader from '../../components/cms-heroes/PageHeroesHeader.vue';
import PageHeroesSelector from '../../components/cms-heroes/PageHeroesSelector.vue';
import PageHeroesForm from '../../components/cms-heroes/PageHeroesForm.vue';
import PageHeroesPreview from '../../components/cms-heroes/PageHeroesPreview.vue';
import PageHeroesSkeleton from '../../components/cms-heroes/PageHeroesSkeleton.vue';
import { useAdminPageHeroes } from '../../composables/useAdminPageHeroes';

const {
  pages,
  loading,
  saving,
  error,
  selectedPage,
  currentPage,
  form,
  selectedFile,
  previewImage,
  hasChanges,
  loadSelectedPage,
  selectImage,
  clearImage,
  resetText,
  loadSettings,
  save
} = useAdminPageHeroes();
</script>
