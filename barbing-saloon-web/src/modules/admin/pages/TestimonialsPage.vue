<template>
  <AdminLayout>
    <section class="space-y-6 animate-fade-in">
      <!-- Header Banner -->
      <div class="relative overflow-hidden rounded-3xl border border-admin/20 bg-gradient-to-br from-obsidian via-charcoal to-[#2A1100] p-8 shadow-2xl flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="absolute -right-16 -top-16 h-64 w-64 rounded-full bg-admin/5 blur-3xl"></div>
        <div class="absolute -left-16 -bottom-16 h-48 w-48 rounded-full bg-admin/5 blur-3xl"></div>
        
        <div class="relative z-10">
          <p class="text-xs uppercase tracking-[0.3em] text-admin/70 font-bold">Testimonials</p>
          <h1 class="mt-2 font-display text-4xl text-theme-text drop-shadow-lg flex items-center gap-3">
            Review <span class="text-transparent bg-clip-text bg-gradient-to-r from-admin to-admin-light">Moderation</span>
            <span class="flex items-center justify-center h-8 px-3 rounded-full bg-admin/20 border border-admin/30 text-lg text-admin-light">{{ testimonials.length }}</span>
          </h1>
          <p class="mt-2 text-sm text-ivory/60">Manage and approve client feedback before it goes live on the public site.</p>
        </div>
      </div>

      <div class="grid gap-5">
        <article 
          v-for="item in testimonials" 
          :key="item.id" 
          class="group relative overflow-hidden rounded-2xl border bg-black/20 p-6 backdrop-blur-sm transition-all duration-300 hover:bg-black/40"
          :class="item.is_approved ? 'border-white/5 hover:border-admin/20' : 'border-admin/30 bg-admin/5'"
        >
          <div class="absolute inset-0 bg-gradient-to-br from-admin/0 via-admin/[0.02] to-admin/5 opacity-0 transition-opacity duration-300 group-hover:opacity-100"></div>
          
          <div class="relative z-10 flex flex-col md:flex-row md:items-start justify-between gap-6">
            <div class="flex-1">
              <div class="flex items-center gap-4 mb-3">
                <div class="h-12 w-12 rounded-full bg-admin/20 border border-admin/30 flex items-center justify-center text-admin font-display text-xl shrink-0">
                  {{ item.client_name.charAt(0).toUpperCase() }}
                </div>
                <div>
                  <h2 class="font-display text-xl text-theme-text group-hover:text-admin-light transition-colors">{{ item.client_name }}</h2>
                  <div class="flex items-center gap-2">
                    <div class="flex text-admin">
                      <span v-for="i in 5" :key="i">
                        <svg v-if="i <= item.rating" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4"><path fill-rule="evenodd" d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.007 5.404.433c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.433 2.082-5.006z" clip-rule="evenodd" /></svg>
                        <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 opacity-30"><path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385c.148.621-.531 1.114-1.005.777l-4.783-2.738a.562.562 0 00-.554 0l-4.783 2.738c-.474.337-1.153-.156-1.005-.777l1.285-5.385a.563.563 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" /></svg>
                      </span>
                    </div>
                    <span class="text-xs text-ivory/40 flex items-center gap-1">
                      <ClockIcon class="h-3.5 w-3.5" />
                      {{ new Date(item.created_at).toLocaleDateString() }}
                    </span>
                  </div>
                </div>
              </div>
              
              <div class="relative">
                <ChatBubbleBottomCenterTextIcon class="absolute -left-2 -top-2 h-8 w-8 text-white/5 rotate-180" />
                <p class="text-ivory/80 leading-relaxed italic relative z-10 pl-6 border-l-2 border-admin/20 py-1">"{{ item.comment }}"</p>
              </div>
            </div>

            <!-- Actions -->
            <div class="flex flex-col gap-2 min-w-[140px] shrink-0 border-t md:border-t-0 md:border-l border-white/10 pt-4 md:pt-0 md:pl-6">
              <div class="flex items-center gap-2 mb-2">
                <div class="h-2 w-2 rounded-full" :class="item.is_approved ? 'bg-emerald-500' : 'bg-admin animate-pulse'"></div>
                <span class="text-xs font-bold uppercase tracking-wider" :class="item.is_approved ? 'text-emerald-400' : 'text-admin'">
                  {{ item.is_approved ? 'Approved' : 'Pending' }}
                </span>
              </div>

              <button 
                v-if="!item.is_approved" 
                @click="toggleApproval(item)" 
                class="flex items-center justify-center gap-2 rounded-xl bg-admin/10 border border-admin/30 text-admin-light px-4 py-2 text-sm font-medium hover:bg-admin hover:text-obsidian hover:border-admin transition-all"
              >
                <CheckCircleIcon class="h-4 w-4" />
                Approve
              </button>
              
              <button 
                @click="deleteReview(item.id)" 
                class="flex items-center justify-center gap-2 rounded-xl border border-red-500/20 bg-red-500/5 text-red-400 px-4 py-2 text-sm font-medium hover:bg-red-500 hover:text-white hover:border-red-500 transition-all"
              >
                <TrashIcon class="h-4 w-4" />
                Delete
              </button>
            </div>
          </div>
        </article>

        <div v-if="testimonials.length === 0" class="col-span-full flex flex-col items-center justify-center py-20 text-center border border-dashed border-white/10 rounded-3xl bg-theme-surface/30">
          <ChatBubbleBottomCenterTextIcon class="h-12 w-12 text-ivory/20 mb-4" />
          <p class="text-ivory/60 font-medium">No testimonials found.</p>
          <p class="text-sm text-ivory/40 mt-1">When clients leave reviews, they will appear here for moderation.</p>
        </div>
      </div>
    </section>
  </AdminLayout>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import AdminLayout from '../layouts/AdminLayout.vue';
import { adminApi } from '../api/admin.api';
import { useConfirm } from '../../../core/composables/useConfirm';
import { useToast } from '../../../core/composables/useToast';
import { ChatBubbleBottomCenterTextIcon, CheckCircleIcon, TrashIcon, ClockIcon } from '@heroicons/vue/24/outline';

const { confirm } = useConfirm();
const toast = useToast();

const testimonials = ref([]);

const loadTestimonials = async () => {
  try {
    const response = await adminApi.testimonials();
    testimonials.value = response.data.data;
  } catch (error) {
    console.error("Failed to load testimonials", error);
    toast.error('Failed to load testimonials');
  }
};

const toggleApproval = async (item) => {
  try {
    await adminApi.approveTestimonial(item.id);
    item.is_approved = true; 
    toast.success('Testimonial approved successfully');
  } catch (error) {
    console.error("Failed to update approval", error);
    toast.error('Failed to approve testimonial');
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
      console.error("Failed to delete", error);
      toast.error('Failed to delete testimonial');
    }
  }
};

onMounted(() => {
  loadTestimonials();
});
</script>