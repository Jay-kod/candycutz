<template>
  <component :is="layout">
    <section class="space-y-8 pb-16 animate-fade-in">
      <!-- Header Banner -->
      <div class="relative overflow-hidden rounded-3xl border border-admin/20 bg-gradient-to-br from-obsidian via-charcoal to-[#2A1100] p-8 lg:p-12 shadow-2xl">
        <div class="absolute -right-16 -top-16 h-64 w-64 rounded-full bg-admin/10 blur-3xl"></div>
        <div class="absolute -left-16 -bottom-16 h-48 w-48 rounded-full bg-admin/5 blur-3xl"></div>

        <div class="relative z-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
          <div>
            <p class="text-xs uppercase tracking-[0.3em] text-admin font-bold flex items-center gap-2">
              <DevicePhoneMobileIcon class="w-4 h-4" /> Mobile App & System CMS
            </p>
            <h1 class="mt-2 font-display text-4xl lg:text-5xl text-theme-text drop-shadow-lg">
              <span class="text-transparent bg-clip-text bg-gradient-to-r from-admin to-admin-light">System & App Configuration</span>
            </h1>
            <p class="mt-3 text-sm lg:text-base text-ivory/60 max-w-2xl">
              Customize mobile app branding backgrounds (Flash screen, Onboarding, Login) and adjust system configurations across the platform.
            </p>
          </div>

          <div class="flex items-center gap-3">
            <button
              @click="saveAll"
              :disabled="saving"
              class="flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-admin to-admin-light px-7 py-3 text-sm font-bold text-obsidian transition-all hover:shadow-[0_4px_25px_rgba(255,103,0,0.4)] active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <span v-if="saving" class="h-4 w-4 animate-spin rounded-full border-2 border-obsidian border-t-transparent"></span>
              <CheckIcon v-else class="h-5 w-5" />
              {{ saving ? 'Saving Changes...' : 'Save Changes' }}
            </button>
          </div>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="flex justify-center py-32">
        <div class="h-10 w-10 animate-spin rounded-full border-4 border-admin/30 border-t-admin"></div>
      </div>

      <div v-else class="space-y-8 animate-slide-up">
        <!-- 1. Mobile App CMS Visual Studio -->
        <div class="rounded-3xl border border-admin/20 bg-theme-surface/90 backdrop-blur-md overflow-hidden shadow-xl">
          <div class="border-b border-white/5 bg-gradient-to-r from-admin/10 to-transparent px-8 py-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
              <div class="h-12 w-12 rounded-2xl bg-admin/10 border border-admin/20 flex items-center justify-center shadow-[0_0_15px_rgba(255,103,0,0.1)]">
                <DevicePhoneMobileIcon class="h-6 w-6 text-admin" />
              </div>
              <div>
                <h2 class="font-display text-2xl text-theme-text">Mobile App CMS Studio</h2>
                <p class="text-sm text-ivory/50 mt-1">Set classic background images for the 3 key entrance screens of the CandyCutz mobile app.</p>
              </div>
            </div>

            <!-- Screen Selector Tabs -->
            <div class="flex items-center rounded-2xl bg-black/40 p-1 border border-white/10">
              <button
                v-for="screen in mobileScreens"
                :key="screen.id"
                @click="activeScreenId = screen.id"
                :class="[
                  'px-4 py-2 text-xs font-bold rounded-xl transition-all',
                  activeScreenId === screen.id
                    ? 'bg-gradient-to-r from-admin to-admin-light text-obsidian shadow-md'
                    : 'text-ivory/60 hover:text-ivory'
                ]"
              >
                {{ screen.title }}
              </button>
            </div>
          </div>

          <!-- Active Screen Studio Layout -->
          <div class="p-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
              <!-- Left: Phone Mockup Preview (5 Cols) -->
              <div class="lg:col-span-5 flex flex-col items-center">
                <p class="text-xs uppercase tracking-wider text-admin/80 font-bold mb-4 flex items-center gap-2">
                  <SparklesIcon class="w-4 h-4" /> Live Mobile Mockup Preview
                </p>

                <!-- Phone Frame Container -->
                <div class="relative w-[280px] h-[560px] rounded-[44px] p-3 bg-[#18181B] border-[6px] border-[#27272A] shadow-[0_25px_60px_-15px_rgba(0,0,0,0.8),0_0_20px_rgba(255,103,0,0.15)] overflow-hidden">
                  <!-- Phone Notch / Dynamic Island -->
                  <div class="absolute top-4 left-1/2 -translate-x-1/2 w-24 h-5 bg-black rounded-full z-30"></div>

                  <!-- Phone Screen Inner -->
                  <div class="relative w-full h-full rounded-[34px] overflow-hidden bg-obsidian flex flex-col">
                    <!-- Background Image Layer -->
                    <img
                      :src="activeScreenPreview || activeScreenFallback"
                      alt="Screen background"
                      class="absolute inset-0 w-full h-full object-cover transition-opacity duration-300"
                    />

                    <!-- Obsidian Vignette Overlay (matches mobile app) -->
                    <div class="absolute inset-0 bg-gradient-to-b from-black/70 via-black/40 to-black/90 pointer-events-none"></div>

                    <!-- Screen Specific Mockup Overlay Elements -->
                    <!-- 1. Flash / Splash Screen -->
                    <div v-if="activeScreenId === 'splash'" class="relative z-20 flex-1 flex flex-col items-center justify-center p-6 text-center">
                      <div class="relative flex items-center justify-center">
                        <div class="absolute w-28 h-28 rounded-full bg-gold/20 blur-xl animate-pulse"></div>
                        <img src="/favicon.png" alt="CandyCutz Logo" class="relative w-20 h-20 rounded-full shadow-2xl border-2 border-gold/40" />
                      </div>
                      <p class="mt-6 font-display text-sm tracking-[0.2em] text-[#E5BA73] font-light">Fresh cuts, clean vibes…</p>
                    </div>

                    <!-- 2. Onboarding Screen -->
                    <div v-else-if="activeScreenId === 'onboarding'" class="relative z-20 flex-1 flex flex-col justify-between p-6">
                      <div class="flex justify-end pt-6">
                        <span class="text-[10px] px-3 py-1 rounded-full bg-black/50 border border-white/20 text-ivory/80 font-bold">Skip</span>
                      </div>
                      <div class="space-y-4 pb-4">
                        <h3 class="font-display text-lg text-white font-bold leading-tight">The Professional Specialists in near by</h3>
                        <p class="text-[11px] text-ivory/70 leading-relaxed">Book elite barbering and precision grooming right from your pocket.</p>
                        <div class="flex items-center justify-between pt-2">
                          <div class="flex gap-1.5">
                            <span class="w-6 h-1.5 rounded-full bg-[#E5BA73]"></span>
                            <span class="w-1.5 h-1.5 rounded-full bg-white/30"></span>
                            <span class="w-1.5 h-1.5 rounded-full bg-white/30"></span>
                          </div>
                          <div class="w-9 h-9 rounded-full bg-[#E5BA73] flex items-center justify-center shadow-lg text-obsidian font-black text-sm">
                            →
                          </div>
                        </div>
                      </div>
                    </div>

                    <!-- 3. Login Screen -->
                    <div v-else-if="activeScreenId === 'login'" class="relative z-20 flex-1 flex flex-col justify-end p-5">
                      <div class="rounded-2xl border border-white/10 bg-black/70 backdrop-blur-md p-4 space-y-3 shadow-2xl">
                        <div class="flex items-center gap-2">
                          <img src="/favicon.png" alt="CandyCutz" class="w-6 h-6 rounded-full" />
                          <span class="text-xs font-display text-gold font-bold">CandyCutz</span>
                        </div>
                        <p class="text-xs font-bold text-white">Sign in to continue</p>
                        <div class="h-7 rounded-lg bg-white/10 border border-white/10 px-2 flex items-center text-[10px] text-ivory/50">
                          you@example.com
                        </div>
                        <div class="h-7 rounded-lg bg-white/10 border border-white/10 px-2 flex items-center text-[10px] text-ivory/50">
                          ••••••••••••
                        </div>
                        <div class="h-7 rounded-lg bg-gradient-to-r from-admin to-admin-light flex items-center justify-center text-[11px] font-bold text-obsidian shadow-md">
                          Sign In
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Right: Screen Upload & Controls (7 Cols) -->
              <div class="lg:col-span-7 space-y-6">
                <div class="rounded-2xl border border-white/10 bg-black/40 p-6 space-y-4">
                  <div class="flex items-center justify-between">
                    <div>
                      <h3 class="font-display text-xl text-theme-text">{{ currentActiveScreen.title }}</h3>
                      <p class="text-sm text-ivory/60 mt-1">{{ currentActiveScreen.description }}</p>
                    </div>
                    <span
                      :class="[
                        'px-3 py-1 rounded-full text-xs font-bold border',
                        hasCustomImage(currentActiveScreen.id)
                          ? 'bg-admin/10 border-admin/30 text-admin'
                          : 'bg-white/5 border-white/10 text-ivory/60'
                      ]"
                    >
                      {{ hasCustomImage(currentActiveScreen.id) ? 'Custom Image Active' : 'Default Asset' }}
                    </span>
                  </div>

                  <!-- Upload Controls -->
                  <div class="pt-4 border-t border-white/10 space-y-4">
                    <label class="block text-sm font-bold text-ivory/80">Upload New Background Image</label>
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-4">
                      <input
                        type="file"
                        accept="image/jpeg,image/png,image/webp"
                        :ref="(el) => { if (el) fileInputs[currentActiveScreen.id] = el; }"
                        @change="(e) => handleFileSelected(e, currentActiveScreen.id)"
                        class="hidden"
                      />

                      <button
                        type="button"
                        @click="triggerFileInput(currentActiveScreen.id)"
                        class="flex items-center justify-center gap-2 rounded-xl border border-admin/30 bg-admin/10 px-5 py-3 text-sm font-bold text-admin transition-all hover:bg-admin/20 hover:border-admin/50"
                      >
                        <PhotoIcon class="w-5 h-5" />
                        Choose Image File (JPG/PNG/WEBP)
                      </button>

                      <button
                        v-if="hasCustomImage(currentActiveScreen.id) || pendingFiles[currentActiveScreen.id]"
                        type="button"
                        @click="resetScreenImage(currentActiveScreen.id)"
                        class="flex items-center justify-center gap-2 rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm font-bold text-red-400 hover:bg-red-500/20"
                      >
                        <TrashIcon class="w-4 h-4" />
                        Reset to Default
                      </button>
                    </div>

                    <p class="text-xs text-ivory/40">
                      Recommended resolution: 1080 × 2400 (9:19.5 aspect ratio). Maximum file size: 4MB.
                    </p>

                    <!-- Pending File Notice -->
                    <div v-if="pendingFiles[currentActiveScreen.id]" class="rounded-xl border border-admin/30 bg-admin/5 p-3 flex items-center justify-between text-xs text-admin">
                      <span>Selected file: <strong>{{ pendingFiles[currentActiveScreen.id].name }}</strong> (Pending Save)</span>
                      <button @click="clearPendingFile(currentActiveScreen.id)" class="text-ivory/60 hover:text-white underline">Cancel</button>
                    </div>
                  </div>
                </div>

                <!-- Quick Tips Card -->
                <div class="rounded-2xl border border-white/5 bg-black/20 p-6 space-y-3">
                  <h4 class="text-xs uppercase tracking-wider text-admin font-bold flex items-center gap-2">
                    <SparklesIcon class="w-4 h-4" /> CMS Aesthetic Guidelines
                  </h4>
                  <ul class="text-xs text-ivory/60 space-y-2 list-disc list-inside leading-relaxed">
                    <li>Choose moody, high-contrast barbershop photographs with warm ambient lighting for best results.</li>
                    <li>The mobile app automatically layers an obsidian vignette gradient over backgrounds to ensure text and buttons remain crystal clear.</li>
                    <li>Uploaded images are automatically optimized and cached on customer devices for instantaneous offline loading.</li>
                  </ul>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- 2. System Configuration Editor -->
        <div class="rounded-3xl border border-theme-border bg-theme-surface p-8 shadow-xl">
          <div class="flex items-center justify-between pb-6 border-b border-theme-border mb-6">
            <div>
              <p class="text-xs uppercase tracking-[0.3em] text-theme-muted font-bold">General & Business</p>
              <h2 class="font-display text-2xl text-gold mt-1">System Configuration</h2>
            </div>
            <button
              @click="saveAll"
              :disabled="saving"
              class="flex items-center gap-2 rounded-xl bg-gold px-5 py-2.5 text-xs font-bold text-obsidian transition-all hover:bg-gold-light disabled:opacity-50"
            >
              {{ saving ? 'Saving...' : 'Save Configuration' }}
            </button>
          </div>

          <div class="grid gap-6 lg:grid-cols-2">
            <article
              v-for="(groupSettings, groupName) in systemSettingsGroups"
              :key="groupName"
              class="rounded-2xl border border-theme-border bg-black/20 p-6 space-y-4"
            >
              <h3 class="font-display text-xl text-gold capitalize">{{ groupName }} Settings</h3>
              <div class="space-y-3">
                <div v-for="(val, key) in groupSettings" :key="key" class="space-y-1">
                  <label class="text-xs font-semibold text-theme-muted uppercase tracking-wider">{{ key }}</label>
                  <input
                    type="text"
                    v-model="groupSettings[key]"
                    class="w-full rounded-xl border border-theme-border bg-theme-surface px-4 py-2.5 text-sm text-theme-text focus:border-gold focus:outline-none transition-colors"
                  />
                </div>
              </div>
            </article>
          </div>
        </div>
      </div>
    </section>
  </component>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import { useRoute } from 'vue-router';
