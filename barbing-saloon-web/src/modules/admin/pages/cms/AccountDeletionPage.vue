<template>
  <div class="space-y-8 animate-fade-in pb-12">
    <!-- Header Banner -->
    <div class="relative overflow-hidden rounded-3xl border border-admin/20 bg-gradient-to-br from-obsidian via-charcoal to-[#2A1100] p-8 lg:p-12 shadow-2xl">
      <div class="absolute -right-16 -top-16 h-64 w-64 rounded-full bg-admin/10 blur-3xl"></div>
      <div class="absolute -left-16 -bottom-16 h-48 w-48 rounded-full bg-admin/5 blur-3xl"></div>
      
      <div class="relative z-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
        <div>
          <p class="text-xs uppercase tracking-[0.3em] text-admin/80 font-bold flex items-center gap-2">
            <TrashIcon class="w-4 h-4" /> Compliance & Account Control
          </p>
          <h1 class="mt-2 font-display text-4xl lg:text-5xl text-theme-text drop-shadow-lg">
            <span class="text-transparent bg-clip-text bg-gradient-to-r from-admin to-admin-light">Account Deletion</span>
          </h1>
          <p class="mt-3 text-sm lg:text-base text-ivory/60 max-w-xl">Configure instructions and policies for Google Play / App Store account deletion requirements.</p>
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
      <div class="rounded-2xl border border-admin/10 bg-black/30 backdrop-blur-md overflow-hidden shadow-xl">
        <div class="border-b border-white/5 bg-gradient-to-r from-admin/10 to-transparent px-8 py-6">
          <h2 class="font-display text-xl text-theme-text">Page Header</h2>
          <p class="mt-1 text-xs text-ivory/40">Title and description displayed on the public account deletion request page.</p>
        </div>
        <div class="p-8 space-y-6">
          <div>
            <label class="block text-xs uppercase tracking-widest text-ivory/50 font-bold mb-2">Page Title</label>
            <input v-model="content.account_deletion_title" type="text" placeholder="Delete Your CandyCutz Account" 
              class="w-full rounded-xl border border-white/10 bg-white/5 px-5 py-3.5 text-theme-text placeholder-ivory/30 focus:border-admin/50 focus:ring-2 focus:ring-admin/20 focus:outline-none transition-all" />
          </div>
          <div>
            <label class="block text-xs uppercase tracking-widest text-ivory/50 font-bold mb-2">Instructions & Notice</label>
            <textarea v-model="content.account_deletion_body" rows="6" placeholder="Instructions explaining how accounts are deleted and what data is removed..."
              class="w-full rounded-xl border border-white/10 bg-white/5 px-5 py-3.5 text-theme-text placeholder-ivory/30 focus:border-admin/50 focus:ring-2 focus:ring-admin/20 focus:outline-none transition-all resize-y"></textarea>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useToast } from '../../../../core/composables/useToast';
import { adminApi } from '@/shared/api/old_adminApi';
import { TrashIcon, CheckIcon } from '@heroicons/vue/24/outline';

const toast = useToast();
const saving = ref(false);

const content = ref({
  account_deletion_title: 'Delete Your CandyCutz Account',
  account_deletion_body: 'If you wish to permanently delete your CandyCutz account and all associated personal data, bookings history, and saved preferences, you may submit a deletion request. This action is irreversible.'
});

const fetchContent = async () => {
  try {
    const response = await adminApi.settings();
    const data = response.data?.data;
    if (data) {
      if (data.account_deletion_title) content.value.account_deletion_title = data.account_deletion_title;
      if (data.account_deletion_body) content.value.account_deletion_body = data.account_deletion_body;
    }
  } catch (error) {
    console.error('Failed to load account deletion content:', error);
  }
};

const saveContent = async () => {
  saving.value = true;
  try {
    const formData = new FormData();
    formData.append('settings[account_deletion_title]', content.value.account_deletion_title);
    formData.append('settings[account_deletion_body]', content.value.account_deletion_body);
    await adminApi.updateSettings(formData);
    toast.success('Account deletion page content saved!');
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
