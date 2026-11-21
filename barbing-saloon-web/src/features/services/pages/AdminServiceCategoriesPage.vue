<template>
  <AdminLayout>
    <main class="mx-auto max-w-7xl space-y-8 px-4 py-6 sm:px-6 lg:px-8">
      <header class="rounded-3xl border border-theme-border bg-theme-surface p-6 sm:p-8">
        <p class="text-xs font-bold uppercase tracking-[0.25em] text-admin">Service catalogue</p>
        <h1 class="mt-2 font-display text-3xl font-bold text-theme-text">Service Categories</h1>
        <p class="mt-2 max-w-2xl text-sm text-theme-text-muted">
          Create the shared categories used to organize services on the customer service pages, and review barber requests.
        </p>
      </header>

      <section class="grid gap-8 xl:grid-cols-[minmax(0,0.8fr)_minmax(0,1.2fr)]">
        <form class="space-y-5 rounded-3xl border border-theme-border bg-theme-surface p-6" @submit.prevent="saveCategory">
          <div>
            <h2 class="text-xl font-bold text-theme-text">{{ editingId ? 'Edit category' : 'Create category' }}</h2>
            <p class="mt-1 text-sm text-theme-text-muted">Admin-created categories are available to services immediately.</p>
          </div>

          <label class="block space-y-2">
            <span class="text-sm font-semibold text-theme-text">Category name</span>
            <input v-model.trim="form.name" required maxlength="150" class="w-full rounded-xl border border-theme-border bg-theme-surface-subtle px-4 py-3 text-theme-text outline-none focus:border-admin" placeholder="e.g. Beard Grooming">
          </label>

          <label class="block space-y-2">
            <span class="text-sm font-semibold text-theme-text">Icon name <span class="font-normal text-theme-text-muted">(optional)</span></span>
            <input v-model.trim="form.icon" maxlength="100" class="w-full rounded-xl border border-theme-border bg-theme-surface-subtle px-4 py-3 text-theme-text outline-none focus:border-admin" placeholder="e.g. scissors">
          </label>

          <div class="flex flex-wrap gap-3">
            <button type="submit" :disabled="saving" class="rounded-xl bg-admin px-5 py-3 font-bold text-white transition disabled:opacity-50">
              {{ saving ? 'Saving…' : editingId ? 'Save changes' : 'Create category' }}
            </button>
            <button v-if="editingId" type="button" class="rounded-xl border border-theme-border px-5 py-3 font-semibold text-theme-text" @click="resetForm">
              Cancel
            </button>
          </div>
        </form>

        <section class="rounded-3xl border border-theme-border bg-theme-surface p-6">
          <div class="mb-5 flex items-center justify-between gap-4">
            <div>
              <h2 class="text-xl font-bold text-theme-text">Published categories</h2>
              <p class="mt-1 text-sm text-theme-text-muted">Categories currently available on service forms.</p>
            </div>
            <span class="rounded-full bg-theme-surface-subtle px-3 py-1 text-sm font-bold text-theme-text">{{ categories.length }}</span>
          </div>

          <p v-if="loading" class="py-8 text-center text-sm text-theme-text-muted">Loading categories…</p>
          <p v-else-if="!categories.length" class="rounded-2xl border border-dashed border-theme-border p-6 text-center text-sm text-theme-text-muted">
            No service categories yet. Create one to make it available in service forms.
          </p>
          <div v-else class="divide-y divide-theme-border">
            <article v-for="category in categories" :key="category.id" class="flex items-center justify-between gap-4 py-4">
              <div class="min-w-0">
                <h3 class="truncate font-semibold text-theme-text">{{ category.name }}</h3>
                <p class="mt-1 text-xs text-theme-text-muted">{{ category.services_count || 0 }} approved services · {{ category.slug }}</p>
              </div>
              <button type="button" class="shrink-0 rounded-lg border border-theme-border px-3 py-2 text-sm font-semibold text-theme-text hover:bg-theme-surface-subtle" @click="editCategory(category)">
                Edit
              </button>
            </article>
          </div>
        </section>
      </section>

      <section class="rounded-3xl border border-theme-border bg-theme-surface p-6">
        <div class="mb-5 flex items-center justify-between gap-4">
          <div>
            <h2 class="text-xl font-bold text-theme-text">Barber category requests</h2>
            <p class="mt-1 text-sm text-theme-text-muted">Approve a request to publish it for the shared service catalogue.</p>
          </div>
          <span class="rounded-full bg-amber-500/10 px-3 py-1 text-sm font-bold text-amber-400">{{ pendingRequests.length }} pending</span>
        </div>

        <p v-if="loading" class="py-8 text-center text-sm text-theme-text-muted">Loading requests…</p>
        <p v-else-if="!requests.length" class="rounded-2xl border border-dashed border-theme-border p-6 text-center text-sm text-theme-text-muted">
          No barber category requests yet.
        </p>
        <div v-else class="space-y-3">
          <article v-for="request in requests" :key="request.id" class="flex flex-col gap-4 rounded-2xl border border-theme-border p-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="min-w-0">
              <div class="flex flex-wrap items-center gap-2">
                <h3 class="font-semibold text-theme-text">{{ request.name }}</h3>
                <span class="rounded-full px-2.5 py-1 text-xs font-bold capitalize" :class="statusClass(request.status)">{{ request.status }}</span>
              </div>
              <p class="mt-1 text-sm text-theme-text-muted">
                Requested by {{ request.requester?.name || 'Barber' }} · {{ formatDate(request.created_at) }}
              </p>
              <p v-if="request.review_note" class="mt-1 text-sm text-theme-text-muted">{{ request.review_note }}</p>
            </div>
            <div v-if="request.status === 'pending'" class="flex shrink-0 gap-2">
              <button type="button" :disabled="reviewingId === request.id" class="rounded-lg bg-emerald-500/15 px-3 py-2 text-sm font-bold text-emerald-400 disabled:opacity-50" @click="reviewRequest(request, 'approve')">
                Approve
              </button>
              <button type="button" :disabled="reviewingId === request.id" class="rounded-lg bg-rose-500/15 px-3 py-2 text-sm font-bold text-rose-400 disabled:opacity-50" @click="reviewRequest(request, 'reject')">
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
import { catalogueApi } from '@/features/catalogue/api';
import { useToast } from '@/core/composables/useToast';

const toast = useToast();
const categories = ref([]);
const requests = ref([]);
const loading = ref(true);
const saving = ref(false);
const reviewingId = ref(null);
const editingId = ref(null);
const form = ref({ name: '', icon: '' });
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
  form.value = { name: '', icon: '' };
}

function editCategory(category) {
  editingId.value = category.id;
  form.value = { name: category.name, icon: category.icon || '' };
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
    icon: form.value.icon.trim() || null,
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
    resetForm();
    await loadData();
  } catch (error) {
    toast.error(error.response?.data?.message || 'Failed to save service category.');
  } finally {
    saving.value = false;
  }
}

async function reviewRequest(categoryRequest, decision) {
  reviewingId.value = categoryRequest.id;
  try {
    if (decision === 'approve') {
      await catalogueApi.approveCategoryRequest(categoryRequest.id);
      toast.success('Category approved and published.');
    } else {
      await catalogueApi.rejectCategoryRequest(categoryRequest.id, {});
      toast.success('Category request rejected.');
    }
    await loadData();
  } catch (error) {
    toast.error(error.response?.data?.message || `Failed to ${decision} category request.`);
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
