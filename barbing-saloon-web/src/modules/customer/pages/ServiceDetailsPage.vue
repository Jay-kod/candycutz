<template>
  <CustomerLayout>
    <section class="animate-fade-in pb-12">
      <!-- Loading State -->
      <div v-if="loading" class="flex items-center justify-center min-h-[60vh]">
        <div class="h-12 w-12 animate-spin rounded-full border-4 border-gold/30 border-t-gold"></div>
      </div>

      <div v-else-if="service" class="max-w-6xl mx-auto space-y-8">
        
        <!-- Header Actions -->
        <div class="flex items-center justify-between">
          <button @click="$router.back()" class="flex items-center gap-2 text-sm font-semibold text-theme-muted hover:text-gold transition-colors group">
            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-theme-surface border border-theme-border group-hover:border-gold/30 group-hover:bg-gold/5 transition-all">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 group-hover:-translate-x-1 transition-transform">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
              </svg>
            </div>
            Back to Services
          </button>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-[1fr_400px] gap-8">
          
          <!-- Left Column: Media & Details -->
          <div class="space-y-8">
            <!-- Main Image Carousel -->
            <div class="relative rounded-3xl overflow-hidden border border-theme-border bg-theme-surface aspect-[16/10] md:aspect-[2/1] group shadow-2xl">
              <div v-if="images.length > 0" class="w-full h-full relative">
                <img :src="images[activeImage]" :alt="service.name" class="w-full h-full object-cover transition-all duration-500" />
                <div class="absolute inset-0 bg-gradient-to-t from-obsidian/80 via-transparent to-transparent"></div>
                
                <!-- Carousel Controls -->
                <div v-if="images.length > 1" class="absolute bottom-6 left-0 right-0 flex justify-center gap-2 z-10">
                  <button 
                    v-for="(img, idx) in images" 
                    :key="idx"
                    @click="activeImage = idx"
                    class="h-2 rounded-full transition-all duration-300"
                    :class="activeImage === idx ? 'w-8 bg-gold' : 'w-2 bg-white/50 hover:bg-white'"
                  ></button>
                </div>
                
                <button v-if="images.length > 1" @click="prevImage" class="absolute left-4 top-1/2 -translate-y-1/2 h-10 w-10 rounded-full bg-black/50 backdrop-blur-md border border-white/10 flex items-center justify-center text-white opacity-0 group-hover:opacity-100 transition-all hover:bg-gold hover:text-black">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" /></svg>
                </button>
                <button v-if="images.length > 1" @click="nextImage" class="absolute right-4 top-1/2 -translate-y-1/2 h-10 w-10 rounded-full bg-black/50 backdrop-blur-md border border-white/10 flex items-center justify-center text-white opacity-0 group-hover:opacity-100 transition-all hover:bg-gold hover:text-black">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg>
                </button>
              </div>
              <div v-else class="w-full h-full flex flex-col items-center justify-center text-theme-muted/40 bg-theme-bg">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor" class="w-20 h-20 mb-4">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M14.25 7.525W6.75a3.75 3.75 0 00-3.75 3.75v8.25c0 2.071 1.679 3.75 3.75 3.75h10.5a3.75 3.75 0 003.75-3.75v-8.25c0-2.071-1.679-3.75-3.75-3.75h-3.375c-.621 0-1.125-.504-1.125-1.125v-1.5c0-.621.504-1.125 1.125-1.125z" />
                </svg>
                <span class="text-sm">No images available</span>
              </div>
            </div>

            <!-- Image Thumbnails (Always show 3 slots) -->
            <div class="grid grid-cols-3 gap-4">
              <button 
                v-for="idx in [0, 1, 2]" 
                :key="idx"
                @click="images[idx] ? activeImage = idx : null"
                class="relative aspect-[4/3] rounded-xl overflow-hidden border-2 transition-all"
                :class="[
                  images[idx] ? (activeImage === idx ? 'border-gold cursor-pointer' : 'border-transparent opacity-50 hover:opacity-100 cursor-pointer') : 'border-dashed border-theme-border opacity-30 cursor-default bg-theme-surface'
                ]"
              >
                <img v-if="images[idx]" :src="images[idx]" class="w-full h-full object-cover" />
                <div v-else class="w-full h-full flex items-center justify-center">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-theme-muted/30">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                  </svg>
                </div>
              </button>
            </div>

            <!-- About Section -->
            <div class="rounded-3xl border border-theme-border bg-theme-surface/60 p-8 backdrop-blur-sm">
              <h2 class="text-2xl font-display text-theme-text mb-4">About this Service</h2>
              <div class="prose prose-invert max-w-none text-theme-muted leading-relaxed">
                <p v-if="service.description">{{ service.description }}</p>
                <p v-else class="italic opacity-70">No detailed description provided for this service.</p>
              </div>
            </div>

            <!-- Real Reviews Section -->
            <div class="rounded-3xl border border-theme-border bg-theme-surface/60 p-8 backdrop-blur-sm mt-8">
              <div class="flex items-center justify-between mb-8">
                <h2 class="text-2xl font-display text-theme-text">Customer Reviews</h2>
                <div v-if="reviews.length > 0" class="flex items-center gap-2">
                  <div class="flex items-center text-gold">
                    <svg v-for="i in 5" :key="i" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
                      <path fill-rule="evenodd" d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.007 5.404.433c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.433 2.082-5.006z" clip-rule="evenodd" />
                    </svg>
                  </div>
                  <span class="text-theme-text font-bold text-lg">{{ averageRating }}</span>
                  <span class="text-theme-muted text-sm">({{ reviews.length }} Reviews)</span>
                </div>
              </div>

              <div v-if="reviewsLoading" class="flex justify-center py-8">
                <div class="h-8 w-8 animate-spin rounded-full border-2 border-gold/30 border-t-gold"></div>
              </div>

              <div v-else-if="reviews.length > 0" class="space-y-6">
                <!-- Review Item -->
                <div v-for="review in reviews" :key="review.id" class="border-b border-theme-border/50 pb-6 last:border-0 last:pb-0">
                  <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-3">
                      <div class="h-10 w-10 rounded-full bg-gradient-to-br from-gold to-amber-600 flex items-center justify-center text-obsidian font-bold text-lg">
                        {{ review.customer_name ? review.customer_name.charAt(0).toUpperCase() : 'C' }}
                      </div>
                      <div>
                        <p class="text-theme-text font-semibold text-sm">{{ review.customer_name || 'Anonymous Customer' }}</p>
                        <p class="text-theme-muted text-xs">{{ new Date(review.created_at).toLocaleDateString() }}</p>
                      </div>
                    </div>
                    <div class="flex items-center gap-3">
                      <span v-if="review.barber_name" class="text-xs font-semibold text-theme-muted bg-theme-bg px-2 py-1 rounded-md border border-theme-border">
                        Barber: <span class="text-gold">{{ review.barber_name }}</span>
                      </span>
                      <div class="flex items-center text-gold">
                        <svg v-for="i in Number(review.rating)" :key="'fill-'+i" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4">
                          <path fill-rule="evenodd" d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.007 5.404.433c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.433 2.082-5.006z" clip-rule="evenodd" />
                        </svg>
                        <svg v-for="i in (5 - Number(review.rating))" :key="'empty-'+i" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-theme-muted/30">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.536a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" />
                        </svg>
                      </div>
                    </div>
                  </div>
                  <p class="text-theme-muted text-sm leading-relaxed">
                    "{{ review.comment }}"
                  </p>
                </div>
              </div>

              <div v-else class="text-center py-6">
                <p class="text-theme-muted text-sm italic">There are no reviews for this service yet. Be the first to book and review!</p>
              </div>

              <!-- See More Button -->
              <button v-if="reviews.length > 5" class="mt-6 w-full rounded-xl border border-gold/30 bg-gold/5 py-3 text-sm font-semibold text-gold transition-colors hover:bg-gold/10">
                Read All {{ reviews.length }} Reviews
              </button>
            </div>
          </div>

          <!-- Right Column: Booking Card -->
          <div class="lg:sticky lg:top-8 space-y-6">
            <div class="rounded-[2rem] border border-gold/20 bg-gradient-to-b from-charcoal to-obsidian p-8 shadow-[0_20px_40px_rgba(0,0,0,0.5)] relative overflow-hidden">
              <div class="absolute -right-20 -top-20 h-48 w-48 rounded-full bg-gold/10 blur-[60px]"></div>
              
              <div class="relative z-10">
                <div class="mb-6 flex items-start justify-between">
                  <span class="inline-flex items-center gap-1.5 rounded-lg bg-gold/10 px-3 py-1.5 text-[10px] font-bold uppercase tracking-widest text-gold border border-gold/20">
                    {{ service.category?.name || service.category_name || 'Service' }}
                  </span>
                </div>

                <h1 class="font-display text-3xl font-bold text-white leading-tight mb-6">
                  {{ service.name }}
                </h1>

                <div class="space-y-4 mb-8">
                  <div class="flex items-center justify-between rounded-2xl bg-white/10 p-4 border border-white/10">
                    <div class="flex items-center gap-3">
                      <div class="p-2 rounded-xl bg-gold/10 text-gold">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                      </div>
                      <div>
                        <p class="text-xs uppercase tracking-wider text-white/50 font-semibold mb-0.5">Duration</p>
                        <p class="font-bold text-white">{{ service.duration_minutes }} Minutes</p>
                      </div>
                    </div>
                  </div>

                  <div class="flex items-center justify-between rounded-2xl bg-white/10 p-4 border border-white/10">
                    <div class="flex items-center gap-3">
                      <div class="p-2 rounded-xl bg-emerald-500/10 text-emerald-400">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                      </div>
                      <div>
                        <p class="text-xs uppercase tracking-wider text-white/50 font-semibold mb-0.5">Price</p>
                        <p class="font-display text-2xl font-bold text-emerald-400">₦{{ Number(service.price).toLocaleString() }}</p>
                      </div>
                    </div>
                  </div>
                </div>

                <RouterLink 
                  :to="`/customer/dashboard/book/${service.id}`" 
                  class="flex w-full items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-gold to-amber-500 py-4 text-sm font-bold text-obsidian transition-all hover:shadow-[0_0_30px_rgba(212,175,55,0.4)] hover:scale-[1.02] group"
                >
                  Book Appointment Now
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-4 w-4 group-hover:translate-x-1 transition-transform"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" /></svg>
                </RouterLink>
              </div>
            </div>
            
            <!-- Guarantee Badge -->
            <div class="flex items-center gap-3 px-2 justify-center text-theme-muted text-sm">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gold"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z" /></svg>
              Satisfaction Guaranteed
            </div>

            <!-- Barber Profile Card -->
            <div v-if="service.barber" class="rounded-[2rem] border border-theme-border bg-theme-surface/80 p-6 backdrop-blur-sm">
              <h3 class="text-xs uppercase tracking-widest text-theme-muted font-bold mb-4">Service Provider</h3>
              <div class="flex items-center gap-4">
                <div class="h-14 w-14 shrink-0 overflow-hidden rounded-full border-2 border-gold/40">
                  <img v-if="service.barber.avatar" :src="getFullImageUrl(service.barber.avatar)" :alt="service.barber.name" class="h-full w-full object-cover" />
                  <div v-else class="h-full w-full bg-gold/10 flex items-center justify-center text-gold font-bold text-xl">
                    {{ service.barber.name?.charAt(0) || 'B' }}
                  </div>
                </div>
                <div>
                  <h4 class="font-display text-lg font-bold text-theme-text">{{ service.barber.name }}</h4>
                  <RouterLink :to="`/customer/dashboard/barber/${service.barber.id}`" class="text-sm text-gold hover:underline mt-0.5 inline-block">
                    View Provider
                  </RouterLink>
                </div>
              </div>
            </div>
          </div>
          
        </div>
      </div>

      <!-- Not Found State -->
      <div v-else class="flex flex-col items-center justify-center py-24 text-center">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-16 h-16 text-theme-muted/40 mb-4">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
        </svg>
        <h3 class="text-xl font-display text-theme-text mb-2">Service Not Found</h3>
        <p class="text-theme-muted text-sm mb-6">The service you are looking for might have been removed or is currently unavailable.</p>
        <button @click="$router.push('/customer/dashboard/services')" class="rounded-xl bg-theme-surface border border-theme-border px-6 py-2.5 text-sm font-semibold hover:bg-white/5 transition-colors">
          Browse All Services
        </button>
      </div>
    </section>
  </CustomerLayout>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import CustomerLayout from '../layouts/CustomerLayout.vue';
