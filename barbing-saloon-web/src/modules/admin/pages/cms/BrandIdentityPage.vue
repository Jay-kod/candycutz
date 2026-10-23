<template>
  <component :is="layout">
    <div class="mx-auto max-w-5xl space-y-6 pb-12">
      <header class="flex flex-col gap-4 rounded-3xl border border-admin/20 bg-gradient-to-br from-obsidian via-charcoal to-[#2A1100] p-6 shadow-2xl sm:flex-row sm:items-center sm:justify-between sm:p-8">
        <div>
          <p class="text-xs font-bold uppercase tracking-[0.25em] text-admin/80">Business Identity</p>
          <h1 class="mt-2 font-display text-3xl text-theme-text sm:text-4xl">Shop Name & Branding</h1>
          <p class="mt-2 max-w-2xl text-sm text-ivory/60">Set the shop name, wordmark colors, and short description used across the website and dashboards.</p>
        </div>
        <button
          type="button"
          :disabled="loading || saving"
          class="inline-flex min-h-11 shrink-0 items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-admin to-admin-light px-5 py-3 text-sm font-bold text-obsidian transition hover:shadow-lg disabled:cursor-not-allowed disabled:opacity-50"
          @click="save"
        >
          <span v-if="saving" class="size-4 animate-spin rounded-full border-2 border-obsidian border-t-transparent"></span>
          <CheckIcon v-else class="size-5" />
          {{ saving ? 'Saving...' : 'Save Branding' }}
        </button>
      </header>

      <div v-if="loading" class="rounded-2xl border border-white/10 bg-black/20 py-16 text-center text-sm text-ivory/60" role="status">
        Loading shop identity...
      </div>

      <section v-else class="grid gap-6 lg:grid-cols-[1fr_0.8fr]">
        <div class="space-y-6 rounded-2xl border border-admin/10 bg-black/30 p-5 shadow-xl sm:p-7">
          <div class="grid gap-5 sm:grid-cols-2">
            <label class="block">
              <span class="mb-2 block text-xs font-bold uppercase tracking-widest text-ivory/50">First name part</span>
              <input v-model="form.first" type="text" maxlength="60" required autocomplete="off" class="editor-input" placeholder="Candy" />
            </label>
            <label class="block">
              <span class="mb-2 block text-xs font-bold uppercase tracking-widest text-ivory/50">Second name part</span>
              <input v-model="form.second" type="text" maxlength="60" required autocomplete="off" class="editor-input" placeholder="Cutz" />
            </label>
          </div>
          <label class="block">
            <span class="mb-2 block text-xs font-bold uppercase tracking-widest text-ivory/50">Space between parts</span>
            <select v-model="form.separator" class="editor-input">
              <option value="none">No space (CandyCutz)</option>
              <option value="space">Add a space (Candy Cutz)</option>
            </select>
          </label>

          <label class="block">
            <span class="mb-2 block text-xs font-bold uppercase tracking-widest text-ivory/50">Gold wordmark part</span>
            <select v-model="form.goldPart" class="editor-input">
              <option value="second">First part white, second part gold</option>
              <option value="first">First part gold, second part white</option>
            </select>
          </label>
          <p class="-mt-4 text-xs text-ivory/45">The other part is displayed in white.</p>

          <label class="block">
            <span class="mb-2 block text-xs font-bold uppercase tracking-widest text-ivory/50">Short description</span>
            <textarea
              v-model="form.tagline"
              rows="4"
              maxlength="300"
              class="editor-input resize-y"
              placeholder="A short description of your shop"
            ></textarea>
            <span class="mt-2 block text-xs text-ivory/45">Shown in the website footer.</span>
          </label>
        </div>

        <aside class="flex flex-col justify-center rounded-2xl border border-admin/10 bg-black/30 p-6 shadow-xl sm:p-8">
          <p class="text-xs font-bold uppercase tracking-[0.16em] text-ivory/50">Live preview</p>
          <div class="mt-5 rounded-2xl border border-white/10 bg-obsidian p-6">
            <p class="text-xs font-semibold uppercase tracking-widest text-ivory/40">Website navigation</p>
            <p class="mt-3 break-words font-display text-2xl font-bold"><BrandWordmark :first="form.first.trim() || 'First'" :second="form.second.trim() || 'Second'" :gold-first="form.goldPart === 'first'" :space-between="form.separator === 'space'" /></p>
            <div class="my-6 border-t border-white/10"></div>
            <p class="text-xs font-semibold uppercase tracking-widest text-ivory/40">Website footer</p>
            <p class="mt-3 break-words font-display text-xl font-bold"><BrandWordmark :first="form.first.trim() || 'First'" :second="form.second.trim() || 'Second'" :gold-first="form.goldPart === 'first'" :space-between="form.separator === 'space'" /></p>
            <p class="mt-2 text-sm leading-6 text-ivory/60">{{ form.tagline.trim() || 'Your shop description will appear here.' }}</p>
          </div>
          <p class="mt-4 text-xs leading-5 text-ivory/45">Your uploaded logo/icon is kept as-is; this page changes the displayed shop name and description.</p>
        </aside>
      </section>

      <div v-if="!loading" class="flex justify-end">
        <button
          type="button"
          :disabled="saving"
          class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-admin to-admin-light px-5 py-3 text-sm font-bold text-obsidian transition hover:shadow-lg disabled:cursor-not-allowed disabled:opacity-50"
          @click="save"
        >
          <CheckIcon v-if="!saving" class="size-5" />
          {{ saving ? 'Saving...' : 'Save Branding' }}
        </button>
      </div>
    </div>
  </component>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue';
