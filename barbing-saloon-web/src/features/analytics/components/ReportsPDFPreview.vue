<template>
  <Teleport to="body">
    <Transition name="modal">
      <div v-if="show" class="fixed inset-0 z-[100] flex flex-col bg-obsidian/95 backdrop-blur-md">
        <!-- Preview Header -->
        <div class="flex items-center justify-between border-b border-white/10 bg-black/40 px-6 py-4">
          <div>
            <h2 class="text-xl font-bold text-theme-text">Report Preview</h2>
            <p class="text-sm text-ivory/60">CandyCutz {{ activeRangeLabel }} Analytics</p>
          </div>
          <div class="flex items-center gap-4">
            <button 
              @click="$emit('close')"
              class="rounded-full p-2 text-ivory/60 transition-colors hover:bg-white/5 hover:text-white"
            >
              <XMarkIcon class="h-6 w-6" />
            </button>
            <button 
              @click="downloadPdf"
              :disabled="isGeneratingPdf"
              class="flex items-center gap-2 rounded-xl bg-admin px-6 py-2.5 font-bold text-obsidian shadow-[0_0_15px_rgba(255,103,0,0.3)] transition-all hover:bg-admin-light hover:shadow-[0_0_25px_rgba(255,103,0,0.5)] disabled:opacity-50"
            >
              <template v-if="isGeneratingPdf">
                <div class="h-5 w-5 animate-spin rounded-full border-2 border-obsidian/30 border-t-obsidian"></div>
                Generating...
              </template>
              <template v-else>
                <ArrowDownTrayIcon class="h-5 w-5" />
                Download PDF
              </template>
            </button>
          </div>
        </div>

        <!-- Preview Scroll Area -->
        <div class="flex-1 overflow-y-auto p-8">
          <div class="mx-auto max-w-[794px] bg-white shadow-2xl">
            <!-- Actual PDF content to be captured -->
            <div ref="reportRef" class="report-document bg-white text-gray-900">
              <!-- Page 1: Summary & KPIs -->
              <ReportsPDFPage1
                :active-range-label="activeRangeLabel"
                :today-formatted="todayFormatted"
                :business-stats="businessStats"
                :platform-stats="platformStats"
                :status-breakdown="statusBreakdown"
                :total-appointments="totalAppointments"
                :status-bar-color="statusBarColor"
              />

              <!-- Page 2: Top Barbers & Services -->
              <ReportsPDFPage2
                :top-barbers="topBarbers"
                :top-services="topServices"
                :active-range-label="activeRangeLabel"
                :today-formatted="todayFormatted"
                :current-time="currentTime"
                :report-id="reportId"
              />
            </div>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, computed } from 'vue'
import { XMarkIcon, ArrowDownTrayIcon } from '@heroicons/vue/24/outline'
import ReportsPDFPage1 from './pdf/ReportsPDFPage1.vue'
import ReportsPDFPage2 from './pdf/ReportsPDFPage2.vue'

const props = defineProps({
  show: { type: Boolean, required: true },
  businessStats: { type: Object, required: true },
  platformStats: { type: Object, required: true },
  topBarbers: { type: Array, required: true },
  topServices: { type: Array, required: true },
  statusBreakdown: { type: Array, required: true },
  activeRangeLabel: { type: String, required: true },
  selectedRange: { type: String, required: true }
})

defineEmits(['close'])

const isGeneratingPdf = ref(false)
const reportRef = ref(null)

const todayFormatted = computed(() => {
  return new Date().toLocaleDateString('en-GB', { 
    weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' 
  })
})

const currentTime = computed(() => {
  return new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' })
})

const reportId = computed(() => {
  const timestamp = Date.now().toString(36).toUpperCase()
  const hash = Math.random().toString(36).substring(2, 8).toUpperCase()
  return `REF-CCZ-${timestamp}-${hash}`
})

const totalAppointments = computed(() => {
  return props.statusBreakdown.reduce((sum, s) => sum + Number(s.count), 0)
})

const statusBarColor = (status) => {
  const colors = {
    completed: 'linear-gradient(135deg, #059669, #10b981)',
    pending: 'linear-gradient(135deg, #d97706, #f59e0b)',
    cancelled: 'linear-gradient(135deg, #dc2626, #ef4444)',
    approved: 'linear-gradient(135deg, #2563eb, #3b82f6)',
    confirmed: 'linear-gradient(135deg, #2563eb, #3b82f6)',
    no_show: 'linear-gradient(135deg, #6b7280, #9ca3af)'
  }
  return colors[status] || 'linear-gradient(135deg, #6b7280, #9ca3af)'
}

