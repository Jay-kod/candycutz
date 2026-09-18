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
              <div class="report-page">
                <div class="report-watermark"></div>
                <div class="relative z-10 px-10 py-12">
                  <!-- Header -->
                  <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 40px; border-bottom: 2px solid #f3f4f6; padding-bottom: 24px;">
                    <div>
                      <h1 style="font-size: 28px; font-weight: 900; color: #111827; margin: 0; letter-spacing: -0.02em;">CANDYCUTZ</h1>
                      <p style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.2em; color: #FF6700; margin: 4px 0 0 0; font-weight: 800;">Management System</p>
                    </div>
                    <div style="text-align: right;">
                      <h2 style="font-size: 20px; font-weight: 700; color: #374151; margin: 0;">Operational Analytics</h2>
                      <p style="font-size: 12px; color: #6b7280; margin: 4px 0 0 0;">{{ todayFormatted }}</p>
                      <p style="display: inline-block; background: #fff7ed; color: #ea580c; border: 1px solid #fed7aa; font-size: 10px; font-weight: 700; padding: 4px 10px; border-radius: 12px; margin-top: 12px; text-transform: uppercase; letter-spacing: 0.05em;">Period: {{ activeRangeLabel }}</p>
                    </div>
                  </div>

                  <!-- Executive Summary (KPIs) -->
                  <h2 style="font-size: 16px; font-weight: 700; color: #1a1a1a; text-transform: uppercase; letter-spacing: 0.15em; margin: 0 0 16px 0; padding-bottom: 8px; border-bottom: 2px solid #FF6700;">Executive Summary</h2>
                  
                  <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; margin-bottom: 32px;">
                    <div style="border: 1px solid #e5e7eb; border-radius: 12px; padding: 16px; background: linear-gradient(135deg, #f0fdf4 0%, #ffffff 100%);">
                      <p style="font-size: 10px; text-transform: uppercase; letter-spacing: 0.1em; color: #6b7280; margin: 0 0 4px 0; font-weight: 700;">Total Revenue</p>
                      <p style="font-size: 24px; font-weight: 800; color: #059669; margin: 0;">₦{{ Number(businessStats.total_revenue || 0).toLocaleString() }}</p>
                    </div>
                    <div style="border: 1px solid #e5e7eb; border-radius: 12px; padding: 16px; background: linear-gradient(135deg, #fff7ed 0%, #ffffff 100%);">
                      <p style="font-size: 10px; text-transform: uppercase; letter-spacing: 0.1em; color: #6b7280; margin: 0 0 4px 0; font-weight: 700;">Completed Appointments</p>
                      <p style="font-size: 24px; font-weight: 800; color: #ea580c; margin: 0;">{{ businessStats.total_appointments || 0 }}</p>
                    </div>
                  </div>

                  <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-bottom: 40px;">
                    <div style="border: 1px solid #e5e7eb; border-radius: 12px; padding: 14px; background: linear-gradient(135deg, #eff6ff 0%, #ffffff 100%);">
                      <p style="font-size: 9px; text-transform: uppercase; letter-spacing: 0.1em; color: #6b7280; margin: 0 0 4px 0; font-weight: 700;">New Clients</p>
                      <p style="font-size: 18px; font-weight: 800; color: #2563eb; margin: 0;">{{ businessStats.new_customers || 0 }}</p>
                      <p style="font-size: 9px; color: #9ca3af; margin: 4px 0 0 0;">In period</p>
                    </div>
                    <div style="border: 1px solid #e5e7eb; border-radius: 12px; padding: 14px; background: linear-gradient(135deg, #e0f2fe 0%, #ffffff 100%);">
                      <p style="font-size: 9px; text-transform: uppercase; letter-spacing: 0.1em; color: #6b7280; margin: 0 0 4px 0; font-weight: 700;">Total Clients</p>
                      <p style="font-size: 18px; font-weight: 800; color: #0284c7; margin: 0;">{{ businessStats.total_customers || 0 }}</p>
                      <p style="font-size: 9px; color: #9ca3af; margin: 4px 0 0 0;">All time</p>
                    </div>
                    <div style="border: 1px solid #e5e7eb; border-radius: 12px; padding: 14px; background: linear-gradient(135deg, #faf5ff 0%, #ffffff 100%);">
                      <p style="font-size: 9px; text-transform: uppercase; letter-spacing: 0.1em; color: #6b7280; margin: 0 0 4px 0; font-weight: 700;">Blog Posts</p>
                      <p style="font-size: 18px; font-weight: 800; color: #7c3aed; margin: 0;">{{ platformStats.total_blog_posts || 0 }}</p>
                      <p style="font-size: 9px; color: #9ca3af; margin: 4px 0 0 0;">Published</p>
                    </div>
                  </div>

                  <!-- Appointment Status -->
                  <h2 style="font-size: 16px; font-weight: 700; color: #1a1a1a; text-transform: uppercase; letter-spacing: 0.15em; margin: 0 0 16px 0; padding-bottom: 8px; border-bottom: 2px solid #FF6700;">Appointment Status Breakdown</h2>
                  
                  <div v-if="statusBreakdown.length > 0" style="margin-bottom: 32px;">
                    <!-- Visual Bar Chart -->
                    <div style="display: flex; flex-direction: column; gap: 12px;">
                      <div v-for="status in statusBreakdown" :key="'pdf-status-' + status.status" style="display: flex; align-items: center; gap: 12px;">
                        <span style="width: 90px; text-align: right; font-size: 12px; font-weight: 600; text-transform: capitalize; color: #374151;">{{ status.status }}</span>
                        <div style="flex: 1; height: 28px; background: #f3f4f6; border-radius: 8px; overflow: hidden; position: relative;">
                          <div :style="{ 
                            width: totalAppointments > 0 ? Math.max((status.count / totalAppointments) * 100, 4) + '%' : '4%', 
                            height: '100%', 
                            borderRadius: '8px',
                            background: statusBarColor(status.status),
                            display: 'flex',
                            alignItems: 'center',
                            justifyContent: 'flex-end',
                            paddingRight: '10px',
                            transition: 'width 0.5s ease'
                          }">
                            <span style="font-size: 11px; font-weight: 700; color: white; text-shadow: 0 1px 2px rgba(0,0,0,0.3);">{{ status.count }}</span>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  <p v-else style="color: #9ca3af; font-size: 13px; text-align: center; padding: 20px 0;">No appointment data available.</p>
                </div>
              </div>

              <!-- Page 2: Top Barbers & Services -->
              <div class="report-page relative border-t border-gray-200">
                <div class="report-watermark"></div>
                <div class="relative z-10 px-10 py-8">
                  <!-- Top Barbers Section -->
                  <h2 style="font-size: 16px; font-weight: 700; color: #1a1a1a; text-transform: uppercase; letter-spacing: 0.15em; margin: 0 0 20px 0; padding-bottom: 8px; border-bottom: 2px solid #FF6700;">
                    Top Performing Barbers
                  </h2>

                  <div v-if="topBarbers.length > 0" style="margin-bottom: 40px;">
                    <table style="width: 100%; border-collapse: separate; border-spacing: 0; font-size: 13px;">
                      <thead>
                        <tr>
                          <th style="text-align: left; padding: 12px 16px; background: transparent; border-bottom: 2px solid #9ca3af; font-size: 10px; text-transform: uppercase; letter-spacing: 0.1em; color: #4b5563; font-weight: 800;">Rank</th>
                          <th style="text-align: left; padding: 12px 16px; background: transparent; border-bottom: 2px solid #9ca3af; font-size: 10px; text-transform: uppercase; letter-spacing: 0.1em; color: #4b5563; font-weight: 800;">Barber Name</th>
                          <th style="text-align: center; padding: 12px 16px; background: transparent; border-bottom: 2px solid #9ca3af; font-size: 10px; text-transform: uppercase; letter-spacing: 0.1em; color: #4b5563; font-weight: 800;">Bookings</th>
                          <th style="text-align: center; padding: 12px 16px; background: transparent; border-bottom: 2px solid #9ca3af; font-size: 10px; text-transform: uppercase; letter-spacing: 0.1em; color: #4b5563; font-weight: 800;">Rating</th>
                          <th style="text-align: right; padding: 12px 16px; background: transparent; border-bottom: 2px solid #9ca3af; font-size: 10px; text-transform: uppercase; letter-spacing: 0.1em; color: #4b5563; font-weight: 800;">Revenue</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-for="(barber, index) in topBarbers" :key="'pdf-barber-' + barber.id" style="background: transparent;">
                          <td style="padding: 14px 16px; border-bottom: 1px solid #d1d5db;">
                            <div :style="{ 
                              width: '28px', height: '28px', borderRadius: '8px', display: 'flex', alignItems: 'center', justifyContent: 'center', 
                              fontWeight: 700, fontSize: '12px', color: 'white',
                              background: index === 0 ? '#FF6700' : index === 1 ? '#6b7280' : '#9ca3af'
                            }">{{ index + 1 }}</div>
                          </td>
                          <td style="padding: 14px 16px; border-bottom: 1px solid #d1d5db; font-weight: 600; color: #111827;">{{ barber.name }}</td>
                          <td style="padding: 14px 16px; border-bottom: 1px solid #d1d5db; text-align: center; color: #374151;">{{ barber.bookings }}</td>
                          <td style="padding: 14px 16px; border-bottom: 1px solid #d1d5db; text-align: center; color: #d97706;">⭐ {{ barber.rating || 'N/A' }}</td>
                          <td style="padding: 14px 16px; border-bottom: 1px solid #d1d5db; text-align: right; font-weight: 700; color: #059669;">₦{{ Number(barber.revenue).toLocaleString() }}</td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                  <p v-else style="color: #9ca3af; font-size: 13px; text-align: center; padding: 20px 0; margin-bottom: 40px;">No barber performance data available for this period.</p>

                  <!-- Top Services Section -->
                  <h2 style="font-size: 16px; font-weight: 700; color: #1a1a1a; text-transform: uppercase; letter-spacing: 0.15em; margin: 0 0 20px 0; padding-bottom: 8px; border-bottom: 2px solid #FF6700;">
                    Most Popular Services
                  </h2>

                  <div v-if="topServices.length > 0" style="margin-bottom: 32px;">
                    <table style="width: 100%; border-collapse: separate; border-spacing: 0; font-size: 13px;">
                      <thead>
                        <tr>
                          <th style="text-align: left; padding: 12px 16px; background: transparent; border-bottom: 2px solid #9ca3af; font-size: 10px; text-transform: uppercase; letter-spacing: 0.1em; color: #4b5563; font-weight: 800;">Rank</th>
                          <th style="text-align: left; padding: 12px 16px; background: transparent; border-bottom: 2px solid #9ca3af; font-size: 10px; text-transform: uppercase; letter-spacing: 0.1em; color: #4b5563; font-weight: 800;">Service Name</th>
                          <th style="text-align: center; padding: 12px 16px; background: transparent; border-bottom: 2px solid #9ca3af; font-size: 10px; text-transform: uppercase; letter-spacing: 0.1em; color: #4b5563; font-weight: 800;">Times Booked</th>
                          <th style="text-align: right; padding: 12px 16px; background: transparent; border-bottom: 2px solid #9ca3af; font-size: 10px; text-transform: uppercase; letter-spacing: 0.1em; color: #4b5563; font-weight: 800;">Revenue</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-for="(service, index) in topServices" :key="'pdf-service-' + index" style="background: transparent;">
                          <td style="padding: 14px 16px; border-bottom: 1px solid #d1d5db;">
                            <div :style="{ 
                              width: '28px', height: '28px', borderRadius: '8px', display: 'flex', alignItems: 'center', justifyContent: 'center', 
                              fontWeight: 700, fontSize: '12px', color: 'white',
                              background: index === 0 ? '#059669' : index === 1 ? '#6b7280' : '#9ca3af'
                            }">{{ index + 1 }}</div>
                          </td>
                          <td style="padding: 14px 16px; border-bottom: 1px solid #d1d5db; font-weight: 600; color: #111827;">{{ service.name }}</td>
                          <td style="padding: 14px 16px; border-bottom: 1px solid #d1d5db; text-align: center; color: #374151;">{{ service.count }}</td>
                          <td style="padding: 14px 16px; border-bottom: 1px solid #d1d5db; text-align: right; font-weight: 700; color: #ea580c;">₦{{ Number(service.revenue).toLocaleString() }}</td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                  <p v-else style="color: #9ca3af; font-size: 13px; text-align: center; padding: 20px 0;">No service data available for this period.</p>

                  <!-- Footer & Authentication -->
                  <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: flex-end;">
                    <div>
                      <p style="font-size: 10px; color: #9ca3af; margin: 0;">This report is auto-generated by CandyCutz Management System.</p>
                      <p style="font-size: 10px; color: #9ca3af; margin: 2px 0 0 0;">Report Period: {{ activeRangeLabel }} • Generated: {{ todayFormatted }} at {{ currentTime }}</p>
                      
                      <!-- Digital Signature / Authenticity stamp -->
                      <div style="margin-top: 12px; display: flex; gap: 8px; align-items: center; background: #f9fafb; padding: 6px 10px; border-radius: 6px; border: 1px dashed #d1d5db; width: fit-content;">
                        <svg xmlns="http://www.w3.org/2000/svg" style="width: 14px; height: 14px; color: #059669;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path><polyline points="9 12 11 14 15 10"></polyline></svg>
                        <div>
                          <p style="font-size: 8px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.1em; margin: 0; font-weight: 700;">Authenticity Verification ID</p>
                          <p style="font-size: 11px; font-family: monospace; font-weight: 800; color: #111827; margin: 2px 0 0 0; letter-spacing: 0.05em;">{{ reportId }}</p>
                        </div>
                      </div>
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                      <div style="width: 24px; height: 24px; border-radius: 6px; background: #FF6700; display: flex; align-items: center; justify-content: center;">
                        <span style="font-size: 12px;">✂️</span>
                      </div>
                      <span style="font-size: 11px; font-weight: 700; color: #374151;">CandyCutz</span>
                    </div>
                  </div>
                </div>
              </div>

            </div>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, computed } from 'vue';
