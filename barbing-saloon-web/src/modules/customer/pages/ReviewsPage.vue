<template>
  <CustomerLayout>
    <section class="animate-fade-in space-y-8">
      <!-- Page Header -->
      <div class="relative overflow-hidden rounded-2xl border border-gold/20 bg-gradient-to-br from-obsidian via-charcoal to-steel p-8">
        <div class="absolute -right-16 -top-16 h-64 w-64 rounded-full bg-gold/5 blur-3xl"></div>
        <div class="absolute -left-24 -bottom-24 h-48 w-48 rounded-full bg-gold/3 blur-2xl"></div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-end md:justify-between gap-6">
          <div>
            <p class="text-xs uppercase tracking-[0.3em] text-gold/70 font-medium">Customer Dashboard</p>
            <h1 class="mt-2 font-display text-3xl lg:text-4xl text-white">
              Your <span class="text-gold">Reviews</span>
            </h1>
            <p class="mt-2 max-w-xl text-sm text-white/70 leading-relaxed">
              Share your experience and review past feedback you've left for your barbers.
            </p>
          </div>
          <!-- Quick Stats -->
          <div class="flex items-center gap-4 flex-wrap">
            <div class="flex items-center gap-3 rounded-xl border border-gold/10 bg-obsidian/60 backdrop-blur px-4 py-3">
              <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-gold/10 border border-gold/20">
                <ChatBubbleBottomCenterTextIcon class="h-4.5 w-4.5 text-gold" />
              </div>
              <div>
                <p class="text-lg font-display text-white leading-none">{{ totalReviews }}</p>
                <p class="text-[10px] uppercase tracking-widest text-white/50 mt-0.5">Reviews</p>
              </div>
            </div>
            <div class="flex items-center gap-3 rounded-xl border border-gold/10 bg-obsidian/60 backdrop-blur px-4 py-3">
              <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-amber-500/10 border border-amber-500/20">
                <StarIcon class="h-4.5 w-4.5 text-amber-400" />
              </div>
              <div>
                <p class="text-lg font-display text-white leading-none">{{ averageRating }}</p>
                <p class="text-[10px] uppercase tracking-widest text-white/50 mt-0.5">Avg Rating</p>
              </div>
            </div>
            <div class="flex items-center gap-3 rounded-xl border border-gold/10 bg-obsidian/60 backdrop-blur px-4 py-3">
              <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-500/10 border border-emerald-500/20">
                <CheckCircleIcon class="h-4.5 w-4.5 text-emerald-400" />
              </div>
              <div>
                <p class="text-lg font-display text-white leading-none">{{ approvedCount }}</p>
                <p class="text-[10px] uppercase tracking-widest text-white/50 mt-0.5">Approved</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="grid gap-6 lg:grid-cols-[0.95fr_1.05fr]">
        <!-- Write a Review Form -->
        <form class="group/form rounded-2xl border border-theme-border bg-theme-surface/80 p-8 backdrop-blur-sm transition-all duration-500 hover:border-gold/20" @submit.prevent="submit">
          <div class="flex items-center gap-3 mb-6">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gold/10 border border-gold/20">
              <PencilSquareIcon class="h-5 w-5 text-gold" />
            </div>
            <div>
              <h2 class="font-display text-2xl text-theme-text">Write a <span class="text-gold">Review</span></h2>
              <p class="text-xs text-theme-muted mt-0.5">Let your barber know how they did</p>
            </div>
          </div>

          <div class="space-y-5">
            <!-- Barber Select -->
            <div class="space-y-2">
              <label class="flex items-center gap-1.5 text-xs font-semibold uppercase tracking-widest text-theme-muted">
                <UserIcon class="h-3.5 w-3.5" />
                Barber
              </label>
              <div class="relative">
                <select v-model="form.barber_id" class="w-full appearance-none rounded-xl border border-theme-border bg-theme-bg px-4 py-3 pr-10 text-theme-text outline-none transition-all focus:border-gold/50 focus:shadow-[0_0_0_3px_rgba(255,153,0,0.08)]">
                  <option value="">Select barber</option>
                  <option v-for="barber in barbers" :key="barber.id" :value="barber.id">{{ barber.name }}</option>
                </select>
                <ChevronDownIcon class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-theme-muted/50" />
              </div>
            </div>

            <!-- Service Select -->
            <div class="space-y-2">
              <label class="flex items-center gap-1.5 text-xs font-semibold uppercase tracking-widest text-theme-muted">
                <ScissorsIcon class="h-3.5 w-3.5" />
                Service
              </label>
              <div class="relative">
                <select v-model="form.service_id" class="w-full appearance-none rounded-xl border border-theme-border bg-theme-bg px-4 py-3 pr-10 text-theme-text outline-none transition-all focus:border-gold/50 focus:shadow-[0_0_0_3px_rgba(255,153,0,0.08)]">
                  <option value="">Select service</option>
                  <option v-for="service in services" :key="service.id" :value="service.id">{{ service.name }}</option>
                </select>
                <ChevronDownIcon class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-theme-muted/50" />
              </div>
            </div>

            <!-- Star Rating -->
            <div class="space-y-2">
              <label class="flex items-center gap-1.5 text-xs font-semibold uppercase tracking-widest text-theme-muted">
                <StarIcon class="h-3.5 w-3.5" />
                Rating
              </label>
              <div class="flex items-center gap-1 p-3 rounded-xl border border-theme-border bg-theme-bg/50 w-fit">
                <button 
                  v-for="star in 5" 
                  :key="star" 
                  type="button" 
                  @click="form.rating = star"
                  @mouseenter="hoveredStar = star"
                  @mouseleave="hoveredStar = 0"
                  class="group/star relative p-1 transition-transform duration-200"
                  :class="{ 'scale-125': hoveredStar === star }"
                >
                  <!-- Glow behind active stars -->
                  <div 
                    v-if="star <= (hoveredStar || form.rating)" 
                    class="absolute inset-0 rounded-full bg-gold/20 blur-md"
                  ></div>
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="relative h-8 w-8 transition-all duration-200" :class="star <= (hoveredStar || form.rating) ? 'text-gold drop-shadow-[0_0_6px_rgba(255,153,0,0.5)]' : 'text-white/10 group-hover/star:text-white/25'">
                    <path fill-rule="evenodd" d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.007 5.404.433c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.433 2.082-5.006z" clip-rule="evenodd" />
                  </svg>
                </button>
                <span class="ml-3 text-sm font-medium" :class="form.rating >= 4 ? 'text-gold' : form.rating >= 3 ? 'text-amber-400' : 'text-theme-muted'">
                  {{ ratingLabels[form.rating - 1] }}
                </span>
              </div>
            </div>

            <!-- Review Textarea -->
            <div class="space-y-2">
              <label class="flex items-center justify-between">
                <span class="flex items-center gap-1.5 text-xs font-semibold uppercase tracking-widest text-theme-muted">
                  <ChatBubbleBottomCenterTextIcon class="h-3.5 w-3.5" />
                  Review
                </span>
                <span class="text-[10px] tabular-nums" :class="form.comment.length > 400 ? 'text-amber-400' : 'text-theme-muted/50'">
                  {{ form.comment.length }} / 500
                </span>
              </label>
              <textarea 
                v-model="form.comment" 
                rows="5" 
                maxlength="500"
                class="w-full rounded-xl border border-theme-border bg-theme-bg px-4 py-3 text-theme-text placeholder-theme-muted outline-none transition-all focus:border-gold/50 focus:shadow-[0_0_0_3px_rgba(255,153,0,0.08)] resize-none leading-relaxed" 
                placeholder="Tell us about your experience..."
              ></textarea>
            </div>
          </div>

          <!-- Submit Button -->
          <button 
            class="mt-8 flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-gold to-gold-dark py-4 text-sm font-bold text-obsidian shadow-[0_0_20px_rgba(255,153,0,0.2)] transition-all duration-300 hover:shadow-[0_0_40px_rgba(255,153,0,0.35)] hover:scale-[1.02] active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed"
            :disabled="submitting"
          >
            <svg v-if="submitting" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
              <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" class="opacity-25" />
              <path d="M4 12a8 8 0 018-8" stroke="currentColor" stroke-width="3" stroke-linecap="round" class="opacity-75" />
            </svg>
            <PaperAirplaneIcon v-else class="h-4 w-4" />
            {{ submitting ? 'Submitting...' : 'Submit review' }}
          </button>
          <p v-if="status" class="mt-4 text-center text-sm font-medium text-emerald-400 bg-emerald-400/10 py-2 rounded-lg border border-emerald-500/20">{{ status }}</p>
        </form>

        <!-- Review History -->
        <div class="rounded-2xl border border-theme-border bg-theme-surface/80 p-8 backdrop-blur-sm">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
              <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gold/10 border border-gold/20">
                <ClockIcon class="h-5 w-5 text-gold" />
              </div>
              <div>
                <h2 class="font-display text-2xl text-theme-text">Your <span class="text-gold">History</span></h2>
                <p class="text-xs text-theme-muted mt-0.5">{{ reviewsList.length }} review{{ reviewsList.length !== 1 ? 's' : '' }} submitted</p>
              </div>
            </div>
            <button 
              class="rounded-xl p-2.5 text-theme-muted hover:bg-gold/10 hover:text-gold transition-all border border-transparent hover:border-gold/20" 
              @click="loadReviews" 
              title="Refresh"
              :class="{ 'animate-spin': loadingReviews }"
            >
              <ArrowPathIcon class="h-5 w-5" />
            </button>
          </div>
          
          <!-- Loading State -->
          <div v-if="loadingReviews" class="mt-8 space-y-4">
            <div v-for="i in 3" :key="i" class="rounded-xl border border-theme-border bg-theme-bg/50 p-5">
              <div class="flex items-center gap-3 mb-4">
                <div class="h-10 w-10 rounded-full skeleton-shimmer"></div>
                <div class="space-y-2 flex-1">
                  <div class="h-4 w-28 rounded skeleton-shimmer"></div>
                  <div class="h-3 w-20 rounded skeleton-shimmer"></div>
                </div>
                <div class="h-5 w-16 rounded-md skeleton-shimmer"></div>
              </div>
              <div class="pl-[52px] space-y-2">
                <div class="h-3 w-24 rounded skeleton-shimmer"></div>
                <div class="h-4 w-full rounded skeleton-shimmer"></div>
              </div>
            </div>
          </div>

          <!-- Review Cards -->
          <div v-else class="mt-6 space-y-4">
            <article 
              v-for="(item, index) in reviewsList" 
              :key="item.id" 
              class="group relative rounded-xl border border-theme-border bg-theme-bg/50 p-5 transition-all duration-300 hover:border-gold/30 hover:bg-theme-surface/50 hover:shadow-[0_8px_30px_-8px_rgba(255,153,0,0.08)]"
              :style="{ animationDelay: `${index * 80}ms` }"
            >
              <!-- Decorative quote mark -->
              <div class="absolute right-4 top-3 font-display text-5xl text-gold/[0.06] leading-none select-none pointer-events-none">"</div>
              
              <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
                <div class="flex items-center gap-3">
                  <div class="h-10 w-10 rounded-full bg-gradient-to-br from-gold to-amber-600 flex items-center justify-center text-obsidian font-bold text-sm shrink-0 shadow-[0_0_12px_rgba(255,153,0,0.2)] group-hover:shadow-[0_0_18px_rgba(255,153,0,0.3)] transition-shadow">
                    {{ item.barber_name ? item.barber_name.charAt(0).toUpperCase() : '?' }}
                  </div>
                  <div>
                    <p class="text-theme-text font-semibold text-sm group-hover:text-gold transition-colors">{{ item.barber_name || 'Unknown Barber' }}</p>
                    <div class="flex items-center gap-2 mt-0.5">
                      <p v-if="item.service_name" class="text-gold/70 text-xs font-medium flex items-center gap-1">
                        <ScissorsIcon class="h-3 w-3" />
                        {{ item.service_name }}
                      </p>
                      <span v-if="item.service_name" class="text-theme-border text-xs">•</span>
                      <p class="text-theme-muted text-xs flex items-center gap-1">
                        <CalendarIcon class="h-3 w-3" />
                        {{ formatDate(item.created_at) }}
                      </p>
                    </div>
                  </div>
                </div>
                <span 
                  class="text-[10px] font-bold uppercase tracking-widest px-2.5 py-1 rounded-full border" 
                  :class="item.is_approved 
                    ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' 
                    : 'bg-amber-500/10 text-amber-400 border-amber-500/20'"
                >
                  <span class="flex items-center gap-1">
                    <span class="h-1.5 w-1.5 rounded-full" :class="item.is_approved ? 'bg-emerald-400' : 'bg-amber-400 animate-pulse'"></span>
                    {{ item.is_approved ? 'Approved' : 'Pending' }}
                  </span>
                </span>
              </div>

              <!-- Star rating display -->
              <div class="flex items-center gap-1 mb-3 pl-[52px]">
                <svg v-for="i in 5" :key="i" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4 transition-colors" :class="i <= item.rating ? 'text-gold drop-shadow-[0_0_3px_rgba(255,153,0,0.4)]' : 'text-white/10'">
                  <path fill-rule="evenodd" d="M10.868 2.884c-.321-.772-1.415-.772-1.736 0l-1.83 4.401-4.753.381c-.833.067-1.171 1.107-.536 1.651l3.62 3.102-1.106 4.637c-.194.813.691 1.456 1.405 1.02L10 15.591l4.069 2.485c.713.436 1.598-.207 1.404-1.02l-1.106-4.637 3.62-3.102c.635-.544.297-1.584-.536-1.65l-4.752-.382-1.831-4.401z" clip-rule="evenodd" />
                </svg>
                <span class="ml-1.5 text-xs text-theme-muted font-medium">{{ item.rating }}/5</span>
              </div>

              <!-- Review text -->
              <div class="pl-[52px]">
                <p class="text-sm text-theme-muted leading-relaxed italic border-l-2 border-gold/20 pl-3 py-1">
                  "{{ item.comment }}"
                </p>
              </div>
            </article>
            
            <!-- Empty State -->
            <div v-if="reviewsList.length === 0" class="rounded-xl border border-dashed border-theme-border p-12 text-center">
              <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-theme-surface border border-theme-border text-theme-muted/30 mb-4">
                <ChatBubbleBottomCenterTextIcon class="h-8 w-8" />
              </div>
              <p class="text-sm font-medium text-theme-muted">No reviews yet</p>
              <p class="text-xs text-theme-muted/70 mt-1">Your submitted reviews will appear here</p>
            </div>
          </div>
        </div>
      </div>
    </section>
  </CustomerLayout>
