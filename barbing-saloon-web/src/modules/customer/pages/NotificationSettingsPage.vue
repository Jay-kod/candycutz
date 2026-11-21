<script setup>
import { ref, reactive, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import {
  Cog6ToothIcon,
  ArrowLeftIcon,
  CheckCircleIcon,
  LockClosedIcon,
  MoonIcon,
  BellIcon,
  DevicePhoneMobileIcon,
  EnvelopeIcon,
} from '@heroicons/vue/24/outline';
import CustomerLayout from '@/portals/customer/layouts/CustomerLayout.vue';
import { client } from '@/shared/api/client';
import { useToast } from '../../../core/composables/useToast';

const router = useRouter();
const toast = useToast();

const loading = ref(false);
const saving = ref(false);

const categories = ref([
  { key: 'security', label: 'Account & Security', description: 'Password changes, login alerts, and critical security notices' },
  { key: 'booking', label: 'Appointments & Bookings', description: 'Booking confirmations, reminders, cancellations, and status updates' },
  { key: 'payment', label: 'Payments & Receipts', description: 'Payment confirmations, receipt uploads, and refund notifications' },
  { key: 'review', label: 'Reviews & Ratings', description: 'New review submissions, verifications, and approvals' },
  { key: 'service', label: 'Services & Stylists', description: 'Service updates, barber assignments, and category availability' },
  { key: 'announcement', label: 'System Announcements', description: 'Maintenance notices, platform updates, and branch news' },
  { key: 'promotion', label: 'Promotions & Discounts', description: 'Special offers, seasonal discounts, and reward campaigns' },
]);

const channels = ref([
  { key: 'in_app', label: 'In-App', icon: BellIcon },
  { key: 'push', label: 'Push', icon: DevicePhoneMobileIcon },
  { key: 'email', label: 'Email', icon: EnvelopeIcon },
]);

const preferences = reactive({
  security: { in_app: true, push: true, email: true, locked_channels: ['in_app', 'email'] },
  booking: { in_app: true, push: true, email: true, locked_channels: [] },
  payment: { in_app: true, push: true, email: true, locked_channels: ['in_app', 'email'] },
  review: { in_app: true, push: true, email: false, locked_channels: [] },
  service: { in_app: true, push: true, email: false, locked_channels: [] },
  announcement: { in_app: true, push: true, email: false, locked_channels: [] },
  promotion: { in_app: true, push: false, email: false, locked_channels: [] },
});

const quietHours = reactive({
  enabled: false,
  start: '22:00',
  end: '08:00',
});

const loadSettings = async () => {
  try {
    loading.value = true;
    const res = await client.get('/notification-preferences');
    if (res.data?.success && res.data?.data) {
      const data = res.data.data;
      if (data.categories) categories.value = data.categories;
      if (data.preferences) {
        Object.keys(data.preferences).forEach((cat) => {
          if (!preferences[cat]) {
            preferences[cat] = {};
          }
          Object.assign(preferences[cat], data.preferences[cat]);
        });
      }
      if (data.quiet_hours) {
        Object.assign(quietHours, data.quiet_hours);
      }
    }
  } catch (err) {
    // Graceful fallback to legacy endpoint
    try {
      const legacyRes = await client.get('/notification-settings');
      if (legacyRes.data?.success && legacyRes.data?.data) {
        const leg = legacyRes.data.data;
        preferences.booking.in_app = leg.notify_bookings ?? true;
        preferences.promotion.in_app = leg.notify_promotions ?? false;
      }
    } catch (fallbackErr) {
      toast.error('Failed to load notification settings');
    }
  } finally {
    loading.value = false;
  }
};

const saveSettings = async () => {
  try {
    saving.value = true;
    const payload = {
      preferences: JSON.parse(JSON.stringify(preferences)),
      quiet_hours: { ...quietHours },
    };
    const res = await client.put('/notification-preferences', payload);
    if (res.data?.success) {
      toast.success('Notification preferences updated successfully');
    }
  } catch (err) {
    toast.error('Failed to update notification preferences');
  } finally {
    saving.value = false;
  }
};

const isLocked = (categoryKey, channelKey) => {
  const lockedList = preferences[categoryKey]?.locked_channels || [];
  return lockedList.includes(channelKey);
};

onMounted(() => {
  loadSettings();
});
</script>

<template>
  <CustomerLayout>
    <div class="max-w-5xl mx-auto space-y-6">
      <!-- Back Button -->
      <div class="flex items-center justify-between">
        <button
          type="button"
          @click="router.back()"
          class="flex items-center gap-2 text-sm font-semibold text-[var(--color-text-secondary)] hover:text-[var(--color-accent)] transition-colors"
        >
          <ArrowLeftIcon class="w-4 h-4" />
          Back
        </button>
      </div>

      <div class="bg-[var(--color-bg-secondary)] border border-[var(--color-border)] rounded-3xl p-6 sm:p-10 shadow-xl space-y-8">
        <!-- Title and Description -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-[var(--color-border)] pb-6">
          <div class="flex items-center gap-3">
            <div class="p-3 bg-[var(--color-accent)]/10 text-[var(--color-accent)] rounded-2xl">
              <Cog6ToothIcon class="w-6 h-6" />
            </div>
            <div>
              <h1 class="text-2xl sm:text-3xl font-display text-[var(--color-text-primary)]">Notification Preferences</h1>
              <p class="text-sm text-[var(--color-text-secondary)] mt-1">Control which alerts and channels you receive updates through.</p>
            </div>
          </div>
          <button
            type="button"
            @click="saveSettings"
            :disabled="saving"
            class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-2xl bg-[var(--color-accent)] text-[var(--color-accent-foreground)] font-bold text-sm hover:brightness-110 active:scale-95 transition-all shadow-lg shadow-[var(--color-accent)]/20 disabled:opacity-50"
          >
            <CheckCircleIcon class="w-5 h-5" />
            <span>{{ saving ? 'Saving...' : 'Save Preferences' }}</span>
          </button>
        </div>

        <!-- Channels Matrix -->
        <div class="space-y-4">
          <!-- Matrix Desktop Header -->
          <div class="hidden sm:grid sm:grid-cols-12 gap-4 pb-3 border-b border-[var(--color-border)] text-xs font-semibold uppercase tracking-wider text-[var(--color-text-tertiary)]">
            <div class="col-span-6">Category</div>
            <div class="col-span-2 text-center flex items-center justify-center gap-1.5">
              <BellIcon class="w-4 h-4 text-[var(--color-accent)]" />
              <span>In-App</span>
            </div>
            <div class="col-span-2 text-center flex items-center justify-center gap-1.5">
              <DevicePhoneMobileIcon class="w-4 h-4 text-emerald-500" />
              <span>Push</span>
            </div>
            <div class="col-span-2 text-center flex items-center justify-center gap-1.5">
              <EnvelopeIcon class="w-4 h-4 text-blue-500" />
              <span>Email</span>
            </div>
          </div>

          <!-- Category Rows -->
          <div class="divide-y divide-[var(--color-border)]/60">
            <div
              v-for="cat in categories"
              :key="cat.key"
              class="py-4 grid grid-cols-1 sm:grid-cols-12 gap-4 items-center"
            >
              <!-- Category info -->
              <div class="sm:col-span-6 space-y-1">
                <div class="flex items-center gap-2">
                  <h3 class="text-base font-semibold text-[var(--color-text-primary)]">{{ cat.label }}</h3>
                  <span
                    v-if="cat.key === 'security' || cat.key === 'payment'"
                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-500/10 text-amber-500 border border-amber-500/20"
                  >
                    <LockClosedIcon class="w-3 h-3" />
                    Mandatory
                  </span>
                </div>
                <p class="text-xs text-[var(--color-text-secondary)]">{{ cat.description }}</p>
              </div>

              <!-- Channel toggles: In-App -->
              <div class="flex items-center justify-between sm:justify-center sm:col-span-2">
                <span class="sm:hidden text-xs text-[var(--color-text-secondary)] flex items-center gap-1">
                  <BellIcon class="w-3.5 h-3.5" /> In-App
                </span>
                <label class="relative inline-flex items-center" :class="{ 'cursor-not-allowed opacity-60': isLocked(cat.key, 'in_app'), 'cursor-pointer': !isLocked(cat.key, 'in_app') }">
                  <input
                    type="checkbox"
                    v-model="preferences[cat.key].in_app"
                    :disabled="isLocked(cat.key, 'in_app')"
                    class="sr-only peer"
                  />
                  <div class="w-10 h-5 bg-[var(--color-bg-tertiary)] peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[var(--color-accent)]"></div>
                </label>
              </div>

              <!-- Channel toggles: Push -->
              <div class="flex items-center justify-between sm:justify-center sm:col-span-2">
                <span class="sm:hidden text-xs text-[var(--color-text-secondary)] flex items-center gap-1">
                  <DevicePhoneMobileIcon class="w-3.5 h-3.5" /> Push
                </span>
                <label class="relative inline-flex items-center cursor-pointer">
                  <input
                    type="checkbox"
                    v-model="preferences[cat.key].push"
                    class="sr-only peer"
                  />
                  <div class="w-10 h-5 bg-[var(--color-bg-tertiary)] peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-500"></div>
                </label>
              </div>

              <!-- Channel toggles: Email -->
              <div class="flex items-center justify-between sm:justify-center sm:col-span-2">
                <span class="sm:hidden text-xs text-[var(--color-text-secondary)] flex items-center gap-1">
                  <EnvelopeIcon class="w-3.5 h-3.5" /> Email
                </span>
                <label class="relative inline-flex items-center" :class="{ 'cursor-not-allowed opacity-60': isLocked(cat.key, 'email'), 'cursor-pointer': !isLocked(cat.key, 'email') }">
                  <input
                    type="checkbox"
                    v-model="preferences[cat.key].email"
                    :disabled="isLocked(cat.key, 'email')"
                    class="sr-only peer"
                  />
                  <div class="w-10 h-5 bg-[var(--color-bg-tertiary)] peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-blue-500"></div>
                </label>
              </div>
            </div>
          </div>
        </div>

        <!-- Quiet Hours Section -->
        <div class="rounded-2xl border border-[var(--color-border)] bg-[var(--color-bg-tertiary)]/40 p-5 sm:p-6 space-y-4">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
              <div class="p-2.5 rounded-xl bg-indigo-500/10 text-indigo-400">
                <MoonIcon class="w-5 h-5" />
              </div>
              <div>
                <h3 class="text-base font-semibold text-[var(--color-text-primary)]">Quiet Hours</h3>
                <p class="text-xs text-[var(--color-text-secondary)]">Mute non-urgent push notifications while resting. Critical security alerts will always deliver.</p>
              </div>
            </div>
            <label class="relative inline-flex items-center cursor-pointer">
              <input type="checkbox" v-model="quietHours.enabled" class="sr-only peer" />
              <div class="w-11 h-6 bg-[var(--color-bg-tertiary)] peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-500"></div>
            </label>
          </div>

          <div v-if="quietHours.enabled" class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-3 border-t border-[var(--color-border)]/60">
            <div>
              <label class="block text-xs font-semibold text-[var(--color-text-secondary)] mb-1">Quiet Hours Start</label>
              <input
                type="time"
                v-model="quietHours.start"
                class="w-full px-3 py-2 rounded-xl bg-[var(--color-bg-secondary)] border border-[var(--color-border)] text-[var(--color-text-primary)] text-sm focus:outline-none focus:border-[var(--color-accent)]"
              />
            </div>
            <div>
              <label class="block text-xs font-semibold text-[var(--color-text-secondary)] mb-1">Quiet Hours End</label>
              <input
                type="time"
                v-model="quietHours.end"
                class="w-full px-3 py-2 rounded-xl bg-[var(--color-bg-secondary)] border border-[var(--color-border)] text-[var(--color-text-primary)] text-sm focus:outline-none focus:border-[var(--color-accent)]"
              />
            </div>
          </div>
        </div>

        <!-- Footer CTA -->
        <div class="flex justify-end pt-2">
          <button
            type="button"
            @click="saveSettings"
            :disabled="saving"
            class="inline-flex items-center gap-2 px-8 py-3 rounded-2xl bg-[var(--color-accent)] text-[var(--color-accent-foreground)] font-bold text-sm hover:brightness-110 active:scale-95 transition-all shadow-lg shadow-[var(--color-accent)]/20 disabled:opacity-50"
          >
            <CheckCircleIcon class="w-5 h-5" />
            <span>{{ saving ? 'Saving Changes...' : 'Save Preferences' }}</span>
          </button>
        </div>
      </div>
    </div>
  </CustomerLayout>
</template>
