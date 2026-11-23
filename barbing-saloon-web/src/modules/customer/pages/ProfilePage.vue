<template>
  <CustomerLayout>
    <section class="animate-fade-in space-y-8 pb-12">
      <!-- Luxury Header Banner -->
      <div class="relative overflow-hidden rounded-3xl border border-slate-200 dark:border-white/[0.08] bg-gradient-to-br from-amber-500/10 via-amber-400/5 to-white dark:from-[#12161C] dark:via-[#0E1217] dark:to-[#12161C] p-8 lg:p-10 shadow-2xl">
        <div class="absolute -right-16 -top-16 h-64 w-64 rounded-full bg-[var(--color-accent)]/5 blur-3xl pointer-events-none"></div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
          <div class="flex items-center gap-6">
            <!-- Avatar in Header -->
            <div class="relative group cursor-pointer shrink-0" @click="$refs.avatarInput.click()">
              <div class="h-20 w-20 md:h-24 md:w-24 rounded-2xl border-2 border-[var(--color-accent)]/40 overflow-hidden shadow-lg transition-all group-hover:border-[var(--color-accent)] bg-slate-100 dark:bg-black/40">
                <img v-if="avatarPreview" :src="avatarPreview" @error="avatarPreview = null" alt="Avatar" class="h-full w-full object-cover" />
                <div v-else class="h-full w-full bg-[var(--color-accent)]/10 flex items-center justify-center">
                  <span class="text-2xl font-display font-bold text-[var(--color-accent)]">{{ initials }}</span>
                </div>
              </div>
              <!-- Camera overlay -->
              <div class="absolute inset-0 rounded-2xl bg-black/60 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                <CameraIcon class="w-6 h-6 text-white" />
              </div>
              <!-- Edit badge -->
              <div class="absolute -bottom-1 -right-1 h-6 w-6 rounded-lg bg-[var(--color-accent)] flex items-center justify-center border border-white dark:border-black shadow-sm text-white">
                <PencilSquareIcon class="w-3.5 h-3.5" />
              </div>
            </div>

            <div>
              <p class="text-xs uppercase tracking-[0.3em] text-[var(--color-accent)] font-bold">Personal Account</p>
              <h1 class="mt-1 font-display text-3xl lg:text-4xl text-slate-900 dark:text-white">
                Account <span class="text-transparent bg-clip-text bg-gradient-to-r from-[var(--color-accent)] to-amber-200">Settings</span>
              </h1>
              <p class="mt-1.5 max-w-xl text-xs sm:text-sm text-[var(--color-text-secondary)] leading-relaxed">
                Update your contact details, grooming preferences, and review your account activity.
              </p>
            </div>
          </div>
        </div>
        <input ref="avatarInput" type="file" accept="image/*" @change="handleFile" class="hidden" />
      </div>

      <div class="grid gap-6 lg:grid-cols-[1.1fr_0.9fr]">
        <!-- Personal Info Form -->
        <form class="rounded-3xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#12161C] p-6 lg:p-8 shadow-xl" @submit.prevent="submit">
          <h2 class="font-display text-xl text-slate-900 dark:text-white pb-4 border-b border-slate-100 dark:border-white/[0.06] flex items-center gap-2">
            <UserCircleIcon class="w-5 h-5 text-[var(--color-accent)]" />
            Personal Details
          </h2>

          <!-- Avatar Preview Row -->
          <div v-if="avatarPreview" class="mt-5 rounded-2xl border border-slate-200 dark:border-white/[0.06] bg-slate-50 dark:bg-white/[0.03] p-3.5 flex items-center gap-4">
            <img :src="avatarPreview" @error="avatarPreview = null" alt="Profile picture" class="h-12 w-12 rounded-xl object-cover border border-[var(--color-accent)]/30" />
            <div class="flex-1 min-w-0">
              <p class="text-xs font-bold text-slate-900 dark:text-white truncate">{{ form.name || form.username || 'Custom Avatar' }}</p>
              <p class="text-[11px] text-[var(--color-text-secondary)]">Click avatar to select replacement</p>
            </div>
            <button type="button" @click="removeAvatar" class="text-xs text-red-500 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 transition-colors px-3 py-1 rounded-lg border border-red-500/20 hover:bg-red-50 dark:hover:bg-red-500/10">
              Remove
            </button>
          </div>
          <div v-else class="mt-5 rounded-2xl border border-dashed border-slate-200 dark:border-white/10 bg-slate-50/50 dark:bg-white/[0.02] p-4 flex items-center gap-3.5 cursor-pointer hover:border-amber-400/50 dark:hover:border-white/20 transition-colors" @click="$refs.avatarInput.click()">
            <div class="h-11 w-11 rounded-xl bg-slate-100 dark:bg-white/[0.04] border border-slate-200 dark:border-white/10 flex items-center justify-center shrink-0 text-[var(--color-accent)]">
              <CameraIcon class="w-5 h-5" />
            </div>
            <div>
              <p class="text-xs font-bold text-slate-900 dark:text-white">Upload Profile Photo</p>
              <p class="text-[11px] text-[var(--color-text-secondary)]">PNG, JPG or WebP (max 5MB)</p>
            </div>
          </div>

          <div class="mt-5 space-y-4">
            <div class="space-y-1.5">
              <label class="text-xs font-semibold uppercase tracking-wider text-[var(--color-text-secondary)]">Username</label>
              <input v-model="form.username" placeholder="Not set" :disabled="!canChangeUsername" class="w-full rounded-xl border border-slate-200 dark:border-white/[0.08] bg-slate-50 dark:bg-white/[0.04] px-4 py-2.5 text-sm text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-white/30 outline-none transition-colors focus:border-[var(--color-accent)] focus:bg-white dark:focus:bg-black/40 disabled:opacity-50 disabled:cursor-not-allowed" />
              <p v-if="!canChangeUsername" class="text-[10px] text-amber-500 dark:text-amber-400">Username can be updated once every 30 days. {{ usernameChangeCountdown }} day(s) remaining.</p>
            </div>
            <div class="space-y-1.5">
              <label class="text-xs font-semibold uppercase tracking-wider text-[var(--color-text-secondary)]">Full Name</label>
              <input v-model="form.name" placeholder="Not set" class="w-full rounded-xl border border-slate-200 dark:border-white/[0.08] bg-slate-50 dark:bg-white/[0.04] px-4 py-2.5 text-sm text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-white/30 outline-none transition-colors focus:border-[var(--color-accent)] focus:bg-white dark:focus:bg-black/40" />
            </div>
            <div class="space-y-1.5">
              <div class="flex items-center justify-between">
                <label class="text-xs font-semibold uppercase tracking-wider text-[var(--color-text-secondary)]">Email Address</label>
                <span
                  v-if="isEmailVerified"
                  class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-600 dark:text-emerald-400 bg-emerald-500/10 px-2 py-0.5 rounded-full border border-emerald-500/20"
                >
                  <CheckCircleIcon class="w-3 h-3" />
                  <span>Verified &amp; Protected</span>
                </span>
                <router-link
                  v-else
                  to="/customer/dashboard/verify-email"
                  class="inline-flex items-center gap-1 text-[10px] font-bold text-amber-600 dark:text-amber-400 bg-amber-500/10 px-2 py-0.5 rounded-full border border-amber-500/20 hover:bg-amber-500/20 transition-colors"
                >
                  <ClockIcon class="w-3 h-3" />
                  <span>Unverified — Verify Now</span>
                </router-link>
              </div>
              <input v-model="form.email" type="email" placeholder="Not set" :disabled="!canChangeEmail" class="w-full rounded-xl border border-slate-200 dark:border-white/[0.08] bg-slate-50 dark:bg-white/[0.04] px-4 py-2.5 text-sm text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-white/30 outline-none transition-colors focus:border-[var(--color-accent)] focus:bg-white dark:focus:bg-black/40 disabled:opacity-50 disabled:cursor-not-allowed" />
              <p v-if="!canChangeEmail" class="text-[10px] text-amber-500 dark:text-amber-400">Email address can only be changed once every 30 days.</p>
            </div>
            <div class="space-y-1.5">
              <label class="text-xs font-semibold uppercase tracking-wider text-[var(--color-text-secondary)]">Phone Number</label>
              <input v-model="form.phone" placeholder="+234..." class="w-full rounded-xl border border-slate-200 dark:border-white/[0.08] bg-slate-50 dark:bg-white/[0.04] px-4 py-2.5 text-sm text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-white/30 outline-none transition-colors focus:border-[var(--color-accent)] focus:bg-white dark:focus:bg-black/40" />
            </div>
            <div class="space-y-1.5">
              <label class="text-xs font-semibold uppercase tracking-wider text-[var(--color-text-secondary)]">Grooming & Style Preferences</label>
              <textarea v-model="form.bio" rows="3" placeholder="Notes for your barbers (e.g. skin sensitivities, preferred blade lengths, parting styles)..." class="w-full rounded-xl border border-slate-200 dark:border-white/[0.08] bg-slate-50 dark:bg-white/[0.04] px-4 py-2.5 text-sm text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-white/30 outline-none transition-colors focus:border-[var(--color-accent)] focus:bg-white dark:focus:bg-black/40 resize-none leading-relaxed"></textarea>
            </div>
          </div>

          <button 
            type="submit"
            :disabled="saving"
            class="mt-6 w-full rounded-xl bg-[var(--color-accent)] py-3 text-xs font-bold text-white shadow-lg shadow-[var(--color-accent)]/20 transition-all hover:brightness-110 active:scale-[0.98] disabled:opacity-50"
          >
            {{ saving ? 'Saving Profile...' : 'Save Profile Changes' }}
          </button>
          <p v-if="status" class="mt-3 text-center text-xs font-medium text-emerald-600 dark:text-emerald-400 bg-emerald-500/10 py-2 rounded-lg border border-emerald-500/20">{{ status }}</p>
        </form>

        <!-- Right Column: Verification & Activity Feed -->
        <div class="space-y-6">
          <EmailVerificationCard :email="form.email" />
          <UserProfileCookiePreferencesCard />

          <div class="rounded-3xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#12161C] p-6 lg:p-8 shadow-xl flex flex-col justify-between">
          <div>
            <div class="mb-6 rounded-2xl border border-slate-200/80 dark:border-white/[0.06] bg-slate-50 dark:bg-white/[0.03] p-4">
              <h3 class="text-sm font-bold text-slate-900 dark:text-white">Display &amp; System</h3>
              <p class="mt-1 text-[11px] text-[var(--color-text-secondary)]">Personal dashboard preferences</p>
              <div class="mt-4 flex items-center justify-between gap-3">
                <div>
                  <p class="text-xs font-semibold text-slate-900 dark:text-white">Theme</p>
                  <p class="mt-0.5 text-[10px] text-[var(--color-text-secondary)]">{{ isDark ? 'Dark appearance' : 'Light appearance' }}</p>
                </div>
                <ThemeToggleSwitch />
              </div>
              <div class="mt-4 flex items-center justify-between gap-3 border-t border-slate-200/80 dark:border-white/[0.06] pt-4">
                <div>
                  <p class="text-xs font-semibold text-slate-900 dark:text-white">System status</p>
                  <p class="mt-0.5 text-[10px] text-[var(--color-text-secondary)]">Database connection</p>
                </div>
                <DatabaseStatusBadge />
              </div>
            </div>

            <h2 class="font-display text-xl text-slate-900 dark:text-white pb-4 border-b border-slate-100 dark:border-white/[0.06] flex items-center gap-2">
              <ClockIcon class="w-5 h-5 text-[var(--color-accent)]" />
              Recent <span class="text-transparent bg-clip-text bg-gradient-to-r from-[var(--color-accent)] to-amber-200">Activity</span>
            </h2>
            <div class="mt-5 space-y-3">
              <article 
                v-for="booking in history.latest_bookings || []" 
                :key="booking.id" 
                class="rounded-2xl border border-slate-200/80 dark:border-white/[0.06] bg-slate-50 dark:bg-[#1E232B]/40 p-4 transition-all hover:border-amber-400/40 dark:hover:border-white/20 hover:bg-slate-100 dark:hover:bg-[#1E232B]"
              >
                <div class="flex items-start justify-between gap-3">
                  <div>
                    <p class="font-display text-base text-slate-900 dark:text-white">{{ booking.service?.name || 'Grooming Cut' }}</p>
                    <p class="mt-1 text-xs text-[var(--color-text-secondary)]">{{ booking.appointment_date }} at {{ booking.appointment_time }}</p>
                  </div>
                  <span class="rounded-full bg-slate-100 dark:bg-white/[0.05] border border-slate-200 dark:border-white/10 px-2.5 py-0.5 text-[10px] font-bold text-slate-700 dark:text-white/70 capitalize">
                    {{ booking.status }}
                  </span>
                </div>
              </article>
              <div v-if="(history.latest_bookings || []).length === 0" class="rounded-2xl border border-dashed border-slate-200 dark:border-white/10 p-10 text-center bg-slate-50/50 dark:bg-white/[0.02]">
                <p class="text-xs text-[var(--color-text-secondary)]">No recent appointments recorded.</p>
              </div>
            </div>
          </div>
        </div>
      </div>
      </div>
    </section>
  </CustomerLayout>
