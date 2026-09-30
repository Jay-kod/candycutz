<script setup>
import { ref, onMounted, computed } from 'vue'
import AdminLayout from '@/portals/admin/layouts/AdminLayout.vue'
import { adminApi } from '@/shared/api/old_adminApi'
import { useToast } from '@/core/composables/useToast'
import {
  CircleStackIcon,
  CloudArrowDownIcon,
  CloudArrowUpIcon,
  ArrowPathIcon,
  TrashIcon,
  PlusIcon,
  ArrowDownTrayIcon,
  ShieldExclamationIcon,
  CheckCircleIcon,
  XMarkIcon,
  DocumentIcon,
  FolderIcon,
  ClockIcon,
  ExclamationTriangleIcon,
  ServerStackIcon,
  ArchiveBoxIcon,
} from '@heroicons/vue/24/outline'

const toast = useToast()

// ── State ──
const backups = ref([])
const loading = ref(true)
const creating = ref(false)
const restoring = ref(false)
const uploading = ref(false)
const customFilename = ref('')
const showCreateModal = ref(false)
const showRestoreModal = ref(false)
const showUploadModal = ref(false)
const selectedBackup = ref(null)
const uploadFile = ref(null)
const totalCount = ref(0)

// ── Fetch Backups ──
const fetchBackups = async () => {
  loading.value = true
  try {
    const res = await adminApi.backups()
    const data = res.data?.data || {}
    backups.value = data.backups || []
    totalCount.value = data.total_count || 0
  } catch (err) {
    toast.error('Failed to load backups')
    console.error(err)
  } finally {
    loading.value = false
  }
}

// ── Create Backup ──
const createBackup = async () => {
  creating.value = true
  try {
    const payload = {}
    if (customFilename.value.trim()) {
      payload.filename = customFilename.value.trim()
    }
    const res = await adminApi.createBackup(payload)
    const data = res.data?.data || {}
    toast.success(`Backup created: ${data.filename || 'Success'}`)
    showCreateModal.value = false
    customFilename.value = ''
    await fetchBackups()
  } catch (err) {
    const msg = err.response?.data?.message || 'Backup creation failed'
    toast.error(msg)
    console.error(err)
  } finally {
    creating.value = false
  }
}

// ── Restore Backup ──
const confirmRestore = (backup) => {
  selectedBackup.value = backup
  showRestoreModal.value = true
}

const executeRestore = async () => {
  if (!selectedBackup.value) return
  restoring.value = true
  try {
    await adminApi.restoreBackup({
      filename: selectedBackup.value.filename,
      confirm: true,
    })
    toast.success(`Database restored from: ${selectedBackup.value.filename}`)
    showRestoreModal.value = false
    selectedBackup.value = null
  } catch (err) {
    const msg = err.response?.data?.message || 'Restore failed'
    toast.error(msg)
    console.error(err)
  } finally {
    restoring.value = false
  }
}

// ── Download Backup ──
const downloadBackup = async (filename) => {
  try {
    const res = await adminApi.downloadBackup(filename)
    const blob = new Blob([res.data])
    const url = window.URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url
    link.download = filename
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
    window.URL.revokeObjectURL(url)
    toast.success(`Downloading: ${filename}`)
  } catch (err) {
    toast.error('Download failed')
    console.error(err)
  }
}

// ── Upload Backup ──
const handleFileSelect = (event) => {
  uploadFile.value = event.target.files[0] || null
}

const executeUpload = async () => {
  if (!uploadFile.value) {
    toast.error('Please select a backup file')
    return
  }
  uploading.value = true
  try {
    const formData = new FormData()
    formData.append('backup_file', uploadFile.value)
    await adminApi.uploadBackup(formData)
    toast.success('Backup uploaded successfully')
    showUploadModal.value = false
    uploadFile.value = null
    await fetchBackups()
  } catch (err) {
    const msg = err.response?.data?.message || 'Upload failed'
    toast.error(msg)
    console.error(err)
  } finally {
    uploading.value = false
  }
}

