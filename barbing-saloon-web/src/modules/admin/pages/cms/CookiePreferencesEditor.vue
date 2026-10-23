<template>
  <div class="space-y-6 animate-fade-in pb-12">
    <header class="flex flex-col gap-4 rounded-3xl border border-admin/20 bg-gradient-to-br from-obsidian via-charcoal to-[#2A1100] p-6 shadow-2xl sm:flex-row sm:items-center sm:justify-between sm:p-8">
      <div>
        <p class="flex items-center gap-2 text-xs font-bold uppercase tracking-[0.25em] text-admin/80">
          <Cog6ToothIcon class="size-4" /> Website CMS
        </p>
        <h1 class="mt-2 font-display text-3xl text-theme-text sm:text-4xl">Manage Cookies</h1>
        <p class="mt-2 max-w-2xl text-sm text-ivory/60">Edit the cookie-preferences page text and category descriptions shown to visitors.</p>
      </div>
      <button
        type="button"
        :disabled="saving"
        class="inline-flex min-h-11 shrink-0 items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-admin to-admin-light px-5 py-3 text-sm font-bold text-obsidian transition hover:shadow-lg disabled:cursor-not-allowed disabled:opacity-50"
        @click="saveContent"
      >
        <span v-if="saving" class="size-4 animate-spin rounded-full border-2 border-obsidian border-t-transparent"></span>
        <CheckIcon v-else class="size-5" />
        {{ saving ? 'Saving...' : 'Save Changes' }}
      </button>
    </header>

    <section class="rounded-2xl border border-admin/10 bg-black/30 shadow-xl">
      <div class="border-b border-white/5 px-5 py-4 sm:px-7">
        <h2 class="font-display text-lg text-theme-text">Page Content</h2>
        <p class="mt-1 text-xs text-ivory/40">These settings update the public Manage Cookies page.</p>
      </div>
      <div class="space-y-5 p-5 sm:p-7">
        <div>
          <label for="cookie-title" class="mb-2 block text-xs font-bold uppercase tracking-widest text-ivory/50">Page Title</label>
          <input id="cookie-title" v-model="content.cookie_title" maxlength="150" class="editor-input" />
        </div>
        <div>
          <label for="cookie-intro" class="mb-2 block text-xs font-bold uppercase tracking-widest text-ivory/50">Introduction</label>
          <textarea id="cookie-intro" v-model="content.cookie_intro" rows="3" maxlength="2000" class="editor-input resize-y"></textarea>
        </div>
      </div>
    </section>

    <section
      v-for="category in categories"
      :key="category.key"
      class="rounded-2xl border border-admin/10 bg-black/30 shadow-xl"
    >
      <div class="border-b border-white/5 px-5 py-4 sm:px-7">
        <h2 class="font-display text-lg text-theme-text">{{ category.label }}</h2>
        <p class="mt-1 text-xs text-ivory/40">{{ category.required ? 'Always enabled and cannot be switched off.' : 'Optional preference that visitors can enable or disable.' }}</p>
      </div>
      <div class="space-y-5 p-5 sm:p-7">
        <div>
          <label :for="`${category.key}-title`" class="mb-2 block text-xs font-bold uppercase tracking-widest text-ivory/50">Display Name</label>
          <input :id="`${category.key}-title`" v-model="content[category.titleKey]" maxlength="120" class="editor-input" />
        </div>
        <div>
          <label :for="`${category.key}-description`" class="mb-2 block text-xs font-bold uppercase tracking-widest text-ivory/50">Description</label>
          <textarea :id="`${category.key}-description`" v-model="content[category.descriptionKey]" rows="3" maxlength="1000" class="editor-input resize-y"></textarea>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue';
import { CheckIcon, Cog6ToothIcon } from '@heroicons/vue/24/outline';
import { useToast } from '../../../../core/composables/useToast';
import { adminApi } from '@/shared/api/old_adminApi';

const toast = useToast();
const saving = ref(false);
const content = reactive({
  cookie_title: 'Manage Cookies',
  cookie_intro: 'Choose which optional cookie categories you want to allow. Necessary storage is always active so the site can provide core features.',
  cookie_necessary_title: 'Necessary',
  cookie_necessary_description: 'Required for core site functions such as keeping your session secure. These cannot be switched off.',
  cookie_preferences_title: 'Preferences',
  cookie_preferences_description: 'Remember choices that make your experience more convenient.',
  cookie_analytics_title: 'Analytics',
  cookie_analytics_description: 'Allow analytics tools to help understand how the site is used, if enabled.',
  cookie_marketing_title: 'Marketing',
  cookie_marketing_description: 'Allow marketing-related cookies or similar technologies, if enabled.',
});

const categories = [
  { key: 'necessary', label: 'Necessary', titleKey: 'cookie_necessary_title', descriptionKey: 'cookie_necessary_description', required: true },
  { key: 'preferences', label: 'Preferences', titleKey: 'cookie_preferences_title', descriptionKey: 'cookie_preferences_description', required: false },
  { key: 'analytics', label: 'Analytics', titleKey: 'cookie_analytics_title', descriptionKey: 'cookie_analytics_description', required: false },
  { key: 'marketing', label: 'Marketing', titleKey: 'cookie_marketing_title', descriptionKey: 'cookie_marketing_description', required: false },
];

const fetchContent = async () => {
  try {
    const response = await adminApi.settings();
    const data = response.data?.data;
    if (!data) return;
    for (const key of Object.keys(content)) {
      if (typeof data[key] === 'string' && data[key].trim()) content[key] = data[key];
    }
  } catch (error) {
    console.error('Failed to load cookie preferences content:', error);
    toast.error('Could not load cookie preferences content.');
  }
};

const saveContent = async () => {
  if (!content.cookie_title.trim() || !content.cookie_intro.trim()) {
    toast.error('Add a page title and introduction before saving.');
    return;
  }

  saving.value = true;
  try {
    const formData = new FormData();
    for (const [key, value] of Object.entries(content)) {
      formData.append(`settings[${key}]`, value.trim());
    }
    await adminApi.updateSettings(formData);
    toast.success('Cookie preferences page saved.');
  } catch (error) {
    console.error('Failed to save cookie preferences content:', error);
    toast.error('Could not save cookie preferences changes.');
  } finally {
    saving.value = false;
  }
};

onMounted(fetchContent);
</script>

<style scoped>
.editor-input {
  width: 100%;
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 0.75rem;
  background: rgba(255, 255, 255, 0.05);
  padding: 0.75rem 1rem;
  color: var(--color-text);
}

.editor-input:focus {
  border-color: rgba(255, 103, 0, 0.5);
  outline: none;
  box-shadow: 0 0 0 2px rgba(255, 103, 0, 0.2);
}
</style>
