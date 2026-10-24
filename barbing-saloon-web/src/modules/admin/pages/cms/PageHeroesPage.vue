<template>
  <div class="space-y-6 animate-fade-in pb-12">
    <header class="flex flex-col gap-4 rounded-3xl border border-admin/20 bg-gradient-to-br from-obsidian via-charcoal to-[#2A1100] p-6 shadow-2xl sm:flex-row sm:items-center sm:justify-between sm:p-8">
      <div>
        <p class="text-xs font-bold uppercase tracking-[0.25em] text-admin/80">Website CMS</p>
        <h1 class="mt-2 font-display text-3xl text-theme-text sm:text-4xl">Page Heroes</h1>
        <p class="mt-2 max-w-2xl text-sm text-ivory/60">Set the 40vh hero title, subtitle, cover image, and overlay opacity for each public content page.</p>
      </div>
      <button
        type="button"
        :disabled="loading || saving"
        class="inline-flex min-h-11 shrink-0 items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-admin to-admin-light px-5 py-3 text-sm font-bold text-obsidian transition hover:shadow-lg disabled:cursor-not-allowed disabled:opacity-50"
        @click="save"
      >
        <span v-if="saving" class="size-4 animate-spin rounded-full border-2 border-obsidian border-t-transparent"></span>
        <CheckIcon v-else class="size-5" />
        {{ saving ? 'Saving...' : 'Save Hero' }}
      </button>
    </header>

    <div v-if="loading" class="rounded-2xl border border-white/10 bg-black/20 py-16 text-center text-sm text-ivory/60" role="status">
      Loading page hero settings...
    </div>

    <template v-else>
      <section class="rounded-2xl border border-admin/10 bg-black/30 p-5 shadow-xl sm:p-7">
        <label for="hero-page" class="mb-2 block text-xs font-bold uppercase tracking-widest text-ivory/50">Website page</label>
        <select id="hero-page" v-model="selectedPage" class="editor-input" @change="loadSelectedPage">
          <option v-for="page in pages" :key="page.key" :value="page.key">{{ page.label }}</option>
        </select>
        <p class="mt-2 text-xs text-ivory/45">Choose a page, add or replace its cover image, then save to publish it to that page’s hero. The homepage cover is managed here too.</p>
      </section>

      <section class="grid gap-6 xl:grid-cols-2">
        <div class="space-y-5 rounded-2xl border border-admin/10 bg-black/30 p-5 shadow-xl sm:p-7">
          <div>
            <label for="hero-title" class="mb-2 block text-xs font-bold uppercase tracking-widest text-ivory/50">Hero title</label>
            <input id="hero-title" v-model.trim="form.title" maxlength="180" class="editor-input" />
          </div>
          <div>
            <label for="hero-subtitle" class="mb-2 block text-xs font-bold uppercase tracking-widest text-ivory/50">Hero subtitle</label>
            <textarea id="hero-subtitle" v-model.trim="form.subtitle" rows="4" maxlength="1000" class="editor-input resize-y"></textarea>
          </div>
          <div>
            <label for="hero-image" class="mb-2 block text-xs font-bold uppercase tracking-widest text-ivory/50">Hero cover image</label>
            <input
              ref="imageInput"
              id="hero-image"
              type="file"
              accept="image/jpeg,image/png,image/webp"
              class="editor-input file:mr-4 file:rounded-lg file:border-0 file:bg-admin/15 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-admin-light"
              @change="selectImage"
            />
            <p class="mt-2 text-xs text-ivory/45">Add a JPG, PNG, or WEBP image up to 5 MB. The saved cover appears behind this page’s hero title.</p>
          </div>
          <div>
            <div class="mb-2 flex items-center justify-between gap-4">
              <label for="hero-opacity" class="block text-xs font-bold uppercase tracking-widest text-ivory/50">Overlay opacity</label>
              <span class="text-sm font-semibold text-admin-light">{{ form.opacity }}%</span>
            </div>
            <input id="hero-opacity" v-model.number="form.opacity" type="range" min="0" max="100" class="w-full accent-admin" />
          </div>
          <div class="flex flex-wrap gap-3">
            <button
              v-if="form.image || selectedFile"
              type="button"
              class="min-h-10 rounded-xl border border-red-500/30 px-4 py-2 text-sm font-semibold text-red-300 transition hover:bg-red-500/10"
              @click="clearImage"
            >
              Remove cover image
            </button>
            <button type="button" class="min-h-10 rounded-xl border border-white/10 px-4 py-2 text-sm font-semibold text-ivory/70 transition hover:border-white/30" @click="resetText">
              Reset title and subtitle
            </button>
          </div>
        </div>

        <div class="min-w-0">
          <p class="mb-3 text-xs font-bold uppercase tracking-[0.16em] text-ivory/50">40vh preview</p>
          <PageHero
            :key="selectedPage"
            :page-key="selectedPage"
            :eyebrow="currentPage.label"
            :fallback-title="form.title || currentPage.defaultTitle"
            :fallback-subtitle="form.subtitle"
            :background-image="previewImage"
            :overlay-opacity="form.opacity"
            :load-settings="false"
          />
        </div>
      </section>

      <div class="flex justify-end">
        <button
          type="button"
          :disabled="saving"
          class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-admin to-admin-light px-5 py-3 text-sm font-bold text-obsidian transition hover:shadow-lg disabled:cursor-not-allowed disabled:opacity-50"
          @click="save"
        >
          <CheckIcon v-if="!saving" class="size-5" />
          {{ saving ? 'Saving...' : 'Save Hero' }}
        </button>
      </div>
    </template>
  </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, reactive, ref } from 'vue';
