<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { ArrowLeftIcon, CheckIcon, XCircleIcon, ArrowPathIcon } from '@heroicons/vue/24/outline'
import { barberApi } from '../api/barber.api'
import BarberLayout from '../layouts/BarberLayout.vue'

const route = useRoute()
const router = useRouter()
const bookingId = route.params.id

const booking = ref(null)
const isLoading = ref(true)
const isProcessing = ref(false)
const error = ref('')

const fetchBooking = async () => {
  isLoading.value = true
  try {
    const res = await barberApi.getBookings()
    const appointments = res.data.data
    const found = appointments.find(a => String(a.id) === String(bookingId))
    if (found) {
      booking.value = {
        ...found,
        customer: {
          name: found.customer_name,
          email: found.customer_email,
          phone: found.customer_phone
        },
        service: {
          name: found.service_name,
          price: found.price,
          duration_minutes: found.duration_minutes
        },
        barber: {
          name: 'You'
        }
      }
    } else {
      error.value = 'Receipt not found.'
    }
  } catch (err) {
    error.value = 'Failed to load booking.'
  } finally {
    isLoading.value = false
  }
}

onMounted(() => {
  fetchBooking()
})

const receiptSrc = computed(() => {
  if (!booking.value) return null
  let img = booking.value.receipt_image
  if (!img) return null
  if (img.startsWith('http')) return img
  if (img.startsWith('/public/')) {
    img = img.replace('/public/', '/')
  }
  return `http://localhost:8000${img}`
})

const isPdf = computed(() => {
  return booking.value?.receipt_image?.toLowerCase().endsWith('.pdf')
})

const verifyPayment = async (action) => {
  isProcessing.value = true
  error.value = ''
  
  try {
    const token = localStorage.getItem('candycutz_auth_token')
    const res = await fetch(`http://localhost:8000/barber/bookings/${booking.value.id}/verify-payment`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${token}`
      },
      body: JSON.stringify({ action: action === 'approve' ? 'approve' : 'reject' })
    })
    
    const data = await res.json()
    if (!res.ok) throw new Error(data.error || 'Verification failed')
    
    router.push('/barber/payments')
  } catch (err) {
    error.value = err.message
  } finally {
    isProcessing.value = false
  }
}
</script>

<template>
  <BarberLayout>
    <div class="max-w-6xl mx-auto h-[calc(100vh-160px)] flex flex-col">
      <div class="flex items-center gap-4 mb-6 shrink-0">
        <button @click="router.push('/barber/payments')" class="p-2 rounded-xl bg-white/5 border border-white/10 text-ivory/70 hover:bg-white/10 hover:text-white transition-all">
          <ArrowLeftIcon class="w-5 h-5" />
        </button>
        <div>
          <h1 class="text-3xl font-bold text-ivory">Receipt Details</h1>
          <p class="text-ivory/60 mt-1">Review the payment receipt for this booking.</p>
        </div>
      </div>

      <div v-if="isLoading" class="flex justify-center p-12 shrink-0">
        <ArrowPathIcon class="w-8 h-8 text-ivory/30 animate-spin" />
      </div>

      <div v-else-if="error" class="bg-red-500/10 border border-red-500/20 text-red-400 p-6 rounded-2xl shrink-0">
        {{ error }}
      </div>

      <div v-else-if="booking" class="bg-theme-surface/95 backdrop-blur-xl border border-white/10 rounded-3xl p-6 sm:p-8 shadow-2xl grid grid-cols-1 md:grid-cols-2 gap-8 flex-1 min-h-0">
      
      <!-- Left Column: Receipt Image/Preview -->
      <div class="flex flex-col h-full min-h-[400px]">
        <div class="flex-1 overflow-hidden bg-black/50 rounded-2xl border border-white/5 flex items-center justify-center p-4">
          <template v-if="!receiptSrc">
            <p class="text-ivory/40">No receipt uploaded yet.</p>
          </template>
          <template v-else-if="isPdf">
            <div class="text-center">
              <div class="w-16 h-16 mx-auto mb-4 bg-white/5 rounded-2xl flex items-center justify-center">
                <svg class="w-8 h-8 text-red-400" fill="currentColor" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6zm-1 2l5 5h-5V4z"/></svg>
              </div>
              <p class="text-ivory mb-4">This receipt is a PDF.</p>
              <a :href="receiptSrc" target="_blank" class="text-gold hover:text-gold/80 underline font-medium">View PDF in new tab →</a>
            </div>
          </template>
          <template v-else>
            <img :src="receiptSrc" alt="Payment Receipt" class="max-h-full max-w-full h-full w-full object-contain rounded-xl" />
          </template>
        </div>
      </div>

      <!-- Right Column: Details & Actions -->
      <div class="flex flex-col h-full">
        <div class="mb-6">
          <p class="text-ivory/60 text-lg">
            <strong class="text-ivory">{{ booking.customer?.name }}</strong> — {{ booking.service?.name }}
            <br>
            <span class="text-gold font-bold">₦{{ (booking.service?.price || 0).toLocaleString() }}</span>
          </p>
        </div>

        <!-- Booking Info -->
        <div class="bg-black/30 rounded-xl p-6 border border-white/5 mb-6 space-y-4 text-sm">
          <div class="flex justify-between items-center border-b border-white/5 pb-3">
            <span class="text-ivory/50">Date & Time</span>
            <span class="text-ivory font-medium">{{ new Date(booking.appointment_date).toLocaleDateString() }} at {{ booking.appointment_time }}</span>
          </div>
          <div class="flex justify-between items-center border-b border-white/5 pb-3">
            <span class="text-ivory/50">Barber</span>
            <span class="text-ivory font-medium">{{ booking.barber?.name }}</span>
          </div>
          <div v-if="booking.transaction_ref" class="flex justify-between items-center border-b border-white/5 pb-3">
            <span class="text-ivory/50">Transaction Ref</span>
            <span class="text-ivory font-mono text-xs bg-white/5 px-2 py-1 rounded">{{ booking.transaction_ref }}</span>
          </div>
          <div class="flex justify-between items-center pt-1">
            <span class="text-ivory/50">Payment Status</span>
            <span class="text-amber-400 font-bold uppercase tracking-wider text-[10px] bg-amber-400/10 border border-amber-400/20 px-2 py-1 rounded-full">{{ booking.payment_status }}</span>
          </div>
        </div>

        <!-- Actions -->
        <div v-if="(!booking.payment_status || ['pending', 'awaiting_verification'].includes(booking.payment_status.toLowerCase()))" class="flex flex-col sm:flex-row items-center gap-4 mt-auto">
          <button 
            @click="verifyPayment('reject')"
            :disabled="isProcessing"
            class="w-full sm:flex-1 py-4 px-6 rounded-xl border border-red-500/30 text-red-400 hover:bg-red-500/10 transition-colors font-semibold flex items-center justify-center gap-2 disabled:opacity-50">
            <XCircleIcon class="w-5 h-5" />
            <span>Reject</span>
          </button>
          
          <button 
            @click="verifyPayment('approve')"
            :disabled="isProcessing"
            class="w-full sm:flex-1 py-4 px-6 rounded-xl bg-gradient-to-r from-gold to-gold-light text-obsidian hover:shadow-[0_0_20px_rgba(212,175,55,0.4)] transition-all font-bold flex items-center justify-center gap-2 disabled:opacity-50">
            <ArrowPathIcon v-if="isProcessing" class="w-5 h-5 animate-spin" />
            <CheckIcon v-else class="w-5 h-5" />
            <span>Approve Payment</span>
          </button>
        </div>
      </div>
      
    </div>
  </div>
  </BarberLayout>
</template>
