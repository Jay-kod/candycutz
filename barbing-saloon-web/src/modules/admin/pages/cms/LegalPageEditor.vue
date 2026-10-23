<template>
  <div class="space-y-6 animate-fade-in pb-12">
    <header class="flex flex-col gap-4 rounded-3xl border border-admin/20 bg-gradient-to-br from-obsidian via-charcoal to-[#2A1100] p-6 shadow-2xl sm:flex-row sm:items-center sm:justify-between sm:p-8">
      <div>
        <p class="flex items-center gap-2 text-xs font-bold uppercase tracking-[0.25em] text-admin/80">
          <DocumentTextIcon class="size-4" /> Website CMS
        </p>
        <h1 class="mt-2 font-display text-3xl text-theme-text sm:text-4xl">{{ pageTitle }}</h1>
        <p class="mt-2 max-w-2xl text-sm text-ivory/60">{{ description }}</p>
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
        <h2 class="font-display text-lg text-theme-text">Page Header</h2>
      </div>
      <div class="p-5 sm:p-7">
        <label class="mb-2 block text-xs font-bold uppercase tracking-widest text-ivory/50" :for="`${settingPrefix}-title`">Page Title</label>
        <input
          :id="`${settingPrefix}-title`"
          v-model="title"
          type="text"
          maxlength="150"
          class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-theme-text focus:border-admin/50 focus:outline-none focus:ring-2 focus:ring-admin/20"
        />
      </div>
    </section>

    <section
      v-for="(section, index) in sections"
      :key="index"
      class="rounded-2xl border border-admin/10 bg-black/30 shadow-xl"
    >
      <div class="flex items-center justify-between border-b border-white/5 px-5 py-4 sm:px-7">
        <h2 class="font-display text-lg text-theme-text">Section {{ index + 1 }}</h2>
        <button
          v-if="sections.length > 1"
          type="button"
          class="text-xs font-bold uppercase tracking-widest text-red-400/80 hover:text-red-300"
          @click="sections.splice(index, 1)"
        >
          Remove
        </button>
      </div>
      <div class="space-y-5 p-5 sm:p-7">
        <div>
          <label class="mb-2 block text-xs font-bold uppercase tracking-widest text-ivory/50">Section Heading</label>
          <input
            v-model="section.heading"
            type="text"
            maxlength="180"
            class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-theme-text focus:border-admin/50 focus:outline-none focus:ring-2 focus:ring-admin/20"
          />
        </div>
        <div>
          <label class="mb-2 block text-xs font-bold uppercase tracking-widest text-ivory/50">Content</label>
          <textarea
            v-model="section.body"
            rows="5"
            maxlength="12000"
            class="w-full resize-y rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-theme-text focus:border-admin/50 focus:outline-none focus:ring-2 focus:ring-admin/20"
          ></textarea>
        </div>
      </div>
    </section>

    <button
      type="button"
      class="w-full rounded-2xl border-2 border-dashed border-white/10 bg-black/10 py-4 text-sm font-semibold uppercase tracking-widest text-ivory/50 transition hover:border-admin/40 hover:text-admin"
      @click="sections.push({ heading: '', body: '' })"
    >
      + Add Section
    </button>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import { DocumentTextIcon, CheckIcon } from '@heroicons/vue/24/outline';
import { useToast } from '../../../../core/composables/useToast';
import { adminApi } from '@/shared/api/old_adminApi';

const props = defineProps({
  settingPrefix: { type: String, required: true },
  pageTitle: { type: String, required: true },
  description: { type: String, required: true },
  defaultSections: { type: Array, default: () => [] },
});

const toast = useToast();
const saving = ref(false);
const title = ref(props.pageTitle);
const sections = ref(props.defaultSections.map(section => ({ ...section })));
const titleKey = `${props.settingPrefix}_title`;
const sectionsKey = `${props.settingPrefix}_sections`;

const fetchContent = async () => {
  try {
    const response = await adminApi.settings();
    const data = response.data?.data;
    if (!data) return;
    if (typeof data[titleKey] === 'string' && data[titleKey].trim()) title.value = data[titleKey];
    if (data[sectionsKey]) {
      const parsedSections = typeof data[sectionsKey] === 'string'
        ? JSON.parse(data[sectionsKey])
        : data[sectionsKey];
      if (Array.isArray(parsedSections)) sections.value = parsedSections;
    }
  } catch (error) {
    console.error(`Failed to load ${props.pageTitle} content:`, error);
    toast.error(`Could not load ${props.pageTitle} content.`);
  }
};

const saveContent = async () => {
  if (!title.value.trim() || sections.value.some(section => !section.heading.trim())) {
    toast.error('Add a page title and a heading for each section before saving.');
    return;
  }

  saving.value = true;
  try {
    const formData = new FormData();
    formData.append(`settings[${titleKey}]`, title.value.trim());
    formData.append(`settings[${sectionsKey}]`, JSON.stringify(sections.value));
    await adminApi.updateSettings(formData);
    toast.success(`${props.pageTitle} page saved.`);
  } catch (error) {
    console.error(`Failed to save ${props.pageTitle} content:`, error);
    toast.error(`Could not save ${props.pageTitle} changes.`);
  } finally {
    saving.value = false;
  }
};

onMounted(fetchContent);
</script>
