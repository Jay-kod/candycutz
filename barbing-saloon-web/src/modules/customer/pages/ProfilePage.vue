<template>
  <CustomerLayout>
    <section class="animate-fade-in space-y-8">
      <!-- Page Header -->
      <div class="relative overflow-hidden rounded-2xl border border-[var(--color-border)] bg-[var(--color-bg-secondary)] p-8 shadow-xl">
        <div class="absolute -right-16 -top-16 h-64 w-64 rounded-full bg-[var(--color-accent)]/5 blur-3xl pointer-events-none"></div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
          <div class="flex items-center gap-6">
            <!-- Avatar in Header -->
            <div class="relative group cursor-pointer shrink-0" @click="$refs.avatarInput.click()">
              <div class="h-24 w-24 rounded-full border-2 border-[var(--color-accent)]/40 overflow-hidden shadow-sm transition-all group-hover:border-[var(--color-accent)] group-hover:shadow-md">
                <img v-if="avatarPreview" :src="avatarPreview" alt="Avatar" class="h-full w-full object-cover" />
                <div v-else class="h-full w-full bg-[var(--color-accent)]/10 flex items-center justify-center">
                  <span class="text-2xl font-display font-bold text-[var(--color-accent)]">{{ initials }}</span>
                </div>
              </div>
              <!-- Camera overlay -->
              <div class="absolute inset-0 rounded-full bg-black/50 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
              </div>
              <!-- Edit badge -->
              <div class="absolute -bottom-1 -right-1 h-7 w-7 rounded-full bg-[var(--color-accent)] flex items-center justify-center border-2 border-[var(--color-bg-secondary)] shadow-sm">
                <svg class="w-3.5 h-3.5 text-[var(--color-accent-foreground)]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                </svg>
              </div>
            </div>
            <div>
              <p class="text-xs uppercase tracking-[0.3em] text-[var(--color-accent)]/80 font-medium">Customer Dashboard</p>
              <h1 class="mt-2 font-display text-3xl lg:text-4xl text-[var(--color-text-primary)]">
                Your <span class="text-[var(--color-accent)]">Profile</span>
              </h1>
              <p class="mt-2 max-w-xl text-sm text-[var(--color-text-secondary)] leading-relaxed">
                Manage your personal information and view your recent activity.
              </p>
            </div>
          </div>
        </div>
        <!-- Hidden file input -->
        <input ref="avatarInput" type="file" accept="image/*" @change="handleFile" class="hidden" />
      </div>

      <div class="grid gap-6 lg:grid-cols-[0.95fr_1.05fr]">
        <form class="rounded-2xl border border-[var(--color-border)] bg-[var(--color-bg-secondary)] p-8 shadow-sm" @submit.prevent="submit">
          <h2 class="font-display text-2xl text-[var(--color-text-primary)]">Personal Info</h2>

          <!-- Avatar Preview Card -->
          <div v-if="avatarPreview" class="mt-5 rounded-xl border border-[var(--color-border)] bg-[var(--color-bg-tertiary)] p-4 flex items-center gap-4">
            <img :src="avatarPreview" alt="Profile picture" class="h-14 w-14 rounded-full object-cover border border-[var(--color-accent)]/30" />
            <div class="flex-1 min-w-0">
              <p class="text-sm font-semibold text-[var(--color-text-primary)] truncate">{{ form.name || form.username || 'Your photo' }}</p>
              <p class="text-xs text-[var(--color-text-muted)]">Click the avatar in the header to change your picture</p>
            </div>
            <button type="button" @click="removeAvatar" class="text-xs text-[var(--color-danger)] hover:underline transition-colors px-3 py-1.5 rounded-lg border border-[var(--color-danger)]/20 hover:bg-[var(--color-danger)]/10">
              Remove
            </button>
          </div>
          <div v-else class="mt-5 rounded-xl border border-dashed border-[var(--color-border)] bg-[var(--color-bg-tertiary)] p-4 flex items-center gap-4 cursor-pointer hover:border-[var(--color-accent)]/40 transition-colors" @click="$refs.avatarInput.click()">
            <div class="h-14 w-14 rounded-full bg-[var(--color-accent)]/10 border border-[var(--color-accent)]/20 flex items-center justify-center shrink-0">
              <svg class="w-6 h-6 text-[var(--color-accent)]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
              </svg>
            </div>
            <div>
              <p class="text-sm font-semibold text-[var(--color-text-primary)]">Upload a profile picture</p>
              <p class="text-xs text-[var(--color-text-muted)]">JPG, PNG or WebP. Max 5MB.</p>
            </div>
          </div>

          <div class="mt-6 space-y-5">
            <div class="space-y-2">
              <label class="text-xs font-semibold uppercase tracking-widest text-[var(--color-text-secondary)]">Username</label>
              <input v-model="form.username" placeholder="Not set" :disabled="!canChangeUsername" class="w-full rounded-xl border border-[var(--color-border)] bg-[var(--color-bg-input)] px-4 py-3 text-[var(--color-text-primary)] placeholder-[var(--color-text-muted)] outline-none transition-colors focus:border-[var(--color-accent)]/50 disabled:opacity-50 disabled:cursor-not-allowed" />
              <p v-if="!canChangeUsername" class="text-[10px] text-[var(--color-danger)]">You can only change your username once every 30 days. Try again in {{ usernameChangeCountdown }} day{{ usernameChangeCountdown !== 1 ? 's' : '' }}.</p>
            </div>
            <div class="space-y-2">
              <label class="text-xs font-semibold uppercase tracking-widest text-[var(--color-text-secondary)]">Real Name / Display Name</label>
              <input v-model="form.name" placeholder="Not set" class="w-full rounded-xl border border-[var(--color-border)] bg-[var(--color-bg-input)] px-4 py-3 text-[var(--color-text-primary)] placeholder-[var(--color-text-muted)] outline-none transition-colors focus:border-[var(--color-accent)]/50" />
            </div>
            <div class="space-y-2">
              <label class="text-xs font-semibold uppercase tracking-widest text-[var(--color-text-secondary)]">Email</label>
              <input v-model="form.email" type="email" placeholder="Not set" :disabled="!canChangeEmail" class="w-full rounded-xl border border-[var(--color-border)] bg-[var(--color-bg-input)] px-4 py-3 text-[var(--color-text-primary)] placeholder-[var(--color-text-muted)] outline-none transition-colors focus:border-[var(--color-accent)]/50 disabled:opacity-50 disabled:cursor-not-allowed" />
              <p v-if="!canChangeEmail" class="text-[10px] text-[var(--color-danger)]">You can only change your email once every 30 days.</p>
            </div>
            <div class="space-y-2">
              <label class="text-xs font-semibold uppercase tracking-widest text-[var(--color-text-secondary)]">Phone</label>
              <input v-model="form.phone" placeholder="Not set" class="w-full rounded-xl border border-[var(--color-border)] bg-[var(--color-bg-input)] px-4 py-3 text-[var(--color-text-primary)] placeholder-[var(--color-text-muted)] outline-none transition-colors focus:border-[var(--color-accent)]/50" />
            </div>
            <div class="space-y-2">
              <label class="text-xs font-semibold uppercase tracking-widest text-[var(--color-text-secondary)]">Bio & Grooming Preferences</label>
              <textarea v-model="form.bio" rows="3" placeholder="Tell your barber about your style preferences, skin sensitivities, or grooming notes..." class="w-full rounded-xl border border-[var(--color-border)] bg-[var(--color-bg-input)] px-4 py-3 text-[var(--color-text-primary)] placeholder-[var(--color-text-muted)] outline-none transition-colors focus:border-[var(--color-accent)]/50 resize-none"></textarea>
            </div>
          </div>
          <button class="mt-8 w-full rounded-xl bg-[var(--color-accent)] py-4 text-sm font-bold text-[var(--color-accent-foreground)] shadow-sm transition-all hover:bg-[var(--color-accent-hover)] hover:scale-[1.02]">
            Save profile
          </button>
          <p v-if="status" class="mt-4 text-center text-sm font-medium text-[var(--color-success)] bg-[var(--color-success)]/10 py-2 rounded-lg border border-[var(--color-success)]/20">{{ status }}</p>
        </form>

        <div class="rounded-2xl border border-[var(--color-border)] bg-[var(--color-bg-secondary)] p-8 shadow-sm">
          <h2 class="font-display text-2xl text-[var(--color-text-primary)]">Recent <span class="text-[var(--color-accent)]">Activity</span></h2>
          <div class="mt-6 space-y-4">
            <article v-for="booking in history.latest_bookings || []" :key="booking.id" class="rounded-xl border border-[var(--color-border)] bg-[var(--color-bg-tertiary)] p-5 transition-all hover:border-[var(--color-accent)]/30 hover:bg-[var(--color-bg-secondary)]">
              <div class="flex items-start justify-between">
                <div>
                  <p class="font-display text-lg text-[var(--color-text-primary)]">{{ booking.service?.name }}</p>
                  <p class="mt-1 text-sm text-[var(--color-text-secondary)]">{{ booking.appointment_date }} at {{ booking.appointment_time }}</p>
                </div>
              </div>
            </article>
            <div v-if="(history.latest_bookings || []).length === 0" class="rounded-xl border border-dashed border-[var(--color-border)] p-8 text-center">
              <p class="text-sm text-[var(--color-text-muted)]">No recent activity.</p>
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
import { customerApi } from '@/shared/api/old_customerApi';
import { useToast } from '../../../core/composables/useToast';
import { useAuthStore } from '../../auth/store/auth.store';
import { getStorageUrl } from '@/core/utils/url';

