<template>
  <CustomerLayout>
    <section class="animate-fade-in space-y-8">
      <!-- Page Header -->
      <div class="relative overflow-hidden rounded-2xl border border-[var(--color-border)] bg-[var(--color-bg-secondary)] p-8 shadow-xl">
        <div class="absolute -right-16 -top-16 h-64 w-64 rounded-full bg-[var(--color-accent)]/5 blur-3xl pointer-events-none"></div>
        <div class="absolute -left-24 -bottom-24 h-48 w-48 rounded-full bg-[var(--color-accent)]/5 blur-2xl pointer-events-none"></div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-end md:justify-between gap-6">
          <div>
            <p class="text-xs uppercase tracking-[0.3em] text-[var(--color-accent)]/80 font-medium">Customer Dashboard</p>
            <h1 class="mt-2 font-display text-3xl lg:text-4xl text-[var(--color-text-primary)]">
              Your <span class="text-[var(--color-accent)]">Reviews</span>
            </h1>
            <p class="mt-2 max-w-xl text-sm text-[var(--color-text-secondary)] leading-relaxed">
              Share your experience and review past feedback you've left for your barbers.
            </p>
          </div>
          <!-- Quick Stats -->
          <div class="flex items-center gap-4 flex-wrap">
            <div class="flex items-center gap-3 rounded-xl border border-[var(--color-border)] bg-[var(--color-bg-tertiary)] px-4 py-3">
              <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-[var(--color-accent)]/10 border border-[var(--color-accent)]/20">
                <ChatBubbleBottomCenterTextIcon class="h-4.5 w-4.5 text-[var(--color-accent)]" />
              </div>
              <div>
                <p class="text-lg font-display text-[var(--color-text-primary)] leading-none">{{ totalReviews }}</p>
                <p class="text-[10px] uppercase tracking-widest text-[var(--color-text-muted)] mt-0.5">Reviews</p>
              </div>
            </div>
            <div class="flex items-center gap-3 rounded-xl border border-[var(--color-border)] bg-[var(--color-bg-tertiary)] px-4 py-3">
              <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-amber-500/10 border border-amber-500/20">
                <StarIcon class="h-4.5 w-4.5 text-amber-500" />
              </div>
              <div>
                <p class="text-lg font-display text-[var(--color-text-primary)] leading-none">{{ averageRating }}</p>
                <p class="text-[10px] uppercase tracking-widest text-[var(--color-text-muted)] mt-0.5">Avg Rating</p>
              </div>
            </div>
            <div class="flex items-center gap-3 rounded-xl border border-[var(--color-border)] bg-[var(--color-bg-tertiary)] px-4 py-3">
              <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-[var(--color-success)]/10 border border-[var(--color-success)]/20">
                <CheckCircleIcon class="h-4.5 w-4.5 text-[var(--color-success)]" />
              </div>
              <div>
                <p class="text-lg font-display text-[var(--color-text-primary)] leading-none">{{ approvedCount }}</p>
                <p class="text-[10px] uppercase tracking-widest text-[var(--color-text-muted)] mt-0.5">Approved</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="grid gap-6 lg:grid-cols-[0.95fr_1.05fr]">
        <!-- Write a Review Form -->
        <CustomerReviewForm
          :barbers="barbers"
          :services="services"
          :form="form"
          :submitting="submitting"
          :status="status"
          :rating-labels="ratingLabels"
          @submit="submit"
        />

        <!-- Review History -->
        <div class="rounded-2xl border border-[var(--color-border)] bg-[var(--color-bg-secondary)] p-8 shadow-sm">
          <div class="flex items-center justify-between flex-wrap gap-4">
            <div class="flex items-center gap-3">
              <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-[var(--color-accent)]/10 border border-[var(--color-accent)]/20">
                <ClockIcon class="h-5 w-5 text-[var(--color-accent)]" />
              </div>
              <div>
                <h2 class="font-display text-2xl text-[var(--color-text-primary)]">Your <span class="text-[var(--color-accent)]">History</span></h2>
                <p class="text-xs text-[var(--color-text-secondary)] mt-0.5">{{ filteredReviewsList.length }} review{{ filteredReviewsList.length !== 1 ? 's' : '' }} submitted</p>
              </div>
            </div>
            <div class="flex items-center gap-3">
              <select 
                v-model="selectedServiceFilter"
                class="bg-[var(--color-bg-tertiary)] border border-[var(--color-border)] text-[var(--color-text-primary)] text-xs rounded-xl px-3 py-2 focus:outline-none focus:border-[var(--color-accent)]/50"
              >
                <option value="all">All Services</option>
                <option v-for="s in services" :key="s.id" :value="s.id">{{ s.name }}</option>
              </select>
              <button 
                class="rounded-xl p-2.5 text-[var(--color-text-secondary)] hover:bg-[var(--color-accent)]/10 hover:text-[var(--color-accent)] transition-all border border-transparent hover:border-[var(--color-accent)]/20" 
                @click="loadReviews" 
                title="Refresh"
                :class="{ 'animate-spin': loadingReviews }"
              >
                <ArrowPathIcon class="h-5 w-5" />
              </button>
            </div>
          </div>
          
          <!-- Loading State -->
          <div v-if="loadingReviews" class="mt-8 space-y-4">
            <div v-for="i in 3" :key="i" class="rounded-xl border border-[var(--color-border)] bg-[var(--color-bg-tertiary)] p-5">
              <div class="flex items-center gap-3 mb-4">
                <div class="h-10 w-10 rounded-full bg-[var(--color-bg-secondary)] animate-pulse"></div>
                <div class="space-y-2 flex-1">
                  <div class="h-4 w-28 rounded bg-[var(--color-bg-secondary)] animate-pulse"></div>
                  <div class="h-3 w-20 rounded bg-[var(--color-bg-secondary)] animate-pulse"></div>
                </div>
                <div class="h-5 w-16 rounded-md bg-[var(--color-bg-secondary)] animate-pulse"></div>
              </div>
              <div class="pl-[52px] space-y-2">
                <div class="h-3 w-24 rounded bg-[var(--color-bg-secondary)] animate-pulse"></div>
                <div class="h-4 w-full rounded bg-[var(--color-bg-secondary)] animate-pulse"></div>
              </div>
            </div>
          </div>

          <!-- Review Cards -->
          <div v-else class="mt-6 space-y-4">
            <CustomerReviewItem
              v-for="(item, index) in filteredReviewsList"
              :key="item.id"
              :item="item"
              :index="index"
              :format-date="formatDate"
            />
            
            <!-- Empty State -->
            <div v-if="filteredReviewsList.length === 0" class="rounded-xl border border-dashed border-[var(--color-border)] p-12 text-center">
              <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-[var(--color-bg-tertiary)] border border-[var(--color-border)] text-[var(--color-text-muted)] mb-4">
                <ChatBubbleBottomCenterTextIcon class="h-8 w-8" />
              </div>
              <p class="text-sm font-medium text-[var(--color-text-primary)]">No reviews found</p>
              <p class="text-xs text-[var(--color-text-secondary)] mt-1">Try selecting another service or submit your first review</p>
            </div>
          </div>
        </div>
      </div>
    </section>
  </CustomerLayout>
