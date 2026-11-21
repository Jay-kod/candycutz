<template>
  <CustomerLayout>
    <section class="animate-fade-in pb-12">
      <!-- Loading State -->
      <div v-if="loading" class="max-w-6xl mx-auto space-y-8">
        <div class="flex items-center justify-between">
          <div class="skeleton-block h-10 w-40 bg-[var(--color-bg-tertiary)] animate-pulse rounded-md"></div>
        </div>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
          <div class="skeleton-block aspect-[4/3] w-full rounded-2xl bg-[var(--color-bg-tertiary)] animate-pulse"></div>
          <div class="space-y-4">
            <div class="skeleton-block h-8 w-3/4 bg-[var(--color-bg-tertiary)] animate-pulse rounded-md"></div>
            <div class="skeleton-block h-8 w-24 rounded-full bg-[var(--color-bg-tertiary)] animate-pulse"></div>
            <div class="skeleton-block h-5 w-full bg-[var(--color-bg-tertiary)] animate-pulse rounded-md"></div>
            <div class="skeleton-block h-5 w-5/6 bg-[var(--color-bg-tertiary)] animate-pulse rounded-md"></div>
            <div class="skeleton-block h-5 w-2/3 bg-[var(--color-bg-tertiary)] animate-pulse rounded-md"></div>
            <div class="mt-6 flex gap-3">
              <div class="skeleton-block h-14 w-40 rounded-xl bg-[var(--color-bg-tertiary)] animate-pulse"></div>
              <div class="skeleton-block h-14 w-40 rounded-xl bg-[var(--color-bg-tertiary)] animate-pulse"></div>
            </div>
          </div>
        </div>
      </div>

      <div v-else-if="service" class="max-w-6xl mx-auto space-y-8">
        <!-- Header Actions -->
        <div class="flex items-center justify-between">
          <button @click="$router.back()" class="flex items-center gap-2 text-sm font-semibold text-[var(--color-text-secondary)] hover:text-[var(--color-accent)] transition-colors group">
            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-[var(--color-bg-secondary)] border border-[var(--color-border)] group-hover:border-[var(--color-accent)]/30 group-hover:bg-[var(--color-accent)]/10 transition-all shadow-sm">
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
            <ServiceGallerySection
              :images="images"
              :service-name="service.name"
              :active-image="activeImage"
              @update:active-image="activeImage = $event"
              @prev-image="prevImage"
              @next-image="nextImage"
            />

            <!-- About Section -->
            <div class="rounded-3xl border border-[var(--color-border)] bg-[var(--color-bg-secondary)] p-8 shadow-sm">
              <h2 class="text-2xl font-display text-[var(--color-text-primary)] mb-4">About this Service</h2>
              <div class="prose dark:prose-invert max-w-none text-[var(--color-text-secondary)] leading-relaxed">
                <p v-if="service.description">{{ service.description }}</p>
                <p v-else class="italic opacity-70">No detailed description provided for this service.</p>
              </div>
            </div>

            <!-- Real Reviews Section -->
            <ServiceReviewsSection
              :reviews="reviews"
              :reviews-loading="reviewsLoading"
              :average-rating="averageRating"
            />
          </div>

          <!-- Right Column: Booking Card -->
          <ServiceBookingCard
            :service="service"
            :primary-barber="primaryBarber"
            :other-barbers="otherBarbers"
            :get-full-image-url="getFullImageUrl"
          />
        </div>
      </div>

      <!-- Friendly Upgrades / Not Found State -->
      <div v-else class="flex flex-col items-center justify-center py-20 px-4 text-center max-w-lg mx-auto animate-fade-in">
        <div class="relative mb-6">
          <div class="h-20 w-20 rounded-3xl bg-gradient-to-br from-[var(--color-accent)]/20 via-[var(--color-accent)]/10 to-transparent border border-[var(--color-accent)]/25 flex items-center justify-center text-[var(--color-accent)] shadow-xl shadow-[var(--color-accent)]/15">
            <SparklesIcon class="w-10 h-10 animate-pulse" />
          </div>
          <div class="absolute -bottom-1 -right-1 h-7 w-7 rounded-full bg-[var(--color-bg-secondary)] border border-[var(--color-border)] flex items-center justify-center text-[var(--color-accent)] shadow-sm">
            <ScissorsIcon class="w-3.5 h-3.5" />
          </div>
        </div>

        <div class="inline-flex items-center gap-1.5 px-3.5 py-1 rounded-full bg-[var(--color-accent)]/10 border border-[var(--color-accent)]/20 text-[var(--color-accent)] text-xs font-bold uppercase tracking-wider mb-3">
          <span class="h-1.5 w-1.5 rounded-full bg-[var(--color-accent)] animate-ping"></span>
          <span>Enhancements Underway</span>
        </div>

        <h3 class="text-2xl sm:text-3xl font-display font-bold text-[var(--color-text-primary)] mb-3 text-center !text-center">
          Upgrades on the Way!
        </h3>
        <p class="text-[var(--color-text-secondary)] text-sm sm:text-base leading-relaxed mb-8 max-w-md mx-auto text-center !text-center">
          We’re giving this service a fresh polish to make your experience even better. Give it just a moment to refresh, or feel free to explore our other cuts and styles in the meantime!
        </p>
        <div class="flex flex-wrap items-center justify-center gap-3">
          <button
            type="button"
            @click="$router.push('/customer/dashboard/services')"
            class="inline-flex items-center gap-2 rounded-xl bg-[var(--color-accent)] px-6 py-3 text-sm font-bold text-white shadow-lg shadow-[var(--color-accent)]/20 hover:brightness-110 active:scale-[0.98] transition-all cursor-pointer"
          >
            <SparklesIcon class="w-4 h-4" />
            <span>Explore All Services</span>
          </button>
          <button
            type="button"
            @click="refreshService"
            class="inline-flex items-center gap-2 rounded-xl bg-[var(--color-bg-secondary)] border border-[var(--color-border)] text-[var(--color-text-primary)] px-5 py-3 text-sm font-semibold hover:border-[var(--color-accent)]/40 hover:text-[var(--color-accent)] transition-colors cursor-pointer"
          >
            <ArrowPathIcon class="w-4 h-4" :class="{ 'animate-spin': isRefreshing }" />
            <span>Give It a Moment</span>
          </button>
          <button
            type="button"
            @click="$router.push('/customer/dashboard')"
            class="inline-flex items-center gap-2 rounded-xl bg-[var(--color-bg-secondary)] border border-[var(--color-border)] text-[var(--color-text-primary)] px-5 py-3 text-sm font-semibold hover:border-[var(--color-accent)]/40 hover:text-[var(--color-accent)] transition-colors cursor-pointer"
          >
            <ArrowLeftIcon class="w-4 h-4" />
            <span>Dashboard</span>
          </button>
        </div>
      </div>
    </section>
  </CustomerLayout>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRoute } from 'vue-router'