import { publicApi } from '../../public/api/public.api';

const route = useRoute();
const router = useRouter();

const serviceId = route.params.id;
const service = ref(null);
const loading = ref(true);
const activeImage = ref(0);

const reviews = ref([]);
const reviewsLoading = ref(true);

const images = computed(() => {
  if (!service.value) return [];
  const imgs = [];
  if (service.value.image) imgs.push(service.value.image);
  if (service.value.image2) imgs.push(service.value.image2);
  if (service.value.image3) imgs.push(service.value.image3);
  return imgs;
});

const API_ROOT = import.meta.env.VITE_API_BASE_URL?.replace('/api', '') || 'http://localhost:8000';

function getFullImageUrl(path) {
  if (!path) return '';
  if (path.startsWith('http')) return path;
  return `${API_ROOT}${path.startsWith('/') ? '' : '/'}${path}`;
}

const averageRating = computed(() => {
  if (!reviews.value.length) return '0.0';
  const total = reviews.value.reduce((acc, rev) => acc + Number(rev.rating), 0);
  return (total / reviews.value.length).toFixed(1);
});

function nextImage() {
  if (images.value.length <= 1) return;
  activeImage.value = (activeImage.value + 1) % images.value.length;
}

function prevImage() {
  if (images.value.length <= 1) return;
  activeImage.value = (activeImage.value - 1 + images.value.length) % images.value.length;
}

onMounted(async () => {
  loading.value = true;
  reviewsLoading.value = true;
  try {
    const res = await publicApi.services();
    const allServices = res.data.data || [];
    service.value = allServices.find(s => String(s.id) === String(serviceId));
    
    // Fetch reviews for this specific service
    const reviewsRes = await publicApi.testimonials({ service_id: serviceId });
    reviews.value = reviewsRes.data.data || [];
  } catch (err) {
    console.error('Failed to load service data', err);
  } finally {
    loading.value = false;
    reviewsLoading.value = false;
  }
});
</script>

<style scoped>
.animate-fade-in {
  animation: fadeIn 0.4s ease-out forwards;
}
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}
.scrollbar-hide::-webkit-scrollbar { display: none; }
.scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
</style>