import {
  CheckIcon,
  DevicePhoneMobileIcon,
  PhotoIcon,
  SparklesIcon,
  TrashIcon
} from '@heroicons/vue/24/outline';
import AdminLayout from '@/portals/admin/layouts/AdminLayout.vue';
import SuperAdminLayout from '@/portals/superadmin/layouts/SuperAdminLayout.vue';
import { adminApi } from '@/shared/api/old_adminApi';
import { superadminApi } from '@/shared/api/old_superadminApi';
import { getStorageUrl } from '@/core/utils/url';
import { useToast } from '@/core/composables/useToast';

const route = useRoute();
const toast = useToast();

const layout = computed(() => {
  return route.path.startsWith('/admin') ? AdminLayout : SuperAdminLayout;
});

const currentApi = computed(() => {
  return route.path.startsWith('/admin') ? adminApi : superadminApi;
});

const loading = ref(true);
const saving = ref(false);
const rawSettings = ref({});

const mobileScreens = [
  {
    id: 'splash',
    title: '1. Flash Screen',
    settingKey: 'splash_background_image',
    uploadKey: 'splash_image',
    description: 'Initial launch splash screen with animated golden halo & CandyCutz logo.',
    fallback: '/assets/images/splash-bg.jpg',
  },
  {
    id: 'onboarding',
    title: '2. Introductory Page',
    settingKey: 'onboarding_background_image',
    uploadKey: 'onboarding_image',
    description: 'Welcome and onboarding introduction slides presented on fresh app installation.',
    fallback: '/assets/images/onboarding-1.jpg',
  },
  {
    id: 'login',
    title: '3. Login Screen',
    settingKey: 'login_background_image',
    uploadKey: 'login_image',
    description: 'Classic luxury backdrop layered behind customer & barber authentication cards.',
    fallback: '/assets/images/splash-bg.jpg',
  },
];

