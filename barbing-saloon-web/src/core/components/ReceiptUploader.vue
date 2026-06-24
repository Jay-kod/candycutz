<script setup>
import { ref, watch, onMounted } from 'vue'
import { CloudArrowUpIcon, DocumentTextIcon, XMarkIcon, CheckCircleIcon, ArrowPathIcon } from '@heroicons/vue/24/outline'
import { publicApi } from '../../modules/public/api/public.api'
import { customerApi } from '../../modules/customer/api/customer.api'

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
  <div class="bg-theme-surface/60 backdrop-blur-xl border border-white/5 rounded-3xl p-6">
    <h3 class="text-xl font-bold text-ivory mb-2">Upload Payment Receipt</h3>
    <p class="text-ivory/50 text-sm mb-6">Please upload a screenshot or PDF of your payment confirmation.</p>
    
    <div v-if="error" class="bg-red-500/10 border border-red-500/20 text-red-400 p-3 rounded-xl mb-6 text-sm">
      {{ error }}
    </div>

    <!-- Bank Details Card (Dynamic from Settings) -->
    <div v-if="loadingSettings" class="mb-6 bg-black/40 border border-white/10 rounded-2xl p-5 text-center">
      <ArrowPathIcon class="h-6 w-6 text-gold animate-spin mx-auto mb-2" />
      <p class="text-ivory/40 text-sm">Loading payment details...</p>
    </div>
    
    <div v-else-if="hasBankDetails()" class="mb-6 bg-black/40 border border-white/10 rounded-2xl p-5 relative overflow-hidden">
      <div class="absolute -right-10 -top-10 h-32 w-32 rounded-full bg-gold/10 blur-2xl pointer-events-none"></div>
      <h4 class="text-xs uppercase tracking-widest text-gold mb-3 font-semibold">Transfer Payment To</h4>
      
      <div class="space-y-3">
        <div class="flex justify-between items-center border-b border-white/5 pb-2">
          <span class="text-ivory/50 text-sm">Bank Name</span>
          <span class="text-ivory font-medium">{{ bankDetails.bank_name }}</span>
        </div>
        <div class="flex justify-between items-center border-b border-white/5 pb-2">
          <span class="text-ivory/50 text-sm">Account Name</span>
          <span class="text-ivory font-medium">{{ bankDetails.account_name }}</span>
        </div>
        <div class="flex justify-between items-center">
          <span class="text-ivory/50 text-sm">Account Number</span>
          <span class="text-gold font-display text-lg tracking-wider">{{ bankDetails.account_number }}</span>
        </div>
      </div>
    </div>

    <div v-else class="mb-6 bg-amber-500/10 border border-amber-500/20 rounded-2xl p-5 text-amber-400 text-sm text-center">
      Payment account details have not been configured yet. Please contact the salon.
    </div>

    <!-- Upload Area -->
    <div v-if="!file" class="relative">
      <input type="file" @change="handleFileChange" accept="image/*,.pdf" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" />
      <div class="border-2 border-dashed border-white/10 rounded-2xl p-8 text-center flex flex-col items-center justify-center bg-white/5 group hover:border-gold/30 hover:bg-white/10 transition-all">
        <div class="h-16 w-16 bg-white/5 rounded-full flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
          <CloudArrowUpIcon class="h-8 w-8 text-gold" />
        </div>
        <p class="text-ivory font-medium">Click to upload or drag and drop</p>
        <p class="text-ivory/40 text-xs mt-2">PNG, JPG or PDF (max. 5MB)</p>
      </div>
    </div>
    
    <!-- Preview Area -->
    <div v-else class="space-y-6">
      <div class="relative bg-black/30 rounded-2xl p-4 border border-white/10 flex items-center gap-4 overflow-hidden group">
        <div class="h-16 w-16 bg-white/5 rounded-xl flex-shrink-0 flex items-center justify-center overflow-hidden">
          <img v-if="previewUrl" :src="previewUrl" class="w-full h-full object-cover" />
          <DocumentTextIcon v-else class="h-8 w-8 text-ivory/50" />
        </div>
        
        <div class="flex-1 min-w-0">
          <p class="text-ivory text-sm font-medium truncate">{{ file.name }}</p>
          <p class="text-ivory/40 text-xs mt-1">{{ (file.size / 1024 / 1024).toFixed(2) }} MB</p>
        </div>
        
        <button @click="clearFile" :disabled="isUploading" class="p-2 bg-white/10 hover:bg-red-500/20 text-ivory hover:text-red-400 rounded-lg transition-colors z-20">
          <XMarkIcon class="h-4 w-4" />
        </button>
      </div>
      
      <button 
        @click="uploadReceipt" 
        :disabled="isUploading"
        class="w-full relative overflow-hidden group bg-gold text-obsidian px-6 py-4 rounded-xl font-bold text-lg hover:bg-yellow-400 transition-all shadow-[0_0_20px_rgba(212,175,55,0.3)] disabled:opacity-70 disabled:cursor-not-allowed">
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