const downloadPdf = async () => {
  if (!reportRef.value) return
  isGeneratingPdf.value = true
  
  try {
    const html2pdf = (await import('html2pdf.js')).default
    
    const opt = {
      margin: 0,
      filename: `CandyCutz_Report_${props.selectedRange}_${new Date().toISOString().slice(0, 10)}.pdf`,
      image: { type: 'jpeg', quality: 0.98 },
      html2canvas: { 
        scale: 2, 
        useCORS: true,
        backgroundColor: '#ffffff',
        letterRendering: true
      },
      jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' },
      pagebreak: { mode: ['css', 'legacy'] }
    }
    
    await html2pdf().set(opt).from(reportRef.value).save()
  } catch (err) {
    console.error('PDF generation failed:', err)
  } finally {
    isGeneratingPdf.value = false
  }
}
</script>

<style scoped>
.modal-enter-active,
.modal-leave-active {
  transition: all 0.3s ease;
}
.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}
.modal-enter-from .report-document,
.modal-leave-to .report-document {
  transform: translateY(40px) scale(0.95);
  opacity: 0;
}

.report-document {
  transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.3s ease;
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
  position: relative;
  width: 794px;
  margin: 0 auto;
}

:deep(.report-page) {
  width: 794px;
  position: relative;
  box-sizing: border-box;
}