import { CheckIcon } from '@heroicons/vue/24/outline';
import PageHero from '@/shared/ui/PageHero.vue';
import { adminApi } from '@/shared/api/old_adminApi';
import { getStorageUrl } from '@/core/utils/url';
import { useToast } from '@/core/composables/useToast';

const pages = [
  { key: 'home', label: 'Home', defaultTitle: 'Precision Cuts. Made for You.', defaultSubtitle: 'Expert barbers. Fresh fades. A better kind of barbershop experience.' },
  { key: 'about', label: 'About', defaultTitle: 'About CandyCutz', defaultSubtitle: 'Our story, our craft, and the standard behind every cut.' },
  { key: 'services', label: 'Services', defaultTitle: 'Our Premium Services', defaultSubtitle: 'Explore our full range of grooming services designed to keep you looking sharp and feeling confident.', titleKey: 'services_title', subtitleKey: 'services_subtitle' },
  { key: 'gallery', label: 'Gallery', defaultTitle: 'Our Gallery', defaultSubtitle: 'Browse our portfolio of premium cuts, styles, and grooming transformations.', titleKey: 'gallery_title', subtitleKey: 'gallery_subtitle' },
  { key: 'contact', label: 'Contact', defaultTitle: 'Contact', defaultSubtitle: 'Get in touch with the CandyCutz team.', titleKey: 'contact_title', subtitleKey: 'contact_subtitle' },
  { key: 'privacy', label: 'Privacy Policy', defaultTitle: 'Privacy Policy', defaultSubtitle: 'How we collect, use, and protect your information.', titleKey: 'privacy_title' },
  { key: 'terms', label: 'Terms of Service', defaultTitle: 'Terms of Service', defaultSubtitle: 'The terms that apply when you use CandyCutz services.', titleKey: 'terms_title' },
  { key: 'legal', label: 'Legal Stuff', defaultTitle: 'Legal Stuff', defaultSubtitle: 'Important information about CandyCutz.', titleKey: 'legal_stuff_title' },
  { key: 'security', label: 'Security', defaultTitle: 'Security', defaultSubtitle: 'How CandyCutz approaches the security of your information.', titleKey: 'security_title' },
  { key: 'cookies', label: 'Manage Cookies', defaultTitle: 'Manage Cookies', defaultSubtitle: 'Manage your cookie preferences.', titleKey: 'cookie_title', subtitleKey: 'cookie_intro' },
  { key: 'account_deletion', label: 'Account Deletion', defaultTitle: 'Account Deactivation & Erasure', defaultSubtitle: 'Instructions for requesting deletion or deactivation of your account and associated personal data.', titleKey: 'account_deletion_title' },
];

