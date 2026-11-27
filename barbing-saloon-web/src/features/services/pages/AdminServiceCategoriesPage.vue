<template>
  <AdminLayout>
    <main class="mx-auto max-w-7xl space-y-8 px-4 py-6 sm:px-6 lg:px-8">
      <!-- Header -->
      <header class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 rounded-3xl border border-theme-border bg-theme-surface p-6 sm:p-8">
        <div>
          <p class="text-xs font-bold uppercase tracking-[0.25em] text-admin">Service catalogue</p>
          <h1 class="mt-2 font-display text-3xl font-bold text-theme-text">Service Categories</h1>
          <p class="mt-2 max-w-2xl text-sm text-theme-text-muted">
            Create and organize the categories displayed on the customer service menu and booking flows.
          </p>
        </div>
        <div class="flex flex-wrap items-center gap-2.5">
          <router-link
            to="/admin/gallery-categories"
            class="inline-flex items-center gap-2 rounded-xl border border-theme-border bg-theme-surface-subtle px-4 py-2.5 text-xs font-bold text-theme-text transition hover:border-admin/40 hover:bg-theme-surface"
          >
            <span>Gallery Categories</span>
            <span class="text-admin font-normal">→</span>
          </router-link>
          <router-link
            to="/admin/services"
            class="inline-flex items-center gap-2 rounded-xl border border-theme-border bg-theme-surface-subtle px-4 py-2.5 text-xs font-bold text-theme-text transition hover:border-admin/40 hover:bg-theme-surface"
          >
            All Services
          </router-link>
        </div>
      </header>

      <!-- Main Two-Column Layout -->
      <section class="grid gap-8 xl:grid-cols-[minmax(0,0.85fr)_minmax(0,1.15fr)]">
        <!-- Form: Create / Edit Category -->
        <form class="space-y-5 rounded-3xl border border-theme-border bg-theme-surface p-6" @submit.prevent="saveCategory">
          <div class="border-b border-theme-border pb-4">
            <h2 class="text-xl font-bold text-theme-text">{{ editingId ? 'Edit service category' : 'Create service category' }}</h2>
            <p class="mt-1 text-sm text-theme-text-muted">
              Published categories are immediately available for service assignments.
            </p>
          </div>

          <label class="block space-y-2">
            <span class="text-sm font-semibold text-theme-text">Category name</span>
            <input
              v-model.trim="form.name"
              required
              maxlength="150"
              class="w-full rounded-xl border border-theme-border bg-theme-surface-subtle px-4 py-3 text-theme-text outline-none focus:border-admin transition"
              placeholder="e.g. Signature Haircuts"
            >
          </label>

          <!-- Dynamic Real Icon Picker -->
          <div class="space-y-2">
            <div class="flex items-center justify-between">
              <span class="text-sm font-semibold text-theme-text">Category icon</span>
              <span class="text-xs text-theme-text-muted">Select a real visual icon</span>
            </div>
            <CategoryIconPicker
              v-model="form.icon"
              color-theme="admin"
            />
          </div>

          <div class="flex flex-wrap gap-3 pt-2">
            <button
              type="submit"
              :disabled="saving"
              class="rounded-xl bg-admin px-6 py-3 font-bold text-white transition disabled:opacity-50 hover:bg-admin-light shadow-sm"
            >
              {{ saving ? 'Saving…' : editingId ? 'Save changes' : 'Create category' }}
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

        <!-- Published Categories List -->
        <section class="rounded-3xl border border-theme-border bg-theme-surface p-6 flex flex-col">
          <div class="mb-5 flex items-center justify-between gap-4 border-b border-theme-border pb-4">
            <div>
              <h2 class="text-xl font-bold text-theme-text">Published categories</h2>
              <p class="mt-1 text-sm text-theme-text-muted">Categories currently available on service forms.</p>
            </div>
            <span class="rounded-full bg-admin/15 text-admin px-3 py-1 text-sm font-bold">{{ categories.length }}</span>
          </div>

          <div v-if="loading" class="py-12 text-center text-sm text-theme-text-muted">
            Loading categories…
          </div>
          <div v-else-if="!categories.length" class="rounded-2xl border border-dashed border-theme-border p-8 text-center text-sm text-theme-text-muted">
            No service categories yet. Create one to organize salon services.
          </div>
          <div v-else class="divide-y divide-theme-border flex-1">
            <article
              v-for="category in categories"
              :key="category.id"
              class="flex items-center justify-between gap-4 py-4 group"
            >
              <div class="flex items-center gap-3.5 min-w-0">
                <!-- Visual Icon Badge -->
                <div class="flex size-11 shrink-0 items-center justify-center rounded-2xl border border-admin/30 bg-admin/10 text-admin shadow-sm transition-transform group-hover:scale-105">
                  <CategoryIcon :name="category.icon" custom-class="size-5" />
                </div>
                <div class="min-w-0">
                  <h3 class="truncate font-semibold text-theme-text">{{ category.name }}</h3>
                  <p class="mt-0.5 text-xs text-theme-text-muted">
                    {{ category.services_count || 0 }} approved services · <code class="text-[11px] text-theme-text-muted/80">{{ category.slug }}</code>
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

      <!-- Barber Category Requests -->
      <section class="rounded-3xl border border-theme-border bg-theme-surface p-6">
        <div class="mb-5 flex items-center justify-between gap-4 border-b border-theme-border pb-4">
          <div>
            <h2 class="text-xl font-bold text-theme-text">Barber category requests</h2>
            <p class="mt-1 text-sm text-theme-text-muted">Approve a barber's request to publish it for the shared catalogue.</p>
          </div>
          <span class="rounded-full bg-amber-500/15 px-3 py-1 text-sm font-bold text-amber-400">{{ pendingRequests.length }} pending</span>
        </div>

        <p v-if="loading" class="py-8 text-center text-sm text-theme-text-muted">Loading requests…</p>
        <p v-else-if="!requests.length" class="rounded-2xl border border-dashed border-theme-border p-6 text-center text-sm text-theme-text-muted">
          No barber category requests yet.
        </p>
        <div v-else class="space-y-3">
          <article
            v-for="request in requests"
            :key="request.id"
            class="flex flex-col gap-4 rounded-2xl border border-theme-border p-4 sm:flex-row sm:items-center sm:justify-between"
          >
            <div class="flex items-center gap-3.5 min-w-0">
              <div class="flex size-10 shrink-0 items-center justify-center rounded-xl border border-theme-border bg-theme-surface-subtle text-theme-text-muted">
                <CategoryIcon :name="request.icon" custom-class="size-5" />
              </div>
              <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                  <h3 class="font-semibold text-theme-text">{{ request.name }}</h3>
                  <span class="rounded-full px-2.5 py-0.5 text-[11px] font-bold capitalize" :class="statusClass(request.status)">{{ request.status }}</span>
                </div>
                <p class="mt-1 text-xs text-theme-text-muted">
                  Requested by {{ request.requester?.name || 'Barber' }} · {{ formatDate(request.created_at) }}
                </p>
                <p v-if="request.review_note" class="mt-1 text-xs text-theme-text-muted">{{ request.review_note }}</p>
              </div>
            </div>

            <div v-if="request.status === 'pending'" class="flex shrink-0 gap-2">
              <button
                type="button"
                :disabled="reviewingId === request.id"
                class="rounded-lg bg-emerald-500/15 px-3.5 py-2 text-xs font-bold text-emerald-400 hover:bg-emerald-500/25 disabled:opacity-50 transition"
                @click="reviewRequest(request, 'approve')"
              >
                Approve
              </button>
              <button
                type="button"
                :disabled="reviewingId === request.id"
                class="rounded-lg bg-rose-500/15 px-3.5 py-2 text-xs font-bold text-rose-400 hover:bg-rose-500/25 disabled:opacity-50 transition"
                @click="reviewRequest(request, 'reject')"
              >
                Reject
              </button>
            </div>
          </article>
        </div>
      </section>
    </main>
  </AdminLayout>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
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
const requests = ref([]);
const loading = ref(true);
const saving = ref(false);
const reviewingId = ref(null);
const editingId = ref(null);
const form = ref({ name: '', icon: 'scissors' });