const toast = useToast();
const authStore = useAuthStore();
const form = reactive({ name: '', username: '', email: '', phone: '', bio: '', avatar: null });
const status = ref('');
const history = ref({});
const originalProfile = ref({});
const avatarPreview = ref(null);
const avatarInput = ref(null);

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

function handleFile(event) {
  const file = event.target.files[0];
  if (!file) return;

  if (file.size > 5 * 1024 * 1024) {
    toast.error('Image must be under 5MB');
    return;
  }

  form.avatar = file;
  const reader = new FileReader();
  reader.onload = (e) => {
    avatarPreview.value = e.target.result;
  };
  reader.readAsDataURL(file);
}

function removeAvatar() {
  form.avatar = null;
  avatarPreview.value = null;
  if (avatarInput.value) avatarInput.value.value = '';
}

async function submit() {
  const payload = new FormData();
  payload.append('name', form.name);
  payload.append('phone', form.phone || '');
  payload.append('username', form.username || '');
  payload.append('email', form.email || '');
  payload.append('bio', form.bio || '');

  if (form.avatar) {
    payload.append('avatar', form.avatar);
  }

  try {
    const response = await customerApi.updateProfile(payload);
    status.value = response.data.message;
    toast.success('Profile updated');
    await loadProfile();
    await authStore.fetchUser();
  } catch (error) {
    toast.error(error.response?.data?.error || 'Failed to update profile');
  }
}

async function loadProfile() {
  const response = await customerApi.profile();
  const profile = response.data.data.profile.data ?? response.data.data.profile;
  originalProfile.value = profile;
  form.name = profile.name || '';
  form.username = profile.username || '';
  form.email = profile.email || '';
  form.phone = profile.phone || '';
  form.bio = profile.bio || '';
  history.value = response.data.data.history;

  if (profile.avatar) {
    avatarPreview.value = getStorageUrl(profile.avatar);
  } else {
    avatarPreview.value = null;
  }
}

onMounted(() => {
  loadProfile();
});
</script>
