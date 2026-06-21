<template>
  <CustomerLayout>
    <section class="animate-fade-in space-y-8">
      <!-- Page Header -->
      <div class="relative overflow-hidden rounded-2xl border border-gold/20 bg-gradient-to-br from-obsidian via-charcoal to-steel p-8">
        <div class="absolute -right-16 -top-16 h-64 w-64 rounded-full bg-gold/5 blur-3xl"></div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-end md:justify-between gap-4">
          <div>
            <p class="text-xs uppercase tracking-[0.3em] text-gold/70 font-medium">Customer Dashboard</p>
            <h1 class="mt-2 font-display text-3xl lg:text-4xl text-ivory">
              Your <span class="text-gold">Reviews</span>
            </h1>
            <p class="mt-2 max-w-xl text-sm text-ivory/50 leading-relaxed">
              Share your experience and review past feedback you've left for your barbers.
            </p>
          </div>
        </div>
      </div>

      <div class="grid gap-6 lg:grid-cols-[0.95fr_1.05fr]">
        <form class="rounded-2xl border border-white/[0.06] bg-charcoal/80 p-8 backdrop-blur-sm" @submit.prevent="submit">
          <h2 class="font-display text-2xl text-ivory">Write a <span class="text-gold">Review</span></h2>
          <div class="mt-6 space-y-5">
            <div class="space-y-2">
              <label class="text-xs font-semibold uppercase tracking-widest text-ivory/50">Barber</label>
              <select v-model="form.barber_id" class="w-full rounded-xl border border-white/[0.08] bg-obsidian px-4 py-3 text-ivory outline-none transition-colors focus:border-gold/50">
                <option value="">Select barber</option>
                <option v-for="barber in barbers" :key="barber.id" :value="barber.id">{{ barber.name }}</option>
              </select>
            </div>
            <div class="space-y-2">
              <label class="text-xs font-semibold uppercase tracking-widest text-ivory/50">Rating (1-5)</label>
              <div class="flex items-center gap-2">
                <button 
                  v-for="star in 5" 
                  :key="star" 
                  type="button" 
                  @click="form.rating = star"
                  class="p-1 hover:scale-110 transition-transform"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-8 w-8 transition-colors" :class="star <= form.rating ? 'text-gold' : 'text-white/10'">
                    <path fill-rule="evenodd" d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.007 5.404.433c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.433 2.082-5.006z" clip-rule="evenodd" />
                  </svg>
                </button>
              </div>
            </div>
            <div class="space-y-2">
              <label class="text-xs font-semibold uppercase tracking-widest text-ivory/50">Review</label>
              <textarea v-model="form.comment" rows="5" class="w-full rounded-xl border border-white/[0.08] bg-obsidian px-4 py-3 text-ivory placeholder-ivory/30 outline-none transition-colors focus:border-gold/50 resize-none" placeholder="Tell us about your experience..."></textarea>
            </div>
          </div>
          <button class="mt-8 w-full rounded-xl bg-gradient-to-r from-gold to-gold-dark py-4 text-sm font-bold text-obsidian shadow-[0_0_20px_rgba(212,175,55,0.25)] transition-all hover:shadow-[0_0_30px_rgba(212,175,55,0.4)] hover:scale-[1.02]">
            Submit review
          </button>
          <p v-if="status" class="mt-4 text-center text-sm font-medium text-emerald-400 bg-emerald-400/10 py-2 rounded-lg">{{ status }}</p>
        </form>

        <div class="rounded-2xl border border-white/[0.06] bg-charcoal/80 p-8 backdrop-blur-sm">
          <div class="flex items-center justify-between">
            <h2 class="font-display text-2xl text-ivory">Your <span class="text-gold">History</span></h2>
            <button class="rounded-lg p-2 text-ivory/40 hover:bg-gold/10 hover:text-gold transition-colors" @click="loadReviews" title="Refresh">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" /></svg>
            </button>
          </div>
          
          <div v-if="loadingReviews" class="mt-12 flex justify-center">
            <div class="h-8 w-8 animate-spin rounded-full border-4 border-gold border-t-transparent"></div>
          </div>

          <div v-else class="mt-6 space-y-4">
            <article v-for="item in reviews.data || reviews" :key="item.id" class="rounded-xl border border-white/[0.04] bg-obsidian/50 p-5 transition-all hover:border-gold/30 hover:bg-white/[0.02]">
              <div class="flex items-center gap-1 mb-2">
                <svg v-for="i in 5" :key="i" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4" :class="i <= item.rating ? 'text-gold' : 'text-white/10'">
                  <path fill-rule="evenodd" d="M10.868 2.884c-.321-.772-1.415-.772-1.736 0l-1.83 4.401-4.753.381c-.833.067-1.171 1.107-.536 1.651l3.62 3.102-1.106 4.637c-.194.813.691 1.456 1.405 1.02L10 15.591l4.069 2.485c.713.436 1.598-.207 1.404-1.02l-1.106-4.637 3.62-3.102c.635-.544.297-1.584-.536-1.65l-4.752-.382-1.831-4.401z" clip-rule="evenodd" />
                </svg>
              </div>
              <p class="text-sm text-ivory/80 italic">"{{ item.comment }}"</p>
            </article>
            
            <div v-if="(reviews.data || reviews).length === 0" class="rounded-xl border border-dashed border-white/10 p-8 text-center">
              <p class="text-sm text-ivory/40">You haven't left any reviews yet.</p>
            </div>
          </div>
        </div>
      </div>
    </section>
  </CustomerLayout>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue';
import CustomerLayout from '../layouts/CustomerLayout.vue';
import { publicApi } from '../../public/api/public.api';
import { customerApi } from '../api/customer.api';
import { useToast } from 'vue-toastification';

const toast = useToast();
const form = reactive({ barber_id: '', rating: 5, comment: '' });
const reviews = ref([]);
const barbers = ref([]);
const status = ref('');
const loadingReviews = ref(true);

async function loadReviews() {
  loadingReviews.value = true;
  try {
    const response = await customerApi.reviews();
    reviews.value = response.data.data || [];
  } catch (err) {
    toast.error('Failed to load reviews');
  } finally {
    loadingReviews.value = false;
  }
}

async function loadOptions() {
  try {
    const barbersResponse = await publicApi.barbers();
    barbers.value = barbersResponse.data.data;
  } catch (err) {
    toast.error('Failed to load barbers');
  }
}

async function submit() {
  if (!form.comment) {
    toast.warning('Please enter a review comment');
    return;
  }
  
  try {
    const response = await customerApi.createReview(form);
    status.value = response.data.message;
    toast.success('Review submitted successfully!');
    form.comment = '';
    form.rating = 5;
    await loadReviews();
  } catch (err) {
    toast.error('Failed to submit review');
  }
}

onMounted(async () => {
  await loadOptions();
  await loadReviews();
});
</script>