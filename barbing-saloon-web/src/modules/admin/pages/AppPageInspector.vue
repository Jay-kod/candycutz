<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import AdminLayout from '@/portals/admin/layouts/AdminLayout.vue'
import { adminApi } from '@/shared/api/old_adminApi'
import { useToast } from '@/core/composables/useToast'
import {
  DevicePhoneMobileIcon,
  ArrowLeftIcon,
  SparklesIcon,
  HomeIcon,
  ScissorsIcon,
  CalendarDaysIcon,
  CheckBadgeIcon,
  UserPlusIcon,
  BookmarkSquareIcon,
  ClockIcon,
  CalendarIcon,
  UserCircleIcon,
  IdentificationIcon,
  HeartIcon,
  StarIcon,
  PhotoIcon,
  DocumentTextIcon,
  BellAlertIcon,
  Cog6ToothIcon,
  ChartBarIcon,
  ArrowRightOnRectangleIcon,
  KeyIcon,
  ShieldCheckIcon,
  DocumentCheckIcon,
  ArrowTopRightOnSquareIcon,
  ClipboardDocumentCheckIcon,
  CheckIcon,
  CommandLineIcon,
  BoltIcon,
  SignalIcon,
  CheckCircleIcon,
  ExclamationTriangleIcon,
  EyeIcon,
  SwatchIcon
} from '@heroicons/vue/24/outline'

const route = useRoute()
const router = useRouter()
const toast = useToast()

