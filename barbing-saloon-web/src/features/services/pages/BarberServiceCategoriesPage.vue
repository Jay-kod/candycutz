<template>
  <BarberLayout>
    <main class="mx-auto max-w-7xl space-y-8 px-4 py-6 sm:px-6 lg:px-8">
      <header class="rounded-3xl border border-theme-border bg-theme-surface p-6 sm:p-8">
        <p class="text-xs font-bold uppercase tracking-[0.25em] text-gold">Service catalogue</p>
        <h1 class="mt-2 font-display text-3xl font-bold text-theme-text">Service Categories</h1>
        <p class="mt-2 max-w-2xl text-sm text-theme-text-muted">
          Categories organize services on the customer service pages. Request a category if you need one that is not listed; an admin must approve it before it becomes shared.
        </p>
      </header>

      <section class="grid gap-8 lg:grid-cols-[minmax(0,0.8fr)_minmax(0,1.2fr)]">
        <form class="space-y-5 rounded-3xl border border-theme-border bg-theme-surface p-6" @submit.prevent="submitRequest">
          <div>
            <h2 class="text-xl font-bold text-theme-text">Request a category</h2>
            <p class="mt-1 text-sm text-theme-text-muted">Your request will be sent to an admin for review.</p>
          </div>

          <label class="block space-y-2">
            <span class="text-sm font-semibold text-theme-text">Category name</span>
            <input v-model.trim="form.name" required maxlength="150" class="w-full rounded-xl border border-theme-border bg-theme-surface-subtle px-4 py-3 text-theme-text outline-none focus:border-gold" placeholder="e.g. Beard Grooming">
          </label>

          <div class="space-y-2">
            <div class="flex items-center justify-between">
              <span class="text-sm font-semibold text-theme-text">Category icon <span class="font-normal text-theme-text-muted">(optional)</span></span>
              <span class="text-xs text-theme-text-muted">Select a real visual icon</span>
            </div>
            <CategoryIconPicker v-model="form.icon" color-theme="gold" />
          </div>

          <button type="submit" :disabled="submitting" class="rounded-xl bg-gold px-5 py-3 font-bold text-obsidian transition disabled:opacity-50">
            {{ submitting ? 'Submitting…' : 'Submit for review' }}
          </button>
        </form>

        <section class="rounded-3xl border border-theme-border bg-theme-surface p-6">
          <div class="mb-5 flex items-center justify-between gap-4">
            <div>
              <h2 class="text-xl font-bold text-theme-text">Available categories</h2>
              <p class="mt-1 text-sm text-theme-text-muted">Approved categories can be selected when creating a service.</p>
            </div>
            <span class="rounded-full bg-theme-surface-subtle px-3 py-1 text-sm font-bold text-theme-text">{{ categories.length }}</span>
          </div>

          <p v-if="loading" class="py-8 text-center text-sm text-theme-text-muted">Loading categories…</p>
          <p v-else-if="!categories.length" class="rounded-2xl border border-dashed border-theme-border p-6 text-center text-sm text-theme-text-muted">
            No categories are available yet. Submit a request and an admin can publish it.
          </p>
          <div v-else class="divide-y divide-theme-border">
            <article v-for="category in categories" :key="category.id" class="flex items-center justify-between gap-4 py-4">
              <div class="flex items-center gap-3.5 min-w-0">
                <div class="flex size-10 shrink-0 items-center justify-center rounded-xl border border-gold/30 bg-gold/10 text-gold">
                  <CategoryIcon :name="category.icon" custom-class="size-5" />
                </div>
                <div class="min-w-0">
                  <h3 class="truncate font-semibold text-theme-text">{{ category.name }}</h3>
                  <p class="mt-1 text-xs text-theme-text-muted">{{ category.services_count || 0 }} approved services</p>
                </div>
              </div>
              <span class="shrink-0 text-xs font-semibold text-emerald-400">Available</span>
            </article>
          </div>
        </section>
      </section>

      <section class="rounded-3xl border border-theme-border bg-theme-surface p-6">
        <h2 class="text-xl font-bold text-theme-text">My category requests</h2>
        <p class="mb-5 mt-1 text-sm text-theme-text-muted">Track the review status of categories you have submitted.</p>

        <p v-if="loading" class="py-8 text-center text-sm text-theme-text-muted">Loading requests…</p>
        <p v-else-if="!requests.length" class="rounded-2xl border border-dashed border-theme-border p-6 text-center text-sm text-theme-text-muted">
          You have not submitted any category requests.
        </p>
        <div v-else class="space-y-3">
          <article v-for="request in requests" :key="request.id" class="flex flex-col gap-2 rounded-2xl border border-theme-border p-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3 min-w-0">
              <div class="flex size-9 shrink-0 items-center justify-center rounded-xl border border-theme-border bg-theme-surface-subtle text-gold">
                <CategoryIcon :name="request.icon" custom-class="size-4" />
              </div>
              <div class="min-w-0">
                <h3 class="font-semibold text-theme-text">{{ request.name }}</h3>
                <p class="mt-1 text-sm text-theme-text-muted">{{ formatDate(request.created_at) }}</p>
                <p v-if="request.review_note" class="mt-1 text-sm text-theme-text-muted">{{ request.review_note }}</p>
              </div>
            </div>
            <span class="w-fit rounded-full px-3 py-1 text-xs font-bold capitalize" :class="statusClass(request.status)">{{ request.status }}</span>
          </article>
        </div>
      </section>
    </main>
  </BarberLayout>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import BarberLayout from '@/portals/barber/layouts/BarberLayout.vue';
import CategoryIcon from '@/shared/ui/CategoryIcon.vue';
import CategoryIconPicker from '@/shared/ui/CategoryIconPicker.vue';
import { catalogueApi } from '@/features/catalogue/api';
import { useToast } from '@/core/composables/useToast';

const toast = useToast();
const categories = ref([]);
const requests = ref([]);
const loading = ref(true);
const submitting = ref(false);
const form = ref({ name: '', icon: 'scissors' });

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

async function submitRequest() {
  submitting.value = true;
  try {
    await catalogueApi.requestCategory({
      name: form.value.name.trim(),
      icon: form.value.icon ? form.value.icon.trim() : null
    });
    form.value = { name: '', icon: 'scissors' };
    toast.success('Category request sent to an admin for review.');
    await loadData();
  } catch (error) {
    toast.error(error.response?.data?.message || 'Failed to submit category request.');
  } finally {
    submitting.value = false;
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
