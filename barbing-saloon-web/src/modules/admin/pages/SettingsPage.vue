<script setup>
import AdminLayout from '@/portals/admin/layouts/AdminLayout.vue'
import { useAdminSettings, SETTINGS_CATEGORIES } from '../composables/useAdminSettings'
import SettingsHeader from '../components/settings/SettingsHeader.vue'
import SettingsTabs from '../components/settings/SettingsTabs.vue'
import SettingsGroupCard from '../components/settings/SettingsGroupCard.vue'
import SettingsSkeleton from '../components/settings/SettingsSkeleton.vue'

const {
  rawSettings,
  loading,
  saving,
  activeTab,
  searchQuery,
  isDirty,
  categorizedSettings,
  categoryCounts,
  fetchSettings,
  saveSettings,
  revertChanges,
} = useAdminSettings()
</script>

<template>
  <AdminLayout>
    <div class="space-y-6 animate-fade-in max-w-7xl mx-auto pb-12">
      <!-- Executive Header -->
      <SettingsHeader
        :loading="loading"
        :saving="saving"
        :is-dirty="isDirty"
        @save="saveSettings"
        @revert="revertChanges"
        @refresh="fetchSettings"
      />

      <!-- Preloading Skeleton on Initial Load -->
      <SettingsSkeleton v-if="loading && !Object.keys(rawSettings).length" />

      <!-- Active Content -->
      <template v-else>
        <!-- Category Navigation & Real-time Filter -->
        <SettingsTabs
          :categories="SETTINGS_CATEGORIES"
          v-model:active-tab="activeTab"
          v-model:search-query="searchQuery"
          :category-counts="categoryCounts"
        />

        <!-- Category Configuration Card -->
        <SettingsGroupCard
          :categorized-settings="categorizedSettings"
          :categories="SETTINGS_CATEGORIES"
          :active-tab="activeTab"
          :search-query="searchQuery"
          :raw-settings="rawSettings"
        />
      </template>
    </div>
  </AdminLayout>
</template>
