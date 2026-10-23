<template>
  <AdminLayout>
    <section class="space-y-6 animate-fade-in">
      <header class="relative overflow-hidden rounded-3xl border border-admin/20 bg-gradient-to-br from-obsidian via-charcoal to-[#2A1100] p-6 shadow-2xl sm:p-8">
        <div class="absolute -right-16 -top-16 size-64 rounded-full bg-admin/5 blur-3xl" aria-hidden="true"></div>
        <div class="relative flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
          <div>
            <p class="text-xs font-bold uppercase tracking-[0.25em] text-admin/80">Private Customer Feedback</p>
            <h1 class="mt-2 font-display text-3xl text-theme-text sm:text-4xl">Feedback Inbox</h1>
            <p class="mt-2 text-sm text-ivory/60">Review suggestions and maintenance reports. This inbox is visible only to administrators.</p>
          </div>
          <div class="flex flex-wrap gap-3">
            <select v-model="filters.category" class="min-h-10 rounded-xl border border-admin/20 bg-black/40 px-3 text-sm text-theme-text" @change="loadSubmissions(1)">
              <option value="">All topics</option>
              <option value="feature_request">Feature requests</option>
              <option value="maintenance">Maintenance</option>
              <option value="improvement">Improvements</option>
            </select>
            <select v-model="filters.status" class="min-h-10 rounded-xl border border-admin/20 bg-black/40 px-3 text-sm text-theme-text" @change="loadSubmissions(1)">
              <option value="">All statuses</option>
              <option v-for="status in statuses" :key="status.value" :value="status.value">{{ status.label }}</option>
            </select>
            <button type="button" class="min-h-10 rounded-xl border border-admin/20 px-4 text-sm font-semibold text-admin-light hover:bg-admin/10" :disabled="loading" @click="loadSubmissions(page)">
              Refresh
            </button>
          </div>
        </div>
      </header>

      <div v-if="errorMessage" class="rounded-xl border border-red-500/30 bg-red-500/10 p-4 text-sm text-red-300" role="alert">
        {{ errorMessage }}
      </div>

      <div v-if="loading" class="rounded-2xl border border-white/10 bg-black/20 p-8 text-center text-sm text-ivory/60" role="status">
        Loading feedback...
      </div>

      <div v-else-if="submissions.length === 0" class="rounded-2xl border border-dashed border-white/15 bg-black/10 p-10 text-center">
        <ChatBubbleLeftRightIcon class="mx-auto size-10 text-admin/60" />
        <h2 class="mt-3 font-display text-xl text-theme-text">No submissions found</h2>
        <p class="mt-1 text-sm text-ivory/50">Customer feedback will appear here after it is submitted.</p>
      </div>

      <article v-for="item in submissions" :key="item.id" class="rounded-2xl border border-white/10 bg-black/20 p-5 sm:p-6">
        <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
          <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-center gap-2">
              <span class="rounded-full border border-admin/20 bg-admin/10 px-3 py-1 text-xs font-semibold text-admin-light">{{ categoryLabel(item.category) }}</span>
              <span class="text-xs text-ivory/40">{{ formatDate(item.created_at) }}</span>
            </div>
            <h2 class="mt-3 break-words font-display text-xl text-theme-text">{{ item.title }}</h2>
            <p class="mt-3 whitespace-pre-wrap break-words text-sm leading-6 text-ivory/75">{{ item.description }}</p>
            <p class="mt-4 text-xs text-ivory/45">
              From <span class="font-semibold text-ivory/70">{{ item.user?.name || 'Deleted customer' }}</span>
              <span v-if="item.user?.email"> · {{ item.user.email }}</span>
            </p>
          </div>

          <div class="w-full space-y-3 rounded-xl border border-white/10 bg-black/20 p-4 lg:max-w-sm">
            <div>
              <label :for="`feedback-status-${item.id}`" class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-ivory/50">Status</label>
              <select :id="`feedback-status-${item.id}`" v-model="drafts[item.id].status" class="min-h-10 w-full rounded-lg border border-white/10 bg-obsidian px-3 text-sm text-theme-text">
                <option v-for="status in statuses" :key="status.value" :value="status.value">{{ status.label }}</option>
              </select>
            </div>
            <div>
              <label :for="`feedback-note-${item.id}`" class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-ivory/50">Internal note</label>
              <textarea :id="`feedback-note-${item.id}`" v-model="drafts[item.id].admin_note" rows="3" maxlength="5000" placeholder="Visible to admins only" class="w-full resize-y rounded-lg border border-white/10 bg-obsidian px-3 py-2 text-sm text-theme-text placeholder:text-ivory/30"></textarea>
            </div>
            <button
              type="button"
              class="min-h-10 w-full rounded-lg bg-admin px-4 py-2 text-sm font-bold text-obsidian transition hover:bg-admin-light disabled:cursor-not-allowed disabled:opacity-50"
              :disabled="savingId === item.id"
              @click="saveSubmission(item)"
            >
              {{ savingId === item.id ? 'Saving...' : 'Save review' }}
            </button>
          </div>
        </div>
      </article>

      <div v-if="lastPage > 1" class="flex items-center justify-between rounded-xl border border-white/10 bg-black/10 px-4 py-3">
        <p class="text-xs text-ivory/50">Page {{ page }} of {{ lastPage }}</p>
        <div class="flex gap-2">
          <button type="button" class="rounded-lg border border-white/10 px-3 py-2 text-sm text-theme-text disabled:opacity-40" :disabled="page <= 1 || loading" @click="loadSubmissions(page - 1)">Previous</button>
          <button type="button" class="rounded-lg border border-white/10 px-3 py-2 text-sm text-theme-text disabled:opacity-40" :disabled="page >= lastPage || loading" @click="loadSubmissions(page + 1)">Next</button>
        </div>
      </div>
    </section>
  </AdminLayout>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue';
