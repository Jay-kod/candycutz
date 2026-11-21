<script setup>
import AdminLayout from '@/portals/admin/layouts/AdminLayout.vue'
import { useAdminBackups } from '../composables/useAdminBackups'
import BackupHeader from '../components/backups/BackupHeader.vue'
import BackupSummaryCards from '../components/backups/BackupSummaryCards.vue'
import BackupList from '../components/backups/BackupList.vue'
import BackupCreateModal from '../components/backups/BackupCreateModal.vue'
import BackupRestoreModal from '../components/backups/BackupRestoreModal.vue'
import BackupUploadModal from '../components/backups/BackupUploadModal.vue'
import BackupSkeleton from '../components/backups/BackupSkeleton.vue'

const {
  backups,
  loading,
  searchQuery,
  extensionFilter,
  filteredBackups,
  summary,
  creating,
  restoring,
  uploading,
  deleting,
  showCreateModal,
  showRestoreModal,
  showUploadModal,
  selectedBackup,
  customFilename,
  uploadFile,
  fetchBackups,
  createBackup,
  openRestoreModal,
  closeRestoreModal,
  executeRestore,
  downloadBackup,
  handleFileSelect,
  executeUpload,
  deleteBackup,
  formatTime,
  extColor,
} = useAdminBackups()
</script>

<template>
  <AdminLayout>
    <div class="space-y-8 animate-fade-in max-w-7xl mx-auto pb-12">
      <!-- Executive Header -->
      <BackupHeader
        :loading="loading"
        @refresh="fetchBackups(false)"
        @open-create="showCreateModal = true"
        @open-upload="showUploadModal = true"
      />

      <!-- Preloading Skeleton on Initial Load -->
      <BackupSkeleton v-if="loading && !backups.length" />

      <!-- Active Content -->
      <template v-else>
        <!-- 4 Summary Metric Cards -->
        <BackupSummaryCards
          :summary="summary"
          :loading="loading"
          :format-time="formatTime"
        />

        <!-- Backups List & Registry Table -->
        <BackupList
          :backups="filteredBackups"
          :loading="loading"
          v-model:search-query="searchQuery"
          v-model:extension-filter="extensionFilter"
          :deleting="deleting"
          :format-time="formatTime"
          :ext-color="extColor"
          @download="downloadBackup"
          @restore="openRestoreModal"
          @delete="deleteBackup"
          @open-create="showCreateModal = true"
        />
      </template>

      <!-- Create Snapshot Modal -->
      <BackupCreateModal
        :show="showCreateModal"
        :creating="creating"
        v-model="customFilename"
        @close="showCreateModal = false"
        @create="createBackup"
      />

      <!-- Confirm Restore Modal -->
      <BackupRestoreModal
        :show="showRestoreModal"
        :restoring="restoring"
        :selected-backup="selectedBackup"
        :format-time="formatTime"
        @close="closeRestoreModal"
        @restore="executeRestore"
      />

      <!-- Upload Snapshot Modal -->
      <BackupUploadModal
        :show="showUploadModal"
        :uploading="uploading"
        :upload-file="uploadFile"
        @close="showUploadModal = false; uploadFile = null"
        @file-select="handleFileSelect"
        @upload="executeUpload"
      />
    </div>
  </AdminLayout>
</template>
