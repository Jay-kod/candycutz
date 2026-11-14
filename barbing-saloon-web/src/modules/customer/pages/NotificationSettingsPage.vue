<script setup>
import { ref, reactive, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { Cog6ToothIcon, ArrowLeftIcon, CheckCircleIcon } from '@heroicons/vue/24/outline';
import CustomerLayout from '@/portals/customer/layouts/CustomerLayout.vue';
import { useAuthStore } from '../../auth/store/auth.store';
import { client } from '@/shared/api/client';
import { useToast } from '../../../core/composables/useToast';

const router = useRouter();
const authStore = useAuthStore();
const toast = useToast();

const loading = ref(false);
const settings = reactive({
  notify_appointments: true,
  notify_bookings: true,
  notify_system: true,
  notify_promotions: false,
  notify_wishlist: true,
  notify_blog: false,
  notify_general: true,
});

const loadSettings = async () => {
  try {
    loading.value = true;
    const res = await client.get('/notification-settings');
    if (res.data?.success && res.data?.data) {
      Object.assign(settings, res.data.data);
    }
  } catch (err) {
    toast.error('Failed to load notification settings');
  } finally {
    loading.value = false;
  }
};

const saveSettings = async () => {
  try {
    loading.value = true;
    const res = await client.post('/notification-settings', settings);
    if (res.data?.success) {
      toast.success('Notification preferences updated successfully');
    }
  } catch (err) {
    toast.error('Failed to update notification preferences');
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  loadSettings();
});
</script>

<template>
  <CustomerLayout>
    <div class="max-w-4xl mx-auto space-y-6">
      <!-- Back Button and Title -->
      <div class="flex items-center justify-between">
        <button
          @click="router.back()"
          class="flex items-center gap-2 text-sm font-semibold text-[var(--color-text-secondary)] hover:text-[var(--color-accent)] transition-colors"
        >
          <ArrowLeftIcon class="w-4 h-4" />
          Back
        </button>
      </div>

      <div class="bg-[var(--color-bg-secondary)] border border-[var(--color-border)] rounded-3xl p-6 sm:p-10 shadow-xl space-y-8">
        <div>
          <div class="flex items-center gap-3">
            <div class="p-3 bg-[var(--color-accent)]/10 text-[var(--color-accent)] rounded-2xl">
              <Cog6ToothIcon class="w-6 h-6" />
            </div>
            <div>
              <h1 class="text-2xl sm:text-3xl font-display text-[var(--color-text-primary)]">Notification Preferences</h1>
              <p class="text-sm text-[var(--color-text-secondary)] mt-1">Control which updates and alerts you receive across channels.</p>
            </div>
          </div>
        </div>

        <div class="divide-y divide-[var(--color-border)]">
          <div class="py-4 flex items-center justify-between">
            <div>
              <h3 class="text-base font-semibold text-[var(--color-text-primary)]">Appointments & Bookings</h3>
              <p class="text-sm text-[var(--color-text-secondary)]">Get alerts when your appointment status updates (confirmed, cancelled, completed).</p>
            </div>
            <label class="relative inline-flex items-center cursor-pointer">
              <input type="checkbox" v-model="settings.notify_bookings" class="sr-only peer" />
              <div class="w-11 h-6 bg-[var(--color-bg-tertiary)] peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[var(--color-accent)]"></div>
            </label>
          </div>

          <div class="py-4 flex items-center justify-between">
            <div>
              <h3 class="text-base font-semibold text-[var(--color-text-primary)]">System Alerts</h3>
              <p class="text-sm text-[var(--color-text-secondary)]">Important security notices and platform maintenance schedules.</p>
            </div>
            <label class="relative inline-flex items-center cursor-pointer">
              <input type="checkbox" v-model="settings.notify_system" class="sr-only peer" />
              <div class="w-11 h-6 bg-[var(--color-bg-tertiary)] peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[var(--color-accent)]"></div>
            </label>
          </div>

          <div class="py-4 flex items-center justify-between">
            <div>
              <h3 class="text-base font-semibold text-[var(--color-text-primary)]">Wishlist & Availability</h3>
              <p class="text-sm text-[var(--color-text-secondary)]">Get notified when favored styles or preferred barbers become available.</p>
            </div>
            <label class="relative inline-flex items-center cursor-pointer">
              <input type="checkbox" v-model="settings.notify_wishlist" class="sr-only peer" />
              <div class="w-11 h-6 bg-[var(--color-bg-tertiary)] peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[var(--color-accent)]"></div>
            </label>
          </div>

          <div class="py-4 flex items-center justify-between">
            <div>
              <h3 class="text-base font-semibold text-[var(--color-text-primary)]">Promotions & Exclusive Deals</h3>
              <p class="text-sm text-[var(--color-text-secondary)]">Seasonal discounts, referral rewards, and VIP salon packages.</p>
            </div>
            <label class="relative inline-flex items-center cursor-pointer">
              <input type="checkbox" v-model="settings.notify_promotions" class="sr-only peer" />
              <div class="w-11 h-6 bg-[var(--color-bg-tertiary)] peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[var(--color-accent)]"></div>
            </label>
          </div>
        </div>

        <div class="pt-4 flex justify-end">
          <button
            @click="saveSettings"
            :disabled="loading"
            class="inline-flex items-center gap-2 px-8 py-3 rounded-2xl bg-[var(--color-accent)] text-[var(--color-accent-foreground)] font-bold text-sm hover:brightness-110 active:scale-95 transition-all shadow-lg shadow-[var(--color-accent)]/20 disabled:opacity-50"
          >
            <CheckCircleIcon class="w-5 h-5" />
            <span>{{ loading ? 'Saving...' : 'Save Preferences' }}</span>
          </button>
        </div>
      </div>
    </div>
  </CustomerLayout>
</template>
