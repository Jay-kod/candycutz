<template>
  <CustomerLayout>
    <div class="animate-fade-in pb-20 max-w-4xl mx-auto">
      
      <!-- Header -->
      <section class="relative overflow-hidden rounded-3xl border border-[var(--color-border)] bg-[var(--color-bg-secondary)] p-4 xs:p-6 sm:p-8 mb-6 sm:mb-12 shadow-2xl">
        <div class="absolute -right-16 -top-16 h-64 w-64 rounded-full bg-[var(--color-accent)]/5 blur-3xl pointer-events-none"></div>
        <div class="relative z-10">
          <button @click="goBack" class="mb-4 sm:mb-6 flex items-center gap-2 text-sm text-[var(--color-text-secondary)] hover:text-[var(--color-accent)] transition-colors">
            <ArrowLeftIcon class="h-4 w-4" /> Back to Services
          </button>
          <p class="text-xs uppercase tracking-[0.3em] text-[var(--color-accent)]/80 font-medium">Book Appointment</p>
          <h1 class="mt-2 font-display text-2xl xs:text-3xl lg:text-4xl text-[var(--color-text-primary)]">
            Secure your <span class="text-[var(--color-accent)]">Seat</span>
          </h1>
        </div>
      </section>

      <!-- Loading State -->
      <div v-if="loadingService" class="flex justify-center py-20">
        <div class="h-10 w-10 animate-spin rounded-full border-4 border-[var(--color-accent)] border-t-transparent shadow-[0_0_15px_rgba(255,153,0,0.5)]"></div>
      </div>

      <div v-else-if="!service" class="text-center py-16 px-4 bg-[var(--color-bg-secondary)] rounded-3xl border border-[var(--color-border)] max-w-xl mx-auto shadow-sm animate-fade-in">
        <div class="relative inline-block mb-4">
          <div class="h-16 w-16 mx-auto rounded-2xl bg-gradient-to-br from-[var(--color-accent)]/20 via-[var(--color-accent)]/10 to-transparent border border-[var(--color-accent)]/20 flex items-center justify-center text-[var(--color-accent)] shadow-md shadow-[var(--color-accent)]/10">
            <SparklesIcon class="w-8 h-8 animate-pulse" />
          </div>
          <div class="absolute -bottom-1 -right-1 h-6 w-6 rounded-full bg-[var(--color-bg-secondary)] border border-[var(--color-border)] flex items-center justify-center text-[var(--color-accent)] shadow-sm">
            <ScissorsIcon class="w-3 h-3" />
          </div>
        </div>
        <div class="block">
          <span class="inline-flex items-center gap-1.5 px-3 py-0.5 rounded-full bg-[var(--color-accent)]/10 border border-[var(--color-accent)]/20 text-[var(--color-accent)] text-[11px] font-bold uppercase tracking-wider mb-3">
            <span class="h-1.5 w-1.5 rounded-full bg-[var(--color-accent)] animate-ping"></span>
            <span>Enhancements Underway</span>
          </span>
        </div>
        <h3 class="text-xl sm:text-2xl font-display font-bold text-[var(--color-text-primary)] mb-2 text-center !text-center">Upgrades on the Way!</h3>
        <p class="text-xs sm:text-sm text-[var(--color-text-secondary)] leading-relaxed mb-6 max-w-md mx-auto text-center !text-center">
          We’re giving this service a fresh polish to make your experience even better. Give it just a moment to refresh, or feel free to explore our other cuts and styles in the meantime!
        </p>
        <div class="flex flex-wrap items-center justify-center gap-3">
          <button
            type="button"
            @click="router.push('/customer/dashboard/services')"
            class="inline-flex items-center gap-2 rounded-xl bg-[var(--color-accent)] px-5 py-2.5 text-xs sm:text-sm font-bold text-white shadow-lg shadow-[var(--color-accent)]/20 hover:brightness-110 active:scale-[0.98] transition-all cursor-pointer"
          >
            <SparklesIcon class="w-4 h-4" />
            <span>Explore All Services</span>
          </button>
          <button
            type="button"
            @click="refreshData"
            class="inline-flex items-center gap-2 rounded-xl bg-[var(--color-bg-secondary)] border border-[var(--color-border)] text-[var(--color-text-primary)] px-4 py-2.5 text-xs sm:text-sm font-semibold hover:border-[var(--color-accent)]/40 hover:text-[var(--color-accent)] transition-colors cursor-pointer"
          >
            <ArrowPathIcon class="w-4 h-4" :class="{ 'animate-spin': isRefreshing }" />
            <span>Give It a Moment</span>
          </button>
          <button
            type="button"
            @click="router.push('/customer/dashboard')"
            class="inline-flex items-center gap-2 rounded-xl bg-[var(--color-bg-secondary)] border border-[var(--color-border)] text-[var(--color-text-primary)] px-4 py-2.5 text-xs sm:text-sm font-semibold hover:border-[var(--color-accent)]/40 hover:text-[var(--color-accent)] transition-colors cursor-pointer"
          >
            <ArrowLeftIcon class="w-4 h-4" />
            <span>Dashboard</span>
          </button>
        </div>
      </div>

      <div v-else class="space-y-8">
        
        <!-- Selected Service & Barber Summary -->
        <div class="bg-[var(--color-bg-secondary)] backdrop-blur-xl border border-[var(--color-border)] rounded-2xl p-4 sm:p-6 shadow-xl">
          <div class="flex flex-col sm:flex-row items-center gap-6">
            <div class="h-24 w-24 rounded-xl overflow-hidden shrink-0 border border-[var(--color-border)]">
              <img v-if="service.image" :src="getStorageUrl(service.image)" :alt="service.name" class="h-full w-full object-cover" />
              <div v-else class="h-full w-full bg-[var(--color-bg-tertiary)] flex items-center justify-center">
                <ScissorsIcon class="w-8 h-8 text-[var(--color-accent)]/60" />
              </div>
            </div>
            <div class="flex-1 text-center sm:text-left">
              <span class="text-[10px] uppercase tracking-widest text-[var(--color-accent)]/80">{{ service.category?.name || 'Service' }}</span>
              <h2 class="font-display text-2xl font-bold text-[var(--color-text-primary)] mt-1">{{ service.name }}</h2>
              <div class="flex flex-wrap items-center justify-center sm:justify-start gap-4 mt-3 text-sm text-[var(--color-text-secondary)]">
                <span class="flex items-center gap-1.5"><ClockIcon class="w-4 h-4 text-[var(--color-accent)]/60" /> {{ service.duration_minutes }} mins</span>
                <span class="hidden sm:inline text-[var(--color-border)]">•</span>
                <span class="flex items-center gap-1.5 font-bold text-[var(--color-text-primary)]"><CurrencyDollarIcon class="w-4 h-4 text-[var(--color-accent)]/60" /> {{ formatNairaAmount(service.price) }}</span>
              </div>
            </div>
          </div>

          <!-- Select Barber Section -->
          <BookingBarberSelect
            :barbers="barbers"
            :selected-barber-id="form.barber_id"
            :get-full-image-url="getFullImageUrl"
            @select="selectBarber"
          />
        </div>

        <!-- Booking Form: Date & Time only -->
        <form @submit.prevent="submit" class="bg-[var(--color-bg-secondary)] backdrop-blur-xl border border-[var(--color-border)] rounded-3xl p-4 sm:p-6 md:p-8 shadow-2xl relative overflow-hidden">
          <div class="absolute top-0 right-0 w-64 h-64 bg-[var(--color-accent)]/5 rounded-full blur-3xl -mr-32 -mt-32 pointer-events-none"></div>

          <!-- Step 1: Date & Time -->
          <BookingTimeSlotPicker
            :appointment-date="form.appointment_date"
            :appointment-time="form.appointment_time"
            :today-str="todayStr"
            :slots="slots"
            :loading-slots="loadingSlots"
            @update:appointment-date="form.appointment_date = $event"
            @update:appointment-time="form.appointment_time = $event"
            @date-change="loadSlots"
          />

          <!-- Step 2: Notes & Submit -->
          <transition name="fade-slide">
            <BookingSummaryStep
              v-if="form.appointment_time"
              key="step2"
              :notes="form.notes"
              :service="service"
              :selected-barber-name="selectedBarberName"
              :appointment-date="form.appointment_date"
              :appointment-time="form.appointment_time"
              :format-date="formatDate"
              :is-submitting="isSubmitting"
              @update:notes="form.notes = $event"
            />
          </transition>
        </form>
      </div>

    </div>
  </CustomerLayout>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import { formatNairaAmount } from '@/core/utils'
