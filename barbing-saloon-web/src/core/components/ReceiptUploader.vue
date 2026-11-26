<script setup>
import { ref, watch, onMounted } from 'vue'
import { CloudArrowUpIcon, DocumentTextIcon, XMarkIcon, CheckCircleIcon, ArrowPathIcon, DocumentDuplicateIcon } from '@heroicons/vue/24/outline'
import { customerApi } from '@/shared/api/old_customerApi';
import { client } from '@/shared/api/client';
import { useToast } from '@/core/composables/useToast';

const toast = useToast();

const props = defineProps({
  appointmentId: {
    type: [Number, String],
    required: true
  },
  initialBankDetails: {
    type: Object,
    default: () => null
  }
})

const emit = defineEmits(['uploaded'])

const file = ref(null)
const previewUrl = ref(null)
const isUploading = ref(false)
const error = ref('')

// Dynamic bank details from settings
const bankDetails = ref({
  bank_name: '',
  account_name: '',
  account_number: ''
})
const loadingSettings = ref(true)

async function copyAccountNumber(num) {
  if (!num) return
  try {
    await navigator.clipboard.writeText(String(num))
    toast.success('Account number copied!')
  } catch (err) {
    toast.error('Failed to copy')
  }
}

onMounted(async () => {
  if (props.initialBankDetails && props.initialBankDetails.bank_name && props.initialBankDetails.account_number) {
    bankDetails.value.bank_name = props.initialBankDetails.bank_name
    bankDetails.value.account_name = props.initialBankDetails.account_name || ''
    bankDetails.value.account_number = props.initialBankDetails.account_number
    loadingSettings.value = false
  }

  try {
    const res = await customerApi.getPaymentDetails(props.appointmentId)
    const data = res.data?.data || res.data || {}
    if (data.bank_name && data.account_number) {
      bankDetails.value.bank_name = data.bank_name
      bankDetails.value.account_name = data.account_name || ''
      bankDetails.value.account_number = data.account_number
    }
  } catch (err) {
    console.warn('Failed to load payment settings', err)
  }

  // Fallback to global salon settings if still missing
  if (!bankDetails.value.bank_name || !bankDetails.value.account_number) {
    try {
      const setRes = await client.get('/settings')
      const sData = setRes.data?.data || setRes.data || {}
      if (sData.bank_name && sData.account_number) {
        bankDetails.value.bank_name = sData.bank_name
        bankDetails.value.account_name = sData.account_name || 'Candy Cutz Luxury Grooming'
        bankDetails.value.account_number = sData.account_number
      }
    } catch (e) {
      // Non-blocking fallback
    }
  }

  loadingSettings.value = false
})

const hasBankDetails = () => {
  return Boolean(bankDetails.value.bank_name && bankDetails.value.account_name && bankDetails.value.account_number)
}

const handleFileChange = (e) => {
  const selected = e.target.files[0]
  if (!selected) return
  
  // Validate type
  const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'application/pdf']
  if (!allowedTypes.includes(selected.type)) {
    error.value = 'Please select a valid image (JPG, PNG) or PDF.'
    return
  }

  // Validate size (max 10MB)
  if (selected.size > 10 * 1024 * 1024) {
    error.value = 'Receipt file size must be less than 10MB.'
    return
  }
  
  error.value = ''
  file.value = selected
  
  if (selected.type.startsWith('image/')) {
    previewUrl.value = URL.createObjectURL(selected)
  } else {
    previewUrl.value = null // PDF
  }
}

const clearFile = () => {
  file.value = null
  previewUrl.value = null
  error.value = ''
}

const uploadReceipt = async () => {
  if (!file.value) return
  
  isUploading.value = true
  error.value = ''
  
  const formData = new FormData()
  formData.append('receipt', file.value)
  
  try {
    const res = await customerApi.uploadReceipt(props.appointmentId, formData)
    emit('uploaded', res.data)
  } catch (err) {
    const rawMsg = err.response?.data?.message || err.response?.data?.error || err.message || 'Upload failed'
    if (typeof rawMsg === 'string' && (rawMsg.includes('SQLSTATE') || rawMsg.includes('Base table'))) {
      error.value = 'Failed to submit receipt. Please try again or contact salon support.'
    } else {
      error.value = typeof rawMsg === 'string' ? rawMsg : 'Upload failed. Please check the file and try again.'
    }
  } finally {
    isUploading.value = false
  }
}