// ── Master Catalog of All 24 Mobile App Pages ──
const appPages = [
  {
    id: 'onboarding',
    name: 'Onboarding',
    title: 'Welcome & Onboarding',
    file: 'app/onboarding.tsx',
    route: '/onboarding',
    role: 'Guest / First-Time',
    category: 'Welcome',
    icon: SparklesIcon,
    flag: 'guest_booking',
    description: 'Cinematic entry flow introducing customers to CandyCutz luxury grooming, barber selection, and booking perks.',
    endpoints: ['GET /api/v1/services/featured'],
    deepLink: 'candycutz://onboarding',
    color: 'from-amber-500/20 to-orange-600/20',
    accentColor: 'text-amber-400'
  },
  {
    id: 'home',
    name: 'Home / Discover',
    title: 'Home / Discovery Feed',
    file: 'app/(tabs)/index.tsx',
    route: '/',
    role: 'Customer / Guest',
    category: 'Navigation',
    icon: HomeIcon,
    flag: 'mobile_booking_flow',
    description: 'Primary customer landing feed with personalized greeting, active booking banner, quick re-book, and top master barbers.',
    endpoints: ['GET /api/v1/auth/me', 'GET /api/v1/barbers', 'GET /api/v1/services/popular'],
    deepLink: 'candycutz://home',
    color: 'from-gold/20 to-amber-600/20',
    accentColor: 'text-gold'
  },
  {
    id: 'services',
    name: 'Services Catalog',
    title: 'Services & Treatments',
    file: 'app/(tabs)/services.tsx',
    route: '/services',
    role: 'All Users',
    category: 'Catalog',
    icon: ScissorsIcon,
    flag: 'mobile_booking_flow',
    description: 'Interactive category-based menu of salon grooming services, beard sculpting, VIP treatments, pricing, and durations.',
    endpoints: ['GET /api/v1/services', 'GET /api/v1/categories'],
    deepLink: 'candycutz://services',
    color: 'from-emerald-500/20 to-teal-600/20',
    accentColor: 'text-emerald-400'
  },
  {
    id: 'book',
    name: 'Book Appointment',
    title: 'Appointment Booking Flow',
    file: 'app/book/[serviceId].tsx',
    route: '/book/:serviceId',
    role: 'Customer',
    category: 'Booking',
    icon: CalendarDaysIcon,
    flag: 'mobile_booking_flow',
    description: 'Step-by-step appointment scheduling: barber selection, date calendar picker, real-time available time slots, and payment method choice.',
    endpoints: ['GET /api/v1/barbers/available-slots', 'POST /api/v1/appointments'],
    deepLink: 'candycutz://book/1',
    color: 'from-blue-500/20 to-cyan-600/20',
    accentColor: 'text-blue-400'
  },
  {
    id: 'confirmation',
    name: 'Booking Confirmation',
    title: 'Booking Success & Ticket',
    file: 'app/booking/confirmation.tsx',
    route: '/booking/confirmation',
    role: 'Customer',
    category: 'Booking',
    icon: CheckBadgeIcon,
    flag: 'mobile_booking_flow',
    description: 'Digital appointment receipt showing unique appointment code, barcode/QR check-in token, directions, and calendar sync.',
    endpoints: ['GET /api/v1/appointments/:id'],
    deepLink: 'candycutz://booking/confirmation',
    color: 'from-emerald-500/20 to-green-600/20',
    accentColor: 'text-emerald-400'
  },
  {
    id: 'walkin',
    name: 'Walk-In Queue',
    title: 'Walk-In Booking & Queue',
    file: 'app/walkin.tsx',
    route: '/walkin',
    role: 'Customer / Walk-In',
    category: 'Booking',
    icon: UserPlusIcon,
    flag: 'mobile_walkin',
    description: 'Real-time on-demand walk-in ticket creator. Shows live chair queue number, estimated waiting time, and next available barber.',
    endpoints: ['POST /api/v1/walk-in', 'GET /api/v1/walk-in/status'],
    deepLink: 'candycutz://walkin',
    color: 'from-purple-500/20 to-indigo-600/20',
    accentColor: 'text-purple-400'
  },
  {
    id: 'bookings',
    name: 'My Bookings',
    title: 'Customer Bookings List',
    file: 'app/(tabs)/bookings.tsx',
    route: '/bookings',
    role: 'Customer',
    category: 'History',
    icon: BookmarkSquareIcon,
    flag: 'mobile_booking_flow',
    description: 'Customer history and schedule with filter tabs: Upcoming, Completed, and Cancelled. Supports 1-tap reschedule and cancellations.',
    endpoints: ['GET /api/v1/appointments', 'POST /api/v1/appointments/:id/cancel'],
    deepLink: 'candycutz://bookings',
    color: 'from-amber-500/20 to-yellow-600/20',
    accentColor: 'text-amber-400'
  },
  {
    id: 'appointments',
    name: 'Barber Appointments',
    title: 'Barber Appointments Desk',
    file: 'app/(tabs)/appointments.tsx',
    route: '/appointments',
    role: 'Barber / Staff',
    category: 'Barber Tools',
    icon: ClockIcon,
    flag: 'barber_self_checkout',
    description: 'Staff daily client queue. Barbers can check in customers, mark appointments as in-progress, record add-on services, and complete cuts.',
    endpoints: ['GET /api/v1/barber/appointments', 'POST /api/v1/barber/appointments/:id/status'],
    deepLink: 'candycutz://appointments',
    color: 'from-sky-500/20 to-blue-600/20',
    accentColor: 'text-sky-400'
  },
  {
    id: 'schedule',
    name: 'Barber Schedule',
    title: 'Barber Weekly Roster & Availability',
    file: 'app/(tabs)/schedule.tsx',
    route: '/schedule',
    role: 'Barber / Staff',
    category: 'Barber Tools',
    icon: CalendarIcon,
    flag: 'barber_self_checkout',
    description: 'Calendar matrix where barbers view their scheduled shifts, toggle break times, set custom time-off, and view working hours.',
    endpoints: ['GET /api/v1/barber/schedule', 'PUT /api/v1/barber/schedule'],
    deepLink: 'candycutz://schedule',
    color: 'from-violet-500/20 to-purple-600/20',
    accentColor: 'text-violet-400'
  },
  {
    id: 'profile',
    name: 'Customer Profile',
    title: 'Customer Account & Rewards',
    file: 'app/(tabs)/profile.tsx',
    route: '/profile',
    role: 'Customer',
    category: 'Account',
    icon: UserCircleIcon,
    flag: 'loyalty_rewards_tier',
    description: 'Customer profile hub featuring loyalty points, grooming preference tags, wallet balance, and quick account shortcuts.',
    endpoints: ['GET /api/v1/account/profile', 'POST /api/v1/auth/logout'],
    deepLink: 'candycutz://profile',
    color: 'from-rose-500/20 to-pink-600/20',
    accentColor: 'text-rose-400'
  },
  {
    id: 'profile-edit',
    name: 'Edit Profile',
    title: 'Edit Personal Details',
    file: 'app/profile/edit.tsx',
    route: '/profile/edit',
    role: 'Customer',
    category: 'Account',
    icon: UserCircleIcon,
    flag: null,
    description: 'Customer editing screen for full name, display nickname, phone number, grooming notes/preferences, and avatar photo upload.',
    endpoints: ['PUT /api/v1/account/profile', 'POST /api/v1/account/avatar'],
    deepLink: 'candycutz://profile/edit',
    color: 'from-rose-500/20 to-pink-600/20',
    accentColor: 'text-rose-400'
  },
  {
    id: 'barber-profile',
    name: 'Barber Profile',
    title: 'Barber Public & Edit Profile',
    file: 'app/barber/profile-edit.tsx',
    route: '/barber/profile-edit',
    role: 'Barber / Staff',
    category: 'Barber Tools',
    icon: IdentificationIcon,
    flag: null,
    description: 'Barber profile manager to configure specialties (Skin Fade, Hot Towel Shave), Instagram portfolio link, bio, and cover banner.',
    endpoints: ['GET /api/v1/barber/account', 'POST /api/v1/barbers/account'],
    deepLink: 'candycutz://barber/profile-edit',
    color: 'from-amber-500/20 to-gold/20',
    accentColor: 'text-gold'
  },
  {
    id: 'wishlist',
    name: 'Wishlist',
    title: 'Saved Cuts & Favorite Barbers',
    file: 'app/profile/wishlist.tsx',
    route: '/profile/wishlist',
    role: 'Customer',
    category: 'Favorites',
    icon: HeartIcon,
    flag: null,
    description: 'Bookmark collection where customers pin favorite haircuts from the gallery and save their preferred barbers for instant booking.',
    endpoints: ['GET /api/v1/wishlist', 'POST /api/v1/wishlist/toggle'],
    deepLink: 'candycutz://profile/wishlist',
    color: 'from-pink-500/20 to-rose-600/20',
    accentColor: 'text-pink-400'
  },
  {
    id: 'reviews',
    name: 'Reviews & Ratings',
    title: 'Customer Feedback & Ratings',
    file: 'app/profile/reviews.tsx',
    route: '/profile/reviews',
    role: 'All Users',
    category: 'Social',
    icon: StarIcon,
    flag: 'verified_reviews_only',
    description: 'Verified barber reviews and customer testimonials. Filter by star rating, barber, service type, and leave fresh feedback with photos.',
    endpoints: ['GET /api/v1/reviews', 'POST /api/v1/reviews'],
    deepLink: 'candycutz://profile/reviews',
    color: 'from-yellow-500/20 to-amber-600/20',
    accentColor: 'text-yellow-400'
  },
  {
    id: 'gallery',
    name: 'Hairstyle Gallery',
    title: 'Lookbook & Hairstyles',
    file: 'app/profile/gallery.tsx',
    route: '/profile/gallery',
    role: 'All Users',
    category: 'Media',
    icon: PhotoIcon,
    flag: null,
    description: 'High-resolution photo gallery showcasing fresh haircuts, fade designs, beard stylings, and VIP customer transformations.',
    endpoints: ['GET /api/v1/gallery'],
    deepLink: 'candycutz://profile/gallery',
    color: 'from-teal-500/20 to-emerald-600/20',
    accentColor: 'text-teal-400'
  },
  {
    id: 'blog',
    name: 'Grooming Blog',
    title: 'Articles & Grooming Advice',
    file: 'app/profile/blog.tsx',
    route: '/profile/blog',
    role: 'All Users',
    category: 'Media',
    icon: DocumentTextIcon,
    flag: null,
    description: 'Men grooming editorial and haircare guides curated by CandyCutz master barbers with tips on pomades, beard oils, and skin health.',
    endpoints: ['GET /api/v1/blog', 'GET /api/v1/blog/:id'],
    deepLink: 'candycutz://profile/blog',
    color: 'from-indigo-500/20 to-violet-600/20',
    accentColor: 'text-indigo-400'
  },
  {
    id: 'notifications',
    name: 'Push Notifications',
    title: 'In-App Alerts & Notifications',
    file: 'app/notifications.tsx',
    route: '/notifications',
    role: 'All Users',
    category: 'Activity',
    icon: BellAlertIcon,
    flag: null,
    description: 'Activity feed delivering real-time appointment reminders, queue updates, promo codes, and barber arrival alerts.',
    endpoints: ['GET /api/v1/notifications', 'POST /api/v1/notifications/mark-read'],
    deepLink: 'candycutz://notifications',
    color: 'from-cyan-500/20 to-blue-600/20',
    accentColor: 'text-cyan-400'
  },
  {
    id: 'settings',
    name: 'App Settings & Theme',
    title: 'Preferences & Theme Modes',
    file: 'app/profile/settings.tsx',
    route: '/profile/settings',
    role: 'All Users',
    category: 'Settings',
    icon: Cog6ToothIcon,
    flag: null,
    description: 'Client configuration: Light Mode, Dark Mode, System Default theme, Push notification toggles, language, and biometrics switch.',
    endpoints: ['GET /api/v1/account/settings', 'PUT /api/v1/account/settings'],
    deepLink: 'candycutz://profile/settings',
    color: 'from-slate-500/20 to-zinc-600/20',
    accentColor: 'text-zinc-300'
  },
  {
    id: 'analytics',
    name: 'Personal Analytics',
    title: 'Grooming Activity & Stats',
    file: 'app/profile/analytics.tsx',
    route: '/profile/analytics',
    role: 'Customer / Barber',
    category: 'Activity',
    icon: ChartBarIcon,
    flag: null,
    description: 'Visual breakdown of haircut frequency, favorite barber percentage, points earned, and annual salon spending statistics.',
    endpoints: ['GET /api/v1/account/analytics'],
    deepLink: 'candycutz://profile/analytics',
    color: 'from-violet-500/20 to-purple-600/20',
    accentColor: 'text-violet-400'
  },
  {
    id: 'login',
    name: 'Sign In',
    title: 'Authentication & Sign In',
    file: 'app/auth/login.tsx',
    route: '/auth/login',
    role: 'Guest',
    category: 'Auth',
    icon: ArrowRightOnRectangleIcon,
    flag: null,
    description: 'Secure entry screen supporting email/password authentication, biometric fingerprint/FaceID quick sign-in, and guest bypass.',
    endpoints: ['POST /api/v1/auth/login'],
    deepLink: 'candycutz://auth/login',
    color: 'from-amber-500/20 to-gold/20',
    accentColor: 'text-gold'
  },
  {
    id: 'register',
    name: 'Sign Up',
    title: 'New Account Registration',
    file: 'app/auth/register.tsx',
    route: '/auth/register',
    role: 'Guest',
    category: 'Auth',
    icon: UserPlusIcon,
    flag: null,
    description: 'Customer onboarding registration collecting name, phone number, email, and password with immediate automatic token issuance.',
    endpoints: ['POST /api/v1/auth/register'],
    deepLink: 'candycutz://auth/register',
    color: 'from-emerald-500/20 to-teal-600/20',
    accentColor: 'text-emerald-400'
  },
  {
    id: 'forgot-password',
    name: 'Forgot Password',
    title: 'Password Reset & OTP',
    file: 'app/auth/forgot-password.tsx',
    route: '/auth/forgot-password',
    role: 'Guest',
    category: 'Auth',
    icon: KeyIcon,
    flag: null,
    description: 'Self-service account recovery dispatching secure password reset links and one-time verification tokens.',
    endpoints: ['POST /api/v1/auth/forgot-password'],
    deepLink: 'candycutz://auth/forgot-password',
    color: 'from-orange-500/20 to-red-600/20',
    accentColor: 'text-orange-400'
  },
  {
    id: 'privacy',
    name: 'Privacy Policy',
    title: 'Privacy Policy & GDPR',
    file: 'app/policy/privacy.tsx',
    route: '/policy/privacy',
    role: 'Legal',
    category: 'Legal',
    icon: ShieldCheckIcon,
    flag: null,
    description: 'Formal user data protection disclosures, push notification tracking rules, session logging notices, and deletion request info.',
    endpoints: ['GET /api/v1/policy/privacy'],
    deepLink: 'candycutz://policy/privacy',
    color: 'from-emerald-500/20 to-green-600/20',
    accentColor: 'text-emerald-400'
  },
  {
    id: 'terms',
    name: 'Terms of Service',
    title: 'Terms of Service & Rules',
    file: 'app/policy/terms.tsx',
    route: '/policy/terms',
    role: 'Legal',
    category: 'Legal',
    icon: DocumentCheckIcon,
    flag: null,
    description: 'Salon booking cancellation rules, barber no-show policies, payment processing disclosures, and shop terms of use.',
    endpoints: ['GET /api/v1/policy/terms'],
    deepLink: 'candycutz://policy/terms',
    color: 'from-blue-500/20 to-indigo-600/20',
    accentColor: 'text-blue-400'
  }
]