import { useRoute, useRouter } from 'vue-router'
import CustomerLayout from '@/portals/customer/layouts/CustomerLayout.vue'
import client from '@/shared/api/client'
import { useToast } from '../../../core/composables/useToast'
import BookingBarberSelect from '../components/booking/BookingBarberSelect.vue'
import BookingTimeSlotPicker from '../components/booking/BookingTimeSlotPicker.vue'
import BookingSummaryStep from '../components/booking/BookingSummaryStep.vue'
import { 
  ArrowLeftIcon, 
  ArrowPathIcon,
  ScissorsIcon, 
  SparklesIcon, 
  ClockIcon, 
  CurrencyDollarIcon 
} from '@heroicons/vue/24/outline'
import { getStorageUrl, getStorageUrl as getFullImageUrl } from '@/core/utils/url'

const route = useRoute()
const router = useRouter()
const toast = useToast()

const serviceId = route.params.serviceId

const form = reactive({ barber_id: '', service_id: serviceId, appointment_date: '', appointment_time: '', notes: '' })
const service = ref(null)
const barbers = ref([])
const slots = ref([])

const loadingService = ref(true)
const loadingSlots = ref(false)
const isSubmitting = ref(false)
const isRefreshing = ref(false)

const todayStr = new Date().toISOString().split('T')[0]