import { useRoute } from 'vue-router';
import { CheckIcon } from '@heroicons/vue/24/outline';
import AdminLayout from '@/portals/admin/layouts/AdminLayout.vue';
import SuperAdminLayout from '@/portals/superadmin/layouts/SuperAdminLayout.vue';
import { adminApi } from '@/shared/api/old_adminApi';
import { superadminApi } from '@/shared/api/old_superadminApi';
import { useToast } from '@/core/composables/useToast';
import BrandWordmark from '@/shared/ui/BrandWordmark.vue';
import { applyBrandSettings, splitBrandName } from '@/shared/composables/useBrandIdentity';

const route = useRoute();
const toast = useToast();
const loading = ref(true);
const saving = ref(false);
const form = reactive({ first: 'Candy', second: 'Cutz', separator: 'none', goldPart: 'second', tagline: '' });
const isAdmin = computed(() => route.path.startsWith('/admin'));
const layout = computed(() => (isAdmin.value ? AdminLayout : SuperAdminLayout));
const api = computed(() => (isAdmin.value ? adminApi : superadminApi));

const load = async () => {
  loading.value = true;
  try {
    const response = await api.value.settings();
    const settings = response.data?.data || {};
    const name = settings.brand_name || settings.site_name || 'CandyCutz';
    const [first, second, separator] = splitBrandName(name);
    form.first = settings.brand_name_part_one ?? first;
    form.second = settings.brand_name_part_two ?? second;
    form.separator = settings.brand_name_separator ?? separator;
    form.goldPart = settings.brand_gold_part === 'first' ? 'first' : 'second';
    form.tagline = settings.brand_tagline || settings.site_description || '';
    applyBrandSettings(settings);
  } catch (error) {
    console.error('Failed to load shop identity settings:', error);
    toast.error('Could not load shop identity settings.');
  } finally {
    loading.value = false;
  }
};

const save = async () => {
  const first = form.first.trim();
  const second = form.second.trim();
  if (!first || !second) {
    toast.error('Enter both parts of the shop name before saving.');
    return;
  }
  const name = `${first}${form.separator === 'space' ? ' ' : ''}${second}`;

  saving.value = true;
  try {
    const formData = new FormData();
    formData.append('settings[brand_name]', name);
    formData.append('settings[site_name]', name);
    formData.append('settings[brand_name_part_one]', first);
    formData.append('settings[brand_name_part_two]', second);
    formData.append('settings[brand_name_separator]', form.separator);
    formData.append('settings[brand_gold_part]', form.goldPart);
    formData.append('settings[brand_tagline]', form.tagline.trim());
    formData.append('settings[site_description]', form.tagline.trim());
    await api.value.updateSettings(formData);
    applyBrandSettings({
      brand_name: name,
      brand_name_part_one: first,
      brand_name_part_two: second,
      brand_name_separator: form.separator,
      brand_gold_part: form.goldPart,
      brand_tagline: form.tagline.trim(),
    });
    toast.success('Shop name and branding saved.');
  } catch (error) {
    console.error('Failed to save shop identity settings:', error);
    toast.error('Could not save shop identity settings.');
  } finally {
    saving.value = false;
  }
};

onMounted(load);
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
