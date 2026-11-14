<template>
  <CustomerLayout>
    <section class="animate-fade-in space-y-8 pb-12">
      <!-- Luxury Header Banner -->
      <div class="relative overflow-hidden rounded-3xl border border-slate-200 dark:border-white/[0.08] bg-gradient-to-br from-amber-500/10 via-amber-400/5 to-white dark:from-[#12161C] dark:via-[#0E1217] dark:to-[#12161C] p-8 shadow-2xl">
        <div class="absolute -right-16 -top-16 h-64 w-64 rounded-full bg-[var(--color-accent)]/5 blur-3xl pointer-events-none"></div>
        <div class="absolute -left-24 -bottom-24 h-48 w-48 rounded-full bg-[var(--color-accent)]/5 blur-2xl pointer-events-none"></div>
        
        <div class="relative z-10 flex flex-col md:flex-row md:items-end md:justify-between gap-6">
          <div>
            <p class="text-xs uppercase tracking-[0.3em] text-[var(--color-accent)] font-bold">Feedback & Ratings</p>
            <h1 class="mt-2 font-display text-3xl lg:text-4xl text-slate-900 dark:text-white">
              Your <span class="text-transparent bg-clip-text bg-gradient-to-r from-[var(--color-accent)] to-amber-200">Reviews</span>
            </h1>
            <p class="mt-2 max-w-xl text-sm text-[var(--color-text-secondary)] leading-relaxed">
              Share your styling experience, rate your barber's craft, and review past testimonials left for our team.
            </p>
          </div>

          <!-- Quick Stats Pills -->
          <div class="flex items-center gap-3 flex-wrap">
            <div class="flex items-center gap-3 rounded-2xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-white/[0.03] px-4 py-2.5 shadow-sm">
              <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-[var(--color-accent)]/10 border border-[var(--color-accent)]/20 text-[var(--color-accent)]">
                <ChatBubbleBottomCenterTextIcon class="h-4.5 w-4.5" />
              </div>
              <div>
                <p class="text-lg font-display text-slate-900 dark:text-white leading-none">{{ totalReviews }}</p>
                <p class="text-[9px] uppercase tracking-widest text-[var(--color-text-muted)] font-bold mt-0.5">Reviews</p>
              </div>
            </div>
            
            <div class="flex items-center gap-3 rounded-2xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-white/[0.03] px-4 py-2.5 shadow-sm">
              <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-500/10 border border-amber-500/20 text-amber-500 dark:text-amber-400">
                <StarIcon class="h-4.5 w-4.5" />
              </div>
              <div>
                <p class="text-lg font-display text-slate-900 dark:text-white leading-none">{{ averageRating }}</p>
                <p class="text-[9px] uppercase tracking-widest text-[var(--color-text-muted)] font-bold mt-0.5">Avg Score</p>
              </div>
            </div>

            <div class="flex items-center gap-3 rounded-2xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-white/[0.03] px-4 py-2.5 shadow-sm">
              <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 dark:text-emerald-400">
                <CheckCircleIcon class="h-4.5 w-4.5" />
              </div>
              <div>
                <p class="text-lg font-display text-slate-900 dark:text-white leading-none">{{ approvedCount }}</p>
                <p class="text-[9px] uppercase tracking-widest text-[var(--color-text-muted)] font-bold mt-0.5">Published</p>
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
        <div class="rounded-3xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#12161C] p-6 lg:p-8 shadow-xl flex flex-col justify-between">
          <div>
            <div class="flex items-center justify-between flex-wrap gap-4 pb-4 border-b border-slate-100 dark:border-white/[0.06]">
              <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-[var(--color-accent)]/10 border border-[var(--color-accent)]/20 text-[var(--color-accent)]">
                  <ClockIcon class="h-5 w-5" />
                </div>
                <div>
                  <h2 class="font-display text-xl text-slate-900 dark:text-white">Your <span class="text-transparent bg-clip-text bg-gradient-to-r from-[var(--color-accent)] to-amber-200">History</span></h2>
                  <p class="text-xs text-[var(--color-text-secondary)] mt-0.5">{{ filteredReviewsList.length }} review{{ filteredReviewsList.length !== 1 ? 's' : '' }} recorded</p>
                </div>
              </div>
              <div class="flex items-center gap-2">
                <select 
                  v-model="selectedServiceFilter"
                  class="bg-slate-50 dark:bg-white/[0.04] border border-slate-200 dark:border-white/[0.08] text-slate-900 dark:text-white text-xs rounded-xl px-3 py-2 focus:outline-none focus:border-[var(--color-accent)]"
                >
                  <option value="all">All Services</option>
                  <option v-for="s in services" :key="s.id" :value="s.id">{{ s.name }}</option>
                </select>
                <button 
                  class="rounded-xl p-2 text-slate-500 dark:text-[var(--color-text-secondary)] hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-white/[0.06] transition-all border border-slate-200 dark:border-white/[0.08]" 
                  @click="loadReviews" 
                  title="Refresh"
                >
                  <ArrowPathIcon class="h-4 w-4" :class="{ 'animate-spin': loadingReviews }" />
                </button>
              </div>
            </div>
            
            <!-- Zero-CLS Skeleton State -->
            <div v-if="loadingReviews" class="mt-6 space-y-4">
              <div v-for="i in 3" :key="i" class="h-28 rounded-2xl bg-slate-100 dark:bg-white/5 border border-slate-200 dark:border-white/5 animate-pulse"></div>
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
              <div v-if="filteredReviewsList.length === 0" class="rounded-2xl border border-dashed border-slate-200 dark:border-white/10 p-12 text-center bg-slate-50/50 dark:bg-white/[0.02]">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 dark:bg-white/[0.04] border border-slate-200 dark:border-white/10 text-[var(--color-accent)]/50 mb-3">
                  <ChatBubbleBottomCenterTextIcon class="h-7 w-7" />
                </div>
                <p class="text-sm font-semibold text-slate-900 dark:text-white">No Reviews Found</p>
                <p class="text-xs text-[var(--color-text-secondary)] mt-1">Submit your first review on the left to document your experience.</p>
              </div>
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
import { client } from '@/shared/api/client'
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

