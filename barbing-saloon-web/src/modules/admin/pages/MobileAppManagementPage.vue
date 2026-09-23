<script setup>
import { ref, onMounted, computed, watch } from 'vue'
import AdminLayout from '@/portals/admin/layouts/AdminLayout.vue'
import { adminApi } from '@/shared/api/old_adminApi'
import { useToast } from '@/core/composables/useToast'
import {
  DevicePhoneMobileIcon,
  ArrowPathIcon,
  ShieldCheckIcon,
  ExclamationTriangleIcon,
  BellAlertIcon,
  BugAntIcon,
  ServerStackIcon,
  ArrowUpCircleIcon,
  TrashIcon,
  CheckCircleIcon,
  MagnifyingGlassIcon,
  ChevronLeftIcon,
  ChevronRightIcon,
  PaperAirplaneIcon,
  XMarkIcon,
  WrenchScrewdriverIcon,
  SignalIcon,
  UserGroupIcon,
  ComputerDesktopIcon,
  ExclamationCircleIcon,
  PlusIcon
} from '@heroicons/vue/24/outline'

const toast = useToast()

// ── Tab Management ──
const tabs = [
  { key: 'overview', label: 'Overview & Versions', icon: ArrowUpCircleIcon },
  { key: 'pages', label: 'All App Pages (24)', icon: DevicePhoneMobileIcon },
  { key: 'sessions', label: 'Devices & Sessions', icon: ShieldCheckIcon },
  { key: 'push', label: 'Push Health', icon: BellAlertIcon },
  { key: 'crashes', label: 'Crash Logs', icon: BugAntIcon },
]
const activeTab = ref('overview')

// ── Loading & Data ──
const loading = ref(true)
const overview = ref({})

// ── Overview Tab ──
const versionForm = ref({
  version: '',
  platform: 'android',
  is_minimum: false,
  is_latest: false,
  force_update: false,
  release_notes: '',
  store_url: '',
})
const versionFormVisible = ref(false)
const savingVersion = ref(false)
const maintenanceEnabled = ref(false)
const maintenanceMessage = ref('')
const togglingMaintenance = ref(false)

const fetchOverview = async () => {
  loading.value = true
  try {
    const res = await adminApi.appOverview()
    overview.value = res.data?.data || {}
    maintenanceEnabled.value = overview.value.maintenance?.enabled ?? false
    maintenanceMessage.value = overview.value.maintenance?.message ?? ''
  } catch (err) {
    console.error('Failed to load app overview:', err)
  } finally {
    loading.value = false
  }
}

const saveVersion = async () => {
  savingVersion.value = true
  try {
    await adminApi.storeAppVersion(versionForm.value)
    toast.success('App version configured successfully.')
    versionFormVisible.value = false
    versionForm.value = { version: '', platform: 'android', is_minimum: false, is_latest: false, force_update: false, release_notes: '', store_url: '' }
    await fetchOverview()
  } catch (err) {
    const msg = err.response?.data?.message || 'Failed to save version.'
    toast.error(msg)
  } finally {
    savingVersion.value = false
  }
}

const handleToggleMaintenance = async () => {
  togglingMaintenance.value = true
  try {
    const newState = !maintenanceEnabled.value
    await adminApi.toggleMaintenance({ enabled: newState, message: maintenanceMessage.value })
    maintenanceEnabled.value = newState
    toast.success(`Maintenance mode ${newState ? 'enabled' : 'disabled'}.`)
  } catch (err) {
    toast.error('Failed to toggle maintenance mode.')
  } finally {
    togglingMaintenance.value = false
  }
}

// ── Sessions Tab ──
const sessions = ref([])
const sessionsLoading = ref(false)
const sessionSearch = ref('')
const sessionPage = ref(1)
const sessionMeta = ref({})
const revokingId = ref(null)

const fetchSessions = async () => {
  sessionsLoading.value = true
  try {
    const res = await adminApi.appSessions({ search: sessionSearch.value, page: sessionPage.value, per_page: 15 })
    const data = res.data?.data || {}
    sessions.value = data.data || []
    sessionMeta.value = { current_page: data.current_page, last_page: data.last_page, total: data.total }
  } catch (err) {
    console.error('Failed to load sessions:', err)
  } finally {
    sessionsLoading.value = false
  }
}

const handleRevokeSession = async (id) => {
  revokingId.value = id
  try {
    await adminApi.revokeSession(id)
    toast.success('Session token revoked.')
    sessions.value = sessions.value.filter(s => s.id !== id)
  } catch (err) {
    toast.error('Failed to revoke session.')
  } finally {
    revokingId.value = null
  }
}

const handleRevokeUserSessions = async (userId) => {
  try {
    await adminApi.revokeUserSessions(userId)
    toast.success('All user sessions revoked.')
    await fetchSessions()
  } catch (err) {
    toast.error('Failed to revoke user sessions.')
  }
}

// ── Push Health Tab ──
const pushData = ref({})
const pushLoading = ref(false)
const pushTestModal = ref(false)
const pushTestForm = ref({ user_id: '', token: '', title: 'Test Push', body: 'This is a test notification from the admin dashboard.' })
const sendingTest = ref(false)

const fetchPushHealth = async () => {
  pushLoading.value = true
  try {
    const res = await adminApi.pushHealth()
    pushData.value = res.data?.data || {}
  } catch (err) {
    console.error('Failed to load push health:', err)
  } finally {
    pushLoading.value = false
  }
}

const sendTestPush = async () => {
  sendingTest.value = true
  try {
    await adminApi.pushTest({
      title: pushTestForm.value.title,
      body: pushTestForm.value.body,
      user_id: pushTestForm.value.user_id ? Number(pushTestForm.value.user_id) : undefined,
      token: pushTestForm.value.token || undefined,
    })
    toast.success('Test push notification queued.')
    pushTestModal.value = false
  } catch (err) {
    const msg = err.response?.data?.message || 'Failed to send test push.'
    toast.error(msg)
  } finally {
    sendingTest.value = false
  }
}

// ── Crashes Tab ──
const crashes = ref([])
const crashesLoading = ref(false)
const crashSearch = ref('')
const crashStatus = ref('unresolved')
const crashPage = ref(1)
const crashMeta = ref({})
const resolvingId = ref(null)
const crashDetailModal = ref(null)