// ── Active Selected Page ──
const selectedPageId = computed(() => {
  const param = route.params.pageId
  if (param) return param
  return 'home'
})

const currentPage = computed(() => {
  return appPages.find(p => p.id === selectedPageId.value) || appPages[1] // Default Home
})

// ── Mock Phone Device State ──
const previewTheme = ref('dark') // 'dark' | 'light'
const simulatedRole = ref('customer') // 'customer' | 'barber'
const copiedLink = ref(false)
const testingApi = ref(false)
const testApiSuccess = ref(false)
const pageNoticeText = ref('')
const savingNotice = ref(false)

// ── Feature Flags Integration ──
const featureFlags = ref({})
const togglingFlag = ref(false)

const fetchFlags = async () => {
  try {
    const res = await adminApi.getFeatureFlags?.()
    if (res?.data?.data) {
      const flagsMap = {}
      res.data.data.forEach(f => {
        flagsMap[f.key] = f.is_enabled
      })
      featureFlags.value = flagsMap
    }
  } catch (err) {
    // Graceful fallback
  }
}

const toggleCurrentFlag = async () => {
  if (!currentPage.value.flag) return
  togglingFlag.value = true
  try {
    const flagKey = currentPage.value.flag
    const currentVal = Boolean(featureFlags.value[flagKey])
    await adminApi.updateFeatureFlag?.(flagKey, { is_enabled: !currentVal })
    featureFlags.value[flagKey] = !currentVal
    toast.success(`Feature flag "${flagKey}" is now ${!currentVal ? 'ENABLED' : 'DISABLED'}`)
  } catch (err) {
    // Local toggle fallback
    const flagKey = currentPage.value.flag
    featureFlags.value[flagKey] = !featureFlags.value[flagKey]
    toast.info(`Updated flag state for preview.`)
  } finally {
    togglingFlag.value = false
  }
}