</template>

<script setup>
import { onMounted, reactive, ref, computed } from 'vue';
import CustomerLayout from '@/portals/customer/layouts/CustomerLayout.vue';
import { client } from '@/shared/api/client';
import { useToast } from '../../../core/composables/useToast';
import { useAuthStore } from '../../auth/store/auth.store';
import { getStorageUrl } from '@/core/utils/url';
import ThemeToggleSwitch from '@/components/ThemeToggleSwitch.vue';
import DatabaseStatusBadge from '@/core/components/DatabaseStatusBadge.vue';
import EmailVerificationCard from '@/shared/components/EmailVerificationCard.vue';
import UserProfileCookiePreferencesCard from '@/shared/components/UserProfileCookiePreferencesCard.vue';
import { useTheme } from '@/core/composables/useTheme';
import { 
  UserCircleIcon, 
  CameraIcon, 
  PencilSquareIcon, 
  ClockIcon,
  CheckCircleIcon
} from '@heroicons/vue/24/outline';

const toast = useToast();
const authStore = useAuthStore();
const { isDark } = useTheme();
const form = reactive({ name: '', username: '', email: '', phone: '', bio: '', avatar: null });
const status = ref('');
const saving = ref(false);
const history = ref({});
const originalProfile = ref({});
const avatarPreview = ref(null);
const avatarInput = ref(null);
const removeAvatarRequested = ref(false);