:deep(.report-watermark) {
  position: absolute;
  inset: 0;
  z-index: 0;
  pointer-events: none;
  opacity: 0.06;
  background-image: url("data:image/svg+xml;base64,PHN2ZyB4bWxucz0naHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmcnIHdpZHRoPScxNjAnIGhlaWdodD0nMTAwJz48ZyB0cmFuc2Zvcm09J3JvdGF0ZSgtMzAgODAgNTApJz48dGV4dCB4PSc4MCcgeT0nLTI1JyBmb250LWZhbWlseT0nc2Fucy1zZXJpZicgZm9udC1zaXplPScxMCcgZm9udC13ZWlnaHQ9JzkwMCcgZmlsbD0nIzAwMCcgdGV4dC1hbmNob3I9J21pZGRsZSc+QWRtaW4gQ2FuZHlDdXR6IEJhcmJpbmcgU2Fsb29uPC90ZXh0Pjx0ZXh0IHg9JzAnIHk9Jy0xMCcgZm9udC1mYW1pbHk9J3NhbnMtc2VyaWYnIGZvbnQtc2l6ZT0nMTAnIGZvbnQtd2VpZ2h0PSc5MDAnIGZpbGw9JyMwMDAnIHRleHQtYW5jaG9yPSdtaWRkbGUnPkFkbWluIENhbmR5Q3V0eiBCYXJiaW5nIFNhbG9vbjwvdGV4dD48dGV4dCB4PScxNjAnIHk9Jy0xMCcgZm9udC1mYW1pbHk9J3NhbnMtc2VyaWYnIGZvbnQtc2l6ZT0nMTAnIGZvbnQtd2VpZ2h0PSc5MDAnIGZpbGw9JyMwMDAnIHRleHQtYW5jaG9yPSdtaWRkbGUnPkFkbWluIENhbmR5Q3V0eiBCYXJiaW5nIFNhbG9vbjwvdGV4dD48dGV4dCB4PSc4MCcgeT0nNScgZm9udC1mYW1pbHk9J3NhbnMtc2VyaWYnIGZvbnQtc2l6ZT0nMTAnIGZvbnQtd2VpZ2h0PSc5MDAnIGZpbGw9JyMwMDAnIHRleHQtYW5jaG9yPSdtaWRkbGUnPkFkbWluIENhbmR5Q3V0eiBCYXJiaW5nIFNhbG9vbjwvdGV4dD48dGV4dCB4PScwJyB5PScyMCcgZm9udC1mYW1pbHk9J3NhbnMtc2VyaWYnIGZvbnQtc2l6ZT0nMTAnIGZvbnQtd2VpZ2h0PSc5MDAnIGZpbGw9JyMwMDAnIHRleHQtYW5jaG9yPSdtaWRkbGUnPkFkbWluIENhbmR5Q3V0eiBCYXJiaW5nIFNhbG9vbjwvdGV4dD48dGV4dCB4PScxNjAnIHk9JzIwJyBmb250LWZhbWlseT0nc2Fucy1zZXJpZicgZm9udC1zaXplPScxMCcgZm9udC13ZWlnaHQ9JzkwMCcgZmlsbD0nIzAwMCcgdGV4dC1hbmNob3I9J21pZGRsZSc+QWRtaW4gQ2FuZHlDdXR6IEJhcmJpbmcgU2Fsb29uPC90ZXh0Pjx0ZXh0IHg9JzgwJyB5PSczNScgZm9udC1mYW1pbHk9J3NhbnMtc2VyaWYnIGZvbnQtc2l6ZT0nMTAnIGZvbnQtd2VpZ2h0PSc5MDAnIGZpbGw9JyMwMDAnIHRleHQtYW5jaG9yPSdtaWRkbGUnPkFkbWluIENhbmR5Q3V0eiBCYXJiaW5nIFNhbG9vbjwvdGV4dD48dGV4dCB4PScwJyB5PSc1MCcgZm9udC1mYW1pbHk9J3NhbnMtc2VyaWYnIGZvbnQtc2l6ZT0nMTAnIGZvbnQtd2VpZ2h0PSc5MDAnIGZpbGw9JyMwMDAnIHRleHQtYW5jaG9yPSdtaWRkbGUnPkFkbWluIENhbmR5Q3V0eiBCYXJiaW5nIFNhbG9vbjwvdGV4dD48dGV4dCB4PScxNjAnIHk9JzUwJyBmb250LWZhbWlseT0nc2Fucy1zZXJpZicgZm9udC1zaXplPScxMCcgZm9udC13ZWlnaHQ9JzkwMCcgZmlsbD0nIzAwMCcgdGV4dC1hbmNob3I9J21pZGRsZSc+QWRtaW4gQ2FuZHlDdXR6IEJhcmJpbmcgU2Fsb29uPC90ZXh0Pjx0ZXh0IHg9JzgwJyB5PSc2NScgZm9udC1mYW1pbHk9J3NhbnMtc2VyaWYnIGZvbnQtc2l6ZT0nMTAnIGZvbnQtd2VpZ2h0PSc5MDAnIGZpbGw9JyMwMDAnIHRleHQtYW5jaG9yPSdtaWRkbGUnPkFkbWluIENhbmR5Q3V0eiBCYXJiaW5nIFNhbG9vbjwvdGV4dD48dGV4dCB4PScwJyB5PSc4MCcgZm9udC1mYW1pbHk9J3NhbnMtc2VyaWYnIGZvbnQtc2l6ZT0nMTAnIGZvbnQtd2VpZ2h0PSc5MDAnIGZpbGw9JyMwMDAnIHRleHQtYW5jaG9yPSdtaWRkbGUnPkFkbWluIENhbmR5Q3V0eiBCYXJiaW5nIFNhbG9vbjwvdGV4dD48dGV4dCB4PScxNjAnIHk9JzgwJyBmb250LWZhbWlseT0nc2Fucy1zZXJpZicgZm9udC1zaXplPScxMCcgZm9udC13ZWlnaHQ9JzkwMCcgZmlsbD0nIzAwMCcgdGV4dC1hbmNob3I9J21pZGRsZSc+QWRtaW4gQ2FuZHlDdXR6IEJhcmJpbmcgU2Fsb29uPC90ZXh0Pjx0ZXh0IHg9JzgwJyB5PSc5NScgZm9udC1mYW1pbHk9J3NhbnMtc2VyaWYnIGZvbnQtc2l6ZT0nMTAnIGZvbnQtd2VpZ2h0PSc5MDAnIGZpbGw9JyMwMDAnIHRleHQtYW5jaG9yPSdtaWRkbGUnPkFkbWluIENhbmR5Q3V0eiBCYXJiaW5nIFNhbG9vbjwvdGV4dD48dGV4dCB4PScwJyB5PScxMScgZm9udC13ZWlnaHQ9JzkwMCcgZmlsbD0nIzAwMCcgdGV4dC1hbmNob3I9J21pZGRsZSc+QWRtaW4gQ2FuZHlDdXR6IEJhcmJpbmcgU2Fsb29uPC90ZXh0Pjx0ZXh0IHg9JzE2MCcgeT0nMTEwJyBmb250LWZhbWlseT0nc2Fucy1zZXJpZicgZm9udC1zaXplPScxMCcgZm9udC13ZWlnaHQ9JzkwMCcgZmlsbD0nIzAwMCcgdGV4dC1hbmNob3I9J21pZGRsZSc+QWRtaW4gQ2FuZHlDdXR6IEJhcmJpbmcgU2Fsb29uPC90ZXh0Pjx0ZXh0IHg9JzgwJyB5PScxMjUnIGZvbnQtZmFtaWx5PSdzYW5zLXNlcmlmJyBmb250LXNpemU9JzEwJyBmb250LXdlaWdodD0nOTAwJyBmaWxsPScjMDAwJyB0ZXh0LWFuY2hvcj0nbWlkZGxlJz5BZG1pbiBDYW5keUN1dHogQmFyYmluZyBTYWxvb248L3RleHQ+PC9nPjwvc3ZnPg==");
  background-repeat: repeat;
}
</style>
