<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { Cog6ToothIcon, ArrowLeftIcon, CheckCircleIcon } from '@heroicons/vue/24/outline'
import CustomerLayout from '@/portals/customer/layouts/CustomerLayout.vue'
import { useAuthStore } from '../../auth/store/auth.store'
import { customerApi } from '@/shared/api/old_customerApi';
import { useToast } from '../../../core/composables/useToast'

const router = useRouter()
const authStore = useAuthStore()
const toast = useToast()

const loading = ref(false)
const settings = reactive({
  notify_bookings: true,
  notify_system: true,
  notify_wishlist: true,
  notify_blog: true,
  notify_general: true
})

const loadSettings = () => {
  if (authStore.user?.notification_preferences) {
    let prefs = authStore.user.notification_preferences
    if (typeof prefs === 'string') {
      try { 
        prefs = JSON.parse(prefs); 
      } catch { 
        // ignore invalid json fallback
      }
    }
    settings.notify_bookings = prefs.notify_bookings ?? true
    settings.notify_system = prefs.notify_system ?? true
    settings.notify_wishlist = prefs.notify_wishlist ?? true
    settings.notify_blog = prefs.notify_blog ?? true
    settings.notify_general = prefs.notify_general ?? true
  }
}

const saveSettings = async () => {
  loading.value = true
  try {
    await customerApi.updateNotificationSettings(settings)
    toast.success('Notification preferences saved successfully')
    await authStore.fetchUser()
  } catch (err) {
    toast.error('Failed to save preferences')
    console.error(err)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  loadSettings()
})
</script>