const fetchCrashes = async () => {
  crashesLoading.value = true
  try {
    const res = await adminApi.appCrashes({ search: crashSearch.value, status: crashStatus.value, page: crashPage.value, per_page: 15 })
    const data = res.data?.data || {}
    crashes.value = data.data || []
    crashMeta.value = { current_page: data.current_page, last_page: data.last_page, total: data.total }
  } catch (err) {
    console.error('Failed to load crashes:', err)
  } finally {
    crashesLoading.value = false
  }
}

const handleResolveCrash = async (id) => {
  resolvingId.value = id
  try {
    await adminApi.resolveCrash(id)
    toast.success('Crash marked as resolved.')
    crashes.value = crashes.value.map(c => c.id === id ? { ...c, resolved_at: new Date().toISOString() } : c)
    if (crashDetailModal.value?.id === id) crashDetailModal.value = null
  } catch (err) {
    toast.error('Failed to resolve crash.')
  } finally {
    resolvingId.value = null
  }
}

// ── Watchers ──
watch(activeTab, (tab) => {
  if (tab === 'sessions') fetchSessions()
  else if (tab === 'push') fetchPushHealth()
  else if (tab === 'crashes') fetchCrashes()
})
watch(sessionSearch, () => { sessionPage.value = 1; fetchSessions() })
watch(crashSearch, () => { crashPage.value = 1; fetchCrashes() })
watch(crashStatus, () => { crashPage.value = 1; fetchCrashes() })

// ── Helpers ──
const formatDate = (d) => d ? new Date(d).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : '—'
const timeAgo = (d) => {
  if (!d) return 'Never'
  const diff = Date.now() - new Date(d).getTime()
  const mins = Math.floor(diff / 60000)
  if (mins < 1) return 'Just now'
  if (mins < 60) return `${mins}m ago`
  const hrs = Math.floor(mins / 60)
  if (hrs < 24) return `${hrs}h ago`
  return `${Math.floor(hrs / 24)}d ago`
}

const pushCoverage = computed(() => pushData.value.coverage_percentage ?? 0)
const pushCoverageColor = computed(() => {
  if (pushCoverage.value >= 70) return 'text-emerald-400'
  if (pushCoverage.value >= 40) return 'text-amber-400'
  return 'text-red-400'
})

// ── App Pages Directory Catalog ──
const pageSearch = ref('')
const selectedRoleFilter = ref('all')
const allAppPages = [
  { id: 'onboarding', name: 'Onboarding', title: 'Welcome & Onboarding', file: 'app/onboarding.tsx', route: '/onboarding', role: 'Guest', category: 'Welcome', flag: 'guest_booking', description: 'Cinematic entry flow introducing customers to CandyCutz luxury grooming.' },
  { id: 'home', name: 'Home / Discover', title: 'Home / Discovery Feed', file: 'app/(tabs)/index.tsx', route: '/', role: 'Customer', category: 'Navigation', flag: 'mobile_booking_flow', description: 'Primary customer landing feed with personalized greeting and active booking banner.' },
  { id: 'services', name: 'Services Catalog', title: 'Services & Treatments', file: 'app/(tabs)/services.tsx', route: '/services', role: 'All', category: 'Catalog', flag: 'mobile_booking_flow', description: 'Interactive menu of salon services, beard sculpting, pricing, and durations.' },
  { id: 'book', name: 'Book Appointment', title: 'Appointment Booking Flow', file: 'app/book/[serviceId].tsx', route: '/book/:serviceId', role: 'Customer', category: 'Booking', flag: 'mobile_booking_flow', description: 'Step-by-step appointment scheduling with barber picker and time slot matrix.' },
  { id: 'confirmation', name: 'Booking Confirmation', title: 'Booking Success & Ticket', file: 'app/booking/confirmation.tsx', route: '/booking/confirmation', role: 'Customer', category: 'Booking', flag: 'mobile_booking_flow', description: 'Digital receipt with unique appointment code, barcode check-in, and calendar sync.' },
  { id: 'walkin', name: 'Walk-In Queue', title: 'Walk-In Booking & Queue', file: 'app/walkin.tsx', route: '/walkin', role: 'Walk-In', category: 'Booking', flag: 'mobile_walkin', description: 'On-demand walk-in ticket creator with live chair queue and wait time estimates.' },
  { id: 'bookings', name: 'My Bookings', title: 'Customer Bookings List', file: 'app/(tabs)/bookings.tsx', route: '/bookings', role: 'Customer', category: 'History', flag: 'mobile_booking_flow', description: 'Upcoming, past, and cancelled appointments with 1-tap reschedule actions.' },
  { id: 'appointments', name: 'Barber Appointments', title: 'Barber Appointments Desk', file: 'app/(tabs)/appointments.tsx', route: '/appointments', role: 'Barber', category: 'Barber Tools', flag: 'barber_self_checkout', description: 'Staff client queue with check-in, in-progress tracking, and completion actions.' },
  { id: 'schedule', name: 'Barber Schedule', title: 'Barber Weekly Roster & Availability', file: 'app/(tabs)/schedule.tsx', route: '/schedule', role: 'Barber', category: 'Barber Tools', flag: 'barber_self_checkout', description: 'Weekly shift schedule, break time toggles, and working hours manager.' },
  { id: 'profile', name: 'Customer Profile', title: 'Customer Account & Rewards', file: 'app/(tabs)/profile.tsx', route: '/profile', role: 'Customer', category: 'Account', flag: 'loyalty_rewards_tier', description: 'Loyalty rewards balance, grooming preferences, and account controls.' },
  { id: 'profile-edit', name: 'Edit Profile', title: 'Edit Personal Details', file: 'app/profile/edit.tsx', route: '/profile/edit', role: 'Customer', category: 'Account', flag: null, description: 'Customer editing screen for name, display nickname, phone, and avatar upload.' },
  { id: 'barber-profile', name: 'Barber Profile', title: 'Barber Public & Edit Profile', file: 'app/barber/profile-edit.tsx', route: '/barber/profile-edit', role: 'Barber', category: 'Barber Tools', flag: null, description: 'Barber profile manager for specialties, Instagram handle, and portfolio.' },
  { id: 'wishlist', name: 'Wishlist', title: 'Saved Cuts & Favorite Barbers', file: 'app/profile/wishlist.tsx', route: '/profile/wishlist', role: 'Customer', category: 'Favorites', flag: null, description: 'Saved haircut lookbooks and favorite master barbers for rapid re-booking.' },
  { id: 'reviews', name: 'Reviews & Ratings', title: 'Customer Feedback & Ratings', file: 'app/profile/reviews.tsx', route: '/profile/reviews', role: 'All', category: 'Social', flag: 'verified_reviews_only', description: 'Verified barber reviews, star ratings, and community client testimonials.' },
  { id: 'gallery', name: 'Hairstyle Gallery', title: 'Lookbook & Hairstyles', file: 'app/profile/gallery.tsx', route: '/profile/gallery', role: 'All', category: 'Media', flag: null, description: 'High-res haircut transformations, fades, beard sculpts, and styling photos.' },
  { id: 'blog', name: 'Grooming Blog', title: 'Articles & Grooming Advice', file: 'app/profile/blog.tsx', route: '/profile/blog', role: 'All', category: 'Media', flag: null, description: 'Editorial haircare guides, beard oil tips, and grooming masterclasses.' },
  { id: 'notifications', name: 'Push Notifications', title: 'In-App Alerts & Notifications', file: 'app/notifications.tsx', route: '/notifications', role: 'All', category: 'Activity', flag: null, description: 'Real-time appointment alerts, queue notifications, and shop announcements.' },
  { id: 'settings', name: 'App Settings & Theme', title: 'Preferences & Theme Modes', file: 'app/profile/settings.tsx', route: '/profile/settings', role: 'All', category: 'Settings', flag: null, description: 'Theme switcher (Light/Dark/System), push alerts, language, and biometrics.' },
  { id: 'analytics', name: 'Personal Analytics', title: 'Grooming Activity & Stats', file: 'app/profile/analytics.tsx', route: '/profile/analytics', role: 'Customer', category: 'Activity', flag: null, description: 'Haircut frequency metrics, loyalty savings, and personal grooming habits.' },
  { id: 'login', name: 'Sign In', title: 'Authentication & Sign In', file: 'app/auth/login.tsx', route: '/auth/login', role: 'Guest', category: 'Auth', flag: null, description: 'Secure email/password authentication, biometric login, and guest access.' },
  { id: 'register', name: 'Sign Up', title: 'New Account Registration', file: 'app/auth/register.tsx', route: '/auth/register', role: 'Guest', category: 'Auth', flag: null, description: 'Customer sign-up flow collecting details and establishing auth session.' },
  { id: 'forgot-password', name: 'Forgot Password', title: 'Password Reset & OTP', file: 'app/auth/forgot-password.tsx', route: '/auth/forgot-password', role: 'Guest', category: 'Auth', flag: null, description: 'Self-service account recovery dispatching secure password reset OTPs.' },
  { id: 'privacy', name: 'Privacy Policy', title: 'Privacy Policy & GDPR', file: 'app/policy/privacy.tsx', route: '/policy/privacy', role: 'Legal', category: 'Legal', flag: null, description: 'Formal disclosures, push token tracking, session policies, and privacy.' },
  { id: 'terms', name: 'Terms of Service', title: 'Terms of Service & Rules', file: 'app/policy/terms.tsx', route: '/policy/terms', role: 'Legal', category: 'Legal', flag: null, description: 'Salon booking cancellation terms, no-show rules, and shop conduct.' },
]

