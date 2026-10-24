<template>
  <AdminLayout>
    <section class="space-y-6 animate-fade-in">
      <!-- Header Banner -->
      <div class="relative overflow-hidden rounded-3xl border border-admin/20 bg-gradient-to-br from-obsidian via-charcoal to-[#2A1100] p-8 shadow-2xl flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="absolute -right-16 -top-16 h-64 w-64 rounded-full bg-admin/5 blur-3xl"></div>
        <div class="absolute -left-16 -bottom-16 h-48 w-48 rounded-full bg-admin/5 blur-3xl"></div>
        
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
          <div>
            <p class="text-xs uppercase tracking-[0.3em] text-admin/70 font-bold">Testimonials</p>
            <h1 class="mt-2 font-display text-4xl text-theme-text drop-shadow-lg flex items-center gap-3">
              Review <span class="text-transparent bg-clip-text bg-gradient-to-r from-admin to-admin-light">Moderation</span>
              <span class="flex items-center justify-center h-8 px-3 rounded-full bg-admin/20 border border-admin/30 text-lg text-admin-light">{{ filteredTestimonials.length }}</span>
            </h1>
            <p class="mt-2 text-sm text-ivory/60">Approve reviews and separately choose which approved testimonials appear on the website.</p>
          </div>

          <!-- Filters Bar -->
          <div class="flex items-center gap-3 flex-wrap">
            <div class="flex rounded-xl bg-black/40 p-1 border border-white/10">
              <button
                @click="activeStatusFilter = 'all'"
                class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all"
                :class="activeStatusFilter === 'all' ? 'bg-admin text-obsidian shadow' : 'text-white/60 hover:text-white'"
              >
                All
              </button>
              <button
                @click="activeStatusFilter = 'pending'"
                class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all flex items-center gap-1.5"
                :class="activeStatusFilter === 'pending' ? 'bg-admin text-obsidian shadow' : 'text-white/60 hover:text-white'"
              >
                Pending
                <span v-if="pendingCount > 0" class="h-4 w-4 rounded-full bg-red-500 text-white text-[10px] flex items-center justify-center font-extrabold">{{ pendingCount }}</span>
              </button>
              <button
                @click="activeStatusFilter = 'approved'"
                class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all"
                :class="activeStatusFilter === 'approved' ? 'bg-admin text-obsidian shadow' : 'text-white/60 hover:text-white'"
              >
                Approved
              </button>
            </div>

            <select
              v-model="activeServiceFilter"
              class="bg-black/60 border border-admin/30 text-white text-xs rounded-xl px-3 py-2 focus:outline-none focus:border-admin"
            >
              <option value="all">All Services</option>
              <option v-for="s in services" :key="s.id" :value="s.id">{{ s.name }}</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Testimonial Flow Motion Controls (Speed & Direction) -->
      <TestimonialsFlowSettingsCard />

      <!-- Reviews Moderation List -->
      <div class="grid gap-5">
        <div v-if="isLoading" class="rounded-2xl border border-white/10 bg-theme-surface/30 px-6 py-12 text-center text-sm text-ivory/60">
          Loading testimonials...
        </div>

        <div v-else-if="loadError" class="rounded-2xl border border-red-500/20 bg-red-500/5 px-6 py-8 text-center text-sm text-red-300">
          Unable to load testimonials. Please refresh the page and try again.
        </div>

        <AdminTestimonialCard
          v-else
          v-for="item in filteredTestimonials"
          :key="item.id"
          :item="item"
          :is-moderating="moderatingIds.has(item.id)"
          @toggle-approval="toggleApproval"
          @toggle-visibility="toggleWebsiteVisibility"
          @delete="deleteReview"
        />

        <div v-if="!isLoading && !loadError && testimonials.length === 0" class="col-span-full flex flex-col items-center justify-center py-20 text-center border border-dashed border-white/10 rounded-3xl bg-theme-surface/30">
          <ChatBubbleBottomCenterTextIcon class="h-12 w-12 text-ivory/20 mb-4" />
          <p class="text-ivory/60 font-medium">No testimonials found.</p>
          <p class="text-sm text-ivory/40 mt-1">When clients leave reviews, they will appear here for moderation.</p>
        </div>

        <div v-else-if="!isLoading && !loadError && filteredTestimonials.length === 0" class="rounded-2xl border border-dashed border-white/10 bg-theme-surface/30 px-6 py-10 text-center text-sm text-ivory/50">
          No testimonials match the selected filters.
        </div>
      </div>
    </section>
  </AdminLayout>
</template>