</template>

<script setup>
import { onMounted, reactive, ref, computed } from 'vue'
import CustomerLayout from '@/portals/customer/layouts/CustomerLayout.vue'
import { publicApi } from '@/shared/api/old_publicApi'
import { customerApi } from '@/shared/api/old_customerApi'
import { useToast } from '../../../core/composables/useToast'
import CustomerReviewForm from '../components/reviews/CustomerReviewForm.vue'
import CustomerReviewItem from '../components/reviews/CustomerReviewItem.vue'
import { 
  StarIcon, 
  ClockIcon, 
  CheckCircleIcon,
  ChatBubbleBottomCenterTextIcon,
  ArrowPathIcon
} from '@heroicons/vue/24/outline'

const toast = useToast()
const form = reactive({ barber_id: '', service_id: '', rating: 5, comment: '' })
const reviews = ref([])
const barbers = ref([])
const services = ref([])
const status = ref('')
const loadingReviews = ref(true)
const submitting = ref(false)
const selectedServiceFilter = ref('all')

const ratingLabels = ['Poor', 'Fair', 'Good', 'Great', 'Excellent']

const reviewsList = computed(() => reviews.value.data || reviews.value)
const filteredReviewsList = computed(() => {
  const list = reviewsList.value
  if (selectedServiceFilter.value === 'all') return list
  return list.filter(r => (r.service_id == selectedServiceFilter.value || r.service?.id == selectedServiceFilter.value))
})
const totalReviews = computed(() => reviewsList.value.length)

const averageRating = computed(() => {
  const list = reviewsList.value
  if (!list.length) return '—'
  const avg = list.reduce((sum, r) => sum + (r.rating || 0), 0) / list.length
  return avg.toFixed(1)
})

const approvedCount = computed(() => {
  return reviewsList.value.filter(r => r.is_approved).length
})

function formatDate(dateStr) {
  const d = new Date(dateStr)
  return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
}

async function loadReviews() {
  loadingReviews.value = true
  try {
    const response = await customerApi.reviews()
    reviews.value = response.data.data || []
  } catch (err) {
    toast.error('Failed to load reviews')
  } finally {
    loadingReviews.value = false
  }
}

async function loadOptions() {
  try {
    const [barbersResponse, servicesResponse] = await Promise.all([
      publicApi.barbers(),
      publicApi.services()
    ])
    barbers.value = barbersResponse.data.data
    services.value = servicesResponse.data.data
  } catch (err) {
    toast.error('Failed to load options')
  }
}

async function submit() {
  if (!form.comment) {
    toast.warning('Please enter a review comment')
    return
  }
  
  submitting.value = true
  try {
    const response = await customerApi.createReview(form)
    status.value = response.data.message
    toast.success('Review submitted successfully!')
    form.comment = ''
    form.rating = 5
    form.service_id = ''
    await loadReviews()
  } catch (err) {
    toast.error('Failed to submit review')
  } finally {
    submitting.value = false
  }
}

onMounted(async () => {
  await loadOptions()
  await loadReviews()
})
</script>
