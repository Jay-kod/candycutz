<template>
  <div class="space-y-8 pb-12">
    <header class="flex flex-col justify-between gap-5 sm:flex-row sm:items-center">
      <div>
        <p class="text-xs font-bold uppercase tracking-[0.2em] text-admin">Website content</p>
        <h1 class="mt-2 text-3xl font-bold text-theme-text">Homepage CTA</h1>
        <p class="mt-2 max-w-2xl text-sm text-ivory/60">
          Edit the homepage headline, supporting lines, headline color, and booking button.
        </p>
      </div>
      <button
        type="button"
        class="inline-flex min-h-11 items-center justify-center gap-2 rounded-lg bg-admin px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-admin-light disabled:cursor-not-allowed disabled:opacity-60"
        :disabled="loading || saving"
        @click="save"
      >
        <span
          v-if="saving"
          class="h-4 w-4 animate-spin rounded-full border-2 border-white/40 border-t-white"
          aria-hidden="true"
        ></span>
        <CheckIcon v-else class="h-4 w-4" />
        {{ saving ? 'Saving...' : 'Save Changes' }}
      </button>
    </header>

    <div v-if="loading" class="flex justify-center rounded-xl border border-white/10 bg-black/20 py-16">
      <span class="h-8 w-8 animate-spin rounded-full border-2 border-admin/30 border-t-admin" aria-label="Loading CTA settings"></span>
    </div>

    <template v-else>
      <section class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_minmax(0,1.1fr)]">
        <form class="space-y-5 rounded-xl border border-white/10 bg-black/20 p-5 sm:p-7" @submit.prevent="save">
          <div>
            <label for="cta-title" class="mb-2 block text-sm font-semibold text-theme-text">Headline</label>
            <input
              id="cta-title"
              v-model.trim="settings.cta_title"
              maxlength="140"
              required
              type="text"
              class="cta-input"
              placeholder="Fresh Cut. Zero Stress."
            />
            <p class="mt-2 text-xs text-ivory/50">Keep the main headline to one sentence.</p>
          </div>

          <div>
            <label for="cta-title-color" class="mb-2 block text-sm font-semibold text-theme-text">Headline color</label>
            <div class="flex items-center gap-3">
              <input
                id="cta-title-color"
                v-model="settings.cta_title_color"
                type="color"
                class="h-11 w-16 cursor-pointer rounded-lg border border-white/10 bg-black/30 p-1"
              />
              <span class="text-sm text-ivory/70">{{ settings.cta_title_color }}</span>
            </div>
          </div>

          <div>
            <label for="cta-subtitle" class="mb-2 block text-sm font-semibold text-theme-text">Supporting line</label>
            <textarea
              id="cta-subtitle"
              v-model="settings.cta_subtitle"
              maxlength="240"
              required
              rows="2"
              class="cta-input resize-y"
              placeholder="Pick your barber, choose your style, lock your time."
            ></textarea>
          </div>

          <div>
            <label for="cta-caption" class="mb-2 block text-sm font-semibold text-theme-text">Smaller supporting text</label>
            <textarea
              id="cta-caption"
              v-model="settings.cta_caption"
              maxlength="240"
              rows="2"
              class="cta-input resize-y"
              placeholder="Expert barbers. Fresh fades. A better kind of barbershop experience."
            ></textarea>
            <p class="mt-2 text-xs text-ivory/50">This appears below the supporting line and above the booking button.</p>
          </div>

          <div class="grid gap-5">
            <div>
              <label for="cta-primary" class="mb-2 block text-sm font-semibold text-theme-text">Booking button</label>
              <input
                id="cta-primary"
                v-model="settings.cta_primary_text"
                maxlength="40"
                required
                type="text"
                class="cta-input"
              />
              <p class="mt-2 text-xs text-ivory/50">Links to the customer sign-in and booking flow.</p>
            </div>

          </div>
        </form>

        <div class="min-w-0">
          <p class="mb-3 text-xs font-bold uppercase tracking-[0.16em] text-ivory/50">Live preview</p>
          <BarberCta
            :title="settings.cta_title"
            :subtitle="settings.cta_subtitle"
            :caption="settings.cta_caption"
            :title-color="settings.cta_title_color"
            :primary-text="settings.cta_primary_text"
            :background-image="settings.hero_image ? getStorageUrl(settings.hero_image) : ''"
            :image-opacity="settings.hero_image_opacity"
            compact
          />
          <p class="mt-3 text-xs text-ivory/50">Preview the colored single-sentence headline and supporting text hierarchy.</p>
        </div>
      </section>

      <div class="flex justify-end">
        <button
          type="button"
          class="inline-flex min-h-11 items-center justify-center gap-2 rounded-lg bg-admin px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-admin-light disabled:cursor-not-allowed disabled:opacity-60"
          :disabled="saving"
          @click="save"
        >
          <span
            v-if="saving"
            class="h-4 w-4 animate-spin rounded-full border-2 border-white/40 border-t-white"
            aria-hidden="true"
          ></span>
          <CheckIcon v-else class="h-4 w-4" />
          {{ saving ? 'Saving...' : 'Save Changes' }}
        </button>
      </div>
    </template>
  </div>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue';
