<template>
  <div class="space-y-8 animate-fade-in pb-12">
    <!-- Header Banner -->
    <div class="relative overflow-hidden rounded-3xl border border-admin/20 bg-gradient-to-br from-obsidian via-charcoal to-[#2A1100] p-8 lg:p-12 shadow-2xl">
      <div class="absolute -right-16 -top-16 h-64 w-64 rounded-full bg-admin/10 blur-3xl"></div>
      <div class="absolute -left-16 -bottom-16 h-48 w-48 rounded-full bg-admin/5 blur-3xl"></div>
      
      <div class="relative z-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
        <div>
          <p class="text-xs uppercase tracking-[0.3em] text-admin/80 font-bold flex items-center gap-2">
            <PhotoIcon class="w-4 h-4" /> Gallery Page Content
          </p>
          <h1 class="mt-2 font-display text-4xl lg:text-5xl text-theme-text drop-shadow-lg">
            <span class="text-transparent bg-clip-text bg-gradient-to-r from-admin to-admin-light">Gallery</span>
          </h1>
          <p class="mt-3 text-sm lg:text-base text-ivory/60 max-w-xl">Manage the header and layout of your public Gallery page.</p>
        </div>
        <button 
          @click="saveContent" 
          :disabled="saving" 
          class="flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-admin to-admin-light px-6 py-3 text-sm font-bold text-obsidian transition-all hover:shadow-[0_4px_25px_rgba(255,103,0,0.4)] active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed"
        >
          <span v-if="saving" class="h-4 w-4 animate-spin rounded-full border-2 border-obsidian border-t-transparent"></span>
          <CheckIcon v-else class="h-5 w-5" />
          {{ saving ? 'Saving...' : 'Save Changes' }}
        </button>
      </div>
    </div>

    <!-- Content Editor -->
    <div class="space-y-6">
      <!-- Page Header -->
      <div class="rounded-2xl border border-admin/10 bg-black/30 backdrop-blur-md overflow-hidden shadow-xl">
        <div class="border-b border-white/5 bg-gradient-to-r from-admin/10 to-transparent px-8 py-6">
          <h2 class="font-display text-xl text-theme-text">Page Header</h2>
          <p class="mt-1 text-xs text-ivory/40">The title and subtitle shown at the top of the Gallery page.</p>
        </div>
        <div class="p-8 space-y-6">
          <div>
            <label class="block text-xs uppercase tracking-widest text-ivory/50 font-bold mb-2">Page Title</label>
            <input v-model="content.gallery_title" type="text" placeholder="Our Gallery" 
              class="w-full rounded-xl border border-white/10 bg-white/5 px-5 py-3.5 text-theme-text placeholder-ivory/30 focus:border-admin/50 focus:ring-2 focus:ring-admin/20 focus:outline-none transition-all" />
          </div>
          <div>
            <label class="block text-xs uppercase tracking-widest text-ivory/50 font-bold mb-2">Page Subtitle</label>
            <textarea v-model="content.gallery_subtitle" rows="3" placeholder="Browse our portfolio of premium cuts and styles..."
              class="w-full rounded-xl border border-white/10 bg-white/5 px-5 py-3.5 text-theme-text placeholder-ivory/30 focus:border-admin/50 focus:ring-2 focus:ring-admin/20 focus:outline-none transition-all resize-none"></textarea>
          </div>
        </div>
      </div>

      <!-- Info Banner -->
      <div class="rounded-2xl border border-white/5 bg-black/20 p-8 flex items-center gap-4">
        <InformationCircleIcon class="w-6 h-6 text-admin/60 shrink-0" />
        <p class="text-sm text-ivory/50">Gallery images are managed from the <RouterLink to="/admin/gallery" class="text-admin hover:text-admin-light underline transition-colors">Gallery Management</RouterLink> page.</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useToast } from '../../../../core/composables/useToast';
import { adminApi } from '../../api/admin.api';
import { PhotoIcon, CheckIcon, InformationCircleIcon } from '@heroicons/vue/24/outline';

const toast = useToast();
const saving = ref(false);

const content = ref({
  gallery_title: 'Our Gallery',
  gallery_subtitle: 'Browse our portfolio of premium cuts, styles, and grooming transformations.'
});

const fetchContent = async () => {
  try {
    const response = await adminApi.settings();
    const data = response.data.data;
    if (data) {
      if (data.gallery_title) content.value.gallery_title = data.gallery_title;
      if (data.gallery_subtitle) content.value.gallery_subtitle = data.gallery_subtitle;
    }
  } catch (error) {
    console.error('Failed to load gallery content:', error);
  }
};

const saveContent = async () => {
  saving.value = true;
  try {
    const formData = new FormData();
    formData.append('settings[gallery_title]', content.value.gallery_title);
    formData.append('settings[gallery_subtitle]', content.value.gallery_subtitle);
    await adminApi.updateSettings(formData);
    toast.success('Gallery page content saved!');
  } catch (error) {
    toast.error('Failed to save changes.');
  } finally {
    saving.value = false;
  }
};

onMounted(() => {
  fetchContent();
});
</script>
