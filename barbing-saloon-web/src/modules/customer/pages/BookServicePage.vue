<template>
  <CustomerLayout>
    <div class="animate-fade-in pb-20 max-w-4xl mx-auto">
      
      <!-- Header -->
      <section class="relative overflow-hidden rounded-3xl border border-[var(--color-border)] bg-[var(--color-bg-secondary)] p-8 mb-12 shadow-2xl">
        <div class="absolute -right-16 -top-16 h-64 w-64 rounded-full bg-[var(--color-accent)]/5 blur-3xl pointer-events-none"></div>
        <div class="relative z-10">
          <button @click="goBack" class="mb-6 flex items-center gap-2 text-sm text-[var(--color-text-secondary)] hover:text-[var(--color-accent)] transition-colors">
            <ArrowLeftIcon class="h-4 w-4" /> Back to Services
          </button>
          <p class="text-xs uppercase tracking-[0.3em] text-[var(--color-accent)]/80 font-medium">Book Appointment</p>
          <h1 class="mt-2 font-display text-3xl lg:text-4xl text-[var(--color-text-primary)]">
            Secure your <span class="text-[var(--color-accent)]">Seat</span>
          </h1>
        </div>
      </section>

      <!-- Loading State -->
      <div v-if="loadingService" class="flex justify-center py-20">
        <div class="h-10 w-10 animate-spin rounded-full border-4 border-[var(--color-accent)] border-t-transparent shadow-[0_0_15px_rgba(255,153,0,0.5)]"></div>
      </div>

      <div v-else-if="!service" class="text-center py-20 bg-[var(--color-bg-secondary)] rounded-3xl border border-[var(--color-border)]">
        <p class="text-[var(--color-text-muted)]">Service not found.</p>
        <button @click="router.push('/customer/dashboard/services')" class="mt-4 text-[var(--color-accent)] hover:underline">Go back to services</button>
      </div>

      <div v-else class="space-y-8">
        
        <!-- Selected Service & Barber Summary -->
        <div class="bg-[var(--color-bg-secondary)] backdrop-blur-xl border border-[var(--color-border)] rounded-2xl p-6 shadow-xl">
          <div class="flex flex-col sm:flex-row items-center gap-6">
            <div class="h-24 w-24 rounded-xl overflow-hidden shrink-0 border border-[var(--color-border)]">
              <img v-if="service.image" :src="service.image" :alt="service.name" class="h-full w-full object-cover" />
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
        <form @submit.prevent="submit" class="bg-[var(--color-bg-secondary)] backdrop-blur-xl border border-[var(--color-border)] rounded-3xl p-6 md:p-8 shadow-2xl relative overflow-hidden">
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
import { publicApi } from '@/shared/api/old_publicApi'
import { customerApi } from '@/shared/api/old_customerApi'
import { useToast } from '../../../core/composables/useToast'
import BookingBarberSelect from '../components/booking/BookingBarberSelect.vue'
import BookingTimeSlotPicker from '../components/booking/BookingTimeSlotPicker.vue'
import BookingSummaryStep from '../components/booking/BookingSummaryStep.vue'
import { 
  ArrowLeftIcon, 
  ScissorsIcon, 
  ClockIcon, 
  CurrencyDollarIcon 
} from '@heroicons/vue/24/outline'
import { getStorageUrl as getFullImageUrl } from '@/core/utils/url'

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

const todayStr = new Date().toISOString().split('T')[0]

const selectedBarberName = computed(() => {
  const b = barbers.value.find(item => item.id === form.barber_id)
  return b ? b.name : (service.value?.barber?.name || 'Any Master Barber')
})

function selectBarber(id) {
  form.barber_id = id
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
    const [servicesResponse, barbersResponse] = await Promise.all([
      publicApi.services(),
      publicApi.barbers(),
    ])

    barbers.value = barbersResponse.data.data || []
    
    // Find the specific service
    const foundService = (servicesResponse.data.data || []).find(s => s.id == serviceId)
    if (!foundService) {
      toast.error('Service not found')
      loadingService.value = false
      return
    }
    
    service.value = foundService
    
    // Auto-select preferred barber
    if (foundService.barber_id) {
      form.barber_id = foundService.barber_id
    } else if (barbers.value.length > 0) {
      const preferred = barbers.value.find(b => b.id === 4) || barbers.value[0]
      form.barber_id = preferred.id
    }
    
  } catch (err) {
    toast.error('Failed to load booking details')
  } finally {
    loadingService.value = false
  }
}

async function loadSlots() {
  if (!form.barber_id || !form.service_id || !form.appointment_date) {
    slots.value = []
    return
  }

  loadingSlots.value = true
  try {
    const response = await publicApi.availableSlots({
      barber_id: form.barber_id,
      service_id: form.service_id,
      date: form.appointment_date,
    })
    slots.value = response.data.data || []
    
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
    await customerApi.createBooking(form)
    toast.success('Booking created successfully! Please pay to confirm.')
    router.push('/customer/dashboard/bookings')
  } catch (err) {
    toast.error(err.response?.data?.error || 'Failed to create booking')
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
