<template>
  <AdminLayout>
    <main class="mx-auto max-w-7xl space-y-8 px-4 py-6 sm:px-6 lg:px-8">
      <!-- Header -->
      <header class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 rounded-3xl border border-theme-border bg-theme-surface p-6 sm:p-8">
        <div>
          <p class="text-xs font-bold uppercase tracking-[0.25em] text-admin">Portfolio catalogue</p>
          <h1 class="mt-2 font-display text-3xl font-bold text-theme-text">Gallery Categories</h1>
          <p class="mt-2 max-w-2xl text-sm text-theme-text-muted">
            Create and organize portfolio categories used to filter works on the public gallery and admin portfolio manager.
          </p>
        </div>
        <div class="flex flex-wrap items-center gap-2.5">
          <router-link
            to="/admin/gallery"
            class="inline-flex items-center gap-2 rounded-xl border border-theme-border bg-theme-surface-subtle px-4 py-2.5 text-xs font-bold text-theme-text transition hover:border-admin/40 hover:bg-theme-surface"
          >
            ← Back to Gallery
          </router-link>
          <router-link
            to="/admin/service-categories"
            class="inline-flex items-center gap-2 rounded-xl border border-theme-border bg-theme-surface-subtle px-4 py-2.5 text-xs font-bold text-theme-text transition hover:border-admin/40 hover:bg-theme-surface"
          >
            Service Categories
          </router-link>
        </div>
      </header>

      <!-- Main Two-Column Layout -->
      <section class="grid gap-8 xl:grid-cols-[minmax(0,0.85fr)_minmax(0,1.15fr)]">
        <!-- Form: Create / Edit Category -->
        <form class="space-y-5 rounded-3xl border border-theme-border bg-theme-surface p-6" @submit.prevent="saveCategory">
          <div class="border-b border-theme-border pb-4">
            <h2 class="text-xl font-bold text-theme-text">{{ editingId ? 'Edit gallery category' : 'Create gallery category' }}</h2>
            <p class="mt-1 text-sm text-theme-text-muted">
              Published categories appear immediately in photo uploads and filter tabs.
            </p>
          </div>

          <label class="block space-y-2">
            <span class="text-sm font-semibold text-theme-text">Category name</span>
            <input
              v-model.trim="form.name"
              required
              maxlength="150"
              class="w-full rounded-xl border border-theme-border bg-theme-surface-subtle px-4 py-3 text-theme-text outline-none focus:border-admin transition"
              placeholder="e.g. Haircuts, Transformations, Beard Art"
            >
          </label>

          <!-- Dynamic Real Icon Picker -->
          <div class="space-y-2">
            <div class="flex items-center justify-between">
              <span class="text-sm font-semibold text-theme-text">Category icon</span>
              <span class="text-xs text-theme-text-muted">Select a real icon</span>
            </div>
            <CategoryIconPicker
              v-model="form.icon"
              color-theme="admin"
            />
          </div>

          <label class="block space-y-2">
            <span class="text-sm font-semibold text-theme-text">Description <span class="font-normal text-theme-text-muted">(optional)</span></span>
            <textarea
              v-model.trim="form.description"
              rows="3"
              maxlength="500"
              class="w-full rounded-xl border border-theme-border bg-theme-surface-subtle px-4 py-3 text-theme-text outline-none focus:border-admin transition resize-none text-sm placeholder:text-theme-text-muted/50"
              placeholder="Brief summary of what photos belong to this category..."
            ></textarea>
          </label>

          <div class="flex flex-wrap gap-3 pt-2">
            <button
              type="submit"
              :disabled="saving"
              class="rounded-xl bg-admin px-6 py-3 font-bold text-white transition disabled:opacity-50 hover:bg-admin-light shadow-sm"
            >
              {{ saving ? 'Saving…' : editingId ? 'Save changes' : 'Create gallery category' }}
            </button>
            <button
              v-if="editingId"
              type="button"
              class="rounded-xl border border-theme-border px-5 py-3 font-semibold text-theme-text hover:bg-theme-surface-subtle transition"
              @click="resetForm"
            >
              Cancel
            </button>
          </div>
        </form>

        <!-- Published Gallery Categories List -->
        <section class="rounded-3xl border border-theme-border bg-theme-surface p-6 flex flex-col">
          <div class="mb-5 flex items-center justify-between gap-4 border-b border-theme-border pb-4">
            <div>
              <h2 class="text-xl font-bold text-theme-text">Published categories</h2>
              <p class="mt-1 text-sm text-theme-text-muted">Categories currently active for photos and portfolio filter tags.</p>
            </div>
            <span class="rounded-full bg-admin/15 text-admin px-3 py-1 text-sm font-bold">{{ categories.length }}</span>
          </div>

          <div v-if="loading" class="py-12 text-center text-sm text-theme-text-muted">
            Loading gallery categories…
          </div>
          <div v-else-if="!categories.length" class="rounded-2xl border border-dashed border-theme-border p-8 text-center text-sm text-theme-text-muted">
            No gallery categories yet. Create one to organize photos.
          </div>
          <div v-else class="divide-y divide-theme-border flex-1">
            <article
              v-for="category in categories"
              :key="category.id"
              class="flex items-center justify-between gap-4 py-4 group"
            >
              <div class="flex items-center gap-3.5 min-w-0">
                <!-- Visual Real Icon Badge -->
                <div class="flex size-11 shrink-0 items-center justify-center rounded-2xl border border-admin/30 bg-admin/10 text-admin shadow-sm transition-transform group-hover:scale-105">
                  <CategoryIcon :name="category.icon" custom-class="size-5" />
                </div>
                <div class="min-w-0">
                  <div class="flex items-center gap-2">
                    <h3 class="truncate font-semibold text-theme-text">{{ category.name }}</h3>
                    <span v-if="category.items_count > 0" class="rounded-full bg-theme-surface-subtle px-2 py-0.5 text-[10px] font-bold text-theme-text-muted">
                      {{ category.items_count }} {{ category.items_count === 1 ? 'photo' : 'photos' }}
                    </span>
                  </div>
                  <p v-if="category.description" class="mt-0.5 text-xs text-theme-text-muted line-clamp-1">
                    {{ category.description }}
                  </p>
                  <p class="mt-0.5 text-[11px] text-theme-text-muted/70">
                    Tag: <code class="text-admin/80">{{ category.slug }}</code>
                  </p>
                </div>
              </div>

              <div class="flex items-center gap-2 shrink-0">
                <button
                  type="button"
                  class="rounded-lg border border-theme-border px-3 py-1.5 text-xs font-semibold text-theme-text hover:bg-theme-surface-subtle transition"
                  @click="editCategory(category)"
                >
                  Edit
                </button>
                <button
                  type="button"
                  class="rounded-lg border border-theme-border px-3 py-1.5 text-xs font-semibold text-rose-400 hover:bg-rose-500/10 hover:border-rose-500/30 transition"
                  @click="deleteCategory(category)"
                >
                  Delete
                </button>
              </div>
            </article>
          </div>
        </section>
      </section>
    </main>
  </AdminLayout>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import AdminLayout from '@/portals/admin/layouts/AdminLayout.vue';
