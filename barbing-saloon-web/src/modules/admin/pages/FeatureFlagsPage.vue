<script setup>
import { ref, onMounted, computed } from 'vue'
import AdminLayout from '@/portals/admin/layouts/AdminLayout.vue'
import { adminApi } from '@/shared/api/old_adminApi'
import { useToast } from '@/core/composables/useToast'
import {
  FlagIcon,
  ArrowPathIcon,
  PlusIcon,
  PencilSquareIcon,
  TrashIcon,
  XMarkIcon,
  CheckCircleIcon,
  DevicePhoneMobileIcon,
  GlobeAltIcon,
  ExclamationTriangleIcon
} from '@heroicons/vue/24/outline'

const toast = useToast()

// ── State ──
const flags = ref([])
const loading = ref(true)
const modalOpen = ref(false)
const editingFlag = ref(null) // null = create, object = edit
const saving = ref(false)
const deletingId = ref(null)
const togglingId = ref(null)
const confirmDeleteModal = ref(null)

const form = ref({
  key: '',
  name: '',
  description: '',
  enabled_for: ['web', 'app'],
  rollout_percentage: 100,
  is_active: true,
})

// ── Data ──
const fetchFlags = async () => {
  loading.value = true
  try {
    const res = await adminApi.featureFlags()
    flags.value = res.data?.data || []
  } catch (err) {
    console.error('Failed to load feature flags:', err)
  } finally {
    loading.value = false
  }
}

const openCreateModal = () => {
  editingFlag.value = null
  form.value = { key: '', name: '', description: '', enabled_for: ['web', 'app'], rollout_percentage: 100, is_active: true }
  modalOpen.value = true
}

const openEditModal = (flag) => {
  editingFlag.value = flag
  form.value = {
    key: flag.key,
    name: flag.name,
    description: flag.description || '',
    enabled_for: Array.isArray(flag.enabled_for) ? [...flag.enabled_for] : ['web', 'app'],
    rollout_percentage: flag.rollout_percentage ?? 100,
    is_active: flag.is_active ?? true,
  }
  modalOpen.value = true
}

const saveFlag = async () => {
  if (!form.value.key || !form.value.name || !form.value.enabled_for.length) {
    toast.error('Key, name, and at least one platform are required.')
    return
  }
  saving.value = true
  try {
    if (editingFlag.value) {
      await adminApi.updateFeatureFlag(editingFlag.value.id, form.value)
      toast.success('Feature flag updated.')
    } else {
      await adminApi.createFeatureFlag(form.value)
      toast.success('Feature flag created.')
    }
    modalOpen.value = false
    await fetchFlags()
  } catch (err) {
    const msg = err.response?.data?.message || 'Failed to save feature flag.'
    toast.error(msg)
  } finally {
    saving.value = false
  }
}

const handleToggle = async (flag) => {
  togglingId.value = flag.id
  try {
    await adminApi.toggleFeatureFlag(flag.id)
    flag.is_active = !flag.is_active
    toast.success(`${flag.key} is now ${flag.is_active ? 'active' : 'inactive'}.`)
  } catch (err) {
    toast.error('Failed to toggle flag.')
  } finally {
    togglingId.value = null
  }
}

const handleDelete = async (flag) => {
  deletingId.value = flag.id
  try {
    await adminApi.deleteFeatureFlag(flag.id)
    flags.value = flags.value.filter(f => f.id !== flag.id)
    toast.success('Feature flag deleted.')
    confirmDeleteModal.value = null
  } catch (err) {
    toast.error('Failed to delete feature flag.')
  } finally {
    deletingId.value = null
  }
}

const togglePlatform = (platform) => {
  const idx = form.value.enabled_for.indexOf(platform)
  if (idx > -1) {
    if (form.value.enabled_for.length > 1) form.value.enabled_for.splice(idx, 1)
  } else {
    form.value.enabled_for.push(platform)
  }
}

// ── Helpers ──
const activeCount = computed(() => flags.value.filter(f => f.is_active).length)
const inactiveCount = computed(() => flags.value.filter(f => !f.is_active).length)

const formatDate = (d) => d ? new Date(d).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : '—'

// ── Init ──
onMounted(fetchFlags)
</script>