import { XMarkIcon, ArrowDownTrayIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
  show: { type: Boolean, required: true },
  businessStats: { type: Object, required: true },
  platformStats: { type: Object, required: true },
  topBarbers: { type: Array, required: true },
  topServices: { type: Array, required: true },
  statusBreakdown: { type: Array, required: true },
  activeRangeLabel: { type: String, required: true },
  selectedRange: { type: String, required: true }
});

defineEmits(['close']);

const isGeneratingPdf = ref(false);
const reportRef = ref(null);

const todayFormatted = computed(() => {
  return new Date().toLocaleDateString('en-GB', { 
    weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' 
  });
});

const currentTime = computed(() => {
  return new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
});

const reportId = computed(() => {
  const timestamp = Date.now().toString(36).toUpperCase();
  const hash = Math.random().toString(36).substring(2, 8).toUpperCase();
  return `REF-CCZ-${timestamp}-${hash}`;
});

const totalAppointments = computed(() => {
  return props.statusBreakdown.reduce((sum, s) => sum + Number(s.count), 0);
});

const statusBarColor = (status) => {
  const colors = {
    completed: 'linear-gradient(135deg, #059669, #10b981)',
    pending: 'linear-gradient(135deg, #d97706, #f59e0b)',
    cancelled: 'linear-gradient(135deg, #dc2626, #ef4444)',
    approved: 'linear-gradient(135deg, #2563eb, #3b82f6)',
    confirmed: 'linear-gradient(135deg, #2563eb, #3b82f6)',
    no_show: 'linear-gradient(135deg, #6b7280, #9ca3af)'
  };
  return colors[status] || 'linear-gradient(135deg, #6b7280, #9ca3af)';
};