const toast = useToast();
const settings = ref({});
const loading = ref(true);
const saving = ref(false);
const selectedPage = ref('home');
const selectedFile = ref(null);
const imageInput = ref(null);
const localPreviewUrl = ref('');
const removeImage = ref(false);
const form = reactive({ title: '', subtitle: '', image: '', opacity: 22 });
const currentPage = computed(() => pages.find(page => page.key === selectedPage.value) || pages[0]);
const isHome = computed(() => selectedPage.value === 'home');
const titleKey = computed(() => (isHome.value ? 'cta_title' : `page_hero_${selectedPage.value}_title`));
const subtitleKey = computed(() => (isHome.value ? 'cta_subtitle' : `page_hero_${selectedPage.value}_subtitle`));
const imageKey = computed(() => (isHome.value ? 'hero_image' : `page_hero_${selectedPage.value}_image`));
const opacityKey = computed(() => `page_hero_${selectedPage.value}_overlay_opacity`);
const previewImage = computed(() => {
  if (localPreviewUrl.value) return localPreviewUrl.value;
  if (removeImage.value || !form.image) return '';
  return getStorageUrl(form.image);
});

const loadSelectedPage = () => {
  const page = currentPage.value;
  const fallbackTitle = page.titleKey ? settings.value[page.titleKey] : '';
  const fallbackSubtitle = page.subtitleKey ? settings.value[page.subtitleKey] : '';
  form.title = settings.value[titleKey.value] || fallbackTitle || page.defaultTitle;
  form.subtitle = settings.value[subtitleKey.value] || fallbackSubtitle || page.defaultSubtitle;
  form.image = settings.value[imageKey.value] || '';
  form.opacity = Number(settings.value[opacityKey.value] ?? 76);
  selectedFile.value = null;
  if (imageInput.value) imageInput.value.value = '';
  removeImage.value = false;
  clearPreviewUrl();
};

const clearPreviewUrl = () => {
  if (localPreviewUrl.value) URL.revokeObjectURL(localPreviewUrl.value);
  localPreviewUrl.value = '';
};

const selectImage = (event) => {
  const file = event.target.files?.[0] || null;
  selectedFile.value = file;
  removeImage.value = false;
  clearPreviewUrl();
  if (file) localPreviewUrl.value = URL.createObjectURL(file);
};

const clearImage = () => {
  selectedFile.value = null;
  if (imageInput.value) imageInput.value.value = '';
  removeImage.value = true;
  clearPreviewUrl();
};

const resetText = () => {
  form.title = currentPage.value.titleKey
    ? settings.value[currentPage.value.titleKey] || currentPage.value.defaultTitle
    : currentPage.value.defaultTitle;
  form.subtitle = currentPage.value.subtitleKey
    ? settings.value[currentPage.value.subtitleKey] || currentPage.value.defaultSubtitle
    : currentPage.value.defaultSubtitle;
};

const loadSettings = async () => {
  loading.value = true;
  try {
    const response = await adminApi.settings();
    settings.value = response.data?.data || {};
    loadSelectedPage();
  } catch (error) {
    console.error('Failed to load page hero settings:', error);
    toast.error('Could not load page hero settings.');
  } finally {
    loading.value = false;
  }
};

const save = async () => {
  if (!form.title.trim() || !form.subtitle.trim()) {
    toast.error('A hero title and subtitle are required.');
    return;
  }

  saving.value = true;
  try {
    const formData = new FormData();
    formData.append(`settings[${titleKey.value}]`, form.title.trim());
    formData.append(`settings[${subtitleKey.value}]`, form.subtitle.trim());
    formData.append(`settings[${opacityKey.value}]`, String(form.opacity));
    formData.append('page_hero_page', selectedPage.value);
    if (selectedFile.value) formData.append('page_hero_image', selectedFile.value);
    else if (removeImage.value) formData.append(`settings[${imageKey.value}]`, '');

    const response = await adminApi.updateSettings(formData);
    settings.value = response.data?.data || settings.value;
    loadSelectedPage();
    toast.success(`${currentPage.value.label} hero saved.`);
  } catch (error) {
    console.error(`Failed to save ${currentPage.value.label} hero:`, error);
    toast.error(`Could not save the ${currentPage.value.label} hero.`);
  } finally {
    saving.value = false;
  }
};

onMounted(loadSettings);
onBeforeUnmount(clearPreviewUrl);
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