import CategoryIcon from '@/shared/ui/CategoryIcon.vue';
import CategoryIconPicker from '@/shared/ui/CategoryIconPicker.vue';
import { catalogueApi } from '@/features/catalogue/api';
import { useToast } from '@/core/composables/useToast';
import { useConfirm } from '@/core/composables/useConfirm';
import { usePublicContentStore } from '@/core/stores/publicContent.store';

const toast = useToast();
const { confirm } = useConfirm();
const publicStore = usePublicContentStore();

function extractErrorMessage(error, fallback) {
  const fieldErrors = error?.response?.data?.errors;
  if (fieldErrors && typeof fieldErrors === 'object') {
    const first = Object.values(fieldErrors).flat().find(Boolean);
    if (first) return String(first);
  }
  return error?.response?.data?.message || fallback;
}

const categories = ref([]);
const loading = ref(true);
const saving = ref(false);
const editingId = ref(null);
const form = ref({
  name: '',
  icon: 'camera',
  description: ''
});

function responseData(response) {
  return Array.isArray(response.data?.data) ? response.data.data : [];
}

async function loadData() {
  loading.value = true;
  try {
    const res = await catalogueApi.getGalleryCategories({ include_inactive: true });
    categories.value = responseData(res);
  } catch (error) {
    toast.error(error.response?.data?.message || 'Failed to load gallery categories.');
  } finally {
    loading.value = false;
  }
}

function makeSlug(value) {
  return value.normalize('NFKD').replace(/[\u0300-\u036f]/g, '').toLowerCase().trim()
    .replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
}

function resetForm() {
  editingId.value = null;
  form.value = {
    name: '',
    icon: 'camera',
    description: ''
  };
}

function editCategory(category) {
  editingId.value = category.id;
  form.value = {
    name: category.name,
    icon: category.icon || 'camera',
    description: category.description || ''
  };
  window.scrollTo({ top: 0, behavior: 'smooth' });
}

async function deleteCategory(category) {
  const confirmed = await confirm({
    title: 'Delete Gallery Category',
    message: `Are you sure you want to delete "${category.name}"? Photos in this category will remain in the gallery.`,
    confirmText: 'Delete Category',
    cancelText: 'Cancel',
    type: 'danger'
  });

  if (!confirmed) return;

  try {
    await catalogueApi.deleteGalleryCategory(category.id);
    publicStore.invalidate('galleryCategories');
    publicStore.invalidate('gallery');
    toast.success('Gallery category deleted successfully.');
    if (editingId.value === category.id) {
      resetForm();
    }
    await loadData();
  } catch (error) {
    toast.error(extractErrorMessage(error, 'Failed to delete gallery category.'));
  }
}

async function saveCategory() {
  const slug = makeSlug(form.value.name);
  if (!slug) {
    toast.error('Enter a category name containing letters or numbers.');
    return;
  }

  saving.value = true;
  const payload = {
    name: form.value.name.trim(),
    slug,
    icon: form.value.icon ? form.value.icon.trim() : null,
    description: form.value.description ? form.value.description.trim() : null,
    display_order: editingId.value
      ? categories.value.find(c => c.id === editingId.value)?.display_order || 0
      : categories.value.reduce((max, c) => Math.max(max, Number(c.display_order) || 0), 0) + 1
  };

  try {
    if (editingId.value) {
      await catalogueApi.updateGalleryCategory(editingId.value, payload);
      toast.success('Gallery category updated successfully.');
    } else {
      await catalogueApi.createGalleryCategory(payload);
      toast.success('Gallery category created and published.');
    }
    publicStore.invalidate('galleryCategories');
    publicStore.invalidate('gallery');
    resetForm();
    await loadData();
  } catch (error) {
    toast.error(extractErrorMessage(error, 'Failed to save gallery category.'));
  } finally {
    saving.value = false;
  }
}

onMounted(loadData);
</script>