const activeScreenId = ref('splash');
const currentActiveScreen = computed(() => {
  return mobileScreens.find((s) => s.id === activeScreenId.value) || mobileScreens[0];
});

const fileInputs = ref({});
const pendingFiles = ref({});
const pendingPreviews = ref({});
const resetFlags = ref({});

const activeScreenPreview = computed(() => {
  const current = currentActiveScreen.value;
  if (pendingPreviews.value[current.id]) {
    return pendingPreviews.value[current.id];
  }
  if (resetFlags.value[current.id]) {
    return current.fallback;
  }
  const customUrl = getMobileSettingValue(current.settingKey);
  return customUrl ? getStorageUrl(customUrl) : null;
});

const activeScreenFallback = computed(() => {
  return currentActiveScreen.value.fallback;
});

function getMobileSettingValue(key) {
  if (rawSettings.value.mobile && rawSettings.value.mobile[key]) {
    return rawSettings.value.mobile[key];
  }
  if (rawSettings.value[key]) {
    return rawSettings.value[key];
  }
  return null;
}

function hasCustomImage(screenId) {
  const screen = mobileScreens.find((s) => s.id === screenId);
  if (!screen) return false;
  if (resetFlags.value[screenId]) return false;
  return Boolean(getMobileSettingValue(screen.settingKey) || pendingFiles.value[screenId]);
}