<script setup>
import { onMounted, ref, computed } from 'vue';
import AdminLayout from '@/portals/admin/layouts/AdminLayout.vue';
import client from '@/shared/api/client';
import { adminApi } from '@/shared/api/old_adminApi';
import { useConfirm } from '../../../core/composables/useConfirm';
import { useToast } from '../../../core/composables/useToast';
import { ChatBubbleBottomCenterTextIcon } from '@heroicons/vue/24/outline';
import TestimonialsFlowSettingsCard from '../components/TestimonialsFlowSettingsCard.vue';
import AdminTestimonialCard from '../components/AdminTestimonialCard.vue';

const { confirm } = useConfirm();
const toast = useToast();

const testimonials = ref([]);
const services = ref([]);
const activeStatusFilter = ref('all');
const activeServiceFilter = ref('all');
const isLoading = ref(true);
const loadError = ref(false);
const moderatingIds = ref(new Set());

const pendingCount = computed(() => {
  return testimonials.value.filter(t => !t.is_approved).length;
});

const filteredTestimonials = computed(() => {
  let list = testimonials.value;
  if (activeStatusFilter.value === 'pending') {
    list = list.filter(t => !t.is_approved);
  } else if (activeStatusFilter.value === 'approved') {
    list = list.filter(t => t.is_approved);
  }

  if (activeServiceFilter.value !== 'all') {
    list = list.filter(t => (t.service_id == activeServiceFilter.value || t.service?.id == activeServiceFilter.value));
  }

  return list;
});

const loadServices = async () => {
  try {
    const response = await client.get('/services');
    services.value = response.data?.data || response.data || [];
  } catch (err) {
    console.error('Failed to load services', err);
  }
};

const loadTestimonials = async () => {
  isLoading.value = true;
  loadError.value = false;
  try {
    const response = await adminApi.testimonials();
    const payload = response.data?.data;
    const items = Array.isArray(payload)
      ? payload
      : Array.isArray(payload?.data)
        ? payload.data
        : null;

    if (!items) {
      throw new TypeError('The testimonials response did not contain a list.');
    }

    testimonials.value = items;
  } catch (error) {
    console.error('Failed to load testimonials', error);
    loadError.value = true;
    toast.error('Failed to load testimonials');
  } finally {
    isLoading.value = false;
  }
};

const toggleApproval = async (item) => {
  const isApproved = !item.is_approved;
  moderatingIds.value = new Set(moderatingIds.value).add(item.id);
  try {
    const response = await adminApi.approveTestimonial(item.id, isApproved);
    item.is_approved = response.data?.data?.is_approved ?? isApproved;
    toast.success(isApproved ? 'Testimonial approved' : 'Testimonial unapproved and hidden from the website');
  } catch (error) {
    console.error('Failed to update testimonial approval', error);
    toast.error(isApproved ? 'Failed to approve testimonial' : 'Failed to unapprove testimonial');
  } finally {
    const nextIds = new Set(moderatingIds.value);
    nextIds.delete(item.id);
    moderatingIds.value = nextIds;
  }
};

const toggleWebsiteVisibility = async (item) => {
  if (!item.is_approved) {
    toast.error('Approve this testimonial before displaying it');
    return;
  }

  const isFeatured = !item.is_featured;
  moderatingIds.value = new Set(moderatingIds.value).add(item.id);
  try {
    const response = await adminApi.featureTestimonial(item.id, isFeatured);
    item.is_featured = response.data?.data?.is_featured ?? isFeatured;
    toast.success(isFeatured ? 'Testimonial is now visible on the website' : 'Testimonial hidden from the website');
  } catch (error) {
    console.error('Failed to update testimonial website visibility', error);
    toast.error(isFeatured ? 'Failed to display testimonial on the website' : 'Failed to hide testimonial from the website');
  } finally {
    const nextIds = new Set(moderatingIds.value);
    nextIds.delete(item.id);
    moderatingIds.value = nextIds;
  }
};

const deleteReview = async (id) => {
  const ok = await confirm({ 
    title: 'Delete Testimonial', 
    message: 'Are you sure you want to delete this testimonial? This action cannot be undone.', 
    confirmText: 'Delete',
    type: 'danger'
  });
  
  if (ok) {
    try {
      await adminApi.deleteTestimonial(id);
      testimonials.value = testimonials.value.filter(t => t.id !== id);
      toast.success('Testimonial deleted');
    } catch (error) {
      console.error('Failed to delete', error);
      toast.error('Failed to delete testimonial');
    }
  }
};

onMounted(() => {
  loadServices();
  loadTestimonials();
});
</script>
