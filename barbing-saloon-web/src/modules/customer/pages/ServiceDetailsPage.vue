<template>
  <CustomerLayout>
    <section class="animate-fade-in pb-12">
      <!-- Loading State -->
      <div v-if="loading" class="max-w-6xl mx-auto space-y-8">
        <div class="flex items-center justify-between">
          <div class="skeleton-block h-10 w-40"></div>
        </div>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
          <div class="skeleton-block aspect-[4/3] w-full rounded-2xl"></div>
          <div class="space-y-4">
            <div class="skeleton-block h-8 w-3/4"></div>
            <div class="skeleton-block h-8 w-24 rounded-full"></div>
            <div class="skeleton-block h-5 w-full"></div>
            <div class="skeleton-block h-5 w-5/6"></div>
            <div class="skeleton-block h-5 w-2/3"></div>
            <div class="mt-6 flex gap-3">
              <div class="skeleton-block h-14 w-40 rounded-xl"></div>
              <div class="skeleton-block h-14 w-40 rounded-xl"></div>
            </div>
          </div>
        </div>
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
            <ServiceGallerySection
              :images="images"
              :service-name="service.name"
              :active-image="activeImage"
              @update:active-image="activeImage = $event"
              @prev-image="prevImage"
              @next-image="nextImage"
            />

            <!-- About Section -->
            <div class="rounded-3xl border border-theme-border bg-theme-surface/60 p-8 backdrop-blur-sm">
              <h2 class="text-2xl font-display text-theme-text mb-4">About this Service</h2>
              <div class="prose prose-invert max-w-none text-theme-muted leading-relaxed">
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
import { ref, onMounted, computed } from 'vue'
import { useRoute } from 'vue-router'
import CustomerLayout from '@/portals/customer/layouts/CustomerLayout.vue'
import { publicApi } from '@/shared/api/old_publicApi'
import ServiceGallerySection from '../components/services/ServiceGallerySection.vue'
import ServiceBookingCard from '../components/services/ServiceBookingCard.vue'
import ServiceReviewsSection from '../components/services/ServiceReviewsSection.vue'

const route = useRoute()

const serviceId = route.params.id
const service = ref(null)
const loading = ref(true)
const activeImage = ref(0)

const reviews = ref([])
const reviewsLoading = ref(true)

const images = computed(() => {
  if (!service.value) return []
  const imgs = []
  if (service.value.image) imgs.push(service.value.image)
  if (service.value.image2) imgs.push(service.value.image2)
  if (service.value.image3) imgs.push(service.value.image3)
  return imgs
})

const API_ROOT = import.meta.env.VITE_API_BASE_URL?.replace('/api', '') || 'http://localhost:8000'

function getFullImageUrl(path) {
  if (!path) return ''
  if (path.startsWith('http')) return path
  return `${API_ROOT}${path.startsWith('/') ? '' : '/'}${path}`
}

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

onMounted(async () => {
  loading.value = true
  reviewsLoading.value = true
  try {
    const res = await publicApi.services()
    const allServices = res.data.data || []
    service.value = allServices.find(s => String(s.id) === String(serviceId))
    
    // Fetch reviews for this specific service
    const reviewsRes = await publicApi.testimonials({ service_id: serviceId })
    reviews.value = reviewsRes.data.data || []
  } catch (err) {
    console.error('Failed to load service data', err)
  } finally {
    loading.value = false
    reviewsLoading.value = false
  }
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