import { CheckIcon } from '@heroicons/vue/24/outline';
import api from '@/shared/api/client';
import { useToast } from '@/core/composables/useToast';
import BarberCta from '@/shared/ui/BarberCta.vue';
import { getStorageUrl } from '@/core/utils/url';

const toast = useToast();
const loading = ref(true);
const saving = ref(false);
const settings = reactive({
  cta_title: 'Fresh Cut. Zero Stress.',
  cta_title_color: '#ffb84d',
  cta_subtitle: 'Pick your barber, choose your style, lock your time.',
  cta_caption: 'Expert barbers. Fresh fades. A better kind of barbershop experience.',
  cta_primary_text: 'Book your cut',
  hero_image: null,
  hero_image_opacity: 22
});

const loadSettings = async () => {
  loading.value = true;
  try {
    const response = await api.get('/v1/admin/settings');
    const data = response.data.data || {};
    settings.cta_title = data.cta_title || data.hero_title || settings.cta_title;
    settings.cta_subtitle = data.cta_subtitle || data.hero_subtitle || settings.cta_subtitle;
    settings.cta_title_color = /^#[\da-f]{6}$/i.test(data.cta_title_color || '')
      ? data.cta_title_color
      : settings.cta_title_color;
    settings.cta_caption = data.cta_caption ?? settings.cta_caption;
    settings.cta_primary_text = data.cta_primary_text || data.hero_btn1_text || settings.cta_primary_text;
    settings.hero_image = data.hero_image || null;
    settings.hero_image_opacity = data.hero_image_opacity ?? data.hero_opacity ?? 22;
  } catch (error) {
    console.error('Failed to load homepage CTA settings:', error);
    toast.error('Could not load homepage CTA settings');
  } finally {
    loading.value = false;
  }
};

const save = async () => {
  if (
    !settings.cta_title.trim() ||
    !settings.cta_subtitle.trim() ||
    !settings.cta_primary_text.trim()
  ) {
    toast.error('Headline, description, and booking button label are required');
    return;
  }

  saving.value = true;
  try {
    const formData = new FormData();
    Object.entries(settings).forEach(([key, value]) => {
      formData.append(`settings[${key}]`, value);
    });
    await api.post('/v1/admin/settings', formData);
    toast.success('Homepage CTA updated');
  } catch (error) {
    console.error('Failed to save homepage CTA settings:', error);
    toast.error('Could not save homepage CTA settings');
  } finally {
    saving.value = false;
  }
};

onMounted(loadSettings);
</script>

<style scoped>
.cta-input {
  width: 100%;
  border: 1px solid rgba(255, 255, 255, 0.12);
  border-radius: 0.5rem;
  background: rgba(0, 0, 0, 0.35);
  padding: 0.75rem 0.875rem;
  color: #f5f5f5;
  outline: none;
  transition: border-color 160ms ease, box-shadow 160ms ease;
}

.cta-input:focus {
  border-color: #ff6700;
  box-shadow: 0 0 0 2px rgba(255, 103, 0, 0.18);
}

.cta-input::placeholder {
  color: rgba(255, 255, 255, 0.35);
}
</style>