// ── Delete Backup ──
const deleteBackup = async (filename) => {
  if (!confirm(`Delete backup "${filename}"? This cannot be undone.`)) return
  try {
    await adminApi.deleteBackup(filename)
    toast.success(`Deleted: ${filename}`)
    await fetchBackups()
  } catch (err) {
    toast.error('Delete failed')
    console.error(err)
  }
}

// ── Helpers ──
const formatTime = (iso) => {
  if (!iso) return '—'
  return new Date(iso).toLocaleString('en-US', { dateStyle: 'medium', timeStyle: 'short' })
}

const extColor = (ext) => {
  const map = {
    sql: 'bg-blue-500/10 text-blue-400 border-blue-500/20',
    sqlite: 'bg-purple-500/10 text-purple-400 border-purple-500/20',
    gz: 'bg-amber-500/10 text-amber-400 border-amber-500/20',
  }
  return map[ext] || 'bg-zinc-500/10 text-zinc-400 border-zinc-500/20'
}

onMounted(fetchBackups)
</script>

<template>
  <AdminLayout>
    <section class="space-y-8 animate-fade-in">
      <!-- Page Header -->
      <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold text-white flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500/20 to-indigo-500/20 border border-blue-500/20 flex items-center justify-center">
              <ServerStackIcon class="w-5 h-5 text-blue-400" />
            </div>
            Backup & Restore
          </h1>
          <p class="text-sm text-zinc-400 mt-1">Manage database snapshots and disaster recovery</p>
        </div>
        <div class="flex items-center gap-3">
          <button
            @click="showUploadModal = true"
            class="flex items-center gap-2 px-4 py-2 rounded-lg bg-white/[0.03] border border-white/[0.06] text-zinc-300 hover:bg-white/[0.06] transition-all text-xs font-medium"
          >
            <CloudArrowUpIcon class="w-4 h-4" />
            Upload Backup
          </button>
          <button
            @click="showCreateModal = true"
            class="flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-blue-600/20 to-indigo-600/20 border border-blue-500/30 text-blue-300 hover:from-blue-600/30 hover:to-indigo-600/30 transition-all text-sm font-medium"
          >
            <PlusIcon class="w-4 h-4" />
            Create Backup
          </button>
        </div>
      </div>

      <!-- Summary Cards -->
      <div class="grid gap-4 grid-cols-2 lg:grid-cols-4">
        <div class="rounded-xl p-4 bg-blue-500/5 border border-blue-500/15">
          <div class="flex items-center gap-2 mb-2">
            <ArchiveBoxIcon class="w-5 h-5 text-blue-400" />
          </div>
          <p class="text-xs text-zinc-400 mb-0.5">Total Backups</p>
          <p class="text-2xl font-bold text-white">{{ totalCount }}</p>
        </div>
        <div class="rounded-xl p-4 bg-emerald-500/5 border border-emerald-500/15">
          <div class="flex items-center gap-2 mb-2">
            <CheckCircleIcon class="w-5 h-5 text-emerald-400" />
          </div>
          <p class="text-xs text-zinc-400 mb-0.5">Valid Backups</p>
          <p class="text-2xl font-bold text-white">{{ backups.filter(b => b.is_valid).length }}</p>
        </div>
        <div class="rounded-xl p-4 bg-purple-500/5 border border-purple-500/15">
          <div class="flex items-center gap-2 mb-2">
            <FolderIcon class="w-5 h-5 text-purple-400" />
          </div>
          <p class="text-xs text-zinc-400 mb-0.5">Total Size</p>
          <p class="text-lg font-bold text-white">
            {{ backups.length ? (backups.reduce((sum, b) => sum + (b.size_bytes || 0), 0) / 1048576).toFixed(2) + ' MB' : '—' }}
          </p>
        </div>
        <div class="rounded-xl p-4 bg-amber-500/5 border border-amber-500/15">
          <div class="flex items-center gap-2 mb-2">
            <ClockIcon class="w-5 h-5 text-amber-400" />
          </div>
          <p class="text-xs text-zinc-400 mb-0.5">Latest Backup</p>
          <p class="text-sm font-semibold text-white">{{ backups.length ? formatTime(backups[0].created_at) : 'None' }}</p>
        </div>
      </div>

      <!-- Backups Table -->
      <div class="rounded-2xl bg-white/[0.02] border border-white/[0.06] overflow-hidden">
        <div class="px-5 py-4 border-b border-white/[0.06] flex items-center justify-between">
          <div class="flex items-center gap-3">
            <CircleStackIcon class="w-5 h-5 text-blue-400" />
            <h2 class="text-sm font-semibold text-white">Database Backups</h2>
          </div>
          <button
            @click="fetchBackups"
            :disabled="loading"
            class="text-xs text-zinc-400 hover:text-white transition-colors flex items-center gap-1"
          >
            <ArrowPathIcon class="w-3.5 h-3.5" :class="{ 'animate-spin': loading }" />
            Refresh
          </button>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="p-8 flex justify-center">
          <ArrowPathIcon class="w-6 h-6 text-zinc-400 animate-spin" />
        </div>

        <!-- Empty State -->
        <div v-else-if="!backups.length" class="p-12 text-center">
          <ArchiveBoxIcon class="w-12 h-12 text-zinc-600 mx-auto mb-3" />
          <p class="text-zinc-400 text-sm">No backups found</p>
          <p class="text-zinc-500 text-xs mt-1">Create your first backup to protect your data</p>
          <button
            @click="showCreateModal = true"
            class="mt-4 px-4 py-2 rounded-lg bg-blue-600/20 border border-blue-500/30 text-blue-300 text-sm hover:bg-blue-600/30 transition-all"
          >
            Create First Backup
          </button>
        </div>

        <!-- Backup List -->
        <div v-else class="divide-y divide-white/[0.04]">
          <div
            v-for="backup in backups"
            :key="backup.filename"
            class="px-5 py-4 flex items-center justify-between hover:bg-white/[0.02] transition-colors group"
          >
            <div class="flex items-center gap-4 flex-1 min-w-0">
              <div class="w-10 h-10 rounded-lg bg-white/[0.04] border border-white/[0.06] flex items-center justify-center flex-shrink-0">
                <DocumentIcon class="w-5 h-5 text-zinc-400" />
              </div>
              <div class="min-w-0 flex-1">
                <p class="text-sm text-white font-mono truncate">{{ backup.filename }}</p>
                <div class="flex items-center gap-3 mt-1">
                  <span class="text-xs text-zinc-500">{{ backup.size_formatted }}</span>
                  <span class="text-xs text-zinc-600">·</span>
                  <span class="text-xs text-zinc-500">{{ formatTime(backup.created_at) }}</span>
                </div>
              </div>
            </div>

            <div class="flex items-center gap-2 ml-4">
              <span :class="['text-[10px] px-2 py-0.5 rounded-full border font-medium uppercase', extColor(backup.extension)]">
                {{ backup.extension }}
              </span>
              <span v-if="!backup.is_valid" class="text-[10px] px-2 py-0.5 rounded-full bg-red-500/10 text-red-400 border border-red-500/20">
                Invalid
              </span>

              <button
                @click="downloadBackup(backup.filename)"
                class="p-2 rounded-lg text-zinc-400 hover:text-blue-400 hover:bg-blue-500/10 transition-all"
                title="Download"
              >
                <ArrowDownTrayIcon class="w-4 h-4" />
              </button>
              <button
                v-if="backup.is_valid"
                @click="confirmRestore(backup)"
                class="p-2 rounded-lg text-zinc-400 hover:text-emerald-400 hover:bg-emerald-500/10 transition-all"
                title="Restore"
              >
                <ArrowPathIcon class="w-4 h-4" />
              </button>
              <button
                @click="deleteBackup(backup.filename)"
                class="p-2 rounded-lg text-zinc-400 hover:text-red-400 hover:bg-red-500/10 transition-all opacity-0 group-hover:opacity-100"
                title="Delete"
              >
                <TrashIcon class="w-4 h-4" />
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Create Backup Modal -->
      <Teleport to="body">
        <div v-if="showCreateModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
          <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" @click="showCreateModal = false"></div>
          <div class="relative w-full max-w-md bg-zinc-900 border border-white/[0.08] rounded-2xl p-6 shadow-2xl">
            <div class="flex items-center justify-between mb-6">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-blue-500/10 border border-blue-500/20 flex items-center justify-center">
                  <PlusIcon class="w-5 h-5 text-blue-400" />
                </div>
                <h3 class="text-lg font-semibold text-white">Create Database Backup</h3>
              </div>
              <button @click="showCreateModal = false" class="p-1 rounded-lg hover:bg-white/[0.06] transition-colors">
                <XMarkIcon class="w-5 h-5 text-zinc-400" />
              </button>
            </div>
            <div class="space-y-4">
              <div>
                <label class="text-xs text-zinc-400 block mb-1.5">Custom Filename (optional)</label>
                <input
                  v-model="customFilename"
                  type="text"
                  placeholder="e.g. pre-migration-backup"
                  class="w-full px-4 py-2.5 rounded-lg bg-white/[0.04] border border-white/[0.08] text-white text-sm placeholder-zinc-500 focus:outline-none focus:border-blue-500/40 transition-colors"
                />
                <p class="text-[10px] text-zinc-500 mt-1">.sql extension will be added automatically if omitted</p>
              </div>
              <div class="flex gap-3 pt-2">
                <button
                  @click="showCreateModal = false"
                  class="flex-1 py-2.5 rounded-lg bg-white/[0.04] border border-white/[0.06] text-zinc-400 text-sm hover:bg-white/[0.06] transition-all"
                >
                  Cancel
                </button>
                <button
                  @click="createBackup"
                  :disabled="creating"
                  class="flex-1 py-2.5 rounded-lg bg-blue-600/20 border border-blue-500/30 text-blue-300 text-sm font-medium hover:bg-blue-600/30 transition-all disabled:opacity-50 flex items-center justify-center gap-2"
                >
                  <ArrowPathIcon v-if="creating" class="w-4 h-4 animate-spin" />
                  {{ creating ? 'Creating…' : 'Create Backup' }}
                </button>
              </div>
            </div>
          </div>
        </div>
      </Teleport>

      <!-- Restore Confirmation Modal -->
      <Teleport to="body">
        <div v-if="showRestoreModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
          <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" @click="showRestoreModal = false"></div>
          <div class="relative w-full max-w-md bg-zinc-900 border border-red-500/20 rounded-2xl p-6 shadow-2xl">
            <div class="flex items-center gap-3 mb-6">
              <div class="w-10 h-10 rounded-xl bg-red-500/10 border border-red-500/20 flex items-center justify-center">
                <ShieldExclamationIcon class="w-5 h-5 text-red-400" />
              </div>
              <div>
                <h3 class="text-lg font-semibold text-white">Confirm Database Restore</h3>
                <p class="text-xs text-red-400">This is a destructive operation</p>
              </div>
            </div>
            <div class="rounded-xl bg-red-500/5 border border-red-500/15 p-4 mb-6">
              <div class="flex items-start gap-3">
                <ExclamationTriangleIcon class="w-5 h-5 text-red-400 flex-shrink-0 mt-0.5" />
                <div class="text-xs text-zinc-300 space-y-2">
                  <p><strong class="text-red-400">Warning:</strong> Restoring a backup will <strong>overwrite all current data</strong> in the database with the snapshot.</p>
                  <p>File: <span class="font-mono text-white">{{ selectedBackup?.filename }}</span></p>
                  <p>Size: <span class="text-white">{{ selectedBackup?.size_formatted }}</span></p>
                  <p>Created: <span class="text-white">{{ formatTime(selectedBackup?.created_at) }}</span></p>
                </div>
              </div>
            </div>
            <div class="flex gap-3">
              <button
                @click="showRestoreModal = false; selectedBackup = null"
                class="flex-1 py-2.5 rounded-lg bg-white/[0.04] border border-white/[0.06] text-zinc-400 text-sm hover:bg-white/[0.06] transition-all"
              >
                Cancel
              </button>
              <button
                @click="executeRestore"
                :disabled="restoring"
                class="flex-1 py-2.5 rounded-lg bg-red-600/20 border border-red-500/30 text-red-300 text-sm font-medium hover:bg-red-600/30 transition-all disabled:opacity-50 flex items-center justify-center gap-2"
              >
                <ArrowPathIcon v-if="restoring" class="w-4 h-4 animate-spin" />
                {{ restoring ? 'Restoring…' : 'Restore Database' }}
              </button>
            </div>
          </div>
        </div>
      </Teleport>

      <!-- Upload Backup Modal -->
      <Teleport to="body">
        <div v-if="showUploadModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
          <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" @click="showUploadModal = false"></div>
          <div class="relative w-full max-w-md bg-zinc-900 border border-white/[0.08] rounded-2xl p-6 shadow-2xl">
            <div class="flex items-center justify-between mb-6">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-500/10 border border-indigo-500/20 flex items-center justify-center">
                  <CloudArrowUpIcon class="w-5 h-5 text-indigo-400" />
                </div>
                <h3 class="text-lg font-semibold text-white">Upload Backup File</h3>
              </div>
              <button @click="showUploadModal = false" class="p-1 rounded-lg hover:bg-white/[0.06] transition-colors">
                <XMarkIcon class="w-5 h-5 text-zinc-400" />
              </button>
            </div>
            <div class="space-y-4">
              <div>
                <label class="text-xs text-zinc-400 block mb-1.5">Backup File (.sql, .sqlite, .gz)</label>
                <div class="relative">
                  <input
                    type="file"
                    accept=".sql,.sqlite,.gz"
                    @change="handleFileSelect"
                    class="w-full px-4 py-3 rounded-lg bg-white/[0.04] border border-white/[0.08] border-dashed text-zinc-400 text-sm file:mr-3 file:py-1 file:px-3 file:rounded-md file:border file:border-white/10 file:bg-white/[0.06] file:text-zinc-300 file:text-xs file:font-medium focus:outline-none focus:border-indigo-500/40 transition-colors"
                  />
                </div>
                <p class="text-[10px] text-zinc-500 mt-1">Maximum file size: 100MB</p>
              </div>
              <div class="flex gap-3 pt-2">
                <button
                  @click="showUploadModal = false; uploadFile = null"
                  class="flex-1 py-2.5 rounded-lg bg-white/[0.04] border border-white/[0.06] text-zinc-400 text-sm hover:bg-white/[0.06] transition-all"
                >
                  Cancel
                </button>
                <button
                  @click="executeUpload"
                  :disabled="uploading || !uploadFile"
                  class="flex-1 py-2.5 rounded-lg bg-indigo-600/20 border border-indigo-500/30 text-indigo-300 text-sm font-medium hover:bg-indigo-600/30 transition-all disabled:opacity-50 flex items-center justify-center gap-2"
                >
                  <ArrowPathIcon v-if="uploading" class="w-4 h-4 animate-spin" />
                  {{ uploading ? 'Uploading…' : 'Upload' }}
                </button>
              </div>
            </div>
          </div>
        </div>
      </Teleport>
    </section>
  </AdminLayout>
</template>