const isEmailVerified = computed(() => {
  return !!authStore.user?.is_email_verified || !!authStore.user?.email_verified_at;
});

const initials = computed(() => {
  const name = form.name || form.username || form.email || '?';
  return name.split(' ').map(w => w[0]).join('').toUpperCase().slice(0, 2);
});

const usernameChangeCountdown = computed(() => {
  if (!originalProfile.value.last_username_change) return 0;
  const targetDate = new Date(originalProfile.value.last_username_change);
  targetDate.setDate(targetDate.getDate() + 30);
  const now = new Date();
  const diffTime = targetDate - now;
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
  return diffDays > 0 ? diffDays : 0;
});

const canChangeUsername = computed(() => {
  if (!originalProfile.value.username) return true;
  if (!originalProfile.value.last_username_change) return true;
  return usernameChangeCountdown.value === 0;
});

const canChangeEmail = computed(() => {
  if (!originalProfile.value.email) return true;
  if (!originalProfile.value.last_email_change) return true;
  const targetDate = new Date(originalProfile.value.last_email_change);
  targetDate.setDate(targetDate.getDate() + 30);
  return new Date() > targetDate;
});

async function handleFile(event) {
  const file = event.target.files[0];
  if (!file) return;

  if (file.size > 5 * 1024 * 1024) {
    toast.error('Image must be under 5MB');
    return;
  }

  removeAvatarRequested.value = false;
  form.avatar = file;
  const reader = new FileReader();
  reader.onload = (e) => {
    avatarPreview.value = e.target.result;
  };
  reader.readAsDataURL(file);

  // Immediately persist avatar upload to the database
  const uploadData = new FormData();
  uploadData.append('avatar', file);

  saving.value = true;
  try {
    const res = await client.post('/customer/avatar', uploadData);
    const updated = res.data?.data?.profile?.data ?? res.data?.data?.user ?? res.data?.data ?? {};
    const newAvatar = updated.avatar_url || updated.avatar;
    if (newAvatar) {
      avatarPreview.value = getStorageUrl(newAvatar);
    }
    form.avatar = null;
    removeAvatarRequested.value = false;
    await authStore.fetchUser();
    toast.success('Profile picture saved successfully');
  } catch (error) {
    const errorMsg =
      error.response?.data?.message ||
      error.response?.data?.error?.message ||
      (typeof error.response?.data?.error === 'string' ? error.response?.data?.error : null) ||
      'Failed to upload profile picture';
    toast.error(errorMsg);
  } finally {
    saving.value = false;
  }
}