const pendingRequests = computed(() => requests.value.filter(request => request.status === 'pending'));

function responseData(response) {
  return Array.isArray(response.data?.data) ? response.data.data : [];
}

async function loadData() {
  loading.value = true;
  try {
    const [categoryResponse, requestResponse] = await Promise.all([
      catalogueApi.getCategories(),
      catalogueApi.getCategoryRequests()
    ]);
    categories.value = responseData(categoryResponse);
    requests.value = responseData(requestResponse);
  } catch (error) {
    toast.error(error.response?.data?.message || 'Failed to load service categories.');
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
  form.value = { name: '', icon: 'scissors' };
}

function editCategory(category) {
  editingId.value = category.id;
  form.value = {
    name: category.name,
    icon: category.icon || 'scissors'
  };
  window.scrollTo({ top: 0, behavior: 'smooth' });
}

async function deleteCategory(category) {
  const confirmed = await confirm({
    title: 'Delete Service Category',
    message: `Are you sure you want to delete "${category.name}"? Services linked to this category may need reassigning.`,
    confirmText: 'Delete Category',
    cancelText: 'Cancel',
    type: 'danger'
  });

  if (!confirmed) return;

  try {
    await catalogueApi.deleteCategory(category.id);
    publicStore.invalidate('categories');
    publicStore.invalidate('services');
    toast.success('Category deleted successfully.');
    if (editingId.value === category.id) {
      resetForm();
    }
    await loadData();
  } catch (error) {
    toast.error(extractErrorMessage(error, 'Failed to delete category.'));
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
    display_order: editingId.value
      ? categories.value.find(category => category.id === editingId.value)?.display_order || 0
      : categories.value.reduce((max, category) => Math.max(max, Number(category.display_order) || 0), 0) + 1
  };

  try {
    if (editingId.value) {
      await catalogueApi.updateCategory(editingId.value, payload);
      toast.success('Service category updated.');
    } else {
      await catalogueApi.createCategory(payload);
      toast.success('Service category created and published.');
    }
    publicStore.invalidate('categories');
    publicStore.invalidate('services');
    resetForm();
    await loadData();
  } catch (error) {
    toast.error(extractErrorMessage(error, 'Failed to save service category.'));
  } finally {
    saving.value = false;
  }
}

async function reviewRequest(categoryRequest, decision) {
  reviewingId.value = categoryRequest.id;
  try {
    if (decision === 'approve') {
      await catalogueApi.approveCategoryRequest(categoryRequest.id);
      publicStore.invalidate('categories');
      publicStore.invalidate('services');
      toast.success('Category approved and published.');
    } else {
      await catalogueApi.rejectCategoryRequest(categoryRequest.id, {});
      toast.success('Category request rejected.');
    }
    await loadData();
  } catch (error) {
    toast.error(extractErrorMessage(error, `Failed to ${decision} category request.`));
  } finally {
    reviewingId.value = null;
  }
}

function statusClass(status) {
  return {
    pending: 'bg-amber-500/10 text-amber-400',
    approved: 'bg-emerald-500/10 text-emerald-400',
    rejected: 'bg-rose-500/10 text-rose-400'
  }[status] || 'bg-theme-surface-subtle text-theme-text-muted';
}

function formatDate(value) {
  return value ? new Date(value).toLocaleDateString() : '';
}

onMounted(loadData);
</script>