<template>
  <CustomerLayout>
    <div class="space-y-8 animate-fade-in max-w-4xl mx-auto">
      <!-- Header -->
      <div class="relative overflow-hidden rounded-3xl border border-[var(--color-border)] bg-[var(--color-bg-secondary)] p-8 shadow-2xl flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="absolute -right-16 -top-16 h-64 w-64 rounded-full bg-[var(--color-accent)]/5 blur-3xl pointer-events-none"></div>
        
        <div class="relative z-10 flex items-center gap-6">
          <button @click="router.push('/customer/dashboard/notifications')" class="h-12 w-12 flex items-center justify-center rounded-xl bg-[var(--color-bg-tertiary)] border border-[var(--color-border)] text-[var(--color-text-primary)] hover:text-[var(--color-accent)] hover:border-[var(--color-accent)]/30 transition-all">
            <ArrowLeftIcon class="w-6 h-6" />
          </button>
          <div>
            <p class="text-xs uppercase tracking-[0.3em] text-[var(--color-accent)] font-bold">Preferences</p>
            <h1 class="mt-2 font-display text-4xl text-[var(--color-text-primary)] drop-shadow-lg flex items-center gap-3">
              Notification <span class="text-transparent bg-clip-text bg-gradient-to-r from-[var(--color-accent)] to-[var(--color-text-primary)]">Settings</span>
            </h1>
          </div>
        </div>
        
        <button 
          @click="saveSettings"
          :disabled="loading"
          class="relative z-10 flex items-center justify-center gap-2 rounded-xl bg-[var(--color-accent)] text-[var(--color-accent-foreground)] px-6 py-3 text-sm font-bold hover:brightness-110 transition-all active:scale-[0.98] shrink-0 disabled:opacity-70"
        >
          <CheckCircleIcon class="w-5 h-5" />
          {{ loading ? 'Saving...' : 'Save Changes' }}
        </button>
      </div>

      <!-- Settings Content -->
      <div class="rounded-2xl border border-[var(--color-border)] bg-[var(--color-bg-secondary)] shadow-xl p-8">
        <div class="flex items-center gap-3 mb-8 pb-6 border-b border-[var(--color-border)]">
          <div class="h-12 w-12 rounded-xl bg-[var(--color-accent)]/10 border border-[var(--color-accent)]/20 flex items-center justify-center">
            <Cog6ToothIcon class="w-6 h-6 text-[var(--color-accent)]" />
          </div>
          <div>
            <h2 class="font-display text-2xl text-[var(--color-text-primary)]">Alert Preferences</h2>
            <p class="text-sm text-[var(--color-text-secondary)] mt-1">Choose which notifications you'd like to receive in your activity stream.</p>
          </div>
        </div>
        
        <div class="space-y-4">
          <label class="flex items-center justify-between cursor-pointer p-5 rounded-xl border border-[var(--color-border)] bg-[var(--color-bg-tertiary)] hover:border-[var(--color-accent)]/40 transition-colors group">
            <div class="pr-6">
              <p class="font-bold text-[var(--color-text-primary)] text-lg group-hover:text-[var(--color-accent)] transition-colors">Booking Updates</p>
              <p class="text-sm text-[var(--color-text-secondary)] mt-1">Get notified immediately about appointment confirmations, cancellations, and status changes.</p>
            </div>
            <input type="checkbox" v-model="settings.notify_bookings" class="w-6 h-6 accent-[var(--color-accent)] cursor-pointer shrink-0" />
          </label>
          
          <label class="flex items-center justify-between cursor-pointer p-5 rounded-xl border border-[var(--color-border)] bg-[var(--color-bg-tertiary)] hover:border-[var(--color-accent)]/40 transition-colors group">
            <div class="pr-6">
              <p class="font-bold text-[var(--color-text-primary)] text-lg group-hover:text-[var(--color-accent)] transition-colors">System Updates</p>
              <p class="text-sm text-[var(--color-text-secondary)] mt-1">Alert me whenever my personal profile information or avatar is updated.</p>
            </div>
            <input type="checkbox" v-model="settings.notify_system" class="w-6 h-6 accent-[var(--color-accent)] cursor-pointer shrink-0" />
          </label>

          <label class="flex items-center justify-between cursor-pointer p-5 rounded-xl border border-[var(--color-border)] bg-[var(--color-bg-tertiary)] hover:border-[var(--color-accent)]/40 transition-colors group">
            <div class="pr-6">
              <p class="font-bold text-[var(--color-text-primary)] text-lg group-hover:text-[var(--color-accent)] transition-colors">Wishlist Updates</p>
              <p class="text-sm text-[var(--color-text-secondary)] mt-1">Receive alerts when new items are successfully saved to your personal wishlist.</p>
            </div>
            <input type="checkbox" v-model="settings.notify_wishlist" class="w-6 h-6 accent-[var(--color-accent)] cursor-pointer shrink-0" />
          </label>

          <label class="flex items-center justify-between cursor-pointer p-5 rounded-xl border border-[var(--color-border)] bg-[var(--color-bg-tertiary)] hover:border-[var(--color-accent)]/40 transition-colors group">
            <div class="pr-6">
              <p class="font-bold text-[var(--color-text-primary)] text-lg group-hover:text-[var(--color-accent)] transition-colors">New Blog Updates</p>
              <p class="text-sm text-[var(--color-text-secondary)] mt-1">Be the first to know when our barbers publish new articles, style tips, or grooming advice.</p>
            </div>
            <input type="checkbox" v-model="settings.notify_blog" class="w-6 h-6 accent-[var(--color-accent)] cursor-pointer shrink-0" />
          </label>
          
          <label class="flex items-center justify-between cursor-pointer p-5 rounded-xl border border-[var(--color-border)] bg-[var(--color-bg-tertiary)] hover:border-[var(--color-accent)]/40 transition-colors group">
            <div class="pr-6">
              <p class="font-bold text-[var(--color-text-primary)] text-lg group-hover:text-[var(--color-accent)] transition-colors">General System Settings</p>
              <p class="text-sm text-[var(--color-text-secondary)] mt-1">Get notifications when you make changes to your global account preferences and settings.</p>
            </div>
            <input type="checkbox" v-model="settings.notify_general" class="w-6 h-6 accent-[var(--color-accent)] cursor-pointer shrink-0" />
          </label>
        </div>
      </div>
    </div>
  </CustomerLayout>
</template>