const selectedBarberName = computed(() => {
  const b = barbers.value.find(item => item.id === form.barber_id)
  return b ? b.name : (service.value?.barber?.name || 'Any Master Barber')
})

function selectBarber(id) {
  form.barber_id = Number(id)
  form.appointment_time = ''
  if (form.appointment_date) {
    loadSlots()
  }
}

function goBack() {
  router.back()
}

function formatDate(dateStr) {
  if (!dateStr) return ''
  return new Date(dateStr).toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric' })
}

async function loadData() {
  loadingService.value = true
  try {
    const [serviceRes, barbersResponse] = await Promise.all([
      client.get(`/services/${serviceId}`).catch(async () => {
        const fallback = await client.get('/services')
        const list = fallback.data?.data || fallback.data || []
        return { data: { data: list.find(s => String(s.id) === String(serviceId) || s.slug === String(serviceId)) } }
      }),
      client.get('/barbers'),
    ])

    barbers.value = barbersResponse.data?.data || barbersResponse.data || []
    
    // Find the specific service
    const foundService = serviceRes.data?.data || serviceRes.data
    if (!foundService) {
      loadingService.value = false
      return
    }
    
    service.value = foundService
    form.service_id = Number(foundService.id)
    
    // Auto-select preferred barber from active list
    if (route.query.barber_id && barbers.value.some(b => String(b.id) === String(route.query.barber_id))) {
      form.barber_id = Number(route.query.barber_id)
    } else if (foundService.barber_id && barbers.value.some(b => Number(b.id) === Number(foundService.barber_id))) {
      form.barber_id = Number(foundService.barber_id)
    } else if (barbers.value.length > 0) {
      const preferred = barbers.value.find(b => b.id === 4) || barbers.value[0]
      form.barber_id = Number(preferred.id)
    }

    // Auto-select initial appointment date (next open business day, skipping past hours & closed Sundays)
    if (!form.appointment_date) {
      const now = new Date()
      const targetDate = new Date(now)
      if (now.getHours() >= 18) {
        targetDate.setDate(targetDate.getDate() + 1)
      }
      if (targetDate.getDay() === 0) {
        targetDate.setDate(targetDate.getDate() + 1)
      }
      const y = targetDate.getFullYear()
      const m = String(targetDate.getMonth() + 1).padStart(2, '0')
      const d = String(targetDate.getDate()).padStart(2, '0')
      form.appointment_date = `${y}-${m}-${d}`
    }

    if (form.barber_id && form.service_id && form.appointment_date) {
      await loadSlots()
    }
    
  } catch (err) {
    toast.error('Failed to load booking details')
  } finally {
    loadingService.value = false
  }
}

async function refreshData() {
  isRefreshing.value = true
  await loadData()
  setTimeout(() => {
    isRefreshing.value = false
  }, 400)
}

async function loadSlots(customDate) {
  const dateToUse = (typeof customDate === 'string' && customDate) ? customDate : form.appointment_date
  if (!form.barber_id || !form.service_id || !dateToUse) {
    slots.value = []
    return
  }
  form.appointment_date = dateToUse

  loadingSlots.value = true
  try {
    const response = await client.get('/available-slots', {
      params: {
        barber_id: form.barber_id,
        service_id: form.service_id,
        date: dateToUse,
      }
    })
    slots.value = response.data?.data || response.data || []
    
    if (form.appointment_time && !slots.value.includes(form.appointment_time)) {
      form.appointment_time = ''
    }
  } catch (err) {
    toast.error('Failed to load available slots')
  } finally {
    loadingSlots.value = false
  }
}

async function submit() {
  if (!form.barber_id) { toast.warning('Please select a master barber'); return }
  if (!form.service_id) { toast.warning('Please select a service'); return }
  if (!form.appointment_date) { toast.warning('Please select a date'); return }
  if (!form.appointment_time) { toast.warning('Please select a time slot'); return }
  
  isSubmitting.value = true
  try {
    const res = await client.post('/customer/bookings', form)
    const data = res.data?.data || res.data || {}
    const apptId = data.id || data.appointment?.id
    toast.success('Booking created! Please complete payment.')
    if (apptId) {
      router.push(`/customer/dashboard/payment/${apptId}`)
    } else {
      router.push('/customer/dashboard/bookings')
    }
  } catch (err) {
    toast.error(err.response?.data?.message || err.response?.data?.error || 'Failed to create booking')
  } finally {
    isSubmitting.value = false
  }
}

onMounted(() => {
  loadData()
})
</script>

<style scoped>
.fade-slide-enter-active,
.fade-slide-leave-active {
  transition: all 0.5s ease;
}
.fade-slide-enter-from,
.fade-slide-leave-to {
  opacity: 0;
  transform: translateY(-20px);
}
</style>
