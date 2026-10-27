<script setup>
import { ref, watch, onMounted } from 'vue'
import { CloudArrowUpIcon, DocumentTextIcon, XMarkIcon, CheckCircleIcon, ArrowPathIcon } from '@heroicons/vue/24/outline'
import { customerApi } from '@/shared/api/old_customerApi';

const props = defineProps({
  appointmentId: {
    type: [Number, String],
    required: true
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

onMounted(async () => {
  try {
    const res = await customerApi.getPaymentDetails(props.appointmentId)
    const data = res.data.data || {}
    bankDetails.value.bank_name = data.bank_name || ''
    bankDetails.value.account_name = data.account_name || ''
    bankDetails.value.account_number = data.account_number || ''
  } catch (err) {
    console.error('Failed to load payment settings', err)
  } finally {
    loadingSettings.value = false
  }
})

const hasBankDetails = () => {
  return bankDetails.value.bank_name && bankDetails.value.account_name && bankDetails.value.account_number
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
    error.value = err.response?.data?.error || err.message || 'Upload failed'
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
  <div class="bg-[var(--color-bg-secondary)] backdrop-blur-xl border border-[var(--color-border)] rounded-3xl p-6">
    <h3 class="text-xl font-bold text-[var(--color-text-primary)] mb-2">Upload Payment Receipt</h3>
    <p class="text-[var(--color-text-secondary)] text-sm mb-6">Please upload a screenshot or PDF of your payment confirmation.</p>
    
    <div v-if="error" class="bg-[var(--color-danger)]/10 border border-[var(--color-danger)]/20 text-[var(--color-danger)] p-3 rounded-xl mb-6 text-sm">
      {{ error }}
    </div>

    <!-- Bank Details Card (Dynamic from Settings) -->
    <div v-if="loadingSettings" class="mb-6 bg-[var(--color-bg-tertiary)] border border-[var(--color-border)] rounded-2xl p-5 text-center">
      <ArrowPathIcon class="h-6 w-6 text-[var(--color-accent)] animate-spin mx-auto mb-2" />
      <p class="text-[var(--color-text-muted)] text-sm">Loading payment details...</p>
    </div>
    
    <div v-else-if="hasBankDetails()" class="mb-6 bg-[var(--color-bg-tertiary)] border border-[var(--color-border)] rounded-2xl p-5 relative overflow-hidden">
      <div class="absolute -right-10 -top-10 h-32 w-32 rounded-full bg-[var(--color-accent)]/10 blur-2xl pointer-events-none"></div>
      <h4 class="text-xs uppercase tracking-widest text-[var(--color-accent)] mb-3 font-semibold">Transfer Payment To</h4>
      
      <div class="space-y-3">
        <div class="flex justify-between items-center border-b border-[var(--color-border)] pb-2">
          <span class="text-[var(--color-text-secondary)] text-sm">Bank Name</span>
          <span class="text-[var(--color-text-primary)] font-medium">{{ bankDetails.bank_name }}</span>
        </div>
        <div class="flex justify-between items-center border-b border-[var(--color-border)] pb-2">
          <span class="text-[var(--color-text-secondary)] text-sm">Account Name</span>
          <span class="text-[var(--color-text-primary)] font-medium">{{ bankDetails.account_name }}</span>
        </div>
        <div class="flex justify-between items-center">
          <span class="text-[var(--color-text-secondary)] text-sm">Account Number</span>
          <span class="text-[var(--color-accent)] font-display text-lg tracking-wider">{{ bankDetails.account_number }}</span>
        </div>
      </div>
    </div>

    <div v-else class="mb-6 bg-amber-500/10 border border-amber-500/20 rounded-2xl p-5 text-amber-500 text-sm text-center">
      Payment account details have not been configured yet. Please contact the salon.
    </div>

    <!-- Upload Area -->
    <div v-if="!file" class="relative">
      <input type="file" @change="handleFileChange" accept="image/*,.pdf" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" />
      <div class="border-2 border-dashed border-[var(--color-border)] rounded-2xl p-8 text-center flex flex-col items-center justify-center bg-[var(--color-bg-primary)]/50 group hover:border-[var(--color-accent)]/30 hover:bg-[var(--color-bg-tertiary)]/50 transition-all">
        <div class="h-16 w-16 bg-[var(--color-bg-secondary)] rounded-full flex items-center justify-center mb-4 group-hover:scale-110 transition-transform shadow-sm">
          <CloudArrowUpIcon class="h-8 w-8 text-[var(--color-accent)]" />
        </div>
        <p class="text-[var(--color-text-primary)] font-medium">Click to upload or drag and drop</p>
        <p class="text-[var(--color-text-muted)] text-xs mt-2">PNG, JPG or PDF (max. 5MB)</p>
      </div>
    </div>
    
    <!-- Preview Area -->
    <div v-else class="space-y-6">
      <div class="relative bg-[var(--color-bg-tertiary)] rounded-2xl p-4 border border-[var(--color-border)] flex items-center gap-4 overflow-hidden group">
        <div class="h-16 w-16 bg-[var(--color-bg-secondary)] rounded-xl flex-shrink-0 flex items-center justify-center overflow-hidden border border-[var(--color-border)]">
          <img v-if="previewUrl" :src="previewUrl" class="w-full h-full object-cover" />
          <DocumentTextIcon v-else class="h-8 w-8 text-[var(--color-text-muted)]" />
        </div>
        
        <div class="flex-1 min-w-0">
          <p class="text-[var(--color-text-primary)] text-sm font-medium truncate">{{ file.name }}</p>
          <p class="text-[var(--color-text-muted)] text-xs mt-1">{{ (file.size / 1024 / 1024).toFixed(2) }} MB</p>
        </div>
        
        <button @click="clearFile" :disabled="isUploading" class="p-2 bg-[var(--color-bg-secondary)] hover:bg-[var(--color-danger)]/10 text-[var(--color-text-secondary)] hover:text-[var(--color-danger)] rounded-lg transition-colors z-20 border border-[var(--color-border)]">
          <XMarkIcon class="h-4 w-4" />
        </button>
      </div>
      
      <button 
        @click="uploadReceipt" 
        :disabled="isUploading"
        class="w-full relative overflow-hidden group bg-[var(--color-accent)] text-[var(--color-accent-foreground)] px-6 py-4 rounded-xl font-bold text-lg hover:bg-[var(--color-accent-hover)] transition-all shadow-md disabled:opacity-70 disabled:cursor-not-allowed">
        <div class="absolute inset-0 bg-white/20 translate-y-full group-hover:translate-y-0 transition-transform duration-300 ease-out"></div>
        <div class="relative flex items-center justify-center gap-2">
          <ArrowPathIcon v-if="isUploading" class="h-5 w-5 animate-spin" />
          <CheckCircleIcon v-else class="h-5 w-5" />
          <span>{{ isUploading ? 'Uploading...' : 'Submit Receipt' }}</span>
        </div>
      </button>
    </div>
  </div>
</template>