function triggerFileInput(screenId) {
  const el = fileInputs.value[screenId];
  if (el) el.click();
}

function handleFileSelected(event, screenId) {
  const file = event.target.files?.[0];
  if (!file) return;

  if (file.size > 4 * 1024 * 1024) {
    toast.error('Image size must be less than 4MB');
    return;
  }

  pendingFiles.value[screenId] = file;
  pendingPreviews.value[screenId] = URL.createObjectURL(file);
  resetFlags.value[screenId] = false;
  toast.info('Preview updated. Click "Save Changes" to apply.');
}

function clearPendingFile(screenId) {
  delete pendingFiles.value[screenId];
  delete pendingPreviews.value[screenId];
  if (fileInputs.value[screenId]) {
    fileInputs.value[screenId].value = '';
  }
}

function resetScreenImage(screenId) {
  clearPendingFile(screenId);
  resetFlags.value[screenId] = true;
  toast.warning('Marked to reset to default asset. Click "Save Changes" to confirm.');
}

// System configuration groups (excluding mobile)
const systemSettingsGroups = computed(() => {
  const groups = {};
  for (const [group, values] of Object.entries(rawSettings.value)) {
    if (group === 'mobile') continue;
    if (typeof values === 'object' && values !== null && !Array.isArray(values)) {
      groups[group] = { ...values };
    }
  }
  return groups;
});

