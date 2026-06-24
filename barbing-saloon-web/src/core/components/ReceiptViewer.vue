<script setup>
import { ref, computed } from 'vue'
import { XMarkIcon, CheckIcon, XCircleIcon, ArrowPathIcon, DocumentIcon, ArrowTopRightOnSquareIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  booking: {
    type: Object,
    required: true
  },
  portal: {
    type: String,
    default: 'admin'
  }
})

const emit = defineEmits(['close', 'approve', 'reject'])

const isProcessing = ref(false)
const error = ref('')

const receiptSrc = computed(() => {
  let img = props.booking.receipt_image
  if (!img) return null
  if (img.startsWith('http')) return img
  if (img.startsWith('/public/')) {
    img = img.replace('/public/', '/')
  }
  return `http://localhost:8000${img}`
})

const isPdf = computed(() => {
  return props.booking.receipt_image?.toLowerCase().endsWith('.pdf')
})

const verifyPayment = async (action) => {
  isProcessing.value = true
  error.value = ''
  
  try {
    const token = localStorage.getItem('candycutz_auth_token')
    let endpoint, method, body;
    
    if (props.portal === 'barber') {
      endpoint = `/barber/bookings/${props.booking.id}/verify-payment`
      method = 'POST'
      body = JSON.stringify({ action: action === 'approve' ? 'approve' : 'reject' })
    } else {
      endpoint = action === 'approve'
        ? `/admin/appointments/${props.booking.id}/approve`
        : `/admin/appointments/${props.booking.id}/cancel`
      method = 'PATCH'
      body = undefined
    }
    
    const res = await fetch(`http://localhost:8000${endpoint}`, {
      method,
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${token}`
      },
      body
    })
    
    const data = await res.json()
    if (!res.ok) throw new Error(data.error || 'Verification failed')
    
    emit(action === 'approve' ? 'approve' : 'reject')
    emit('close')
  } catch (err) {
    error.value = err.message
  } finally {
    isProcessing.value = false
  }
}
</script>

<template>
  <div class="fixed inset-0 z-[70] flex items-center justify-center p-0">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-[#050505] transition-opacity" @click="emit('close')"></div>
    
    <!-- Modal (Page-like Full Screen) -->
    <div class="bg-[#111111] relative w-full max-w-none h-screen flex flex-col md:flex-row overflow-hidden animate-slide-up z-10">
      
      <!-- Close Button (Absolute to Modal) -->
      <button @click="emit('close')" class="absolute top-6 right-6 z-20 text-white/50 hover:text-white bg-black/20 hover:bg-black/40 backdrop-blur-md rounded-full p-2.5 transition-all border border-white/5">
        <XMarkIcon class="w-6 h-6" />
      </button>

      <!-- Left Side: Receipt Viewer -->
      <div class="w-full md:w-[60%] lg:w-[65%] h-[50vh] md:h-full bg-black/80 relative flex items-center justify-center p-8 border-b md:border-b-0 md:border-r border-white/[0.05]">
        <!-- Decorative Glow -->
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_center,rgba(255,103,0,0.05),transparent_70%)]"></div>
        
        <div class="relative z-10 w-full h-full flex items-center justify-center">
          <template v-if="!receiptSrc">
            <div class="flex flex-col items-center justify-center text-white/30">
              <DocumentIcon class="h-16 w-16 mb-4 opacity-30" />
              <p class="font-medium">No receipt uploaded yet.</p>
            </div>
          </template>
          <template v-else-if="isPdf">
            <div class="text-center">
              <div class="w-20 h-20 mx-auto mb-6 bg-white/5 rounded-3xl flex items-center justify-center border border-white/10 shadow-lg">
                <svg class="w-10 h-10 text-red-400" fill="currentColor" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6zm-1 2l5 5h-5V4z"/></svg>
              </div>
              <p class="text-white/80 mb-6 text-lg font-medium">This receipt is a PDF document.</p>
              <a :href="receiptSrc" target="_blank" class="px-6 py-3.5 rounded-2xl bg-admin/10 text-admin font-bold border border-admin/20 hover:bg-admin hover:text-white transition-all inline-flex items-center gap-2">
                Open PDF Document <ArrowTopRightOnSquareIcon class="h-5 w-5" />
              </a>
            </div>
          </template>
          <template v-else>
            <div class="w-full h-full p-2 bg-white/[0.02] border border-white/[0.05] rounded-3xl overflow-hidden shadow-2xl backdrop-blur-sm">
              <img :src="receiptSrc" alt="Payment Receipt" class="w-full h-full object-contain rounded-2xl" />
            </div>
          </template>
        </div>
      </div>

      <!-- Right Side: Details & Actions -->
      <div class="w-full md:w-[40%] lg:w-[35%] h-[50vh] md:h-full flex flex-col bg-gradient-to-b from-[#151515] to-[#0a0a0a] overflow-y-auto custom-scrollbar">
        <div class="p-8 md:p-10 flex flex-col min-h-full">
          
          <div class="mb-8 pr-12">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-admin/10 border border-admin/20 text-admin text-[10px] font-bold uppercase tracking-widest mb-4 shadow-sm">
              Payment Verification
            </div>
            <h3 class="font-display text-3xl font-bold text-white leading-tight mb-2">Booking Details</h3>
            <p class="text-white/50 text-sm leading-relaxed">
              <span class="text-white font-bold">{{ booking.customer?.name }}</span> booked <span class="text-white font-bold">{{ booking.service?.name }}</span>
            </p>
          </div>

          <div v-if="error" class="bg-red-500/10 border border-red-500/20 text-red-400 p-4 rounded-xl mb-6 text-sm font-medium">
            {{ error }}
          </div>

          <!-- Booking Info Table -->
          <div class="bg-black/40 rounded-2xl border border-white/[0.05] mb-8">
            <div class="flex justify-between items-center p-5 border-b border-white/[0.05]">
              <span class="text-white/40 uppercase tracking-wider text-[10px] font-bold">Amount Due</span>
              <span class="text-emerald-400 font-bold text-xl">₦{{ (booking.service?.price || 0).toLocaleString() }}</span>
            </div>
            <div class="flex justify-between items-center p-5 border-b border-white/[0.05]">
              <span class="text-white/40 uppercase tracking-wider text-[10px] font-bold">Date & Time</span>
              <span class="text-white font-medium text-sm text-right leading-tight">
                {{ new Date(booking.appointment_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) }} <br> 
                <span class="text-admin">{{ booking.appointment_time }}</span>
              </span>
            </div>
            <div class="flex justify-between items-center p-5 border-b border-white/[0.05]">
              <span class="text-white/40 uppercase tracking-wider text-[10px] font-bold">Provider</span>
              <span class="text-white text-sm font-bold flex items-center gap-1.5"><div class="h-2 w-2 rounded-full bg-admin/50"></div> {{ booking.barber?.name || 'Any Barber' }}</span>
            </div>
            <div v-if="booking.transaction_ref" class="flex justify-between items-center p-5 border-b border-white/[0.05]">
              <span class="text-white/40 uppercase tracking-wider text-[10px] font-bold">Trans. Ref</span>
              <span class="text-white/80 font-mono text-[11px] bg-white/5 px-2 py-1 rounded">{{ booking.transaction_ref }}</span>
            </div>
            <div class="flex justify-between items-center p-5">
              <span class="text-white/40 uppercase tracking-wider text-[10px] font-bold">Status</span>
              <span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-widest border border-amber-500/30 text-amber-400 bg-[#111] shadow-sm">{{ booking.payment_status?.replace('_', ' ') || 'UNPAID' }}</span>
            </div>
          </div>

          <!-- Actions -->
          <div class="mt-auto space-y-3 pt-4 border-t border-white/[0.05]">
            <template v-if="(portal === 'admin' || portal === 'barber') && (!booking.payment_status || ['pending', 'awaiting_verification'].includes(booking.payment_status.toLowerCase()))">
              <button 
                @click="verifyPayment('approve')"
                :disabled="isProcessing"
                class="w-full py-4.5 px-6 rounded-2xl bg-gradient-to-r from-emerald-500 to-emerald-400 text-obsidian hover:shadow-[0_8px_30px_rgba(16,185,129,0.3)] transition-all font-bold flex items-center justify-center gap-2 disabled:opacity-50 text-sm uppercase tracking-wider group relative overflow-hidden">
                <div class="absolute inset-0 bg-white/20 translate-y-full group-hover:translate-y-0 transition-transform duration-300"></div>
                <ArrowPathIcon v-if="isProcessing" class="w-5 h-5 animate-spin relative z-10" />
                <CheckIcon v-else class="w-5 h-5 group-hover:scale-110 transition-transform relative z-10" />
                <span class="relative z-10">Approve Payment</span>
              </button>

              <button 
                @click="verifyPayment('reject')"
                :disabled="isProcessing"
                class="w-full py-4 px-6 rounded-2xl border border-red-500/30 bg-red-500/5 text-red-400 hover:bg-red-500 hover:text-white hover:border-red-500 transition-all font-bold flex items-center justify-center gap-2 disabled:opacity-50 text-sm uppercase tracking-wider group">
                <XCircleIcon class="w-5 h-5 group-hover:scale-110 transition-transform" />
                <span>Reject Receipt</span>
              </button>
            </template>
            <template v-else>
               <button 
                @click="emit('close')"
                class="w-full py-4 px-6 rounded-2xl border border-white/10 bg-white/5 text-white/80 hover:bg-white/10 hover:text-white transition-all font-bold flex items-center justify-center gap-2 text-sm uppercase tracking-wider">
                <span>Close Viewer</span>
              </button>
            </template>
          </div>
          
        </div>
      </div>
      
    </div>
  </div>
</template>
<style scoped>
.animate-slide-up {
  animation: slideUp 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
@keyframes slideUp {
  from { opacity: 0; transform: translateY(40px) scale(0.98); }
  to { opacity: 1; transform: translateY(0) scale(1); }
}

/* Custom scrollbar for right pane */
.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: rgba(255, 255, 255, 0.1);
  border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: rgba(255, 255, 255, 0.2);
}
</style>