import { ChatBubbleLeftRightIcon } from '@heroicons/vue/24/outline';
import AdminLayout from '@/portals/admin/layouts/AdminLayout.vue';
import { adminApi } from '@/shared/api/old_adminApi';
import { useToast } from '@/core/composables/useToast';

const toast = useToast();
const submissions = ref([]);
const loading = ref(false);
const savingId = ref(null);
const errorMessage = ref('');
const page = ref(1);
const lastPage = ref(1);
const filters = reactive({ category: '', status: '' });
const drafts = reactive({});

const statuses = [
  { value: 'new', label: 'New' },
  { value: 'reviewing', label: 'Reviewing' },
  { value: 'planned', label: 'Planned' },
  { value: 'completed', label: 'Completed' },
  { value: 'declined', label: 'Declined' },
];

const categoryLabel = (category) => ({
  feature_request: 'Feature request',
  maintenance: 'Maintenance',
  improvement: 'Improvement',
}[category] || category);

const formatDate = (value) => value
  ? new Intl.DateTimeFormat(undefined, { dateStyle: 'medium', timeStyle: 'short' }).format(new Date(value))
  : 'Date unavailable';

const loadSubmissions = async (requestedPage) => {
  loading.value = true;
  errorMessage.value = '';
  try {
    const params = { page: requestedPage };
    if (filters.category) params.category = filters.category;
    if (filters.status) params.status = filters.status;
    const response = await adminApi.feedbackSubmissions(params);
    submissions.value = response.data?.data || [];
    page.value = response.data?.meta?.current_page || requestedPage;
    lastPage.value = response.data?.meta?.last_page || 1;
    for (const submission of submissions.value) {
      drafts[submission.id] = {
        status: submission.status,
        admin_note: submission.admin_note || '',
      };
    }
  } catch (error) {
    console.error('Failed to load customer feedback submissions:', error);
    errorMessage.value = 'Feedback submissions could not be loaded. Please refresh and try again.';
  } finally {
    loading.value = false;
  }
};

const saveSubmission = async (submission) => {
  savingId.value = submission.id;
  try {
    const response = await adminApi.updateFeedbackSubmission(submission.id, drafts[submission.id]);
    const updated = response.data?.data;
    if (updated) {
      if (filters.status && filters.status !== updated.status) {
        await loadSubmissions(page.value);
      } else {
        const index = submissions.value.findIndex(item => item.id === submission.id);
        if (index !== -1) submissions.value[index] = updated;
        drafts[submission.id] = { status: updated.status, admin_note: updated.admin_note || '' };
      }
    }
    toast.success('Feedback review saved.');
  } catch (error) {
    console.error(`Failed to update feedback submission ${submission.id}:`, error);
    toast.error('Could not save the feedback review.');
  } finally {
    savingId.value = null;
  }
};

onMounted(() => loadSubmissions(1));
</script>