const downloadPdf = async () => {
  if (!reportRef.value) return;
  isGeneratingPdf.value = true;
  
  try {
    const html2pdf = (await import('html2pdf.js')).default;
    
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
    };
    
    await html2pdf().set(opt).from(reportRef.value).save();
  } catch (err) {
    console.error('PDF generation failed:', err);
  } finally {
    isGeneratingPdf.value = false;
  }
};
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

.report-page {
  width: 794px;
  position: relative;
  box-sizing: border-box;
}

.report-watermark {
  position: absolute;
  inset: 0;
  z-index: 0;
  pointer-events: none;
  opacity: 0.06;
  background-image: url("data:image/svg+xml;base64,PHN2ZyB4bWxucz0naHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmcnIHdpZHRoPScxNjAnIGhlaWdodD0nMTAwJz48ZyB0cmFuc2Zvcm09J3JvdGF0ZSgtMzAgODAgNTApJz48dGV4dCB4PSc4MCcgeT0nLTI1JyBmb250LWZhbWlseT0nc2Fucy1zZXJpZicgZm9udC1zaXplPScxMCcgZm9udC13ZWlnaHQ9JzkwMCcgZmlsbD0nIzAwMCcgdGV4dC1hbmNob3I9J21pZGRsZSc+QWRtaW4gQ2FuZHlDdXR6IEJhcmJpbmcgU2Fsb29uPC90ZXh0Pjx0ZXh0IHg9JzAnIHk9Jy0xMCcgZm9udC1mYW1pbHk9J3NhbnMtc2VyaWYnIGZvbnQtc2l6ZT0nMTAnIGZvbnQtd2VpZ2h0PSc5MDAnIGZpbGw9JyMwMDAnIHRleHQtYW5jaG9yPSdtaWRkbGUnPkFkbWluIENhbmR5Q3V0eiBCYXJiaW5nIFNhbG9vbjwvdGV4dD48dGV4dCB4PScxNjAnIHk9Jy0xMCcgZm9udC1mYW1pbHk9J3NhbnMtc2VyaWYnIGZvbnQtc2l6ZT0nMTAnIGZvbnQtd2VpZ2h0PSc5MDAnIGZpbGw9JyMwMDAnIHRleHQtYW5jaG9yPSdtaWRkbGUnPkFkbWluIENhbmR5Q3V0eiBCYXJiaW5nIFNhbG9vbjwvdGV4dD48dGV4dCB4PSc4MCcgeT0nNScgZm9udC1mYW1pbHk9J3NhbnMtc2VyaWYnIGZvbnQtc2l6ZT0nMTAnIGZvbnQtd2VpZ2h0PSc5MDAnIGZpbGw9JyMwMDAnIHRleHQtYW5jaG9yPSdtaWRkbGUnPkFkbWluIENhbmR5Q3V0eiBCYXJiaW5nIFNhbG9vbjwvdGV4dD48dGV4dCB4PScwJyB5PScyMCcgZm9udC1mYW1pbHk9J3NhbnMtc2VyaWYnIGZvbnQtc2l6ZT0nMTAnIGZvbnQtd2VpZ2h0PSc5MDAnIGZpbGw9JyMwMDAnIHRleHQtYW5jaG9yPSdtaWRkbGUnPkFkbWluIENhbmR5Q3V0eiBCYXJiaW5nIFNhbG9vbjwvdGV4dD48dGV4dCB4PScxNjAnIHk9JzIwJyBmb250LWZhbWlseT0nc2Fucy1zZXJpZicgZm9udC1zaXplPScxMCcgZm9udC13ZWlnaHQ9JzkwMCcgZmlsbD0nIzAwMCcgdGV4dC1hbmNob3I9J21pZGRsZSc+QWRtaW4gQ2FuZHlDdXR6IEJhcmJpbmcgU2Fsb29uPC90ZXh0Pjx0ZXh0IHg9JzgwJyB5PSczNScgZm9udC1mYW1pbHk9J3NhbnMtc2VyaWYnIGZvbnQtc2l6ZT0nMTAnIGZvbnQtd2VpZ2h0PSc5MDAnIGZpbGw9JyMwMDAnIHRleHQtYW5jaG9yPSdtaWRkbGUnPkFkbWluIENhbmR5Q3V0eiBCYXJiaW5nIFNhbG9vbjwvdGV4dD48dGV4dCB4PScwJyB5PSc1MCcgZm9udC1mYW1pbHk9J3NhbnMtc2VyaWYnIGZvbnQtc2l6ZT0nMTAnIGZvbnQtd2VpZ2h0PSc5MDAnIGZpbGw9JyMwMDAnIHRleHQtYW5jaG9yPSdtaWRkbGUnPkFkbWluIENhbmR5Q3V0eiBCYXJiaW5nIFNhbG9vbjwvdGV4dD48dGV4dCB4PScxNjAnIHk9JzUwJyBmb250LWZhbWlseT0nc2Fucy1zZXJpZicgZm9udC1zaXplPScxMCcgZm9udC13ZWlnaHQ9JzkwMCcgZmlsbD0nIzAwMCcgdGV4dC1hbmNob3I9J21pZGRsZSc+QWRtaW4gQ2FuZHlDdXR6IEJhcmJpbmcgU2Fsb29uPC90ZXh0Pjx0ZXh0IHg9JzgwJyB5PSc2NScgZm9udC1mYW1pbHk9J3NhbnMtc2VyaWYnIGZvbnQtc2l6ZT0nMTAnIGZvbnQtd2VpZ2h0PSc5MDAnIGZpbGw9JyMwMDAnIHRleHQtYW5jaG9yPSdtaWRkbGUnPkFkbWluIENhbmR5Q3V0eiBCYXJiaW5nIFNhbG9vbjwvdGV4dD48dGV4dCB4PScwJyB5PSc4MCcgZm9udC1mYW1pbHk9J3NhbnMtc2VyaWYnIGZvbnQtc2l6ZT0nMTAnIGZvbnQtd2VpZ2h0PSc5MDAnIGZpbGw9JyMwMDAnIHRleHQtYW5jaG9yPSdtaWRkbGUnPkFkbWluIENhbmR5Q3V0eiBCYXJiaW5nIFNhbG9vbjwvdGV4dD48dGV4dCB4PScxNjAnIHk9JzgwJyBmb250LWZhbWlseT0nc2Fucy1zZXJpZicgZm9udC1zaXplPScxMCcgZm9udC13ZWlnaHQ9JzkwMCcgZmlsbD0nIzAwMCcgdGV4dC1hbmNob3I9J21pZGRsZSc+QWRtaW4gQ2FuZHlDdXR6IEJhcmJpbmcgU2Fsb29uPC90ZXh0Pjx0ZXh0IHg9JzgwJyB5PSc5NScgZm9udC1mYW1pbHk9J3NhbnMtc2VyaWYnIGZvbnQtc2l6ZT0nMTAnIGZvbnQtd2VpZ2h0PSc5MDAnIGZpbGw9JyMwMDAnIHRleHQtYW5jaG9yPSdtaWRkbGUnPkFkbWluIENhbmR5Q3V0eiBCYXJiaW5nIFNhbG9vbjwvdGV4dD48dGV4dCB4PScwJyB5PScxMScgZm9udC13ZWlnaHQ9JzkwMCcgZmlsbD0nIzAwMCcgdGV4dC1hbmNob3I9J21pZGRsZSc+QWRtaW4gQ2FuZHlDdXR6IEJhcmJpbmcgU2Fsb29uPC90ZXh0Pjx0ZXh0IHg9JzE2MCcgeT0nMTEwJyBmb250LWZhbWlseT0nc2Fucy1zZXJpZicgZm9udC1zaXplPScxMCcgZm9udC13ZWlnaHQ9JzkwMCcgZmlsbD0nIzAwMCcgdGV4dC1hbmNob3I9J21pZGRsZSc+QWRtaW4gQ2FuZHlDdXR6IEJhcmJpbmcgU2Fsb29uPC90ZXh0Pjx0ZXh0IHg9JzgwJyB5PScxMjUnIGZvbnQtZmFtaWx5PSdzYW5zLXNlcmlmJyBmb250LXNpemU9JzEwJyBmb250LXdlaWdodD0nOTAwJyBmaWxsPScjMDAwJyB0ZXh0LWFuY2hvcj0nbWlkZGxlJz5BZG1pbiBDYW5keUN1dHogQmFyYmluZyBTYWxvb248L3RleHQ+PC9nPjwvc3ZnPg==");
  background-repeat: repeat;
}
</style>
