import { ref, computed, onMounted } from 'vue'
import client from '@/shared/api/client'
import { useToast } from '@/core/composables/useToast'

export function useAdminBackups() {
  const toast = useToast()

  // ── State ──
  const backups = ref([])
  const loading = ref(true)
  const totalCount = ref(0)
  const searchQuery = ref('')
  const extensionFilter = ref('')

  // Action Loading States
  const creating = ref(false)
  const restoring = ref(false)
  const uploading = ref(false)
  const deleting = ref({})

  // Modal States
  const showCreateModal = ref(false)
  const showRestoreModal = ref(false)
  const showUploadModal = ref(false)
  const selectedBackup = ref(null)
  const customFilename = ref('')
  const uploadFile = ref(null)

  // ── Fetch Backups ──
  const fetchBackups = async (silent = false) => {
    loading.value = true
    try {
      const res = await client.get('/admin/system/backups')
      const data = res.data?.data || res.data || {}
      backups.value = data.backups || []
      totalCount.value = data.total_count ?? backups.value.length
      if (!silent) toast.success('Snapshot registry synchronized')
    } catch (err) {
      toast.error('Failed to query database snapshots')
      console.error('Backups fetch error:', err)
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
      const res = await client.post('/admin/system/backups', payload)
      const data = res.data?.data || res.data || {}
      toast.success(`Database snapshot created: ${data.filename || 'Success'}`)
      showCreateModal.value = false
      customFilename.value = ''
      await fetchBackups(true)
    } catch (err) {
      const msg = err.response?.data?.message || 'Snapshot generation failed'
      toast.error(msg)
      console.error('Create backup error:', err)
    } finally {
      creating.value = false
    }
  }

  // ── Restore Backup ──
  const openRestoreModal = (backup) => {
    selectedBackup.value = backup
    showRestoreModal.value = true
  }

  const closeRestoreModal = () => {
    showRestoreModal.value = false
    selectedBackup.value = null
  }

  const executeRestore = async () => {
    if (!selectedBackup.value) return
    restoring.value = true
    try {
      await client.post('/admin/system/backups/restore', {
        filename: selectedBackup.value.filename,
        confirm: true,
      })
      toast.success(`Database successfully restored from: ${selectedBackup.value.filename}`)
      closeRestoreModal()
      await fetchBackups(true)
    } catch (err) {
      const msg = err.response?.data?.message || 'Database restoration failed'
      toast.error(msg)
      console.error('Restore error:', err)
    } finally {
      restoring.value = false
    }
  }

  // ── Download Backup ──
  const downloadBackup = async (filename) => {
    try {
      toast.info(`Preparing download: ${filename}…`)
      const res = await client.get(`/admin/system/backups/${encodeURIComponent(filename)}/download`, {
        responseType: 'blob',
      })
      const blob = new Blob([res.data])
      const url = window.URL.createObjectURL(blob)
      const link = document.createElement('a')
      link.href = url
      link.download = filename
      document.body.appendChild(link)
      link.click()
      document.body.removeChild(link)
      window.URL.revokeObjectURL(url)
      toast.success(`Snapshot downloaded: ${filename}`)
    } catch (err) {
      toast.error('Snapshot download failed')
      console.error('Download error:', err)
    }
  }

  // ── Upload Backup ──
  const handleFileSelect = (file) => {
    uploadFile.value = file
  }

  const executeUpload = async () => {
    if (!uploadFile.value) {
      toast.error('Please select a valid backup archive (.sql, .sqlite, .gz)')
      return
    }
    uploading.value = true
    try {
      const formData = new FormData()
      formData.append('backup_file', uploadFile.value)
      await client.post('/admin/system/backups/upload', formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
      })
      toast.success('Backup archive uploaded and verified')
      showUploadModal.value = false
      uploadFile.value = null
      await fetchBackups(true)
    } catch (err) {
      const msg = err.response?.data?.message || 'Snapshot upload failed'
      toast.error(msg)
      console.error('Upload error:', err)
    } finally {
      uploading.value = false
    }
  }

  // ── Delete Backup ──
  const deleteBackup = async (filename) => {
    if (!confirm(`Permanently purge snapshot "${filename}"? This action cannot be reversed.`)) return
    deleting.value[filename] = true
    try {
      await client.delete(`/admin/system/backups/${encodeURIComponent(filename)}`)
      toast.success(`Purged snapshot: ${filename}`)
      await fetchBackups(true)
    } catch (err) {
      toast.error('Snapshot deletion failed')
      console.error('Delete error:', err)
    } finally {
      delete deleting.value[filename]
    }
  }

  // ── Computed Filtered Backups ──
  const filteredBackups = computed(() => {
    let list = backups.value || []
    if (searchQuery.value.trim()) {
      const q = searchQuery.value.trim().toLowerCase()
      list = list.filter(b => b.filename.toLowerCase().includes(q))
    }
    if (extensionFilter.value) {
      list = list.filter(b => (b.extension || '').toLowerCase() === extensionFilter.value.toLowerCase())
    }
    return list
  })

  // ── Computed Metrics Summary ──
  const summary = computed(() => {
    const list = backups.value || []
    const totalBytes = list.reduce((sum, b) => sum + (b.size_bytes || 0), 0)
    const totalMb = (totalBytes / (1024 * 1024)).toFixed(2)
    const validCount = list.filter(b => b.is_valid).length
    const latest = list.length > 0 ? list[0].created_at : null

    return {
      total: totalCount.value,
      valid: validCount,
      totalSize: totalBytes > 0 ? `${totalMb} MB` : '0 MB',
      latestTime: latest,
    }
  })

  // ── Helpers ──
  const formatTime = (iso) => iso ? new Date(iso).toLocaleString('en-US', { dateStyle: 'medium', timeStyle: 'short' }) : '—'

  const extColor = (ext) => {
    const map = {
      sql: 'bg-blue-500/10 text-blue-400 border-blue-500/20',
      sqlite: 'bg-purple-500/10 text-purple-400 border-purple-500/20',
      gz: 'bg-amber-500/10 text-amber-400 border-amber-500/20',
    }
    return map[ext] || 'bg-zinc-500/10 text-zinc-400 border-zinc-500/20'
  }

  onMounted(() => fetchBackups(true))

  return {
    backups,
    loading,
    totalCount,
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
  }
}