async function fetchSettings() {
  loading.value = true;
  try {
    const res = await currentApi.value.settings();
    const data = res.data?.data || {};
    rawSettings.value = data;
  } catch (err) {
    console.error('Failed to load settings:', err);
    toast.error('Failed to load system settings');
  } finally {
    loading.value = false;
  }
}

async function saveAll() {
  saving.value = true;
  try {
    const formData = new FormData();

    // 1. Append mobile CMS image uploads
    for (const screen of mobileScreens) {
      if (pendingFiles.value[screen.id]) {
        formData.append(screen.uploadKey, pendingFiles.value[screen.id]);
      } else if (resetFlags.value[screen.id]) {
        formData.append(`settings[${screen.settingKey}]`, '');
      }
    }

    // 2. Append general system configuration values
    for (const [groupName, groupValues] of Object.entries(systemSettingsGroups.value)) {
      for (const [key, value] of Object.entries(groupValues)) {
        formData.append(`settings[${key}]`, value ?? '');
      }
    }

    await currentApi.value.updateSettings(formData);
    toast.success('Settings and mobile CMS backgrounds saved successfully!');

    // Reset pending state & refresh
    pendingFiles.value = {};
    pendingPreviews.value = {};
    resetFlags.value = {};
    await fetchSettings();
  } catch (err) {
    console.error('Failed to save settings:', err);
    toast.error('Failed to save settings. Please try again.');
  } finally {
    saving.value = false;
  }
}

onMounted(() => {
  fetchSettings();
});
</script>

<style scoped>
.animate-fade-in {
  animation: fadeIn 0.4s ease-out forwards;
}
.animate-slide-up {
  animation: slideUp 0.5s ease-out forwards;
}
@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}
@keyframes slideUp {
  from { opacity: 0; transform: translateY(16px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>