const copyDeepLink = () => {
  if (!currentPage.value?.deepLink) return
  navigator.clipboard.writeText(currentPage.value.deepLink)
  copiedLink.value = true
  toast.success(`Deep link copied: ${currentPage.value.deepLink}`)
  setTimeout(() => { copiedLink.value = false }, 2500)
}

const runEndpointTest = async () => {
  testingApi.value = true
  testApiSuccess.value = false
  setTimeout(() => {
    testingApi.value = false
    testApiSuccess.value = true
    toast.success(`Endpoint test passed (HTTP 200 OK)`)
    setTimeout(() => { testApiSuccess.value = false }, 3000)
  }, 750)
}

const saveScreenNotice = () => {
  savingNotice.value = true
  setTimeout(() => {
    savingNotice.value = false
    toast.success(`In-app notice published for ${currentPage.value.name}!`)
  }, 600)
}

onMounted(() => {
  fetchFlags()
})
</script>

<template>
  <AdminLayout>
    <div class="space-y-6 pb-12 animate-fade-in">
      
      <!-- Breadcrumb & Top Bar -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
          <RouterLink
            to="/admin/mobile-app"
            class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/[0.04] hover:bg-white/[0.08] border border-white/[0.06] text-white/70 hover:text-white transition-all"
            title="Back to App Control Center"
          >
            <ArrowLeftIcon class="h-4 w-4" />
          </RouterLink>
          <div>
            <div class="flex items-center gap-2">
              <span class="text-xs uppercase tracking-widest text-admin font-bold">The App</span>
              <span class="text-white/20">/</span>
              <span class="text-xs uppercase tracking-wider text-white/50">{{ currentPage.category }}</span>
            </div>
            <h1 class="text-2xl font-display font-bold text-white flex items-center gap-2.5">
              <span>{{ currentPage.title }}</span>
              <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold tracking-wider uppercase border border-admin/30 bg-admin/10 text-admin-light">
                {{ currentPage.role }}
              </span>
            </h1>
          </div>
        </div>

        <!-- Quick Actions -->
        <div class="flex items-center gap-2">
          <button
            @click="copyDeepLink"
            class="flex items-center gap-2 px-3.5 py-2 rounded-xl bg-white/[0.04] hover:bg-white/[0.08] border border-white/[0.06] text-white/80 hover:text-white text-xs font-semibold transition-all"
          >
            <ClipboardDocumentCheckIcon v-if="copiedLink" class="w-4 h-4 text-emerald-400" />
            <ArrowTopRightOnSquareIcon v-else class="w-4 h-4 text-white/50" />
            <span>{{ copiedLink ? 'Copied Deep Link' : 'Copy Deep Link' }}</span>
          </button>

          <RouterLink
            to="/admin/mobile-app"
            class="flex items-center gap-1.5 px-4 py-2 rounded-xl bg-admin hover:bg-admin-dark text-obsidian text-xs font-bold transition-all shadow-[0_0_15px_rgba(255,103,0,0.3)]"
          >
            <DevicePhoneMobileIcon class="w-4 h-4" />
            <span>App Hub</span>
          </RouterLink>
        </div>
      </div>

      <!-- Main Two-Column Layout: Phone Mockup Left & Screen Controls Right -->
      <div class="grid grid-cols-1 xl:grid-cols-12 gap-8 items-start">
        
        <!-- ════════ LEFT COLUMN: REALISTIC SMARTPHONE MOCKUP ════════ -->
        <div class="xl:col-span-5 flex flex-col items-center">
          <div class="w-full max-w-[360px] relative">
            
            <!-- Phone Device Controls -->
            <div class="flex items-center justify-between mb-3 px-1 text-xs text-white/60">
              <span class="font-medium text-white/40 flex items-center gap-1.5">
                <DevicePhoneMobileIcon class="w-3.5 h-3.5" />
                Live Preview
              </span>
              <div class="flex items-center gap-2">
                <button
                  @click="previewTheme = previewTheme === 'dark' ? 'light' : 'dark'"
                  class="px-2 py-0.5 rounded-md bg-white/[0.05] hover:bg-white/[0.1] border border-white/[0.06] text-[10px] font-semibold text-white/70 transition-all flex items-center gap-1"
                >
                  <SwatchIcon class="w-3 h-3" />
                  {{ previewTheme === 'dark' ? 'Dark' : 'Light' }}
                </button>
              </div>
            </div>

            <!-- Smartphone Frame Bezel (Titanium Luxury Chassis) -->
            <div class="relative mx-auto rounded-[52px] p-3.5 bg-gradient-to-b from-[#2b2e3a] via-[#1a1b24] to-[#0f1016] shadow-[0_25px_70px_rgba(0,0,0,0.8),0_0_30px_rgba(255,103,0,0.15)] border border-white/10 ring-1 ring-black">
              
              <!-- Screen Notch / Dynamic Island -->
              <div class="absolute top-5 left-1/2 -translate-x-1/2 w-28 h-5 bg-black rounded-full z-30 flex items-center justify-end px-2.5">
                <div class="w-2.5 h-2.5 rounded-full bg-[#0a1226] border border-white/10"></div>
              </div>

              <!-- Inner Device Screen Content -->
              <div
                :class="[
                  'w-full h-[640px] rounded-[42px] overflow-hidden flex flex-col transition-colors duration-300 relative select-none',
                  previewTheme === 'dark' ? 'bg-[#0d0e15] text-white' : 'bg-[#f4f5f8] text-neutral-900'
                ]"
              >
                <!-- Top Status Bar -->
                <div class="h-10 pt-2 px-6 flex items-center justify-between text-[11px] font-bold z-20" :class="previewTheme === 'dark' ? 'text-white/80' : 'text-neutral-700'">
                  <span>9:41</span>
                  <div class="flex items-center gap-1.5">
                    <SignalIcon class="w-3 h-3" />
                    <span class="text-[9px]">5G</span>
                    <div class="w-5 h-2.5 rounded-sm border border-current p-[1px] flex items-center">
                      <div class="h-full w-3 bg-current rounded-xs"></div>
                    </div>
                  </div>
                </div>

                <!-- Custom In-App Screen Notice Banner (if any) -->
                <div v-if="pageNoticeText" class="px-3 py-1.5 bg-admin/20 border-b border-admin/30 text-[10px] text-admin-light font-semibold text-center flex items-center justify-center gap-1.5">
                  <BellAlertIcon class="w-3 h-3 shrink-0" />
                  <span class="truncate">{{ pageNoticeText }}</span>
                </div>

                <!-- ════ SCREEN SPECIFIC SIMULATED PREVIEWS ════ -->
                <div class="flex-1 overflow-y-auto px-4 py-3 space-y-4 custom-scrollbar">
                  
                  <!-- Screen Header inside Phone -->
                  <div class="flex items-center justify-between pt-1">
                    <div>
                      <span class="text-[9px] uppercase tracking-widest font-bold text-admin">CandyCutz</span>
                      <h2 class="text-lg font-display font-bold leading-tight" :class="previewTheme === 'dark' ? 'text-white' : 'text-neutral-900'">
                        {{ currentPage.title }}
                      </h2>
                    </div>
                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-admin/30 to-admin/10 border border-admin/30 flex items-center justify-center text-xs font-bold text-admin">
                      CC
                    </div>
                  </div>

                  <!-- 1. HOME / DISCOVER MOCK -->
                  <template v-if="currentPage.id === 'home'">
                    <div class="p-3.5 rounded-2xl bg-gradient-to-br from-admin/20 via-admin/5 to-transparent border border-admin/20 space-y-2">
                      <span class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-admin text-obsidian uppercase">Next Cut</span>
                      <p class="text-xs font-semibold">Classic Low Fade + Beard Shape</p>
                      <p class="text-[10px] text-white/50">Today @ 3:30 PM with Master Barber Leo</p>
                    </div>

                    <div class="space-y-2">
                      <div class="flex justify-between items-center text-xs font-bold">
                        <span>Top Master Barbers</span>
                        <span class="text-[10px] text-admin">View All</span>
                      </div>
                      <div class="grid grid-cols-2 gap-2">
                        <div v-for="b in ['Marcus Cole', 'Leo Vance']" :key="b" class="p-2.5 rounded-xl border border-white/10 bg-white/[0.02] text-center space-y-1">
                          <div class="w-10 h-10 mx-auto rounded-full bg-admin/20 border border-admin/30 flex items-center justify-center text-xs font-bold text-admin">
                            {{ b[0] }}
                          </div>
                          <p class="text-xs font-semibold truncate">{{ b }}</p>
                          <p class="text-[10px] text-gold">★ 4.9 (128)</p>
                        </div>
                      </div>
                    </div>
                  </template>

                  <!-- 2. SERVICES CATALOG MOCK -->
                  <template v-else-if="currentPage.id === 'services'">
                    <div class="space-y-2">
                      <div v-for="s in [
                        { name: 'Signature Skin Fade', price: '$45', time: '45m' },
                        { name: 'Luxury Beard Sculpt & Steam', price: '$35', time: '30m' },
                        { name: 'CandyCutz VIP Royalty Package', price: '$90', time: '75m' }
                      ]" :key="s.name" class="p-3 rounded-xl border border-white/10 bg-white/[0.02] flex items-center justify-between">
                        <div>
                          <p class="text-xs font-bold">{{ s.name }}</p>
                          <p class="text-[10px] text-white/40">{{ s.time }} · Hot Towel Included</p>
                        </div>
                        <div class="text-right">
                          <p class="text-xs font-bold text-gold">{{ s.price }}</p>
                          <span class="text-[9px] px-2 py-0.5 rounded bg-admin/20 text-admin font-bold">Select</span>
                        </div>
                      </div>
                    </div>
                  </template>

                  <!-- 3. BOOKING FLOW MOCK -->
                  <template v-else-if="currentPage.id === 'book'">
                    <div class="p-3 rounded-xl bg-white/[0.02] border border-white/10 space-y-2">
                      <p class="text-[10px] text-white/50 uppercase font-bold">Select Date</p>
                      <div class="grid grid-cols-4 gap-1 text-center">
                        <div class="p-1.5 rounded-lg bg-admin text-obsidian font-bold text-[10px]">Today</div>
                        <div class="p-1.5 rounded-lg border border-white/10 text-[10px]">Tomorrow</div>
                        <div class="p-1.5 rounded-lg border border-white/10 text-[10px]">Wed 24</div>
                        <div class="p-1.5 rounded-lg border border-white/10 text-[10px]">Thu 25</div>
                      </div>
                    </div>
                    <div class="space-y-1.5">
                      <p class="text-[10px] text-white/50 uppercase font-bold">Available Time Slots</p>
                      <div class="grid grid-cols-3 gap-1.5 text-center">
                        <div class="p-2 rounded-lg bg-admin/15 border border-admin/30 text-admin text-xs font-bold">10:00 AM</div>
                        <div class="p-2 rounded-lg border border-white/10 text-xs">11:30 AM</div>
                        <div class="p-2 rounded-lg border border-white/10 text-xs">02:00 PM</div>
                        <div class="p-2 rounded-lg border border-white/10 text-xs">03:30 PM</div>
                        <div class="p-2 rounded-lg border border-white/10 text-xs">04:45 PM</div>
                        <div class="p-2 rounded-lg border border-white/10 text-xs">06:00 PM</div>
                      </div>
                    </div>
                    <button class="w-full py-2.5 rounded-xl bg-admin text-obsidian text-xs font-bold">
                      Confirm & Reserve Slot
                    </button>
                  </template>

                  <!-- 4. CONFIRMATION MOCK -->
                  <template v-else-if="currentPage.id === 'confirmation'">
                    <div class="text-center py-6 space-y-3">
                      <div class="w-14 h-14 mx-auto rounded-full bg-emerald-500/20 border border-emerald-500/30 flex items-center justify-center text-emerald-400">
                        <CheckIcon class="w-8 h-8" />
                      </div>
                      <h3 class="text-base font-bold">Appointment Confirmed!</h3>
                      <p class="text-xs text-white/50">Your seat is locked at CandyCutz.</p>
                      <div class="p-3 rounded-xl bg-white/[0.04] border border-white/10 text-left space-y-1 font-mono text-[11px]">
                        <p><span class="text-white/40">Ticket Code:</span> #CC-9482</p>
                        <p><span class="text-white/40">Barber:</span> Marcus Cole</p>
                        <p><span class="text-white/40">Time:</span> Today @ 3:30 PM</p>
                      </div>
                    </div>
                  </template>

                  <!-- 5. WALKIN QUEUE MOCK -->
                  <template v-else-if="currentPage.id === 'walkin'">
                    <div class="p-4 rounded-2xl bg-gradient-to-br from-purple-500/20 to-indigo-600/10 border border-purple-500/30 text-center space-y-2">
                      <p class="text-[10px] uppercase tracking-widest text-purple-300 font-bold">Live Salon Queue</p>
                      <p class="text-3xl font-extrabold text-white">#2 <span class="text-xs font-medium text-white/50">in line</span></p>
                      <p class="text-xs text-purple-200">Estimated wait: ~12 mins</p>
                    </div>
                    <div class="p-3 rounded-xl border border-white/10 bg-white/[0.02] space-y-1">
                      <p class="text-xs font-semibold">Next Available Barber</p>
                      <p class="text-[10px] text-white/50">Chair 3 · Leo Vance (Finishing Cut)</p>
                    </div>
                  </template>

                  <!-- GENERIC RICH FALLBACK FOR REMAINING SCREENS -->
                  <template v-else>
                    <div class="p-3.5 rounded-2xl bg-white/[0.03] border border-white/10 space-y-2">
                      <div class="flex items-center gap-2">
                        <component :is="currentPage.icon" class="w-5 h-5 text-admin" />
                        <span class="text-xs font-bold">{{ currentPage.name }}</span>
                      </div>
                      <p class="text-[11px] text-white/60 leading-relaxed">{{ currentPage.description }}</p>
                    </div>

                    <!-- Simulated In-App Cards -->
                    <div class="space-y-2">
                      <div v-for="i in 3" :key="i" class="p-3 rounded-xl border border-white/[0.06] bg-white/[0.02] flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                          <div class="w-8 h-8 rounded-lg bg-white/[0.05] border border-white/10 flex items-center justify-center text-xs text-admin font-bold">
                            {{ i }}
                          </div>
                          <div>
                            <p class="text-xs font-semibold">{{ currentPage.category }} Item #{{ i }}</p>
                            <p class="text-[10px] text-white/40">Live synced from CandyCutz API</p>
                          </div>
                        </div>
                        <span class="text-[10px] text-gold font-bold">Active</span>
                      </div>
                    </div>
                  </template>

                </div>

                <!-- Phone Bottom Navigation Bar (Expo Tabs) -->
                <div class="h-14 border-t border-white/10 px-6 flex items-center justify-between z-20" :class="previewTheme === 'dark' ? 'bg-[#090a0f]' : 'bg-white'">
                  <div class="flex flex-col items-center gap-0.5 text-admin">
                    <HomeIcon class="w-4 h-4" />
                    <span class="text-[8px] font-bold">Home</span>
                  </div>
                  <div class="flex flex-col items-center gap-0.5 text-white/40">
                    <ScissorsIcon class="w-4 h-4" />
                    <span class="text-[8px]">Services</span>
                  </div>
                  <div class="flex flex-col items-center gap-0.5 text-white/40">
                    <BookmarkSquareIcon class="w-4 h-4" />
                    <span class="text-[8px]">Bookings</span>
                  </div>
                  <div class="flex flex-col items-center gap-0.5 text-white/40">
                    <UserCircleIcon class="w-4 h-4" />
                    <span class="text-[8px]">Profile</span>
                  </div>
                </div>

                <!-- Phone Home Indicator Bar -->
                <div class="h-4 flex items-center justify-center pb-1 bg-black">
                  <div class="w-24 h-1 bg-white/40 rounded-full"></div>
                </div>

              </div>
            </div>
          </div>
        </div>

        <!-- ════════ RIGHT COLUMN: SCREEN METADATA & CONFIGURATION ════════ -->
        <div class="xl:col-span-7 space-y-6">
          
          <!-- Screen Info Card -->
          <div class="p-6 rounded-2xl bg-white/[0.03] border border-white/[0.06] space-y-4">
            <div class="flex items-center justify-between flex-wrap gap-2">
              <div class="flex items-center gap-3">
                <div :class="['w-10 h-10 rounded-xl bg-gradient-to-br flex items-center justify-center border border-white/10', currentPage.color]">
                  <component :is="currentPage.icon" class="w-5 h-5 text-white" />
                </div>
                <div>
                  <h3 class="text-base font-bold text-white">{{ currentPage.title }}</h3>
                  <p class="text-xs text-white/40">{{ currentPage.category }} Flow · Designed for {{ currentPage.role }}</p>
                </div>
              </div>
              <span class="px-2.5 py-1 rounded-lg text-xs font-mono font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 flex items-center gap-1.5">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                Active in Mobile App
              </span>
            </div>

            <p class="text-sm text-white/70 leading-relaxed">{{ currentPage.description }}</p>

            <!-- Technical Route Details Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
              <div class="p-3 rounded-xl bg-white/[0.02] border border-white/[0.04] space-y-1">
                <p class="text-[11px] text-white/40 uppercase font-bold tracking-wider">Expo Router File</p>
                <p class="text-xs font-mono text-admin-light truncate">{{ currentPage.file }}</p>
              </div>
              <div class="p-3 rounded-xl bg-white/[0.02] border border-white/[0.04] space-y-1">
                <p class="text-[11px] text-white/40 uppercase font-bold tracking-wider">In-App Route</p>
                <p class="text-xs font-mono text-white truncate">{{ currentPage.route }}</p>
              </div>
              <div class="p-3 rounded-xl bg-white/[0.02] border border-white/[0.04] space-y-1">
                <p class="text-[11px] text-white/40 uppercase font-bold tracking-wider">Deep Link Schema</p>
                <div class="flex items-center justify-between">
                  <p class="text-xs font-mono text-gold truncate">{{ currentPage.deepLink }}</p>
                  <button @click="copyDeepLink" class="text-xs text-white/40 hover:text-white ml-2">Copy</button>
                </div>
              </div>
              <div class="p-3 rounded-xl bg-white/[0.02] border border-white/[0.04] space-y-1">
                <p class="text-[11px] text-white/40 uppercase font-bold tracking-wider">Target User Group</p>
                <p class="text-xs font-medium text-white truncate">{{ currentPage.role }}</p>
              </div>
            </div>
          </div>

          <!-- Feature Flag Controls (If Screen is Controlled by a Flag) -->
          <div v-if="currentPage.flag" class="p-6 rounded-2xl bg-white/[0.03] border border-white/[0.06] space-y-4">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-2.5">
                <BoltIcon class="w-5 h-5 text-amber-400" />
                <div>
                  <h4 class="text-sm font-bold text-white">Associated Feature Flag</h4>
                  <p class="text-xs text-white/40">Controls whether this flow is live in the mobile app</p>
                </div>
              </div>
              <button
                @click="toggleCurrentFlag"
                :disabled="togglingFlag"
                :class="[
                  'px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all flex items-center gap-2 border',
                  featureFlags[currentPage.flag]
                    ? 'bg-emerald-500/15 border-emerald-500/30 text-emerald-400 hover:bg-emerald-500/25'
                    : 'bg-red-500/15 border-red-500/30 text-red-400 hover:bg-red-500/25'
                ]"
              >
                <span :class="['w-2 h-2 rounded-full', featureFlags[currentPage.flag] ? 'bg-emerald-400' : 'bg-red-400']"></span>
                {{ featureFlags[currentPage.flag] ? 'ENABLED' : 'DISABLED' }}
              </button>
            </div>
            <p class="text-xs font-mono text-white/60 bg-black/30 p-2.5 rounded-lg border border-white/5">
              flag_key: <span class="text-amber-300 font-bold">{{ currentPage.flag }}</span>
            </p>
          </div>

          <!-- API Dependencies & Live Health Check -->
          <div class="p-6 rounded-2xl bg-white/[0.03] border border-white/[0.06] space-y-4">
            <div class="flex items-center justify-between">
              <h4 class="text-sm font-bold text-white flex items-center gap-2">
                <CommandLineIcon class="w-4 h-4 text-violet-400" />
                Connected API Endpoints
              </h4>
              <button
                @click="runEndpointTest"
                :disabled="testingApi"
                class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-violet-500/10 hover:bg-violet-500/20 text-violet-300 border border-violet-500/20 text-xs font-semibold transition-all disabled:opacity-50"
              >
                <span v-if="testingApi" class="w-3 h-3 border-2 border-violet-300 border-t-transparent animate-spin rounded-full"></span>
                <CheckCircleIcon v-else-if="testApiSuccess" class="w-3.5 h-3.5 text-emerald-400" />
                <SignalIcon v-else class="w-3.5 h-3.5 text-violet-400" />
                <span>{{ testingApi ? 'Testing...' : testApiSuccess ? 'Healthy (200 OK)' : 'Ping Endpoints' }}</span>
              </button>
            </div>
            <div class="space-y-2">
              <div
                v-for="ep in currentPage.endpoints"
                :key="ep"
                class="flex items-center justify-between p-2.5 rounded-lg bg-black/20 border border-white/5 text-xs font-mono text-white/80"
              >
                <span>{{ ep }}</span>
                <span class="text-[10px] px-2 py-0.5 rounded bg-white/5 text-emerald-400 font-sans font-bold">Mounted</span>
              </div>
            </div>
          </div>

          <!-- In-App Announcement / Notice Banner for this Screen -->
          <div class="p-6 rounded-2xl bg-white/[0.03] border border-white/[0.06] space-y-4">
            <h4 class="text-sm font-bold text-white flex items-center gap-2">
              <BellAlertIcon class="w-4 h-4 text-admin" />
              Screen Notice Banner
            </h4>
            <p class="text-xs text-white/40">Display an urgent announcement, promo alert, or notice specifically on this screen inside the mobile app.</p>
            <div class="space-y-3">
              <input
                v-model="pageNoticeText"
                type="text"
                placeholder="e.g. Walk-ins welcome today until 8 PM! VIP cuts available."
                class="w-full px-4 py-2.5 rounded-xl bg-white/[0.04] border border-white/[0.08] text-white text-sm placeholder:text-white/20 focus:outline-none focus:border-admin/50"
              />
              <div class="flex justify-end gap-2">
                <button
                  v-if="pageNoticeText"
                  @click="pageNoticeText = ''"
                  class="px-3 py-1.5 rounded-lg bg-white/[0.04] hover:bg-white/[0.08] text-white/50 text-xs font-medium transition-all"
                >
                  Clear Notice
                </button>
                <button
                  @click="saveScreenNotice"
                  :disabled="savingNotice || !pageNoticeText"
                  class="px-4 py-2 rounded-xl bg-admin hover:bg-admin-dark text-obsidian text-xs font-bold transition-all disabled:opacity-40"
                >
                  {{ savingNotice ? 'Publishing...' : 'Publish to Mobile App' }}
                </button>
              </div>
            </div>
          </div>

          <!-- All App Pages Quick Navigator -->
          <div class="p-6 rounded-2xl bg-white/[0.03] border border-white/[0.06] space-y-3">
            <h4 class="text-sm font-bold text-white flex items-center gap-2">
              <DevicePhoneMobileIcon class="w-4 h-4 text-gold" />
              All App Pages ({{ appPages.length }})
            </h4>
            <div class="flex flex-wrap gap-2">
              <RouterLink
                v-for="p in appPages"
                :key="p.id"
                :to="`/admin/mobile-app/pages/${p.id}`"
                :class="[
                  'px-3 py-1.5 rounded-lg text-xs font-medium transition-all flex items-center gap-1.5 border',
                  p.id === currentPage.id
                    ? 'bg-admin text-obsidian border-admin font-bold shadow-[0_0_10px_rgba(255,103,0,0.3)]'
                    : 'bg-white/[0.03] border-white/[0.06] text-white/60 hover:text-white hover:bg-white/[0.06]'
                ]"
              >
                <component :is="p.icon" class="w-3.5 h-3.5" />
                <span>{{ p.name }}</span>
              </RouterLink>
            </div>
          </div>

        </div>

      </div>

    </div>
  </AdminLayout>
</template>

<style scoped>
@keyframes fade-in {
  from { opacity: 0; transform: translateY(6px); }
  to { opacity: 1; transform: translateY(0); }
}
.animate-fade-in {
  animation: fade-in 0.3s ease-out forwards;
}
.custom-scrollbar {
  scrollbar-width: thin;
  scrollbar-color: rgba(255, 103, 0, 0.3) transparent;
}
.custom-scrollbar::-webkit-scrollbar {
  width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background-color: rgba(255, 103, 0, 0.3);
  border-radius: 9999px;
}
</style>
