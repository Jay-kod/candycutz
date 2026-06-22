<template>
  <AdminLayout>
    <section class="space-y-8">
      <div class="rounded-3xl border border-theme-border bg-theme-surface p-8">
        <p class="text-sm uppercase tracking-[0.3em] text-theme-muted">Site Configuration</p>
        <h1 class="mt-3 font-display text-4xl text-admin">Settings</h1>
      </div>

      <div class="rounded-3xl border border-theme-border bg-theme-surface p-8">
        <h2 class="font-display text-2xl text-admin mb-6">Hero Section</h2>
        <form @submit.prevent="saveSettings" class="space-y-6">
          
          <div>
            <label class="block text-sm font-medium text-theme-muted mb-2">Hero Title</label>
            <input v-model="settings.hero_title" type="text" class="w-full rounded-xl border border-theme-border bg-black/20 p-4 text-theme-text focus:border-admin focus:outline-none focus:ring-1 focus:ring-admin" required />
          </div>

          <div>
            <label class="block text-sm font-medium text-theme-muted mb-2">Hero Subtitle</label>
            <textarea v-model="settings.hero_subtitle" rows="3" class="w-full rounded-xl border border-theme-border bg-black/20 p-4 text-theme-text focus:border-admin focus:outline-none focus:ring-1 focus:ring-admin" required></textarea>
          </div>

          <div>
            <label class="block text-sm font-medium text-theme-muted mb-2">Hero Background Image</label>
            <div v-if="currentHeroImage" class="mb-4">
              <img :src="getStorageUrl(currentHeroImage)" alt="Current Hero Image" class="h-48 w-auto object-cover rounded-xl border border-theme-border" />
            </div>
            <input type="file" @change="handleImageUpload" accept="image/jpeg,image/png,image/webp" class="w-full text-sm text-theme-muted file:mr-4 file:rounded-full file:border-0 file:bg-admin file:px-4 file:py-2 file:text-sm file:font-semibold file:text-obsidian hover:file:bg-admin-light" />
            <p class="mt-2 text-xs text-white/50">Recommended: 1920x1080 (Max 2MB, JPG/PNG/WEBP)</p>
          </div>

          <div class="pt-4 border-t border-theme-border flex justify-end">
            <button type="submit" :disabled="saving" class="rounded-full bg-admin px-8 py-3 font-bold text-obsidian shadow-[0_0_15px_rgba(255,103,0,0.3)] transition hover:scale-105 hover:bg-admin-light disabled:opacity-50 disabled:hover:scale-100">
              {{ saving ? 'Saving...' : 'Save Settings' }}
            </button>
          </div>
        </form>
      </div>
    </section>
  </AdminLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useToast } from '../../../core/composables/useToast';
import AdminLayout from '../layouts/AdminLayout.vue';
import { adminApi } from '../api/admin.api';

const toast = useToast();
const saving = ref(false);
const settings = ref({
  hero_title: '',
  hero_subtitle: ''
});
const currentHeroImage = ref(null);
const heroImageFile = ref(null);

const getStorageUrl = (path) => {
  if (!path) return '';
  return `${import.meta.env.VITE_API_BASE_URL.replace('/api', '')}/storage/${path}`;
};

const fetchSettings = async () => {
  try {
    const response = await adminApi.settings();
    const data = response.data.data;
    if (data) {
      settings.value.hero_title = data.hero_title || '';
      settings.value.hero_subtitle = data.hero_subtitle || '';
      currentHeroImage.value = data.hero_image || null;
    }
  } catch (error) {
    console.error('Failed to load settings:', error);
  }
};

const handleImageUpload = (event) => {
  const file = event.target.files[0];
  if (file) {
    if (file.size > 2 * 1024 * 1024) {
      toast.error('Image must be less than 2MB');
      event.target.value = '';
      return;
    }
    heroImageFile.value = file;
  }
};

const saveSettings = async () => {
  saving.value = true;
  try {
    const formData = new FormData();
    formData.append('settings[hero_title]', settings.value.hero_title);
    formData.append('settings[hero_subtitle]', settings.value.hero_subtitle);
    
    if (heroImageFile.value) {
      formData.append('hero_image', heroImageFile.value);
    }

    const response = await adminApi.updateSettings(formData);
    toast.success('Settings updated successfully');
    
    // Update local state with new image path
    const updatedData = response.data.data;
    if (updatedData) {
      currentHeroImage.value = updatedData.hero_image || currentHeroImage.value;
      heroImageFile.value = null; // reset file input conceptually
    }
  } catch (error) {
    console.error('Error saving settings:', error);
  } finally {
    saving.value = false;
  }
};

onMounted(() => {
  fetchSettings();
});
</script>
