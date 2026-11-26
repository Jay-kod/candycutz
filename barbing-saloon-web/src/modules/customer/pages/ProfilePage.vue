<template>
  <CustomerLayout>
    <section class="animate-fade-in space-y-4 sm:space-y-6 lg:space-y-8 pb-6 sm:pb-12">
      <!-- Profile Header Banner -->
      <CustomerProfileHeader
        :avatar-preview="avatarPreview"
        :initials="initials"
        @pick-avatar="openAvatarPicker"
        @avatar-error="avatarPreview = null"
      />
      <input ref="avatarInput" type="file" accept="image/*" @change="handleFile" class="hidden" />

      <div class="grid gap-4 sm:gap-6 lg:grid-cols-[1.1fr_0.9fr]">
        <!-- Personal Info Form -->
        <CustomerProfileFormCard
          :form="form"
          :avatar-preview="avatarPreview"
          :saving="saving"
          :status="status"
          :is-email-verified="isEmailVerified"
          :can-change-username="canChangeUsername"
          :can-change-email="canChangeEmail"
          :username-change-countdown="usernameChangeCountdown"
          @submit="submit"
          @pick-avatar="openAvatarPicker"
          @remove-avatar="removeAvatar"
          @avatar-error="avatarPreview = null"
          @update:field="updateFormField"
        />

        <!-- Right Column: Verification, Privacy & Activity -->
        <div class="space-y-4 sm:space-y-6">
          <EmailVerificationCard :email="form.email" />
          <UserProfileCookiePreferencesCard />
          <CustomerProfileActivityCard
            :is-dark="isDark"
            :latest-bookings="history.latest_bookings || []"
          />
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
import EmailVerificationCard from '@/shared/components/EmailVerificationCard.vue';
import UserProfileCookiePreferencesCard from '@/shared/components/UserProfileCookiePreferencesCard.vue';
import CustomerProfileHeader from '../components/profile/CustomerProfileHeader.vue';
import CustomerProfileFormCard from '../components/profile/CustomerProfileFormCard.vue';
import CustomerProfileActivityCard from '../components/profile/CustomerProfileActivityCard.vue';
import { useTheme } from '@/core/composables/useTheme';

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

const openAvatarPicker = () => {
  avatarInput.value?.click();
};

const updateFormField = ({ key, value }) => {
  if (key in form) {
    form[key] = value;
  }
};

const isEmailVerified = computed(() => {
  return !!authStore.user?.is_email_verified || !!authStore.user?.email_verified_at;
});

const initials = computed(() => {
  const name = form.name || form.username || form.email || '?';
  return name.split(' ').map((w) => w[0]).join('').toUpperCase().slice(0, 2);
});

const usernameChangeCountdown = computed(() => {
  if (!originalProfile.value.last_username_change) return 0;
  const targetDate = new Date(originalProfile.value.last_username_change);
  targetDate.setDate(targetDate.getDate() + 30);
  const diffDays = Math.ceil((targetDate.getTime() - Date.now()) / (1000 * 60 * 60 * 24));
  return diffDays > 0 ? diffDays : 0;
});

const canChangeUsername = computed(() => {
  if (!originalProfile.value.username || !originalProfile.value.last_username_change) return true;
  return usernameChangeCountdown.value === 0;
});

const canChangeEmail = computed(() => {
  if (!originalProfile.value.email || !originalProfile.value.last_email_change) return true;
  const targetDate = new Date(originalProfile.value.last_email_change);
  targetDate.setDate(targetDate.getDate() + 30);
  return new Date() > targetDate;
});

function extractApiError(error, fallback) {
  return (
    error.response?.data?.message ||
    error.response?.data?.error?.message ||
    (typeof error.response?.data?.error === 'string' ? error.response?.data?.error : null) ||
    fallback
  );
}

async function handleFile(event) {
  const file = event.target.files?.[0];
  if (!file) return;
  if (file.size > 5 * 1024 * 1024) {
    toast.error('Image must be under 5MB');
    return;
  }

  removeAvatarRequested.value = false;
  form.avatar = file;
  const reader = new FileReader();
  reader.onload = (e) => {
    avatarPreview.value = e.target?.result || null;
  };
  reader.readAsDataURL(file);

  const uploadData = new FormData();
  uploadData.append('avatar', file);
  saving.value = true;
  try {
    const res = await client.post('/customer/avatar', uploadData);
    const updated = res.data?.data?.profile?.data ?? res.data?.data?.user ?? res.data?.data ?? {};
    const newAvatar = updated.avatar_url || updated.avatar;
    if (newAvatar) avatarPreview.value = getStorageUrl(newAvatar);
    form.avatar = null;
    await authStore.fetchUser();
    toast.success('Profile picture saved successfully');
  } catch (error) {
    toast.error(extractApiError(error, 'Failed to upload profile picture'));
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
    toast.error(extractApiError(error, 'Failed to remove profile picture'));
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
  if (removeAvatarRequested.value) payload.append('remove_avatar', '1');
  else if (form.avatar instanceof File) payload.append('avatar', form.avatar);

  try {
    const response = await client.post('/customer/profile', payload);
    status.value = response.data?.message || 'Profile updated successfully!';
    toast.success('Profile updated');
    form.avatar = null;
    removeAvatarRequested.value = false;
    await loadProfile();
    await authStore.fetchUser();
  } catch (error) {
    toast.error(extractApiError(error, 'Failed to update profile'));
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
    avatarPreview.value = avatarUrl ? getStorageUrl(avatarUrl) : null;
  } catch (err) {
    console.error('Failed to load profile:', err);
  }
}

onMounted(() => {
  loadProfile();
});
</script>
