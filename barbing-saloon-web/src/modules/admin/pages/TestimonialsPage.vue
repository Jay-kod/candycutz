<template>
  <AdminLayout>
    <section class="space-y-6">
      <div class="rounded-3xl border border-theme-border bg-theme-surface p-8">
        <p class="text-sm uppercase tracking-[0.3em] text-theme-muted">Testimonials</p>
        <h1 class="mt-3 font-display text-4xl text-admin">Review moderation</h1>
      </div>

      <div class="grid gap-6">
        <article v-for="item in testimonials" :key="item.id" class="rounded-3xl border border-theme-border bg-theme-surface p-6 shadow-sm hover:shadow-md transition-shadow">
          <div class="flex flex-col md:flex-row md:items-start justify-between gap-4">
            <div>
              <div class="flex items-center gap-3">
                <h2 class="font-display text-2xl text-obsidian dark:text-ivory">{{ item.client_name }}</h2>
                <div class="flex text-admin text-sm">
                  <span v-for="i in 5" :key="i">
                    <svg v-if="i <= item.rating" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4"><path fill-rule="evenodd" d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.007 5.404.433c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.433 2.082-5.006z" clip-rule="evenodd" /></svg>
                    <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385c.148.621-.531 1.114-1.005.777l-4.783-2.738a.562.562 0 00-.554 0l-4.783 2.738c-.474.337-1.153-.156-1.005-.777l1.285-5.385a.563.563 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" /></svg>
                  </span>
                </div>
              </div>
              <p class="mt-4 text-theme-text leading-relaxed">{{ item.comment }}</p>
              <p class="mt-4 text-xs text-theme-muted">{{ new Date(item.created_at).toLocaleDateString() }}</p>
            </div>

            <!-- Actions -->
            <div class="flex flex-col gap-2 min-w-[120px]">
              <span :class="[item.is_approved ? 'bg-green-100 text-green-800 border-green-200' : 'bg-yellow-100 text-yellow-800 border-yellow-200', 'px-3 py-1 rounded-full text-xs font-semibold border text-center mb-2']">
                {{ item.is_approved ? 'Approved' : 'Pending' }}
              </span>

              <button v-if="!item.is_approved" @click="toggleApproval(item)" class="flex items-center justify-center gap-2 rounded-lg bg-obsidian text-ivory px-4 py-2 text-sm font-medium hover:bg-obsidian-light transition-colors">
                Approve
              </button>
              <button v-else @click="toggleApproval(item)" class="flex items-center justify-center gap-2 rounded-lg border border-theme-border bg-theme-bg px-4 py-2 text-sm font-medium hover:bg-theme-surface transition-colors">
                Unapprove
              </button>
              
              <button @click="deleteReview(item.id)" class="flex items-center justify-center gap-2 rounded-lg border border-red-200 bg-red-50 text-red-600 px-4 py-2 text-sm font-medium hover:bg-red-100 transition-colors">
                Delete
              </button>
            </div>
          </div>
        </article>
      </div>
    </section>
  </AdminLayout>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import AdminLayout from '../layouts/AdminLayout.vue';
import { adminApi } from '../api/admin.api';
import { useConfirm } from '../../../core/composables/useConfirm';

const { confirm } = useConfirm();

const testimonials = ref([]);

const loadTestimonials = async () => {
  try {
    const response = await adminApi.testimonials();
    testimonials.value = response.data.data;
  } catch (error) {
    console.error("Failed to load testimonials", error);
  }
};

const toggleApproval = async (item) => {
  try {
    const newStatus = !item.is_approved;
    await adminApi.updateTestimonial(item.id, { is_approved: newStatus });
    item.is_approved = newStatus;
  } catch (error) {
    console.error("Failed to update approval", error);
  }
};

const deleteReview = async (id) => {
  const ok = await confirm({ title: 'Delete Testimonial', message: 'Are you sure you want to delete this testimonial? This action cannot be undone.', confirmText: 'Delete' });
  if (ok) {
    try {
      await adminApi.deleteTestimonial(id);
      testimonials.value = testimonials.value.filter(t => t.id !== id);
    } catch (error) {
      console.error("Failed to delete", error);
    }
  }
};

onMounted(() => {
  loadTestimonials();
});
</script>