// Cleanup object url
watch(previewUrl, (newUrl, oldUrl) => {
  if (oldUrl) URL.revokeObjectURL(oldUrl)
})
</script>

<template>
  <div class="bg-[var(--color-bg-secondary)] md:backdrop-blur-xl border border-[var(--color-border)] rounded-xl md:rounded-3xl p-4 sm:p-6">
    <h3 class="text-lg sm:text-xl font-bold text-[var(--color-text-primary)] mb-1.5 text-left-forced">Upload Payment Receipt</h3>
    <p class="text-[var(--color-text-secondary)] text-xs sm:text-sm mb-4 sm:mb-6 text-left-forced">Please upload a screenshot or PDF of your payment confirmation.</p>
    
    <div v-if="error" class="bg-[var(--color-danger)]/10 border border-[var(--color-danger)]/20 text-[var(--color-danger)] p-3 rounded-lg md:rounded-xl mb-4 sm:mb-6 text-xs sm:text-sm text-left-forced">
      {{ error }}
    </div>

    <!-- Bank Details Card (Dynamic from Settings) -->
    <div v-if="loadingSettings" class="mb-4 sm:mb-6 bg-[var(--color-bg-tertiary)] border border-[var(--color-border)] rounded-xl md:rounded-2xl p-4 sm:p-5 text-center">
      <ArrowPathIcon class="h-6 w-6 text-[var(--color-accent)] animate-spin mx-auto mb-2" />
      <p class="text-[var(--color-text-muted)] text-xs sm:text-sm text-center">Loading payment details...</p>
    </div>
    
    <div v-else-if="hasBankDetails()" class="mb-4 sm:mb-6 bg-[var(--color-bg-tertiary)] border border-[var(--color-border)] rounded-xl md:rounded-2xl p-3.5 sm:p-5 relative overflow-hidden">
      <div class="hidden md:block absolute -right-10 -top-10 h-32 w-32 rounded-full bg-[var(--color-accent)]/10 blur-2xl pointer-events-none"></div>
      <h4 class="text-xs uppercase tracking-widest text-[var(--color-accent)] mb-3 font-semibold text-left-forced">Transfer Payment To</h4>
      
      <div class="space-y-2.5 sm:space-y-3 text-xs sm:text-sm">
        <div class="flex justify-between items-start gap-3 border-b border-[var(--color-border)] pb-2">
          <span class="text-[var(--color-text-secondary)] shrink-0">Bank Name</span>
          <span class="text-[var(--color-text-primary)] font-medium text-right min-w-0 break-words">{{ bankDetails.bank_name }}</span>
        </div>
        <div class="flex justify-between items-start gap-3 border-b border-[var(--color-border)] pb-2">
          <span class="text-[var(--color-text-secondary)] shrink-0">Account Name</span>
          <span class="text-[var(--color-text-primary)] font-medium text-right min-w-0 break-words">{{ bankDetails.account_name }}</span>
        </div>
        <div class="flex flex-wrap justify-between items-center gap-2 bg-[var(--color-bg-primary)] md:bg-black/30 rounded-lg md:rounded-xl px-3 py-2 border border-[var(--color-border)] md:border-white/[0.06]">
          <span class="text-[var(--color-text-secondary)] shrink-0">Account Number</span>
          <div class="flex items-center gap-2 min-w-0">
            <span class="text-[var(--color-accent)] font-mono text-sm sm:text-base font-bold tracking-wider tabular-nums select-all">{{ bankDetails.account_number }}</span>
            <button
              type="button"
              @click="copyAccountNumber(bankDetails.account_number)"
              class="min-h-[36px] min-w-[36px] p-1.5 text-[var(--color-accent)] hover:bg-[var(--color-accent)]/15 rounded-md transition-colors inline-flex items-center justify-center shrink-0"
              title="Copy Account Number"
              aria-label="Copy Account Number"
            >
              <DocumentDuplicateIcon class="h-4 w-4" />
            </button>
          </div>
        </div>
      </div>
    </div>

    <div v-else class="mb-4 sm:mb-6 bg-amber-500/10 border border-amber-500/20 rounded-xl md:rounded-2xl p-4 sm:p-5 text-amber-600 dark:text-amber-400 text-xs sm:text-sm text-center">
      Payment account details have not been configured yet. Please contact the salon.
    </div>

    <!-- Upload Area -->
    <div v-if="!file" class="relative">
      <input type="file" @change="handleFileChange" accept="image/*,.pdf" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" />
      <div class="border-2 border-dashed border-[var(--color-border)] rounded-xl md:rounded-2xl p-5 sm:p-8 text-center flex flex-col items-center justify-center bg-[var(--color-bg-primary)]/50 group hover:border-[var(--color-accent)]/30 hover:bg-[var(--color-bg-tertiary)]/50 transition-all">
        <div class="h-12 w-12 sm:h-16 sm:w-16 bg-[var(--color-bg-secondary)] rounded-full flex items-center justify-center mb-3 sm:mb-4 group-hover:scale-110 transition-transform shadow-none sm:shadow-sm">
          <CloudArrowUpIcon class="h-6 w-6 sm:h-8 sm:w-8 text-[var(--color-accent)]" />
        </div>
        <p class="text-[var(--color-text-primary)] text-sm font-medium text-center">Click to upload or drag and drop</p>
        <p class="text-[var(--color-text-muted)] text-xs mt-1.5 sm:mt-2 text-center">PNG, JPG or PDF (max. 5MB)</p>
      </div>
    </div>
    
    <!-- Preview Area -->
    <div v-else class="space-y-4 sm:space-y-6">
      <div class="relative bg-[var(--color-bg-tertiary)] rounded-xl md:rounded-2xl p-3.5 sm:p-4 border border-[var(--color-border)] flex items-center gap-3 sm:gap-4 overflow-hidden group">
        <div class="h-14 w-14 sm:h-16 sm:w-16 bg-[var(--color-bg-secondary)] rounded-lg md:rounded-xl flex-shrink-0 flex items-center justify-center overflow-hidden border border-[var(--color-border)]">
          <img v-if="previewUrl" :src="previewUrl" class="w-full h-full object-cover" />
          <DocumentTextIcon v-else class="h-7 w-7 sm:h-8 sm:w-8 text-[var(--color-text-muted)]" />
        </div>
        
        <div class="flex-1 min-w-0">
          <p class="text-[var(--color-text-primary)] text-sm font-medium truncate text-left-forced">{{ file.name }}</p>
          <p class="text-[var(--color-text-muted)] text-xs mt-1 tabular-nums text-left-forced">{{ (file.size / 1024 / 1024).toFixed(2) }} MB</p>
        </div>
        
        <button @click="clearFile" :disabled="isUploading" type="button" aria-label="Remove selected file" class="min-h-[38px] min-w-[38px] p-2 bg-[var(--color-bg-secondary)] hover:bg-[var(--color-danger)]/10 text-[var(--color-text-secondary)] hover:text-[var(--color-danger)] rounded-lg transition-colors z-20 border border-[var(--color-border)] inline-flex items-center justify-center shrink-0">
          <XMarkIcon class="h-4 w-4" />
        </button>
      </div>
      
      <button 
        @click="uploadReceipt" 
        :disabled="isUploading"
        type="button"
        class="w-full min-h-[44px] relative overflow-hidden group bg-[var(--color-accent)] text-[var(--color-accent-foreground)] px-5 py-3 sm:py-4 rounded-lg md:rounded-xl font-bold text-sm sm:text-base md:text-lg hover:bg-[var(--color-accent-hover)] transition-all shadow-none md:shadow-md disabled:opacity-70 disabled:cursor-not-allowed">
        <div class="hidden md:block absolute inset-0 bg-white/20 translate-y-full group-hover:translate-y-0 transition-transform duration-300 ease-out"></div>
        <div class="relative flex items-center justify-center gap-2">
          <ArrowPathIcon v-if="isUploading" class="h-5 w-5 animate-spin" />
          <CheckCircleIcon v-else class="h-5 w-5" />
          <span>{{ isUploading ? 'Uploading...' : 'Submit Receipt' }}</span>
        </div>
      </button>
    </div>
  </div>
</template>
