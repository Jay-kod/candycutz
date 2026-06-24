<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { Cog6ToothIcon, ArrowLeftIcon, CheckCircleIcon } from '@heroicons/vue/24/outline'
import CustomerLayout from '../layouts/CustomerLayout.vue'
import { useAuthStore } from '../../auth/store/auth.store'
import { customerApi } from '../api/customer.api'
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
      try { prefs = JSON.parse(prefs) } catch(e) {}
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
      <div class="relative overflow-hidden rounded-3xl border border-gold/20 bg-gradient-to-br from-obsidian via-charcoal to-steel p-8 shadow-2xl flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="absolute -right-16 -top-16 h-64 w-64 rounded-full bg-gold/5 blur-3xl pointer-events-none"></div>
        
        <div class="relative z-10 flex items-center gap-6">
          <button @click="router.push('/customer/dashboard/notifications')" class="h-12 w-12 flex items-center justify-center rounded-xl bg-theme-bg/50 border border-theme-border text-ivory hover:text-gold hover:border-gold/30 transition-all">
            <ArrowLeftIcon class="w-6 h-6" />
          </button>
          <div>
            <p class="text-xs uppercase tracking-[0.3em] text-gold/70 font-bold">Preferences</p>
            <h1 class="mt-2 font-display text-4xl text-theme-text drop-shadow-lg flex items-center gap-3">
              Notification <span class="text-transparent bg-clip-text bg-gradient-to-r from-gold to-ivory">Settings</span>
            </h1>
          </div>
        </div>
        
        <button 
          @click="saveSettings"
          :disabled="loading"
          class="relative z-10 flex items-center justify-center gap-2 rounded-xl bg-gold text-obsidian px-6 py-3 text-sm font-bold hover:bg-gold-light transition-all active:scale-[0.98] shrink-0 disabled:opacity-70"
        >
          <CheckCircleIcon class="w-5 h-5" />
          {{ loading ? 'Saving...' : 'Save Changes' }}
        </button>
      </div>

      <!-- Settings Content -->
      <div class="rounded-2xl border border-theme-border bg-theme-surface shadow-xl p-8">
        <div class="flex items-center gap-3 mb-8 pb-6 border-b border-theme-border/50">
          <div class="h-12 w-12 rounded-xl bg-gold/10 border border-gold/20 flex items-center justify-center">
            <Cog6ToothIcon class="w-6 h-6 text-gold" />
          </div>
          <div>
            <h2 class="font-display text-2xl text-theme-text">Alert Preferences</h2>
            <p class="text-sm text-ivory/60 mt-1">Choose which notifications you'd like to receive in your activity stream.</p>
          </div>
        </div>
        
        <div class="space-y-4">
          <label class="flex items-center justify-between cursor-pointer p-5 rounded-xl border border-theme-border bg-theme-bg/50 hover:border-gold/30 transition-colors group">
            <div class="pr-6">
              <p class="font-bold text-theme-text text-lg group-hover:text-gold transition-colors">Booking Updates</p>
              <p class="text-sm text-ivory/50 mt-1">Get notified immediately about appointment confirmations, cancellations, and status changes.</p>
            </div>
            <input type="checkbox" v-model="settings.notify_bookings" class="w-6 h-6 accent-gold cursor-pointer shrink-0" />
          </label>
          
          <label class="flex items-center justify-between cursor-pointer p-5 rounded-xl border border-theme-border bg-theme-bg/50 hover:border-gold/30 transition-colors group">
            <div class="pr-6">
              <p class="font-bold text-theme-text text-lg group-hover:text-gold transition-colors">System Updates</p>
              <p class="text-sm text-ivory/50 mt-1">Alert me whenever my personal profile information or avatar is updated.</p>
            </div>
            <input type="checkbox" v-model="settings.notify_system" class="w-6 h-6 accent-gold cursor-pointer shrink-0" />
          </label>

          <label class="flex items-center justify-between cursor-pointer p-5 rounded-xl border border-theme-border bg-theme-bg/50 hover:border-gold/30 transition-colors group">
            <div class="pr-6">
              <p class="font-bold text-theme-text text-lg group-hover:text-gold transition-colors">Wishlist Updates</p>
              <p class="text-sm text-ivory/50 mt-1">Receive alerts when new items are successfully saved to your personal wishlist.</p>
            </div>
            <input type="checkbox" v-model="settings.notify_wishlist" class="w-6 h-6 accent-gold cursor-pointer shrink-0" />
          </label>

          <label class="flex items-center justify-between cursor-pointer p-5 rounded-xl border border-theme-border bg-theme-bg/50 hover:border-gold/30 transition-colors group">
            <div class="pr-6">
              <p class="font-bold text-theme-text text-lg group-hover:text-gold transition-colors">New Blog Updates</p>
              <p class="text-sm text-ivory/50 mt-1">Be the first to know when our barbers publish new articles, style tips, or grooming advice.</p>
            </div>
            <input type="checkbox" v-model="settings.notify_blog" class="w-6 h-6 accent-gold cursor-pointer shrink-0" />
          </label>
          
          <label class="flex items-center justify-between cursor-pointer p-5 rounded-xl border border-theme-border bg-theme-bg/50 hover:border-gold/30 transition-colors group">
            <div class="pr-6">
              <p class="font-bold text-theme-text text-lg group-hover:text-gold transition-colors">General System Settings</p>
              <p class="text-sm text-ivory/50 mt-1">Get notifications when you make changes to your global account preferences and settings.</p>
            </div>
            <input type="checkbox" v-model="settings.notify_general" class="w-6 h-6 accent-gold cursor-pointer shrink-0" />
          </label>
        </div>
      </div>
    </div>
  </CustomerLayout>
</template>