</template>

<script setup>
import { onMounted, reactive, ref, computed } from 'vue';
import CustomerLayout from '../layouts/CustomerLayout.vue';
import { publicApi } from '../../public/api/public.api';
import { customerApi } from '../api/customer.api';
import { useToast } from '../../../core/composables/useToast';
import { 
  StarIcon, 
  ClockIcon, 
  CheckCircleIcon,
  ChatBubbleBottomCenterTextIcon,
  PencilSquareIcon,
  UserIcon,
  ChevronDownIcon,
  PaperAirplaneIcon,
  CalendarIcon,
  ArrowPathIcon
} from '@heroicons/vue/24/outline';
import { ScissorsIcon } from '@heroicons/vue/24/solid';

const toast = useToast();
const form = reactive({ barber_id: '', service_id: '', rating: 5, comment: '' });
const reviews = ref([]);
const barbers = ref([]);
const services = ref([]);
const status = ref('');
const loadingReviews = ref(true);
const submitting = ref(false);
const hoveredStar = ref(0);

const ratingLabels = ['Poor', 'Fair', 'Good', 'Great', 'Excellent'];

const reviewsList = computed(() => reviews.value.data || reviews.value);

const totalReviews = computed(() => reviewsList.value.length);

const averageRating = computed(() => {
  const list = reviewsList.value;
  if (!list.length) return '—';
  const avg = list.reduce((sum, r) => sum + (r.rating || 0), 0) / list.length;
  return avg.toFixed(1);
});

const approvedCount = computed(() => {
  return reviewsList.value.filter(r => r.is_approved).length;
});

function formatDate(dateStr) {
  const d = new Date(dateStr);
  return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
}

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
    const [barbersResponse, servicesResponse] = await Promise.all([
      publicApi.barbers(),
      publicApi.services()
    ]);
    barbers.value = barbersResponse.data.data;
    services.value = servicesResponse.data.data;
  } catch (err) {
    toast.error('Failed to load options');
  }
}

async function submit() {
  if (!form.comment) {
    toast.warning('Please enter a review comment');
    return;
  }
  
  submitting.value = true;
  try {
    const response = await customerApi.createReview(form);
    status.value = response.data.message;
    toast.success('Review submitted successfully!');
    form.comment = '';
    form.rating = 5;
    form.service_id = '';
    await loadReviews();
  } catch (err) {
    toast.error('Failed to submit review');
  } finally {
    submitting.value = false;
  }
}

onMounted(async () => {
  await loadOptions();
  await loadReviews();
});
</script>