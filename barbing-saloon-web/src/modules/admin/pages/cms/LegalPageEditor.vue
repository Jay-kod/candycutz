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
    <LegalEditorSkeleton v-if="loading" />

    <!-- Loaded Studio View -->
    <template v-else>
      <!-- Luxury Legal Header -->
      <LegalEditorHeader
        :page-title="pageTitle"
        :description="description"
        :section-count="sections.length"
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
        <!-- Page Title Card -->
        <LegalPageHeaderCard
          v-model="title"
          :setting-prefix="settingPrefix"
        />

        <!-- Sections List -->
        <LegalSectionsList
          :sections="sections"
          @add-section="addSection"
          @remove-section="removeSection"
        />
      </div>

      <!-- Live Document Preview View -->
      <div v-show="activeView === 'preview'">
        <LegalDocumentPreview
          :title="title"
          :sections="sections"
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
          <span>{{ saving ? 'Saving Document...' : 'Save Document' }}</span>
        </button>
      </div>
    </template>
  </div>
</template>

<script setup>
import LegalEditorHeader from '../../components/cms-legal/LegalEditorHeader.vue';
import LegalPageHeaderCard from '../../components/cms-legal/LegalPageHeaderCard.vue';
import LegalSectionsList from '../../components/cms-legal/LegalSectionsList.vue';
import LegalDocumentPreview from '../../components/cms-legal/LegalDocumentPreview.vue';
import LegalEditorSkeleton from '../../components/cms-legal/LegalEditorSkeleton.vue';
import { useAdminLegalEditor } from '../../composables/useAdminLegalEditor';

const props = defineProps({
  settingPrefix: { type: String, required: true },
  pageTitle: { type: String, required: true },
  description: { type: String, required: true },
  defaultSections: { type: Array, default: () => [] },
});

const {
  loading,
  saving,
  error,
  activeView,
  title,
  sections,
  hasChanges,
  addSection,
  removeSection,
  resetToDefaults,
  fetchContent,
  saveContent
} = useAdminLegalEditor(props);
</script>