const filteredAppPages = computed(() => {
  return allAppPages.filter(p => {
    const matchesSearch = !pageSearch.value || 
      p.name.toLowerCase().includes(pageSearch.value.toLowerCase()) ||
      p.title.toLowerCase().includes(pageSearch.value.toLowerCase()) ||
      p.file.toLowerCase().includes(pageSearch.value.toLowerCase()) ||
      p.route.toLowerCase().includes(pageSearch.value.toLowerCase())
    const matchesRole = selectedRoleFilter.value === 'all' || p.role.toLowerCase().includes(selectedRoleFilter.value.toLowerCase())
    return matchesSearch && matchesRole
  })
})

// ── Init ──
onMounted(() => {
  fetchOverview()
})
</script>

<template>
  <AdminLayout>
    <section class="space-y-6 animate-fade-in">
      <!-- Page Header -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
          <div class="p-2.5 rounded-xl bg-gradient-to-br from-violet-500/20 to-purple-600/20 border border-violet-500/20">
            <DevicePhoneMobileIcon class="w-6 h-6 text-violet-400" />
          </div>
          <div>
            <h1 class="text-xl font-bold text-white">Mobile App Management</h1>
            <p class="text-sm text-white/50">Monitor, control and maintain the CandyCutz mobile app</p>
          </div>
        </div>
        <button @click="fetchOverview" :disabled="loading"
          class="flex items-center gap-2 px-4 py-2 rounded-xl bg-white/[0.04] hover:bg-white/[0.08] border border-white/[0.06] text-white/70 hover:text-white transition-all text-sm">
          <ArrowPathIcon class="w-4 h-4" :class="{ 'animate-spin': loading }" />
          Refresh
        </button>
      </div>

      <!-- Tabs -->
      <div class="flex gap-1 p-1 rounded-xl bg-white/[0.03] border border-white/[0.06]">
        <button v-for="tab in tabs" :key="tab.key" @click="activeTab = tab.key"
          :class="[
            'flex items-center gap-2 px-4 py-2.5 rounded-lg text-sm font-medium transition-all',
            activeTab === tab.key
              ? 'bg-gradient-to-r from-violet-500/20 to-purple-500/20 text-white border border-violet-500/30 shadow-lg shadow-violet-500/10'
              : 'text-white/50 hover:text-white/70 hover:bg-white/[0.04]'
          ]">
          <component :is="tab.icon" class="w-4 h-4" />
          <span class="hidden sm:inline">{{ tab.label }}</span>
        </button>
      </div>

      <!-- Loading -->
      <div v-if="loading && activeTab === 'overview'" class="grid gap-4 grid-cols-2 lg:grid-cols-4">
        <div v-for="i in 4" :key="i" class="h-32 rounded-2xl bg-white/[0.03] animate-pulse border border-white/[0.04]"></div>
      </div>

      <!-- ═══════ OVERVIEW & VERSIONS TAB ═══════ -->
      <template v-if="activeTab === 'overview' && !loading">
        <!-- KPI Cards -->
        <div class="grid gap-4 grid-cols-2 lg:grid-cols-4">
          <div class="p-5 rounded-2xl bg-white/[0.03] border border-white/[0.06] space-y-2">
            <p class="text-xs text-white/40 uppercase tracking-wider">Active Sessions</p>
            <p class="text-2xl font-bold text-white">{{ overview.metrics?.active_sessions?.toLocaleString() ?? '—' }}</p>
            <div class="flex items-center gap-1 text-xs text-emerald-400">
              <SignalIcon class="w-3.5 h-3.5" /> Connected devices
            </div>
          </div>
          <div class="p-5 rounded-2xl bg-white/[0.03] border border-white/[0.06] space-y-2">
            <p class="text-xs text-white/40 uppercase tracking-wider">Push Tokens</p>
            <p class="text-2xl font-bold text-white">{{ overview.metrics?.push_tokens?.toLocaleString() ?? '—' }}</p>
            <div class="flex items-center gap-1 text-xs" :class="pushCoverageColor">
              <BellAlertIcon class="w-3.5 h-3.5" /> {{ overview.metrics?.push_coverage_pct ?? 0 }}% coverage
            </div>
          </div>
          <div class="p-5 rounded-2xl bg-white/[0.03] border border-white/[0.06] space-y-2">
            <p class="text-xs text-white/40 uppercase tracking-wider">Unresolved Crashes</p>
            <p class="text-2xl font-bold" :class="(overview.metrics?.unresolved_crashes ?? 0) > 0 ? 'text-red-400' : 'text-white'">
              {{ overview.metrics?.unresolved_crashes ?? 0 }}
            </p>
            <div class="flex items-center gap-1 text-xs text-white/40">
              <BugAntIcon class="w-3.5 h-3.5" /> Needs attention
            </div>
          </div>
          <div class="p-5 rounded-2xl bg-white/[0.03] border border-white/[0.06] space-y-2">
            <p class="text-xs text-white/40 uppercase tracking-wider">Maintenance Mode</p>
            <p class="text-2xl font-bold" :class="maintenanceEnabled ? 'text-amber-400' : 'text-emerald-400'">
              {{ maintenanceEnabled ? 'ON' : 'OFF' }}
            </p>
            <div class="flex items-center gap-1 text-xs" :class="maintenanceEnabled ? 'text-amber-400/70' : 'text-white/40'">
              <WrenchScrewdriverIcon class="w-3.5 h-3.5" /> {{ maintenanceEnabled ? 'App is down' : 'App is live' }}
            </div>
          </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
          <!-- Version Controls -->
          <div class="p-6 rounded-2xl bg-white/[0.03] border border-white/[0.06] space-y-5">
            <div class="flex items-center justify-between">
              <h3 class="text-sm font-semibold text-white flex items-center gap-2">
                <ArrowUpCircleIcon class="w-5 h-5 text-violet-400" /> Version Control
              </h3>
              <button @click="versionFormVisible = !versionFormVisible"
                class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-violet-500/10 hover:bg-violet-500/20 text-violet-400 text-xs font-medium transition-all border border-violet-500/20">
                <PlusIcon class="w-3.5 h-3.5" /> Add Version
              </button>
            </div>

            <!-- Current versions table -->
            <div class="space-y-3">
              <div v-for="plat in ['android', 'ios']" :key="plat"
                class="flex items-center justify-between p-3 rounded-xl bg-white/[0.02] border border-white/[0.04]">
                <div class="flex items-center gap-3">
                  <span class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider"
                    :class="plat === 'android' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-blue-500/10 text-blue-400 border border-blue-500/20'">
                    {{ plat }}
                  </span>
                  <div>
                    <p class="text-sm text-white font-medium">Min: {{ overview.versions?.[plat]?.minimum ?? '—' }}</p>
                    <p class="text-xs text-white/40">Latest: {{ overview.versions?.[plat]?.latest ?? '—' }}</p>
                  </div>
                </div>
                <span v-if="overview.versions?.[plat]?.force_update"
                  class="px-2 py-0.5 rounded-md text-[10px] font-semibold bg-red-500/10 text-red-400 border border-red-500/20">
                  Force Update
                </span>
              </div>
            </div>

            <!-- Version Distribution -->
            <div v-if="overview.version_distribution?.length" class="space-y-2 pt-2">
              <p class="text-xs text-white/40 uppercase tracking-wider">Active Version Distribution</p>
              <div v-for="v in overview.version_distribution" :key="v.app_version" class="flex items-center gap-3">
                <span class="text-xs text-white/70 w-16">v{{ v.app_version }}</span>
                <div class="flex-1 h-2 rounded-full bg-white/[0.06] overflow-hidden">
                  <div class="h-full rounded-full bg-gradient-to-r from-violet-500 to-purple-500 transition-all duration-500"
                    :style="{ width: `${Math.min(100, (v.count / Math.max(...overview.version_distribution.map(d => d.count))) * 100)}%` }"></div>
                </div>
                <span class="text-xs text-white/50 w-10 text-right">{{ v.count }}</span>
              </div>
            </div>

            <!-- Add Version Form -->
            <div v-if="versionFormVisible" class="space-y-3 pt-3 border-t border-white/[0.06]">
              <div class="grid gap-3 grid-cols-2">
                <div>
                  <label class="text-xs text-white/40 mb-1 block">Version</label>
                  <input v-model="versionForm.version" type="text" placeholder="1.2.0"
                    class="w-full px-3 py-2 rounded-lg bg-white/[0.04] border border-white/[0.08] text-white text-sm placeholder:text-white/20 focus:outline-none focus:border-violet-500/40" />
                </div>
                <div>
                  <label class="text-xs text-white/40 mb-1 block">Platform</label>
                  <select v-model="versionForm.platform"
                    class="w-full px-3 py-2 rounded-lg bg-white/[0.04] border border-white/[0.08] text-white text-sm focus:outline-none focus:border-violet-500/40">
                    <option value="android">Android</option>
                    <option value="ios">iOS</option>
                  </select>
                </div>
              </div>
              <div>
                <label class="text-xs text-white/40 mb-1 block">Store URL</label>
                <input v-model="versionForm.store_url" type="url" placeholder="https://play.google.com/..."
                  class="w-full px-3 py-2 rounded-lg bg-white/[0.04] border border-white/[0.08] text-white text-sm placeholder:text-white/20 focus:outline-none focus:border-violet-500/40" />
              </div>
              <div>
                <label class="text-xs text-white/40 mb-1 block">Release Notes</label>
                <textarea v-model="versionForm.release_notes" rows="2" placeholder="What's new..."
                  class="w-full px-3 py-2 rounded-lg bg-white/[0.04] border border-white/[0.08] text-white text-sm placeholder:text-white/20 focus:outline-none focus:border-violet-500/40 resize-none"></textarea>
              </div>
              <div class="flex gap-4 flex-wrap">
                <label class="flex items-center gap-2 text-xs text-white/60 cursor-pointer">
                  <input type="checkbox" v-model="versionForm.is_minimum" class="accent-violet-500" /> Set as minimum
                </label>
                <label class="flex items-center gap-2 text-xs text-white/60 cursor-pointer">
                  <input type="checkbox" v-model="versionForm.is_latest" class="accent-violet-500" /> Set as latest
                </label>
                <label class="flex items-center gap-2 text-xs text-white/60 cursor-pointer">
                  <input type="checkbox" v-model="versionForm.force_update" class="accent-red-500" /> Force update
                </label>
              </div>
              <div class="flex gap-2">
                <button @click="saveVersion" :disabled="savingVersion || !versionForm.version"
                  class="flex items-center gap-2 px-4 py-2 rounded-lg bg-violet-600 hover:bg-violet-500 text-white text-sm font-medium transition-all disabled:opacity-40">
                  <ArrowPathIcon v-if="savingVersion" class="w-4 h-4 animate-spin" />
                  Save Version
                </button>
                <button @click="versionFormVisible = false"
                  class="px-4 py-2 rounded-lg bg-white/[0.04] hover:bg-white/[0.08] text-white/60 text-sm transition-all">
                  Cancel
                </button>
              </div>
            </div>
          </div>

          <!-- Maintenance Mode -->
          <div class="p-6 rounded-2xl bg-white/[0.03] border border-white/[0.06] space-y-5">
            <h3 class="text-sm font-semibold text-white flex items-center gap-2">
              <WrenchScrewdriverIcon class="w-5 h-5 text-amber-400" /> Maintenance Mode
            </h3>
            <p class="text-xs text-white/40">When enabled, all mobile app users will see a maintenance screen. API requests from the app will receive HTTP 503.</p>
            <div>
              <label class="text-xs text-white/40 mb-1 block">Maintenance Message</label>
              <textarea v-model="maintenanceMessage" rows="3" placeholder="We're improving your experience. Back soon!"
                class="w-full px-3 py-2 rounded-lg bg-white/[0.04] border border-white/[0.08] text-white text-sm placeholder:text-white/20 focus:outline-none focus:border-violet-500/40 resize-none"></textarea>
            </div>
            <button @click="handleToggleMaintenance" :disabled="togglingMaintenance"
              :class="[
                'flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold transition-all w-full justify-center',
                maintenanceEnabled
                  ? 'bg-emerald-600 hover:bg-emerald-500 text-white'
                  : 'bg-amber-600 hover:bg-amber-500 text-white'
              ]">
              <ArrowPathIcon v-if="togglingMaintenance" class="w-4 h-4 animate-spin" />
              {{ maintenanceEnabled ? 'Disable Maintenance Mode' : 'Enable Maintenance Mode' }}
            </button>
          </div>
        </div>
      </template>

      <!-- ═══════ APP PAGES DIRECTORY TAB ═══════ -->
      <template v-if="activeTab === 'pages'">
        <!-- Filter and Search Header -->
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
          <div class="relative flex-1 w-full">
            <MagnifyingGlassIcon class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-white/30" />
            <input
              v-model="pageSearch"
              type="text"
              placeholder="Search screen by title, Expo file, or route..."
              class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-white/[0.04] border border-white/[0.06] text-white text-sm placeholder:text-white/25 focus:outline-none focus:border-admin/40"
            />
          </div>
          <div class="flex items-center gap-2 w-full sm:w-auto overflow-x-auto pb-1 sm:pb-0">
            <button
              v-for="role in ['all', 'customer', 'barber', 'guest', 'legal']"
              :key="role"
              @click="selectedRoleFilter = role"
              :class="[
                'px-3 py-1.5 rounded-lg text-xs font-semibold capitalize whitespace-nowrap transition-all border',
                selectedRoleFilter === role
                  ? 'bg-admin text-obsidian border-admin font-bold'
                  : 'bg-white/[0.03] border-white/[0.06] text-white/50 hover:text-white hover:bg-white/[0.06]'
              ]"
            >
              {{ role }}
            </button>
          </div>
        </div>

        <!-- 24 Pages Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <RouterLink
            v-for="page in filteredAppPages"
            :key="page.id"
            :to="`/admin/mobile-app/pages/${page.id}`"
            class="group p-5 rounded-2xl bg-white/[0.03] hover:bg-white/[0.05] border border-white/[0.06] hover:border-admin/30 transition-all space-y-3 relative overflow-hidden"
          >
            <!-- Hover ambient glow -->
            <div class="absolute -right-8 -top-8 w-24 h-24 rounded-full bg-admin/5 blur-2xl group-hover:bg-admin/15 transition-all"></div>

            <div class="flex items-center justify-between">
              <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-white/[0.04] text-white/60 border border-white/[0.06]">
                {{ page.category }}
              </span>
              <span class="text-[10px] font-semibold text-admin-light">
                {{ page.role }}
              </span>
            </div>

            <div>
              <h4 class="text-base font-bold text-white group-hover:text-admin transition-colors flex items-center justify-between">
                <span>{{ page.title }}</span>
                <ChevronRightIcon class="w-4 h-4 text-white/20 group-hover:text-admin group-hover:translate-x-0.5 transition-all" />
              </h4>
              <p class="text-xs text-white/40 mt-1 line-clamp-2 leading-relaxed">{{ page.description }}</p>
            </div>

            <div class="pt-2 border-t border-white/[0.04] flex items-center justify-between text-[11px] font-mono text-white/40">
              <span class="truncate max-w-[180px]">{{ page.file }}</span>
              <span class="text-gold font-sans font-semibold">Inspect &rarr;</span>
            </div>
          </RouterLink>
        </div>
      </template>

      <!-- ═══════ SESSIONS TAB ═══════ -->
      <template v-if="activeTab === 'sessions'">
        <div class="flex items-center gap-3 flex-wrap">
          <div class="relative flex-1 min-w-[200px]">
            <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-white/30" />
            <input v-model="sessionSearch" type="text" placeholder="Search by user name, email, or token name..."
              class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-white/[0.04] border border-white/[0.06] text-white text-sm placeholder:text-white/25 focus:outline-none focus:border-violet-500/40" />
          </div>
        </div>

        <div v-if="sessionsLoading" class="space-y-3">
          <div v-for="i in 5" :key="i" class="h-16 rounded-xl bg-white/[0.03] animate-pulse border border-white/[0.04]"></div>
        </div>

        <div v-else-if="!sessions.length" class="text-center py-16">
          <ShieldCheckIcon class="w-12 h-12 text-white/15 mx-auto mb-3" />
          <p class="text-white/40 text-sm">No active sessions found.</p>
        </div>

        <div v-else class="space-y-2">
          <div v-for="session in sessions" :key="session.id"
            class="flex items-center justify-between p-4 rounded-xl bg-white/[0.03] border border-white/[0.06] hover:bg-white/[0.05] transition-all group">
            <div class="flex items-center gap-3 min-w-0">
              <div class="w-9 h-9 rounded-lg bg-violet-500/10 border border-violet-500/20 flex items-center justify-center flex-shrink-0">
                <ComputerDesktopIcon class="w-4.5 h-4.5 text-violet-400" />
              </div>
              <div class="min-w-0">
                <p class="text-sm font-medium text-white truncate">{{ session.tokenable?.name ?? 'Unknown User' }}</p>
                <p class="text-xs text-white/40 truncate">{{ session.name || 'auth-token' }} · {{ session.tokenable?.email ?? '' }}</p>
              </div>
            </div>
            <div class="flex items-center gap-4">
              <div class="text-right hidden sm:block">
                <p class="text-xs text-white/50">Last active</p>
                <p class="text-xs text-white/70">{{ timeAgo(session.last_used_at) }}</p>
              </div>
              <div class="flex gap-1.5">
                <button @click="handleRevokeSession(session.id)" :disabled="revokingId === session.id"
                  class="px-3 py-1.5 rounded-lg bg-red-500/10 hover:bg-red-500/20 text-red-400 text-xs font-medium transition-all border border-red-500/20 disabled:opacity-40">
                  {{ revokingId === session.id ? 'Revoking...' : 'Revoke' }}
                </button>
                <button v-if="session.tokenable_id" @click="handleRevokeUserSessions(session.tokenable_id)"
                  class="px-3 py-1.5 rounded-lg bg-white/[0.04] hover:bg-white/[0.08] text-white/50 text-xs font-medium transition-all border border-white/[0.06]"
                  title="Revoke all sessions for this user">
                  Revoke All
                </button>
              </div>
            </div>
          </div>

          <!-- Pagination -->
          <div v-if="sessionMeta.last_page > 1" class="flex items-center justify-center gap-3 pt-4">
            <button @click="sessionPage--; fetchSessions()" :disabled="sessionPage <= 1"
              class="p-2 rounded-lg bg-white/[0.04] hover:bg-white/[0.08] border border-white/[0.06] text-white/50 disabled:opacity-30 transition-all">
              <ChevronLeftIcon class="w-4 h-4" />
            </button>
            <span class="text-xs text-white/50">Page {{ sessionMeta.current_page }} of {{ sessionMeta.last_page }}</span>
            <button @click="sessionPage++; fetchSessions()" :disabled="sessionPage >= sessionMeta.last_page"
              class="p-2 rounded-lg bg-white/[0.04] hover:bg-white/[0.08] border border-white/[0.06] text-white/50 disabled:opacity-30 transition-all">
              <ChevronRightIcon class="w-4 h-4" />
            </button>
          </div>
        </div>
      </template>

      <!-- ═══════ PUSH HEALTH TAB ═══════ -->
      <template v-if="activeTab === 'push'">
        <div v-if="pushLoading" class="grid gap-4 grid-cols-2 lg:grid-cols-4">
          <div v-for="i in 4" :key="i" class="h-32 rounded-2xl bg-white/[0.03] animate-pulse border border-white/[0.04]"></div>
        </div>

        <template v-else>
          <!-- Push KPIs -->
          <div class="grid gap-4 grid-cols-2 lg:grid-cols-4">
            <div class="p-5 rounded-2xl bg-white/[0.03] border border-white/[0.06] space-y-2">
              <p class="text-xs text-white/40 uppercase tracking-wider">Push Coverage</p>
              <p class="text-2xl font-bold" :class="pushCoverageColor">{{ pushCoverage }}%</p>
              <p class="text-xs text-white/40">{{ pushData.registered_users_count ?? 0 }} of {{ pushData.total_users ?? 0 }} users</p>
            </div>
            <div class="p-5 rounded-2xl bg-white/[0.03] border border-white/[0.06] space-y-2">
              <p class="text-xs text-white/40 uppercase tracking-wider">Active Tokens</p>
              <p class="text-2xl font-bold text-white">{{ (pushData.active_tokens_count ?? 0).toLocaleString() }}</p>
              <p class="text-xs text-white/40">Registered devices</p>
            </div>
            <div class="p-5 rounded-2xl bg-white/[0.03] border border-white/[0.06] space-y-2">
              <p class="text-xs text-white/40 uppercase tracking-wider">Revoked Tokens</p>
              <p class="text-2xl font-bold text-red-400">{{ (pushData.revoked_tokens_count ?? 0).toLocaleString() }}</p>
              <p class="text-xs text-white/40">Invalid / unregistered</p>
            </div>
            <div class="p-5 rounded-2xl bg-white/[0.03] border border-white/[0.06] space-y-2">
              <p class="text-xs text-white/40 uppercase tracking-wider">Platform Split</p>
              <div class="flex gap-2 pt-1">
                <span v-for="p in (pushData.platform_breakdown || [])" :key="p.platform"
                  class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase"
                  :class="p.platform === 'ios' ? 'bg-blue-500/10 text-blue-400 border border-blue-500/20' : 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20'">
                  {{ p.platform }}: {{ p.count }}
                </span>
              </div>
            </div>
          </div>

          <!-- Delivery History -->
          <div class="p-6 rounded-2xl bg-white/[0.03] border border-white/[0.06] space-y-4">
            <div class="flex items-center justify-between">
              <h3 class="text-sm font-semibold text-white flex items-center gap-2">
                <SignalIcon class="w-5 h-5 text-emerald-400" /> 14-Day Delivery History
              </h3>
              <button @click="pushTestModal = true"
                class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-violet-500/10 hover:bg-violet-500/20 text-violet-400 text-xs font-medium transition-all border border-violet-500/20">
                <PaperAirplaneIcon class="w-3.5 h-3.5" /> Send Test Push
              </button>
            </div>

            <div v-if="pushData.history?.length" class="space-y-2">
              <div v-for="day in pushData.history" :key="day.date"
                class="flex items-center gap-4 p-3 rounded-xl bg-white/[0.02] border border-white/[0.04]">
                <span class="text-xs text-white/50 w-20">{{ new Date(day.date).toLocaleDateString('en-US', { month: 'short', day: 'numeric' }) }}</span>
                <div class="flex-1 flex gap-2 items-center">
                  <div class="flex-1 h-2 rounded-full bg-white/[0.06] overflow-hidden relative">
                    <div class="h-full rounded-full bg-emerald-500 absolute left-0"
                      :style="{ width: day.total_tokens > 0 ? `${(day.delivered_count / day.total_tokens) * 100}%` : '0%' }"></div>
                    <div v-if="day.failed_count" class="h-full rounded-full bg-red-500 absolute right-0"
                      :style="{ width: day.total_tokens > 0 ? `${(day.failed_count / day.total_tokens) * 100}%` : '0%' }"></div>
                  </div>
                </div>
                <span class="text-xs text-emerald-400 w-12 text-right">{{ day.delivered_count ?? 0 }}</span>
                <span class="text-xs text-red-400 w-10 text-right">{{ day.failed_count ?? 0 }}</span>
              </div>
            </div>
            <div v-else class="text-center py-8 text-white/30 text-sm">No delivery history yet.</div>
          </div>

          <!-- Common Failure Reasons -->
          <div v-if="Object.keys(pushData.common_failure_reasons || {}).length" class="p-6 rounded-2xl bg-white/[0.03] border border-white/[0.06] space-y-3">
            <h3 class="text-sm font-semibold text-white flex items-center gap-2">
              <ExclamationTriangleIcon class="w-5 h-5 text-amber-400" /> Common Failure Reasons (14 days)
            </h3>
            <div class="grid gap-2 grid-cols-1 sm:grid-cols-2">
              <div v-for="(count, reason) in pushData.common_failure_reasons" :key="reason"
                class="flex items-center justify-between p-3 rounded-xl bg-white/[0.02] border border-white/[0.04]">
                <span class="text-xs text-white/60 font-mono">{{ reason }}</span>
                <span class="text-xs font-semibold text-red-400">{{ count }}</span>
              </div>
            </div>
          </div>
        </template>

        <!-- Test Push Modal -->
        <Teleport to="body">
          <div v-if="pushTestModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" @click.self="pushTestModal = false">
            <div class="w-full max-w-md rounded-2xl bg-[#1a1a2e] border border-white/[0.08] p-6 space-y-4 shadow-2xl">
              <div class="flex items-center justify-between">
                <h3 class="text-base font-semibold text-white">Send Test Push</h3>
                <button @click="pushTestModal = false" class="p-1.5 rounded-lg hover:bg-white/[0.06] text-white/40 transition-all">
                  <XMarkIcon class="w-5 h-5" />
                </button>
              </div>
              <div class="space-y-3">
                <div>
                  <label class="text-xs text-white/40 mb-1 block">User ID (optional)</label>
                  <input v-model="pushTestForm.user_id" type="text" placeholder="e.g. 42"
                    class="w-full px-3 py-2 rounded-lg bg-white/[0.04] border border-white/[0.08] text-white text-sm placeholder:text-white/20 focus:outline-none focus:border-violet-500/40" />
                </div>
                <div>
                  <label class="text-xs text-white/40 mb-1 block">Expo Token (optional)</label>
                  <input v-model="pushTestForm.token" type="text" placeholder="ExponentPushToken[...]"
                    class="w-full px-3 py-2 rounded-lg bg-white/[0.04] border border-white/[0.08] text-white text-sm placeholder:text-white/20 focus:outline-none focus:border-violet-500/40" />
                </div>
                <div>
                  <label class="text-xs text-white/40 mb-1 block">Title</label>
                  <input v-model="pushTestForm.title" type="text"
                    class="w-full px-3 py-2 rounded-lg bg-white/[0.04] border border-white/[0.08] text-white text-sm focus:outline-none focus:border-violet-500/40" />
                </div>
                <div>
                  <label class="text-xs text-white/40 mb-1 block">Body</label>
                  <textarea v-model="pushTestForm.body" rows="2"
                    class="w-full px-3 py-2 rounded-lg bg-white/[0.04] border border-white/[0.08] text-white text-sm focus:outline-none focus:border-violet-500/40 resize-none"></textarea>
                </div>
              </div>
              <div class="flex gap-2 justify-end">
                <button @click="pushTestModal = false" class="px-4 py-2 rounded-lg bg-white/[0.04] hover:bg-white/[0.08] text-white/60 text-sm transition-all">Cancel</button>
                <button @click="sendTestPush" :disabled="sendingTest || !pushTestForm.title"
                  class="flex items-center gap-2 px-4 py-2 rounded-lg bg-violet-600 hover:bg-violet-500 text-white text-sm font-medium transition-all disabled:opacity-40">
                  <PaperAirplaneIcon v-if="!sendingTest" class="w-4 h-4" />
                  <ArrowPathIcon v-else class="w-4 h-4 animate-spin" />
                  Send
                </button>
              </div>
            </div>
          </div>
        </Teleport>
      </template>

      <!-- ═══════ CRASH LOGS TAB ═══════ -->
      <template v-if="activeTab === 'crashes'">
        <div class="flex items-center gap-3 flex-wrap">
          <div class="relative flex-1 min-w-[200px]">
            <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-white/30" />
            <input v-model="crashSearch" type="text" placeholder="Search error messages, platform, or version..."
              class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-white/[0.04] border border-white/[0.06] text-white text-sm placeholder:text-white/25 focus:outline-none focus:border-violet-500/40" />
          </div>
          <select v-model="crashStatus"
            class="px-3 py-2.5 rounded-xl bg-white/[0.04] border border-white/[0.06] text-white text-sm focus:outline-none focus:border-violet-500/40">
            <option value="unresolved">Unresolved</option>
            <option value="resolved">Resolved</option>
            <option value="">All</option>
          </select>
        </div>

        <div v-if="crashesLoading" class="space-y-3">
          <div v-for="i in 5" :key="i" class="h-20 rounded-xl bg-white/[0.03] animate-pulse border border-white/[0.04]"></div>
        </div>

        <div v-else-if="!crashes.length" class="text-center py-16">
          <CheckCircleIcon class="w-12 h-12 text-emerald-500/30 mx-auto mb-3" />
          <p class="text-white/40 text-sm">No crash reports found. Looking good!</p>
        </div>

        <div v-else class="space-y-2">
          <div v-for="crash in crashes" :key="crash.id"
            class="p-4 rounded-xl bg-white/[0.03] border border-white/[0.06] hover:bg-white/[0.05] transition-all cursor-pointer"
            @click="crashDetailModal = crash">
            <div class="flex items-start justify-between gap-4">
              <div class="min-w-0 flex-1">
                <div class="flex items-center gap-2 mb-1">
                  <ExclamationCircleIcon class="w-4 h-4 flex-shrink-0" :class="crash.resolved_at ? 'text-white/30' : 'text-red-400'" />
                  <p class="text-sm font-medium text-white truncate">{{ crash.error_message }}</p>
                </div>
                <div class="flex items-center gap-3 text-xs text-white/40">
                  <span>{{ crash.user?.name ?? 'Anonymous' }}</span>
                  <span>·</span>
                  <span class="px-1.5 py-0.5 rounded bg-white/[0.04] font-mono">v{{ crash.app_version }}</span>
                  <span>·</span>
                  <span class="uppercase">{{ crash.platform }}</span>
                  <span>·</span>
                  <span>{{ timeAgo(crash.created_at) }}</span>
                </div>
              </div>
              <div class="flex items-center gap-2 flex-shrink-0">
                <span v-if="crash.resolved_at"
                  class="px-2 py-0.5 rounded-md text-[10px] font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Resolved</span>
                <button v-else @click.stop="handleResolveCrash(crash.id)" :disabled="resolvingId === crash.id"
                  class="px-3 py-1.5 rounded-lg bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-400 text-xs font-medium transition-all border border-emerald-500/20 disabled:opacity-40">
                  {{ resolvingId === crash.id ? '...' : 'Resolve' }}
                </button>
              </div>
            </div>
          </div>

          <!-- Pagination -->
          <div v-if="crashMeta.last_page > 1" class="flex items-center justify-center gap-3 pt-4">
            <button @click="crashPage--; fetchCrashes()" :disabled="crashPage <= 1"
              class="p-2 rounded-lg bg-white/[0.04] hover:bg-white/[0.08] border border-white/[0.06] text-white/50 disabled:opacity-30 transition-all">
              <ChevronLeftIcon class="w-4 h-4" />
            </button>
            <span class="text-xs text-white/50">Page {{ crashMeta.current_page }} of {{ crashMeta.last_page }} ({{ crashMeta.total }} total)</span>
            <button @click="crashPage++; fetchCrashes()" :disabled="crashPage >= crashMeta.last_page"
              class="p-2 rounded-lg bg-white/[0.04] hover:bg-white/[0.08] border border-white/[0.06] text-white/50 disabled:opacity-30 transition-all">
              <ChevronRightIcon class="w-4 h-4" />
            </button>
          </div>
        </div>

        <!-- Crash Detail Modal -->
        <Teleport to="body">
          <div v-if="crashDetailModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" @click.self="crashDetailModal = null">
            <div class="w-full max-w-2xl max-h-[80vh] overflow-y-auto rounded-2xl bg-[#1a1a2e] border border-white/[0.08] p-6 space-y-4 shadow-2xl">
              <div class="flex items-center justify-between">
                <h3 class="text-base font-semibold text-white flex items-center gap-2">
                  <BugAntIcon class="w-5 h-5 text-red-400" /> Crash Detail
                </h3>
                <button @click="crashDetailModal = null" class="p-1.5 rounded-lg hover:bg-white/[0.06] text-white/40 transition-all">
                  <XMarkIcon class="w-5 h-5" />
                </button>
              </div>
              <div class="grid gap-3 grid-cols-2 text-xs">
                <div><span class="text-white/40">User:</span> <span class="text-white ml-1">{{ crashDetailModal.user?.name ?? 'Anonymous' }}</span></div>
                <div><span class="text-white/40">Platform:</span> <span class="text-white ml-1 uppercase">{{ crashDetailModal.platform }}</span></div>
                <div><span class="text-white/40">Version:</span> <span class="text-white ml-1 font-mono">{{ crashDetailModal.app_version }}</span></div>
                <div><span class="text-white/40">Time:</span> <span class="text-white ml-1">{{ formatDate(crashDetailModal.created_at) }}</span></div>
              </div>
              <div>
                <p class="text-xs text-white/40 mb-1">Error Message</p>
                <p class="text-sm text-red-400 bg-red-500/5 p-3 rounded-lg border border-red-500/10 font-mono break-all">{{ crashDetailModal.error_message }}</p>
              </div>
              <div>
                <p class="text-xs text-white/40 mb-1">Stack Trace</p>
                <pre class="text-xs text-white/60 bg-white/[0.02] p-3 rounded-lg border border-white/[0.06] overflow-x-auto max-h-48 whitespace-pre-wrap font-mono">{{ crashDetailModal.stack_trace || 'No stack trace available.' }}</pre>
              </div>
              <div v-if="crashDetailModal.component_stack">
                <p class="text-xs text-white/40 mb-1">Component Stack</p>
                <pre class="text-xs text-white/50 bg-white/[0.02] p-3 rounded-lg border border-white/[0.06] overflow-x-auto max-h-32 whitespace-pre-wrap font-mono">{{ crashDetailModal.component_stack }}</pre>
              </div>
              <div v-if="crashDetailModal.device_info" class="text-xs text-white/50">
                <p class="text-white/40 mb-1">Device Info</p>
                <pre class="bg-white/[0.02] p-3 rounded-lg border border-white/[0.06] overflow-x-auto font-mono">{{ JSON.stringify(crashDetailModal.device_info, null, 2) }}</pre>
              </div>
              <div class="flex justify-end gap-2">
                <button v-if="!crashDetailModal.resolved_at" @click="handleResolveCrash(crashDetailModal.id)"
                  :disabled="resolvingId === crashDetailModal.id"
                  class="flex items-center gap-2 px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white text-sm font-medium transition-all disabled:opacity-40">
                  <CheckCircleIcon class="w-4 h-4" /> Mark as Resolved
                </button>
                <span v-else class="px-3 py-2 text-xs text-emerald-400">✓ Resolved {{ formatDate(crashDetailModal.resolved_at) }}</span>
              </div>
            </div>
          </div>
        </Teleport>
      </template>
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
select option {
  background: #1a1a2e;
  color: #fff;
}
</style>