import { ScissorsIcon, SparklesIcon, ArrowLeftIcon, ArrowPathIcon } from '@heroicons/vue/24/outline'
import CustomerLayout from '@/portals/customer/layouts/CustomerLayout.vue'
import client from '@/shared/api/client'
import ServiceGallerySection from '../components/services/ServiceGallerySection.vue'
import ServiceBookingCard from '../components/services/ServiceBookingCard.vue'
import ServiceReviewsSection from '../components/services/ServiceReviewsSection.vue'
import { getStorageUrl as getFullImageUrl } from '@/core/utils/url'

const route = useRoute()

const serviceId = route.params.id
const service = ref(null)
const loading = ref(true)
const isRefreshing = ref(false)
const activeImage = ref(0)

const reviews = ref([])
const reviewsLoading = ref(true)

const images = computed(() => {
  if (!service.value) return []
  if (Array.isArray(service.value.images) && service.value.images.length > 0) {
    return service.value.images.map(img => getFullImageUrl(img))
  }
  const imgs = []
  if (service.value.image || service.value.image_url) imgs.push(getFullImageUrl(service.value.image_url || service.value.image))
  if (service.value.image2 || service.value.image2_url) imgs.push(getFullImageUrl(service.value.image2_url || service.value.image2))
  if (service.value.image3 || service.value.image3_url) imgs.push(getFullImageUrl(service.value.image3_url || service.value.image3))
  return imgs
})

const averageRating = computed(() => {
  if (!reviews.value.length) return '0.0'
  const total = reviews.value.reduce((acc, rev) => acc + Number(rev.rating), 0)
  return (total / reviews.value.length).toFixed(1)
})

const primaryBarber = computed(() => {
  if (!service.value) return null
  if (service.value.barber?.id) return service.value.barber
  return service.value.barbers?.[0] || null
})

const otherBarbers = computed(() => {
  if (!service.value) return []
  const primaryId = primaryBarber.value?.id
  return (service.value.barbers || []).filter(b => b.id !== primaryId)
})

function nextImage() {
  if (images.value.length <= 1) return
  activeImage.value = (activeImage.value + 1) % images.value.length
}

function prevImage() {
  if (images.value.length <= 1) return
  activeImage.value = (activeImage.value - 1 + images.value.length) % images.value.length
}

async function fetchServiceData() {
  loading.value = true
  reviewsLoading.value = true
  try {
    const [serviceRes, reviewsRes] = await Promise.all([
      client.get(`/services/${serviceId}`).catch(async () => {
        const fallback = await client.get('/services');
        const list = fallback.data?.data || fallback.data || [];
        return { data: { data: list.find(s => String(s.id) === String(serviceId)) } };
      }),
      client.get('/testimonials', { params: { service_id: serviceId } }).catch(() => ({ data: { data: [] } }))
    ]);

    service.value = serviceRes.data?.data || serviceRes.data || null;
    reviews.value = reviewsRes.data?.data || reviewsRes.data || [];
  } catch (err) {
    console.error('Failed to load service data', err)
  } finally {
    loading.value = false
    reviewsLoading.value = false
  }
}

async function refreshService() {
  isRefreshing.value = true
  await fetchServiceData()
  setTimeout(() => {
    isRefreshing.value = false
  }, 400)
}

onMounted(() => {
  fetchServiceData()
})
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