const reviewsList = computed(() => reviews.value?.data || reviews.value || [])
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
  if (!dateStr) return ''
  const d = new Date(dateStr)
  return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
}

async function loadReviews() {
  loadingReviews.value = true
  try {
    const response = await client.get('/customer/reviews')
    reviews.value = response.data?.data || response.data || []
  } catch (err) {
    toast.error('Failed to load reviews')
  } finally {
    loadingReviews.value = false
  }
}

async function loadOptions() {
  try {
    const [barbersResponse, servicesResponse] = await Promise.all([
      client.get('/barbers'),
      client.get('/services')
    ])
    barbers.value = barbersResponse.data?.data || barbersResponse.data || []
    services.value = servicesResponse.data?.data || servicesResponse.data || []
  } catch (err) {
    toast.error('Failed to load review options')
  }
}

async function submit() {
  if (!form.barber_id || !form.service_id) {
    toast.error('Please select both a barber and a service')
    return
  }
  if (!form.comment.trim()) {
    toast.error('Please provide some comments for your review')
    return
  }

  submitting.value = true
  status.value = ''
  try {
    await client.post('/customer/reviews', {
      barber_id: form.barber_id,
      service_id: form.service_id,
      rating: form.rating,
      comment: form.comment
    })
    status.value = 'Review submitted! It will appear once approved by admin.'
    toast.success('Review submitted successfully')
    form.barber_id = ''
    form.service_id = ''
    form.comment = ''
    form.rating = 5
    await loadReviews()
  } catch (err) {
    toast.error(err.response?.data?.message || 'Failed to submit review')
  } finally {
    submitting.value = false
  }
}

onMounted(async () => {
  await Promise.all([loadReviews(), loadOptions()])
})
</script>