async function removeAvatar() {
  saving.value = true;
  try {
    await client.delete('/customer/avatar');
    form.avatar = null;
    avatarPreview.value = null;
    removeAvatarRequested.value = false;
    if (avatarInput.value) avatarInput.value.value = '';
    await authStore.fetchUser();
    toast.success('Profile picture removed');
  } catch (error) {
    const errorMsg =
      error.response?.data?.message ||
      error.response?.data?.error?.message ||
      (typeof error.response?.data?.error === 'string' ? error.response?.data?.error : null) ||
      'Failed to remove profile picture';
    toast.error(errorMsg);
  } finally {
    saving.value = false;
  }
}

async function submit() {
  saving.value = true;
  const payload = new FormData();
  payload.append('name', form.name);
  payload.append('phone', form.phone || '');
  payload.append('username', form.username || '');
  payload.append('email', form.email || '');
  payload.append('bio', form.bio || '');

  if (removeAvatarRequested.value) {
    payload.append('remove_avatar', '1');
  } else if (form.avatar instanceof File) {
    payload.append('avatar', form.avatar);
  }

  try {
    const response = await client.post('/customer/profile', payload);
    status.value = response.data?.message || 'Profile updated successfully!';
    toast.success('Profile updated');
    form.avatar = null;
    removeAvatarRequested.value = false;
    await loadProfile();
    await authStore.fetchUser();
  } catch (error) {
    const errorMsg =
      error.response?.data?.message ||
      error.response?.data?.error?.message ||
      (typeof error.response?.data?.error === 'string' ? error.response?.data?.error : null) ||
      'Failed to update profile';
    toast.error(errorMsg);
  } finally {
    saving.value = false;
  }
}

async function loadProfile() {
  try {
    const response = await client.get('/customer/profile');
    const profile = response.data?.data?.profile?.data ?? response.data?.data?.profile ?? {};
    originalProfile.value = profile;
    form.name = profile.name || '';
    form.username = profile.username || '';
    form.email = profile.email || '';
    form.phone = profile.phone || '';
    form.bio = profile.bio || '';
    history.value = response.data?.data?.history || {};

    const avatarUrl = profile.avatar_url || profile.avatar;
    if (avatarUrl) {
      avatarPreview.value = getStorageUrl(avatarUrl);
    } else {
      avatarPreview.value = null;
    }
  } catch (err) {
    console.error('Failed to load profile:', err);
  }
}

onMounted(() => {
  loadProfile();
});
</script>