<template>
  <AdminLayout>
    <section class="space-y-6 animate-fade-in">
      <!-- Page Header -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
          <div class="p-2.5 rounded-xl bg-gradient-to-br from-amber-500/20 to-orange-600/20 border border-amber-500/20">
            <FlagIcon class="w-6 h-6 text-amber-400" />
          </div>
          <div>
            <h1 class="text-xl font-bold text-white">Feature Flags</h1>
            <p class="text-sm text-white/50">Control feature rollouts across platforms</p>
          </div>
        </div>
        <div class="flex gap-2">
          <button @click="fetchFlags" :disabled="loading"
            class="flex items-center gap-2 px-4 py-2 rounded-xl bg-white/[0.04] hover:bg-white/[0.08] border border-white/[0.06] text-white/70 hover:text-white transition-all text-sm">
            <ArrowPathIcon class="w-4 h-4" :class="{ 'animate-spin': loading }" />
            Refresh
          </button>
          <button @click="openCreateModal"
            class="flex items-center gap-2 px-4 py-2 rounded-xl bg-gradient-to-r from-amber-600 to-orange-600 hover:from-amber-500 hover:to-orange-500 text-white text-sm font-medium transition-all shadow-lg shadow-amber-500/15">
            <PlusIcon class="w-4 h-4" />
            New Flag
          </button>
        </div>
      </div>

      <!-- Summary Cards -->
      <div class="grid gap-4 grid-cols-3">
        <div class="p-5 rounded-2xl bg-white/[0.03] border border-white/[0.06] space-y-1">
          <p class="text-xs text-white/40 uppercase tracking-wider">Total Flags</p>
          <p class="text-2xl font-bold text-white">{{ flags.length }}</p>
        </div>
        <div class="p-5 rounded-2xl bg-white/[0.03] border border-white/[0.06] space-y-1">
          <p class="text-xs text-white/40 uppercase tracking-wider">Active</p>
          <p class="text-2xl font-bold text-emerald-400">{{ activeCount }}</p>
        </div>
        <div class="p-5 rounded-2xl bg-white/[0.03] border border-white/[0.06] space-y-1">
          <p class="text-xs text-white/40 uppercase tracking-wider">Inactive</p>
          <p class="text-2xl font-bold text-white/40">{{ inactiveCount }}</p>
        </div>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="space-y-3">
        <div v-for="i in 4" :key="i" class="h-20 rounded-2xl bg-white/[0.03] animate-pulse border border-white/[0.04]"></div>
      </div>

      <!-- Empty State -->
      <div v-else-if="!flags.length" class="text-center py-20">
        <FlagIcon class="w-14 h-14 text-white/10 mx-auto mb-4" />
        <p class="text-white/40 text-sm mb-4">No feature flags yet. Create your first flag to control feature rollouts.</p>
        <button @click="openCreateModal"
          class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-500 text-white text-sm font-medium transition-all">
          <PlusIcon class="w-4 h-4" /> Create First Flag
        </button>
      </div>

      <!-- Flags List -->
      <div v-else class="space-y-3">
        <div v-for="flag in flags" :key="flag.id"
          class="p-5 rounded-2xl bg-white/[0.03] border border-white/[0.06] hover:bg-white/[0.05] transition-all">
          <div class="flex items-start justify-between gap-4">
            <div class="flex-1 min-w-0">
              <div class="flex items-center gap-3 mb-2">
                <h3 class="text-sm font-semibold text-white">{{ flag.name }}</h3>
                <span class="px-2 py-0.5 rounded-md text-[10px] font-mono font-semibold bg-white/[0.04] text-white/50 border border-white/[0.06]">{{ flag.key }}</span>
                <span class="px-2 py-0.5 rounded-md text-[10px] font-semibold"
                  :class="flag.is_active ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-white/[0.04] text-white/30 border border-white/[0.06]'">
                  {{ flag.is_active ? 'Active' : 'Inactive' }}
                </span>
              </div>
              <p v-if="flag.description" class="text-xs text-white/40 mb-3">{{ flag.description }}</p>

              <div class="flex items-center gap-4 flex-wrap">
                <!-- Platform tags -->
                <div class="flex gap-1.5">
                  <span v-for="p in (Array.isArray(flag.enabled_for) ? flag.enabled_for : [])" :key="p"
                    class="flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-semibold"
                    :class="p === 'app'
                      ? 'bg-violet-500/10 text-violet-400 border border-violet-500/20'
                      : 'bg-cyan-500/10 text-cyan-400 border border-cyan-500/20'">
                    <DevicePhoneMobileIcon v-if="p === 'app'" class="w-3 h-3" />
                    <GlobeAltIcon v-else class="w-3 h-3" />
                    {{ p === 'app' ? 'App' : 'Web' }}
                  </span>
                </div>

                <!-- Rollout percentage -->
                <div class="flex items-center gap-2">
                  <div class="w-20 h-1.5 rounded-full bg-white/[0.06] overflow-hidden">
                    <div class="h-full rounded-full bg-gradient-to-r from-amber-500 to-orange-500 transition-all duration-500"
                      :style="{ width: `${flag.rollout_percentage ?? 100}%` }"></div>
                  </div>
                  <span class="text-[10px] text-white/40 font-mono">{{ flag.rollout_percentage ?? 100 }}%</span>
                </div>

                <span class="text-[10px] text-white/30">Created {{ formatDate(flag.created_at) }}</span>
              </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center gap-1.5 flex-shrink-0">
              <!-- Toggle Switch -->
              <button @click="handleToggle(flag)" :disabled="togglingId === flag.id"
                class="relative w-10 h-5.5 rounded-full transition-all duration-300 border flex-shrink-0"
                :class="flag.is_active
                  ? 'bg-emerald-500/20 border-emerald-500/40'
                  : 'bg-white/[0.06] border-white/[0.08]'">
                <div class="absolute top-0.5 w-4 h-4 rounded-full transition-all duration-300 shadow-sm"
                  :class="flag.is_active
                    ? 'left-[calc(100%-18px)] bg-emerald-400'
                    : 'left-0.5 bg-white/30'"></div>
              </button>

              <button @click="openEditModal(flag)"
                class="p-2 rounded-lg hover:bg-white/[0.06] text-white/40 hover:text-white transition-all"
                title="Edit">
                <PencilSquareIcon class="w-4 h-4" />
              </button>
              <button @click="confirmDeleteModal = flag"
                class="p-2 rounded-lg hover:bg-red-500/10 text-white/30 hover:text-red-400 transition-all"
                title="Delete">
                <TrashIcon class="w-4 h-4" />
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Create/Edit Modal -->
      <Teleport to="body">
        <div v-if="modalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" @click.self="modalOpen = false">
          <div class="w-full max-w-md rounded-2xl bg-[#1a1a2e] border border-white/[0.08] p-6 space-y-5 shadow-2xl">
            <div class="flex items-center justify-between">
              <h3 class="text-base font-semibold text-white">{{ editingFlag ? 'Edit Feature Flag' : 'Create Feature Flag' }}</h3>
              <button @click="modalOpen = false" class="p-1.5 rounded-lg hover:bg-white/[0.06] text-white/40 transition-all">
                <XMarkIcon class="w-5 h-5" />
              </button>
            </div>

            <div class="space-y-4">
              <div>
                <label class="text-xs text-white/40 mb-1 block">Key (unique identifier)</label>
                <input v-model="form.key" type="text" placeholder="e.g. new_booking_flow"
                  class="w-full px-3 py-2 rounded-lg bg-white/[0.04] border border-white/[0.08] text-white text-sm placeholder:text-white/20 focus:outline-none focus:border-amber-500/40 font-mono" />
              </div>
              <div>
                <label class="text-xs text-white/40 mb-1 block">Display Name</label>
                <input v-model="form.name" type="text" placeholder="e.g. New Booking Flow"
                  class="w-full px-3 py-2 rounded-lg bg-white/[0.04] border border-white/[0.08] text-white text-sm placeholder:text-white/20 focus:outline-none focus:border-amber-500/40" />
              </div>
              <div>
                <label class="text-xs text-white/40 mb-1 block">Description (optional)</label>
                <textarea v-model="form.description" rows="2" placeholder="What does this flag control?"
                  class="w-full px-3 py-2 rounded-lg bg-white/[0.04] border border-white/[0.08] text-white text-sm placeholder:text-white/20 focus:outline-none focus:border-amber-500/40 resize-none"></textarea>
              </div>

              <!-- Platform Selection -->
              <div>
                <label class="text-xs text-white/40 mb-2 block">Enabled Platforms</label>
                <div class="flex gap-2">
                  <button @click="togglePlatform('web')"
                    :class="[
                      'flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium transition-all border',
                      form.enabled_for.includes('web')
                        ? 'bg-cyan-500/10 text-cyan-400 border-cyan-500/30'
                        : 'bg-white/[0.03] text-white/30 border-white/[0.06]'
                    ]">
                    <GlobeAltIcon class="w-4 h-4" /> Web
                  </button>
                  <button @click="togglePlatform('app')"
                    :class="[
                      'flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium transition-all border',
                      form.enabled_for.includes('app')
                        ? 'bg-violet-500/10 text-violet-400 border-violet-500/30'
                        : 'bg-white/[0.03] text-white/30 border-white/[0.06]'
                    ]">
                    <DevicePhoneMobileIcon class="w-4 h-4" /> App
                  </button>
                </div>
              </div>

              <!-- Rollout Slider -->
              <div>
                <div class="flex items-center justify-between mb-2">
                  <label class="text-xs text-white/40">Rollout Percentage</label>
                  <span class="text-xs font-mono text-amber-400">{{ form.rollout_percentage }}%</span>
                </div>
                <input type="range" v-model.number="form.rollout_percentage" min="0" max="100" step="5"
                  class="w-full h-1.5 rounded-full appearance-none bg-white/[0.06] accent-amber-500 cursor-pointer" />
                <div class="flex justify-between text-[10px] text-white/20 mt-1">
                  <span>0%</span><span>50%</span><span>100%</span>
                </div>
              </div>

              <!-- Active toggle -->
              <label class="flex items-center gap-3 cursor-pointer">
                <input type="checkbox" v-model="form.is_active" class="accent-emerald-500 w-4 h-4 rounded" />
                <span class="text-sm text-white/60">Start as active</span>
              </label>
            </div>

            <div class="flex gap-2 justify-end pt-2">
              <button @click="modalOpen = false"
                class="px-4 py-2 rounded-lg bg-white/[0.04] hover:bg-white/[0.08] text-white/60 text-sm transition-all">
                Cancel
              </button>
              <button @click="saveFlag" :disabled="saving || !form.key || !form.name"
                class="flex items-center gap-2 px-5 py-2 rounded-lg bg-gradient-to-r from-amber-600 to-orange-600 hover:from-amber-500 hover:to-orange-500 text-white text-sm font-medium transition-all disabled:opacity-40 shadow-lg shadow-amber-500/15">
                <ArrowPathIcon v-if="saving" class="w-4 h-4 animate-spin" />
                {{ editingFlag ? 'Update Flag' : 'Create Flag' }}
              </button>
            </div>
          </div>
        </div>
      </Teleport>

      <!-- Delete Confirmation Modal -->
      <Teleport to="body">
        <div v-if="confirmDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" @click.self="confirmDeleteModal = null">
          <div class="w-full max-w-sm rounded-2xl bg-[#1a1a2e] border border-white/[0.08] p-6 space-y-4 shadow-2xl">
            <div class="flex items-center gap-3">
              <div class="p-2 rounded-xl bg-red-500/10 border border-red-500/20">
                <ExclamationTriangleIcon class="w-5 h-5 text-red-400" />
              </div>
              <div>
                <h3 class="text-base font-semibold text-white">Delete Feature Flag</h3>
                <p class="text-xs text-white/40">This action cannot be undone.</p>
              </div>
            </div>
            <p class="text-sm text-white/60">
              Are you sure you want to delete <strong class="text-white">{{ confirmDeleteModal.name }}</strong>
              (<code class="text-xs text-amber-400 font-mono">{{ confirmDeleteModal.key }}</code>)?
            </p>
            <div class="flex gap-2 justify-end">
              <button @click="confirmDeleteModal = null"
                class="px-4 py-2 rounded-lg bg-white/[0.04] hover:bg-white/[0.08] text-white/60 text-sm transition-all">
                Cancel
              </button>
              <button @click="handleDelete(confirmDeleteModal)" :disabled="deletingId === confirmDeleteModal.id"
                class="flex items-center gap-2 px-4 py-2 rounded-lg bg-red-600 hover:bg-red-500 text-white text-sm font-medium transition-all disabled:opacity-40">
                <TrashIcon class="w-4 h-4" />
                {{ deletingId === confirmDeleteModal.id ? 'Deleting...' : 'Delete' }}
              </button>
            </div>
          </div>
        </div>
      </Teleport>
    </section>
  </AdminLayout>
</template>

<style scoped>
@keyframes fade-in {
  from { opacity: 0; transform: translateY(8px); }
  to { opacity: 1; transform: translateY(0); }
}
.animate-fade-in {
  animation: fade-in 0.4s ease-out;
}
</style>
